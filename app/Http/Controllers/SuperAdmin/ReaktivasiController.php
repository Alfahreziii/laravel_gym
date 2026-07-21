<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Master\DatabasePool;
use App\Models\Master\Package;
use App\Models\Master\Tenant;
use App\Models\Master\TenantBackup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReaktivasiController extends Controller
{
    public function create(Tenant $tenant)
    {
        if ($tenant->status !== 'archived') {
            return redirect()->route('super_admin.kelola_tenant.show', $tenant)->withErrors([
                '_general' => "Tenant «{$tenant->nama_gym}» tidak dalam status arsip.",
            ]);
        }

        $pools    = DatabasePool::where('status', 'available')->orderBy('db_name')->get();
        $packages = Package::orderBy('nama')->get();

        // Arsip ZIP milik tenant ini yang file-nya masih ada di server (disk 'local')
        $archives = $tenant->backups()
            ->whereNotNull('storage_zip_path')
            ->orderByDesc('tgl_backup')
            ->get()
            ->filter(fn ($backup) => Storage::disk('local')->exists($backup->storage_zip_path))
            ->values();

        return view('superadmin.kelola-tenant.reaktivasi', compact('tenant', 'pools', 'packages', 'archives'));
    }

    public function store(Request $request, Tenant $tenant)
    {
        // ── 1. Guard status ────────────────────────────────────────────
        if ($tenant->status !== 'archived') {
            return redirect()->route('super_admin.kelola_tenant.show', $tenant)->withErrors([
                '_general' => "Tenant «{$tenant->nama_gym}» tidak dalam status arsip.",
            ]);
        }

        // ── 2. Validasi ─────────────────────────────────────────────────
        $request->validate([
            'database_pool_id'  => ['required', 'integer'],
            'package_id'        => ['required', 'integer'],
            'tgl_mulai'         => ['required', 'date'],
            'tgl_selesai'       => ['required', 'date', 'after:tgl_mulai'],
            'zip_source'        => ['required', 'in:archive,upload,skip'],
            'archive_backup_id' => ['nullable', 'integer', 'required_if:zip_source,archive'],
            'storage_zip'       => ['nullable', 'file', 'required_if:zip_source,upload'],
        ]);

        // ── 3. Guard pool — cegah race condition (fresh dari DB master) ──
        $pool = DatabasePool::find($request->integer('database_pool_id'));
        if (! $pool || $pool->status !== 'available') {
            return back()->withInput()->withErrors([
                'database_pool_id' => 'Database pool ini sudah digunakan atau tidak tersedia. Pilih pool lain.',
            ]);
        }

        $package = Package::find($request->integer('package_id'));
        if (! $package) {
            return back()->withInput()->withErrors([
                'package_id' => 'Paket tidak ditemukan.',
            ]);
        }

        // ── 4. Resolve sumber ZIP + link download SQL terkait ─────────────
        $zipSourcePath  = null; // absolute path file zip yang akan di-extract
        $tempUploadPath = null; // relative path di disk 'local', dihapus setelah dipakai
        $sqlDownloadUrl = null;

        if ($request->zip_source === 'archive' && $request->filled('archive_backup_id')) {
            $backup = TenantBackup::where('tenant_id', $tenant->id)
                ->find($request->integer('archive_backup_id'));

            if ($backup && $backup->storage_zip_path && Storage::disk('local')->exists($backup->storage_zip_path)) {
                $zipSourcePath = Storage::disk('local')->path($backup->storage_zip_path);
            }
            if ($backup && $backup->sql_path && Storage::disk('local')->exists($backup->sql_path)) {
                $sqlDownloadUrl = route('super_admin.backup.download', ['backup' => $backup->id, 'type' => 'sql']);
            }
        } elseif ($request->zip_source === 'upload' && $request->hasFile('storage_zip') && $request->file('storage_zip')->isValid()) {
            $timestamp      = now()->format('Ymd_His');
            $tempUploadPath = "backups/{$tenant->subdomain}_reaktivasi_{$timestamp}.zip";
            $request->file('storage_zip')->storeAs('backups', basename($tempUploadPath), 'local');
            $zipSourcePath  = Storage::disk('local')->path($tempUploadPath);
        }

        // Fallback: kalau belum ada link SQL (upload manual / skip), pakai backup
        // ber-sql_path terbaru milik tenant ini yang masih ada di server.
        if (! $sqlDownloadUrl) {
            $latestSqlBackup = $tenant->backups()
                ->whereNotNull('sql_path')
                ->orderByDesc('tgl_backup')
                ->get()
                ->first(fn ($b) => Storage::disk('local')->exists($b->sql_path));

            if ($latestSqlBackup) {
                $sqlDownloadUrl = route('super_admin.backup.download', ['backup' => $latestSqlBackup->id, 'type' => 'sql']);
            }
        }

        // ── 5. Extract ZIP ke storage tenant (non-fatal) ──────────────────
        $extractWarning = null;
        $zipRestored    = false;

        if ($zipSourcePath) {
            try {
                $zip    = new \ZipArchive();
                $result = $zip->open($zipSourcePath);

                if ($result !== true) {
                    throw new \RuntimeException("ZipArchive tidak dapat membuka file (kode error: {$result}).");
                }

                $destPath = Storage::disk('public')->path("tenants/{$tenant->subdomain}");
                if (! is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }

                $zip->extractTo($destPath);
                $zip->close();
                $zipRestored = true;
            } catch (\Throwable $e) {
                $extractWarning = $e->getMessage();
                Log::warning('Reaktivasi: gagal extract ZIP storage', [
                    'tenant' => $tenant->subdomain,
                    'error'  => $e->getMessage(),
                ]);
            }

            // File upload sementara sudah tidak diperlukan lagi (bukan arsip resmi)
            if ($tempUploadPath) {
                Storage::disk('local')->delete($tempUploadPath);
            }
        }

        // ── 6. Update MASTER dalam transaction ────────────────────────────
        // Status → 'nonaktif' (BUKAN 'aktif'): SQL arsip belum di-import, jadi
        // DB tenant masih kosong. Super admin ubah ke 'aktif' manual di Kelola
        // Tenant setelah proses import SQL selesai.
        try {
            DB::connection('mysql_master')->transaction(function () use ($tenant, $pool, $package, $request) {
                $tenant->update([
                    'status'           => 'nonaktif',
                    'database_pool_id' => $pool->id,
                    'package_id'       => $package->id,
                    'tgl_mulai'        => $request->tgl_mulai,
                    'tgl_selesai'      => $request->tgl_selesai,
                ]);

                $tenant->module()->updateOrCreate(
                    ['tenant_id' => $tenant->id],
                    [
                        'trainer'  => $package->trainer,
                        'pos'      => $package->pos,
                        'keuangan' => $package->keuangan,
                    ]
                );

                $pool->update(['status' => 'used']);
            });
        } catch (\Throwable $e) {
            // Best-effort cleanup: hapus folder yang baru di-extract agar tidak orphan
            if ($zipRestored) {
                try {
                    Storage::disk('public')->deleteDirectory("tenants/{$tenant->subdomain}");
                } catch (\Throwable $cleanupError) {
                    Log::warning('Reaktivasi: gagal cleanup folder storage setelah master gagal', [
                        'tenant' => $tenant->subdomain,
                        'reason' => $cleanupError->getMessage(),
                    ]);
                }
            }

            Log::error('Reaktivasi: master write gagal', [
                'tenant' => $tenant->subdomain,
                'pool'   => $pool->db_name,
                'error'  => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                '_master' => 'Gagal menyimpan data reaktivasi ke database master: ' . $e->getMessage(),
            ]);
        }

        // Catatan: tidak ada switch koneksi 'tenant' di controller ini — semua
        // operasi hanya menyentuh mysql_master + filesystem, jadi tidak perlu
        // DB::purge('tenant').

        // ── 7. Redirect sukses + instruksi langkah manual ─────────────────
        return redirect()
            ->route('super_admin.kelola_tenant.show', $tenant)
            ->with('success', "Gym \"{$tenant->nama_gym}\" siap diaktifkan kembali. Status saat ini: NON-AKTIF (gym belum bisa diakses).")
            ->with('reaktivasi_info', [
                'db_name'          => $pool->db_name,
                'subdomain'        => $tenant->subdomain,
                'base_domain'      => env('TENANT_BASE_DOMAIN', 'sistemgate.com'),
                'sql_download_url' => $sqlDownloadUrl,
                'zip_restored'     => $zipRestored,
                'zip_warning'      => $extractWarning,
            ]);
    }
}

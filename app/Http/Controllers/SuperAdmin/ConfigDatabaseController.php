<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Master\DatabasePool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ConfigDatabaseController extends Controller
{
    public function index()
    {
        $pools = DatabasePool::with('tenant')->orderBy('created_at')->get();

        $stats = [
            'total'     => $pools->count(),
            'available' => $pools->where('status', 'available')->count(),
            'used'      => $pools->where('status', 'used')->count(),
        ];

        return view('superadmin.config-database', compact('pools', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'db_name'     => ['required', 'string', 'max:100'],
            'db_host'     => ['required', 'string', 'max:255'],
            'db_username' => ['required', 'string', 'max:100'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ]);

        // Cek unik secara manual agar pakai koneksi mysql_master
        if (DatabasePool::where('db_name', $request->db_name)->exists()) {
            return back()
                ->withErrors(['db_name' => "Database '{$request->db_name}' sudah terdaftar di pool."])
                ->withInput()
                ->with('open_modal', 'add-pool');
        }

        DatabasePool::create([
            'db_name'     => $request->db_name,
            'db_host'     => $request->db_host,
            'db_username' => $request->db_username,
            'db_password' => $request->db_password ?? '',
            'status'      => 'available',
        ]);

        return back()->with('success', "Database '{$request->db_name}' berhasil ditambahkan ke pool.");
    }

    // ──────────────────────────────────────────────────────────────────────
    // POST /config-database/{pool}/clear
    // Truncate selective + archive tenant di master + pool kembali available.
    // Tenant TIDAK dihapus — diarsipkan agar riwayat gym tetap terlihat.
    // ──────────────────────────────────────────────────────────────────────
    public function clear(DatabasePool $pool): RedirectResponse
    {
        $pool->load('tenant.backups');
        $tenant = $pool->tenant;

        // Guard 1: pool harus punya tenant
        if (! $tenant) {
            return back()->withErrors([
                '_clear' => "Pool «{$pool->db_name}» tidak memiliki tenant terkait. Tidak ada yang perlu di-clear.",
            ]);
        }

        // Guard 2: tenant harus nonaktif
        if ($tenant->status === 'aktif') {
            return back()->withErrors([
                '_clear' => "Gym «{$tenant->nama_gym}» masih berstatus Aktif. Jalankan Backup & Nonaktifkan terlebih dahulu.",
            ]);
        }

        // Guard 2b: sudah diarsipkan (database sudah pernah di-clear)
        if ($tenant->status === 'archived') {
            return back()->withErrors([
                '_clear' => "Gym «{$tenant->nama_gym}» sudah diarsipkan. Database sudah pernah di-clear sebelumnya.",
            ]);
        }

        // Guard 3: harus ada backup yang sudah didownload
        $hasDownloaded = $tenant->backups->where('downloaded', true)->isNotEmpty();
        if (! $hasDownloaded) {
            $hasAny = $tenant->backups->isNotEmpty();
            return back()->withErrors([
                '_clear' => $hasAny
                    ? "Gym «{$tenant->nama_gym}» memiliki backup tapi belum didownload. Download file SQL terlebih dahulu."
                    : "Gym «{$tenant->nama_gym}» belum pernah di-backup. Lakukan backup terlebih dahulu.",
            ]);
        }

        // ── 1. Switch koneksi ke tenant DB ────────────────────────────
        config()->set('database.connections.tenant.host',     $pool->db_host);
        config()->set('database.connections.tenant.database', $pool->db_name);
        config()->set('database.connections.tenant.username', $pool->db_username);
        config()->set('database.connections.tenant.password', $pool->db_password);

        try {
            DB::purge('tenant');
            DB::reconnect('tenant');
        } catch (\Throwable $e) {
            return back()->withErrors([
                '_clear' => "Tidak dapat terhubung ke database «{$pool->db_name}»: " . $e->getMessage(),
            ]);
        }

        // ── 2. Truncate selective (PRESERVE: tabel sistem/template) ───
        try {
            $this->truncateTenantDb();
        } catch (\Throwable $e) {
            DB::purge('tenant');
            return back()->withErrors([
                '_clear' => "Gagal truncate database «{$pool->db_name}»: " . $e->getMessage(),
            ]);
        }

        DB::purge('tenant'); // WAJIB setelah selesai

        // ── 2b. Hapus folder storage tenant ──────────────────────────
        // Foto/file gym sudah diarsipkan di ZIP backup sebelumnya.
        // Setelah clear, gym tidak aktif — storage tidak diperlukan lagi.
        try {
            $storagePath = "tenants/{$tenant->subdomain}";
            if (Storage::disk('public')->exists($storagePath)) {
                Storage::disk('public')->deleteDirectory($storagePath);
            }
        } catch (\Throwable $e) {
            Log::warning('Clear DB: gagal hapus folder storage tenant', [
                'tenant' => $tenant->subdomain,
                'path'   => "tenants/{$tenant->subdomain}",
                'error'  => $e->getMessage(),
            ]);
            // Non-fatal — lanjut ke archive tenant
        }

        // ── 3. Archive tenant + bebaskan pool ────────────────────────
        // Tenant TIDAK dihapus — status → 'archived', database_pool_id → NULL.
        // tenant_modules + tenant_backups tetap ada sebagai riwayat.
        try {
            DB::connection('mysql_master')->transaction(function () use ($pool, $tenant) {
                $tenant->update([
                    'status'           => 'archived',
                    'database_pool_id' => null,       // lepas pool agar bisa dipakai gym baru
                ]);
                $pool->update(['status' => 'available']);
            });
        } catch (\Throwable $e) {
            Log::error('Clear DB: master update gagal setelah truncate berhasil', [
                'pool'   => $pool->db_name,
                'tenant' => $tenant->subdomain,
                'error'  => $e->getMessage(),
            ]);
            return back()->withErrors([
                '_clear' => "Truncate selesai tapi gagal update master: " . $e->getMessage(),
            ]);
        }

        return redirect()->route('super_admin.config_database')->with(
            'success',
            "Database «{$pool->db_name}» selesai di-clear. Tenant «{$tenant->nama_gym}» diarsipkan. Pool kembali tersedia."
        );
    }

    // ──────────────────────────────────────────────────────────────────────
    // Truncate semua tabel KECUALI tabel sistem/template yang dipertahankan.
    // Menggunakan SHOW FULL TABLES agar tahan perubahan nama tabel di masa depan.
    // ──────────────────────────────────────────────────────────────────────
    private function truncateTenantDb(): void
    {
        $preserve = array_map('strtolower', [
            'migrations',
            'roles',
            'permissions',
            'role_has_permissions',
            'akun_keuangans',
            'kategori_akuns',
        ]);

        $tableRows = DB::connection('tenant')
            ->select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");

        DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($tableRows as $tableRow) {
                $tableName = array_values((array) $tableRow)[0];
                if (! in_array(strtolower($tableName), $preserve, true)) {
                    DB::connection('tenant')->statement("TRUNCATE TABLE `{$tableName}`");
                }
            }
        } finally {
            // Restore FK checks bahkan jika truncate parsial gagal
            DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
}

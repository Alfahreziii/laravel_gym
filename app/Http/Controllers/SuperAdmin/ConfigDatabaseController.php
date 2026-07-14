<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Master\DatabasePool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    // Truncate selective + hapus tenant dari master + pool kembali available.
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

        // ── 3. Update master: hapus tenant + bebaskan pool ────────────
        // Catatan: $tenant->delete() men-cascade ke tenant_modules dan
        // tenant_backups (DB records). File SQL di storage/app/backups/ TIDAK terhapus.
        try {
            DB::connection('mysql_master')->transaction(function () use ($pool, $tenant) {
                $tenant->delete();                        // cascade: modules + backup records
                $pool->update(['status' => 'available']); // pool siap dipakai gym baru
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
            "Database «{$pool->db_name}» selesai di-clear. Pool kembali tersedia untuk gym baru."
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

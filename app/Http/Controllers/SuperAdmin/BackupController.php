<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Master\Tenant;
use App\Models\Master\TenantBackup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    // Chunk size per INSERT statement — aman untuk gym (data kecil)
    private const INSERT_CHUNK = 200;

    // ──────────────────────────────────────────────────────────────────
    // POST /tenant/{tenant}/backup
    // ──────────────────────────────────────────────────────────────────
    public function create(Tenant $tenant): RedirectResponse
    {
        $tenant->load('databasePool');
        $pool = $tenant->databasePool;

        if (! $pool) {
            return back()->withErrors([
                '_backup' => "Tenant «{$tenant->nama_gym}» tidak memiliki database pool.",
            ]);
        }

        // ── 1. Switch koneksi tenant ──────────────────────────────────
        config()->set('database.connections.tenant.host',     $pool->db_host);
        config()->set('database.connections.tenant.database', $pool->db_name);
        config()->set('database.connections.tenant.username', $pool->db_username);
        config()->set('database.connections.tenant.password', $pool->db_password);

        try {
            DB::purge('tenant');
            DB::reconnect('tenant');
        } catch (\Throwable $e) {
            return back()->withErrors([
                '_backup' => "Tidak dapat terhubung ke database «{$pool->db_name}»: " . $e->getMessage(),
            ]);
        }

        // ── 2. Generate SQL dump via PHP murni ────────────────────────
        $sqlPath = null;
        try {
            $sqlContent = $this->generateSqlDump($pool->db_name, $tenant->subdomain);

            // Simpan ke storage/app/backups/ (private, tidak public)
            $timestamp = now()->format('Ymd_His');
            $sqlPath   = "backups/{$tenant->subdomain}_{$timestamp}.sql";

            Storage::disk('local')->put($sqlPath, $sqlContent);
            unset($sqlContent); // bebaskan memori

        } catch (\Throwable $e) {
            DB::purge('tenant');
            return back()->withErrors([
                '_backup' => "Gagal membuat SQL dump: " . $e->getMessage(),
            ]);
        }

        // ── 3. Tulis master: TenantBackup + ubah status ───────────────
        // Storage ZIP dilewati — isolasi folder tenant belum ada (Fase 7).
        try {
            DB::connection('mysql_master')->transaction(function () use ($tenant, $sqlPath) {
                TenantBackup::create([
                    'tenant_id'        => $tenant->id,
                    'sql_path'         => $sqlPath,
                    'storage_zip_path' => null,   // Fase 7
                    'tgl_backup'       => today(),
                    'downloaded'       => false,
                ]);

                $tenant->update(['status' => 'nonaktif']);
            });
        } catch (\Throwable $e) {
            // Master gagal → hapus file yang sudah dibuat, jangan tinggalkan orphan
            if ($sqlPath) {
                Storage::disk('local')->delete($sqlPath);
            }
            DB::purge('tenant');

            Log::error('Backup tenant: master write gagal', [
                'tenant'   => $tenant->subdomain,
                'sql_path' => $sqlPath,
                'error'    => $e->getMessage(),
            ]);

            return back()->withErrors([
                '_backup' => "SQL dump berhasil dibuat tapi gagal menyimpan record ke master: " . $e->getMessage(),
            ]);
        }

        // ── 4. Reset koneksi tenant — WAJIB ──────────────────────────
        DB::purge('tenant');

        return redirect()->route('super_admin.dashboard')->with(
            'success',
            "Backup «{$tenant->nama_gym}» selesai. Status diubah ke Non-aktif. Silakan download file SQL dari halaman ini."
        );
    }

    // ──────────────────────────────────────────────────────────────────
    // GET /backup/{backup}/download/{type}
    // ──────────────────────────────────────────────────────────────────
    public function download(TenantBackup $backup, string $type): StreamedResponse|RedirectResponse
    {
        $path = $type === 'sql' ? $backup->sql_path : $backup->storage_zip_path;

        if (! $path || ! Storage::disk('local')->exists($path)) {
            return redirect()->route('super_admin.dashboard')
                             ->withErrors(['_backup' => 'File backup tidak ditemukan di server.']);
        }

        // Tandai sudah didownload (info untuk fase clear nanti)
        if (! $backup->downloaded) {
            $backup->update(['downloaded' => true]);
        }

        return Storage::disk('local')->download($path, basename($path));
    }

    // ──────────────────────────────────────────────────────────────────
    // SQL dump generator — PHP murni, tanpa mysqldump
    // ──────────────────────────────────────────────────────────────────
    private function generateSqlDump(string $dbName, string $label): string
    {
        $pdo = DB::connection('tenant')->getPdo();
        $now = now()->toDateTimeString();

        $out  = "-- HexaGym Database Backup\n";
        $out .= "-- Gym      : {$label}\n";
        $out .= "-- Database : {$dbName}\n";
        $out .= "-- Waktu    : {$now}\n";
        $out .= "-- Generator: HexaGym Super Admin (PHP native)\n";
        $out .= str_repeat('-', 60) . "\n\n";
        $out .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $out .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $out .= "SET NAMES utf8mb4;\n\n";

        // Hanya base tables — abaikan VIEW agar SHOW CREATE TABLE tidak error
        $tableRows = DB::connection('tenant')
            ->select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");

        foreach ($tableRows as $tableRow) {
            // array_values()[0] — tidak butuh reference, aman di PHP 8+
            $tableName = array_values((array) $tableRow)[0];
            $out .= $this->dumpTable($tableName, $pdo);
        }

        $out .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        return $out;
    }

    private function dumpTable(string $tableName, \PDO $pdo): string
    {
        $out = "-- ─────────────────────────────────────────\n";
        $out .= "-- Table: `{$tableName}`\n";
        $out .= "-- ─────────────────────────────────────────\n";

        // Struktur: DROP + CREATE TABLE
        $createRows = DB::connection('tenant')
            ->select("SHOW CREATE TABLE `{$tableName}`");
        $createSql  = $createRows[0]->{'Create Table'};

        $out .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
        $out .= $createSql . ";\n\n";

        // Data
        $count = DB::connection('tenant')->table($tableName)->count();
        if ($count === 0) {
            return $out;
        }

        $columns  = null;
        $colsList = '';
        $offset   = 0;

        while ($offset < $count) {
            $rows = DB::connection('tenant')
                ->table($tableName)
                ->offset($offset)
                ->limit(self::INSERT_CHUNK)
                ->get();

            if ($rows->isEmpty()) {
                break;
            }

            if ($columns === null) {
                $columns  = array_keys((array) $rows->first());
                $colsList = '`' . implode('`, `', $columns) . '`';
            }

            $valueLines = $rows->map(function ($row) use ($pdo) {
                $vals = array_map(
                    fn($v) => $v === null ? 'NULL' : $pdo->quote((string) $v),
                    (array) $row
                );
                return '(' . implode(', ', $vals) . ')';
            });

            $out .= "INSERT INTO `{$tableName}` ({$colsList}) VALUES\n";
            $out .= $valueLines->implode(",\n") . ";\n";

            $offset += self::INSERT_CHUNK;
        }

        $out .= "\n";
        return $out;
    }
}

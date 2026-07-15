<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Master\TenantBackup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ArsipBackupController extends Controller
{
    public function index()
    {
        $backups = TenantBackup::with('tenant')
                               ->orderByDesc('tgl_backup')
                               ->orderByDesc('id')
                               ->get()
                               ->map(function (TenantBackup $backup) {
                                   $backup->sql_exists = $backup->sql_path
                                       && Storage::disk('local')->exists($backup->sql_path);
                                   $backup->zip_exists = $backup->storage_zip_path
                                       && Storage::disk('local')->exists($backup->storage_zip_path);
                                   return $backup;
                               });

        return view('superadmin.arsip-backup', compact('backups'));
    }

    public function destroy(TenantBackup $backup): RedirectResponse
    {
        // Hapus file dari disk — jangan error kalau sudah tidak ada
        if ($backup->sql_path) {
            Storage::disk('local')->delete($backup->sql_path);
        }
        if ($backup->storage_zip_path) {
            Storage::disk('local')->delete($backup->storage_zip_path);
        }

        $label = $backup->nama_gym ?? "Backup #{$backup->id}";
        $backup->delete();

        return redirect()
            ->route('super_admin.arsip_backup')
            ->with('success', "Arsip \"{$label}\" berhasil dihapus.");
    }
}

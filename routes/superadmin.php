<?php

use App\Http\Controllers\SuperAdmin\AktivasiController;
use App\Http\Controllers\SuperAdmin\ArsipBackupController;
use App\Http\Controllers\SuperAdmin\BackupController;
use App\Http\Controllers\SuperAdmin\ConfigDatabaseController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\KelolaTenantController;
use App\Http\Controllers\SuperAdmin\PaketController;
use App\Http\Controllers\SuperAdmin\ReaktivasiController;
use App\Http\Controllers\SuperAdmin\SuperAdminAuthController;
use Illuminate\Support\Facades\Route;

// Guest: belum login sebagai super admin → tampilkan login
Route::middleware('guest:super_admin')->group(function () {
    Route::get('/login', [SuperAdminAuthController::class, 'showLogin'])
        ->name('super_admin.login');
    Route::post('/login', [SuperAdminAuthController::class, 'login'])
        ->name('super_admin.login.post');
});

// Auth: sudah login sebagai super admin → akses panel
Route::middleware('auth:super_admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->name('super_admin.dashboard');
    Route::post('/logout', [SuperAdminAuthController::class, 'logout'])
        ->name('super_admin.logout');

    // Backup tenant + nonaktifkan
    Route::post('/tenant/{tenant}/backup', [BackupController::class, 'create'])
        ->name('super_admin.backup.create');
    Route::get('/backup/{backup}/download/{type}', [BackupController::class, 'download'])
        ->name('super_admin.backup.download')
        ->where('type', 'sql|zip');

    // Kelola Tenant
    Route::get('/kelola-tenant', [KelolaTenantController::class, 'index'])
        ->name('super_admin.kelola_tenant');
    Route::get('/kelola-tenant/{tenant}', [KelolaTenantController::class, 'show'])
        ->name('super_admin.kelola_tenant.show');
    Route::put('/kelola-tenant/{tenant}', [KelolaTenantController::class, 'update'])
        ->name('super_admin.kelola_tenant.update');
    Route::patch('/kelola-tenant/{tenant}/modules', [KelolaTenantController::class, 'updateModules'])
        ->name('super_admin.kelola_tenant.update_modules');

    // Aktifkan Kembali (tenant archived → aktif lagi)
    Route::get('/kelola-tenant/{tenant}/reaktivasi', [ReaktivasiController::class, 'create'])
        ->name('super_admin.reaktivasi');
    Route::post('/kelola-tenant/{tenant}/reaktivasi', [ReaktivasiController::class, 'store'])
        ->name('super_admin.reaktivasi.store');

    // Aktivasi Gym (tenant baru)
    Route::get('/aktivasi', [AktivasiController::class, 'create'])
        ->name('super_admin.aktivasi');
    Route::post('/aktivasi', [AktivasiController::class, 'store'])
        ->name('super_admin.aktivasi.store');

    // Arsip Backup
    Route::get('/arsip-backup', [ArsipBackupController::class, 'index'])
        ->name('super_admin.arsip_backup');
    Route::delete('/arsip-backup/{backup}', [ArsipBackupController::class, 'destroy'])
        ->name('super_admin.arsip_backup.destroy');

    // Paket (read-only reference)
    Route::get('/paket', [PaketController::class, 'index'])->name('super_admin.paket');

    // Config Database (pool management)
    Route::get('/config-database', [ConfigDatabaseController::class, 'index'])
        ->name('super_admin.config_database');
    Route::post('/config-database', [ConfigDatabaseController::class, 'store'])
        ->name('super_admin.config_database.store');
    Route::post('/config-database/{pool}/clear', [ConfigDatabaseController::class, 'clear'])
        ->name('super_admin.config_database.clear');
});

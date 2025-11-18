<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GateController;

/*
|--------------------------------------------------------------------------
| API Routes untuk Gate System
|--------------------------------------------------------------------------
| 
| Tambahkan routes ini ke file routes/api.php
|
*/

// Ping endpoint untuk test koneksi
Route::get('/gate/ping', [GateController::class, 'ping']);

// Check member by RFID
Route::post('/gate/check-member', [GateController::class, 'checkMember']);

// Main absensi endpoint
Route::post('/gate', [GateController::class, 'absen']);

/*
|--------------------------------------------------------------------------
| Jika ingin menambahkan authentication (opsional)
|--------------------------------------------------------------------------
|
| Uncomment code di bawah jika ingin menggunakan Sanctum/Bearer Token
|

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/gate/absen', [GateController::class, 'absen']);
    Route::post('/gate/check-member', [GateController::class, 'checkMember']);
});

*/
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AbsenNotifController extends Controller
{
    /**
     * Polling endpoint — hanya untuk admin/spv
     * Return data absen terbaru jika timestamp-nya lebih baru dari 'since'
     */
    public function latest(Request $request)
    {
        $since   = (int) $request->query('since', 0);
        $payload = Cache::get('absen_notif_latest');

        if (!$payload || $payload['timestamp'] <= $since) {
            return response()->json(['has_new' => false]);
        }

        return response()->json([
            'has_new' => true,
            'data'    => $payload,
        ]);
    }
}

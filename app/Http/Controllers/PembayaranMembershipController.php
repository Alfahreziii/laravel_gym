<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnggotaMembership;
use App\Models\PembayaranMembership;

class PembayaranMembershipController extends Controller
{
    public function index()
    {
        $anggotaMemberships = AnggotaMembership::with(['anggota', 'paketMembership', 'pembayaranMemberships'])->latest()->get();
        return view('pages.pembayaranmembership.index', compact('anggotaMemberships'));
    }

    public function tambahPembayaran(Request $request, $id)
    {
        $request->validate([
            'tgl_bayar'        => 'required|date',
            'jumlah_bayar'     => 'required|numeric|min:0',
            'metode_pembayaran'=> 'required|string',
        ]);

        $anggotaMembership = AnggotaMembership::findOrFail($id);

        PembayaranMembership::create([
            'id_anggota_membership' => $anggotaMembership->id,
            'tgl_bayar'             => $request->tgl_bayar,
            'jumlah_bayar'          => $request->jumlah_bayar,
            'metode_pembayaran'     => $request->metode_pembayaran,
        ]);

        // update status otomatis
        $totalDibayar = $anggotaMembership->pembayaranMemberships()->sum('jumlah_bayar');
        $anggotaMembership->status_pembayaran = $totalDibayar >= $anggotaMembership->total_biaya ? 'Lunas' : 'Belum Lunas';
        $anggotaMembership->save();

        return redirect()->route('pembayaran_membership.index')
            ->with('success', 'Pembayaran baru berhasil ditambahkan.');
    }
}

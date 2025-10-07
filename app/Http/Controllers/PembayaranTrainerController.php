<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MemberTrainer;
use App\Models\PembayaranMemberTrainer;

class PembayaranTrainerController extends Controller
{
    public function index()
    {
        $memberTrainers = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'trainer', 'pembayaranMemberTrainers'])
            ->latest()->get();
        return view('pages.pembayarantrainer.index', compact('memberTrainers'));
    }

    public function detail_pembayaran(Request $request, $id)
    {
        $memberTrainers = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'trainer', 'pembayaranMemberTrainers'])
            ->latest()->findOrFail($id);
        return view('pages.pembayarantrainer.index', compact('memberTrainers'));
    }

    public function tambahPembayaran(Request $request, $id)
    {
        $request->validate([
            'tgl_bayar'         => 'required|date',
            'jumlah_bayar'      => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|string',
        ]);

        $memberTrainer = MemberTrainer::findOrFail($id);

        PembayaranMemberTrainer::create([
            'id_member_trainer' => $memberTrainer->id,
            'tgl_bayar'         => $request->tgl_bayar,
            'jumlah_bayar'      => $request->jumlah_bayar,
            'metode_pembayaran' => $request->metode_pembayaran,
        ]);

        // update status otomatis
        $totalDibayar = $memberTrainer->pembayaranMemberTrainers()->sum('jumlah_bayar');
        $memberTrainer->status_pembayaran = $totalDibayar >= $memberTrainer->total_biaya ? 'Lunas' : 'Belum Lunas';
        $memberTrainer->save();

        return redirect()->route('pembayaran_trainer.index')
            ->with('success', 'Pembayaran baru berhasil ditambahkan.');
    }
}

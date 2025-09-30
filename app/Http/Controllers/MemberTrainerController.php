<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MemberTrainer;
use App\Models\Anggota;
use App\Models\PaketPersonalTrainer;
use App\Models\Trainer;
use App\Models\PembayaranMemberTrainer;

class MemberTrainerController extends Controller
{
    public function index()
    {
        $memberTrainers = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'trainer', 'pembayaranMemberTrainers'])
            ->latest()->get();
        return view('pages.membertrainer.index', compact('memberTrainers'));
    }

    public function create()
    {
        $anggotas = Anggota::all();
        $trainers = Trainer::all();
        $pakets = PaketPersonalTrainer::all();
        return view('pages.membertrainer.create', compact('anggotas', 'pakets', 'trainers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_anggota'               => 'required|exists:anggotas,id',
            'id_paket_personal_trainer'=> 'required|exists:paket_personal_trainers,id',
            'id_trainer'               => 'required|exists:trainers,id',
            'diskon'                   => 'nullable|numeric|min:0',
            'total_biaya'              => 'required|numeric|min:0',
            // validasi pembayaran pertama
            'tgl_bayar'                => 'required|date',
            'jumlah_bayar'             => 'required|numeric|min:0',
            'metode_pembayaran'        => 'required|string',
        ]);

        $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(uniqid());

        // 1️⃣ simpan member trainer
        $memberTrainer = MemberTrainer::create([
            'kode_transaksi'         => $kodeTransaksi,
            'id_anggota'             => $request->id_anggota,
            'id_paket_personal_trainer' => $request->id_paket_personal_trainer,
            'id_trainer'             => $request->id_trainer,
            'diskon'                 => $request->diskon,
            'total_biaya'            => $request->total_biaya,
            'status_pembayaran'      => $request->jumlah_bayar >= $request->total_biaya ? 'Lunas' : 'Belum Lunas',
        ]);

        // 2️⃣ simpan pembayaran pertama
        PembayaranMemberTrainer::create([
            'id_member_trainer'   => $memberTrainer->id,
            'tgl_bayar'           => $request->tgl_bayar,
            'jumlah_bayar'        => $request->jumlah_bayar,
            'metode_pembayaran'   => $request->metode_pembayaran,
        ]);

        return redirect()->route('membertrainer.index')
            ->with('success', 'Data member trainer berhasil ditambahkan beserta pembayaran pertama.');
    }

    public function show($id)
    {
        $memberTrainer = MemberTrainer::with('pembayaranMemberTrainers')->findOrFail($id);
        return view('pages.membertrainer.show', compact('memberTrainer'));
    }

    public function edit($id)
    {
        $memberTrainer = MemberTrainer::with('pembayaranMemberTrainers')->findOrFail($id);
        $anggotas = Anggota::all();
        $pakets = PaketPersonalTrainer::all();
        $trainers = Trainer::all();

        return view('pages.membertrainer.edit', compact('memberTrainer', 'anggotas', 'pakets', 'trainers'));
    }

    public function update(Request $request, $id)
    {
        $memberTrainer = MemberTrainer::findOrFail($id);

        $request->validate([
            'id_anggota'               => 'required|exists:anggotas,id',
            'id_paket_personal_trainer'=> 'required|exists:paket_personal_trainers,id',
            'id_trainer'               => 'required|exists:trainers,id',
            'diskon'                   => 'nullable|numeric|min:0',
            'total_biaya'              => 'required|numeric|min:0',
        ]);

        $memberTrainer->update($request->only([
            'id_anggota',
            'id_paket_personal_trainer',
            'id_trainer',
            'diskon',
            'total_biaya',
        ]));

        return redirect()->route('membertrainer.index')
            ->with('success', 'Data member trainer berhasil diperbarui.');
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

        return redirect()->route('membertrainer.edit', $id)
            ->with('success', 'Pembayaran baru berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $memberTrainer = MemberTrainer::findOrFail($id);
        $memberTrainer->delete();

        return redirect()->route('membertrainer.index')->with('success', 'Data member trainer berhasil dihapus.');
    }

    public function destroyPembayaran($id)
    {
        $pembayaran = PembayaranMemberTrainer::findOrFail($id);
        $memberTrainer = $pembayaran->memberTrainer;

        $pembayaran->delete();

        // update status setelah hapus
        $totalDibayar = $memberTrainer->pembayaranMemberTrainers()->sum('jumlah_bayar');
        $memberTrainer->status_pembayaran = $totalDibayar >= $memberTrainer->total_biaya ? 'Lunas' : 'Belum Lunas';
        $memberTrainer->save();

        return redirect()->route('membertrainer.edit', $memberTrainer->id)
            ->with('success', 'Pembayaran berhasil dihapus.');
    }
}
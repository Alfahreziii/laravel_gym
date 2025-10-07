<?php

namespace App\Http\Controllers;

use App\Models\AnggotaMembership;
use App\Models\PembayaranMembership;
use App\Models\Anggota;
use App\Models\PaketMembership;
use Illuminate\Http\Request;

class AnggotaMembershipController extends Controller
{
    public function index()
    {
        $anggotaMemberships = AnggotaMembership::with(['anggota', 'paketMembership'])->latest()->get();
        return view('pages.anggotapaketmember.index', compact('anggotaMemberships'));
    }

    public function create()
    {
        $anggotas = Anggota::all();
        $pakets = PaketMembership::all();
        return view('pages.anggotapaketmember.create', compact('anggotas', 'pakets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_anggota'          => 'required|exists:anggotas,id',
            'id_paket_membership' => 'required|exists:paket_memberships,id',
            'tgl_mulai'           => 'required|date',
            'tgl_selesai'         => 'required|date',
            'diskon'              => 'nullable|numeric|min:0',
            'total_biaya'         => 'required|numeric|min:0',
            // validasi pembayaran pertama
            'tgl_bayar'           => 'required|date',
            'jumlah_bayar'        => 'required|numeric|min:0',
            'metode_pembayaran'   => 'required|string',
        ]);

        $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(uniqid());

        // 1️⃣ simpan membership dulu
        $anggotaMembership = AnggotaMembership::create([
            'kode_transaksi'      => $kodeTransaksi,
            'id_anggota'          => $request->id_anggota,
            'id_paket_membership' => $request->id_paket_membership,
            'tgl_mulai'           => $request->tgl_mulai,
            'tgl_selesai'         => $request->tgl_selesai,
            'diskon'              => $request->diskon,
            'total_biaya'         => $request->total_biaya,
            'status_pembayaran'   => $request->jumlah_bayar >= $request->total_biaya ? 'Lunas' : 'Belum Lunas',
        ]);

        // 2️⃣ simpan pembayaran pertama
        PembayaranMembership::create([
            'id_anggota_membership' => $anggotaMembership->id,
            'tgl_bayar'             => $request->tgl_bayar,
            'jumlah_bayar'          => $request->jumlah_bayar,
            'metode_pembayaran'     => $request->metode_pembayaran,
        ]);

        return redirect()->route('anggota_membership.index')
            ->with('success', 'Data anggota membership berhasil ditambahkan beserta pembayaran pertama.');
    }

    public function show($id)
    {
        $anggotaMembership = AnggotaMembership::with('pembayaranMemberships')->findOrFail($id);
        return view('pages.anggotapaketmember.show', compact('anggotaMembership'));
    }
    /**
     * Form edit data
     */
    public function edit($id)
    {
        $anggotaMembership = AnggotaMembership::with('pembayaranMemberships')->findOrFail($id);
        $anggotas = Anggota::all();
        $pakets = PaketMembership::all();
        $paketMemberships = PaketMembership::all();

        return view('pages.anggotapaketmember.edit', compact('anggotaMembership', 'anggotas', 'paketMemberships', 'pakets'));
    }


    public function update(Request $request, $id)
    {
        $anggotaMembership = AnggotaMembership::findOrFail($id);

        // jika update info membership
        $request->validate([
            'id_anggota'          => 'required|exists:anggotas,id',
            'id_paket_membership' => 'required|exists:paket_memberships,id',
            'tgl_mulai'           => 'required|date',
            'tgl_selesai'         => 'required|date|after_or_equal:tgl_mulai',
            'diskon'              => 'nullable|numeric|min:0',
            'total_biaya'         => 'required|numeric|min:0',
        ]);

        $anggotaMembership->update($request->only([
            'id_anggota',
            'id_paket_membership',
            'tgl_mulai',
            'tgl_selesai',
            'diskon',
            'total_biaya',
        ]));

        return redirect()->route('anggota_membership.index')
            ->with('success', 'Data membership berhasil diperbarui.');
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

        return redirect()->route('anggota_membership.edit', $id)
            ->with('success', 'Pembayaran baru berhasil ditambahkan.');
    }

    /**
     * Hapus data
     */
    public function destroy($id)
    {
        $anggotaMembership = AnggotaMembership::findOrFail($id);
        $anggotaMembership->delete();

        return redirect()->route('anggota_membership.index')->with('success', 'Data anggota membership berhasil dihapus.');
    }

    /**
     * Hapus data pembayaran
     */
    public function destroyPembayaran($id)
    {
        $pembayaran = PembayaranMembership::findOrFail($id);
        $anggotaMembership = $pembayaran->anggotaMembership; // relasi balik

        $pembayaran->delete();

        // update status setelah hapus
        $totalDibayar = $anggotaMembership->pembayaranMemberships()->sum('jumlah_bayar');
        $anggotaMembership->status_pembayaran = $totalDibayar >= $anggotaMembership->total_biaya ? 'Lunas' : 'Belum Lunas';
        $anggotaMembership->save();

        return redirect()->route('anggota_membership.edit', $anggotaMembership->id)
            ->with('success', 'Pembayaran berhasil dihapus.');
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\AnggotaMembership;
use App\Models\Anggota;
use App\Models\PaketMembership;
use Illuminate\Http\Request;

class AnggotaMembershipController extends Controller
{
    /**
     * Tampilkan semua data anggota membership
     */
    public function index()
    {
        $anggotaMemberships = AnggotaMembership::with(['anggota', 'paketMembership'])->latest()->get();
        return view('pages.anggotapaketmember.index', compact('anggotaMemberships'));
    }

    /**
     * Form tambah data baru
     */
    public function create()
    {
        $anggotas = Anggota::all();
        $pakets = PaketMembership::all();
        return view('pages.anggotapaketmember.create', compact('anggotas', 'pakets'));
    }

    /**
     * Simpan data baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_anggota'          => 'required|exists:anggotas,id',
            'id_paket_membership' => 'required|exists:paket_memberships,id',
            'tgl_mulai'           => 'required|date',
            'tgl_selesai'         => 'required|date',
            'diskon'              => 'nullable|numeric|min:0',
            'total_biaya'         => 'required|numeric|min:0',
            'metode_pembayaran'   => 'required|string',
            'tgl_bayar'           => 'nullable|date',
            'total_dibayarkan'    => 'nullable|numeric|min:0',
            'status_pembayaran'   => 'required|string',
        ]);

        // Kode transaksi tetap dibuat otomatis di backend
        $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(uniqid());

        AnggotaMembership::create([
            'kode_transaksi'      => $kodeTransaksi,
            'id_anggota'          => $request->id_anggota,
            'id_paket_membership' => $request->id_paket_membership,
            'tgl_mulai'           => $request->tgl_mulai,
            'tgl_selesai'         => $request->tgl_selesai,
            'diskon'              => $request->diskon,
            'total_biaya'         => $request->total_biaya,
            'metode_pembayaran'   => $request->metode_pembayaran,
            'tgl_bayar'           => $request->tgl_bayar,
            'total_dibayarkan'    => $request->total_dibayarkan,
            'status_pembayaran'   => $request->status_pembayaran,
        ]);

        return redirect()->route('anggota_membership.index')
            ->with('success', 'Data anggota membership berhasil ditambahkan.');
    }


    /**
     * Form edit data
     */
    public function edit($id)
    {
        $anggotaMembership = AnggotaMembership::findOrFail($id);
        $anggotas = Anggota::all();
        $pakets = PaketMembership::all();
        $paketMemberships = PaketMembership::all();

        return view('pages.anggotapaketmember.edit', compact('anggotaMembership', 'anggotas', 'paketMemberships', 'pakets'));
    }

    /**
     * Update data
     */
    public function update(Request $request, $id)
    {
        $anggotaMembership = AnggotaMembership::findOrFail($id);

        $request->validate([
            'id_anggota'          => 'required|exists:anggotas,id',
            'id_paket_membership' => 'required|exists:paket_memberships,id',
            'tgl_mulai'           => 'required|date',
            'tgl_selesai'         => 'required|date|after_or_equal:tgl_mulai',
            'diskon'              => 'nullable|numeric|min:0',
            'total_biaya'         => 'required|numeric|min:0',
            'metode_pembayaran'   => 'required|string',
            'status_pembayaran'   => 'required|string',
            'tgl_bayar'           => 'nullable|date',
            'total_dibayarkan'    => 'nullable|numeric|min:0',
        ]);

        // update data tanpa sentuh kode_transaksi
        $anggotaMembership->update($request->except('kode_transaksi'));

        return redirect()->route('anggota_membership.index')
            ->with('success', 'Data anggota membership berhasil diperbarui.');
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
}

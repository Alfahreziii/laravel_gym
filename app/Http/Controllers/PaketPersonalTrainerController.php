<?php

namespace App\Http\Controllers;

use App\Models\PaketPersonalTrainer;
use Illuminate\Http\Request;

class PaketPersonalTrainerController extends Controller
{
    /**
     * Tampilkan semua paket personal trainer
     */
    public function index()
    {
        $paketPersonalTrainers = PaketPersonalTrainer::all();
        return view('pages.pakettrainer.index', compact('paketPersonalTrainers'));
    }

    /**
     * Tampilkan form untuk membuat paket baru
     */
    public function create()
    {
        return view('pages.pakettrainer.create');
    }

    /**
     * Simpan paket baru ke database
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_paket' => 'required|string|max:255',
                'jumlah_sesi' => 'required|integer|min:1',
                'durasi' => 'required|integer',
                'periode' => 'required|string|max:50',
                'biaya' => 'required|numeric|min:0',
            ]);

            PaketPersonalTrainer::create($request->all());

            return redirect()->route('paket_personal_trainer.index')
                            ->with('success', 'Paket personal trainer berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('danger', 'Gagal menambahkan paket: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Tampilkan form edit paket
     */
    public function edit(PaketPersonalTrainer $paket_personal_trainer)
    {
        return view('pages.pakettrainer.edit', compact('paket_personal_trainer'));
    }

    /**
     * Update data paket
     */
    public function update(Request $request, PaketPersonalTrainer $paket_personal_trainer)
    {
        try {
            $request->validate([
                'nama_paket' => 'required|string|max:255',
                'jumlah_sesi' => 'required|integer|min:1',
                'durasi' => 'required|integer',
                'periode' => 'required|string|max:50',
                'biaya' => 'required|numeric|min:0',
            ]);

            $paket_personal_trainer->update($request->all());

            return redirect()->route('paket_personal_trainer.index')
                            ->with('success', 'Paket personal trainer berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('danger', 'Gagal memperbarui paket: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Hapus paket
     */
    public function destroy(PaketPersonalTrainer $paket_personal_trainer)
    {
        try {
            $paket_personal_trainer->delete();

            return redirect()->route('paket_personal_trainer.index')
                            ->with('success', 'Paket personal trainer berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('danger', 'Gagal menghapus paket: ' . $e->getMessage());
        }
    }
}
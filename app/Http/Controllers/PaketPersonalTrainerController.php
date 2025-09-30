<?php

namespace App\Http\Controllers;

use App\Models\PaketPersonalTrainer;
use Illuminate\Http\Request;

class PaketPersonalTrainerController extends Controller
{
    /**
     * Tampilkan daftar paket personal trainer
     */
    public function index()
    {
        $paketPersonalTrainers = PaketPersonalTrainer::latest()->get();
        return view('pages.pakettrainer.index', compact('paketPersonalTrainers'));
    }

    /**
     * Form tambah data
     */
    public function create()
    {
        return view('pages.pakettrainer.create');
    }

    /**
     * Simpan data baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'jumlah_sesi' => 'required|integer|min:1',
            'durasi' => 'required|string|max:100',
            'biaya' => 'required|numeric|min:0',
        ]);

        PaketPersonalTrainer::create($request->all());

        return redirect()->route('paket_personal_trainer.index')
            ->with('success', 'Paket Personal Trainer berhasil ditambahkan.');
    }

    /**
     * Form edit data
     */
    public function edit($id)
    {
        $paketPersonalTrainer = PaketPersonalTrainer::findOrFail($id);
        return view('pages.pakettrainer.edit', compact('paketPersonalTrainer'));
    }

    /**
     * Update data
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'jumlah_sesi' => 'required|integer|min:1',
            'durasi' => 'required|string|max:100',
            'biaya' => 'required|numeric|min:0',
        ]);

        $paketPersonalTrainer = PaketPersonalTrainer::findOrFail($id);
        $paketPersonalTrainer->update($request->all());

        return redirect()->route('paket_personal_trainer.index')
            ->with('success', 'Paket Personal Trainer berhasil diperbarui.');
    }

    /**
     * Hapus data
     */
    public function destroy($id)
    {
        $paketPersonalTrainer = PaketPersonalTrainer::findOrFail($id);
        $paketPersonalTrainer->delete();

        return redirect()->route('paket_personal_trainer.index')
            ->with('success', 'Paket Personal Trainer berhasil dihapus.');
    }
}

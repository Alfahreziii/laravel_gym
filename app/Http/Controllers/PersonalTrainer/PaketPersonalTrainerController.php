<?php

namespace App\Http\Controllers\PersonalTrainer;

use App\Http\Controllers\Controller;

use App\Models\PaketPersonalTrainer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaketPersonalTrainerController extends Controller
{
    public function datatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = PaketPersonalTrainer::latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_paket', 'like', "%{$search}%")
                  ->orWhere('periode', 'like', "%{$search}%");
            });
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage) {
                return [
                    'no'          => (($page - 1) * $perPage) + $index + 1,
                    'id'          => $item->id,
                    'nama_paket'  => $item->nama_paket,
                    'durasi'      => $item->durasi,
                    'periode'     => ucfirst($item->periode),
                    'jumlah_sesi' => $item->jumlah_sesi ?? '-',
                    'biaya'       => 'Rp ' . number_format($item->biaya, 0, ',', '.'),
                    'edit_url'    => route('paket_personal_trainer.edit', $item->id),
                    'delete_url'  => route('paket_personal_trainer.destroy', $item->id),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    /**
     * Tampilkan semua paket personal trainer
     */
    public function index()
    {
        $paketPersonalTrainers = PaketPersonalTrainer::all();
        return view('pages.admin.personal-trainer.paket-trainer.index', compact('paketPersonalTrainers'));
    }

    /**
     * Tampilkan form untuk membuat paket baru
     */
    public function create()
    {
        return view('pages.admin.personal-trainer.paket-trainer.create');
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
            Log::error('Gagal menambahkan paket personal trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                            ->with('danger', 'Gagal menambahkan paket. Silakan coba lagi atau hubungi admin.')
                            ->withInput();
        }
    }

    /**
     * Tampilkan form edit paket
     */
    public function edit(PaketPersonalTrainer $paket_personal_trainer)
    {
        return view('pages.admin.personal-trainer.paket-trainer.edit', compact('paket_personal_trainer'));
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
            Log::error('Gagal memperbarui paket personal trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                            ->with('danger', 'Gagal memperbarui paket. Silakan coba lagi atau hubungi admin.')
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
            Log::error('Gagal menghapus paket personal trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                            ->with('danger', 'Gagal menghapus paket. Silakan coba lagi atau hubungi admin.');
        }
    }
}
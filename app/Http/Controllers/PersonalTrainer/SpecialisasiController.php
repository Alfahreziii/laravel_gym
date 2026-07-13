<?php

namespace App\Http\Controllers\PersonalTrainer;

use App\Http\Controllers\Controller;

use App\Models\Specialisasi;
use Illuminate\Http\Request;
use Exception;

class SpecialisasiController extends Controller
{
    /**
     * Tampilkan semua data specialisasi
     */
    public function index()
    {
        $specialisasis = Specialisasi::all();
        return view('pages.admin.personal-trainer.specialisasi.index', compact('specialisasis'));
    }

    public function datatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = Specialisasi::query();
        if ($search) {
            $query->where('nama_specialisasi', 'like', "%{$search}%");
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->orderBy('nama_specialisasi')->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage) {
                return [
                    'no'                => (($page - 1) * $perPage) + $index + 1,
                    'id'                => $item->id,
                    'nama_specialisasi' => $item->nama_specialisasi,
                    'update_url'        => route('specialisasi.update', $item->id),
                    'delete_url'        => route('specialisasi.destroy', $item->id),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    /**
     * Simpan specialisasi baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_specialisasi' => 'required|string|max:255',
        ]);

        try {
            Specialisasi::create([
                'nama_specialisasi' => $request->nama_specialisasi,
            ]);

            return redirect()->route('specialisasi.index')
                            ->with('success', 'Specialisasi berhasil ditambahkan.');
        } catch (Exception $e) {
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Gagal menambahkan specialisasi: ' . $e->getMessage());
        }
    }

    /**
     * Update specialisasi
     */
    public function update(Request $request, Specialisasi $specialisasi)
    {
        $request->validate([
            'nama_specialisasi' => 'required|string|max:255',
        ]);

        try {
            $specialisasi->update([
                'nama_specialisasi' => $request->nama_specialisasi,
            ]);

            return redirect()->route('specialisasi.index')
                            ->with('success', 'Specialisasi berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Gagal memperbarui specialisasi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus specialisasi
     */
    public function destroy(Specialisasi $specialisasi)
    {
        try {
            $specialisasi->delete();

            return redirect()->route('specialisasi.index')
                            ->with('success', 'Specialisasi berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()->back()
                            ->with('error', 'Gagal menghapus specialisasi: ' . $e->getMessage());
        }
    }
}

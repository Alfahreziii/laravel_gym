<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;

use App\Models\KategoriPaketMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KategoriPaketController extends Controller
{
    /**
     * Tampilkan semua kategori paket membership
     */
    public function index()
    {
        $kategori_paket_memberships = KategoriPaketMembership::all();
        return view('pages.admin.membership.kategori-paket-member.index', compact('kategori_paket_memberships'));
    }

    public function datatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = KategoriPaketMembership::query();

        if ($search) {
            $query->where('nama_kategori', 'like', "%{$search}%");
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->orderBy('nama_kategori')->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage) {
                return [
                    'no'             => (($page - 1) * $perPage) + $index + 1,
                    'id'             => $item->id,
                    'nama_kategori'  => $item->nama_kategori,
                    'update_url'     => route('kategori_paket_membership.update', $item->id),
                    'delete_url'     => route('kategori_paket_membership.destroy', $item->id),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    /**
     * Simpan kategori baru ke database
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_kategori' => 'required|string|max:255|unique:kategori_paket_memberships,nama_kategori',
            ]);

            KategoriPaketMembership::create([
                'nama_kategori' => $request->nama_kategori,
            ]);

            return redirect()->route('kategori_paket_membership.index')->with('success', 'Kategori berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan kategori paket membership', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                            ->with('danger', 'Gagal menambahkan kategori. Silakan coba lagi atau hubungi admin.')
                            ->withInput();
        }
    }
    /**
     * Update kategori
     */
    public function update(Request $request, KategoriPaketMembership $kategori_paket_membership)
    {
        try {
            $request->validate([
                'nama_kategori' => 'required|string|max:255|unique:kategori_paket_memberships,nama_kategori,' . $kategori_paket_membership->id,
            ]);

            $kategori_paket_membership->update([
                'nama_kategori' => $request->nama_kategori,
            ]);

            return redirect()->route('kategori_paket_membership.index')->with('success', 'Kategori berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui kategori paket membership', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                            ->with('danger', 'Gagal memperbarui kategori. Silakan coba lagi atau hubungi admin.')
                            ->withInput();
        }
    }

    /**
     * Hapus kategori
     */
    public function destroy(KategoriPaketMembership $kategori_paket_membership)
    {
        try {
            $kategori_paket_membership->delete();

            return redirect()->route('kategori_paket_membership.index')
                            ->with('success', 'Kategori berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus kategori paket membership', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                            ->with('danger', 'Gagal menghapus kategori. Silakan coba lagi atau hubungi admin.');
        }
    }
}

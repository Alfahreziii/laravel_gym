<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;

use App\Models\PaketMembership;
use App\Models\KategoriPaketMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaketMembershipController extends Controller
{
    public function datatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = PaketMembership::with('kategori')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_paket', 'like', "%{$search}%")
                  ->orWhere('periode', 'like', "%{$search}%")
                  ->orWhereHas('kategori', fn($q2) => $q2->where('nama_kategori', 'like', "%{$search}%"));
            });
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage) {
                return [
                    'no'            => (($page - 1) * $perPage) + $index + 1,
                    'id'            => $item->id,
                    'nama_kategori' => $item->kategori?->nama_kategori ?? '-',
                    'nama_paket'    => $item->nama_paket,
                    'durasi'        => $item->durasi,
                    'periode'       => ucfirst($item->periode),
                    'harga'         => 'Rp ' . number_format($item->harga, 0, ',', '.'),
                    'keterangan'    => $item->keterangan ?? '-',
                    'edit_url'      => route('paket_membership.edit', $item->id),
                    'delete_url'    => route('paket_membership.destroy', $item->id),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    /**
     * Tampilkan semua paket membership
     */
    public function index()
    {
        // ambil semua paket beserta kategori
        $paketMemberships = PaketMembership::with('kategori')->get();
        return view('pages.admin.membership.paket-membership.index', compact('paketMemberships'));
    }

    /**
     * Tampilkan form untuk membuat paket baru
     */
    public function create()
    {
        $kategoriPaket = KategoriPaketMembership::all();
        return view('pages.admin.membership.paket-membership.create', compact('kategoriPaket'));
    }

    /**
     * Simpan paket baru ke database
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_kategori' => 'required|exists:kategori_paket_memberships,id',
                'nama_paket' => 'required|string|max:255',
                'durasi' => 'required|integer',
                'periode' => 'required|string|max:50',
                'harga' => 'required|numeric',
                'keterangan' => 'nullable|string',
            ]);

            PaketMembership::create($request->all());

            return redirect()->route('paket_membership.index')
                            ->with('success', 'Paket membership berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan paket membership', [
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
    public function edit(PaketMembership $paket_membership)
    {
        $kategori = KategoriPaketMembership::all();
        return view('pages.admin.membership.paket-membership.edit', compact('paket_membership', 'kategori'));
    }

    /**
     * Update data paket
     */
    public function update(Request $request, PaketMembership $paket_membership)
    {
        try {
            $request->validate([
                'id_kategori' => 'required|exists:kategori_paket_memberships,id',
                'nama_paket' => 'required|string|max:255',
                'durasi' => 'required|integer',
                'periode' => 'required|string|max:50',
                'harga' => 'required|numeric',
                'keterangan' => 'nullable|string',
            ]);

            $paket_membership->update($request->all());

            return redirect()->route('paket_membership.index')
                            ->with('success', 'Paket membership berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui paket membership', [
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
    public function destroy(PaketMembership $paket_membership)
    {
        try {
            $paket_membership->delete();

            return redirect()->route('paket_membership.index')
                            ->with('success', 'Paket membership berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus paket membership', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                            ->with('danger', 'Gagal menghapus paket. Silakan coba lagi atau hubungi admin.');
        }
    }
}

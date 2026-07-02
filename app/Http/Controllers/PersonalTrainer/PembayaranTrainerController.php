<?php

namespace App\Http\Controllers\PersonalTrainer;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\MemberTrainer;
use App\Models\PembayaranMemberTrainer;
use Barryvdh\DomPDF\Facade\Pdf;

class PembayaranTrainerController extends Controller
{
    public function index()
    {
        $memberTrainers = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'trainer', 'pembayaranMemberTrainers'])
            ->latest()->get();
        return view('pages.admin.personal-trainer.pembayaran-trainer.index', compact('memberTrainers'));
    }

    public function datatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'pembayaranMemberTrainers'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('anggota', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $total   = (clone $query)->count();
        $data    = (clone $query)->skip(($page - 1) * $perPage)->take($perPage)->get();
        $isAdmin = (bool) auth()->user()?->hasRole('admin');

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage, $isAdmin) {
                $totalDibayarkan = $item->pembayaranMemberTrainers->sum('jumlah_bayar');
                $sisaTagihan     = $item->total_biaya - $totalDibayarkan;
                $isLunas         = $item->status_pembayaran === 'Lunas';
                return [
                    'no'                => (($page - 1) * $perPage) + $index + 1,
                    'id'                => $item->id,
                    'kode_transaksi'    => $item->kode_transaksi,
                    'edit_url'          => route('membertrainer.edit', $item->id),
                    'anggota_name'      => $item->anggota->name ?? '-',
                    'paket_nama'        => $item->paketPersonalTrainer->nama_paket ?? '-',
                    'harga'             => $item->paketPersonalTrainer->biaya ?? 0,
                    'diskon'            => $item->diskon,
                    'total_biaya'       => $item->total_biaya,
                    'total_dibayarkan'  => $totalDibayarkan,
                    'sisa_tagihan'      => $sisaTagihan,
                    'status_pembayaran' => $item->status_pembayaran,
                    'is_lunas'          => $isLunas,
                    'nota_url'          => $isLunas ? route('pembayaran_trainer.notaPDF', $item->id) : null,
                    'bayar_url'         => $isAdmin ? route('pembayaran_trainer.tambahPembayaran', $item->id) : null,
                    'is_admin'          => $isAdmin,
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    public function detail_pembayaran(Request $request, $id)
    {
        $memberTrainers = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'trainer', 'pembayaranMemberTrainers'])
            ->latest()->findOrFail($id);
        return view('pages.admin.personal-trainer.pembayaran-trainer.index', compact('memberTrainers'));
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

    /**
     * Export nota transaksi personal trainer ke PDF
     */
    public function exportNotaPDF($id)
    {
        $memberTrainer = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'trainer', 'pembayaranMemberTrainers'])
            ->findOrFail($id);

        // Pastikan statusnya lunas
        if ($memberTrainer->status_pembayaran !== 'Lunas') {
            return redirect()->back()->with('danger', 'Nota hanya dapat dicetak untuk transaksi yang sudah lunas.');
        }

        $data = [
            'transaksi' => $memberTrainer,
            'anggota' => $memberTrainer->anggota,
            'paket' => $memberTrainer->paketPersonalTrainer,
            'trainer' => $memberTrainer->trainer,
            'pembayaran' => $memberTrainer->pembayaranMemberTrainers,
            'totalDibayar' => $memberTrainer->pembayaranMemberTrainers->sum('jumlah_bayar'),
        ];

        $pdf = Pdf::loadView('pages.admin.personal-trainer.pembayaran-trainer.nota-pdf', $data);
        
        return $pdf->download('Nota-Trainer-' . $memberTrainer->kode_transaksi . '.pdf');
    }
}
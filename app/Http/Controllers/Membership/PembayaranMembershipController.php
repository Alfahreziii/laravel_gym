<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\AnggotaMembership;
use App\Models\PembayaranMembership;
use App\Models\AkunKeuangan;
use App\Models\TransaksiKeuangan;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class PembayaranMembershipController extends Controller
{
    public function index()
    {
        $anggotaMemberships = AnggotaMembership::with(['anggota', 'paketMembership', 'pembayaranMemberships'])
            ->latest()
            ->get();

        return view('pages.admin.membership.pembayaran-membership.index', compact('anggotaMemberships'));
    }

    public function datatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = AnggotaMembership::with(['anggota', 'paketMembership', 'pembayaranMemberships'])->latest();

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
                $totalDibayarkan = $item->pembayaranMemberships->sum('jumlah_bayar');
                $sisaTagihan     = $item->total_biaya - $totalDibayarkan;
                $isLunas         = $item->status_pembayaran === 'Lunas';
                return [
                    'no'                => (($page - 1) * $perPage) + $index + 1,
                    'id'                => $item->id,
                    'kode_transaksi'    => $item->kode_transaksi,
                    'edit_url'          => route('anggota_membership.edit', $item->id),
                    'anggota_name'      => $item->anggota->name ?? '-',
                    'paket_nama'        => $item->paketMembership->nama_paket ?? '-',
                    'harga'             => $item->paketMembership->harga ?? 0,
                    'diskon'            => $item->diskon,
                    'total_biaya'       => $item->total_biaya,
                    'total_dibayarkan'  => $totalDibayarkan,
                    'sisa_tagihan'      => $sisaTagihan,
                    'status_pembayaran' => $item->status_pembayaran,
                    'is_lunas'          => $isLunas,
                    'nota_url'          => $isLunas ? route('pembayaran_membership.notaPDF', $item->id) : null,
                    'bayar_url'         => $isAdmin ? route('pembayaran_membership.tambahPembayaran', $item->id) : null,
                    'is_admin'          => $isAdmin,
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
    }

    public function tambahPembayaran(Request $request, $id)
    {
        $request->validate([
            'tgl_bayar'        => 'required|date',
            'jumlah_bayar'     => 'required|numeric|min:0',
            'metode_pembayaran'=> 'required|string',
        ]);

        $anggotaMembership = AnggotaMembership::findOrFail($id);

        // Simpan pembayaran baru
        $pembayaran = PembayaranMembership::create([
            'id_anggota_membership' => $anggotaMembership->id,
            'tgl_bayar'             => $request->tgl_bayar,
            'jumlah_bayar'          => $request->jumlah_bayar,
            'metode_pembayaran'     => $request->metode_pembayaran,
        ]);

        // Ambil akun-akun penting berdasarkan KODE dari seeder
        $akunKas = AkunKeuangan::where('kode', 'AST001')->first(); // Kas
        $akunPiutang = AkunKeuangan::where('kode', 'AST002')->first(); // Piutang
        $akunPendapatan = AkunKeuangan::where('kode', 'MOD003')->first(); // Pendapatan Membership

        if (! $akunKas || ! $akunPiutang || ! $akunPendapatan) {
            Log::warning('Akun keuangan tidak lengkap untuk transaksi otomatis.', [
                'akunKas' => $akunKas?->toArray(),
                'akunPiutang' => $akunPiutang?->toArray(),
                'akunPendapatan' => $akunPendapatan?->toArray(),
            ]);
        } else {
            try {
                // Hitung total pembayaran sejauh ini (setelah transaksi baru)
                $totalDibayar = $anggotaMembership->pembayaranMemberships()->sum('jumlah_bayar');
                $totalTagihan = $anggotaMembership->total_biaya;

                // --- Debit Kas ---
                TransaksiKeuangan::create([
                    'akun_id' => $akunKas->id,
                    'deskripsi' => 'Penerimaan pembayaran membership dari ' . ($anggotaMembership->anggota->nama ?? 'Member'),
                    'debit' => $request->jumlah_bayar,
                    'kredit' => 0,
                    'tanggal' => $request->tgl_bayar,
                    'referensi_id' => $pembayaran->id,
                    'referensi_tabel' => 'pembayaran_memberships',
                ]);

                // --- Kredit Pendapatan atau Piutang ---
                if ($totalDibayar >= $totalTagihan) {
                    // Sudah lunas → pendapatan
                    TransaksiKeuangan::create([
                        'akun_id' => $akunPendapatan->id,
                        'deskripsi' => 'Pendapatan membership dari ' . ($anggotaMembership->anggota->nama ?? 'Member'),
                        'debit' => 0,
                        'kredit' => $request->jumlah_bayar,
                        'tanggal' => $request->tgl_bayar,
                        'referensi_id' => $pembayaran->id,
                        'referensi_tabel' => 'pembayaran_memberships',
                    ]);
                } else {
                    // Belum lunas → piutang
                    TransaksiKeuangan::create([
                        'akun_id' => $akunPiutang->id,
                        'deskripsi' => 'Piutang dari ' . ($anggotaMembership->anggota->nama ?? 'Member'),
                        'debit' => 0,
                        'kredit' => $request->jumlah_bayar,
                        'tanggal' => $request->tgl_bayar,
                        'referensi_id' => $pembayaran->id,
                        'referensi_tabel' => 'pembayaran_memberships',
                    ]);
                }

            } catch (\Throwable $e) {
                Log::error('Gagal mencatat transaksi ke TransaksiKeuangan: ' . $e->getMessage(), [
                    'pembayaran_id' => $pembayaran->id,
                    'jumlah' => $request->jumlah_bayar,
                ]);
            }
        }

        // Update status pembayaran anggota
        $totalDibayar = $anggotaMembership->pembayaranMemberships()->sum('jumlah_bayar');
        $anggotaMembership->status_pembayaran = $totalDibayar >= $anggotaMembership->total_biaya
            ? 'Lunas'
            : 'Belum Lunas';
        $anggotaMembership->save();

        return redirect()->route('pembayaran_membership.index')
            ->with('success', 'Pembayaran baru berhasil ditambahkan dan dicatat dalam transaksi keuangan.');
    }

    /**
     * Export nota transaksi membership ke PDF
     */
    public function exportNotaPDF($id)
    {
        $anggotaMembership = AnggotaMembership::with(['anggota', 'paketMembership', 'pembayaranMemberships'])
            ->findOrFail($id);

        // Pastikan statusnya lunas
        if ($anggotaMembership->status_pembayaran !== 'Lunas') {
            return redirect()->back()->with('danger', 'Nota hanya dapat dicetak untuk transaksi yang sudah lunas.');
        }

        $data = [
            'transaksi' => $anggotaMembership,
            'anggota' => $anggotaMembership->anggota,
            'paket' => $anggotaMembership->paketMembership,
            'pembayaran' => $anggotaMembership->pembayaranMemberships,
            'totalDibayar' => $anggotaMembership->pembayaranMemberships->sum('jumlah_bayar'),
            'tenant' => app('tenant'),
        ];

        $pdf = Pdf::loadView('pages.admin.membership.pembayaran-membership.nota-pdf', $data);
        
        return $pdf->download('Nota-' . $anggotaMembership->kode_transaksi . '.pdf');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnggotaMembership;
use App\Models\PembayaranMembership;
use App\Models\AkunKeuangan;
use App\Models\TransaksiKeuangan;
use Illuminate\Support\Facades\Log;

class PembayaranMembershipController extends Controller
{
    public function index()
    {
        $anggotaMemberships = AnggotaMembership::with(['anggota', 'paketMembership', 'pembayaranMemberships'])
            ->latest()
            ->get();

        return view('pages.pembayaranmembership.index', compact('anggotaMemberships'));
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
}

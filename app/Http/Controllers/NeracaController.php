<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriAkun;
use App\Models\AkunKeuangan;
use App\Models\TransaksiKeuangan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NeracaController extends Controller
{
    public function index()
    {
        // Ambil semua kategori beserta akun & transaksi (AST, KEW, MOD, BEB)
        $kategori = KategoriAkun::with(['akun.transaksi'])->get();

        // Hitung saldo per akun sesuai normal balance per kategori:
        // - AST & BEB: saldo = debit - kredit
        // - KEW & MOD: saldo = kredit - debit
        $kategori->each(function ($kat) {
            $kat->akun->each(function ($akun) use ($kat) {
                $debit  = $akun->transaksi->sum('debit');
                $kredit = $akun->transaksi->sum('kredit');

                if (in_array($kat->kode, ['AST', 'BEB'])) {
                    // Aset & Beban bertambah di DEBIT
                    $akun->saldo = $debit - $kredit;
                } else {
                    // Kewajiban & Modal bertambah di KREDIT
                    $akun->saldo = $kredit - $debit;
                }
            });
        });

        // Hitung total per kelompok
        $total_aset       = $kategori->where('kode', 'AST')->first()?->akun->sum('saldo') ?? 0;
        $total_kewajiban  = $kategori->where('kode', 'KEW')->first()?->akun->sum('saldo') ?? 0;
        $total_modal      = $kategori->where('kode', 'MOD')->first()?->akun->sum('saldo') ?? 0;
        $total_beban      = $kategori->where('kode', 'BEB')->first()?->akun->sum('saldo') ?? 0;

        // Rumus neraca: ASET = KEW + MOD − BEB
        $total_kewajiban_modal = $total_kewajiban + $total_modal - $total_beban;

        return view('pages.neraca.index', compact(
            'kategori',
            'total_aset',
            'total_kewajiban',
            'total_modal',
            'total_beban',
            'total_kewajiban_modal'
        ));
    }

    /**
     * Tambah kas manual (setoran modal awal atau penambahan kas lainnya)
     * 
     * Jurnal:
     * Debit:  Kas (AST001)           = jumlah
     * Kredit: Modal Pemilik (MOD001) = jumlah
     */
    public function tambahKas(Request $request)
    {
        $validated = $request->validate([
            'jumlah' => 'required|numeric|min:0.01',
            'deskripsi' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $akunKas = AkunKeuangan::where('kode', 'AST001')->first(); // Kas
            $akunModal = AkunKeuangan::where('kode', 'MOD001')->first(); // Modal Pemilik

            if (!$akunKas) {
                throw new \Exception('Akun Kas (AST001) tidak ditemukan.');
            }
            if (!$akunModal) {
                throw new \Exception('Akun Modal Pemilik (MOD001) tidak ditemukan.');
            }

            $jumlah = floatval($validated['jumlah']);
            $deskripsi = $validated['deskripsi'];
            $tanggal = now()->format('Y-m-d');

            // Debit Kas (aset bertambah)
            TransaksiKeuangan::create([
                'akun_id' => $akunKas->id,
                'deskripsi' => $deskripsi,
                'debit' => $jumlah,
                'kredit' => 0,
                'tanggal' => $tanggal,
                'referensi_id' => null,
                'referensi_tabel' => 'manual_kas',
            ]);

            // Kredit Modal Pemilik (modal bertambah)
            TransaksiKeuangan::create([
                'akun_id' => $akunModal->id,
                'deskripsi' => $deskripsi,
                'debit' => 0,
                'kredit' => $jumlah,
                'tanggal' => $tanggal,
                'referensi_id' => null,
                'referensi_tabel' => 'manual_kas',
            ]);

            DB::commit();

            Log::info('Penambahan kas manual berhasil', [
                'jumlah' => $jumlah,
                'deskripsi' => $deskripsi,
            ]);

            return redirect()->route('neraca.index')
                ->with('success', 'Kas berhasil ditambahkan sebesar Rp ' . number_format($jumlah, 2, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambah kas manual', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->with('danger', 'Gagal menambahkan kas: ' . $e->getMessage())
                ->withInput();
        }
    }
}
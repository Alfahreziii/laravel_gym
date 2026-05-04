<?php

namespace App\Http\Controllers;

use App\Models\AnggotaMembership;
use App\Models\PembayaranMembership;
use App\Models\Anggota;
use App\Models\PaketMembership;
use App\Models\AkunKeuangan;
use App\Models\TransaksiKeuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AnggotaMembershipController extends Controller
{
    public function exportPdf(Request $request)
    {
        $request->validate([
            'status_filter' => 'required|in:all,lunas,belum_lunas',
            'filter_type'   => 'required|in:all,single,range',
            'bulan'         => 'nullable|required_if:filter_type,single|integer|between:1,12',
            'tahun'         => 'nullable|required_if:filter_type,single|integer|min:2000',
            'bulan_dari'    => 'nullable|required_if:filter_type,range|integer|between:1,12',
            'tahun_dari'    => 'nullable|required_if:filter_type,range|integer|min:2000',
            'bulan_sampai'  => 'nullable|required_if:filter_type,range|integer|between:1,12',
            'tahun_sampai'  => 'nullable|required_if:filter_type,range|integer|min:2000',
        ]);

        try {
            $statusFilter = $request->status_filter;
            $filterType   = $request->filter_type;

            // Hitung statistik dari SEMUA data (tidak terfilter)
            $allMemberships = AnggotaMembership::with(['anggota', 'paketMembership', 'pembayaranMemberships'])->get();

            $totalMembership  = $allMemberships->count();
            $totalLunas       = $allMemberships->where('status_pembayaran', 'Lunas')->count();
            $totalBelumLunas  = $allMemberships->where('status_pembayaran', 'Belum Lunas')->count();

            $totalPendapatan = $allMemberships->sum('total_biaya');
            $totalTerbayar   = $allMemberships->sum(function ($item) {
                return $item->pembayaranMemberships->sum('jumlah_bayar');
            });
            $totalPiutang = $totalPendapatan - $totalTerbayar;

            // Query untuk data yang akan ditampilkan
            $query = AnggotaMembership::with(['anggota', 'paketMembership', 'pembayaranMemberships']);

            if ($statusFilter === 'lunas') {
                $query->where('status_pembayaran', 'Lunas');
            } elseif ($statusFilter === 'belum_lunas') {
                $query->where('status_pembayaran', 'Belum Lunas');
            }

            $filterInfo = '';
            if ($filterType === 'single') {
                $bulan = $request->bulan;
                $tahun = $request->tahun;

                $query->whereYear('tgl_mulai', $tahun)
                    ->whereMonth('tgl_mulai', $bulan);

                $filterInfo = Carbon::create($tahun, $bulan)->locale('id')->isoFormat('MMMM YYYY');
            } elseif ($filterType === 'range') {
                $dariTanggal   = Carbon::create($request->tahun_dari, $request->bulan_dari, 1)->startOfMonth();
                $sampaiTanggal = Carbon::create($request->tahun_sampai, $request->bulan_sampai, 1)->endOfMonth();

                $query->whereBetween('tgl_mulai', [$dariTanggal, $sampaiTanggal]);

                $filterInfo = $dariTanggal->locale('id')->isoFormat('MMMM YYYY') . ' - ' .
                    $sampaiTanggal->locale('id')->isoFormat('MMMM YYYY');
            } else {
                $filterInfo = 'Semua Periode';
            }

            $statusInfo = match ($statusFilter) {
                'lunas'       => 'Lunas',
                'belum_lunas' => 'Belum Lunas',
                default       => 'Semua Status',
            };

            $anggotaMemberships = $query->orderBy('tgl_mulai', 'desc')->get();

            $title = 'Laporan Anggota Membership';
            if ($statusFilter !== 'all' || $filterType !== 'all') {
                $title .= ' - ';
                if ($statusFilter !== 'all') {
                    $title .= $statusInfo;
                }
                if ($filterType !== 'all') {
                    $title .= ($statusFilter !== 'all' ? ' - ' : '') . $filterInfo;
                }
            }

            $pdf = Pdf::loadView('pages.anggotapaketmember.pdf', compact(
                'anggotaMemberships',
                'totalMembership',
                'totalLunas',
                'totalBelumLunas',
                'totalPendapatan',
                'totalTerbayar',
                'totalPiutang',
                'title',
                'statusInfo',
                'filterInfo',
                'statusFilter',
                'filterType'
            ));

            $pdf->setPaper('a4', 'landscape');

            $filename = 'Laporan_Membership_' . ucfirst($statusFilter) . '_' . date('Y-m-d_His') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Gagal export PDF membership', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('danger', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $anggotaMemberships = AnggotaMembership::with(['anggota', 'paketMembership'])->latest()->get();
        return view('pages.anggotapaketmember.index', compact('anggotaMemberships'));
    }

    public function create()
    {
        $anggotas = Anggota::all();
        $pakets   = PaketMembership::all();
        return view('pages.anggotapaketmember.create', compact('anggotas', 'pakets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_anggota'          => 'required|exists:anggotas,id',
            'id_paket_membership' => 'required|exists:paket_memberships,id',
            'tgl_mulai'           => 'required|date',
            'tgl_selesai'         => 'required|date|after_or_equal:tgl_mulai',
            'diskon'              => 'nullable|numeric|min:0',
            'total_biaya'         => 'required|numeric|min:0',
            'tgl_bayar'           => 'required|date',
            'jumlah_bayar'        => 'required|numeric|min:0',
            'metode_pembayaran'   => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(uniqid());

            // 1️⃣ Simpan membership
            $anggotaMembership = AnggotaMembership::create([
                'kode_transaksi'      => $kodeTransaksi,
                'id_anggota'          => $request->id_anggota,
                'id_paket_membership' => $request->id_paket_membership,
                'tgl_mulai'           => $request->tgl_mulai,
                'tgl_selesai'         => $request->tgl_selesai,
                'diskon'              => $request->diskon ?? 0,
                'total_biaya'         => $request->total_biaya,
                'status_pembayaran'   => $request->jumlah_bayar >= $request->total_biaya ? 'Lunas' : 'Belum Lunas',
            ]);

            // 2️⃣ Catat piutang awal
            // FIX: kirim tgl_bayar agar tanggal piutang = min(tgl_mulai, tgl_bayar)
            // Ini mencegah bug di mana Kas dilunasi sebelum Piutang timbul
            $this->createPiutangAwalMembership($anggotaMembership, $request->tgl_bayar);

            // 3️⃣ Simpan pembayaran pertama
            $pembayaran = PembayaranMembership::create([
                'id_anggota_membership' => $anggotaMembership->id,
                'tgl_bayar'             => $request->tgl_bayar,
                'jumlah_bayar'          => $request->jumlah_bayar,
                'metode_pembayaran'     => $request->metode_pembayaran,
            ]);

            // 4️⃣ Catat transaksi keuangan pembayaran
            $this->createTransaksiKeuanganForPembayaran(
                $pembayaran,
                $anggotaMembership,
                $request->jumlah_bayar,
                $request->tgl_bayar,
                'Pembayaran awal membership'
            );

            DB::commit();

            return redirect()->route('anggota_membership.index')
                ->with('success', 'Data anggota membership berhasil ditambahkan beserta pembayaran pertama.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan membership', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data membership: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $anggotaMembership = AnggotaMembership::with('pembayaranMemberships.anggotaMembership')->findOrFail($id);
        return view('pages.anggotapaketmember.show', compact('anggotaMembership'));
    }

    public function edit($id)
    {
        $anggotaMembership = AnggotaMembership::with('pembayaranMemberships')->findOrFail($id);
        $anggotas          = Anggota::all();
        $pakets            = PaketMembership::all();
        return view('pages.anggotapaketmember.edit', compact('anggotaMembership', 'anggotas', 'pakets'));
    }

    public function update(Request $request, $id)
    {
        $anggotaMembership = AnggotaMembership::findOrFail($id);

        $request->validate([
            'id_anggota'          => 'required|exists:anggotas,id',
            'id_paket_membership' => 'required|exists:paket_memberships,id',
            'tgl_mulai'           => 'required|date',
            'tgl_selesai'         => 'required|date|after_or_equal:tgl_mulai',
            'diskon'              => 'nullable|numeric|min:0',
            'total_biaya'         => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalBiayaLama = $anggotaMembership->total_biaya;
            $totalBiayaBaru = $request->total_biaya;

            $anggotaMembership->update($request->only([
                'id_anggota',
                'id_paket_membership',
                'tgl_mulai',
                'tgl_selesai',
                'diskon',
                'total_biaya',
            ]));

            if ($totalBiayaLama != $totalBiayaBaru) {
                $this->updatePiutangDanPendapatan($anggotaMembership, $totalBiayaLama, $totalBiayaBaru);
            }

            $totalDibayar = $anggotaMembership->pembayaranMemberships()->sum('jumlah_bayar');
            $anggotaMembership->status_pembayaran = $totalDibayar >= $anggotaMembership->total_biaya
                ? 'Lunas'
                : 'Belum Lunas';
            $anggotaMembership->save();

            DB::commit();

            return redirect()->route('anggota_membership.index')
                ->with('success', 'Data membership berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update membership', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal update membership: ' . $e->getMessage());
        }
    }

    public function tambahPembayaran(Request $request, $id)
    {
        $request->validate([
            'tgl_bayar'         => 'required|date',
            'jumlah_bayar'      => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $anggotaMembership = AnggotaMembership::findOrFail($id);

            $totalDibayar = $anggotaMembership->pembayaranMemberships()->sum('jumlah_bayar');
            $sisaTagihan  = $anggotaMembership->total_biaya - $totalDibayar;

            if ($request->jumlah_bayar > $sisaTagihan) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Jumlah pembayaran (Rp ' . number_format($request->jumlah_bayar, 0, ',', '.') .
                        ') melebihi sisa tagihan (Rp ' . number_format($sisaTagihan, 0, ',', '.') . ')');
            }

            $pembayaran = PembayaranMembership::create([
                'id_anggota_membership' => $anggotaMembership->id,
                'tgl_bayar'             => $request->tgl_bayar,
                'jumlah_bayar'          => $request->jumlah_bayar,
                'metode_pembayaran'     => $request->metode_pembayaran,
            ]);

            $this->createTransaksiKeuanganForPembayaran(
                $pembayaran,
                $anggotaMembership,
                $request->jumlah_bayar,
                $request->tgl_bayar,
                'Pembayaran membership'
            );

            $totalDibayarBaru = $anggotaMembership->pembayaranMemberships()->sum('jumlah_bayar');
            $anggotaMembership->status_pembayaran = $totalDibayarBaru >= $anggotaMembership->total_biaya
                ? 'Lunas'
                : 'Belum Lunas';
            $anggotaMembership->save();

            DB::commit();

            return redirect()->route('anggota_membership.edit', $id)
                ->with('success', 'Pembayaran baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal tambah pembayaran', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Gagal menambah pembayaran: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $anggotaMembership = AnggotaMembership::findOrFail($id);

            foreach ($anggotaMembership->pembayaranMemberships as $pembayaran) {
                TransaksiKeuangan::where('referensi_id', $pembayaran->id)
                    ->where('referensi_tabel', 'pembayaran_memberships')
                    ->delete();
                $pembayaran->delete();
            }

            TransaksiKeuangan::where('referensi_id', $anggotaMembership->id)
                ->where('referensi_tabel', 'anggota_memberships')
                ->delete();

            $anggotaMembership->delete();

            DB::commit();

            return redirect()->route('anggota_membership.index')
                ->with('success', 'Data anggota membership berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus membership', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Gagal menghapus membership: ' . $e->getMessage());
        }
    }

    public function destroyPembayaran($id)
    {
        DB::beginTransaction();
        try {
            $pembayaran        = PembayaranMembership::findOrFail($id);
            $anggotaMembership = $pembayaran->anggotaMembership;

            TransaksiKeuangan::where('referensi_id', $pembayaran->id)
                ->where('referensi_tabel', 'pembayaran_memberships')
                ->delete();

            $pembayaran->delete();

            $totalDibayar = $anggotaMembership->pembayaranMemberships()->sum('jumlah_bayar');
            $anggotaMembership->status_pembayaran = $totalDibayar >= $anggotaMembership->total_biaya
                ? 'Lunas'
                : 'Belum Lunas';
            $anggotaMembership->save();

            DB::commit();

            return redirect()->route('anggota_membership.edit', $anggotaMembership->id)
                ->with('success', 'Pembayaran berhasil dihapus dan transaksi keuangan diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus pembayaran', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Gagal menghapus pembayaran: ' . $e->getMessage());
        }
    }

    public function updatePembayaran(Request $request, $id)
    {
        $request->validate([
            'tgl_bayar'         => 'required|date',
            'jumlah_bayar'      => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $pembayaran        = PembayaranMembership::findOrFail($id);
            $anggotaMembership = $pembayaran->anggotaMembership;

            $totalDibayarLain = $anggotaMembership->pembayaranMemberships()
                ->where('id', '!=', $pembayaran->id)
                ->sum('jumlah_bayar');

            $sisaTagihan = $anggotaMembership->total_biaya - $totalDibayarLain;

            if ($request->jumlah_bayar > $sisaTagihan) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Jumlah pembayaran (Rp ' . number_format($request->jumlah_bayar, 0, ',', '.') .
                        ') melebihi sisa tagihan (Rp ' . number_format($sisaTagihan, 0, ',', '.') . ')');
            }

            $pembayaran->update([
                'tgl_bayar'         => $request->tgl_bayar,
                'jumlah_bayar'      => $request->jumlah_bayar,
                'metode_pembayaran' => $request->metode_pembayaran,
            ]);

            // Hapus transaksi lama lalu buat ulang
            TransaksiKeuangan::where('referensi_id', $pembayaran->id)
                ->where('referensi_tabel', 'pembayaran_memberships')
                ->delete();

            $this->createTransaksiKeuanganForPembayaran(
                $pembayaran,
                $anggotaMembership,
                $request->jumlah_bayar,
                $request->tgl_bayar,
                'Edit pembayaran membership'
            );

            $totalDibayar = $anggotaMembership->pembayaranMemberships()->sum('jumlah_bayar');
            $anggotaMembership->status_pembayaran = $totalDibayar >= $anggotaMembership->total_biaya
                ? 'Lunas'
                : 'Belum Lunas';
            $anggotaMembership->save();

            DB::commit();

            return redirect()->route('anggota_membership.edit', $anggotaMembership->id)
                ->with('success', 'Pembayaran berhasil diperbarui dan transaksi keuangan diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update pembayaran', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Gagal update pembayaran: ' . $e->getMessage());
        }
    }

    // =====================================================
    // FUNGSI HELPER UNTUK TRANSAKSI KEUANGAN
    // =====================================================

    /**
     * Catat piutang awal dan pendapatan saat membership dibuat.
     *
     * FIX — Tanggal piutang pakai: min(tgl_mulai, tgl_bayar)
     * -------------------------------------------------------
     * Masalah sebelumnya: tanggal piutang selalu pakai tgl_mulai.
     * Jika tgl_bayar < tgl_mulai (misal bayar April, mulai Mei),
     * maka di laporan April ada Kas masuk tapi Piutang belum timbul
     * → neraca tidak balance di bulan April.
     *
     * Solusi: ambil tanggal yang paling awal antara keduanya,
     * sehingga Piutang selalu timbul SEBELUM atau BERSAMAAN
     * dengan pelunasannya.
     *
     * @param  AnggotaMembership  $anggotaMembership
     * @param  string|null        $tglBayarPertama   tanggal dari form (tgl_bayar)
     */
    protected function createPiutangAwalMembership($anggotaMembership, $tglBayarPertama = null)
    {
        $akunPendapatan = AkunKeuangan::where('kode', 'MOD003')->first(); // Pendapatan Membership
        $akunPiutang    = AkunKeuangan::where('kode', 'AST002')->first(); // Piutang Usaha

        if (!$akunPendapatan || !$akunPiutang) {
            Log::error('Akun keuangan tidak ditemukan untuk pencatatan piutang membership', [
                'anggota_membership_id' => $anggotaMembership->id,
            ]);
            throw new \Exception('Konfigurasi akun keuangan tidak lengkap. Pastikan akun MOD003 dan AST002 tersedia.');
        }

        // Hindari duplikasi jika fungsi dipanggil lebih dari sekali
        $sudahDicatat = TransaksiKeuangan::where('referensi_id', $anggotaMembership->id)
            ->where('referensi_tabel', 'anggota_memberships')
            ->where('akun_id', $akunPiutang->id)
            ->exists();

        if ($sudahDicatat) {
            Log::warning('Piutang membership sudah pernah dicatat', [
                'anggota_membership_id' => $anggotaMembership->id,
            ]);
            return;
        }

        // ✅ FIX: ambil tanggal paling awal antara tgl_mulai dan tgl_bayar
        $tglMulai       = Carbon::parse($anggotaMembership->tgl_mulai);
        $tanggalPiutang = $tglMulai;

        if ($tglBayarPertama) {
            $tglBayar = Carbon::parse($tglBayarPertama);
            // Jika bayar lebih awal dari mulai, piutang timbul di tanggal bayar
            // supaya neraca tetap balance di bulan pembayaran
            if ($tglBayar->lt($tglMulai)) {
                $tanggalPiutang = $tglBayar;
            }
        }

        $totalBiaya  = $anggotaMembership->total_biaya;
        $namaAnggota = $anggotaMembership->anggota->nama ?? 'Member';

        // DEBIT: Piutang Usaha (Aset bertambah)
        TransaksiKeuangan::create([
            'akun_id'         => $akunPiutang->id,
            'deskripsi'       => "Piutang membership dari {$namaAnggota}",
            'debit'           => $totalBiaya,
            'kredit'          => 0,
            'tanggal'         => $tanggalPiutang,
            'referensi_id'    => $anggotaMembership->id,
            'referensi_tabel' => 'anggota_memberships',
        ]);

        // KREDIT: Pendapatan Membership
        TransaksiKeuangan::create([
            'akun_id'         => $akunPendapatan->id,
            'deskripsi'       => "Pendapatan membership dari {$namaAnggota}",
            'debit'           => 0,
            'kredit'          => $totalBiaya,
            'tanggal'         => $tanggalPiutang,
            'referensi_id'    => $anggotaMembership->id,
            'referensi_tabel' => 'anggota_memberships',
        ]);

        Log::info('Piutang dan pendapatan membership berhasil dicatat', [
            'anggota_membership_id' => $anggotaMembership->id,
            'tanggal_piutang'       => $tanggalPiutang->toDateString(),
            'tgl_mulai'             => $tglMulai->toDateString(),
            'tgl_bayar_pertama'     => $tglBayarPertama,
            'jumlah'                => $totalBiaya,
        ]);
    }

    /**
     * Catat transaksi keuangan untuk setiap pembayaran.
     * Jurnal: Debit Kas (AST001), Kredit Piutang Usaha (AST002)
     */
    protected function createTransaksiKeuanganForPembayaran(
        $pembayaran,
        $anggotaMembership,
        $jumlah,
        $tanggal,
        $keteranganPrefix = 'Pembayaran membership'
    ) {
        $akunKas    = AkunKeuangan::where('kode', 'AST001')->first(); // Kas
        $akunPiutang = AkunKeuangan::where('kode', 'AST002')->first(); // Piutang Usaha

        if (!$akunKas || !$akunPiutang) {
            Log::error('Akun keuangan tidak ditemukan untuk pembayaran membership', [
                'pembayaran_id' => $pembayaran->id,
            ]);
            throw new \Exception('Konfigurasi akun keuangan tidak lengkap. Pastikan akun AST001 dan AST002 tersedia.');
        }

        $namaAnggota      = $anggotaMembership->anggota->nama ?? 'Member';
        $metodePembayaran = $pembayaran->metode_pembayaran ?? 'Tunai';

        // DEBIT: Kas (bertambah karena menerima uang)
        TransaksiKeuangan::create([
            'akun_id'         => $akunKas->id,
            'deskripsi'       => "{$keteranganPrefix} dari {$namaAnggota} via {$metodePembayaran}",
            'debit'           => $jumlah,
            'kredit'          => 0,
            'tanggal'         => $tanggal,
            'referensi_id'    => $pembayaran->id,
            'referensi_tabel' => 'pembayaran_memberships',
        ]);

        // KREDIT: Piutang Usaha (berkurang karena dilunasi)
        TransaksiKeuangan::create([
            'akun_id'         => $akunPiutang->id,
            'deskripsi'       => "{$keteranganPrefix} dari {$namaAnggota}",
            'debit'           => 0,
            'kredit'          => $jumlah,
            'tanggal'         => $tanggal,
            'referensi_id'    => $pembayaran->id,
            'referensi_tabel' => 'pembayaran_memberships',
        ]);

        Log::info('Pembayaran membership berhasil dicatat', [
            'pembayaran_id'         => $pembayaran->id,
            'anggota_membership_id' => $anggotaMembership->id,
            'metode'                => $metodePembayaran,
            'jumlah'                => $jumlah,
        ]);
    }

    /**
     * Update transaksi piutang dan pendapatan saat total biaya membership berubah.
     * Mencatat jurnal penyesuaian (selisih) tanpa mengubah jurnal awal.
     */
    protected function updatePiutangDanPendapatan($anggotaMembership, $totalBiayaLama, $totalBiayaBaru)
    {
        $akunPendapatan = AkunKeuangan::where('kode', 'MOD003')->first();
        $akunPiutang    = AkunKeuangan::where('kode', 'AST002')->first();

        if (!$akunPendapatan || !$akunPiutang) {
            Log::error('Akun keuangan tidak ditemukan untuk update piutang');
            throw new \Exception('Konfigurasi akun keuangan tidak lengkap');
        }

        $selisih     = $totalBiayaBaru - $totalBiayaLama;
        $namaAnggota = $anggotaMembership->anggota->nama ?? 'Member';

        if ($selisih > 0) {
            // Total biaya naik → tambah piutang dan pendapatan
            TransaksiKeuangan::create([
                'akun_id'         => $akunPiutang->id,
                'deskripsi'       => "Penyesuaian piutang membership {$namaAnggota} (naik)",
                'debit'           => $selisih,
                'kredit'          => 0,
                'tanggal'         => now(),
                'referensi_id'    => $anggotaMembership->id,
                'referensi_tabel' => 'anggota_memberships',
            ]);

            TransaksiKeuangan::create([
                'akun_id'         => $akunPendapatan->id,
                'deskripsi'       => "Penyesuaian pendapatan membership {$namaAnggota} (naik)",
                'debit'           => 0,
                'kredit'          => $selisih,
                'tanggal'         => now(),
                'referensi_id'    => $anggotaMembership->id,
                'referensi_tabel' => 'anggota_memberships',
            ]);
        } elseif ($selisih < 0) {
            // Total biaya turun → kurangi piutang dan pendapatan
            $selisihAbs = abs($selisih);

            TransaksiKeuangan::create([
                'akun_id'         => $akunPiutang->id,
                'deskripsi'       => "Penyesuaian piutang membership {$namaAnggota} (turun)",
                'debit'           => 0,
                'kredit'          => $selisihAbs,
                'tanggal'         => now(),
                'referensi_id'    => $anggotaMembership->id,
                'referensi_tabel' => 'anggota_memberships',
            ]);

            TransaksiKeuangan::create([
                'akun_id'         => $akunPendapatan->id,
                'deskripsi'       => "Penyesuaian pendapatan membership {$namaAnggota} (turun)",
                'debit'           => $selisihAbs,
                'kredit'          => 0,
                'tanggal'         => now(),
                'referensi_id'    => $anggotaMembership->id,
                'referensi_tabel' => 'anggota_memberships',
            ]);
        }

        Log::info('Piutang dan pendapatan berhasil disesuaikan', [
            'anggota_membership_id' => $anggotaMembership->id,
            'total_biaya_lama'      => $totalBiayaLama,
            'total_biaya_baru'      => $totalBiayaBaru,
            'selisih'               => $selisih,
        ]);
    }
}

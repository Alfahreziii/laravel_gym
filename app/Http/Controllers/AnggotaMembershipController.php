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
            'filter_type' => 'required|in:all,single,range',
            'bulan' => 'nullable|required_if:filter_type,single|integer|between:1,12',
            'tahun' => 'nullable|required_if:filter_type,single|integer|min:2000',
            'bulan_dari' => 'nullable|required_if:filter_type,range|integer|between:1,12',
            'tahun_dari' => 'nullable|required_if:filter_type,range|integer|min:2000',
            'bulan_sampai' => 'nullable|required_if:filter_type,range|integer|between:1,12',
            'tahun_sampai' => 'nullable|required_if:filter_type,range|integer|min:2000',
        ]);

        try {
            $statusFilter = $request->status_filter;
            $filterType = $request->filter_type;
            
            // Hitung statistik dari SEMUA data (tidak terfilter)
            $allMemberships = AnggotaMembership::with(['anggota', 'paketMembership', 'pembayaranMemberships'])->get();
            
            $totalMembership = $allMemberships->count();
            $totalLunas = $allMemberships->where('status_pembayaran', 'Lunas')->count();
            $totalBelumLunas = $allMemberships->where('status_pembayaran', 'Belum Lunas')->count();
            
            // Hitung total keuangan keseluruhan
            $totalPendapatan = $allMemberships->sum('total_biaya');
            $totalTerbayar = $allMemberships->sum(function($item) {
                return $item->pembayaranMemberships->sum('jumlah_bayar');
            });
            $totalPiutang = $totalPendapatan - $totalTerbayar;
            
            // Query untuk data yang akan ditampilkan
            $query = AnggotaMembership::with(['anggota', 'paketMembership', 'pembayaranMemberships']);
            
            // Filter berdasarkan status pembayaran
            if ($statusFilter === 'lunas') {
                $query->where('status_pembayaran', 'Lunas');
            } elseif ($statusFilter === 'belum_lunas') {
                $query->where('status_pembayaran', 'Belum Lunas');
            }
            
            // Filter berdasarkan tanggal
            $filterInfo = '';
            if ($filterType === 'single') {
                $bulan = $request->bulan;
                $tahun = $request->tahun;
                
                $query->whereYear('tgl_mulai', $tahun)
                    ->whereMonth('tgl_mulai', $bulan);
                
                $filterInfo = Carbon::create($tahun, $bulan)->locale('id')->isoFormat('MMMM YYYY');
            } elseif ($filterType === 'range') {
                $dariTanggal = Carbon::create($request->tahun_dari, $request->bulan_dari, 1)->startOfMonth();
                $sampaiTanggal = Carbon::create($request->tahun_sampai, $request->bulan_sampai, 1)->endOfMonth();
                
                $query->whereBetween('tgl_mulai', [$dariTanggal, $sampaiTanggal]);
                
                $filterInfo = $dariTanggal->locale('id')->isoFormat('MMMM YYYY') . ' - ' . 
                            $sampaiTanggal->locale('id')->isoFormat('MMMM YYYY');
            } else {
                $filterInfo = 'Semua Periode';
            }
            
            // Status filter info
            $statusInfo = 'Semua Status';
            if ($statusFilter === 'lunas') {
                $statusInfo = 'Lunas';
            } elseif ($statusFilter === 'belum_lunas') {
                $statusInfo = 'Belum Lunas';
            }
            
            $anggotaMemberships = $query->orderBy('tgl_mulai', 'desc')->get();
            
            // Buat title dinamis
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
                'trace' => $e->getTraceAsString()
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
        $pakets = PaketMembership::all();
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
            $this->createPiutangAwalMembership($anggotaMembership);

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
                'trace' => $e->getTraceAsString()
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
        $anggotas = Anggota::all();
        $pakets = PaketMembership::all();
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

            // Update data membership
            $anggotaMembership->update($request->only([
                'id_anggota',
                'id_paket_membership',
                'tgl_mulai',
                'tgl_selesai',
                'diskon',
                'total_biaya',
            ]));

            // Jika total biaya berubah, update transaksi piutang dan pendapatan
            if ($totalBiayaLama != $totalBiayaBaru) {
                $this->updatePiutangDanPendapatan($anggotaMembership, $totalBiayaLama, $totalBiayaBaru);
            }

            // Update status pembayaran
            $totalDibayar = $anggotaMembership->pembayaranMemberships()->sum('jumlah_bayar');
            $anggotaMembership->status_pembayaran = $totalDibayar >= $anggotaMembership->total_biaya ? 'Lunas' : 'Belum Lunas';
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
            'tgl_bayar'        => 'required|date',
            'jumlah_bayar'     => 'required|numeric|min:0',
            'metode_pembayaran'=> 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $anggotaMembership = AnggotaMembership::findOrFail($id);

            // Validasi: cek apakah pembayaran tidak melebihi sisa tagihan
            $totalDibayar = $anggotaMembership->pembayaranMemberships()->sum('jumlah_bayar');
            $sisaTagihan = $anggotaMembership->total_biaya - $totalDibayar;

            if ($request->jumlah_bayar > $sisaTagihan) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', "Jumlah pembayaran (Rp " . number_format($request->jumlah_bayar, 0, ',', '.') . 
                           ") melebihi sisa tagihan (Rp " . number_format($sisaTagihan, 0, ',', '.') . ")");
            }

            // Simpan pembayaran baru
            $pembayaran = PembayaranMembership::create([
                'id_anggota_membership' => $anggotaMembership->id,
                'tgl_bayar'             => $request->tgl_bayar,
                'jumlah_bayar'          => $request->jumlah_bayar,
                'metode_pembayaran'     => $request->metode_pembayaran,
            ]);

            // Catat transaksi keuangan (Debit Kas, Kredit Piutang)
            $this->createTransaksiKeuanganForPembayaran(
                $pembayaran, 
                $anggotaMembership, 
                $request->jumlah_bayar, 
                $request->tgl_bayar, 
                'Pembayaran membership'
            );

            // Update status otomatis
            $totalDibayarBaru = $anggotaMembership->pembayaranMemberships()->sum('jumlah_bayar');
            $anggotaMembership->status_pembayaran = $totalDibayarBaru >= $anggotaMembership->total_biaya ? 'Lunas' : 'Belum Lunas';
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

            // Hapus semua pembayaran dan transaksi keuangan terkait pembayaran
            foreach ($anggotaMembership->pembayaranMemberships as $pembayaran) {
                TransaksiKeuangan::where('referensi_id', $pembayaran->id)
                    ->where('referensi_tabel', 'pembayaran_memberships')
                    ->delete();
                $pembayaran->delete();
            }

            // Hapus transaksi piutang dan pendapatan awal
            TransaksiKeuangan::where('referensi_id', $anggotaMembership->id)
                ->where('referensi_tabel', 'anggota_memberships')
                ->delete();

            // Hapus membership
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
            $pembayaran = PembayaranMembership::findOrFail($id);
            $anggotaMembership = $pembayaran->anggotaMembership;

            // Hapus transaksi keuangan terkait
            TransaksiKeuangan::where('referensi_id', $pembayaran->id)
                ->where('referensi_tabel', 'pembayaran_memberships')
                ->delete();

            // Hapus pembayaran
            $pembayaran->delete();

            // Update status setelah hapus
            $totalDibayar = $anggotaMembership->pembayaranMemberships()->sum('jumlah_bayar');
            $anggotaMembership->status_pembayaran = $totalDibayar >= $anggotaMembership->total_biaya ? 'Lunas' : 'Belum Lunas';
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
            'tgl_bayar'        => 'required|date',
            'jumlah_bayar'     => 'required|numeric|min:0',
            'metode_pembayaran'=> 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $pembayaran = PembayaranMembership::findOrFail($id);
            $anggotaMembership = $pembayaran->anggotaMembership;

            // Validasi: cek apakah total pembayaran tidak melebihi total tagihan
            $totalDibayarLain = $anggotaMembership->pembayaranMemberships()
                ->where('id', '!=', $pembayaran->id)
                ->sum('jumlah_bayar');
            
            $sisaTagihan = $anggotaMembership->total_biaya - $totalDibayarLain;

            if ($request->jumlah_bayar > $sisaTagihan) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', "Jumlah pembayaran (Rp " . number_format($request->jumlah_bayar, 0, ',', '.') . 
                           ") melebihi sisa tagihan (Rp " . number_format($sisaTagihan, 0, ',', '.') . ")");
            }

            // Update pembayaran
            $pembayaran->update([
                'tgl_bayar' => $request->tgl_bayar,
                'jumlah_bayar' => $request->jumlah_bayar,
                'metode_pembayaran' => $request->metode_pembayaran,
            ]);

            // Hapus transaksi keuangan lama
            TransaksiKeuangan::where('referensi_id', $pembayaran->id)
                ->where('referensi_tabel', 'pembayaran_memberships')
                ->delete();

            // Buat transaksi keuangan baru
            $this->createTransaksiKeuanganForPembayaran(
                $pembayaran, 
                $anggotaMembership, 
                $request->jumlah_bayar, 
                $request->tgl_bayar, 
                'Edit pembayaran membership'
            );

            // Update status pembayaran
            $totalDibayar = $anggotaMembership->pembayaranMemberships()->sum('jumlah_bayar');
            $anggotaMembership->status_pembayaran = $totalDibayar >= $anggotaMembership->total_biaya ? 'Lunas' : 'Belum Lunas';
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
     * ⚠️ MASALAH DI SINI: Menggunakan MOD003 (Modal) padahal seharusnya Kewajiban
     * 
     * PILIHAN 1: Tetap gunakan MOD003 (tidak ideal tapi neraca seimbang)
     * PILIHAN 2: Tambah akun KEW003 untuk Pendapatan Diterima Dimuka (recommended)
     * 
     * Saya akan gunakan PILIHAN 1 dulu sesuai struktur akun yang ada,
     * tapi dengan CATATAN PENTING di bawah
     */
    protected function createPiutangAwalMembership($anggotaMembership)
    {
        // ⚠️ CATATAN: MOD003 digunakan sebagai Pendapatan langsung
        // Ini adalah pendekatan CASH BASIS (langsung akui pendapatan)
        // Bukan accrual basis (pendapatan diterima dimuka)
        
        $akunPendapatan = AkunKeuangan::where('kode', 'MOD003')->first(); // Pendapatan Membership
        $akunPiutang = AkunKeuangan::where('kode', 'AST002')->first(); // Piutang Usaha

        if (!$akunPendapatan || !$akunPiutang) {
            Log::error('Akun keuangan tidak ditemukan untuk pencatatan piutang membership', [
                'anggota_membership_id' => $anggotaMembership->id
            ]);
            throw new \Exception('Konfigurasi akun keuangan tidak lengkap. Pastikan akun MOD003 dan AST002 tersedia.');
        }

        // Cek apakah sudah pernah dicatat (avoid duplicate)
        $sudahDicatat = TransaksiKeuangan::where('referensi_id', $anggotaMembership->id)
            ->where('referensi_tabel', 'anggota_memberships')
            ->where('akun_id', $akunPiutang->id)
            ->exists();

        if ($sudahDicatat) {
            Log::warning('Piutang membership sudah pernah dicatat', [
                'anggota_membership_id' => $anggotaMembership->id
            ]);
            return;
        }

        $totalBiaya = $anggotaMembership->total_biaya;
        $namaAnggota = $anggotaMembership->anggota->nama ?? 'Member';
        $tanggal = $anggotaMembership->tgl_mulai;

        // DEBIT: Piutang Usaha (Aset bertambah)
        TransaksiKeuangan::create([
            'akun_id' => $akunPiutang->id,
            'deskripsi' => "Piutang membership dari {$namaAnggota}",
            'debit' => $totalBiaya,
            'kredit' => 0,
            'tanggal' => $tanggal,
            'referensi_id' => $anggotaMembership->id,
            'referensi_tabel' => 'anggota_memberships',
        ]);

        // KREDIT: Pendapatan Membership (Modal/Pendapatan bertambah)
        TransaksiKeuangan::create([
            'akun_id' => $akunPendapatan->id,
            'deskripsi' => "Pendapatan membership dari {$namaAnggota}",
            'debit' => 0,
            'kredit' => $totalBiaya,
            'tanggal' => $tanggal,
            'referensi_id' => $anggotaMembership->id,
            'referensi_tabel' => 'anggota_memberships',
        ]);

        Log::info('Piutang dan pendapatan membership berhasil dicatat', [
            'anggota_membership_id' => $anggotaMembership->id,
            'jumlah' => $totalBiaya
        ]);
    }

    /**
     * Catat transaksi keuangan untuk setiap pembayaran
     * Jurnal: Debit Kas, Kredit Piutang
     * 
     * Semua pembayaran masuk ke Kas (AST001) tanpa melihat metode pembayaran
     */
    protected function createTransaksiKeuanganForPembayaran($pembayaran, $anggotaMembership, $jumlah, $tanggal, $keteranganPrefix = 'Pembayaran membership')
    {
        $akunKas = AkunKeuangan::where('kode', 'AST001')->first(); // Kas
        $akunPiutang = AkunKeuangan::where('kode', 'AST002')->first(); // Piutang Usaha

        if (!$akunKas || !$akunPiutang) {
            Log::error('Akun keuangan tidak ditemukan untuk pembayaran membership', [
                'pembayaran_id' => $pembayaran->id
            ]);
            throw new \Exception('Konfigurasi akun keuangan tidak lengkap. Pastikan akun AST001 dan AST002 tersedia.');
        }

        $namaAnggota = $anggotaMembership->anggota->nama ?? 'Member';
        $metodePembayaran = $pembayaran->metode_pembayaran ?? 'Tunai';

        // DEBIT: Kas (bertambah karena menerima uang)
        TransaksiKeuangan::create([
            'akun_id' => $akunKas->id,
            'deskripsi' => "{$keteranganPrefix} dari {$namaAnggota} via {$metodePembayaran}",
            'debit' => $jumlah,
            'kredit' => 0,
            'tanggal' => $tanggal,
            'referensi_id' => $pembayaran->id,
            'referensi_tabel' => 'pembayaran_memberships',
        ]);

        // KREDIT: Piutang Usaha (berkurang karena dilunasi)
        TransaksiKeuangan::create([
            'akun_id' => $akunPiutang->id,
            'deskripsi' => "{$keteranganPrefix} dari {$namaAnggota}",
            'debit' => 0,
            'kredit' => $jumlah,
            'tanggal' => $tanggal,
            'referensi_id' => $pembayaran->id,
            'referensi_tabel' => 'pembayaran_memberships',
        ]);

        Log::info('Pembayaran membership berhasil dicatat', [
            'pembayaran_id' => $pembayaran->id,
            'anggota_membership_id' => $anggotaMembership->id,
            'metode' => $metodePembayaran,
            'jumlah' => $jumlah
        ]);
    }

    /**
     * Update transaksi piutang dan pendapatan saat total biaya membership berubah
     */
    protected function updatePiutangDanPendapatan($anggotaMembership, $totalBiayaLama, $totalBiayaBaru)
    {
        $akunPendapatan = AkunKeuangan::where('kode', 'MOD003')->first();
        $akunPiutang = AkunKeuangan::where('kode', 'AST002')->first();

        if (!$akunPendapatan || !$akunPiutang) {
            Log::error('Akun keuangan tidak ditemukan untuk update piutang');
            throw new \Exception('Konfigurasi akun keuangan tidak lengkap');
        }

        $selisih = $totalBiayaBaru - $totalBiayaLama;
        $namaAnggota = $anggotaMembership->anggota->nama ?? 'Member';

        if ($selisih > 0) {
            // Total biaya bertambah: tambah piutang dan pendapatan
            TransaksiKeuangan::create([
                'akun_id' => $akunPiutang->id,
                'deskripsi' => "Penyesuaian piutang membership {$namaAnggota} (naik)",
                'debit' => $selisih,
                'kredit' => 0,
                'tanggal' => now(),
                'referensi_id' => $anggotaMembership->id,
                'referensi_tabel' => 'anggota_memberships',
            ]);

            TransaksiKeuangan::create([
                'akun_id' => $akunPendapatan->id,
                'deskripsi' => "Penyesuaian pendapatan membership {$namaAnggota} (naik)",
                'debit' => 0,
                'kredit' => $selisih,
                'tanggal' => now(),
                'referensi_id' => $anggotaMembership->id,
                'referensi_tabel' => 'anggota_memberships',
            ]);
        } elseif ($selisih < 0) {
            // Total biaya berkurang: kurangi piutang dan pendapatan
            $selisihAbs = abs($selisih);

            TransaksiKeuangan::create([
                'akun_id' => $akunPiutang->id,
                'deskripsi' => "Penyesuaian piutang membership {$namaAnggota} (turun)",
                'debit' => 0,
                'kredit' => $selisihAbs,
                'tanggal' => now(),
                'referensi_id' => $anggotaMembership->id,
                'referensi_tabel' => 'anggota_memberships',
            ]);

            TransaksiKeuangan::create([
                'akun_id' => $akunPendapatan->id,
                'deskripsi' => "Penyesuaian pendapatan membership {$namaAnggota} (turun)",
                'debit' => $selisihAbs,
                'kredit' => 0,
                'tanggal' => now(),
                'referensi_id' => $anggotaMembership->id,
                'referensi_tabel' => 'anggota_memberships',
            ]);
        }

        Log::info('Piutang dan pendapatan berhasil disesuaikan', [
            'anggota_membership_id' => $anggotaMembership->id,
            'total_biaya_lama' => $totalBiayaLama,
            'total_biaya_baru' => $totalBiayaBaru,
            'selisih' => $selisih
        ]);
    }
}
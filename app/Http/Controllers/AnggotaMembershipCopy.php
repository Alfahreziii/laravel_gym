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

class AnggotaMembershipController extends Controller
{
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
            'tgl_selesai'         => 'required|date',
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
                'diskon'              => $request->diskon,
                'total_biaya'         => $request->total_biaya,
                'status_pembayaran'   => $request->jumlah_bayar >= $request->total_biaya ? 'Lunas' : 'Belum Lunas',
            ]);

            // 2️⃣ Catat piutang awal (Debit Piutang, Kredit Pendapatan)
            $this->createPiutangAwalMembership($anggotaMembership);

            // 3️⃣ Simpan pembayaran pertama
            $pembayaran = PembayaranMembership::create([
                'id_anggota_membership' => $anggotaMembership->id,
                'tgl_bayar'             => $request->tgl_bayar,
                'jumlah_bayar'          => $request->jumlah_bayar,
                'metode_pembayaran'     => $request->metode_pembayaran,
            ]);

            // 4️⃣ Catat transaksi keuangan pembayaran (Debit Kas, Kredit Piutang)
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
                return redirect()->back()
                    ->with('warning', "Jumlah pembayaran (Rp " . number_format($request->jumlah_bayar, 0, ',', '.') . 
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
                return redirect()->back()
                    ->with('warning', "Jumlah pembayaran (Rp " . number_format($request->jumlah_bayar, 0, ',', '.') . 
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
     * Catat piutang awal saat membership dibuat
     * Jurnal: Debit Piutang, Kredit Pendapatan
     */
    protected function createPiutangAwalMembership($anggotaMembership)
    {
        $akunPendapatan = AkunKeuangan::where('kode', 'MOD003')->first(); // Pendapatan Membership
        $akunPiutang = AkunKeuangan::where('kode', 'AST002')->first(); // Piutang Usaha

        if (!$akunPendapatan || !$akunPiutang) {
            Log::error('Akun keuangan tidak ditemukan untuk pencatatan piutang membership', [
                'anggota_membership_id' => $anggotaMembership->id
            ]);
            throw new \Exception('Konfigurasi akun keuangan tidak lengkap');
        }

        // Cek apakah sudah pernah dicatat (avoid duplicate)
        $sudahDicatat = TransaksiKeuangan::where('referensi_id', $anggotaMembership->id)
            ->where('referensi_tabel', 'anggota_memberships')
            ->exists();

        if ($sudahDicatat) {
            Log::warning('Piutang membership sudah pernah dicatat', [
                'anggota_membership_id' => $anggotaMembership->id
            ]);
            return;
        }

        $totalBiaya = $anggotaMembership->total_biaya;
        $namaAnggota = $anggotaMembership->anggota->nama ?? 'Member';
        $tanggal = $anggotaMembership->created_at ?? now();

        // DEBIT: Piutang Usaha (bertambah)
        TransaksiKeuangan::create([
            'akun_id' => $akunPiutang->id,
            'deskripsi' => "Piutang membership dari {$namaAnggota}",
            'debit' => $totalBiaya,
            'kredit' => 0,
            'tanggal' => $tanggal,
            'referensi_id' => $anggotaMembership->id,
            'referensi_tabel' => 'anggota_memberships',
        ]);

        // KREDIT: Pendapatan Membership (bertambah)
        TransaksiKeuangan::create([
            'akun_id' => $akunPendapatan->id,
            'deskripsi' => "Pendapatan membership dari {$namaAnggota}",
            'debit' => 0,
            'kredit' => $totalBiaya,
            'tanggal' => $tanggal,
            'referensi_id' => $anggotaMembership->id,
            'referensi_tabel' => 'anggota_memberships',
        ]);

        Log::info('Piutang membership berhasil dicatat', [
            'anggota_membership_id' => $anggotaMembership->id,
            'jumlah' => $totalBiaya
        ]);
    }

    /**
     * Catat transaksi keuangan untuk setiap pembayaran
     * Jurnal: Debit Kas, Kredit Piutang
     */
    protected function createTransaksiKeuanganForPembayaran($pembayaran, $anggotaMembership, $jumlah, $tanggal, $keteranganPrefix = 'Pembayaran membership')
    {
        $akunKas = AkunKeuangan::where('kode', 'AST001')->first(); // Kas
        $akunPiutang = AkunKeuangan::where('kode', 'AST002')->first(); // Piutang Usaha

        if (!$akunKas || !$akunPiutang) {
            Log::error('Akun keuangan tidak ditemukan untuk pembayaran membership', [
                'pembayaran_id' => $pembayaran->id
            ]);
            throw new \Exception('Konfigurasi akun keuangan tidak lengkap');
        }

        $namaAnggota = $anggotaMembership->anggota->nama ?? 'Member';
        $tanggalTransaksi = $tanggal ?? now();

        // DEBIT: Kas (bertambah karena menerima uang)
        TransaksiKeuangan::create([
            'akun_id' => $akunKas->id,
            'deskripsi' => "{$keteranganPrefix} dari {$namaAnggota}",
            'debit' => $jumlah,
            'kredit' => 0,
            'tanggal' => $tanggalTransaksi,
            'referensi_id' => $pembayaran->id,
            'referensi_tabel' => 'pembayaran_memberships',
        ]);

        // KREDIT: Piutang Usaha (berkurang karena dilunasi)
        TransaksiKeuangan::create([
            'akun_id' => $akunPiutang->id,
            'deskripsi' => "{$keteranganPrefix} dari {$namaAnggota}",
            'debit' => 0,
            'kredit' => $jumlah,
            'tanggal' => $tanggalTransaksi,
            'referensi_id' => $pembayaran->id,
            'referensi_tabel' => 'pembayaran_memberships',
        ]);

        Log::info('Pembayaran membership berhasil dicatat', [
            'pembayaran_id' => $pembayaran->id,
            'anggota_membership_id' => $anggotaMembership->id,
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
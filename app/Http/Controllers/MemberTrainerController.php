<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MemberTrainer;
use App\Models\Anggota;
use App\Models\PaketPersonalTrainer;
use App\Models\Trainer;
use App\Models\PembayaranMemberTrainer;
use App\Models\SesiMemberTrainer;
use App\Models\SesiTrainer;
use App\Models\AkunKeuangan;
use App\Models\TransaksiKeuangan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class MemberTrainerController extends Controller
{
    public function exportPdf(Request $request)
    {
        try {
            $statusFilter = $request->input('status_filter', 'all');
            
            // Hitung statistik dari SEMUA data (tidak terfilter)
            $allMemberTrainers = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'trainer'])->get();
            
            $totalMemberTrainer = $allMemberTrainers->count();
            $totalLunas = $allMemberTrainers->where('status_pembayaran', 'Lunas')->count();
            $totalBelumLunas = $allMemberTrainers->where('status_pembayaran', 'Belum Lunas')->count();
            
            // Hitung total biaya
            $totalPendapatan = $allMemberTrainers->sum('total_biaya');
            $totalTerbayar = $allMemberTrainers->sum(function($item) {
                return $item->pembayaranMemberTrainers()->sum('jumlah_bayar');
            });
            $totalPiutang = $totalPendapatan - $totalTerbayar;
            
            // Query untuk data yang akan ditampilkan (terfilter)
            $query = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'trainer', 'pembayaranMemberTrainers']);
            
            // Filter berdasarkan status
            if ($statusFilter === 'lunas') {
                $query->where('status_pembayaran', 'Lunas');
                $title = 'Laporan Member Trainer - Lunas';
            } elseif ($statusFilter === 'belum_lunas') {
                $query->where('status_pembayaran', 'Belum Lunas');
                $title = 'Laporan Member Trainer - Belum Lunas';
            } else {
                $title = 'Laporan Member Trainer - Semua Data';
            }
            
            $memberTrainers = $query->orderBy('created_at', 'desc')->get();

            $pdf = Pdf::loadView('pages.membertrainer.pdf', compact(
                'memberTrainers',
                'totalMemberTrainer',
                'totalLunas',
                'totalBelumLunas',
                'totalPendapatan',
                'totalTerbayar',
                'totalPiutang',
                'title',
                'statusFilter'
            ));

            $pdf->setPaper('a4', 'landscape');
            
            // Generate filename dengan status filter
            $filename = 'Laporan_Member_Trainer_' . ucfirst($statusFilter) . '_' . date('Y-m-d_His') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Gagal export PDF member trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('danger', 'Gagal export PDF: ' . $e->getMessage());
        }
    }
    public function index()
    {
        $memberTrainers = MemberTrainer::with(['anggota', 'paketPersonalTrainer', 'trainer', 'pembayaranMemberTrainers'])
            ->latest()->get();
        return view('pages.membertrainer.index', compact('memberTrainers'));
    }

    public function create()
    {
        $anggotas = Anggota::all();
        $trainers = Trainer::all();
        $pakets = PaketPersonalTrainer::all();
        return view('pages.membertrainer.create', compact('anggotas', 'pakets', 'trainers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_anggota'                => 'required|exists:anggotas,id',
            'id_paket_personal_trainer' => 'required|exists:paket_personal_trainers,id',
            'id_trainer'                => 'required|exists:trainers,id',
            'tgl_mulai'                 => 'required|date',
            'tgl_selesai'               => 'required|date|after_or_equal:tgl_mulai',
            'diskon'                    => 'nullable|numeric|min:0',
            'total_biaya'               => 'required|numeric|min:0',
            'tgl_bayar'                 => 'required|date',
            'jumlah_bayar'              => 'required|numeric|min:0',
            'metode_pembayaran'         => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(uniqid());
            $paket = PaketPersonalTrainer::findOrFail($request->id_paket_personal_trainer);

            // 1️⃣ Simpan member trainer dengan sesi = jumlah_sesi paket (sisa sesi yang tersedia)
            $memberTrainer = MemberTrainer::create([
                'kode_transaksi'            => $kodeTransaksi,
                'id_anggota'                => $request->id_anggota,
                'id_paket_personal_trainer' => $request->id_paket_personal_trainer,
                'id_trainer'                => $request->id_trainer,
                'tgl_mulai'                 => $request->tgl_mulai,
                'tgl_selesai'               => $request->tgl_selesai,
                'diskon'                    => $request->diskon ?? 0,
                'total_biaya'               => $request->total_biaya,
                'status_pembayaran'         => $request->jumlah_bayar >= $request->total_biaya ? 'Lunas' : 'Belum Lunas',
                'sesi'                      => $paket->jumlah_sesi,
            ]);

            // 2️⃣ Update sesi_belum_dijalani trainer
            $trainer = Trainer::findOrFail($request->id_trainer);
            $trainer->increment('sesi_belum_dijalani', $paket->jumlah_sesi);

            // 3️⃣ Catat piutang awal (Debit Piutang, Kredit Pendapatan PT)
            $this->createPiutangAwalPersonalTrainer($memberTrainer);

            // 4️⃣ Simpan pembayaran pertama
            $pembayaran = PembayaranMemberTrainer::create([
                'id_member_trainer' => $memberTrainer->id,
                'tgl_bayar'         => $request->tgl_bayar,
                'jumlah_bayar'      => $request->jumlah_bayar,
                'metode_pembayaran' => $request->metode_pembayaran,
            ]);

            // 5️⃣ Catat transaksi keuangan pembayaran (Debit Kas, Kredit Piutang)
            $this->createTransaksiKeuanganForPembayaran(
                $pembayaran,
                $memberTrainer,
                $request->jumlah_bayar,
                $request->tgl_bayar,
                'Pembayaran awal personal trainer'
            );

            DB::commit();

            return redirect()->route('membertrainer.index')
                ->with('success', 'Data member trainer berhasil ditambahkan beserta pembayaran pertama.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan member trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data member trainer: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $memberTrainer = MemberTrainer::with(['pembayaranMemberTrainers', 'sesiLogs'])->findOrFail($id);
        return view('pages.membertrainer.show', compact('memberTrainer'));
    }

    public function edit($id)
    {
        $memberTrainer = MemberTrainer::with('pembayaranMemberTrainers')->findOrFail($id);
        $anggotas = Anggota::all();
        $pakets = PaketPersonalTrainer::all();
        $trainers = Trainer::all();

        return view('pages.membertrainer.edit', compact('memberTrainer', 'anggotas', 'pakets', 'trainers'));
    }

    public function update(Request $request, $id)
    {
        $memberTrainer = MemberTrainer::findOrFail($id);

        $request->validate([
            'id_anggota'                => 'required|exists:anggotas,id',
            'id_paket_personal_trainer' => 'required|exists:paket_personal_trainers,id',
            'id_trainer'                => 'required|exists:trainers,id',
            'tgl_mulai'                 => 'required|date',
            'tgl_selesai'               => 'required|date|after_or_equal:tgl_mulai',
            'diskon'                    => 'nullable|numeric|min:0',
            'total_biaya'               => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalBiayaLama = $memberTrainer->total_biaya;
            $totalBiayaBaru = $request->total_biaya;

            // Update data member trainer
            $memberTrainer->update($request->only([
                'id_anggota',
                'id_paket_personal_trainer',
                'id_trainer',
                'tgl_mulai',
                'tgl_selesai',
                'diskon',
                'total_biaya',
            ]));

            // Jika total biaya berubah, update transaksi piutang dan pendapatan
            if ($totalBiayaLama != $totalBiayaBaru) {
                $this->updatePiutangDanPendapatan($memberTrainer, $totalBiayaLama, $totalBiayaBaru);
            }

            // Update status pembayaran
            $totalDibayar = $memberTrainer->pembayaranMemberTrainers()->sum('jumlah_bayar');
            $memberTrainer->status_pembayaran = $totalDibayar >= $memberTrainer->total_biaya ? 'Lunas' : 'Belum Lunas';
            $memberTrainer->save();

            DB::commit();

            return redirect()->route('membertrainer.index')
                ->with('success', 'Data member trainer berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update member trainer', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal update member trainer: ' . $e->getMessage());
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
            $memberTrainer = MemberTrainer::findOrFail($id);

            // Validasi: cek apakah pembayaran tidak melebihi sisa tagihan
            $totalDibayar = $memberTrainer->pembayaranMemberTrainers()->sum('jumlah_bayar');
            $sisaTagihan = $memberTrainer->total_biaya - $totalDibayar;

            if ($request->jumlah_bayar > $sisaTagihan) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', "Jumlah pembayaran (Rp " . number_format($request->jumlah_bayar, 0, ',', '.') . 
                           ") melebihi sisa tagihan (Rp " . number_format($sisaTagihan, 0, ',', '.') . ")");
            }

            // Simpan pembayaran baru
            $pembayaran = PembayaranMemberTrainer::create([
                'id_member_trainer' => $memberTrainer->id,
                'tgl_bayar'         => $request->tgl_bayar,
                'jumlah_bayar'      => $request->jumlah_bayar,
                'metode_pembayaran' => $request->metode_pembayaran,
            ]);

            // Catat transaksi keuangan (Debit Kas, Kredit Piutang)
            $this->createTransaksiKeuanganForPembayaran(
                $pembayaran,
                $memberTrainer,
                $request->jumlah_bayar,
                $request->tgl_bayar,
                'Pembayaran personal trainer'
            );

            // Update status otomatis
            $totalDibayarBaru = $memberTrainer->pembayaranMemberTrainers()->sum('jumlah_bayar');
            $memberTrainer->status_pembayaran = $totalDibayarBaru >= $memberTrainer->total_biaya ? 'Lunas' : 'Belum Lunas';
            $memberTrainer->save();

            DB::commit();

            return redirect()->route('membertrainer.edit', $id)
                ->with('success', 'Pembayaran baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal tambah pembayaran PT', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Gagal menambah pembayaran: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $memberTrainer = MemberTrainer::findOrFail($id);
            $trainer = $memberTrainer->trainer;
            $paket = $memberTrainer->paketPersonalTrainer;

            // Hapus semua pembayaran dan transaksi keuangan terkait pembayaran
            foreach ($memberTrainer->pembayaranMemberTrainers as $pembayaran) {
                TransaksiKeuangan::where('referensi_id', $pembayaran->id)
                    ->where('referensi_tabel', 'pembayaran_member_trainers')
                    ->delete();
                $pembayaran->delete();
            }

            // Hapus transaksi piutang dan pendapatan awal
            TransaksiKeuangan::where('referensi_id', $memberTrainer->id)
                ->where('referensi_tabel', 'member_trainers')
                ->delete();

            // Hapus log sesi
            $memberTrainer->sesiLogs()->delete();

            // ✅ PERBAIKAN: Hitung sesi yang sudah dijalani dan sisa sesi
            $sesiSudahDijalani = $paket->jumlah_sesi - $memberTrainer->sesi; // Misal: 10 - 7 = 3 sesi sudah dijalani
            $sisaSesi = $memberTrainer->sesi; // Sisa sesi yang belum dijalani = 7

            // Kurangi sesi_belum_dijalani trainer (kembalikan sesi yang belum dijalani)
            if ($sisaSesi > 0) {
                $trainer->decrement('sesi_belum_dijalani', $sisaSesi);
            }
            
            // Kurangi sesi_sudah_dijalani trainer (kembalikan sesi yang sudah dijalani)
            if ($sesiSudahDijalani > 0) {
                $trainer->decrement('sesi_sudah_dijalani', $sesiSudahDijalani);
            }

            // Hapus member trainer
            $memberTrainer->delete();

            DB::commit();

            return redirect()->route('membertrainer.index')
                ->with('success', 'Data member trainer berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus member trainer', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Gagal menghapus member trainer: ' . $e->getMessage());
        }
    }

    public function destroyPembayaran($id)
    {
        DB::beginTransaction();
        try {
            $pembayaran = PembayaranMemberTrainer::findOrFail($id);
            $memberTrainer = $pembayaran->memberTrainer;

            // Hapus transaksi keuangan terkait
            TransaksiKeuangan::where('referensi_id', $pembayaran->id)
                ->where('referensi_tabel', 'pembayaran_member_trainers')
                ->delete();

            // Hapus pembayaran
            $pembayaran->delete();

            // Update status setelah hapus
            $totalDibayar = $memberTrainer->pembayaranMemberTrainers()->sum('jumlah_bayar');
            $memberTrainer->status_pembayaran = $totalDibayar >= $memberTrainer->total_biaya ? 'Lunas' : 'Belum Lunas';
            $memberTrainer->save();

            DB::commit();

            return redirect()->route('membertrainer.edit', $memberTrainer->id)
                ->with('success', 'Pembayaran berhasil dihapus dan transaksi keuangan diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus pembayaran PT', ['error' => $e->getMessage()]);
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
            $pembayaran = PembayaranMemberTrainer::findOrFail($id);
            $memberTrainer = $pembayaran->memberTrainer;

            // Validasi: cek apakah total pembayaran tidak melebihi total tagihan
            $totalDibayarLain = $memberTrainer->pembayaranMemberTrainers()
                ->where('id', '!=', $pembayaran->id)
                ->sum('jumlah_bayar');
            
            $sisaTagihan = $memberTrainer->total_biaya - $totalDibayarLain;

            if ($request->jumlah_bayar > $sisaTagihan) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', "Jumlah pembayaran (Rp " . number_format($request->jumlah_bayar, 0, ',', '.') . 
                           ") melebihi sisa tagihan (Rp " . number_format($sisaTagihan, 0, ',', '.') . ")");
            }

            // Update pembayaran
            $pembayaran->update([
                'tgl_bayar'         => $request->tgl_bayar,
                'jumlah_bayar'      => $request->jumlah_bayar,
                'metode_pembayaran' => $request->metode_pembayaran,
            ]);

            // Hapus transaksi keuangan lama
            TransaksiKeuangan::where('referensi_id', $pembayaran->id)
                ->where('referensi_tabel', 'pembayaran_member_trainers')
                ->delete();

            // Buat transaksi keuangan baru
            $this->createTransaksiKeuanganForPembayaran(
                $pembayaran,
                $memberTrainer,
                $request->jumlah_bayar,
                $request->tgl_bayar,
                'Edit pembayaran personal trainer'
            );

            // Update status pembayaran
            $totalDibayar = $memberTrainer->pembayaranMemberTrainers()->sum('jumlah_bayar');
            $memberTrainer->status_pembayaran = $totalDibayar >= $memberTrainer->total_biaya ? 'Lunas' : 'Belum Lunas';
            $memberTrainer->save();

            DB::commit();

            return redirect()->route('membertrainer.edit', $memberTrainer->id)
                ->with('success', 'Pembayaran berhasil diperbarui dan transaksi keuangan diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update pembayaran PT', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Gagal update pembayaran: ' . $e->getMessage());
        }
    }

    // =====================================================
    // FUNGSI HELPER UNTUK TRANSAKSI KEUANGAN
    // =====================================================

    protected function createPiutangAwalPersonalTrainer($memberTrainer)
    {
        $akunPendapatan = AkunKeuangan::where('kode', 'MOD004')->first();
        $akunPiutang = AkunKeuangan::where('kode', 'AST002')->first();

        if (!$akunPendapatan || !$akunPiutang) {
            Log::error('Akun keuangan tidak ditemukan untuk pencatatan piutang PT', [
                'member_trainer_id' => $memberTrainer->id
            ]);
            throw new \Exception('Konfigurasi akun keuangan tidak lengkap. Pastikan akun MOD004 dan AST002 tersedia.');
        }

        $sudahDicatat = TransaksiKeuangan::where('referensi_id', $memberTrainer->id)
            ->where('referensi_tabel', 'member_trainers')
            ->where('akun_id', $akunPiutang->id)
            ->exists();

        if ($sudahDicatat) {
            Log::warning('Piutang PT sudah pernah dicatat', [
                'member_trainer_id' => $memberTrainer->id
            ]);
            return;
        }

        $totalBiaya = $memberTrainer->total_biaya;
        $namaAnggota = $memberTrainer->anggota->name ?? 'Member';
        $namaTrainer = $memberTrainer->trainer->name ?? 'Trainer';
        $tanggal = $memberTrainer->created_at ?? now();

        TransaksiKeuangan::create([
            'akun_id' => $akunPiutang->id,
            'deskripsi' => "Piutang PT dari {$namaAnggota} dengan trainer {$namaTrainer}",
            'debit' => $totalBiaya,
            'kredit' => 0,
            'tanggal' => $tanggal,
            'referensi_id' => $memberTrainer->id,
            'referensi_tabel' => 'member_trainers',
        ]);

        TransaksiKeuangan::create([
            'akun_id' => $akunPendapatan->id,
            'deskripsi' => "Pendapatan PT dari {$namaAnggota} dengan trainer {$namaTrainer}",
            'debit' => 0,
            'kredit' => $totalBiaya,
            'tanggal' => $tanggal,
            'referensi_id' => $memberTrainer->id,
            'referensi_tabel' => 'member_trainers',
        ]);

        Log::info('Piutang dan pendapatan PT berhasil dicatat', [
            'member_trainer_id' => $memberTrainer->id,
            'jumlah' => $totalBiaya
        ]);
    }

    protected function createTransaksiKeuanganForPembayaran($pembayaran, $memberTrainer, $jumlah, $tanggal, $keteranganPrefix = 'Pembayaran personal trainer')
    {
        $akunKas = AkunKeuangan::where('kode', 'AST001')->first();
        $akunPiutang = AkunKeuangan::where('kode', 'AST002')->first();

        if (!$akunKas || !$akunPiutang) {
            Log::error('Akun keuangan tidak ditemukan untuk pembayaran PT', [
                'pembayaran_id' => $pembayaran->id
            ]);
            throw new \Exception('Konfigurasi akun keuangan tidak lengkap. Pastikan akun AST001 dan AST002 tersedia.');
        }

        $namaAnggota = $memberTrainer->anggota->name ?? 'Member';
        $namaTrainer = $memberTrainer->trainer->name ?? 'Trainer';
        $metodePembayaran = $pembayaran->metode_pembayaran ?? 'Tunai';

        TransaksiKeuangan::create([
            'akun_id' => $akunKas->id,
            'deskripsi' => "{$keteranganPrefix} dari {$namaAnggota} (Trainer: {$namaTrainer}) via {$metodePembayaran}",
            'debit' => $jumlah,
            'kredit' => 0,
            'tanggal' => $tanggal,
            'referensi_id' => $pembayaran->id,
            'referensi_tabel' => 'pembayaran_member_trainers',
        ]);

        TransaksiKeuangan::create([
            'akun_id' => $akunPiutang->id,
            'deskripsi' => "{$keteranganPrefix} dari {$namaAnggota} (Trainer: {$namaTrainer})",
            'debit' => 0,
            'kredit' => $jumlah,
            'tanggal' => $tanggal,
            'referensi_id' => $pembayaran->id,
            'referensi_tabel' => 'pembayaran_member_trainers',
        ]);

        Log::info('Pembayaran PT berhasil dicatat', [
            'pembayaran_id' => $pembayaran->id,
            'member_trainer_id' => $memberTrainer->id,
            'metode' => $metodePembayaran,
            'jumlah' => $jumlah
        ]);
    }

    protected function updatePiutangDanPendapatan($memberTrainer, $totalBiayaLama, $totalBiayaBaru)
    {
        $akunPendapatan = AkunKeuangan::where('kode', 'MOD004')->first();
        $akunPiutang = AkunKeuangan::where('kode', 'AST002')->first();

        if (!$akunPendapatan || !$akunPiutang) {
            Log::error('Akun keuangan tidak ditemukan untuk update piutang PT');
            throw new \Exception('Konfigurasi akun keuangan tidak lengkap');
        }

        $selisih = $totalBiayaBaru - $totalBiayaLama;
        $namaAnggota = $memberTrainer->anggota->name ?? 'Member';
        $namaTrainer = $memberTrainer->trainer->name ?? 'Trainer';

        if ($selisih > 0) {
            TransaksiKeuangan::create([
                'akun_id' => $akunPiutang->id,
                'deskripsi' => "Penyesuaian piutang PT {$namaAnggota} (Trainer: {$namaTrainer}) - naik",
                'debit' => $selisih,
                'kredit' => 0,
                'tanggal' => now(),
                'referensi_id' => $memberTrainer->id,
                'referensi_tabel' => 'member_trainers',
            ]);

            TransaksiKeuangan::create([
                'akun_id' => $akunPendapatan->id,
                'deskripsi' => "Penyesuaian pendapatan PT {$namaAnggota} (Trainer: {$namaTrainer}) - naik",
                'debit' => 0,
                'kredit' => $selisih,
                'tanggal' => now(),
                'referensi_id' => $memberTrainer->id,
                'referensi_tabel' => 'member_trainers',
            ]);
        } elseif ($selisih < 0) {
            $selisihAbs = abs($selisih);

            TransaksiKeuangan::create([
                'akun_id' => $akunPiutang->id,
                'deskripsi' => "Penyesuaian piutang PT {$namaAnggota} (Trainer: {$namaTrainer}) - turun",
                'debit' => 0,
                'kredit' => $selisihAbs,
                'tanggal' => now(),
                'referensi_id' => $memberTrainer->id,
                'referensi_tabel' => 'member_trainers',
            ]);

            TransaksiKeuangan::create([
                'akun_id' => $akunPendapatan->id,
                'deskripsi' => "Penyesuaian pendapatan PT {$namaAnggota} (Trainer: {$namaTrainer}) - turun",
                'debit' => $selisihAbs,
                'kredit' => 0,
                'tanggal' => now(),
                'referensi_id' => $memberTrainer->id,
                'referensi_tabel' => 'member_trainers',
            ]);
        }

        Log::info('Piutang dan pendapatan PT berhasil disesuaikan', [
            'member_trainer_id' => $memberTrainer->id,
            'total_biaya_lama' => $totalBiayaLama,
            'total_biaya_baru' => $totalBiayaBaru,
            'selisih' => $selisih
        ]);
    }
}
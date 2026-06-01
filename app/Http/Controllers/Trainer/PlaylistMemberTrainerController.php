<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MemberTrainer;
use App\Models\PlaylistTrainer;
use App\Models\PlaylistMemberTrainer;
use App\Models\Trainer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PlaylistMemberTrainerController extends Controller
{
    /**
     * Get the trainer ID for the authenticated user
     */
    private function getTrainerId()
    {
        $trainerId = Auth::user()->trainer_id;

        if (!$trainerId) {
            throw new \Exception('User ini tidak memiliki data trainer');
        }

        return $trainerId;
    }

    /**
     * Halaman monitoring - tampilkan member yang sedang dalam sesi training
     */
    public function monitoring()
    {
        try {
            $trainerId = $this->getTrainerId();

            $trainer = Trainer::findOrFail($trainerId);

            $activeMember = MemberTrainer::with(['anggota', 'paketPersonalTrainer'])
                ->where('id_trainer', $trainerId)
                ->where('is_session_active', true)
                ->first();

            if (!$activeMember) {
                return view('pages.trainer.monitoring', [
                    'trainer' => $trainer,
                    'activeMember' => null,
                    'playlists' => [],
                    'savedPlaylists' => [],
                    'sesiKe' => null
                ]);
            }

            $playlists = PlaylistTrainer::where('id_trainer', $trainerId)->get();

            $sesiKe = $activeMember->paketPersonalTrainer->jumlah_sesi - $activeMember->sesi + 1;

            // keyBy 'latihan' karena sudah tidak ada id_playlist_trainer
            $savedPlaylists = PlaylistMemberTrainer::where('id_member_trainer', $activeMember->id)
                ->where('sesi_ke', $sesiKe)
                ->get()
                ->keyBy('latihan');

            return view('pages.trainer.monitoring', compact('trainer', 'activeMember', 'playlists', 'savedPlaylists', 'sesiKe'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Store multiple playlist yang di-checklist sekaligus
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_member_trainer' => 'required|exists:member_trainers,id',
            'sesi_ke' => 'required|integer|min:1',
            'playlist_ids' => 'required|array|min:1',
            'playlist_ids.*' => 'required|exists:playlist_trainers,id',
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'nullable|string|max:500',
        ], [
            'playlist_ids.required' => 'Silakan pilih minimal 1 playlist yang sudah dilakukan.',
            'playlist_ids.min' => 'Silakan pilih minimal 1 playlist yang sudah dilakukan.',
        ]);

        DB::beginTransaction();
        try {
            $trainerId = $this->getTrainerId();

            $memberTrainer = MemberTrainer::where('id', $request->id_member_trainer)
                ->where('id_trainer', $trainerId)
                ->where('is_session_active', true)
                ->firstOrFail();

            // Ambil latihan yang sudah tersimpan untuk sesi ini
            $existingLatihanNames = PlaylistMemberTrainer::where('id_member_trainer', $request->id_member_trainer)
                ->where('sesi_ke', $request->sesi_ke)
                ->pluck('latihan')
                ->toArray();

            // Ambil semua latihan sekaligus
            $playlistTrainers = PlaylistTrainer::whereIn('id', $request->playlist_ids)
                ->pluck('latihan', 'id');

            foreach ($request->playlist_ids as $playlistId) {
                $namaLatihan = $playlistTrainers[$playlistId] ?? 'Latihan tidak ditemukan';

                // Skip jika sudah tersimpan (hindari duplikat)
                if (in_array($namaLatihan, $existingLatihanNames)) {
                    continue;
                }

                PlaylistMemberTrainer::create([
                    'id_member_trainer' => $request->id_member_trainer,
                    'latihan' => $namaLatihan,
                    'sesi_ke' => $request->sesi_ke,
                    'keterangan' => $request->keterangan[$playlistId] ?? null,
                    'completed' => true,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Playlist training berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus playlist yang di-unchecklist
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'id_member_trainer' => 'required|exists:member_trainers,id',
            'latihan' => 'required|string',
            'sesi_ke' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $trainerId = $this->getTrainerId();

            $memberTrainer = MemberTrainer::where('id', $request->id_member_trainer)
                ->where('id_trainer', $trainerId)
                ->where('is_session_active', true)
                ->firstOrFail();

            $deleted = PlaylistMemberTrainer::where('id_member_trainer', $request->id_member_trainer)
                ->where('latihan', $request->latihan)
                ->where('sesi_ke', $request->sesi_ke)
                ->delete();

            DB::commit();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Playlist berhasil dihapus dari daftar yang disimpan'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Playlist tidak ditemukan'
                ], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update keterangan playlist
     */
    public function updateKeterangan(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Method tidak digunakan'
        ], 404);
    }

    /**
     * Halaman riwayat gym member - tampilkan semua sesi dan playlist yang sudah dilakukan
     */
    public function memberHistory($memberTrainerId)
    {
        try {
            $trainerId = $this->getTrainerId();

            $trainer = Trainer::findOrFail($trainerId);

            $memberTrainer = MemberTrainer::with(['anggota', 'paketPersonalTrainer'])
                ->where('id', $memberTrainerId)
                ->where('id_trainer', $trainerId)
                ->firstOrFail();

            // Ambil semua sesi yang sudah selesai dari log (type = out)
            $sesiSelesai = \App\Models\SesiMemberTrainer::where('id_member_trainer', $memberTrainer->id)
                ->where('type', 'out')
                ->orderBy('created_at', 'asc')
                ->get();

            $jumlahSesi = $memberTrainer->paketPersonalTrainer->jumlah_sesi;
            $sesiKeList = $sesiSelesai->map(function ($log) use ($jumlahSesi) {
                return $jumlahSesi - $log->current_sesi;
            })->unique()->sort()->values();

            // Buat map sesi_ke => durasi dari description log
            $durasiPerSesi = [];
            $tanggalPerSesi = [];
            foreach ($sesiSelesai as $log) {
                $sesiKe = $jumlahSesi - $log->current_sesi;
                if (!isset($durasiPerSesi[$sesiKe])) {
                    preg_match('/durasi:\s*(-?[\d.]+)\s*menit/i', $log->description ?? '', $matches);
                    $durasiPerSesi[$sesiKe] = isset($matches[1]) ? round(abs((float) $matches[1])) : null;
                    $tanggalPerSesi[$sesiKe] = $log->created_at;
                }
            }

            // Ambil playlist yang tersimpan, group by sesi_ke
            $playlistGrouped = PlaylistMemberTrainer::where('id_member_trainer', $memberTrainer->id)
                ->orderBy('sesi_ke', 'asc')
                ->get()
                ->groupBy('sesi_ke');

            // Merge: semua sesi_ke dari log, dengan playlist (atau kosong kalau tidak ada)
            $history = collect();
            foreach ($sesiKeList as $sesiKe) {
                $history[$sesiKe] = $playlistGrouped->get($sesiKe, collect());
            }

            foreach ($playlistGrouped->keys() as $sesiKe) {
                if (!$history->has($sesiKe)) {
                    $history[$sesiKe] = $playlistGrouped->get($sesiKe);
                }
            }

            $history = $history->sortKeys();

            return view('pages.trainer.playlistmembertrainer.index', compact('trainer', 'memberTrainer', 'history', 'durasiPerSesi', 'tanggalPerSesi'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function exportHistoryPdf($memberTrainerId)
    {
        try {
            $trainerId = $this->getTrainerId();
            $trainer = Trainer::findOrFail($trainerId);

            $memberTrainer = MemberTrainer::with(['anggota', 'paketPersonalTrainer'])
                ->where('id', $memberTrainerId)
                ->where('id_trainer', $trainerId)
                ->firstOrFail();

            $sesiSelesai = \App\Models\SesiMemberTrainer::where('id_member_trainer', $memberTrainer->id)
                ->where('type', 'out')
                ->orderBy('created_at', 'asc')
                ->get();

            $jumlahSesi = $memberTrainer->paketPersonalTrainer->jumlah_sesi;
            $sesiKeList = $sesiSelesai->map(function ($log) use ($jumlahSesi) {
                return $jumlahSesi - $log->current_sesi;
            })->unique()->sort()->values();

            $durasiPerSesi = [];
            $tanggalPerSesi = [];
            foreach ($sesiSelesai as $log) {
                $sesiKe = $jumlahSesi - $log->current_sesi;
                if (!isset($durasiPerSesi[$sesiKe])) {
                    preg_match('/durasi:\s*(-?[\d.]+)\s*menit/i', $log->description ?? '', $matches);
                    $durasiPerSesi[$sesiKe] = isset($matches[1]) ? round(abs((float) $matches[1])) : null;
                    $tanggalPerSesi[$sesiKe] = $log->created_at;
                }
            }

            $playlistGrouped = PlaylistMemberTrainer::where('id_member_trainer', $memberTrainer->id)
                ->orderBy('sesi_ke', 'asc')
                ->get()
                ->groupBy('sesi_ke');

            $history = collect();
            foreach ($sesiKeList as $sesiKe) {
                $history[$sesiKe] = $playlistGrouped->get($sesiKe, collect());
            }
            foreach ($playlistGrouped->keys() as $sesiKe) {
                if (!$history->has($sesiKe)) {
                    $history[$sesiKe] = $playlistGrouped->get($sesiKe);
                }
            }
            $history = $history->sortKeys();

            $totalDurasi = array_sum(array_filter($durasiPerSesi));

            $pdf = Pdf::loadView('pages.trainer.playlistmembertrainer.pdf', compact(
                'trainer',
                'memberTrainer',
                'history',
                'durasiPerSesi',
                'tanggalPerSesi',
                'totalDurasi'
            ))->setPaper('a4', 'portrait');

            $filename = 'riwayat-gym-' . str_replace(' ', '-', strtolower($memberTrainer->anggota->name)) . '-' . now()->format('Ymd') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }
}

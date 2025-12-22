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
            
            // Cari trainer
            $trainer = Trainer::findOrFail($trainerId);
            
            // Ambil member yang sedang dalam sesi aktif
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
            
            // Ambil semua playlist trainer
            $playlists = PlaylistTrainer::where('id_trainer', $trainerId)->get();
            
            // Hitung sesi ke berapa untuk member ini
            $sesiKe = $activeMember->paketPersonalTrainer->jumlah_sesi - $activeMember->sesi + 1;
            
            // Ambil playlist yang sudah disimpan untuk sesi ini (dengan keterangan)
            $savedPlaylists = PlaylistMemberTrainer::where('id_member_trainer', $activeMember->id)
                ->where('sesi_ke', $sesiKe)
                ->get()
                ->keyBy('id_playlist_trainer');
            
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
            
            // Validasi bahwa member trainer ini milik trainer yang login
            $memberTrainer = MemberTrainer::where('id', $request->id_member_trainer)
                ->where('id_trainer', $trainerId)
                ->where('is_session_active', true)
                ->firstOrFail();
            
            // Hapus semua playlist lama untuk sesi ini (fresh start)
            PlaylistMemberTrainer::where('id_member_trainer', $request->id_member_trainer)
                ->where('sesi_ke', $request->sesi_ke)
                ->delete();
            
            // Simpan semua playlist yang di-checklist
            foreach ($request->playlist_ids as $playlistId) {
                PlaylistMemberTrainer::create([
                    'id_member_trainer' => $request->id_member_trainer,
                    'id_playlist_trainer' => $playlistId,
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
            'id_playlist_trainer' => 'required|exists:playlist_trainers,id',
            'sesi_ke' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $trainerId = $this->getTrainerId();
            
            // Validasi bahwa member trainer ini milik trainer yang login
            $memberTrainer = MemberTrainer::where('id', $request->id_member_trainer)
                ->where('id_trainer', $trainerId)
                ->where('is_session_active', true)
                ->firstOrFail();
            
            // Hapus data
            $deleted = PlaylistMemberTrainer::where('id_member_trainer', $request->id_member_trainer)
                ->where('id_playlist_trainer', $request->id_playlist_trainer)
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
        // Method ini tidak diperlukan lagi karena menggunakan submit form
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
            
            // Cari trainer
            $trainer = Trainer::findOrFail($trainerId);
            
            // Ambil data member trainer (langsung berdasarkan ID member_trainer)
            $memberTrainer = MemberTrainer::with(['anggota', 'paketPersonalTrainer'])
                ->where('id', $memberTrainerId)
                ->where('id_trainer', $trainerId)
                ->firstOrFail();
            
            // Ambil semua riwayat playlist yang sudah dilakukan, dikelompokkan per sesi
            $history = PlaylistMemberTrainer::with('playlistTrainer')
                ->where('id_member_trainer', $memberTrainer->id)
                ->orderBy('sesi_ke', 'asc')
                ->get()
                ->groupBy('sesi_ke');
            
            return view('pages.trainer.playlistmembertrainer.index', compact('trainer', 'memberTrainer', 'history'));
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
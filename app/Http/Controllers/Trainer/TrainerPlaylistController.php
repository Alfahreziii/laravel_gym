<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlaylistTrainer;
use Illuminate\Support\Facades\Auth;

class TrainerPlaylistController extends Controller
{
    /**
     * Get the trainer ID for the authenticated user
     */
    private function getTrainerId()
    {
        // Ambil trainer_id dari user yang sedang login
        $trainerId = Auth::user()->trainer_id;
        
        if (!$trainerId) {
            throw new \Exception('User ini tidak memiliki data trainer');
        }
        
        return $trainerId;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $trainerId = $this->getTrainerId();
            // Ambil playlist berdasarkan trainer yang login
            $playlists = PlaylistTrainer::where('id_trainer', $trainerId)->get();
            return view('pages.trainer.playlist.index', compact('playlists'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('danger', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'latihan' => 'required|string|max:255',
        ]);

        try {
            $trainerId = $this->getTrainerId();
            
            PlaylistTrainer::create([
                'id_trainer' => $trainerId,
                'latihan' => $request->latihan,
            ]);

            return redirect()->route('trainerplaylist.index')
                ->with('success', 'Playlist berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('danger', 'Gagal menambahkan playlist: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'latihan' => 'required|string|max:255',
        ]);

        try {
            $trainerId = $this->getTrainerId();
            
            $playlist = PlaylistTrainer::where('id', $id)
                ->where('id_trainer', $trainerId)
                ->firstOrFail();

            $playlist->update([
                'latihan' => $request->latihan,
            ]);

            return redirect()->route('trainerplaylist.index')
                ->with('success', 'Playlist berhasil diupdate!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('danger', 'Gagal mengupdate playlist: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $trainerId = $this->getTrainerId();
            
            $playlist = PlaylistTrainer::where('id', $id)
                ->where('id_trainer', $trainerId)
                ->firstOrFail();

            $playlist->delete();

            return redirect()->route('trainerplaylist.index')
                ->with('success', 'Playlist berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('danger', 'Gagal menghapus playlist: ' . $e->getMessage());
        }
    }
}
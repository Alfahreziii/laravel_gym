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

    public function datatable(Request $request)
    {
        try {
            $trainerId = $this->getTrainerId();
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'total' => 0, 'perPage' => 10, 'page' => 1, 'lastPage' => 1]);
        }

        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = PlaylistTrainer::where('id_trainer', $trainerId);
        if ($search) {
            $query->where('latihan', 'like', "%{$search}%");
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->orderBy('latihan')->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage) {
                return [
                    'no'         => (($page - 1) * $perPage) + $index + 1,
                    'id'         => $item->id,
                    'latihan'    => $item->latihan,
                    'update_url' => route('trainerplaylist.update', $item->id),
                    'delete_url' => route('trainerplaylist.destroy', $item->id),
                ];
            }),
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'lastPage' => max(1, ceil($total / $perPage)),
        ]);
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
<?php

namespace App\Http\Controllers\Trainer;

use App\Models\LevelTrainer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class LevelTrainerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $levels = LevelTrainer::all();
        return view('pages.trainer.level-trainer.index', compact('levels'));
    }

    public function datatable(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('perPage', 10);
        $page    = (int) $request->get('page', 1);

        $query = LevelTrainer::query();
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $total = (clone $query)->count();
        $data  = (clone $query)->orderBy('name')->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $data->map(function ($item, $index) use ($page, $perPage) {
                return [
                    'no'         => (($page - 1) * $perPage) + $index + 1,
                    'id'         => $item->id,
                    'name'       => $item->name,
                    'update_url' => route('level_trainer.update', $item->id),
                    'delete_url' => route('level_trainer.destroy', $item->id),
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
            'name' => 'required|string|max:100|unique:level_trainers,name',
        ], [
            'name.required' => 'Nama level harus diisi',
            'name.unique' => 'Nama level sudah digunakan',
        ]);

        DB::beginTransaction();
        try {
            LevelTrainer::create([
                'name' => $request->name,
            ]);

            DB::commit();
            return redirect()->route('level_trainer.index')
                ->with('success', 'Level Trainer berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambahkan level trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->with('danger', 'Gagal menambahkan level. Silakan coba lagi atau hubungi admin.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:level_trainers,name,' . $id,
        ], [
            'name.required' => 'Nama level harus diisi',
            'name.unique' => 'Nama level sudah digunakan',
        ]);

        DB::beginTransaction();
        try {
            $level = LevelTrainer::findOrFail($id);
            $level->update([
                'name' => $request->name,
            ]);

            DB::commit();
            return redirect()->route('level_trainer.index')
                ->with('success', 'Level Trainer berhasil diupdate!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal mengupdate level trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->with('danger', 'Gagal mengupdate level. Silakan coba lagi atau hubungi admin.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $level = LevelTrainer::findOrFail($id);
            
            // Cek apakah level sedang digunakan
            if ($level->settingGaji()->count() > 0) {
                return redirect()->back()
                    ->with('danger', 'Level tidak dapat dihapus karena sedang digunakan!');
            }

            $level->delete();

            DB::commit();
            return redirect()->route('level_trainer.index')
                ->with('success', 'Level Trainer berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus level trainer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->with('danger', 'Gagal menghapus level. Silakan coba lagi atau hubungi admin.');
        }
    }
}
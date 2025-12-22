<?php

namespace App\Http\Controllers\Trainer;

use App\Models\LevelTrainer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class LevelTrainerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $levels = LevelTrainer::all();
        return view('pages.trainer.leveltrainer.index', compact('levels'));
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
            return redirect()->back()
                ->with('danger', 'Gagal menambahkan level: ' . $e->getMessage());
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
            return redirect()->back()
                ->with('danger', 'Gagal mengupdate level: ' . $e->getMessage());
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
            return redirect()->back()
                ->with('danger', 'Gagal menghapus level: ' . $e->getMessage());
        }
    }
}
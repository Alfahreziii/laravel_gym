<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SettingParameterGajiTrainer;
use App\Models\Trainer;
use App\Models\LevelTrainer;
use Illuminate\Support\Facades\DB;

class GajiTrainerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gajiTrainers = SettingParameterGajiTrainer::with(['trainer.user', 'level'])
            ->get();
        
        return view('pages.trainer.gajitrainer.index', compact('gajiTrainers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil trainer yang belum ada setting gaji
        $trainers = Trainer::whereDoesntHave('settingGaji')
            ->with('user')
            ->where('status', Trainer::STATUS_AKTIF)
            ->get();
        
        $levels = LevelTrainer::all();
        
        return view('pages.trainer.gajitrainer.create', compact('trainers', 'levels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_trainer' => 'required|exists:trainers,id|unique:setting_parameter_gaji_trainers,id_trainer',
            'id_level' => 'required|exists:level_trainers,id',
            'base_rate' => 'required|numeric|min:0',
            'tgl_gajian' => 'required|date',
        ], [
            'id_trainer.required' => 'Trainer harus dipilih',
            'id_trainer.unique' => 'Trainer sudah memiliki setting gaji',
            'id_level.required' => 'Level harus dipilih',
            'base_rate.required' => 'Base rate harus diisi',
            'base_rate.numeric' => 'Base rate harus berupa angka',
            'tgl_gajian.required' => 'Tanggal gajian harus diisi',
        ]);

        DB::beginTransaction();
        try {
            SettingParameterGajiTrainer::create([
                'id_trainer' => $request->id_trainer,
                'id_level' => $request->id_level,
                'base_rate' => $request->base_rate,
                'tgl_gajian' => $request->tgl_gajian,
            ]);

            DB::commit();
            return redirect()->route('gaji_trainer.index')
                ->with('success', 'Setting gaji trainer berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', 'Gagal menambahkan setting gaji: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $gajiTrainer = SettingParameterGajiTrainer::with(['trainer.user', 'level'])
            ->findOrFail($id);
        
        $levels = LevelTrainer::all();
        
        return view('pages.trainer.gajitrainer.edit', compact('gajiTrainer', 'levels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_level' => 'required|exists:level_trainers,id',
            'base_rate' => 'required|numeric|min:0',
            'tgl_gajian' => 'required|date',
        ], [
            'id_level.required' => 'Level harus dipilih',
            'base_rate.required' => 'Base rate harus diisi',
            'base_rate.numeric' => 'Base rate harus berupa angka',
            'tgl_gajian.required' => 'Tanggal gajian harus diisi',
        ]);

        DB::beginTransaction();
        try {
            $gajiTrainer = SettingParameterGajiTrainer::findOrFail($id);
            
            $gajiTrainer->update([
                'id_level' => $request->id_level,
                'base_rate' => $request->base_rate,
                'tgl_gajian' => $request->tgl_gajian,
            ]);

            DB::commit();
            return redirect()->route('gaji_trainer.index')
                ->with('success', 'Setting gaji trainer berhasil diupdate!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', 'Gagal mengupdate setting gaji: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $gajiTrainer = SettingParameterGajiTrainer::findOrFail($id);
            $gajiTrainer->delete();

            DB::commit();
            return redirect()->route('gaji_trainer.index')
                ->with('success', 'Setting gaji trainer berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', 'Gagal menghapus setting gaji: ' . $e->getMessage());
        }
    }
}
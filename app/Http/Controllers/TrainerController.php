<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use App\Models\Specialisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrainerController extends Controller
{
    /**
     * Tampilkan daftar trainer.
     */
    public function index()
    {
        $trainers = Trainer::with('specialisasi')->latest()->paginate(10);
        return view('pages.trainer.index', compact('trainers'));
    }

    /**
     * Form tambah trainer.
     */
    public function create()
    {
        $specialisasis = Specialisasi::all();
        return view('pages.trainer.create', compact('specialisasis'));
    }

    /**
     * Simpan data trainer baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_specialisasi' => 'required|exists:specialisasis,id',
            'rfid'            => 'required|unique:trainers,rfid|max:30',
            'photo'           => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'name'            => 'required|string|max:255',
            'no_telp'         => 'required|string|max:50',
            'experience'      => 'required|string|max:100',
            'tgl_gabung'      => 'required|date',
            'status'          => 'required|string|max:20',
            'keterangan'      => 'nullable|string|max:100',
            'tempat_lahir'    => 'required|string',
            'tgl_lahir'       => 'required|date',
            'jenis_kelamin'   => 'required|string|max:20',
            'alamat'          => 'required|string',

            // Validasi jadwal
            'jadwal.*.day_of_week'       => 'required|string',
            'jadwal.*.start_time'  => 'required',
            'jadwal.*.end_time'=> 'required',
        ]);

        $photoPath = $request->file('photo')->store('trainers', 'public');

        $trainer = Trainer::create([
            'id_specialisasi' => $request->id_specialisasi,
            'rfid'            => $request->rfid,
            'photo'           => $photoPath,
            'name'            => $request->name,
            'no_telp'         => $request->no_telp,
            'experience'      => $request->experience,
            'tgl_gabung'      => $request->tgl_gabung,
            'status'          => $request->status,
            'keterangan'      => $request->keterangan,
            'tempat_lahir'    => $request->tempat_lahir,
            'tgl_lahir'       => $request->tgl_lahir,
            'jenis_kelamin'   => $request->jenis_kelamin,
            'alamat'          => $request->alamat,
        ]);

        // Simpan jadwal
        if($request->has('jadwal')){
            foreach($request->jadwal as $j){
                $trainer->schedules()->create([
                    'day_of_week'       => $j['day_of_week'],
                    'start_time'  => $j['start_time'],
                    'end_time'=> $j['end_time'],
                ]);
            }
        }

        return redirect()->route('trainer.index')->with('success','Trainer berhasil ditambahkan.');
    }

    /**
     * Form edit trainer.
     */
    public function edit(Trainer $trainer)
    {
        $specialisasis = Specialisasi::all();
        return view('pages.trainer.edit', compact('trainer', 'specialisasis'));
    }
    
    /**
     * Tampilkan detail trainer.
     */
    public function show(Trainer $trainer)
    {
        // Load relasi specialisasi dan jadwal
        $trainer->load('specialisasi', 'schedules');

        return view('pages.trainer.show', compact('trainer'));
    }

    /**
     * Update data trainer.
     */
    public function update(Request $request, Trainer $trainer)
    {
        $request->validate([
            'id_specialisasi' => 'required|exists:specialisasis,id',
            'rfid'            => 'required|max:30|unique:trainers,rfid,' . $trainer->id,
            'photo'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'name'            => 'required|string|max:255',
            'no_telp'         => 'required|string|max:50',
            'experience'      => 'required|string|max:100',
            'tgl_gabung'      => 'required|date',
            'status'          => 'required|string|max:20',
            'keterangan'      => 'nullable|string|max:100',
            'tempat_lahir'    => 'required|string',
            'tgl_lahir'       => 'required|date',
            'jenis_kelamin'   => 'required|string|max:20',
            'alamat'          => 'required|string',

            // Validasi jadwal
            'jadwal.*.day_of_week'       => 'required|string',
            'jadwal.*.start_time'  => 'required',
            'jadwal.*.end_time'=> 'required',
        ]);

        $data = $request->only([
            'id_specialisasi','rfid','name','no_telp','experience','tgl_gabung','status',
            'keterangan','tempat_lahir','tgl_lahir','jenis_kelamin','alamat'
        ]);

        if($request->hasFile('photo')){
            $data['photo'] = $request->file('photo')->store('trainers', 'public');
        }

        $trainer->update($data);

        // Update jadwal: hapus dulu lalu buat baru
        $trainer->schedules()->delete();
        if($request->has('jadwal')){
            foreach($request->jadwal as $j){
                $trainer->schedules()->create([
                    'day_of_week'       => $j['day_of_week'],
                    'start_time'  => $j['start_time'],
                    'end_time'=> $j['end_time'],
                ]);
            }
        }

        return redirect()->route('trainer.index')->with('success','Trainer berhasil diperbarui.');
    }

    /**
     * Hapus data trainer.
     */
    public function destroy(Trainer $trainer)
    {
        // Hapus photo dari storage jika ada
        if ($trainer->photo && Storage::disk('public')->exists($trainer->photo)) {
            Storage::disk('public')->delete($trainer->photo);
        }

        // Hapus jadwal terkait (jika perlu)
        $trainer->schedules()->delete();

        // Hapus record trainer
        $trainer->delete();

        return redirect()->route('trainer.index')->with('success', 'Trainer berhasil dihapus.');
    }
}

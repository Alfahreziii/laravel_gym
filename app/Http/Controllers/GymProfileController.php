<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GymProfileController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');
        $tenant->loadMissing('package');

        return view('pages.admin.gym-profil.index', compact('tenant'));
    }

    public function update(Request $request)
    {
        $tenant = app('tenant');

        $request->validate([
            'nama_gym' => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
            'no_hp'    => ['required', 'string', 'max:20'],
            'alamat'   => ['nullable', 'string', 'max:1000'],
            'logo'     => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $data = $request->only(['nama_gym', 'email', 'no_hp', 'alamat']);

        if ($request->hasFile('logo')) {
            if ($tenant->logo && Storage::disk('public')->exists($tenant->logo)) {
                Storage::disk('public')->delete($tenant->logo);
            }
            $data['logo'] = $request->file('logo')->store(tenant_storage_path('logo'), 'public');
        }

        $tenant->update($data);

        return redirect()->route('gym_profile.index')->with('success', 'Profil gym berhasil diperbarui.');
    }
}

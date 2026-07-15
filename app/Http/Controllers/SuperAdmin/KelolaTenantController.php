<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Master\Package;
use App\Models\Master\Tenant;
use Illuminate\Http\Request;

class KelolaTenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with(['package', 'module', 'databasePool', 'backups'])
                         ->orderByDesc('created_at')
                         ->get();

        return view('superadmin.kelola-tenant.index', compact('tenants'));
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['package', 'module', 'databasePool']);
        $packages = Package::orderBy('nama')->get();

        return view('superadmin.kelola-tenant.show', compact('tenant', 'packages'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        if ($tenant->status === 'archived') {
            return back()->withErrors(['_general' => "Tenant yang sudah diarsipkan tidak dapat diedit."]);
        }

        $request->validate([
            'nama_gym'    => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255'],
            'no_hp'       => ['required', 'string', 'max:20'],
            'alamat'      => ['nullable', 'string', 'max:1000'],
            'status'      => ['required', 'in:aktif,nonaktif,suspend'],
            'tgl_mulai'   => ['required', 'date'],
            'tgl_selesai' => ['required', 'date', 'after:tgl_mulai'],
        ]);

        $tenant->update([
            'nama_gym'    => $request->nama_gym,
            'email'       => $request->email,
            'no_hp'       => $request->no_hp,
            'alamat'      => $request->alamat,
            'status'      => $request->status,
            'tgl_mulai'   => $request->tgl_mulai,
            'tgl_selesai' => $request->tgl_selesai,
        ]);

        return redirect()
            ->route('super_admin.kelola_tenant.show', $tenant)
            ->with('success', "Data \"{$tenant->nama_gym}\" berhasil diperbarui.");
    }

    public function updateModules(Request $request, Tenant $tenant)
    {
        if ($tenant->status === 'archived') {
            return back()->withErrors(['_general' => "Tenant yang sudah diarsipkan tidak dapat diedit."]);
        }

        $package = Package::find($request->integer('package_id'));
        if (! $package) {
            return back()->withInput()->withErrors(['package_id' => 'Paket tidak valid.']);
        }

        $tenant->update(['package_id' => $package->id]);

        $tenant->module()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'trainer'  => $package->trainer,
                'pos'      => $package->pos,
                'keuangan' => $package->keuangan,
            ]
        );

        return redirect()
            ->route('super_admin.kelola_tenant.show', $tenant)
            ->with('success', "Paket \"{$package->nama}\" berhasil diterapkan ke {$tenant->nama_gym}.");
    }
}

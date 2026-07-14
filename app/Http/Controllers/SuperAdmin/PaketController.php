<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Master\Package;

class PaketController extends Controller
{
    public function index()
    {
        $packages = Package::withCount('tenants')->orderBy('nama')->get();

        return view('superadmin.paket', compact('packages'));
    }
}

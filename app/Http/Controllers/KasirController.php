<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AlatGym;

class KasirController extends Controller
{
    public function index()
    {
        $alatGyms = AlatGym::latest()->paginate(10);
        return view('pages.kasir.index', compact('alatGyms'));
    }
}

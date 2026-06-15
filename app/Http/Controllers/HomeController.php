<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    /**
     * Halaman fallback untuk route/page yang tidak ditemukan.
     */
    public function pageError()
    {
        return view('pageError');
    }
}

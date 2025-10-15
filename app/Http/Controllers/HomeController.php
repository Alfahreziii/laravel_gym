<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
        public function pageError()
        {
            return view('pageError');
        }
}

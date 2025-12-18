<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DokterDashboardController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('dokter.antrian.index');
    }
}

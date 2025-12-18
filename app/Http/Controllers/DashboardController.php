<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $totalPasien = DB::table('patients')->count();

        $totalAntrianToday = Antrian::whereDate('tanggal_antrian', $today)->count();
        $calledToday = Antrian::whereDate('tanggal_antrian', $today)->where('is_call', 1)->count();
        $remainingToday = Antrian::whereDate('tanggal_antrian', $today)->where('is_call', 0)->count();

        $rawPerPoli = Antrian::whereDate('tanggal_antrian', $today)
            ->select('poli', DB::raw('COUNT(*) AS total'))
            ->groupBy('poli')
            ->pluck('total', 'poli')
            ->toArray();

        $perPoliToday = [
            'umum' => $rawPerPoli['umum'] ?? 0,
            'gigi' => $rawPerPoli['gigi'] ?? 0,
            'tht' => $rawPerPoli['tht'] ?? 0,
            'lansia_disabilitas' => $rawPerPoli['lansia & disabilitas'] ?? 0,
            'balita' => $rawPerPoli['balita'] ?? 0,
            'kia_kb' => $rawPerPoli['kia & kb'] ?? 0,
            'nifas_pnc' => $rawPerPoli['nifas/pnc'] ?? 0,
        ];

        return view('dashboard.index', compact(
            'totalPasien',
            'totalAntrianToday',
            'calledToday',
            'remainingToday',
            'perPoliToday'
        ));
    }
}

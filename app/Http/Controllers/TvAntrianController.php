<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TvAntrianController extends Controller
{
    private function normStatus($row): string
    {
        // kalau kolom status ada, pakai itu
        $s = strtolower(trim((string)($row->status ?? '')));

        // fallback kalau status kosong: pakai is_call
        if ($s === '') {
            $s = ((int)($row->is_call ?? 0) === 1) ? 'dipanggil' : 'menunggu';
        }

        if ($s === 'lewat') $s = 'dilewati';
        return $s;
    }

    public function index()
    {
        $today = now()->toDateString();

        $hasStatus = Schema::hasColumn('antrians', 'status');

        if ($hasStatus) {
            // current = yang dipanggil / dilayani (bukan selesai)
            $current = Antrian::whereDate('tanggal_antrian', $today)
                ->whereIn('status', ['dipanggil', 'dilayani'])
                ->orderByDesc('called_at')
                ->orderByDesc('updated_at')
                ->first();

            // next = yang menunggu
            $next = Antrian::whereDate('tanggal_antrian', $today)
                ->where('status', 'menunggu')
                ->orderBy('id')
                ->take(5)
                ->get();
        } else {
            // fallback sistem lama
            $current = Antrian::where('tanggal_antrian', $today)
                ->where('is_call', 1)
                ->orderBy('updated_at', 'desc')
                ->first();

            $next = Antrian::where('tanggal_antrian', $today)
                ->where('is_call', 0)
                ->orderBy('no_antrian', 'asc')
                ->take(5)
                ->get();
        }

        return view('tv.index', compact('current', 'next'));
    }

    // endpoint JSON untuk TV (tanpa reload)
    public function data()
    {
        $today = now()->toDateString();
        $hasStatus = Schema::hasColumn('antrians', 'status');

        if ($hasStatus) {
            $current = Antrian::whereDate('tanggal_antrian', $today)
                ->whereIn('status', ['dipanggil', 'dilayani'])
                ->orderByDesc('called_at')
                ->orderByDesc('updated_at')
                ->first();

            $next = Antrian::whereDate('tanggal_antrian', $today)
                ->where('status', 'menunggu')
                ->orderBy('id')
                ->take(5)
                ->get();
        } else {
            $current = Antrian::where('tanggal_antrian', $today)
                ->where('is_call', 1)
                ->orderBy('updated_at', 'desc')
                ->first();

            $next = Antrian::where('tanggal_antrian', $today)
                ->where('is_call', 0)
                ->orderBy('no_antrian', 'asc')
                ->take(5)
                ->get();
        }

        return response()->json([
            'current' => $current ? [
                'no_antrian' => $current->no_antrian,
                'poli'       => $current->poli,
                'status'     => $this->normStatus($current),

                // 🔥 ini kunci biar PANGGIL ULANG memicu suara walau nomor sama
                'called_at'  => optional($current->called_at)->toIso8601String(),
                'updated_at' => optional($current->updated_at)->toIso8601String(),
            ] : null,

            'next' => $next->map(function ($row) {
                return [
                    'no_antrian' => $row->no_antrian,
                    'poli'       => $row->poli,
                    'status'     => $this->normStatus($row),
                ];
            })->values(),
        ]);
    }
}

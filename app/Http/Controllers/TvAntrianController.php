<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Support\Facades\Schema;

class TvAntrianController extends Controller
{
    private function normStatus($row): string
    {
        $s = strtolower(trim((string)($row->status ?? '')));

        if ($s === '') {
            $s = ((int)($row->is_call ?? 0) === 1) ? 'dipanggil' : 'menunggu';
        }

        if ($s === 'lewat') $s = 'dilewati';
        return $s;
    }

    private function tvCurrentQuery(string $today)
    {
        // status yang boleh tampil di TV sebagai "sedang dipanggil"
        return Antrian::query()
            ->whereDate('tanggal_antrian', $today)
            ->whereIn('status', ['dipanggil', 'dilayani'])
            // ✅ urutkan berdasarkan updated_at supaya PANGGIL ULANG otomatis naik ke atas
            ->orderByDesc('updated_at');
    }

    private function tvNextQuery(string $today)
    {
        // status yang boleh tampil sebagai "antrian berikutnya"
        // (menunggu saja)
        return Antrian::query()
            ->whereDate('tanggal_antrian', $today)
            ->where('status', 'menunggu')
            ->orderBy('no_antrian')
            ->orderBy('id');
    }

    public function index()
    {
        $today = now()->toDateString();
        $hasStatus = Schema::hasColumn('antrians', 'status');

        if ($hasStatus) {
            $current = $this->tvCurrentQuery($today)->first();
            $next    = $this->tvNextQuery($today)->take(5)->get();
        } else {
            // fallback sistem lama (tanpa status)
            $current = Antrian::whereDate('tanggal_antrian', $today)
                ->where('is_call', 1)
                ->orderByDesc('updated_at')
                ->first();

            $next = Antrian::whereDate('tanggal_antrian', $today)
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
            $current = $this->tvCurrentQuery($today)->first();
            $next    = $this->tvNextQuery($today)->take(5)->get();
        } else {
            $current = Antrian::whereDate('tanggal_antrian', $today)
                ->where('is_call', 1)
                ->orderByDesc('updated_at')
                ->first();

            $next = Antrian::whereDate('tanggal_antrian', $today)
                ->where('is_call', 0)
                ->orderBy('no_antrian', 'asc')
                ->take(5)
                ->get();
        }

        return response()->json([
            'current' => $current ? [
                'id'        => $current->id,
                'no_antrian'=> $current->no_antrian,
                'poli'      => $current->poli,
                'status'    => $this->normStatus($current),

                // ✅ kunci trigger suara / perubahan:
                // pakai updated_at (selalu berubah kalau panggil ulang)
                'called_key' => optional($current->updated_at)->toIso8601String(),

                // OPTIONAL kalau kamu nanti punya kolom called_at:
                // 'called_at'  => optional($current->called_at)->toIso8601String(),
                'updated_at' => optional($current->updated_at)->toIso8601String(),
            ] : null,

            'next' => $next->map(function ($row) {
                return [
                    'id'        => $row->id,
                    'no_antrian'=> $row->no_antrian,
                    'poli'      => $row->poli,
                    'status'    => $this->normStatus($row),
                ];
            })->values(),
        ]);
    }
}

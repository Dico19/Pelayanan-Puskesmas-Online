<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Support\Facades\Schema;

class TvAntrianController extends Controller
{
    private bool $hasStatus = false;
    private bool $hasCalledAt = false;

    public function __construct()
    {
        try {
            $this->hasStatus   = Schema::hasColumn('antrians', 'status');
            $this->hasCalledAt = Schema::hasColumn('antrians', 'called_at');
        } catch (\Throwable $e) {
            $this->hasStatus = false;
            $this->hasCalledAt = false;
        }
    }

    private function normStatus($row): string
    {
        $s = strtolower(trim((string)($row->status ?? '')));

        if ($s === '') {
            $s = ((int)($row->is_call ?? 0) === 1) ? 'dipanggil' : 'menunggu';
        }

        if ($s === 'lewat') $s = 'dilewati';
        if ($s === 'tidak hadir' || $s === 'tidak-hadir') $s = 'tidak_hadir';

        return $s;
    }

    private function tvCurrentQuery(string $today)
    {
        // "Sedang dipanggil" di TV: hanya dipanggil / dilayani
        $q = Antrian::query()
            ->whereDate('tanggal_antrian', $today)
            ->whereNotNull('no_antrian');

        if ($this->hasStatus) {
            $q->whereIn('status', ['dipanggil', 'dilayani']);
        } else {
            // fallback sistem lama (tanpa status)
            $q->where('is_call', 1);
        }

        // Prioritaskan called_at kalau ada (lebih akurat untuk TV)
        if ($this->hasCalledAt) {
            $q->orderByDesc('called_at');
        }

        // Fallback / tie-breaker
        return $q->orderByDesc('updated_at')
                 ->orderByDesc('id');
    }

    private function tvNextQuery(string $today)
    {
        // "Nomor berikutnya": menunggu saja
        $q = Antrian::query()
            ->whereDate('tanggal_antrian', $today)
            ->whereNotNull('no_antrian');

        if ($this->hasStatus) {
            $q->where('status', 'menunggu');
        } else {
            $q->where('is_call', 0);
        }

        return $q->orderBy('no_antrian')
                 ->orderBy('id');
    }

    public function index()
    {
        $today = now()->toDateString();

        $current = $this->tvCurrentQuery($today)->first();
        $next    = $this->tvNextQuery($today)->take(5)->get();

        return view('tv.index', compact('current', 'next'));
    }

    // endpoint JSON untuk TV (tanpa reload)
    public function data()
    {
        $today = now()->toDateString();

        $current = $this->tvCurrentQuery($today)->first();
        $next    = $this->tvNextQuery($today)->take(5)->get();

        return response()->json([
            'current' => $current ? [
                'id'         => $current->id,
                'no_antrian' => $current->no_antrian,
                'poli'       => $current->poli,
                'status'     => $this->normStatus($current),

                // ✅ trigger suara/perubahan: kalau ada called_at pakai itu, kalau tidak pakai updated_at
                'called_key' => $this->hasCalledAt
                    ? optional($current->called_at)->toIso8601String()
                    : optional($current->updated_at)->toIso8601String(),

                'called_at'  => $this->hasCalledAt ? optional($current->called_at)->toIso8601String() : null,
                'updated_at' => optional($current->updated_at)->toIso8601String(),
            ] : null,

            'next' => $next->map(function ($row) {
                return [
                    'id'         => $row->id,
                    'no_antrian' => $row->no_antrian,
                    'poli'       => $row->poli,
                    'status'     => $this->normStatus($row),
                ];
            })->values(),
        ]);
    }
}

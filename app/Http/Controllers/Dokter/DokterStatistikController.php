<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DokterStatistikController extends Controller
{
    public function index()
    {
        return view('dokter.statistik.index');
    }

    /**
     * Endpoint JSON untuk statistik (dipanggil JS / realtime)
     */
    public function data(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $date = Carbon::parse($date)->toDateString();

        // Ambil poli dokter dari role (contoh: dokter_umum, dokter_gigi, dst)
        $user = auth()->user();
        $role = '';

        if (method_exists($user, 'getRoleNames')) {
            $role = (string) ($user->getRoleNames()->first() ?? '');
        } else {
            $role = (string) (data_get($user, 'role.role') ?? data_get($user, 'role') ?? '');
        }

        $role = strtolower(trim($role));
        $poliKey = str_starts_with($role, 'dokter_') ? substr($role, 7) : $role; // buang "dokter_"

        // Normalisasi poli biar cocok sama DB kamu (umum, gigi, tht, balita, kia & kb, nifas/pnc, lansia & disabilitas)
        $map = [
            'umum' => 'umum',
            'gigi' => 'gigi',
            'tht'  => 'tht',
            'balita' => 'balita',
            'kia_kb' => 'kia & kb',
            'kia' => 'kia & kb',
            'kb'  => 'kia & kb',
            'nifas_pnc' => 'nifas/pnc',
            'nifas' => 'nifas/pnc',
            'pnc'   => 'nifas/pnc',
            'lansia_disabilitas' => 'lansia & disabilitas',
            'lansia' => 'lansia & disabilitas',
            'disabilitas' => 'lansia & disabilitas',
        ];

        $poliDb = $map[$poliKey] ?? $poliKey;

        $base = Antrian::query()
            ->whereDate('tanggal_antrian', $date)
            ->where(DB::raw('LOWER(poli)'), strtolower($poliDb));

        // total semua antrian hari itu untuk poli tsb
        $total = (clone $base)->count();

        // Normalisasi status lama: kalau status kosong/null, pakai is_call
        $menunggu = (clone $base)
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', '')
                  ->orWhere(DB::raw('LOWER(status)'), 'menunggu');
            })
            ->where('is_call', 0)
            ->count();

        $dipanggil = (clone $base)
            ->where(function ($q) {
                $q->where(DB::raw('LOWER(status)'), 'dipanggil')
                  ->orWhere('is_call', 1);
            })
            ->count();

        $dilayani = (clone $base)
            ->where(DB::raw('LOWER(status)'), 'dilayani')
            ->count();

        $selesai = (clone $base)
            ->where(DB::raw('LOWER(status)'), 'selesai')
            ->count();

        $tidakHadir = (clone $base)
            ->whereIn(DB::raw('LOWER(status)'), ['tidak_hadir', 'tidak hadir', 'tidak-hadir'])
            ->count();

        return response()->json([
            'ok' => true,
            'date' => $date,
            'poli' => $poliDb,
            'total' => $total,
            'menunggu' => $menunggu,
            'dipanggil' => $dipanggil,
            'dilayani' => $dilayani,
            'selesai' => $selesai,
            'tidak_hadir' => $tidakHadir,
        ]);
    }
}

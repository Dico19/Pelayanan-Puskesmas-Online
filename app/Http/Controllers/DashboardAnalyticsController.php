<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardAnalyticsController extends Controller
{
    private function normPoli(?string $v): string
    {
        $v = strtolower(trim((string) $v));
        return $v;
    }

    private function applyPoliFilter($query, ?string $poli)
    {
        $poli = $this->normPoli($poli);
        if ($poli === '' || $poli === 'all') return $query;

        // case-insensitive
        return $query->whereRaw('LOWER(poli) = ?', [$poli]);
    }

    public function index(Request $request)
    {
        // ====== FILTER INPUT ======
        // contoh:
        // /admin/analytics?date=2025-12-20
        // /admin/analytics?date=2025-12-20&poli=balita
        $selectedPoli = $request->get('poli', 'all');
        $dateStr      = $request->get('date');

        // default: hari ini
        $selectedDate = $dateStr
            ? Carbon::parse($dateStr)->startOfDay()
            : Carbon::today();

        $dayStart = $selectedDate->copy()->startOfDay();
        $dayEnd   = $selectedDate->copy()->endOfDay();

        // label untuk UI
        Carbon::setLocale('id');
        $tanggalLabel = $selectedDate->translatedFormat('d M Y');

        // ====== LIST POLI (untuk dropdown di blade) ======
        $poliOptions = DB::table('antrians')
            ->select('poli')
            ->whereNotNull('poli')
            ->whereRaw("TRIM(poli) <> ''")
            ->distinct()
            ->orderBy('poli')
            ->pluck('poli')
            ->values()
            ->toArray();

        // ====== BASE QUERY: berdasarkan tanggal_antrian (bukan created_at) ======
        $baseDay = DB::table('antrians')
            ->whereBetween('tanggal_antrian', [$dayStart->toDateString(), $dayEnd->toDateString()]);

        // versi yang terfilter poli (untuk angka-angka utama)
        $dayFiltered = DB::table('antrians')
            ->whereBetween('tanggal_antrian', [$dayStart->toDateString(), $dayEnd->toDateString()]);
        $dayFiltered = $this->applyPoliFilter($dayFiltered, $selectedPoli);

        // ====== KPI UTAMA (mengikuti filter poli) ======
        $totalToday = (clone $dayFiltered)->count();

        $uniquePatientsToday = (clone $dayFiltered)
            ->whereNotNull('patient_id')
            ->distinct('patient_id')
            ->count('patient_id');

        // jumlah poli aktif pada tanggal itu (tanpa filter poli biar tetap bermakna)
        $activePoliToday = (clone $baseDay)
            ->distinct('poli')
            ->count('poli');

        // ====== 1) JUMLAH PASIEN PER POLI (untuk chart per poli) ======
        // Ini sengaja *TIDAK* mengikuti filter poli, supaya chart "per poli" masih berguna sebagai perbandingan.
        $perPoli = (clone $baseDay)
            ->select('poli', DB::raw('COUNT(*) as total'))
            ->groupBy('poli')
            ->orderBy('poli')
            ->get();

        // ====== 2) JAM KUNJUNGAN TERSIBUK (mengikuti filter poli) ======
        // Pakai created_at untuk jam; tapi dibatasi tanggal_antrian yang dipilih.
        $perJam = (clone $dayFiltered)
            ->whereNotNull('created_at')
            ->select(DB::raw('HOUR(created_at) as jam'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('jam')
            ->get();

        $busiestHour = null;
        if ($perJam->isNotEmpty()) {
            $max = $perJam->sortByDesc('total')->first();
            $busiestHour = str_pad((string) $max->jam, 2, '0', STR_PAD_LEFT) . ':00';
        }

        // ====== 3) RATA-RATA WAKTU TUNGGU (mengikuti filter poli) ======
        // Prioritas: kalau ada called_at pakai itu (lebih akurat). Kalau tidak ada, fallback updated_at.
        $hasCalledAt = Schema::hasColumn('antrians', 'called_at');

        if ($hasCalledAt) {
            $avgWait = (clone $dayFiltered)
                ->where('is_call', 1)
                ->whereNotNull('created_at')
                ->whereNotNull('called_at')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, called_at)) as avg_wait'))
                ->value('avg_wait');
        } else {
            $avgWait = (clone $dayFiltered)
                ->where('is_call', 1)
                ->whereNotNull('created_at')
                ->whereNotNull('updated_at')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_wait'))
                ->value('avg_wait');
        }

        $avgWait = round($avgWait ?? 0, 1);

        // ====== 4) TREN HARIAN (7 hari terakhir, ending di selectedDate) ======
        $trendStart = $selectedDate->copy()->subDays(6)->startOfDay();
        $trendEnd   = $selectedDate->copy()->endOfDay();

        $dailyTrendQ = DB::table('antrians')
            ->select(DB::raw('DATE(tanggal_antrian) as tanggal'), DB::raw('COUNT(*) as total'))
            ->whereBetween('tanggal_antrian', [$trendStart->toDateString(), $trendEnd->toDateString()]);

        $dailyTrendQ = $this->applyPoliFilter($dailyTrendQ, $selectedPoli);

        $dailyTrend = $dailyTrendQ
            ->groupBy(DB::raw('DATE(tanggal_antrian)'))
            ->orderBy('tanggal')
            ->get();

        // ====== 5) TREN BULANAN (6 bulan terakhir, ending di selectedDate) ======
        $monthStart = $selectedDate->copy()->subMonths(5)->startOfMonth();
        $monthEnd   = $selectedDate->copy()->endOfMonth();

        $monthlyTrendQ = DB::table('antrians')
            ->select(
                DB::raw('DATE_FORMAT(tanggal_antrian, "%Y-%m") as bulan'),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('tanggal_antrian', [$monthStart->toDateString(), $monthEnd->toDateString()]);

        $monthlyTrendQ = $this->applyPoliFilter($monthlyTrendQ, $selectedPoli);

        $monthlyTrend = $monthlyTrendQ
            ->groupBy(DB::raw('DATE_FORMAT(tanggal_antrian, "%Y-%m")'))
            ->orderBy('bulan')
            ->get();

        return view('dashboard.analytics.index', [
            // data lama (tetap agar blade kamu tidak error)
            'perPoli'             => $perPoli,
            'perJam'              => $perJam,
            'avgWait'             => $avgWait,
            'dailyTrend'          => $dailyTrend,
            'monthlyTrend'        => $monthlyTrend,
            'totalToday'          => $totalToday,
            'uniquePatientsToday' => $uniquePatientsToday,
            'activePoliToday'     => $activePoliToday,
            'busiestHour'         => $busiestHour,

            // tambahan untuk filter UI
            'poliOptions'         => $poliOptions,
            'selectedPoli'        => $selectedPoli,
            'selectedDate'        => $selectedDate->toDateString(),
            'tanggalLabel'        => $tanggalLabel,
        ]);
    }
}

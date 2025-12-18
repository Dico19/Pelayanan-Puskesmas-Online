<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardRekapController extends Controller
{
    private function normalizeRange(Request $request): array
    {
        $tipe = $request->get('rekap_tipe', 'today'); // today|week|month|custom
        $today = Carbon::today();

        if ($tipe === 'week') {
            $from = $today->copy()->startOfWeek();
            $to   = $today->copy()->endOfWeek();
        } elseif ($tipe === 'month') {
            $from = $today->copy()->startOfMonth();
            $to   = $today->copy()->endOfMonth();
        } elseif ($tipe === 'custom') {
            $from = Carbon::parse($request->get('rekap_from', $today->toDateString()));
            $to   = Carbon::parse($request->get('rekap_to', $today->toDateString()));
        } else {
            $from = $today->copy();
            $to   = $today->copy();
        }

        // jaga-jaga kalau kebalik inputnya
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->toDateString(), $to->toDateString(), $tipe];
    }

    private function baseQuery(string $from, string $to, string $poli = '')
    {
        $q1 = DB::table('antrians')
            ->select('poli', 'is_call', 'tanggal_antrian');

        $q2 = DB::table('riwayat_antrians')
            ->select('poli', 'is_call', 'tanggal_antrian');

        $union = $q1->unionAll($q2);

        $query = DB::query()
            ->fromSub($union, 'q')
            ->whereDate('tanggal_antrian', '>=', $from)
            ->whereDate('tanggal_antrian', '<=', $to);

        if (!empty($poli)) {
            $query->where('poli', $poli);
        }

        return $query;
    }

    private function getRekapData(Request $request): array
    {
        [$from, $to, $tipe] = $this->normalizeRange($request);
        $poli = (string) $request->get('rekap_poli', '');

        $query = $this->baseQuery($from, $to, $poli);

        $total = (clone $query)->count();
        $sudah = (clone $query)->where('is_call', 1)->count();
        $belum = (clone $query)->where('is_call', 0)->count();

        // ✅ ini yang bikin error kamu hilang (variabel $perHari ada)
        $perHari = (clone $query)
            ->select(
                DB::raw('DATE(tanggal_antrian) as tanggal'),
                DB::raw('COUNT(*) AS total'),
                DB::raw('SUM(CASE WHEN is_call = 1 THEN 1 ELSE 0 END) AS sudah_dipanggil'),
                DB::raw('SUM(CASE WHEN is_call = 0 THEN 1 ELSE 0 END) AS belum_dipanggil')
            )
            ->groupBy(DB::raw('DATE(tanggal_antrian)'))
            ->orderBy('tanggal')
            ->get();

        $perPoli = (clone $query)
            ->select(
                'poli',
                DB::raw('COUNT(*) AS total'),
                DB::raw('SUM(CASE WHEN is_call = 1 THEN 1 ELSE 0 END) AS sudah_dipanggil'),
                DB::raw('SUM(CASE WHEN is_call = 0 THEN 1 ELSE 0 END) AS belum_dipanggil')
            )
            ->groupBy('poli')
            ->orderBy('poli')
            ->get();

        return [
            'rekap_tipe' => $tipe,
            'from' => $from,
            'to' => $to,
            'poli' => $poli,
            'summary' => [
                'total' => $total,
                'sudah' => $sudah,
                'belum' => $belum,
            ],
            'perHari' => $perHari,
            'perPoli' => $perPoli,
        ];
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getRekapData($request);

        $pdf = Pdf::loadView('dashboard.rekap.pdf', $data)
            ->setPaper('A4', 'portrait');

        $filename = "rekap-antrian-{$data['from']}-sd-{$data['to']}.pdf";
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getRekapData($request);
        $filename = "rekap-antrian-{$data['from']}-sd-{$data['to']}.csv";

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['REKAP ANTRIAN']);
            fputcsv($out, ["Periode", "{$data['from']} s/d {$data['to']}"]);
            fputcsv($out, ["Filter Poli", $data['poli'] ?: 'Semua Poli']);
            fputcsv($out, []);

            fputcsv($out, ['Ringkasan']);
            fputcsv($out, ['Total', $data['summary']['total']]);
            fputcsv($out, ['Sudah Dipanggil', $data['summary']['sudah']]);
            fputcsv($out, ['Belum Dipanggil', $data['summary']['belum']]);
            fputcsv($out, []);

            fputcsv($out, ['Rekap Per Hari']);
            fputcsv($out, ['Tanggal', 'Total', 'Sudah Dipanggil', 'Belum Dipanggil']);
            foreach ($data['perHari'] as $r) {
                fputcsv($out, [(string)$r->tanggal, (int)$r->total, (int)$r->sudah_dipanggil, (int)$r->belum_dipanggil]);
            }

            fputcsv($out, []);
            fputcsv($out, ['Rekap Per Poli']);
            fputcsv($out, ['Poli', 'Total', 'Sudah Dipanggil', 'Belum Dipanggil']);
            foreach ($data['perPoli'] as $r) {
                fputcsv($out, [strtoupper((string)$r->poli), (int)$r->total, (int)$r->sudah_dipanggil, (int)$r->belum_dipanggil]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

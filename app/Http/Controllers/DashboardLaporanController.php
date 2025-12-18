<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardLaporanController extends Controller
{
    // ==========================
    // LIST LAPORAN (tabel)
    // ==========================
    protected function buildQuery(Request $request)
    {
        $query = Antrian::query();

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_antrian', $request->tanggal);
        }

        if ($request->filled('poli')) {
            $query->where('poli', $request->poli);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_ktp', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('tanggal_antrian');
    }

    public function index(Request $request)
    {
        $antrians = $this->buildQuery($request)->paginate(10)->withQueryString();
        return view('dashboard.laporan.index', compact('antrians'));
    }

    public function exportPdf(Request $request)
    {
        $antrians = $this->buildQuery($request)->get();

        $pdf = PDF::loadView('dashboard.laporan.pdf', compact('antrians'))
            ->setPaper('A4', 'landscape');

        return $pdf->download('laporan-antrian.pdf');
    }

    public function exportExcelCsv(Request $request): StreamedResponse
    {
        $fileName = 'laporan-antrian.csv';
        $antrians = $this->buildQuery($request)->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $columns = [
            'No Antrian','Nama','Alamat','Jenis Kelamin','No HP','No KTP',
            'Tanggal Lahir','Pekerjaan','Poli','Tanggal Antrian',
        ];

        $callback = function () use ($antrians, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            foreach ($antrians as $row) {
                fputcsv($handle, [
                    $row->no_antrian,
                    $row->nama,
                    $row->alamat,
                    $row->jenis_kelamin,
                    $row->no_hp,
                    $row->no_ktp,
                    $row->tgl_lahir,
                    $row->pekerjaan,
                    $row->poli,
                    $row->tanggal_antrian,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================
    // REKAP (ikut filter)
    // ==========================
    private function normalizeRekapRange(Request $request): array
    {
        $tipe = $request->get('tipe', 'harian'); // harian | per_poli
        $from = $request->get('from');
        $to   = $request->get('to');

        $fromDate = $from ? Carbon::parse($from)->startOfDay() : Carbon::today()->startOfDay();
        $toDate   = $to ? Carbon::parse($to)->startOfDay() : Carbon::today()->startOfDay();

        if ($toDate->lt($fromDate)) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        return [$tipe, $fromDate->toDateString(), $toDate->toDateString()];
    }

    private function rekapBaseQuery(Request $request)
    {
        [, $from, $to] = $this->normalizeRekapRange($request);
        $poli = $request->get('poli'); // optional

        $q1 = DB::table('antrians')->select('poli', 'is_call', 'tanggal_antrian');
        $q2 = DB::table('riwayat_antrians')->select('poli', 'is_call', 'tanggal_antrian');

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
        [$tipe, $from, $to] = $this->normalizeRekapRange($request);
        $poli = $request->get('poli', '');

        $base = $this->rekapBaseQuery($request);

        $total = (clone $base)->count();
        $sudah = (clone $base)->where('is_call', 1)->count();
        $belum = (clone $base)->where('is_call', 0)->count();

        if ($tipe === 'per_poli') {
            $rows = (clone $base)
                ->select(
                    'poli',
                    DB::raw('COUNT(*) AS total'),
                    DB::raw('SUM(CASE WHEN is_call = 1 THEN 1 ELSE 0 END) AS sudah'),
                    DB::raw('SUM(CASE WHEN is_call = 0 THEN 1 ELSE 0 END) AS belum')
                )
                ->groupBy('poli')
                ->orderBy('poli')
                ->get();
        } else {
            $rows = (clone $base)
                ->select(
                    DB::raw('DATE(tanggal_antrian) as tanggal'),
                    DB::raw('COUNT(*) AS total'),
                    DB::raw('SUM(CASE WHEN is_call = 1 THEN 1 ELSE 0 END) AS sudah'),
                    DB::raw('SUM(CASE WHEN is_call = 0 THEN 1 ELSE 0 END) AS belum')
                )
                ->groupBy(DB::raw('DATE(tanggal_antrian)'))
                ->orderBy(DB::raw('DATE(tanggal_antrian)'))
                ->get();
        }

        return [
            'tipe' => $tipe,
            'from' => $from,
            'to'   => $to,
            'poli' => $poli,
            'summary' => compact('total', 'sudah', 'belum'),
            'rows' => $rows,
        ];
    }

    public function exportRekapPdf(Request $request)
    {
        $data = $this->getRekapData($request);

        $pdf = PDF::loadView('dashboard.laporan.rekap-pdf', $data)
            ->setPaper('A4', 'portrait');

        $filename = "rekap-antrian-{$data['from']}-sd-{$data['to']}.pdf";
        return $pdf->download($filename);
    }

    public function exportRekapExcel(Request $request): StreamedResponse
    {
        $data = $this->getRekapData($request);
        $filename = "rekap-antrian-{$data['from']}-sd-{$data['to']}.csv";

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['REKAP ANTRIAN']);
            fputcsv($out, ['Periode', "{$data['from']} s/d {$data['to']}"]);
            fputcsv($out, ['Tipe', $data['tipe'] === 'per_poli' ? 'Per Poli' : 'Harian']);
            fputcsv($out, ['Filter Poli', $data['poli'] ?: 'Semua Poli']);
            fputcsv($out, []);

            fputcsv($out, ['Ringkasan']);
            fputcsv($out, ['Total', $data['summary']['total']]);
            fputcsv($out, ['Sudah Dipanggil', $data['summary']['sudah']]);
            fputcsv($out, ['Belum Dipanggil', $data['summary']['belum']]);
            fputcsv($out, []);

            if ($data['tipe'] === 'per_poli') {
                fputcsv($out, ['Per Poli']);
                fputcsv($out, ['Poli', 'Total', 'Sudah', 'Belum']);
                foreach ($data['rows'] as $r) {
                    fputcsv($out, [strtoupper((string)$r->poli), (int)$r->total, (int)$r->sudah, (int)$r->belum]);
                }
            } else {
                fputcsv($out, ['Harian']);
                fputcsv($out, ['Tanggal', 'Total', 'Sudah', 'Belum']);
                foreach ($data['rows'] as $r) {
                    fputcsv($out, [(string)$r->tanggal, (int)$r->total, (int)$r->sudah, (int)$r->belum]);
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

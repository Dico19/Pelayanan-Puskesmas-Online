<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim((string) $request->get('q', ''));
        $action = (string) $request->get('action', 'all');
        $poli   = (string) $request->get('poli', 'all');

        $from = (string) $request->get('from', now()->subDays(7)->toDateString());
        $to   = (string) $request->get('to', now()->toDateString());

        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate   = Carbon::parse($to)->endOfDay();

        $base = AuditLog::query()
            ->whereBetween('created_at', [$fromDate, $toDate]);

        if ($action !== '' && $action !== 'all') {
            $base->where('action', $action);
        }

        if ($poli !== '' && $poli !== 'all') {
            $base->where('poli', $poli);
        }

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('no_ktp', 'like', "%{$q}%")
                  ->orWhere('no_antrian', 'like', "%{$q}%")
                  ->orWhere('pasien_nama', 'like', "%{$q}%")
                  ->orWhere('dokter_nama', 'like', "%{$q}%");
            });
        }

        $logs = (clone $base)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total'       => (clone $base)->count(),
            'dipanggil'   => (clone $base)->where('action', 'dipanggil')->count(),
            'mulai'       => (clone $base)->where('action', 'mulai')->count(),
            'selesai'     => (clone $base)->where('action', 'selesai')->count(),
            'lewat'       => (clone $base)->whereIn('action', ['lewati','lewat'])->count(),
            'tidak_hadir' => (clone $base)->where('action', 'tidak_hadir')->count(),
        ];

        $polis = AuditLog::query()
            ->whereNotNull('poli')
            ->select('poli')
            ->distinct()
            ->orderBy('poli')
            ->pluck('poli')
            ->toArray();

        return view('admin.audit.index', compact('logs', 'stats', 'polis', 'q', 'action', 'poli', 'from', 'to'));
    }

    public function show(AuditLog $audit)
    {
        return response()->json([
            'id'         => $audit->id,
            'waktu'      => optional($audit->created_at)->toDateTimeString(),
            'dokter'     => $audit->dokter_nama ?? optional($audit->user)->name,
            'poli'       => $audit->poli,
            'no_antrian' => $audit->no_antrian,
            'pasien_nama'=> $audit->pasien_nama,
            'no_ktp'     => $audit->no_ktp,
            'action'     => $audit->action,
            'before'     => $audit->before ?? [],
            'after'      => $audit->after ?? [],
        ]);
    }
}

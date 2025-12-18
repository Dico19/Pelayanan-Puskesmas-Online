<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardAuditController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');

        $q      = trim((string) $request->get('q', ''));
        $action = trim((string) $request->get('action', ''));
        $poli   = trim((string) $request->get('poli', ''));
        $from   = trim((string) $request->get('from', now()->subDays(7)->toDateString()));
        $to     = trim((string) $request->get('to', now()->toDateString()));

        $base = AuditLog::query()
            ->when($from !== '', fn($qq) => $qq->whereDate('created_at', '>=', $from))
            ->when($to !== '', fn($qq) => $qq->whereDate('created_at', '<=', $to))
            ->when($action !== '', fn($qq) => $qq->where('action', $action))
            ->when($poli !== '', fn($qq) => $qq->where('poli', $poli))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($sub) use ($q) {
                    $sub->where('no_ktp', 'like', "%{$q}%")
                        ->orWhere('no_antrian', 'like', "%{$q}%")
                        ->orWhere('pasien_nama', 'like', "%{$q}%")
                        ->orWhere('dokter_nama', 'like', "%{$q}%");
                });
            });

        $logs = (clone $base)->orderByDesc('created_at')->paginate(12)->withQueryString();

        $statsRaw = (clone $base)
            ->selectRaw('action, COUNT(*) as c')
            ->groupBy('action')
            ->pluck('c', 'action')
            ->toArray();

        $stats = [
            'total'   => (int) array_sum(array_map('intval', $statsRaw)),
            'dipanggil' => (int) ($statsRaw['dipanggil'] ?? 0),
            'mulai'     => (int) ($statsRaw['mulai'] ?? 0),
            'selesai'   => (int) ($statsRaw['selesai'] ?? 0),
            'lewati'    => (int) ($statsRaw['lewati'] ?? 0),
            'panggil_ulang' => (int) ($statsRaw['panggil_ulang'] ?? 0),
        ];

        $polis = AuditLog::query()
            ->whereNotNull('poli')->where('poli', '!=', '')
            ->distinct()->orderBy('poli')->pluck('poli')->toArray();

        $actions = [
            'dipanggil' => 'Dipanggil',
            'panggil_ulang' => 'Panggil Ulang',
            'mulai' => 'Mulai',
            'selesai' => 'Selesai',
            'lewati' => 'Lewat',
        ];

        return view('admin.audit.index', compact(
            'logs','q','action','poli','from','to','stats','polis','actions'
        ));
    }

    public function show(AuditLog $audit)
    {
        return response()->json([
            'ok' => true,
            'id' => $audit->id,
            'waktu' => optional($audit->created_at)->toDateTimeString(),
            'dokter' => $audit->dokter_nama,
            'pasien' => $audit->pasien_nama,
            'no_ktp' => $audit->no_ktp,
            'no_antrian' => $audit->no_antrian,
            'poli' => $audit->poli,
            'aksi' => $audit->action,
            'before' => $audit->before,
            'after' => $audit->after,
        ]);
    }
}

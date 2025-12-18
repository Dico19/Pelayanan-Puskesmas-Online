<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim((string) $request->get('q', ''));
        $action = trim((string) $request->get('action', ''));
        $poli   = trim((string) $request->get('poli', ''));
        $from   = trim((string) $request->get('from', ''));
        $to     = trim((string) $request->get('to', ''));

        $logs = AuditLog::query()
            ->when($action !== '', fn($qr) => $qr->where('action', $action))
            ->when($poli !== '', fn($qr) => $qr->where('poli', $poli))
            ->when($from !== '', fn($qr) => $qr->whereDate('created_at', '>=', $from))
            ->when($to !== '', fn($qr) => $qr->whereDate('created_at', '<=', $to))
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($sub) use ($q) {
                    $sub->where('nama_pasien', 'like', "%{$q}%")
                        ->orWhere('no_ktp', 'like', "%{$q}%")
                        ->orWhere('no_antrian', 'like', "%{$q}%")
                        ->orWhere('actor_name', 'like', "%{$q}%")
                        ->orWhere('poli', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $polis = AuditLog::query()
            ->whereNotNull('poli')->where('poli', '<>', '')
            ->select('poli')->distinct()->orderBy('poli')
            ->pluck('poli');

        $actions = ['panggil', 'mulai', 'selesai', 'lewati'];

        return view('admin.audit.index', compact('logs', 'q', 'action', 'poli', 'from', 'to', 'polis', 'actions'));
    }
}

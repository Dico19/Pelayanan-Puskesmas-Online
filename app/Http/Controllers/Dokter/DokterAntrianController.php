<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\Antrian;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DokterAntrianController extends Controller
{
    private function normalizePoliCode(?string $value): ?string
    {
        $value = strtolower(trim((string) $value));
        if ($value === '') return null;

        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        $value = trim((string) $value, '_');

        return $value !== '' ? $value : null;
    }

    private function dokterPoliCode(): ?string
    {
        $user = auth()->user();

        $poliCode = $this->normalizePoliCode($user->poli_code ?? null);
        if ($poliCode) return $poliCode;

        $roleRaw = $user?->role?->role ?? $user?->role ?? '';
        $role = strtolower(str_replace(' ', '_', trim((string) $roleRaw)));

        if (str_starts_with($role, 'dokter_')) {
            $suffix = substr($role, 7);
            return $this->normalizePoliCode($suffix);
        }

        return null;
    }

    private function allowedPoliValues(): array
    {
        $code = $this->dokterPoliCode();

        $map = [
            'umum'   => ['umum'],
            'gigi'   => ['gigi'],
            'tht'    => ['tht'],
            'balita' => ['balita'],

            'kia'    => ['kia', 'kia & kb', 'kia&kb', 'kia_kb', 'kia kb'],
            'kb'     => ['kia', 'kia & kb', 'kia&kb', 'kia_kb', 'kia kb'],
            'kia_kb' => ['kia', 'kia & kb', 'kia&kb', 'kia_kb', 'kia kb'],

            'nifas'      => ['nifas', 'nifas/pnc', 'nifas pnc', 'pnc', 'nifas_pnc'],
            'pnc'        => ['nifas', 'nifas/pnc', 'nifas pnc', 'pnc', 'nifas_pnc'],
            'nifas_pnc'  => ['nifas', 'nifas/pnc', 'nifas pnc', 'pnc', 'nifas_pnc'],

            'lansia'              => ['lansia', 'lansia & disabilitas', 'disabilitas', 'lansia_disabilitas'],
            'disabilitas'         => ['lansia', 'lansia & disabilitas', 'disabilitas', 'lansia_disabilitas'],
            'lansia_disabilitas'  => ['lansia', 'lansia & disabilitas', 'disabilitas', 'lansia_disabilitas'],
        ];

        return $map[$code] ?? (empty($code) ? [] : [$code]);
    }

    private function ensurePoliAccess(Antrian $antrian): void
    {
        $allowed = array_map('strtolower', $this->allowedPoliValues());
        $antrianPoli = strtolower(trim((string) ($antrian->poli ?? $antrian->poli_code ?? '')));

        if (!empty($allowed) && $antrianPoli !== '' && !in_array($antrianPoli, $allowed, true)) {
            abort(403, 'Akses ditolak. Poli antrian tidak sesuai dengan poli dokter.');
        }
    }

    private function snapshot(Antrian $a): array
    {
        return [
            'status' => $a->status ?? null,
            'is_call' => $a->is_call ?? null,
            'no_antrian' => $a->no_antrian ?? null,
            'tanggal_antrian' => $a->tanggal_antrian ?? null,
            'updated_at' => optional($a->updated_at)->toDateTimeString(),
        ];
    }

    private function writeAudit(Antrian $antrian, string $action, array $before, array $after): void
    {
        try {
            AuditLog::create([
                'antrian_id' => $antrian->id,
                'dokter_id' => auth()->id(),
                'dokter_nama' => auth()->user()->name ?? null,
                'no_ktp' => $antrian->no_ktp ?? null,
                'pasien_nama' => $antrian->nama ?? null,
                'no_antrian' => $antrian->no_antrian ?? null,
                'poli' => $antrian->poli ?? ($antrian->poli_code ?? null),
                'action' => $action,
                'before' => $before,
                'after' => $after,
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            // ✅ audit gagal TIDAK BOLEH bikin aksi dokter crash
            Log::error('AuditLog gagal disimpan', [
                'err' => $e->getMessage(),
                'action' => $action,
                'antrian_id' => $antrian->id,
            ]);
        }
    }

    public function index()
    {
        $allowed = array_map('strtolower', $this->allowedPoliValues());
        $poliLabel = $this->dokterPoliCode() ?: '-';

        $data = Antrian::query()
            ->when(!empty($allowed), function ($q) use ($allowed) {
                $q->whereIn(DB::raw('LOWER(poli)'), $allowed);
            })
            ->orderByDesc('tanggal_antrian')
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.antrian.index', [
            'data' => $data,
            'poli' => $poliLabel,
            'today' => now()->toDateString(),
        ]);
    }

    public function panggil($antrianId)
    {
        $antrian = Antrian::findOrFail($antrianId);
        $this->ensurePoliAccess($antrian);

        $before = $this->snapshot($antrian);

        $antrian->is_call = 1;
        $antrian->status = 'dipanggil';
        $antrian->save();

        $antrian->refresh();
        $after = $this->snapshot($antrian);

        $this->writeAudit($antrian, 'dipanggil', $before, $after);

        return back()->with('success', 'Antrian berhasil dipanggil.');
    }

    public function panggilUlang($antrianId)
    {
        $antrian = Antrian::findOrFail($antrianId);
        $this->ensurePoliAccess($antrian);

        $before = $this->snapshot($antrian);

        $antrian->is_call = 1;
        if (empty($antrian->status)) $antrian->status = 'dipanggil';
        $antrian->save();

        $antrian->refresh();
        $after = $this->snapshot($antrian);

        $this->writeAudit($antrian, 'panggil_ulang', $before, $after);

        return back()->with('success', 'Panggil ulang berhasil.');
    }

    public function mulai($antrianId)
    {
        $antrian = Antrian::findOrFail($antrianId);
        $this->ensurePoliAccess($antrian);

        $before = $this->snapshot($antrian);

        $antrian->status = 'dilayani';
        $antrian->is_call = 1;
        $antrian->save();

        $antrian->refresh();
        $after = $this->snapshot($antrian);

        $this->writeAudit($antrian, 'mulai', $before, $after);

        return back()->with('success', 'Pelayanan dimulai.');
    }

    public function selesai($antrianId)
    {
        $antrian = Antrian::findOrFail($antrianId);
        $this->ensurePoliAccess($antrian);

        $before = $this->snapshot($antrian);

        $antrian->status = 'selesai';
        $antrian->save();

        $antrian->refresh();
        $after = $this->snapshot($antrian);

        $this->writeAudit($antrian, 'selesai', $before, $after);

        return back()->with('success', 'Antrian ditandai selesai.');
    }

    public function lewati($antrianId)
    {
        $antrian = Antrian::findOrFail($antrianId);
        $this->ensurePoliAccess($antrian);

        $before = $this->snapshot($antrian);

        $antrian->status = 'dilewati';
        $antrian->save();

        $antrian->refresh();
        $after = $this->snapshot($antrian);

        $this->writeAudit($antrian, 'lewati', $before, $after);

        return back()->with('success', 'Antrian dilewati.');
    }
}

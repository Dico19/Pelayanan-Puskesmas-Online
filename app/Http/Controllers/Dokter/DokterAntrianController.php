<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\Antrian;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DokterAntrianController extends Controller
{
    // =========================
    // CONFIG
    // =========================
    private int $MIN_SKIP_FOR_ABSENT = 2;
    private int $MAX_ABSENT_BLOCK_NIK = 3;

    // cache kolom biar gak berat (sekali per request)
    private bool $hasPoliCode = false;
    private bool $hasCalledAt = false;
    private bool $hasSkippedAt = false;
    private bool $hasAbsentAt = false;
    private bool $hasAbsentCount = false;
    private bool $hasIsNikBlocked = false;

    public function __construct()
    {
        try {
            $this->hasPoliCode     = Schema::hasColumn('antrians', 'poli_code');
            $this->hasCalledAt     = Schema::hasColumn('antrians', 'called_at');
            $this->hasSkippedAt    = Schema::hasColumn('antrians', 'skipped_at');
            $this->hasAbsentAt     = Schema::hasColumn('antrians', 'absent_at');
            $this->hasAbsentCount  = Schema::hasColumn('antrians', 'absent_count');
            $this->hasIsNikBlocked = Schema::hasColumn('antrians', 'is_nik_blocked');
        } catch (\Throwable $e) {
            // biarin false semua
        }
    }

    // =========================
    // HELPERS: Poli / Access
    // =========================
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

        // 1) prioritas poli_code
        $poliCode = $this->normalizePoliCode($user->poli_code ?? null);
        if ($poliCode) return $poliCode;

        // 2) fallback ke poli / poli_name / nama_poli
        $poli = $user->poli ?? $user->poli_name ?? $user->nama_poli ?? null;
        $poliCode = $this->normalizePoliCode($poli);
        if ($poliCode) return $poliCode;

        // 3) fallback ke role (spatie/field role)
        $roleRaw = '';
        if ($user && method_exists($user, 'getRoleNames')) {
            $roleRaw = (string) ($user->getRoleNames()->first() ?? '');
        }
        if ($roleRaw === '') {
            $roleRaw = (string) (data_get($user, 'role.role') ?? data_get($user, 'role') ?? '');
        }

        $role = strtolower(str_replace(' ', '_', trim($roleRaw)));
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

            'nifas'     => ['nifas', 'nifas/pnc', 'nifas pnc', 'pnc', 'nifas_pnc'],
            'pnc'       => ['nifas', 'nifas/pnc', 'nifas pnc', 'pnc', 'nifas_pnc'],
            'nifas_pnc' => ['nifas', 'nifas/pnc', 'nifas pnc', 'pnc', 'nifas_pnc'],

            'lansia'             => ['lansia', 'lansia & disabilitas', 'disabilitas', 'lansia_disabilitas'],
            'disabilitas'        => ['lansia', 'lansia & disabilitas', 'disabilitas', 'lansia_disabilitas'],
            'lansia_disabilitas' => ['lansia', 'lansia & disabilitas', 'disabilitas', 'lansia_disabilitas'],
        ];

        return $map[$code] ?? (empty($code) ? [] : [$code]);
    }

    private function ensurePoliAccess(Antrian $antrian): void
    {
        $allowed = array_map('strtolower', $this->allowedPoliValues());
        $antrianPoli = strtolower(trim((string)($antrian->poli ?? ($this->hasPoliCode ? ($antrian->poli_code ?? '') : ''))));

        if (!empty($allowed) && $antrianPoli !== '' && !in_array($antrianPoli, $allowed, true)) {
            abort(403, 'Akses ditolak. Poli antrian tidak sesuai dengan poli dokter.');
        }
    }

    private function scopePoli($q, array $allowed)
    {
        return $q->where(function ($qq) use ($allowed) {
            $qq->whereIn(DB::raw('LOWER(poli)'), $allowed);
            if ($this->hasPoliCode) {
                $qq->orWhereIn(DB::raw('LOWER(poli_code)'), $allowed);
            }
        });
    }

    // =========================
    // HELPERS: Status / Audit
    // =========================
    private function normStatusRow($row): string
    {
        $s = strtolower(trim((string)($row->status ?? '')));
        if ($s === '') $s = ((int)($row->is_call ?? 0) === 1) ? 'dipanggil' : 'menunggu';

        if ($s === 'lewat') $s = 'dilewati';
        if ($s === 'tidak hadir' || $s === 'tidak-hadir') $s = 'tidak_hadir';

        return $s;
    }

    private function snapshot(Antrian $a): array
    {
        return [
            'status' => $a->status ?? null,
            'is_call' => $a->is_call ?? null,
            'no_antrian' => $a->no_antrian ?? null,
            'tanggal_antrian' => $a->tanggal_antrian ?? null,
            'skip_count' => $a->skip_count ?? null,
            'absent_count' => $this->hasAbsentCount ? ($a->absent_count ?? null) : null,
            'called_at' => $this->hasCalledAt ? optional($a->called_at)->toDateTimeString() : null,
            'skipped_at' => $this->hasSkippedAt ? optional($a->skipped_at)->toDateTimeString() : null,
            'absent_at' => $this->hasAbsentAt ? optional($a->absent_at)->toDateTimeString() : null,
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
                'poli' => $antrian->poli ?? ($this->hasPoliCode ? ($antrian->poli_code ?? null) : null),
                'action' => $action,
                'before' => $before,
                'after' => $after,
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            Log::error('AuditLog gagal disimpan', [
                'err' => $e->getMessage(),
                'action' => $action,
                'antrian_id' => $antrian->id,
            ]);
        }
    }

    // =========================
    // GET: INDEX
    // =========================
    public function index()
    {
        Carbon::setLocale('id');

        $allowed = array_map('strtolower', $this->allowedPoliValues());
        $poliLabel = $this->dokterPoliCode() ?: '-';

        if (empty($allowed)) {
            return view('dokter.antrian.index', [
                'data' => collect(),
                'poli' => $poliLabel,
                'today' => now()->toDateString(),
            ])->with('error', 'Poli dokter tidak terdeteksi. Cek poli_code / role dokter.');
        }

        $data = Antrian::query()
            ->when(true, fn($q) => $this->scopePoli($q, $allowed))
            ->orderByDesc('tanggal_antrian')
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.antrian.index', [
            'data' => $data,
            'poli' => $poliLabel,
            'today' => now()->toDateString(),
        ]);
    }

    // =========================
    // POST: PANGGIL
    // =========================
    public function panggil($antrianId)
    {
        $today = now()->toDateString();

        $antrian = Antrian::findOrFail($antrianId);
        $this->ensurePoliAccess($antrian);

        if (!empty($antrian->tanggal_antrian) && Carbon::parse($antrian->tanggal_antrian)->toDateString() !== $today) {
            return redirect()->route('dokter.antrian.index')->with('warning', 'Panggil hanya bisa untuk antrian hari ini.');
        }

        $status = $this->normStatusRow($antrian);
        if (in_array($status, ['selesai', 'tidak_hadir'], true)) {
            return redirect()->route('dokter.antrian.index')->with('warning', 'Antrian sudah selesai / tidak hadir.');
        }

        $before = $this->snapshot($antrian);

        $update = [
            'is_call' => 1,
            'status'  => in_array($status, ['menunggu', 'dilewati'], true)
                ? 'dipanggil'
                : ($antrian->status ?? 'dipanggil'),
        ];
        if ($this->hasCalledAt) $update['called_at'] = now();

        $antrian->update($update);

        $after = $this->snapshot($antrian);
        $this->writeAudit($antrian, 'panggil', $before, $after);

        return redirect()->route('dokter.antrian.index')
            ->with('success', "Berhasil memanggil {$antrian->no_antrian} - {$antrian->nama}");
    }

    // =========================
    // POST: PANGGIL ULANG
    // =========================
    public function panggilUlang($antrianId)
    {
        $today = now()->toDateString();

        $antrian = Antrian::findOrFail($antrianId);
        $this->ensurePoliAccess($antrian);

        if (!empty($antrian->tanggal_antrian) && Carbon::parse($antrian->tanggal_antrian)->toDateString() !== $today) {
            return redirect()->route('dokter.antrian.index')->with('warning', 'Panggil ulang hanya untuk antrian hari ini.');
        }

        $status = $this->normStatusRow($antrian);

        // ✅ Tidak Hadir tidak boleh dipanggil ulang (sesuai request)
        if (in_array($status, ['selesai', 'tidak_hadir'], true)) {
            return redirect()->route('dokter.antrian.index')->with('warning', 'Tidak bisa panggil ulang: sudah selesai / tidak hadir.');
        }

        $before = $this->snapshot($antrian);

        $update = ['is_call' => 1];

        // ✅ kalau sebelumnya menunggu/dilewati -> balikin jadi dipanggil agar TV monitor tampil lagi
        if (in_array($status, ['menunggu', 'dilewati'], true)) {
            $update['status'] = 'dipanggil';
        }

        if ($this->hasCalledAt) $update['called_at'] = now();

        $antrian->update($update);

        $after = $this->snapshot($antrian);
        $this->writeAudit($antrian, 'panggil_ulang', $before, $after);

        return redirect()->route('dokter.antrian.index')->with('success', 'Panggil ulang berhasil.');
    }

    public function mulai($antrianId)
    {
        $today = now()->toDateString();

        $antrian = Antrian::findOrFail($antrianId);
        $this->ensurePoliAccess($antrian);

        if (!empty($antrian->tanggal_antrian) && Carbon::parse($antrian->tanggal_antrian)->toDateString() !== $today) {
            return redirect()->route('dokter.antrian.index')->with('warning', 'Mulai hanya untuk antrian hari ini.');
        }

        $status = $this->normStatusRow($antrian);
        if ($status !== 'dipanggil') {
            return redirect()->route('dokter.antrian.index')->with('warning', 'Mulai hanya bisa setelah pasien dipanggil.');
        }

        $before = $this->snapshot($antrian);

        // ✅ pastikan tetap dianggap "sudah dipanggil"
        $update = ['status' => 'dilayani', 'is_call' => 1];
        $antrian->update($update);

        $after = $this->snapshot($antrian);
        $this->writeAudit($antrian, 'mulai', $before, $after);

        return redirect()->route('dokter.antrian.index')->with('success', 'Status diubah: sedang dilayani.');
    }

    public function selesai($antrianId)
    {
        $today = now()->toDateString();

        $antrian = Antrian::findOrFail($antrianId);
        $this->ensurePoliAccess($antrian);

        if (!empty($antrian->tanggal_antrian) && Carbon::parse($antrian->tanggal_antrian)->toDateString() !== $today) {
            return redirect()->route('dokter.antrian.index')->with('warning', 'Selesai hanya untuk antrian hari ini.');
        }

        $status = $this->normStatusRow($antrian);
        if (!in_array($status, ['dipanggil', 'dilayani'], true)) {
            return redirect()->route('dokter.antrian.index')->with('warning', 'Selesai hanya bisa saat dipanggil / dilayani.');
        }

        $before = $this->snapshot($antrian);

        // ✅ FIX UTAMA:
        // selesai harus tetap dianggap "pernah dipanggil" supaya diagnosa tidak error "belum dipanggil"
        $update = [
            'status'  => 'selesai',
            'is_call' => 1,
        ];

        // optional: kalau called_at belum ada, isi biar konsisten
        if ($this->hasCalledAt && empty($antrian->called_at)) {
            $update['called_at'] = now();
        }

        $antrian->update($update);

        $after = $this->snapshot($antrian);
        $this->writeAudit($antrian, 'selesai', $before, $after);

        return redirect()->route('dokter.antrian.index')->with('success', 'Antrian selesai.');
    }

    public function lewati($antrianId)
    {
        $today = now()->toDateString();

        $antrian = Antrian::findOrFail($antrianId);
        $this->ensurePoliAccess($antrian);

        if (!empty($antrian->tanggal_antrian) && Carbon::parse($antrian->tanggal_antrian)->toDateString() !== $today) {
            return redirect()->route('dokter.antrian.index')->with('warning', 'Lewatkan hanya untuk antrian hari ini.');
        }

        $status = $this->normStatusRow($antrian);
        if (in_array($status, ['selesai', 'tidak_hadir'], true)) {
            return redirect()->route('dokter.antrian.index')->with('warning', 'Tidak bisa lewati: sudah selesai / tidak hadir.');
        }

        $before = $this->snapshot($antrian);

        // ✅ lewati = keluarkan dari kondisi dipanggil
        $update = [
            'status'     => 'dilewati',
            'is_call'    => 0,
            'skip_count' => ((int)($antrian->skip_count ?? 0)) + 1,
        ];
        if ($this->hasSkippedAt) $update['skipped_at'] = now();

        $antrian->update($update);

        $after = $this->snapshot($antrian);
        $this->writeAudit($antrian, 'lewati', $before, $after);

        return redirect()->route('dokter.antrian.index')->with('success', 'Antrian dilewati.');
    }

    public function tidakHadir($antrianId)
    {
        $today = now()->toDateString();

        $antrian = Antrian::findOrFail($antrianId);
        $this->ensurePoliAccess($antrian);

        if (!empty($antrian->tanggal_antrian) && Carbon::parse($antrian->tanggal_antrian)->toDateString() !== $today) {
            return redirect()->route('dokter.antrian.index')->with('warning', 'Tidak hadir hanya untuk antrian hari ini.');
        }

        $status = $this->normStatusRow($antrian);
        $skipCount = (int)($antrian->skip_count ?? 0);

        if ($status !== 'dilewati' || $skipCount < $this->MIN_SKIP_FOR_ABSENT) {
            return redirect()->route('dokter.antrian.index')
                ->with('warning', "Tidak hadir hanya bisa setelah dilewati minimal {$this->MIN_SKIP_FOR_ABSENT}x.");
        }

        $before = $this->snapshot($antrian);

        $update = [
            'status'  => 'tidak_hadir',
            'is_call' => 0, // ✅ tidak hadir = jangan dianggap dipanggil
        ];
        if ($this->hasAbsentAt) $update['absent_at'] = now();
        if ($this->hasAbsentCount) $update['absent_count'] = ((int)($antrian->absent_count ?? 0)) + 1;

        $antrian->update($update);

        $fresh = Antrian::find($antrian->id);
        $after = $this->snapshot($fresh);
        $this->writeAudit($fresh, 'tidak_hadir', $before, $after);

        if ($this->hasAbsentCount && (int)($fresh->absent_count ?? 0) >= $this->MAX_ABSENT_BLOCK_NIK) {
            if ($this->hasIsNikBlocked) {
                $fresh->update(['is_nik_blocked' => 1]);
            }
            return redirect()->route('dokter.antrian.index')
                ->with('success', 'Ditandai: tidak hadir. NIK sudah mencapai batas tidak hadir (siapkan blokir di pendaftaran).');
        }

        return redirect()->route('dokter.antrian.index')->with('success', 'Ditandai: tidak hadir.');
    }

    // =========================
    // ✅ RESET HARI INI
    // =========================
    public function resetHariIni()
    {
        $allowed = array_map('strtolower', $this->allowedPoliValues());
        if (empty($allowed)) {
            return redirect()->route('dokter.antrian.index')->with('error', 'Poli dokter tidak terdeteksi.');
        }

        $today = now()->toDateString();

        $query = Antrian::query()->whereDate('tanggal_antrian', $today);
        $this->scopePoli($query, $allowed);

        $count = (clone $query)->count();
        if ($count === 0) {
            return redirect()->route('dokter.antrian.index')->with('warning', 'Tidak ada antrian hari ini untuk poli kamu.');
        }

        $rows = (clone $query)->get();

        DB::transaction(function () use ($rows, $query) {
            foreach ($rows as $a) {
                $before = $this->snapshot($a);
                $this->writeAudit($a, 'reset_hari_ini_delete', $before, ['deleted' => true]);
            }
            $query->delete();
        });

        return redirect()->route('dokter.antrian.index')
            ->with('success', "Reset antrian hari ini berhasil. Data dihapus: {$count}");
    }
}

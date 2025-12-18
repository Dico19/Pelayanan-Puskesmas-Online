<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Antrian;
use App\Models\RekamMedik;
use Carbon\Carbon;

class RekamMedikController extends Controller
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

            'nifas'     => ['nifas', 'nifas/pnc', 'nifas pnc', 'pnc', 'nifas_pnc'],
            'pnc'       => ['nifas', 'nifas/pnc', 'nifas pnc', 'pnc', 'nifas_pnc'],
            'nifas_pnc' => ['nifas', 'nifas/pnc', 'nifas pnc', 'pnc', 'nifas_pnc'],

            'lansia'             => ['lansia', 'lansia & disabilitas', 'disabilitas', 'lansia_disabilitas'],
            'disabilitas'        => ['lansia', 'lansia & disabilitas', 'disabilitas', 'lansia_disabilitas'],
            'lansia_disabilitas' => ['lansia', 'lansia & disabilitas', 'disabilitas', 'lansia_disabilitas'],
        ];

        return $map[$code] ?? (empty($code) ? [] : [$code]);
    }

    public function store(Request $request, $antrianId)
    {
        $request->validate([
            'diagnosa' => 'required|string|max:2000',
            'catatan'  => 'nullable|string|max:2000',
            'resep'    => 'nullable|string|max:2000',
        ]);

        $antrian = Antrian::findOrFail($antrianId);

        if ((int) $antrian->is_call !== 1) {
            return back()->with('error', 'Pasien belum dipanggil. Panggil dulu sebelum isi diagnosa.');
        }

        $allowed = array_map('strtolower', $this->allowedPoliValues());
        $antrianPoli = strtolower(trim((string) ($antrian->poli ?? $antrian->poli_code ?? '')));

        if (!empty($allowed) && $antrianPoli !== '' && !in_array($antrianPoli, $allowed, true)) {
            abort(403, 'Akses ditolak. Poli antrian tidak sesuai dengan poli dokter.');
        }

        RekamMedik::updateOrCreate(
            ['antrian_id' => $antrian->id],
            [
                'dokter_id' => auth()->id(),
                'no_ktp' => $antrian->no_ktp,
                'poli' => $antrian->poli ?? ($antrian->poli_code ?? null),
                'tanggal_kunjungan' => $antrian->tanggal_antrian ?? now(),
                'diagnosa' => $request->diagnosa,
                'catatan' => $request->catatan,
                'resep' => $request->resep,
            ]
        );

        return back()->with('success', 'Diagnosa / rekam medis berhasil disimpan.');
    }

    // ✅ MODAL (AJAX)
    public function modal($noKtp)
    {
        Carbon::setLocale('id');

        $allowed = array_map('strtolower', $this->allowedPoliValues());

        $patient = Antrian::where('no_ktp', $noKtp)
            ->orderByDesc('tanggal_antrian')
            ->first();

        $riwayat = RekamMedik::query()
            ->where('rekam_mediks.no_ktp', $noKtp)
            ->when(!empty($allowed), function ($q) use ($allowed) {
                $q->whereIn(DB::raw('LOWER(rekam_mediks.poli)'), $allowed);
            })
            ->orderByDesc('rekam_mediks.tanggal_kunjungan')
            ->limit(5)
            ->get();

        return response()->json([
            'ok' => true,
            'no_ktp' => $noKtp,
            'nama' => $patient->nama ?? null,
            'items' => $riwayat->map(function ($r) {
                $tgl = $r->tanggal_kunjungan
                    ? Carbon::parse($r->tanggal_kunjungan)->translatedFormat('d F Y')
                    : '-';

                return [
                    'id' => $r->id,
                    'tanggal' => $tgl,
                    'poli' => $r->poli,
                    'diagnosa' => $r->diagnosa,
                    'catatan' => $r->catatan,
                    'resep' => $r->resep,
                ];
            })->values(),
        ]);
    }

    // HALAMAN per pasien
    public function riwayat($noKtp)
    {
        Carbon::setLocale('id');

        $allowed = array_map('strtolower', $this->allowedPoliValues());

        $patient = Antrian::where('no_ktp', $noKtp)
            ->orderByDesc('tanggal_antrian')
            ->first();

        $nama = $patient->nama ?? null;

        $riwayat = RekamMedik::query()
            ->where('rekam_mediks.no_ktp', $noKtp)
            ->when(!empty($allowed), function ($q) use ($allowed) {
                $q->whereIn(DB::raw('LOWER(rekam_mediks.poli)'), $allowed);
            })
            ->orderByDesc('rekam_mediks.tanggal_kunjungan')
            ->get();

        return view('dokter.riwayat', compact('noKtp', 'nama', 'riwayat'));
    }

    // ✅ LIST + SEARCH (dengan nama pasien)
    public function index(Request $request)
    {
        Carbon::setLocale('id');

        $q = trim((string) $request->get('q', ''));
        $allowed = array_map('strtolower', $this->allowedPoliValues());

        $riwayat = RekamMedik::query()
            ->leftJoin('antrians', 'antrians.id', '=', 'rekam_mediks.antrian_id')
            ->select('rekam_mediks.*', 'antrians.nama as nama_pasien')
            ->when(!empty($allowed), function ($query) use ($allowed) {
                $query->whereIn(DB::raw('LOWER(rekam_mediks.poli)'), $allowed);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('rekam_mediks.no_ktp', 'like', "%{$q}%")
                        ->orWhere('rekam_mediks.diagnosa', 'like', "%{$q}%")
                        ->orWhere('rekam_mediks.poli', 'like', "%{$q}%")
                        ->orWhere('antrians.nama', 'like', "%{$q}%"); // ✅ cari nama juga
                });
            })
            ->orderByDesc('rekam_mediks.tanggal_kunjungan')
            ->paginate(15)
            ->withQueryString();

        return view('dokter.riwayat_index', compact('riwayat', 'q'));
    }
}

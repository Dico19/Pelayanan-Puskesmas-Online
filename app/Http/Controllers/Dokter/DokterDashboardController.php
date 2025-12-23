<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DokterDashboardController extends Controller
{
    private function getRoleString($user): string
    {
        if (!$user) return '';

        // kalau pakai Spatie
        if (method_exists($user, 'getRoleNames')) {
            return (string) ($user->getRoleNames()->first() ?? '');
        }

        // fallback
        return (string) (data_get($user, 'role.role') ?? data_get($user, 'role') ?? '');
    }

    private function normalizePoliKey(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        if ($value === '') return 'umum';

        // jadikan key aman: "KIA & KB" -> "kia_kb", "nifas/pnc" -> "nifas_pnc"
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        $value = trim((string) $value, '_');

        // normalisasi gabungan
        if (in_array($value, ['kia', 'kb', 'kia_kb'], true)) return 'kia_kb';
        if (in_array($value, ['nifas', 'pnc', 'nifas_pnc'], true)) return 'nifas_pnc';
        if (in_array($value, ['lansia', 'disabilitas', 'lansia_disabilitas'], true)) return 'lansia_disabilitas';

        // default
        return $value ?: 'umum';
    }

    private function poliKeyFromRole($user): string
    {
        $role = strtolower(trim($this->getRoleString($user)));

        // support "dokter_umum", "dokter_nifas", dll
        $role = str_replace(' ', '_', $role);
        if (str_starts_with($role, 'dokter_')) {
            $role = substr($role, 7);
        }

        // deteksi kata kunci
        if (str_contains($role, 'gigi')) return 'gigi';
        if (str_contains($role, 'tht')) return 'tht';
        if (str_contains($role, 'balita')) return 'balita';
        if (str_contains($role, 'kia') || str_contains($role, 'kb')) return 'kia_kb';
        if (str_contains($role, 'nifas') || str_contains($role, 'pnc')) return 'nifas_pnc';
        if (str_contains($role, 'lansia') || str_contains($role, 'disabil')) return 'lansia_disabilitas';

        return 'umum';
    }

    /**
     * Variasi penulisan poli di DB agar query selalu match
     */
    private function allowedPoliValues(string $poliKey): array
    {
        $map = [
            'umum'   => ['umum'],
            'gigi'   => ['gigi'],
            'tht'    => ['tht'],
            'balita' => ['balita'],

            'kia_kb' => ['kia', 'kb', 'kia & kb', 'kia&kb', 'kia kb', 'kia_kb'],

            'nifas_pnc' => ['nifas', 'pnc', 'nifas/pnc', 'nifas pnc', 'nifas_pnc'],

            'lansia_disabilitas' => ['lansia', 'disabilitas', 'lansia & disabilitas', 'lansia_disabilitas'],
        ];

        $vals = $map[$poliKey] ?? [$poliKey];

        return array_values(array_unique(array_map(
            fn ($v) => strtolower(trim((string) $v)),
            $vals
        )));
    }

    private function poliLabel(string $poliKey): string
    {
        return match ($poliKey) {
            'umum'               => 'Poli Umum',
            'gigi'               => 'Poli Gigi',
            'tht'                => 'Poli THT',
            'balita'             => 'Poli Balita',
            'kia_kb'             => 'Poli KIA & KB',
            'nifas_pnc'          => 'Poli Nifas / PNC',
            'lansia_disabilitas' => 'Poli Lansia & Disabilitas',
            default              => 'Poli ' . ucwords(str_replace('_', ' ', $poliKey)),
        };
    }

    private function dokterLabel(string $poliKey): string
    {
        return match ($poliKey) {
            'umum'               => 'Dokter Umum',
            'gigi'               => 'Dokter Gigi',
            'tht'                => 'Dokter THT',
            'balita'             => 'Dokter Balita',
            'kia_kb'             => 'Dokter KIA & KB',
            'nifas_pnc'          => 'Dokter Nifas',
            'lansia_disabilitas' => 'Dokter Lansia',
            default              => 'Dokter ' . ucwords(str_replace('_', ' ', $poliKey)),
        };
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // 1) coba ambil poli dari field user kalau ada
        $poliRaw = $user->poli ?? $user->poli_name ?? $user->nama_poli ?? null;

        // 2) normalisasi jadi key
        $poliKey = $this->normalizePoliKey($poliRaw);

        // 3) fallback dari role kalau poliRaw kosong / tidak valid
        if (!$poliRaw || trim((string)$poliRaw) === '') {
            $poliKey = $this->poliKeyFromRole($user);
        }

        $poliLabel   = $this->poliLabel($poliKey);
        $dokterLabel = $this->dokterLabel($poliKey);

        $today = now()->toDateString();
        $allowedPoli = $this->allowedPoliValues($poliKey);

        // Base query poli + tanggal (aman untuk datetime)
        $base = Antrian::query()
            ->whereDate('tanggal_antrian', $today)
            ->whereIn(DB::raw('LOWER(poli)'), $allowedPoli);

        $antrianHariIni = (clone $base)->count();

        $sudahDipanggil = (clone $base)
            ->where('is_call', 1)
            ->count();

        $sisaHariIni = (clone $base)
            ->where('is_call', 0)
            ->count();

        // Pasien aktif (opsional): ambil yang sedang dilayani/dipanggil
        $aktif = (clone $base)
            ->whereIn(DB::raw('LOWER(status)'), ['dilayani', 'dipanggil'])
            ->orderBy('updated_at', 'desc')
            ->first();

        $pasienAktif = $aktif ? ($aktif->no_antrian . ' • ' . $aktif->nama) : '-';

        // ✅ penting: kirim poliLabel & dokterLabel ke view biar tidak salah tampil
        return view('dokter.dashboard', compact(
            'poliKey',
            'poliLabel',
            'dokterLabel',
            'antrianHariIni',
            'sudahDipanggil',
            'sisaHariIni',
            'pasienAktif'
        ));
    }
}

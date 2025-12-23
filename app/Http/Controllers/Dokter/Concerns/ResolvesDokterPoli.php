<?php

namespace App\Http\Controllers\Dokter\Concerns;

trait ResolvesDokterPoli
{
    protected function normalizePoliKey(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        $value = trim((string) $value, '_');
        return $value ?: 'umum';
    }

    protected function resolvePoliKeyFromUser($user): string
    {
        $roleRaw = '';

        // spatie
        if ($user && method_exists($user, 'getRoleNames')) {
            $roleRaw = (string) ($user->getRoleNames()->first() ?? '');
        }

        // fallback non-spatie
        if ($roleRaw === '') {
            $roleRaw = (string) (data_get($user, 'role.role') ?? data_get($user, 'role') ?? '');
        }

        $role = $this->normalizePoliKey($roleRaw);

        // ambil suffix dokter_
        if (str_starts_with($role, 'dokter_')) {
            $role = substr($role, 7);
        }

        $role = $this->normalizePoliKey($role);

        // normalisasi kategori gabungan
        if (in_array($role, ['kia', 'kb', 'kia_kb'], true)) $role = 'kia_kb';
        if (in_array($role, ['nifas', 'pnc', 'nifas_pnc'], true)) $role = 'nifas_pnc';
        if (in_array($role, ['lansia', 'disabilitas', 'lansia_disabilitas'], true)) $role = 'lansia_disabilitas';

        return $role ?: 'umum';
    }

    protected function poliLabel(string $key): string
    {
        $map = [
            'umum'               => 'Poli Umum',
            'gigi'               => 'Poli Gigi',
            'tht'                => 'Poli THT',
            'balita'             => 'Poli Balita',
            'kia_kb'             => 'Poli KIA & KB',
            'nifas_pnc'          => 'Poli Nifas / PNC',
            'lansia_disabilitas' => 'Poli Lansia & Disabilitas',
        ];

        return $map[$key] ?? ('Poli ' . ucwords(str_replace('_', ' ', $key)));
    }

    protected function dokterLabel(string $key): string
    {
        $map = [
            'umum'               => 'Dokter Umum',
            'gigi'               => 'Dokter Gigi',
            'tht'                => 'Dokter THT',
            'balita'             => 'Dokter Balita',
            'kia_kb'             => 'Dokter KIA & KB',
            'nifas_pnc'          => 'Dokter Nifas',
            'lansia_disabilitas' => 'Dokter Lansia',
        ];

        return $map[$key] ?? ('Dokter ' . ucwords(str_replace('_', ' ', $key)));
    }

    /**
     * Kalau di DB poli kamu kadang "nifas/pnc" kadang "pnc" dsb,
     * ini biar query selalu match.
     */
    protected function allowedPoliValues(string $poliKey): array
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
        return array_values(array_unique(array_map(fn($v) => strtolower(trim((string)$v)), $vals)));
    }
}

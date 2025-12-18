<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin',

            // dokter per poli (sesuai poli di aplikasimu)
            'dokter_umum',
            'dokter_gigi',
            'dokter_tht',
            'dokter_lansia_disabilitas',
            'dokter_balita',
            'dokter_kia_kb',
            'dokter_nifas_pnc',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['role' => $role]);
        }
    }
}

<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DokterRiwayatController extends Controller
{
    private function currentPoli(): string
    {
        $u = auth()->user();

        $poli = $u->poli_code ?? null;
        if ($poli) return strtolower(trim((string) $poli));

        $roleRaw = $u?->role?->role ?? $u?->role ?? '';
        $role = strtolower(trim((string) $roleRaw));

        if (str_starts_with($role, 'dokter_')) {
            $key = substr($role, 7);
            $map = [
                'kia_kb' => 'kia & kb',
                'nifas_pnc' => 'nifas/pnc',
                'lansia_disabilitas' => 'lansia & disabilitas',
            ];
            return $map[$key] ?? str_replace('_', ' ', $key);
        }

        return '';
    }

    public function modal(Request $request, $no_ktp)
    {
        $no_ktp = trim((string) $no_ktp);
        $poli   = $this->currentPoli();

        // ===== ambil 5 kunjungan terakhir (dari antrians + rekam_mediks bila ada) =====
        $query = DB::table('antrians as a')
            ->where('a.no_ktp', $no_ktp);

        // amanin: dokter hanya lihat riwayat pasien di poli dia
        if ($poli !== '') {
            $query->whereRaw('LOWER(a.poli) = ?', [strtolower($poli)]);
        }

        $hasRekam = Schema::hasTable('rekam_mediks');

        if ($hasRekam) {
            $query->leftJoin('rekam_mediks as rm', 'rm.antrian_id', '=', 'a.id')
                ->select([
                    'a.id',
                    'a.no_antrian',
                    'a.nama',
                    'a.poli',
                    'a.tanggal_antrian',
                    'a.status',
                    'rm.diagnosa',
                    'rm.catatan',
                    'rm.resep',
                    'rm.created_at as rekam_created_at',
                ])
                ->orderByDesc('a.tanggal_antrian')
                ->orderByDesc('rm.created_at');
        } else {
            // fallback kalau tabel rekam_mediks belum ada / namanya beda
            $query->select([
                'a.id',
                'a.no_antrian',
                'a.nama',
                'a.poli',
                'a.tanggal_antrian',
                'a.status',
                DB::raw("NULL as diagnosa"),
                DB::raw("NULL as catatan"),
                DB::raw("NULL as resep"),
                DB::raw("NULL as rekam_created_at"),
            ])->orderByDesc('a.tanggal_antrian');
        }

        $items = collect($query->limit(5)->get());

        $patientName = $items->first()->nama ?? '-';

        return view('dokter.riwayat._modal', [
            'patientName' => $patientName,
            'items'       => $items,
        ]);
    }
}

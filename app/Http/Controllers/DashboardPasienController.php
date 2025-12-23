<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardPasienController extends Controller
{
    private int $ABSENT_BLOCK_THRESHOLD = 3;

    private function normalizeNik(?string $nik): string
    {
        $nik = trim((string) $nik);
        return preg_replace('/\D+/', '', $nik) ?? '';
    }

    private function lastResetAt(string $nik): ?string
    {
        return DB::table('nik_block_resets')
            ->where('no_ktp', $nik)
            ->max('created_at');
    }

    private function absentCountSinceReset(string $nik): int
    {
        $nik = $this->normalizeNik($nik);
        if ($nik === '') return 0;

        $q = Antrian::query()
            ->where('no_ktp', $nik)
            ->whereIn(DB::raw('LOWER(status)'), ['tidak_hadir', 'tidak hadir', 'tidak-hadir']);

        // ✅ hitung "tidak_hadir" setelah reset terakhir
        $lastReset = $this->lastResetAt($nik);
        if ($lastReset) {
            $q->where('created_at', '>', $lastReset);
        }

        return (int) $q->count();
    }

    private function nikIsBlocked(string $nik): bool
    {
        return $this->absentCountSinceReset($nik) >= $this->ABSENT_BLOCK_THRESHOLD;
    }

    public function index(Request $request)
    {
        $patients = Patient::query()
            ->when($request->search, function ($q, $search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_ktp', 'like', "%{$search}%");
            })
            ->orderBy('nama')
            ->paginate(10);

        return view('dashboard.pasien.index', compact('patients'));
    }

    public function show(Patient $patient)
    {
        $riwayatAktif = Antrian::where('patient_id', $patient->id)
            ->select(['id','patient_id','no_antrian','poli','tanggal_antrian','pekerjaan','is_call','created_at','updated_at'])
            ->get();

        $riwayatLama = DB::table('riwayat_antrians')
            ->where('patient_id', $patient->id)
            ->select(['id','patient_id','no_antrian','poli','tanggal_antrian','pekerjaan','is_call','created_at','updated_at'])
            ->get();

        $riwayat = $riwayatAktif
            ->concat($riwayatLama)
            ->sortByDesc('tanggal_antrian')
            ->values();

        // ✅ status blocked untuk tombol
        $nik = $this->normalizeNik($patient->no_ktp);
        $absentCount = $this->absentCountSinceReset($nik);
        $isBlocked   = $absentCount >= $this->ABSENT_BLOCK_THRESHOLD;

        return view('dashboard.pasien.show', compact(
            'patient',
            'riwayat',
            'isBlocked',
            'absentCount'
        ));
    }

    /**
     * ✅ POST /admin/dashboard/pasien/{patient}/unblock
     * Reset blokir dengan mencatat "reset" (audit trail)
     */
    public function unblock(Request $request, Patient $patient)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $nik = $this->normalizeNik($patient->no_ktp);

        DB::table('nik_block_resets')->insert([
            'no_ktp'        => $nik,
            'admin_user_id' => auth()->id(), // butuh kolom admin_user_id
            'reason'        => $request->input('reason'),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()
            ->route('admin.pasien.show', $patient->id)
            ->with('success', 'Blokir NIK berhasil dibuka. Pasien bisa ambil antrian lagi.');
    }
}

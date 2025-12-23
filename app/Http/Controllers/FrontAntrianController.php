<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Patient;
use App\Models\RekamMedik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FrontAntrianController extends Controller
{
    /**
     * =========================
     * ✅ BLOKIR NIK (3x TIDAK HADIR) - bisa direset admin
     * =========================
     */
    private int $ABSENT_BLOCK_THRESHOLD = 3;

    /**
     * Status yang dianggap "masih proses / aktif"
     * (kalau masih ada ini di tanggal layanan, pasien tidak boleh ambil lagi)
     */
    private array $ACTIVE_STATUSES = ['menunggu', 'dipanggil', 'dilayani', 'dilewati'];

    private function normalizeNik(?string $nik): string
    {
        $nik = trim((string) $nik);
        return preg_replace('/\D+/', '', $nik) ?? '';
    }

    private function normalizePoliKey(?string $value): ?string
    {
        $value = strtolower(trim((string) $value));
        if ($value === '') return null;

        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        $value = trim((string) $value, '_');

        return $value !== '' ? $value : null;
    }

    /**
     * Variasi penulisan poli di input/DB
     */
    private function allowedPoliValues(?string $poliInput): array
    {
        $code = $this->normalizePoliKey($poliInput);

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

    /**
     * ✅ Normalisasi status antrian (support data lama)
     */
    private function normStatusRow($row): string
    {
        $s = strtolower(trim((string)($row->status ?? '')));

        // fallback data lama (kalau belum punya kolom status / masih kosong)
        if ($s === '') {
            $s = ((int)($row->is_call ?? 0) === 1) ? 'dipanggil' : 'menunggu';
        }

        // alias
        if ($s === 'lewat') $s = 'dilewati';
        if ($s === 'tidak hadir' || $s === 'tidak-hadir') $s = 'tidak_hadir';

        return $s;
    }

    /**
     * ✅ Ambil waktu terakhir admin "unblock/reset" NIK
     */
    private function lastUnblockAt(string $nik): ?string
    {
        $nik = $this->normalizeNik($nik);
        if ($nik === '') return null;

        // butuh tabel nik_block_resets
        return DB::table('nik_block_resets')
            ->where('no_ktp', $nik)
            ->max('created_at');
    }

    /**
     * ✅ Hitung jumlah tidak_hadir setelah terakhir di-unblock admin
     * PENTING: pakai updated_at karena status "tidak_hadir" bisa berubah belakangan
     */
    private function absentCountAfterReset(string $nik): int
    {
        $nik = $this->normalizeNik($nik);
        if ($nik === '') return 0;

        $lastReset = $this->lastUnblockAt($nik);

        $q = Antrian::query()
            ->where('no_ktp', $nik)
            ->whereIn(DB::raw('LOWER(status)'), ['tidak_hadir', 'tidak hadir']);

        if ($lastReset) {
            $q->where('updated_at', '>', $lastReset);
        }

        return (int) $q->count();
    }

    /**
     * ✅ NIK diblokir kalau tidak_hadir (setelah reset terakhir) >= threshold
     */
    private function nikIsBlocked(string $nik): bool
    {
        return $this->absentCountAfterReset($nik) >= $this->ABSENT_BLOCK_THRESHOLD;
    }

    private function blockedNikResponse(Request $request): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    {
        $msg = "Maaf, NIK Anda diblokir sementara karena tercatat {$this->ABSENT_BLOCK_THRESHOLD}× tidak hadir. "
            . "Silakan hubungi pihak Puskesmas melalui menu Contact.";

        if ($request->expectsJson()) {
            return response()->json([
                'ok'      => false,
                'blocked' => true,
                'message' => $msg,
            ], 403);
        }

        return redirect()
            ->route('antrian.cari')
            ->withInput()
            ->with('blocked_nik', true)
            ->with('error', $msg);
    }

    /**
     * ✅ BLOK hanya jika masih ada antrian AKTIF pada TANGGAL LAYANAN (umumnya "hari ini")
     * Status selesai / tidak_hadir => BOLEH ambil lagi.
     *
     * Default: cek aktif untuk NIK di tanggal tsb (semua poli).
     * Kalau kamu mau khusus poli saja, set $checkByPoli = true.
     */
    private function hasActiveQueueOnDate(string $nik, string $tanggal, ?string $poliInput = null, bool $checkByPoli = false): bool
    {
        $nik = $this->normalizeNik($nik);
        if ($nik === '' || trim($tanggal) === '') return false;

        $q = Antrian::query()
            ->where('no_ktp', $nik)
            ->whereDate('tanggal_antrian', $tanggal);

        if ($checkByPoli && $poliInput) {
            $allowedPoli = array_map('strtolower', $this->allowedPoliValues($poliInput));
            if (empty($allowedPoli)) $allowedPoli = [strtolower(trim($poliInput))];

            $q->whereIn(DB::raw('LOWER(poli)'), $allowedPoli);
        }

        $rows = $q->get();

        foreach ($rows as $r) {
            $status = $this->normStatusRow($r);
            if (in_array($status, $this->ACTIVE_STATUSES, true)) {
                return true; // masih aktif -> blok ambil lagi
            }
        }

        return false;
    }

    /**
     * Guard: kalau antrian sudah dipanggil dokter,
     * pasien tidak boleh edit/hapus/status.
     */
    private function blockIfCalled(Antrian $antrian, string $msg = null, ?Request $request = null)
    {
        if ((int) $antrian->is_call === 1) {
            $params = ['antrian' => $antrian->id];

            $back = $request?->query('back') ?? $request?->input('back') ?? null;
            if ($back) $params['back'] = $back;

            return redirect()
                ->route('antrian.rekam-medik', $params)
                ->with('error', $msg ?? 'Antrian sudah dipanggil dokter. Anda hanya bisa melihat diagnosa/rekam medik.');
        }

        return null;
    }

    public function index()
    {
        return view('antrian.index');
    }

    public function create()
    {
        return redirect()->route('antrian.index');
    }

    public function showCariAntrianForm(Request $request)
    {
        if (!$request->filled('no_ktp')) {
            return view('antrian.cari-nik');
        }

        $nik = $this->normalizeNik((string) $request->no_ktp);

        if ($this->nikIsBlocked($nik)) {
            return $this->blockedNikResponse($request);
        }

        $antrians = Antrian::where('no_ktp', $nik)
            ->orderByDesc('tanggal_antrian')
            ->get();

        if ($antrians->isEmpty()) {
            return redirect()
                ->route('antrian.cari')
                ->with('nik_not_found', 'NIK belum terdaftar / tidak valid.');
        }

        return view('antrian.hasil-cari-nik', [
            'nik'      => $nik,
            'antrians' => $antrians,
        ]);
    }

    public function cariProses(Request $request)
    {
        return $this->searchByNik($request);
    }

    public function searchByNik(Request $request)
    {
        $request->validate([
            'no_ktp' => 'required|string',
        ], [
            'no_ktp.required' => 'Silakan masukkan NIK Anda.',
        ]);

        $nik = $this->normalizeNik((string) $request->no_ktp);

        if ($this->nikIsBlocked($nik)) {
            return $this->blockedNikResponse($request);
        }

        return redirect()->route('antrian.cari', ['no_ktp' => $nik]);
    }

    public function rekamMedik(Request $request, Antrian $antrian)
    {
        if ((int) $antrian->is_call !== 1) {
            $back = $request->query('back');

            return $back
                ? redirect()->to($back)->with('error', 'Rekam medik belum tersedia. Pasien belum dipanggil.')
                : redirect()->route('antrian.cari')->with('error', 'Rekam medik belum tersedia. Pasien belum dipanggil.');
        }

        $rekam = RekamMedik::where('antrian_id', $antrian->id)->first();

        return view('antrian.rekam-medik', compact('antrian', 'rekam'));
    }

    public function tiketAntrian($id)
    {
        $antrian = Antrian::findOrFail($id);

        $statusUrl = route('antrian.status', $antrian->id);
        $qrStatus  = $this->generateQrDataUri($statusUrl);

        $surveyUrl = 'https://docs.google.com/forms/d/e/1FAIpQLSdoPhLJcn4n4TdPje5dvg9AiBh-uVzu58DHW6ZvML6wjLlsgg/viewform?usp=header';
        $qrSurvey  = $this->generateQrDataUri($surveyUrl);

        return view('antrian.tiket', [
            'antrian'  => $antrian,
            'qrStatus' => $qrStatus,
            'qrSurvey' => $qrSurvey,
        ]);
    }

    protected function generateQrDataUri(string $text, int $size = 280): string
    {
        $url = 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size . '&cht=qr&chl=' . urlencode($text);

        $png = @file_get_contents($url);
        if ($png === false) return '';

        return 'data:image/png;base64,' . base64_encode($png);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'            => 'required|string',
            'alamat'          => 'required|string',
            'jenis_kelamin'   => 'required|string',
            'no_hp'           => 'required|string',
            'no_ktp'          => 'required|string',
            'tgl_lahir'       => 'required|date',
            'pekerjaan'       => 'nullable|string',
            'poli'            => 'required|string',
            'tanggal_antrian' => 'nullable|date',
        ]);

        $nik = $this->normalizeNik((string) ($data['no_ktp'] ?? ''));

        // ✅ BLOKIR kalau sudah 3x tidak hadir (setelah reset admin terakhir)
        if ($this->nikIsBlocked($nik)) {
            return $this->blockedNikResponse($request);
        }

        $tanggalLayanan = !empty($data['tanggal_antrian'])
            ? Carbon::parse($data['tanggal_antrian'])->toDateString()
            : now()->toDateString();

        // ✅ BLOK hanya jika masih ada antrian AKTIF di tanggal layanan tsb (umumnya hari ini)
        // default cek semua poli. Kalau mau khusus poli => set parameter terakhir true.
        if ($this->hasActiveQueueOnDate($nik, $tanggalLayanan)) {
            return back()
                ->withInput()
                ->with('error',
                    "Tidak bisa mengambil antrian. Anda masih memiliki antrian yang belum selesai pada tanggal {$tanggalLayanan}. " .
                    "Jika status sudah SELESAI / TIDAK HADIR, Anda boleh ambil antrian lagi."
                );
        }

        // ✅ patient pakai nik yang sudah dinormalisasi
        $patient = Patient::where('no_ktp', $nik)->first();
        if (!$patient) {
            $patient = Patient::create([
                'nama'          => $data['nama'],
                'alamat'        => $data['alamat'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'no_hp'         => $data['no_hp'],
                'no_ktp'        => $nik,
                'tgl_lahir'     => $data['tgl_lahir'],
                'pekerjaan'     => $data['pekerjaan'] ?? null,
            ]);
        }

        $antrian = DB::transaction(function () use ($data, $patient, $tanggalLayanan, $nik) {
            // ✅ ambil last dengan lock biar nomor antrian aman (anti dobel)
            $noAntrian = $this->generateNoAntrianForDate((string) $data['poli'], $tanggalLayanan);

            return Antrian::create([
                'patient_id'      => $patient->id,
                'user_id'         => Auth::check() ? Auth::id() : null,
                'no_antrian'      => $noAntrian,
                'nama'            => $data['nama'],
                'alamat'          => $data['alamat'],
                'jenis_kelamin'   => $data['jenis_kelamin'],
                'no_hp'           => $data['no_hp'],
                'no_ktp'          => $nik,
                'tgl_lahir'       => $data['tgl_lahir'],
                'pekerjaan'       => $data['pekerjaan'] ?? null,
                'poli'            => $data['poli'],
                'tanggal_antrian' => $tanggalLayanan,
                'is_call'         => 0,
                'status'          => 'menunggu', // ✅ konsisten
            ]);
        });

        return redirect()
            ->route('antrian.index')
            ->with('success', 'Berhasil mengambil nomor antrian: ' . $antrian->no_antrian);
    }

    public function show(Antrian $antrian)
    {
        return view('antrian.show', compact('antrian'));
    }

    public function status(Request $request, Antrian $antrian)
    {
        if ($resp = $this->blockIfCalled($antrian, 'Antrian sudah dipanggil. Silakan lihat diagnosa/rekam medik.', $request)) {
            return $resp;
        }

        // ✅ orang di depan: yang masih "menunggu"
        $orangDiDepan = Antrian::where('poli', $antrian->poli)
            ->whereDate('tanggal_antrian', $antrian->tanggal_antrian)
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', '')
                  ->orWhere(DB::raw('LOWER(status)'), 'menunggu');
            })
            ->where('is_call', 0)
            ->where('id', '<', $antrian->id)
            ->count();

        $menitPerOrang = 10;
        $estimasiMenit = $orangDiDepan * $menitPerOrang;

        return view('antrian.status', [
            'antrian'       => $antrian,
            'orangDiDepan'  => $orangDiDepan,
            'estimasiMenit' => $estimasiMenit,
        ]);
    }

    public function profilPasien($id)
    {
        $pasien = Patient::findOrFail($id);

        $riwayat = Antrian::where('patient_id', $pasien->id)
            ->orderByDesc('tanggal_antrian')
            ->take(10)
            ->get();

        return view('livewire.pasien.profil', [
            'pasien'  => $pasien,
            'riwayat' => $riwayat,
        ]);
    }

    public function kartuPasien($id)
    {
        $pasien = Patient::findOrFail($id);

        return view('livewire.pasien.kartu', [
            'pasien' => $pasien,
        ]);
    }

    public function edit(Request $request, Antrian $antrian)
    {
        if ($resp = $this->blockIfCalled($antrian, null, $request)) return $resp;

        $poliOptions = [
            'umum'                 => 'Umum',
            'gigi'                 => 'Gigi',
            'tht'                  => 'THT',
            'balita'               => 'Balita',
            'kia & kb'             => 'KIA & KB',
            'nifas/pnc'            => 'Nifas / PNC',
            'lansia & disabilitas' => 'Lansia & Disabilitas',
        ];

        $tanggalOptions = [];
        $date = now();
        $count = 0;

        while ($count < 6) {
            if (! $date->isSunday()) {
                $tanggalOptions[] = $date->copy();
                $count++;
            }
            $date->addDay();
        }

        return view('antrian.edit', [
            'antrian'        => $antrian,
            'poliOptions'    => $poliOptions,
            'tanggalOptions' => $tanggalOptions,
        ]);
    }

    public function update(Request $request, Antrian $antrian)
    {
        if ($resp = $this->blockIfCalled($antrian, null, $request)) return $resp;

        $data = $request->validate([
            'nama'            => 'required|string|max:255',
            'alamat'          => 'required|string',
            'jenis_kelamin'   => 'required|in:laki-laki,perempuan',
            'no_hp'           => 'required|string|max:20',
            'pekerjaan'       => 'nullable|string|max:255',
            'poli'            => 'required|string',
            'tanggal_antrian' => 'required|date',
            'back'            => 'nullable|string',
        ]);

        $antrian->update(collect($data)->except('back')->toArray());

        $back = $request->input('back');
        return $back
            ? redirect()->to($back)->with('success', 'Data antrian berhasil diperbarui.')
            : redirect()->route('antrian.cari')->with('success', 'Data antrian berhasil diperbarui.');
    }

    public function destroy(Request $request, Antrian $antrian)
    {
        if ($resp = $this->blockIfCalled($antrian, null, $request)) return $resp;

        $antrian->delete();

        $back = $request->input('back');
        return $back
            ? redirect()->to($back)->with('success', 'Antrian berhasil dihapus.')
            : redirect()->route('antrian.cari')->with('success', 'Antrian berhasil dihapus.');
    }

    protected function generateNoAntrian(string $poli): string
    {
        return $this->generateNoAntrianForDate($poli, now()->toDateString());
    }

    protected function generateNoAntrianForDate(string $poli, string $tanggal): string
    {
        $prefixMap = [
            'umum'               => 'U',
            'gigi'               => 'G',
            'tht'                => 'T',
            'balita'             => 'B',
            'kia'                => 'K',
            'kb'                 => 'K',
            'kia_kb'             => 'K',
            'nifas'              => 'N',
            'pnc'                => 'N',
            'nifas_pnc'          => 'N',
            'lansia'             => 'L',
            'disabilitas'        => 'L',
            'lansia_disabilitas' => 'L',
        ];

        $key = $this->normalizePoliKey($poli) ?? 'antrian';
        $prefix = $prefixMap[$key] ?? 'A';

        // cari poli yang setara
        $allowedPoli = array_map('strtolower', $this->allowedPoliValues($poli));
        if (empty($allowedPoli)) {
            $allowedPoli = [strtolower(trim($poli))];
        }

        // ✅ lock agar aman dari dobel nomor di jam ramai
        $last = Antrian::query()
            ->whereDate('tanggal_antrian', $tanggal)
            ->whereIn(DB::raw('LOWER(poli)'), $allowedPoli)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if ($last && preg_match('/\d+$/', (string) $last->no_antrian, $match)) {
            $nextNumber = (int) $match[0] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . $nextNumber;
    }
}

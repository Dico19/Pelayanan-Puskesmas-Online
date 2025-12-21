<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Patient;
use App\Models\RekamMedik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FrontAntrianController extends Controller
{
    /**
     * Guard: kalau antrian sudah dipanggil dokter,
     * pasien tidak boleh edit/hapus/status.
     * Arahkan ke halaman diagnosa (rekam medik).
     * ✅ tetap bawa parameter back (kalau ada) agar tombol kembali konsisten.
     */
    private function blockIfCalled(Antrian $antrian, string $msg = null, ?Request $request = null)
    {
        if ((int) $antrian->is_call === 1) {
            $params = ['antrian' => $antrian->id];

            // bawa back kalau ada (dari hasil cari / form / delete)
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

    /**
     * ✅ GET /antrian/cari
     * - kalau belum ada query no_ktp -> tampilkan form
     * - kalau ada query no_ktp -> tampilkan hasil pencarian (tanpa harus POST)
     */
    public function showCariAntrianForm(Request $request)
    {
        if (! $request->filled('no_ktp')) {
            return view('antrian.cari-nik');
        }

        $nik = (string) $request->no_ktp;

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

    /**
     * ✅ POST /antrian/cari
     * FIX PENTING:
     * Jangan return view langsung, tapi redirect ke GET /antrian/cari?no_ktp=...
     * Biar tombol kembali/back bisa balik ke hasil pencarian yang valid.
     */
    public function searchByNik(Request $request)
    {
        $request->validate([
            'no_ktp' => 'required|string',
        ], [
            'no_ktp.required' => 'Silakan masukkan NIK Anda.',
        ]);

        $nik = (string) $request->no_ktp;

        return redirect()->route('antrian.cari', ['no_ktp' => $nik]);
    }

    /**
     * ✅ Halaman Rekam Medik untuk PASIEN
     * hanya boleh kalau sudah dipanggil (is_call=1)
     * ✅ kalau belum dipanggil -> redirect ke back kalau ada
     */
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

        $tanggalLayanan = !empty($data['tanggal_antrian'])
            ? Carbon::parse($data['tanggal_antrian'])->toDateString()
            : now()->toDateString();

        $patient = Patient::where('no_ktp', $data['no_ktp'])->first();

        if (! $patient) {
            $patient = Patient::create([
                'nama'          => $data['nama'],
                'alamat'        => $data['alamat'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'no_hp'         => $data['no_hp'],
                'no_ktp'        => $data['no_ktp'],
                'tgl_lahir'     => $data['tgl_lahir'],
                'pekerjaan'     => $data['pekerjaan'] ?? null,
            ]);
        }

        $noAntrian = $this->generateNoAntrianForDate($data['poli'], $tanggalLayanan);

        $antrian = Antrian::create([
            'patient_id'      => $patient->id,
            'user_id'         => Auth::check() ? Auth::id() : null,
            'no_antrian'      => $noAntrian,
            'nama'            => $data['nama'],
            'alamat'          => $data['alamat'],
            'jenis_kelamin'   => $data['jenis_kelamin'],
            'no_hp'           => $data['no_hp'],
            'no_ktp'          => $data['no_ktp'],
            'tgl_lahir'       => $data['tgl_lahir'],
            'pekerjaan'       => $data['pekerjaan'] ?? null,
            'poli'            => $data['poli'],
            'tanggal_antrian' => $tanggalLayanan,
            'is_call'         => 0,
        ]);

        return redirect()
            ->route('antrian.index')
            ->with('success', 'Berhasil mengambil nomor antrian: ' . $antrian->no_antrian);
    }

    public function show(Antrian $antrian)
    {
        return view('antrian.show', compact('antrian'));
    }

    /**
     * ✅ Status: BLOK kalau sudah dipanggil (langsung ke diagnosa)
     * ✅ bawa back param supaya tombol kembali konsisten.
     */
    public function status(Request $request, Antrian $antrian)
    {
        if ($resp = $this->blockIfCalled($antrian, 'Antrian sudah dipanggil. Silakan lihat diagnosa/rekam medik.', $request)) {
            return $resp;
        }

        $orangDiDepan = Antrian::where('poli', $antrian->poli)
            ->whereDate('tanggal_antrian', $antrian->tanggal_antrian)
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

        // ✅ FIX: jangan ikut update field "back"
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
            'umum'                 => 'U',
            'gigi'                 => 'G',
            'tht'                  => 'T',
            'balita'               => 'B',
            'kia & kb'             => 'K',
            'kia & kb '            => 'K',
            'nifas/pnc'            => 'N',
            'lansia & disabilitas' => 'L',
        ];

        $key    = strtolower($poli);
        $prefix = $prefixMap[$key] ?? 'A';

        $last = Antrian::where('poli', $poli)
            ->whereDate('tanggal_antrian', $tanggal)
            ->orderByDesc('id')
            ->first();

        if ($last && preg_match('/\d+$/', $last->no_antrian, $match)) {
            $nextNumber = (int) $match[0] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . $nextNumber;
    }
}

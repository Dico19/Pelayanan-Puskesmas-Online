<?php

namespace App\Http\Livewire\Antrian;

use App\Models\Antrian;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ShowAntrian extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // form/modal fields
    public $antrian_id,
        $no_antrian,
        $nama,
        $alamat,
        $jenis_kelamin,
        $no_hp,
        $no_ktp,
        $tgl_lahir,
        $pekerjaan,
        $poli,
        $tanggal_antrian,
        $user_id,
        $data;

    // filter tabel
    public $filterPoli = '';

    // step modal
    public $step = 1;

    public $listPoli = [
        'umum'                 => 'Umum',
        'gigi'                 => 'Gigi',
        'tht'                  => 'THT',
        'lansia & disabilitas' => 'Lansia & Disabilitas',
        'balita'               => 'Balita',
        'kia & kb'             => 'KIA & KB',
        'nifas/pnc'            => 'Nifas / PNC',
    ];

    public $tanggalPilihan = [];

    // alert UI modal
    public string $uiAlert = '';

    // blokir jika 3x tidak hadir
    private int $ABSENT_BLOCK_THRESHOLD = 3;

    // status aktif (untuk cegah duplikat di tanggal yg sama)
    private array $ACTIVE_STATUSES = ['menunggu', 'dipanggil', 'dilayani', 'dilewati'];

    protected function rules()
    {
        return [
            'nama'            => 'required|string|max:100',
            'alamat'          => 'required|string|max:255',
            'jenis_kelamin'   => 'required|in:laki-laki,perempuan',
            'no_hp'           => 'required|regex:/^\d{10,15}$/',
            'no_ktp'          => 'required|digits:16',
            'tgl_lahir'       => 'required|date',
            'pekerjaan'       => 'required|string|max:100',
            'poli'            => 'required',
            'tanggal_antrian' => 'required|date',
        ];
    }

    protected $messages = [
        'nama.required'            => 'Nama wajib diisi.',
        'alamat.required'          => 'Alamat wajib diisi.',
        'jenis_kelamin.required'   => 'Jenis kelamin wajib dipilih.',
        'jenis_kelamin.in'         => 'Jenis kelamin tidak valid.',
        'no_hp.required'           => 'Nomor HP wajib diisi.',
        'no_hp.regex'              => 'Nomor HP harus angka (10–15 digit).',
        'no_ktp.required'          => 'NIK / No KTP wajib diisi.',
        'no_ktp.digits'            => 'NIK / No KTP harus tepat 16 digit angka.',
        'tgl_lahir.required'       => 'Tanggal lahir wajib diisi.',
        'tgl_lahir.date'           => 'Tanggal lahir tidak valid.',
        'pekerjaan.required'       => 'Pekerjaan wajib diisi.',
        'poli.required'            => 'Poli wajib dipilih.',
        'tanggal_antrian.required' => 'Tanggal layanan wajib dipilih.',
        'tanggal_antrian.date'     => 'Tanggal layanan tidak valid.',
    ];

    // =========================
    // INPUT SANITIZE
    // =========================
    public function updatedNoKtp($value)
    {
        $clean = preg_replace('/\D+/', '', (string) $value);
        $this->no_ktp = substr($clean, 0, 16);
    }

    public function updatedNoHp($value)
    {
        $clean = preg_replace('/\D+/', '', (string) $value);
        $this->no_hp = substr($clean, 0, 15);
    }

    public function updated($fields)
    {
        $this->validateOnly($fields);
    }

    public function updatedFilterPoli()
    {
        $this->resetPage();
    }

    // =========================
    // HELPERS
    // =========================
    private function normalizeNik(?string $nik): string
    {
        $nik = trim((string) $nik);
        return preg_replace('/\D+/', '', $nik) ?? '';
    }

    private function hasStatusColumn(): bool
    {
        static $cached = null;
        if ($cached !== null) return $cached;

        try {
            $cached = Schema::hasColumn('antrians', 'status');
        } catch (\Throwable $e) {
            $cached = false;
        }

        return $cached;
    }

    private function hasResetTable(): bool
    {
        static $cached = null;
        if ($cached !== null) return $cached;

        try {
            $cached = Schema::hasTable('nik_block_resets');
        } catch (\Throwable $e) {
            $cached = false;
        }

        return $cached;
    }

    private function normStatusRow($row): string
    {
        $s = strtolower(trim((string)($row->status ?? '')));

        if ($s === '') {
            $s = ((int)($row->is_call ?? 0) === 1) ? 'dipanggil' : 'menunggu';
        }

        if ($s === 'lewat') $s = 'dilewati';
        if ($s === 'tidak hadir' || $s === 'tidak-hadir') $s = 'tidak_hadir';

        return $s;
    }

    private function lastUnblockAt(string $nik): ?string
    {
        $nik = $this->normalizeNik($nik);
        if ($nik === '' || !$this->hasResetTable()) return null;

        return DB::table('nik_block_resets')
            ->where('no_ktp', $nik)
            ->max('created_at');
    }

    private function absentCountAfterReset(string $nik): int
    {
        $nik = $this->normalizeNik($nik);
        if ($nik === '') return 0;

        $lastReset = $this->lastUnblockAt($nik);

        $q = Antrian::query()
            ->where('no_ktp', $nik)
            ->whereIn(DB::raw('LOWER(status)'), ['tidak_hadir', 'tidak hadir', 'tidak-hadir']);

        if ($lastReset) {
            $q->where('updated_at', '>', $lastReset);
        }

        return (int) $q->count();
    }

    private function nikIsBlocked(string $nik): bool
    {
        return $this->absentCountAfterReset($nik) >= $this->ABSENT_BLOCK_THRESHOLD;
    }

    private function nikAlreadyHasActiveQueueOnDate(string $nik, string $tanggal): bool
    {
        $nik = $this->normalizeNik($nik);
        $tanggal = trim((string) $tanggal);
        if ($nik === '' || $tanggal === '') return false;

        $rows = Antrian::query()
            ->where('no_ktp', $nik)
            ->whereDate('tanggal_antrian', $tanggal)
            ->get();

        foreach ($rows as $r) {
            $st = $this->normStatusRow($r);
            if (in_array($st, $this->ACTIVE_STATUSES, true)) return true;
        }

        return false;
    }

    // =========================
    // MODAL FLOW
    // =========================
    public function openCreate()
    {
        $this->resetValidation();
        $this->resetErrorBag();

        $this->uiAlert = '';

        // reset input modal (JANGAN reset filterPoli)
        $this->no_antrian      = '';
        $this->nama            = '';
        $this->alamat          = '';
        $this->jenis_kelamin   = '';
        $this->no_hp           = '';
        $this->no_ktp          = '';
        $this->poli            = '';
        $this->tgl_lahir       = '';
        $this->pekerjaan       = '';
        $this->tanggal_antrian = null;
        $this->tanggalPilihan  = [];
        $this->step            = 1;
    }

    public function pilihPoli($kode)
    {
        if (!array_key_exists($kode, $this->listPoli)) return;

        $this->uiAlert = '';

        $this->poli = $kode;
        $this->tanggal_antrian = null;
        $this->generateTanggalPilihan();
        $this->step = 2;
    }

    public function kembaliKePilihPoli()
    {
        $this->uiAlert = '';
        $this->step = 1;
        $this->poli = '';
        $this->tanggal_antrian = null;
        $this->tanggalPilihan = [];
    }

    protected function generateTanggalPilihan()
    {
        $this->tanggalPilihan = [];
        $today = Carbon::today();

        for ($i = 0; $i < 6; $i++) {
            $tanggal = $today->copy()->addDays($i);
            $isMinggu = $tanggal->dayOfWeek === Carbon::SUNDAY;

            $jumlah = 0;
            if ($this->poli && !$isMinggu) {
                $q = Antrian::query()
                    ->where('poli', $this->poli)
                    ->whereDate('tanggal_antrian', $tanggal->toDateString())
                    ->where(function ($w) {
                        $w->whereNull('status')
                          ->orWhere('status', '')
                          ->orWhereIn(DB::raw('LOWER(status)'), $this->ACTIVE_STATUSES);
                    });

                $jumlah = (int) $q->count();
            }

            $this->tanggalPilihan[] = [
                'date'        => $tanggal->toDateString(),
                'hari'        => $this->hariIndo($tanggal),
                'tanggal'     => $tanggal->format('d'),
                'bulan_tahun' => $this->bulanIndo($tanggal) . ' ' . $tanggal->format('Y'),
                'is_libur'    => $isMinggu,
                'jumlah'      => $jumlah,
            ];
        }
    }

    protected function hariIndo(Carbon $date)
    {
        $map = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $map[$date->dayOfWeek] ?? '';
    }

    protected function bulanIndo(Carbon $date)
    {
        $map = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $map[(int) $date->format('n')] ?? '';
    }

    public function pilihTanggal($tanggal)
    {
        $this->uiAlert = '';

        foreach ($this->tanggalPilihan as $item) {
            if ($item['date'] === $tanggal && !$item['is_libur']) {
                $this->tanggal_antrian = $tanggal;
                $this->step = 3;
                return;
            }
        }
    }

    public function kembaliKePilihTanggal()
    {
        $this->uiAlert = '';
        $this->step = 2;
    }

    // =========================
    // SAVE
    // =========================
    public function save()
    {
        $this->uiAlert = '';

        $validatedData = $this->validate();

        $nik = $this->normalizeNik($this->no_ktp);
        $tgl = (string) $this->tanggal_antrian;

        if ($this->nikIsBlocked($nik)) {
            $this->uiAlert =
                "Maaf, NIK Anda diblokir sementara karena tercatat {$this->ABSENT_BLOCK_THRESHOLD}× tidak hadir. " .
                "Silakan hubungi pihak Puskesmas melalui menu Contact.";
            $this->addError('no_ktp', 'NIK diblokir sementara.');
            return;
        }

        if ($this->nikAlreadyHasActiveQueueOnDate($nik, $tgl)) {
            $this->uiAlert =
                "Anda sudah memiliki antrian aktif pada tanggal " . Carbon::parse($tgl)->format('d-m-Y') . ". " .
                "Silakan cek menu Antrianku untuk melihat detail antrian Anda.";
            $this->addError('no_ktp', 'NIK masih punya antrian aktif pada tanggal ini.');
            return;
        }

        $prefixMap = [
            'umum'                 => 'U',
            'gigi'                 => 'G',
            'tht'                  => 'T',
            'lansia & disabilitas' => 'L',
            'balita'               => 'B',
            'kia & kb'             => 'K',
            'nifas/pnc'            => 'N',
        ];
        $prefix = $prefixMap[$this->poli] ?? 'A';

        $antrian = DB::transaction(function () use ($validatedData, $nik, $prefix) {
            $latest = Antrian::query()
                ->where('poli', $this->poli)
                ->whereDate('tanggal_antrian', $this->tanggal_antrian)
                ->where('no_antrian', 'like', $prefix . '%')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($latest && preg_match('/(\d+)$/', (string) $latest->no_antrian, $m)) {
                $nextNumber = ((int) $m[1]) + 1;
            }

            $this->no_antrian = $prefix . $nextNumber;

            $validatedData['no_ktp']          = $nik;
            $validatedData['no_antrian']      = $this->no_antrian;
            $validatedData['tanggal_antrian'] = $this->tanggal_antrian;
            $validatedData['user_id']         = null;
            $validatedData['is_call']         = 0;

            if ($this->hasStatusColumn()) {
                $validatedData['status'] = $validatedData['status'] ?? 'menunggu';
            }

            return Antrian::create($validatedData);
        });

        session()->flash('success', 'Berhasil Mengambil Antrian');

        $this->emit('update');
        $this->openCreate();

        $this->dispatchBrowserEvent('close-createAntrian');
        $this->dispatchBrowserEvent('closeModal');

        return redirect()->route('antrian.status', $antrian->id);
    }

    public function close_modal()
    {
        $this->openCreate();
    }

    // =========================
    // RENDER (TABLE AMAN)
    // =========================
    public function render()
    {
        // ✅ IMPORTANT:
        // Select hanya kolom yang "aman" untuk ditampilkan di publik.
        // (alamat/no_hp/no_ktp/pekerjaan tetap ada di DB, tapi tidak ikut diambil untuk tabel)
        $query = Antrian::query()
            ->select([
                'id',
                'no_antrian',
                'nama',
                'jenis_kelamin',
                'poli',
                'tanggal_antrian',
                'status',
                'is_call',
            ])
            ->where('is_call', 0)
            ->where(function ($w) {
                $w->whereNull('status')
                  ->orWhere('status', '')
                  ->orWhereIn(DB::raw('LOWER(status)'), $this->ACTIVE_STATUSES);
            });

        if ($this->filterPoli) {
            $query->where('poli', $this->filterPoli);
        }

        $query->orderByDesc('tanggal_antrian')->orderBy('no_antrian')->orderBy('id');

        return view('livewire.antrian.show-antrian', [
            'antrian'       => $query->paginate(10),
            'cekAntrian'    => 0,
            'detailAntrian' => collect(),
        ]);
    }
}

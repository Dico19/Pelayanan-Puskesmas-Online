<?php

namespace App\Http\Livewire\Antrian;

use App\Models\Antrian;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class ShowAntrian extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

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

    // FILTER TABLE (biar gak bentrok sama $poli untuk modal)
    public $filterPoli = '';

    // STEP: 1 pilih poli, 2 pilih tanggal, 3 isi form
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

    /**
     * RULES (Update: NIK 16 digit)
     */
    protected function rules()
    {
        return [
            'nama'            => 'required|string|max:100',
            'alamat'          => 'required|string|max:255',
            'jenis_kelamin'   => 'required|in:laki-laki,perempuan',

            // HP angka 10-15 digit
            'no_hp'           => 'required|regex:/^\d{10,15}$/',

            // ✅ NIK/KTP wajib tepat 16 digit
            'no_ktp'          => 'required|digits:16',

            'tgl_lahir'       => 'required|date',
            'pekerjaan'       => 'required|string|max:100',
            'poli'            => 'required',
            'tanggal_antrian' => 'required|date',
        ];
    }

    /**
     * CUSTOM MESSAGES
     */
    protected $messages = [
        'nama.required'           => 'Nama wajib diisi.',
        'alamat.required'         => 'Alamat wajib diisi.',
        'jenis_kelamin.required'  => 'Jenis kelamin wajib dipilih.',
        'jenis_kelamin.in'        => 'Jenis kelamin tidak valid.',

        'no_hp.required'          => 'Nomor HP wajib diisi.',
        'no_hp.regex'             => 'Nomor HP harus angka (10–15 digit).',

        'no_ktp.required'         => 'NIK / No KTP wajib diisi.',
        'no_ktp.digits'           => 'NIK / No KTP harus tepat 16 digit angka.',

        'tgl_lahir.required'      => 'Tanggal lahir wajib diisi.',
        'tgl_lahir.date'          => 'Tanggal lahir tidak valid.',

        'pekerjaan.required'      => 'Pekerjaan wajib diisi.',
        'poli.required'           => 'Poli wajib dipilih.',
        'tanggal_antrian.required'=> 'Tanggal layanan wajib dipilih.',
        'tanggal_antrian.date'    => 'Tanggal layanan tidak valid.',
    ];

    /**
     * Auto bersihkan input: hanya angka + limit
     * (biar user ketik "32-01..." tetap jadi angka)
     */
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

    private function isAdmin()
    {
        return auth()->check() && auth()->user()->role_id == 1;
    }

    // dipanggil oleh wire:click="openCreate"
    public function openCreate()
    {
        // reset modal biar selalu mulai step 1
        $this->resetValidation();
        $this->resetErrorBag();

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

        $this->poli = $kode;
        $this->tanggal_antrian = null;
        $this->generateTanggalPilihan();
        $this->step = 2;
    }

    public function kembaliKePilihPoli()
    {
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
                $jumlah = Antrian::where('poli', $this->poli)
                    ->where('tanggal_antrian', $tanggal->toDateString())
                    ->count();
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
        $map = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        return $map[$date->dayOfWeek] ?? '';
    }

    protected function bulanIndo(Carbon $date)
    {
        $map = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
        ];
        return $map[(int)$date->format('n')] ?? '';
    }

    public function pilihTanggal($tanggal)
    {
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
        $this->step = 2;
    }

    public function save()
    {
        $validatedData = $this->validate();

        $latestAntrian = Antrian::where('poli', $this->poli)
            ->where('tanggal_antrian', $this->tanggal_antrian)
            ->latest('id')
            ->first();

        if (!$latestAntrian) {
            if ($this->poli === 'umum') $this->no_antrian = 'U1';
            elseif ($this->poli === 'gigi') $this->no_antrian = 'G1';
            elseif ($this->poli === 'tht') $this->no_antrian = 'T1';
            elseif ($this->poli === 'lansia & disabilitas') $this->no_antrian = 'L1';
            elseif ($this->poli === 'balita') $this->no_antrian = 'B1';
            elseif ($this->poli === 'kia & kb') $this->no_antrian = 'K1';
            elseif ($this->poli === 'nifas/pnc') $this->no_antrian = 'N1';
        } else {
            $kode_awal = substr($latestAntrian->no_antrian, 0, 1);
            $angka = (int) substr($latestAntrian->no_antrian, 1);
            $this->no_antrian = $kode_awal . ($angka + 1);
        }

        $validatedData['no_antrian']       = $this->no_antrian;
        $validatedData['tanggal_antrian'] = $this->tanggal_antrian;
        $validatedData['user_id']         = null;
        $validatedData['is_call']         = 0;

        $antrian = Antrian::create($validatedData);

        session()->flash('success', 'Berhasil Mengambil Antrian');

        $this->emit('update');
        $this->openCreate(); // reset modal fields
        $this->dispatchBrowserEvent('closeModal');

        return redirect()->route('antrian.status', $antrian->id);
    }

    public function close_modal()
    {
        $this->openCreate();
    }

    public function render()
    {
        $query = Antrian::where('is_call', 0);

        if ($this->filterPoli) {
            $query->where('poli', $this->filterPoli);
        }

        return view('livewire.antrian.show-antrian', [
            'antrian' => $query->paginate(10),
            'cekAntrian'    => 0,
            'detailAntrian' => collect(),
        ]);
    }
}

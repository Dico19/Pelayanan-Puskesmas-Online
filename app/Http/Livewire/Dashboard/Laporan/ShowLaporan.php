<?php

namespace App\Http\Livewire\Dashboard\Laporan;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Antrian;

class ShowLaporan extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // FILTER TABEL (bawah)
    public $tanggal_antrian = ''; // today|week|month|''
    public $poli = '';
    public $search = '';

    // FILTER REKAP (atas)
    public $rekap_tipe = 'today'; // today|week|month|custom
    public $rekap_from;
    public $rekap_to;
    public $rekap_poli = '';

    public function mount()
    {
        $today = now()->toDateString();
        $this->rekap_from = $this->rekap_from ?? $today;
        $this->rekap_to   = $this->rekap_to   ?? $today;

        // set default sesuai tipe
        $this->updatedRekapTipe();
    }

    public function updatedRekapTipe()
    {
        $today = now();

        if ($this->rekap_tipe === 'today') {
            $this->rekap_from = $today->toDateString();
            $this->rekap_to   = $today->toDateString();
        } elseif ($this->rekap_tipe === 'week') {
            $this->rekap_from = $today->copy()->startOfWeek()->toDateString();
            $this->rekap_to   = $today->copy()->endOfWeek()->toDateString();
        } elseif ($this->rekap_tipe === 'month') {
            $this->rekap_from = $today->copy()->startOfMonth()->toDateString();
            $this->rekap_to   = $today->copy()->endOfMonth()->toDateString();
        }
        // kalau custom: biarkan user pilih rekap_from & rekap_to
    }

    public function resetRekap()
    {
        $today = now()->toDateString();
        $this->rekap_tipe = 'today';
        $this->rekap_from = $today;
        $this->rekap_to   = $today;
        $this->rekap_poli = '';
    }

    public function updatingTanggalAntrian() { $this->resetPage(); }
    public function updatingPoli()          { $this->resetPage(); }
    public function updatingSearch()        { $this->resetPage(); }

    public function render()
    {
        $q = Antrian::query();

        if ($this->tanggal_antrian === 'today') {
            $q->whereDate('tanggal_antrian', now());
        } elseif ($this->tanggal_antrian === 'week') {
            $q->whereBetween('tanggal_antrian', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($this->tanggal_antrian === 'month') {
            $q->whereBetween('tanggal_antrian', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        if ($this->poli) {
            $q->where('poli', $this->poli);
        }

        if ($this->search) {
            $s = $this->search;
            $q->where(function ($w) use ($s) {
                $w->where('nama', 'like', "%{$s}%")
                  ->orWhere('no_ktp', 'like', "%{$s}%");
            });
        }

        return view('livewire.dashboard.laporan.show-laporan', [
            'laporan' => $q->latest()->paginate(10),
        ]);
    }
}

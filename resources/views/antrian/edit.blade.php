@extends('layouts.main')

@section('title', 'Edit Antrian')

@section('content')
@php
    /**
     * ✅ PRIORITAS balik:
     * 1) ?back= dari hasil pencarian
     * 2) fallback aman: hasil pencarian berdasarkan NIK antrian ini
     * 3) terakhir: url sebelumnya
     */
    $backUrl = request('back');

    if (!$backUrl && !empty($antrian->no_ktp)) {
        $backUrl = route('antrian.cari', ['no_ktp' => $antrian->no_ktp]);
    }

    if (!$backUrl) {
        $backUrl = url()->previous();
    }
@endphp

<section class="py-5" style="margin-top: 90px;">
    <div class="container">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h2 class="fw-bold mb-0 text-uppercase">Edit Antrian</h2>
                <div class="text-muted small">
                    Silakan perbarui data antrian Anda.
                </div>
            </div>

            {{-- ✅ Tombol kembali: selalu balik ke hasil pencarian --}}
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">

                        <form action="{{ route('antrian.update', $antrian) }}" method="POST">
                            @csrf
                            @method('PUT')

                            {{-- ✅ KIRIM BALIK URL agar setelah update redirect ke hasil pencarian --}}
                            <input type="hidden" name="back" value="{{ $backUrl }}">

                            {{-- ================= DATA PASIEN ================= --}}
                            <div class="mb-3">
                                <label for="nama" class="form-label fw-semibold">Nama</label>
                                <input
                                    type="text"
                                    id="nama"
                                    name="nama"
                                    class="form-control @error('nama') is-invalid @enderror"
                                    value="{{ old('nama', $antrian->nama) }}"
                                >
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label fw-semibold">Alamat</label>
                                <textarea
                                    id="alamat"
                                    name="alamat"
                                    rows="2"
                                    class="form-control @error('alamat') is-invalid @enderror"
                                >{{ old('alamat', $antrian->alamat) }}</textarea>
                                @error('alamat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="jenis_kelamin" class="form-label fw-semibold">Jenis Kelamin</label>
                                    <select
                                        id="jenis_kelamin"
                                        name="jenis_kelamin"
                                        class="form-select @error('jenis_kelamin') is-invalid @enderror"
                                    >
                                        <option value="laki-laki" {{ old('jenis_kelamin', $antrian->jenis_kelamin) == 'laki-laki' ? 'selected' : '' }}>
                                            Laki-laki
                                        </option>
                                        <option value="perempuan" {{ old('jenis_kelamin', $antrian->jenis_kelamin) == 'perempuan' ? 'selected' : '' }}>
                                            Perempuan
                                        </option>
                                    </select>
                                    @error('jenis_kelamin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="no_hp" class="form-label fw-semibold">No HP</label>
                                    <input
                                        type="text"
                                        id="no_hp"
                                        name="no_hp"
                                        class="form-control @error('no_hp') is-invalid @enderror"
                                        value="{{ old('no_hp', $antrian->no_hp) }}"
                                    >
                                    @error('no_hp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="pekerjaan" class="form-label fw-semibold">Pekerjaan</label>
                                    <input
                                        type="text"
                                        id="pekerjaan"
                                        name="pekerjaan"
                                        class="form-control @error('pekerjaan') is-invalid @enderror"
                                        value="{{ old('pekerjaan', $antrian->pekerjaan) }}"
                                    >
                                    @error('pekerjaan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- ================= PILIH POLI ================= --}}
                            <div class="mb-3">
                                <h5 class="fw-semibold mb-2">Pilih Poli / Klinik</h5>
                                <p class="text-muted mb-2" style="font-size: 0.9rem;">
                                    Klik salah satu poli untuk layanan Anda.
                                </p>

                                @php
                                    $poliOptions = [
                                        'umum'                 => 'Umum',
                                        'gigi'                 => 'Gigi',
                                        'tht'                  => 'THT',
                                        'balita'               => 'Balita',
                                        'kia & kb'             => 'KIA & KB',
                                        'nifas/pnc'            => 'Nifas / PNC',
                                        'lansia & disabilitas' => 'Lansia & Disabilitas',
                                    ];
                                    $currentPoli = strtolower(old('poli', $antrian->poli));
                                @endphp

                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($poliOptions as $key => $label)
                                        <button
                                            type="button"
                                            class="btn poli-btn {{ $currentPoli === $key ? 'btn-primary active' : 'btn-outline-primary' }}"
                                            data-poli="{{ $key }}"
                                        >
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>

                                <input type="hidden" name="poli" id="poliInput" value="{{ $currentPoli }}">

                                @error('poli')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">

                            {{-- ================= PILIH TANGGAL ================= --}}
                            <div class="mb-3">
                                <h5 class="fw-semibold mb-2">Pilih Tanggal Layanan</h5>
                                <p class="text-muted mb-3" style="font-size: 0.9rem;">
                                    Maksimal 6 hari ke depan. Hari Minggu tidak tersedia.
                                </p>

                                @php
                                    use Carbon\Carbon;

                                    $today   = Carbon::today();
                                    $dates   = [];
                                    $cursor  = $today->copy();

                                    while (count($dates) < 6) {
                                        if (! $cursor->isSunday()) {
                                            $dates[] = $cursor->copy();
                                        }
                                        $cursor->addDay();
                                    }

                                    $currentDate = old('tanggal_antrian', $antrian->tanggal_antrian);
                                @endphp

                                <div class="tanggal-grid">
                                    @foreach ($dates as $date)
                                        @php
                                            $isActive  = $date->toDateString() === $currentDate;
                                            $isSunday  = $date->isSunday();

                                            // catatan: ini masih pakai poli lama ($antrian->poli) sesuai kode kamu
                                            $antrianCount = \App\Models\Antrian::whereDate('tanggal_antrian', $date->toDateString())
                                                ->where('poli', $antrian->poli)
                                                ->count();
                                        @endphp

                                        <button
                                            type="button"
                                            class="tanggal-card {{ $isActive ? 'active' : '' }} {{ $isSunday ? 'disabled' : '' }}"
                                            data-date="{{ $date->toDateString() }}"
                                            @if($isSunday) disabled @endif
                                        >
                                            <div class="hari">{{ $date->translatedFormat('D') }}</div>
                                            <div class="tanggal">{{ $date->format('d') }}</div>
                                            <div class="bulan">{{ $date->translatedFormat('F Y') }}</div>
                                            <div class="jumlah">Antrian: {{ $antrianCount }}</div>
                                        </button>
                                    @endforeach
                                </div>

                                <input
                                    type="hidden"
                                    name="tanggal_antrian"
                                    id="tanggalInput"
                                    value="{{ $currentDate }}"
                                >

                                @error('tanggal_antrian')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end mt-4 gap-2">
                                <a href="{{ $backUrl }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    Simpan Perubahan
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ================== POLI ==================
        const poliButtons = document.querySelectorAll('.poli-btn');
        const poliInput   = document.getElementById('poliInput');

        poliButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                poliButtons.forEach(b => {
                    b.classList.remove('active', 'btn-primary');
                    b.classList.add('btn-outline-primary');
                });

                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary', 'active');

                poliInput.value = this.dataset.poli;
            });
        });

        // ================== TANGGAL ==================
        const tanggalCards = document.querySelectorAll('.tanggal-card');
        const tanggalInput = document.getElementById('tanggalInput');

        tanggalCards.forEach(card => {
            if (card.classList.contains('disabled')) return;

            card.addEventListener('click', function () {
                tanggalCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                tanggalInput.value = this.dataset.date;
            });
        });
    });
</script>
@endpush

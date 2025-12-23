@extends('layouts.dokter')

@section('title', 'Dashboard Dokter')

@section('content')
@php
  $user = auth()->user();

  /**
   * ✅ Ambil dari controller (DokterDashboardController)
   * Pastikan controller mengirim:
   * $poliKey, $poliLabel, $dokterLabel
   */
  $poliKey     = $poliKey     ?? 'umum';
  $poliLabel   = $poliLabel   ?? 'Poli Umum';
  $dokterLabel = $dokterLabel ?? 'Dokter Umum';

  // helper: mapping accent -> rgba bg halus
  $accentBg = [
    'primary'   => 'rgba(13,110,253,.12)',
    'warning'   => 'rgba(255,193,7,.16)',
    'info'      => 'rgba(13,202,240,.14)',
    'success'   => 'rgba(25,135,84,.14)',
    'danger'    => 'rgba(220,53,69,.14)',
    'secondary' => 'rgba(108,117,125,.14)',
    'dark'      => 'rgba(33,37,41,.12)',
  ];

  /**
   * ✅ META per poliKey standar (bukan string mentah DB)
   */
  $poliMeta = [
    'umum' => [
      'nama'   => 'Poli Umum',
      'icon'   => 'bi-clipboard2-pulse',
      'accent' => 'primary'
    ],
    'gigi' => [
      'nama'   => 'Poli Gigi',
      'icon'   => 'bi-emoji-smile',
      'accent' => 'warning'
    ],
    'tht' => [
      'nama'   => 'Poli THT',
      'icon'   => 'bi-ear',
      'accent' => 'info'
    ],
    'balita' => [
      'nama'   => 'Poli Balita',
      'icon'   => 'bi-person-arms-up',
      'accent' => 'success'
    ],
    'kia_kb' => [
      'nama'   => 'Poli KIA & KB',
      'icon'   => 'bi-heart-pulse',
      'accent' => 'danger'
    ],
    'nifas_pnc' => [
      'nama'   => 'Poli Nifas / PNC',
      'icon'   => 'bi-hospital',
      'accent' => 'secondary'
    ],
    'lansia_disabilitas' => [
      'nama'   => 'Poli Lansia & Disabilitas',
      'icon'   => 'bi-person-wheelchair',
      'accent' => 'dark'
    ],
  ];

  $meta = $poliMeta[$poliKey] ?? [
    'nama'   => $poliLabel,
    'icon'   => 'bi-clipboard2-pulse',
    'accent' => 'primary'
  ];

  $welcomeBg = $accentBg[$meta['accent']] ?? 'rgba(13,110,253,.12)';
@endphp

<div class="container-fluid">

  {{-- Welcome --}}
  <div class="card card-soft mb-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center"
             style="width:52px;height:52px;background:{{ $welcomeBg }};">
          <i class="bi {{ $meta['icon'] }} fs-3 text-{{ $meta['accent'] }}"></i>
        </div>

        <div>
          <div class="text-muted" style="font-size:13px;">Selamat datang,</div>
          <h4 class="mb-0 fw-bold">
            {{-- ✅ FIX: judul pakai label dari controller --}}
            {{ $dokterLabel }} — {{ $poliLabel }}
          </h4>
          <div class="text-muted" style="font-size:13px;">
            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
          </div>
        </div>
      </div>

      <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('dokter.antrian.index') }}" class="btn btn-{{ $meta['accent'] }} rounded-pill px-4">
          <i class="bi bi-list-check me-2"></i> Kelola Antrian
        </a>
        <a href="{{ route('dokter.statistik.index') }}" class="btn btn-outline-{{ $meta['accent'] }} rounded-pill px-4">
          <i class="bi bi-graph-up me-2"></i> Statistik
        </a>
      </div>
    </div>
  </div>

  {{-- Cards clickable --}}
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <a href="{{ route('dokter.antrian.index') }}" class="text-decoration-none">
        <div class="card card-soft h-100">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted" style="font-size:13px;">Antrian Hari Ini</div>
              <div class="fs-2 fw-bold">{{ $antrianHariIni ?? 0 }}</div>
              <div class="text-muted" style="font-size:12px;">Klik untuk melihat daftar</div>
            </div>
            <div class="rounded-circle d-flex align-items-center justify-content-center"
                 style="width:48px;height:48px;background:{{ $welcomeBg }};">
              <i class="bi bi-person-lines-fill fs-4 text-{{ $meta['accent'] }}"></i>
            </div>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a href="{{ route('dokter.antrian.index') }}" class="text-decoration-none">
        <div class="card card-soft h-100">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted" style="font-size:13px;">Sudah Dipanggil</div>
              <div class="fs-2 fw-bold">{{ $sudahDipanggil ?? 0 }}</div>
              <div class="text-muted" style="font-size:12px;">Klik untuk detail</div>
            </div>
            <div class="rounded-circle d-flex align-items-center justify-content-center"
                 style="width:48px;height:48px;background:{{ $welcomeBg }};">
              <i class="bi bi-telephone-outbound fs-4 text-{{ $meta['accent'] }}"></i>
            </div>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a href="{{ route('dokter.antrian.index') }}" class="text-decoration-none">
        <div class="card card-soft h-100">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted" style="font-size:13px;">Sisa Hari Ini</div>
              <div class="fs-2 fw-bold">{{ $sisaHariIni ?? 0 }}</div>
              <div class="text-muted" style="font-size:12px;">Belum dipanggil</div>
            </div>
            <div class="rounded-circle d-flex align-items-center justify-content-center"
                 style="width:48px;height:48px;background:{{ $welcomeBg }};">
              <i class="bi bi-hourglass-split fs-4 text-{{ $meta['accent'] }}"></i>
            </div>
          </div>
        </div>
      </a>
    </div>
  </div>

  {{-- Quick links + Tips + Status --}}
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card card-soft">
        <div class="card-body">
          <div class="fw-bold mb-2">Tautan Cepat</div>
          <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('dokter.antrian.index') }}" class="btn btn-light border rounded-pill px-3">
              <i class="bi bi-list-check me-2"></i> Daftar Antrian
            </a>
            <a href="{{ route('dokter.riwayat.index') }}" class="btn btn-light border rounded-pill px-3">
              <i class="bi bi-clock-history me-2"></i> Riwayat
            </a>
            <a href="{{ route('dokter.statistik.index') }}" class="btn btn-light border rounded-pill px-3">
              <i class="bi bi-graph-up me-2"></i> Statistik Poli
            </a>
          </div>

          <div class="mt-3 text-muted" style="font-size:13px;">
            Tip: Gunakan tombol <b>Panggil</b> untuk memulai layanan. Diagnosa aktif setelah pasien dipanggil.
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card card-soft">
        <div class="card-body">
          <div class="fw-bold mb-2">Status Hari Ini</div>

          <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-muted">Poli</span>
            {{-- ✅ FIX: tampilkan dari controller --}}
            <span class="fw-semibold">{{ $poliLabel }}</span>
          </div>

          <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-muted">Pasien aktif</span>
            <span class="fw-semibold">{{ $pasienAktif ?? '-' }}</span>
          </div>

          <div class="d-flex justify-content-between py-2">
            <span class="text-muted">Update</span>
            <span class="fw-semibold">{{ now()->format('H:i') }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

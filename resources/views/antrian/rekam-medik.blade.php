@extends('layouts.main')

@section('title', 'Diagnosa / Rekam Medik')

@php
  use Carbon\Carbon;
  Carbon::setLocale('id');

  $called = ((int) $antrian->is_call === 1);

  // ✅ tombol kembali: prioritas ?back=..., fallback ke hasil cari pakai no_ktp, terakhir previous
  $backUrl = request('back');

  if (!$backUrl && !empty($antrian->no_ktp)) {
      $backUrl = route('antrian.cari', ['no_ktp' => $antrian->no_ktp]);
  }

  if (!$backUrl) {
      $backUrl = url()->previous();
  }
@endphp

@section('content')
<section class="pk-rekam-wrap">
  <div class="container">

    {{-- HEADER (CENTER + RAPIH) --}}
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-9">

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 pk-rekam-head">
          <div>
            <h3 class="pk-rekam-title">Diagnosa / Rekam Medik</h3>

            <div class="pk-rekam-subtitle">
              <span class="pk-pill me-2">
                <span class="dot"></span>
                No Antrian: <b>{{ $antrian->no_antrian }}</b>
              </span>

              <span class="pk-pill">
                <span class="dot"></span>
                Poli: <b class="text-uppercase">{{ $antrian->poli }}</b>
              </span>
            </div>
          </div>

          {{-- ✅ Kembali ke hasil pencarian, bukan ke /antrian/cari --}}
          <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm pk-back-btn">
            <i class="bi bi-arrow-left me-1"></i> Kembali
          </a>
        </div>

        {{-- FLASH --}}
        @if(session('success'))
          <div class="alert alert-success border-0 rounded-4 shadow-sm">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
          </div>
        @endif

        @if(session('error'))
          <div class="alert alert-danger border-0 rounded-4 shadow-sm">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
          </div>
        @endif

        {{-- Jika belum dipanggil --}}
        @if(!$called)
          <div class="pk-alert mt-3">
            <i class="bi bi-info-circle me-2"></i>
            Diagnosa belum bisa dilihat karena pasien belum dipanggil dokter.
          </div>

        @else
          {{-- CARD UTAMA --}}
          <div class="pk-rekam-card mt-3">
            <div class="pk-card-body">

              {{-- INFO ATAS --}}
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="pk-rekam-info">
                    <div class="label">Nama</div>
                    <div class="value">{{ $antrian->nama }}</div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="pk-rekam-info">
                    <div class="label">Tanggal Kunjungan</div>
                    <div class="value">
                      {{ Carbon::parse($antrian->tanggal_antrian)->translatedFormat('d F Y') }}
                    </div>
                  </div>
                </div>
              </div>

              {{-- Isi Rekam --}}
              @if(!$rekam)
                <div class="pk-alert mt-3">
                  <i class="bi bi-hourglass-split me-2"></i>
                  Diagnosa belum tersedia. Silakan tunggu dokter mengisi diagnosa.
                </div>
              @else
                <div class="pk-section">
                  <div class="sec-label">Diagnosa</div>
                  <div class="pk-content">
                    {!! nl2br(e($rekam->diagnosa)) !!}
                  </div>
                </div>

                <div class="pk-section">
                  <div class="sec-label">Catatan / Anjuran</div>
                  <div class="pk-content is-soft">
                    {!! nl2br(e($rekam->catatan ?? '-')) !!}
                  </div>
                </div>

                <div class="pk-section">
                  <div class="sec-label">Resep / Obat</div>
                  <div class="pk-content is-soft">
                    {!! nl2br(e($rekam->resep ?? '-')) !!}
                  </div>
                </div>
              @endif

            </div>
          </div>
        @endif

      </div>
    </div>

  </div>
</section>
@endsection

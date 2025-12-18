@extends('layouts.main')
@include('partials.navbar')

@php
  use Carbon\Carbon;
  Carbon::setLocale('id');
@endphp

@section('content')
<section class="py-5" style="margin-top: 90px;">
  <div class="container">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <div>
        <h3 class="fw-bold mb-0">Diagnosa / Rekam Medik</h3>
        <div class="text-muted small">
          No Antrian: <b>{{ $antrian->no_antrian }}</b> • Poli: <b class="text-uppercase">{{ $antrian->poli }}</b>
        </div>
      </div>

      <a href="{{ route('antrian.cari') }}" class="btn btn-outline-secondary btn-sm">
        ← Kembali
      </a>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body">

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div class="p-3 rounded bg-light">
              <div class="small text-muted">Nama</div>
              <div class="fw-bold">{{ $antrian->nama }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 rounded bg-light">
              <div class="small text-muted">Tanggal Kunjungan</div>
              <div class="fw-bold">
                {{ Carbon::parse($antrian->tanggal_antrian)->translatedFormat('d F Y') }}
              </div>
            </div>
          </div>
        </div>

        @if(!$rekam)
          <div class="alert alert-warning mb-0">
            Diagnosa belum tersedia.
            @if((int)$antrian->is_call !== 1)
              Pasien belum dipanggil dokter.
            @else
              Silakan tunggu dokter mengisi diagnosa.
            @endif
          </div>
        @else
          <div class="mb-3">
            <div class="small text-muted mb-1">Diagnosa</div>
            <div class="p-3 rounded border bg-white">
              {!! nl2br(e($rekam->diagnosa)) !!}
            </div>
          </div>

          <div class="mb-3">
            <div class="small text-muted mb-1">Catatan / Anjuran</div>
            <div class="p-3 rounded border bg-white">
              {!! nl2br(e($rekam->catatan ?? '-')) !!}
            </div>
          </div>

          <div>
            <div class="small text-muted mb-1">Resep / Obat</div>
            <div class="p-3 rounded border bg-white">
              {!! nl2br(e($rekam->resep ?? '-')) !!}
            </div>
          </div>
        @endif

      </div>
    </div>

  </div>
</section>
@endsection

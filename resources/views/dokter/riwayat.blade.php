{{-- resources/views/dokter/riwayat.blade.php --}}
@php
  use Carbon\Carbon;
  use Illuminate\Support\Str;

  Carbon::setLocale('id');
@endphp

@extends('layouts.dokter')

@section('title', 'Riwayat Pasien')
@section('subtitle', 'Riwayat berdasarkan No KTP')
@section('header', 'Riwayat Berdasarkan No KTP')

@section('content')
<style>
  .btn-pill{ border-radius:999px; font-weight:900; }

  .page-head{
    background: linear-gradient(135deg, rgba(13,110,253,.06) 0%, rgba(25,135,84,.05) 100%);
    border: 1px solid rgba(233,236,239,.9);
    border-radius: 18px;
    padding: 18px 18px;
  }

  .meta-small{
    font-size: 12px;
    color:#6c757d;
    font-weight: 800;
    letter-spacing: .03em;
    text-transform: uppercase;
  }

  .badge-soft{
    padding:.38rem .65rem;
    border-radius:999px;
    font-weight:900;
    font-size:12px;
  }
  .badge-poli{ background: rgba(13,110,253,.12); color:#0d6efd; }
  .badge-date{ background: rgba(25,135,84,.12); color:#198754; }

  .visit-card{
    border: 1px solid #e9ecef;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 12px 35px rgba(17,24,39,.06);
    background:#fff;
  }
  .visit-top{
    padding: 14px 16px;
    border-bottom: 1px solid #f1f3f5;
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
  }
  .visit-body{
    padding: 14px 16px 16px;
  }

  .sec-title{
    font-weight: 900;
    font-size: 12px;
    color:#495057;
    letter-spacing:.03em;
    text-transform: uppercase;
    margin-bottom: 6px;
  }

  .sec-box{
    background:#f8fafc;
    border: 1px solid #eef2f6;
    border-radius: 14px;
    padding: 12px 12px;
  }

  .text-pretty{
    white-space: pre-wrap;
    word-break: break-word;
    color:#212529;
    font-size: 14px;
    line-height: 1.5;
    margin:0;
  }

  .link-more{
    font-weight: 900;
    font-size: 12px;
    text-decoration: none;
  }

  .divider-soft{
    height:1px;
    background: #eef2f6;
    margin: 12px 0;
  }
</style>

{{-- HEADER --}}
<div class="page-head mb-3">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
      <div class="meta-small mb-1">Riwayat Rekam Medik</div>
      <h4 class="fw-bold mb-1">Riwayat Berdasarkan No KTP</h4>

      <div class="text-muted" style="font-size:13px;">
        <div>No KTP: <b>{{ $noKtp }}</b></div>
        @if(!empty($nama))
          <div>Nama: <b>{{ $nama }}</b></div>
        @endif
      </div>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('dokter.riwayat.index') }}" class="btn btn-outline-primary btn-pill">
        ← Riwayat (Search)
      </a>
      <a href="{{ route('dokter.antrian.index') }}" class="btn btn-outline-secondary btn-pill">
        ← Antrian
      </a>
    </div>
  </div>
</div>

{{-- CONTENT --}}
@if($riwayat->isEmpty())
  <div class="card card-soft">
    <div class="card-body text-center py-5">
      <div class="fw-bold mb-1">Belum ada data riwayat</div>
      <div class="text-muted" style="font-size:13px;">Belum ada diagnosa tersimpan untuk pasien ini.</div>
    </div>
  </div>
@else
  <div class="d-flex flex-column gap-3">
    @foreach($riwayat as $i => $r)
      @php
        $tgl = $r->tanggal_kunjungan ? Carbon::parse($r->tanggal_kunjungan)->translatedFormat('d M Y') : '-';
        $poli = $r->poli ?? '-';

        $diagnosa = (string)($r->diagnosa ?? '');
        $catatan  = (string)($r->catatan ?? '');
        $resep    = (string)($r->resep ?? '');

        $limit = 200;

        $needCatatan = Str::length($catatan) > $limit;
        $needResep   = Str::length($resep) > $limit;

        $catatanShort = $catatan !== '' ? Str::limit($catatan, $limit) : '-';
        $resepShort   = $resep !== '' ? Str::limit($resep, $limit) : '-';

        $catId = "catatanFull{$i}";
        $rspId = "resepFull{$i}";
      @endphp

      <div class="visit-card">
        <div class="visit-top">
          <div>
            <div class="meta-small">Kunjungan</div>
            <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
              <span class="badge-soft badge-date">
                <i class="bi bi-calendar-event me-1"></i>{{ $tgl }}
              </span>
              <span class="badge-soft badge-poli">
                <i class="bi bi-hospital me-1"></i>POLI {{ strtoupper($poli) }}
              </span>
            </div>
          </div>

          <div class="text-end">
            <div class="meta-small">Dokumen</div>
            <div class="text-muted" style="font-size:13px;">Rekam Medik</div>
          </div>
        </div>

        <div class="visit-body">
          {{-- DIAGNOSA --}}
          <div class="sec-title">Diagnosa</div>
          <div class="sec-box">
            <p class="text-pretty">{{ $diagnosa !== '' ? $diagnosa : '-' }}</p>
          </div>

          <div class="divider-soft"></div>

          <div class="row g-3">
            {{-- CATATAN --}}
            <div class="col-lg-7">
              <div class="sec-title">Catatan / Anjuran</div>
              <div class="sec-box">
                <p class="text-pretty">{{ $catatan !== '' ? $catatanShort : '-' }}</p>

                @if($needCatatan)
                  <div class="mt-2">
                    <a class="link-more" data-bs-toggle="collapse" href="#{{ $catId }}" role="button" aria-expanded="false" aria-controls="{{ $catId }}">
                      Lihat selengkapnya
                    </a>
                  </div>
                  <div class="collapse mt-2" id="{{ $catId }}">
                    <div class="sec-box" style="background:#fff;">
                      <p class="text-pretty">{{ $catatan }}</p>
                      <div class="mt-2">
                        <a class="link-more" data-bs-toggle="collapse" href="#{{ $catId }}" role="button" aria-expanded="true" aria-controls="{{ $catId }}">
                          Tutup
                        </a>
                      </div>
                    </div>
                  </div>
                @endif
              </div>
            </div>

            {{-- RESEP --}}
            <div class="col-lg-5">
              <div class="sec-title">Resep / Obat Dokter</div>
              <div class="sec-box">
                <p class="text-pretty">{{ $resep !== '' ? $resepShort : '-' }}</p>

                @if($needResep)
                  <div class="mt-2">
                    <a class="link-more" data-bs-toggle="collapse" href="#{{ $rspId }}" role="button" aria-expanded="false" aria-controls="{{ $rspId }}">
                      Lihat selengkapnya
                    </a>
                  </div>
                  <div class="collapse mt-2" id="{{ $rspId }}">
                    <div class="sec-box" style="background:#fff;">
                      <p class="text-pretty">{{ $resep }}</p>
                      <div class="mt-2">
                        <a class="link-more" data-bs-toggle="collapse" href="#{{ $rspId }}" role="button" aria-expanded="true" aria-controls="{{ $rspId }}">
                          Tutup
                        </a>
                      </div>
                    </div>
                  </div>
                @endif
              </div>
            </div>
          </div>

        </div>
      </div>
    @endforeach
  </div>
@endif
@endsection

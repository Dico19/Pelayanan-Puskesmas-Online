{{-- resources/views/dokter/riwayat_index.blade.php --}}
@php
  use Carbon\Carbon;
  Carbon::setLocale('id');
@endphp

@extends('layouts.dokter')

@section('title', 'Riwayat Rekam Medik')
@section('subtitle', 'Cari berdasarkan Nama / No KTP / Poli / Diagnosa')
@section('header', 'Riwayat Rekam Medik')

@section('content')
<style>
  .btn-pill{ border-radius:999px; font-weight:900; }
  .table thead th{
    font-size:12px; letter-spacing:.06em; text-transform:uppercase;
    color:#6c757d; border-bottom:1px solid #e9ecef;
  }
  .table tbody td{ vertical-align:middle; }
  .badge-soft{ padding:.35rem .6rem; border-radius:999px; font-weight:900; font-size:12px; }
  .badge-poli{ background: rgba(13,110,253,.12); color:#0d6efd; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-1 fw-bold">Riwayat Rekam Medik</h4>
    <div class="text-muted" style="font-size:13px;">Cari berdasarkan Nama / No KTP / Poli / Diagnosa</div>
  </div>

  <a href="{{ route('dokter.antrian.index') }}" class="btn btn-outline-primary btn-pill">
    ← Kembali ke Antrian
  </a>
</div>

<div class="card card-soft mb-3">
  <div class="card-body">
    <form method="GET" action="{{ route('dokter.riwayat.index') }}" class="row g-2 align-items-center">
      <div class="col-md-9">
        <input type="text" name="q" value="{{ $q ?? '' }}"
               class="form-control"
               placeholder="Cari nama / no KTP / poli / diagnosa...">
      </div>
      <div class="col-md-3 d-grid">
        <button class="btn btn-primary btn-pill">Cari</button>
      </div>
    </form>
  </div>
</div>

<div class="card card-soft">
  <div class="table-responsive p-3">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Nama Pasien</th> {{-- ✅ baru --}}
          <th>No KTP</th>
          <th>Poli</th>
          <th>Diagnosa</th>
          <th>Catatan</th>
          <th>Resep</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($riwayat as $row)
          @php
            $tgl = $row->tanggal_kunjungan
              ? Carbon::parse($row->tanggal_kunjungan)->translatedFormat('d M Y')
              : '-';
            $nama = $row->nama_pasien ?? '-';
          @endphp
          <tr>
            <td class="fw-bold">{{ $tgl }}</td>
            <td>{{ $nama }}</td> {{-- ✅ tampil nama --}}
            <td>{{ $row->no_ktp }}</td>
            <td><span class="badge-soft badge-poli">{{ $row->poli ?? '-' }}</span></td>
            <td>{{ \Illuminate\Support\Str::limit((string)$row->diagnosa, 40) }}</td>
            <td>{{ \Illuminate\Support\Str::limit((string)$row->catatan, 30) }}</td>
            <td>{{ \Illuminate\Support\Str::limit((string)$row->resep, 30) }}</td>
            <td class="text-end">
              {{-- ✅ TANPA target _blank -> tidak buka tab baru --}}
              <a href="{{ route('dokter.riwayat.show', $row->no_ktp) }}"
                 class="btn btn-outline-secondary btn-sm btn-pill">
                Lihat
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center py-5">
              <div class="fw-bold mb-1">Belum ada riwayat</div>
              <div class="text-muted" style="font-size:13px;">Data akan muncul setelah dokter menyimpan diagnosa.</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if(method_exists($riwayat, 'links'))
    <div class="p-3">
      {{ $riwayat->links() }}
    </div>
  @endif
</div>
@endsection

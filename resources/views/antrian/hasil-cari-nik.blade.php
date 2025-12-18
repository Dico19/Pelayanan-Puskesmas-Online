@extends('layouts.main')
@include('partials.navbar')

@section('content')
<section id="hasil-antrian" class="py-5" style="margin-top: 90px;">
  <div class="container">

    {{-- HEADER --}}
    <div class="page-head text-center mb-4">
      <h2 class="fw-bold mb-1 text-uppercase">Hasil Pencarian Antrian</h2>
      <div class="text-muted small">
        Silakan periksa detail antrian Anda di bawah ini.
      </div>
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

    {{-- TOP BAR --}}
    <div class="topbar d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <span class="badge text-bg-primary-subtle text-primary rounded-pill px-3 py-2">
          <i class="bi bi-person-vcard me-1"></i> NIK: <span class="fw-semibold">{{ $nik }}</span>
        </span>
        <span class="badge text-bg-light border rounded-pill px-3 py-2">
          <i class="bi bi-list-check me-1"></i> Total: <span class="fw-semibold">{{ $antrians->count() }}</span>
        </span>
      </div>

      <a href="{{ route('antrian.cari') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Cari NIK lain
      </a>
    </div>

    {{-- CARD TABLE --}}
    <div class="card card-soft border-0 rounded-4 shadow-sm overflow-hidden">
      <div class="card-header bg-white border-0 py-3 px-3 px-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div class="fw-semibold">
            <i class="bi bi-table me-1 text-primary"></i> Daftar Antrian
          </div>
          <div class="text-muted small">
            Status dan akses tombol akan menyesuaikan kondisi antrian.
          </div>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase">
              <tr class="text-center">
                <th style="width:70px;">No</th>
                <th style="width:120px;">No Antrian</th>
                <th>Nama</th>
                <th style="width:140px;">Poli</th>
                <th style="width:140px;">Tgl. Antrian</th>
                <th style="width:150px;">Status</th>
                <th style="width:320px;">Aksi</th>
              </tr>
            </thead>

            <tbody>
              @foreach ($antrians as $index => $antrian)
                @php
                  $called = ((int)$antrian->is_call === 1);
                @endphp

                <tr>
                  <td class="text-center">{{ $index + 1 }}</td>

                  <td class="text-center">
                    <span class="fw-bold">{{ $antrian->no_antrian }}</span>
                  </td>

                  <td>
                    <div class="fw-semibold">{{ $antrian->nama }}</div>
                    <div class="text-muted small">NIK: {{ $antrian->no_ktp }}</div>
                  </td>

                  <td class="text-center text-uppercase fw-semibold">
                    {{ $antrian->poli }}
                  </td>

                  <td class="text-center">
                    <span class="text-muted">{{ $antrian->tanggal_antrian }}</span>
                  </td>

                  <td class="text-center">
                    @if($called)
                      <span class="badge bg-primary rounded-pill px-3 py-2">
                        <i class="bi bi-telephone-check me-1"></i> Sudah dipanggil
                      </span>
                    @else
                      <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                        <i class="bi bi-hourglass-split me-1"></i> Menunggu
                      </span>
                    @endif
                  </td>

                  <td class="text-center">
                    <div class="d-flex flex-wrap justify-content-center gap-2">

                      {{-- DIAGNOSA --}}
                      <a href="{{ route('antrian.rekam-medik', $antrian->id) }}"
                         class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="bi bi-clipboard2-pulse me-1"></i>
                        {{ $called ? 'Lihat Diagnosa' : 'Diagnosa' }}
                      </a>

                      @if(!$called)
                        {{-- EDIT --}}
                        <a href="{{ route('antrian.edit', $antrian->id) }}"
                           class="btn btn-warning btn-sm rounded-pill px-3">
                          <i class="bi bi-pencil-square me-1"></i> Edit
                        </a>

                        {{-- STATUS --}}
                        <a href="{{ route('antrian.status', $antrian->id) }}"
                           class="btn btn-info btn-sm rounded-pill px-3 text-white">
                          <i class="bi bi-geo-alt me-1"></i> Status
                        </a>

                        {{-- HAPUS --}}
                        <form action="{{ route('antrian.destroy', $antrian->id) }}"
                              method="POST"
                              onsubmit="return confirm('Yakin ingin menghapus antrian ini?')">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">
                            <i class="bi bi-trash3 me-1"></i> Hapus
                          </button>
                        </form>
                      @else
                        {{-- tombol nonaktif tampil tetap rapi --}}
                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" disabled title="Tidak tersedia setelah dipanggil">
                          <i class="bi bi-pencil-square me-1"></i> Edit
                        </button>
                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" disabled title="Tidak tersedia setelah dipanggil">
                          <i class="bi bi-geo-alt me-1"></i> Status
                        </button>
                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" disabled title="Tidak tersedia setelah dipanggil">
                          <i class="bi bi-trash3 me-1"></i> Hapus
                        </button>
                      @endif

                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>

          </table>
        </div>
      </div>
    </div>

    {{-- INFO PROFESIONAL --}}
    <div class="mt-3">
      <div class="notice card border-0 rounded-4 shadow-sm">
        <div class="card-body d-flex gap-3">
          <div class="notice-icon">
            <i class="bi bi-shield-lock"></i>
          </div>
          <div>
            <div class="fw-semibold mb-1">Kebijakan Akses Antrian</div>
            <div class="text-muted small mb-0">
              Untuk menjaga integritas layanan, antrian yang <b>sudah dipanggil</b> tidak dapat diubah atau dihapus dari sisi pasien.
              Setelah dipanggil, silakan buka menu <b>Diagnosa</b> untuk melihat hasil pemeriksaan, catatan dokter, dan resep (jika ada).
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<style>
  #hasil-antrian .card-soft { border-radius: 18px; }
  #hasil-antrian .table td, #hasil-antrian .table th { font-size: .92rem; }
  #hasil-antrian .btn-sm { padding: .38rem .85rem; font-size: .78rem; }
  #hasil-antrian .table-hover tbody tr:hover td { background: rgba(13,110,253,.035); }

  .notice-icon{
    width:42px; height:42px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    background: rgba(13,110,253,.08);
    color: #0d6efd;
    font-size: 20px;
    flex: 0 0 auto;
  }

  /* dark mode support (kalau body punya .dark-mode) */
  .dark-mode #hasil-antrian .card,
  .dark-mode #hasil-antrian .card-header { background:#0f172a !important; color:#e5e7eb; }
  .dark-mode #hasil-antrian .table { color:#e5e7eb; }
  .dark-mode #hasil-antrian .table thead { color:#111827; }
  .dark-mode #hasil-antrian .table-hover tbody tr:hover td { background: rgba(255,255,255,.04); }
</style>
@endsection

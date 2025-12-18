@php
    use Carbon\Carbon;
    Carbon::setLocale('id');

    $dataList = $data ?? ($antrians ?? []);
    $rows = collect($dataList);

    $today = $today ?? now()->toDateString();
    $hariIniLabel = Carbon::parse($today)->translatedFormat('d M Y');
    $poliLabel = $poli ?? '-';

    // Status aman (kalau belum punya kolom status, fallback pakai is_call)
    $getStatus = function($r) {
        $s = strtolower(trim((string)($r->status ?? '')));
        if ($s === '') $s = ((int)$r->is_call === 1) ? 'dipanggil' : 'menunggu';
        return $s;
    };

    $todayTotal  = $rows->filter(fn($r) => Carbon::parse($r->tanggal_antrian)->toDateString() === $today)->count();
    $todayCalled = $rows->filter(fn($r) => Carbon::parse($r->tanggal_antrian)->toDateString() === $today && $getStatus($r) !== 'menunggu')->count();
    $todayLeft   = max(0, $todayTotal - $todayCalled);

    // Pasien aktif (prioritas: dilayani, lalu dipanggil)
    $active = $rows->first(fn($r) => Carbon::parse($r->tanggal_antrian)->toDateString() === $today && $getStatus($r) === 'dilayani')
           ?? $rows->first(fn($r) => Carbon::parse($r->tanggal_antrian)->toDateString() === $today && $getStatus($r) === 'dipanggil');

    $nextWait = $rows->first(fn($r) => Carbon::parse($r->tanggal_antrian)->toDateString() === $today && $getStatus($r) === 'menunggu');

    $badgeClass = fn($s) => match($s){
        'dilayani' => 'badge-serve',
        'dipanggil' => 'badge-called',
        'selesai' => 'badge-done',
        'dilewati','lewati' => 'badge-skip',
        default => 'badge-wait',
    };

    $badgeText = fn($s) => match($s){
        'dilayani' => 'Sedang dilayani',
        'dipanggil' => 'Dipanggil',
        'selesai' => 'Selesai',
        'dilewati','lewati' => 'Dilewati',
        default => 'Menunggu',
    };
@endphp

@extends('layouts.dokter')

@section('title', 'Antrian Dokter')
@section('subtitle')
    Selamat datang di dashboard antrian poli <b>{{ $poliLabel }}</b> • Hari ini: <b>{{ $hariIniLabel }}</b>
@endsection
@section('header', 'Antrian Poli ' . $poliLabel)

@section('content')
<style>
  .stat-card { padding:18px; }
  .stat-label { color:#6c757d; font-weight:700; font-size: 13px; }
  .stat-value { font-size: 28px; font-weight: 900; margin-top: 6px; }

  .badge-soft{ padding:.45rem .7rem; border-radius:999px; font-weight:800; font-size:12px; display:inline-flex; align-items:center; gap:.4rem;}
  .badge-called{ background: rgba(13,110,253,.12); color:#0d6efd; }
  .badge-wait  { background: rgba(255,193,7,.20); color:#b58100; }
  .badge-serve { background: rgba(32,201,151,.14); color:#0f766e; }
  .badge-done  { background: rgba(25,135,84,.14); color:#198754; }
  .badge-skip  { background: rgba(220,53,69,.12); color:#dc3545; }
  .badge-today { background: rgba(25,135,84,.12); color:#198754; }

  .btn-pill { border-radius: 999px; font-weight: 900; }
  .btn-pill-sm { border-radius:999px; padding:7px 12px; font-weight:900; }

  .table thead th{
    font-size: 12px; letter-spacing:.06em; text-transform: uppercase;
    color:#6c757d; border-bottom: 1px solid #e9ecef;
  }
  .table tbody td{ vertical-align: middle; }

  .panel-card{
    border: 0;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(17,24,39,.08);
    background: #fff;
  }
  .panel-top{
    background: linear-gradient(180deg, rgba(13,110,253,.08) 0%, rgba(13,110,253,0) 100%);
    border-radius: 18px;
    padding: 16px;
  }
  .meta{ display:flex; flex-wrap:wrap; gap:10px 14px; font-size:13px; color:#6c757d; }
  .meta b{ color:#111827; }
</style>

{{-- PANEL ATAS: INFO SAJA (tidak ada tombol agar tidak double) --}}
<div class="row g-3 mb-3">
  <div class="col-lg-5">
    <div class="panel-card">
      <div class="panel-top d-flex align-items-start justify-content-between">
        <div>
          <div class="text-muted fw-semibold" style="font-size:13px;">Sedang Dilayani</div>
          @if($active)
            @php $st = $getStatus($active); @endphp
            <h4 class="fw-black mb-0 mt-1" style="font-weight:900;">
              {{ $active->no_antrian ?? '-' }} • {{ $active->nama ?? '-' }}
            </h4>
            <div class="meta mt-2">
              <div>No KTP: <b>{{ $active->no_ktp ?? '-' }}</b></div>
              <div>Poli: <b>{{ $poliLabel }}</b></div>
              <div>Tgl: <b>{{ Carbon::parse($active->tanggal_antrian)->translatedFormat('d M Y') }}</b></div>
            </div>
          @else
            <h4 class="fw-black mb-0 mt-1" style="font-weight:900;">Belum ada pasien aktif</h4>
            <div class="text-muted mt-2" style="font-size:13px;">
              Aksi dilakukan lewat tombol di tabel (Panggil / Mulai / Selesai / Lewatkan).
            </div>
          @endif
        </div>

        @if($active)
          @php $st = $getStatus($active); @endphp
          <span class="badge-soft {{ $badgeClass($st) }}">
            <i class="bi bi-activity"></i> {{ $badgeText($st) }}
          </span>
        @else
          <span class="badge-soft badge-wait"><i class="bi bi-info-circle"></i> Menunggu</span>
        @endif
      </div>

      <div class="px-3 pb-3">
        <div class="text-muted" style="font-size:13px;">
          @if($nextWait)
            Berikutnya: <b>{{ $nextWait->no_antrian }}</b> • {{ $nextWait->nama }}
          @else
            Tidak ada antrian menunggu untuk hari ini.
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="row g-3">
      <div class="col-md-4">
        <div class="card card-soft stat-card">
          <div class="stat-label">Antrian Hari Ini</div>
          <div class="stat-value">{{ $todayTotal }}</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-soft stat-card">
          <div class="stat-label">Sudah Diproses (Hari Ini)</div>
          <div class="stat-value">{{ $todayCalled }}</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-soft stat-card">
          <div class="stat-label">Sisa Hari Ini</div>
          <div class="stat-value">{{ $todayLeft }}</div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- TABEL --}}
<div class="card card-soft">
  <div class="d-flex align-items-center justify-content-between px-4 pt-4">
    <div>
      <h5 class="mb-0 fw-bold">Daftar Antrian Poli {{ $poliLabel }}</h5>
      <div class="text-muted mt-1" style="font-size:13px">
        Tombol <b>Panggil / Mulai / Selesai / Lewatkan</b> aktif untuk <b>hari ini</b>.
        <b>Diagnosa</b> aktif setelah pasien <b>dipanggil</b>.
      </div>
    </div>
  </div>

  <div class="table-responsive px-3 pb-3 pt-2">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>No Antrian</th>
          <th>Nama</th>
          <th>Alamat</th>
          <th>JK</th>
          <th>No HP</th>
          <th>No KTP</th>
          <th>Tgl Antrian</th>
          <th>Status</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>

      <tbody>
      @forelse ($dataList as $row)
        @php
          $rowDate = Carbon::parse($row->tanggal_antrian)->toDateString();
          $isToday = ($rowDate === $today);

          $status = $getStatus($row);

          $canCall    = $isToday && $status === 'menunggu';
          $canRecall  = $isToday && in_array($status, ['dipanggil','dilayani'], true);
          $canStart   = $isToday && $status === 'dipanggil';
          $canFinish  = $isToday && in_array($status, ['dipanggil','dilayani'], true);
          $canSkip    = $isToday && in_array($status, ['menunggu','dipanggil'], true);
          $canDiag    = $isToday && ((int)$row->is_call === 1 || in_array($status, ['dipanggil','dilayani','selesai'], true));

          $tglLabel = Carbon::parse($row->tanggal_antrian)->translatedFormat('d F Y');
          $collapseId = 'diag-'.$row->id;
        @endphp

        <tr>
          <td class="fw-bold">{{ $row->no_antrian ?? '-' }}</td>
          <td>{{ $row->nama ?? '-' }}</td>
          <td>{{ $row->alamat ?? '-' }}</td>
          <td>{{ $row->jenis_kelamin ?? '-' }}</td>
          <td>{{ $row->no_hp ?? '-' }}</td>
          <td>{{ $row->no_ktp ?? '-' }}</td>
          <td>
            <div class="fw-bold">{{ $tglLabel }}</div>
            @if($isToday)
              <span class="badge-soft badge-today"><i class="bi bi-calendar2-check"></i> Hari ini</span>
            @endif
          </td>
          <td>
            <span class="badge-soft {{ $badgeClass($status) }}"><i class="bi bi-dot"></i> {{ $badgeText($status) }}</span>
          </td>

          <td class="text-end">
            <div class="d-flex flex-wrap justify-content-end gap-2">

              {{-- Panggil --}}
              <form method="POST" action="{{ route('dokter.antrian.panggil', $row->id) }}">
                @csrf
                <button type="submit" class="btn btn-success btn-pill-sm" {{ $canCall ? '' : 'disabled' }}>
                  <i class="bi bi-telephone-fill me-1"></i> Panggil
                </button>
              </form>

              {{-- Panggil ulang --}}
              <form method="POST" action="{{ route('dokter.antrian.panggilUlang', $row->id) }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-pill-sm" {{ $canRecall ? '' : 'disabled' }}>
                  <i class="bi bi-telephone-repeat me-1"></i> Ulang
                </button>
              </form>

              {{-- Mulai layanan --}}
              <form method="POST" action="{{ route('dokter.antrian.mulai', $row->id) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-pill-sm" {{ $canStart ? '' : 'disabled' }}>
                  <i class="bi bi-play-circle me-1"></i> Mulai
                </button>
              </form>

              {{-- Selesai --}}
              <form method="POST" action="{{ route('dokter.antrian.selesai', $row->id) }}">
                @csrf
                <button type="submit" class="btn btn-success btn-pill-sm" {{ $canFinish ? '' : 'disabled' }}>
                  <i class="bi bi-check2-circle me-1"></i> Selesai
                </button>
              </form>

              {{-- Lewatkan --}}
              <form method="POST" action="{{ route('dokter.antrian.lewati', $row->id) }}"
                    onsubmit="return confirm('Lewatkan antrian {{ $row->no_antrian }}?')">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-pill-sm" {{ $canSkip ? '' : 'disabled' }}>
                  <i class="bi bi-skip-forward me-1"></i> Lewati
                </button>
              </form>

              {{-- Riwayat --}}
              <a class="btn btn-outline-secondary btn-pill-sm"
                 href="{{ route('dokter.riwayat.show', $row->no_ktp) }}" target="_blank">
                <i class="bi bi-clock-history me-1"></i> Riwayat
              </a>

              {{-- Diagnosa --}}
              <button class="btn btn-dark btn-pill-sm"
                      type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#{{ $collapseId }}"
                      {{ $canDiag ? '' : 'disabled' }}>
                <i class="bi bi-clipboard2-pulse me-1"></i> Diagnosa
              </button>
            </div>
          </td>
        </tr>

        {{-- Form Diagnosa --}}
        <tr class="collapse" id="{{ $collapseId }}">
          <td colspan="9">
            <div class="p-3" style="background:#f8fafc;border:1px solid #e9ecef;border-radius:16px;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-bold">Form Diagnosa • {{ $row->no_antrian }} • {{ $row->nama }}</div>
                <button class="btn btn-sm btn-outline-secondary btn-pill"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#{{ $collapseId }}">
                  Tutup
                </button>
              </div>

              <form method="POST" action="{{ route('dokter.rekam-medik.store', $row->id) }}">
                @csrf
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Diagnosa</label>
                    <textarea name="diagnosa" class="form-control" rows="3" required></textarea>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Catatan / Anjuran</label>
                    <textarea name="catatan" class="form-control" rows="3"></textarea>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-bold">Resep / Obat</label>
                    <textarea name="resep" class="form-control" rows="3"></textarea>
                  </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                  <button type="submit" class="btn btn-success btn-pill">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Diagnosa
                  </button>
                </div>
              </form>
            </div>
          </td>
        </tr>

      @empty
        <tr>
          <td colspan="9" class="text-center py-5">
            <div class="fw-bold mb-1">Belum ada data antrian</div>
            <div class="text-muted" style="font-size:13px">Silakan coba lagi nanti.</div>
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

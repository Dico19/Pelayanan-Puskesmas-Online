{{-- resources/views/dokter/antrian/index.blade.php --}}
@php
  use Carbon\Carbon;
  Carbon::setLocale('id');

  $data = $data ?? ($antrians ?? collect());
  $data = $data instanceof \Illuminate\Support\Collection ? $data : collect($data);

  $todayStr = $today ?? now()->toDateString();
  $hariIniLabel = Carbon::parse($todayStr)->translatedFormat('d M Y');
  $poliLabel = $poli ?? '-';

  $normStatus = function ($r) {
    $s = strtolower(trim((string)($r->status ?? '')));
    if ($s === '') {
      $s = ((int)($r->is_call ?? 0) === 1) ? 'dipanggil' : 'menunggu';
    }
    if ($s === 'lewat') $s = 'dilewati';
    return $s;
  };

  $todayRows = $data->filter(function ($r) use ($todayStr) {
    if (empty($r->tanggal_antrian)) return false;
    return Carbon::parse($r->tanggal_antrian)->toDateString() === $todayStr;
  });

  $todayTotal  = $todayRows->count();
  $todayCalled = $todayRows->filter(fn($r) => (int)($r->is_call ?? 0) === 1)->count();
  $todayLeft = $todayRows->filter(fn($r) => $normStatus($r) === 'menunggu')->count();

  $active = $todayRows->first(fn($r) => $normStatus($r) === 'dilayani');
  if (!$active) $active = $todayRows->first(fn($r) => $normStatus($r) === 'dipanggil');

  $next = $todayRows->first(fn($r) => $normStatus($r) === 'menunggu');
@endphp

@extends('layouts.dokter')

@section('title', 'Antrian Dokter')
@section('subtitle')
  Selamat datang di dashboard antrian poli <b>{{ $poliLabel }}</b> • Hari ini: <b>{{ $hariIniLabel }}</b>
@endsection
@section('header', 'Antrian Poli ' . $poliLabel)

@section('content')
<style>
  .stat-card{ padding:18px; }
  .stat-label{ color:#6c757d; font-weight:800; font-size:12px; letter-spacing:.02em; }
  .stat-value{ font-size:28px; font-weight:900; margin-top:6px; }

  .hero-card{
    border:0; border-radius:18px;
    background: linear-gradient(135deg, rgba(13,110,253,.08) 0%, rgba(25,135,84,.06) 100%);
    box-shadow: 0 12px 35px rgba(17,24,39,.08);
  }

  .badge-soft{ padding:.42rem .7rem; border-radius:999px; font-weight:900; font-size:12px; }
  .badge-today{ background: rgba(25,135,84,.12); color:#198754; }
  .badge-notoday{ background: rgba(108,117,125,.14); color:#6c757d; }
  .badge-called{ background: rgba(13,110,253,.12); color:#0d6efd; }
  .badge-wait{ background: rgba(255,193,7,.22); color:#b58100; }
  .badge-done{ background: rgba(25,135,84,.14); color:#198754; }
  .badge-skip{ background: rgba(220,53,69,.12); color:#dc3545; }

  .btn-pill{ border-radius:999px; padding:8px 14px; font-weight:900; }
  .btn-sm.btn-pill{ padding:7px 12px; }
  .btn-disabled{ opacity:.6; cursor:not-allowed; }

  .table thead th{
    font-size:12px; letter-spacing:.06em; text-transform:uppercase;
    color:#6c757d; border-bottom:1px solid #e9ecef;
  }
  .table tbody td{ vertical-align:middle; }

  .row-focus{
    background: rgba(13,110,253,.05);
    border-left: 4px solid rgba(13,110,253,.7);
  }

  .diag-box{
    background:#f8fafc;
    border:1px solid #e9ecef;
    border-radius:16px;
    padding:16px;
  }

  /* Riwayat Modal */
  .riwayat-item{
    border:1px solid #e9ecef;
    border-radius:14px;
    padding:12px 14px;
    background:#fff;
  }
  .riwayat-meta{
    font-size:12px;
    color:#6c757d;
    font-weight:800;
    letter-spacing:.02em;
    text-transform:uppercase;
  }
  .riwayat-label{
    font-weight:900;
    font-size:12px;
    color:#495057;
    margin-top:10px;
  }
  .riwayat-text{
    white-space:pre-wrap;
    font-size:14px;
    margin-top:4px;
    color:#212529;
  }
</style>

{{-- TOP: Card info + Stats --}}
<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="card hero-card p-4">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="stat-label mb-1">Sedang Dilayani</div>

          @if($active)
            @php
              $activeTgl = !empty($active->tanggal_antrian) ? Carbon::parse($active->tanggal_antrian)->translatedFormat('d M Y') : '-';
              $activeStatus = strtolower((string)($active->status ?? ''));
              if ($activeStatus === '') $activeStatus = ((int)($active->is_call ?? 0) === 1) ? 'dipanggil' : 'menunggu';
              if ($activeStatus === 'lewat') $activeStatus = 'dilewati';
            @endphp

            <div class="h4 fw-black mb-1">
              {{ $active->no_antrian ?? '-' }} • {{ $active->nama ?? '-' }}
            </div>

            <div class="text-muted" style="font-size:13px;">
              Poli: <b>{{ $active->poli ?? $poliLabel }}</b> &nbsp;•&nbsp;
              Tgl: <b>{{ $activeTgl }}</b>
            </div>

            <div class="mt-3">
              @if($activeStatus === 'selesai')
                <span class="badge-soft badge-done">Selesai</span>
              @elseif($activeStatus === 'dilayani')
                <span class="badge-soft badge-called">Sedang dilayani</span>
              @elseif($activeStatus === 'dilewati')
                <span class="badge-soft badge-skip">Dilewati</span>
              @else
                <span class="badge-soft badge-called">Dipanggil</span>
              @endif
            </div>

            <div class="mt-3 text-muted" style="font-size:13px;">
              Berikutnya:
              @if($next)
                <b>{{ $next->no_antrian ?? '-' }}</b> • {{ $next->nama ?? '-' }}
              @else
                <b>-</b>
              @endif
            </div>
          @else
            <div class="h5 fw-bold mb-1">Belum ada pasien aktif</div>
            <div class="text-muted" style="font-size:13px;">
              Silakan panggil pasien dari daftar antrian di bawah.
            </div>
            <div class="mt-3">
              <span class="badge-soft badge-notoday">Menunggu panggilan</span>
            </div>
          @endif
        </div>

        <div>
          <span class="badge-soft badge-today">
            <i class="bi bi-calendar2-check me-1"></i> {{ $hariIniLabel }}
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="row g-3">
      <div class="col-md-4">
        <div class="card card-soft stat-card">
          <div class="stat-label">Antrian Hari Ini</div>
          <div class="stat-value">{{ $todayTotal }}</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-soft stat-card">
          <div class="stat-label">Sudah Dipanggil</div>
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

{{-- TABLE --}}
<div class="card card-soft">
  <div class="d-flex align-items-center justify-content-between px-4 pt-4">
    <div>
      <h5 class="mb-0 fw-bold">Daftar Antrian Poli {{ $poliLabel }}</h5>
      <div class="text-muted mt-1" style="font-size:13px">
        <b>Panggil</b> hanya aktif untuk <b>hari ini</b>. <b>Diagnosa</b> aktif setelah pasien dipanggil.
      </div>
    </div>
  </div>

  <div class="table-responsive px-3 pb-3 pt-2">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>No Antrian</th>
          <th>Nama</th>
          <th>JK</th>
          <th>Tgl Antrian</th>
          <th>Status</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>

      <tbody>
      @forelse ($data as $row)
        @php
          $rowDate = !empty($row->tanggal_antrian) ? Carbon::parse($row->tanggal_antrian)->toDateString() : null;
          $isToday = ($rowDate === $todayStr);

          $status = $normStatus($row);
          $isCalled  = in_array($status, ['dipanggil','dilayani','selesai','dilewati'], true) || ((int)($row->is_call ?? 0) === 1);
          $isSkipped = ($status === 'dilewati');
          $isDone    = ($status === 'selesai');
          $isServing = ($status === 'dilayani');

          $tglLabel = !empty($row->tanggal_antrian) ? Carbon::parse($row->tanggal_antrian)->translatedFormat('d F Y') : '-';
          $collapseId = 'diag-'.$row->id;

          $highlight = $active && (int)$active->id === (int)$row->id ? 'row-focus' : '';
        @endphp

        <tr class="{{ $highlight }}">
          <td class="fw-bold">{{ $row->no_antrian ?? '-' }}</td>
          <td>{{ $row->nama ?? '-' }}</td>
          <td>{{ $row->jenis_kelamin ?? '-' }}</td>

          <td>
            <div class="fw-bold">{{ $tglLabel }}</div>
            @if($isToday)
              <span class="badge-soft badge-today"><i class="bi bi-calendar-event me-1"></i> Hari ini</span>
            @else
              <span class="badge-soft badge-notoday">Bukan hari ini</span>
            @endif
          </td>

          <td>
            @if($isDone)
              <span class="badge-soft badge-done">Selesai</span>
            @elseif($isSkipped)
              <span class="badge-soft badge-skip">Dilewati</span>
            @elseif($isServing)
              <span class="badge-soft badge-called">Sedang dilayani</span>
            @elseif($status === 'dipanggil')
              <span class="badge-soft badge-called">Sudah dipanggil</span>
            @else
              <span class="badge-soft badge-wait">Menunggu</span>
            @endif
          </td>

          <td class="text-end">
            <div class="d-inline-flex flex-wrap gap-1 justify-content-end">

              {{-- PANGGIL --}}
              @if($isToday && $status === 'menunggu')
                <form action="{{ route('dokter.antrian.panggil', $row->id) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Panggil antrian {{ $row->no_antrian }} atas nama {{ $row->nama }} ?')">
                  @csrf
                  <button type="submit" class="btn btn-success btn-pill btn-sm">
                    <i class="bi bi-telephone-fill me-1"></i> Panggil
                  </button>
                </form>
              @else
                <button class="btn btn-secondary btn-pill btn-sm btn-disabled" disabled>
                  <i class="bi bi-telephone-fill me-1"></i> Panggil
                </button>
              @endif

              {{-- PANGGIL ULANG --}}
              @if($isToday && in_array($status, ['dipanggil','dilayani','dilewati'], true) && !$isDone)
                <form action="{{ route('dokter.antrian.panggilUlang', $row->id) }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-outline-primary btn-pill btn-sm">
                    <i class="bi bi-megaphone-fill me-1"></i> Panggil ulang
                  </button>
                </form>
              @else
                <button class="btn btn-outline-primary btn-pill btn-sm btn-disabled" disabled
                        title="Hanya untuk pasien yang sudah dipanggil (hari ini)">
                  <i class="bi bi-megaphone-fill me-1"></i> Panggil ulang
                </button>
              @endif

              {{-- MULAI --}}
              @if($isToday && $status === 'dipanggil')
                <form action="{{ route('dokter.antrian.mulai', $row->id) }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-primary btn-pill btn-sm">
                    <i class="bi bi-play-circle-fill me-1"></i> Mulai
                  </button>
                </form>
              @else
                <button class="btn btn-primary btn-pill btn-sm btn-disabled" disabled
                        title="Mulai hanya bisa setelah pasien dipanggil">
                  <i class="bi bi-play-circle-fill me-1"></i> Mulai
                </button>
              @endif

              {{-- SELESAI --}}
              @if($isToday && in_array($status, ['dipanggil','dilayani'], true))
                <form action="{{ route('dokter.antrian.selesai', $row->id) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Tandai SELESAI untuk {{ $row->no_antrian }} - {{ $row->nama }} ?')">
                  @csrf
                  <button type="submit" class="btn btn-success btn-pill btn-sm">
                    <i class="bi bi-check2-circle me-1"></i> Selesai
                  </button>
                </form>
              @else
                <button class="btn btn-success btn-pill btn-sm btn-disabled" disabled
                        title="Selesai hanya bisa saat dipanggil / dilayani">
                  <i class="bi bi-check2-circle me-1"></i> Selesai
                </button>
              @endif

              {{-- LEWATKAN --}}
              @if($isToday && in_array($status, ['menunggu','dipanggil','dilayani'], true) && !$isDone)
                <form action="{{ route('dokter.antrian.lewati', $row->id) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Lewatkan antrian {{ $row->no_antrian }} - {{ $row->nama }} ?')">
                  @csrf
                  <button type="submit" class="btn btn-outline-danger btn-pill btn-sm">
                    <i class="bi bi-skip-forward-fill me-1"></i> Lewatkan
                  </button>
                </form>
              @else
                <button class="btn btn-outline-danger btn-pill btn-sm btn-disabled" disabled
                        title="Lewatkan hanya untuk antrian aktif hari ini">
                  <i class="bi bi-skip-forward-fill me-1"></i> Lewatkan
                </button>
              @endif

              {{-- ✅ RIWAYAT (MODAL - klik sekali) --}}
              <button type="button"
                      class="btn btn-outline-secondary btn-pill btn-sm js-riwayat"
                      data-no-ktp="{{ $row->no_ktp }}"
                      data-nama="{{ $row->nama }}"
                      data-no-antrian="{{ $row->no_antrian }}">
                <i class="bi bi-clock-history me-1"></i> Riwayat
              </button>

              {{-- DIAGNOSA --}}
              @if($isToday && $isCalled && !$isSkipped)
                <button class="btn btn-dark btn-pill btn-sm"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#{{ $collapseId }}"
                        aria-expanded="false"
                        aria-controls="{{ $collapseId }}">
                  <i class="bi bi-clipboard2-pulse me-1"></i> Diagnosa
                </button>
              @else
                <button class="btn btn-dark btn-pill btn-sm btn-disabled" disabled
                        title="Diagnosa aktif setelah pasien dipanggil (hari ini)">
                  <i class="bi bi-clipboard2-pulse me-1"></i> Diagnosa
                </button>
              @endif

            </div>
          </td>
        </tr>

        {{-- FORM DIAGNOSA --}}
        <tr class="collapse" id="{{ $collapseId }}">
          <td colspan="6">
            <div class="diag-box">
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
                    <textarea name="diagnosa" class="form-control" rows="3" placeholder="Diagnosa..." required></textarea>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label fw-bold">Catatan / Anjuran</label>
                    <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan / anjuran..."></textarea>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label fw-bold">Resep / Obat</label>
                    <textarea name="resep" class="form-control" rows="3" placeholder="Resep / obat..."></textarea>
                  </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                  <button class="btn btn-success btn-pill">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Diagnosa
                  </button>
                </div>
              </form>
            </div>
          </td>
        </tr>

      @empty
        <tr>
          <td colspan="6" class="text-center py-5">
            <div class="fw-bold mb-1">Belum ada data antrian</div>
            <div class="text-muted" style="font-size:13px">Silakan coba lagi nanti.</div>
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ✅ MODAL RIWAYAT (klik sekali) --}}
<div class="modal fade" id="riwayatModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:18px;">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="riwayatModalTitle">Riwayat</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body" id="riwayatModalBody">
        <div class="text-muted">Memuat...</div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-pill" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
  // ✅ template URL modal JSON
  const RIWAYAT_URL_TEMPLATE = "{{ route('dokter.riwayat.modal', ['noKtp' => '___NO_KTP___']) }}";

  function escapeHtml(str) {
    return String(str ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function renderRiwayat(items) {
    if (!items || items.length === 0) {
      return `<div class="text-muted">Belum ada riwayat kunjungan (atau belum ada diagnosa tersimpan).</div>`;
    }

    return items.map((it, idx) => {
      const tanggal = escapeHtml(it.tanggal);
      const poli = escapeHtml(it.poli ?? '-');
      const diagnosa = escapeHtml(it.diagnosa ?? '-');
      const catatan = escapeHtml(it.catatan ?? '');
      const resep = escapeHtml(it.resep ?? '');

      return `
        <div class="riwayat-item mb-2">
          <div class="d-flex justify-content-between align-items-center">
            <div class="riwayat-meta">${tanggal}</div>
            <span class="badge-soft badge-called">POLI ${poli.toUpperCase()}</span>
          </div>

          <div class="riwayat-label">Diagnosa</div>
          <div class="riwayat-text">${diagnosa}</div>

          ${catatan ? `
            <div class="riwayat-label">Catatan / Anjuran</div>
            <div class="riwayat-text">${catatan}</div>
          ` : ''}

          ${resep ? `
            <div class="riwayat-label">Obat / Resep</div>
            <div class="riwayat-text">${resep}</div>
          ` : ''}
        </div>
      `;
    }).join('');
  }

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.js-riwayat');
    if (!btn) return;

    const noKtp = btn.getAttribute('data-no-ktp');
    const nama = btn.getAttribute('data-nama') || '';
    const noAntrian = btn.getAttribute('data-no-antrian') || '';

    const titleEl = document.getElementById('riwayatModalTitle');
    const bodyEl = document.getElementById('riwayatModalBody');

    titleEl.textContent = `Riwayat • ${nama} • ${noAntrian}`;
    bodyEl.innerHTML = `
      <div class="d-flex align-items-center gap-2 text-muted">
        <div class="spinner-border spinner-border-sm" role="status"></div>
        <div>Memuat riwayat...</div>
      </div>
    `;

    // Pastikan bootstrap modal ada
    if (!window.bootstrap) {
      bodyEl.innerHTML = `<div class="text-danger">Bootstrap JS belum aktif. Pastikan bootstrap.bundle.min.js ter-load.</div>`;
      return;
    }

    const modalEl = document.getElementById('riwayatModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    try {
      const url = RIWAYAT_URL_TEMPLATE.replace('___NO_KTP___', encodeURIComponent(noKtp)) + `?t=${Date.now()}`;
      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });

      if (!res.ok) throw new Error(`HTTP ${res.status}`);

      const data = await res.json();
      if (!data || data.ok !== true) throw new Error('Response tidak valid');

      bodyEl.innerHTML = renderRiwayat(data.items);

    } catch (err) {
      console.error(err);
      bodyEl.innerHTML = `
        <div class="text-danger fw-bold mb-1">Gagal memuat riwayat.</div>
        <div class="text-muted" style="font-size:13px">Coba refresh halaman, atau cek console error.</div>
      `;
    }
  });
</script>
@endsection

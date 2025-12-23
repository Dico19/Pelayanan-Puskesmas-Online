@extends('dashboard.layouts.main')

@section('content')
@php
  use Carbon\Carbon;

  $selectedDate = $selectedDate ?? now()->toDateString();
  $tanggalLabel = $tanggalLabel ?? Carbon::parse($selectedDate)->translatedFormat('d M Y');

  $selectedPoli = $selectedPoli ?? 'all';
  $poliOptions  = $poliOptions ?? [];

  $todayStr = now()->toDateString();
  $yesterdayStr = now()->subDay()->toDateString();
@endphp

<style>
  .page-wrap{ padding-bottom: 30px; }
  .hero-analytics{
    border:0;
    border-radius:18px;
    background: linear-gradient(135deg, rgba(13,110,253,.08) 0%, rgba(25,135,84,.06) 100%);
    box-shadow: 0 12px 35px rgba(17,24,39,.08);
  }
  .hero-title{
    font-weight:900;
    font-size:28px;
    letter-spacing:-.02em;
    margin:0;
  }
  .hero-sub{
    color:#6c757d;
    margin-top:6px;
    font-size:13px;
  }

  .filter-bar{
    background: rgba(255,255,255,.78);
    border: 1px solid rgba(233,236,239,.9);
    border-radius: 16px;
    padding: 12px;
    box-shadow: 0 10px 25px rgba(17,24,39,.06);
  }
  .seg{
    display:inline-flex;
    gap:8px;
    background:#f8f9fa;
    border:1px solid #e9ecef;
    border-radius: 999px;
    padding: 6px;
  }
  .seg .btn{
    border-radius: 999px;
    font-weight: 800;
    padding: 8px 12px;
    border:0;
  }
  .seg .btn.active{
    background: #0d6efd;
    color: #fff;
  }

  .pill{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(13,110,253,.08);
    border: 1px solid rgba(13,110,253,.20);
    font-weight: 800;
    font-size: 12px;
    color:#0b5ed7;
  }

  .card-soft{
    border:0;
    border-radius:16px;
    box-shadow: 0 10px 30px rgba(17,24,39,.08);
  }
  .metric-card{
    border:0;
    border-radius:16px;
    box-shadow: 0 10px 30px rgba(17,24,39,.08);
    background:#fff;
    padding: 14px 14px;
  }
  .metric-label{ font-size:12px; color:#6c757d; font-weight:800; }
  .metric-value{ font-size:26px; font-weight:900; margin-top:6px; }

  .metric-icon{
    width:46px; height:46px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    color:#fff;
  }

  .chart-card h6{
    font-weight:900;
    margin-bottom: 12px;
  }
  .empty-box{
    padding: 24px;
    border: 1px dashed #dee2e6;
    border-radius: 14px;
    color:#6c757d;
    text-align:center;
    font-size: 13px;
    background: #fbfcfe;
  }
</style>

<div class="container-fluid px-4 page-wrap">

  {{-- HERO + FILTER --}}
  <div class="card hero-analytics p-4 mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
      <div>
        <h2 class="hero-title">Analitik Layanan Antrian</h2>
        <div class="hero-sub">
          Ringkasan data antrian & pola kunjungan pasien — <b>{{ $tanggalLabel }}</b>
          @if($selectedPoli && $selectedPoli !== 'all')
            • Poli: <b>{{ ucfirst($selectedPoli) }}</b>
          @else
            • Semua poli
          @endif
        </div>
      </div>

      <div class="pill">
        <i class="bi bi-funnel-fill"></i>
        Filter aktif
      </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="filter-bar mt-3">
      <form method="GET" action="{{ route('admin.analytics') }}" class="d-flex flex-wrap gap-2 align-items-center">
        {{-- segmented buttons --}}
        <div class="seg" role="group" aria-label="Filter tanggal cepat">
          <button type="button"
                  class="btn btn-light {{ $selectedDate === $todayStr ? 'active' : '' }}"
                  onclick="setQuickDate('{{ $todayStr }}')">
            <i class="bi bi-calendar2-check me-1"></i> Hari ini
          </button>

          <button type="button"
                  class="btn btn-light {{ $selectedDate === $yesterdayStr ? 'active' : '' }}"
                  onclick="setQuickDate('{{ $yesterdayStr }}')">
            <i class="bi bi-calendar2-minus me-1"></i> Kemarin
          </button>

          <button type="button"
                  class="btn btn-light {{ !in_array($selectedDate, [$todayStr, $yesterdayStr], true) ? 'active' : '' }}"
                  onclick="focusDate()">
            <i class="bi bi-calendar-week me-1"></i> Pilih tanggal
          </button>
        </div>

        {{-- hidden date that will be submitted --}}
        <input type="date" class="form-control"
               name="date"
               id="dateInput"
               value="{{ $selectedDate }}"
               style="max-width: 170px; border-radius: 12px; font-weight:800;">

        {{-- poli select --}}
        <select name="poli" class="form-select" style="max-width: 220px; border-radius: 12px; font-weight:800;">
          <option value="all" {{ $selectedPoli === 'all' ? 'selected' : '' }}>Semua Poli</option>
          @foreach($poliOptions as $p)
            @php $pv = strtolower(trim((string)$p)); @endphp
            <option value="{{ $pv }}" {{ strtolower($selectedPoli) === $pv ? 'selected' : '' }}>
              {{ $p }}
            </option>
          @endforeach
        </select>

        <button type="submit" class="btn btn-primary"
                style="border-radius: 999px; font-weight:900; padding:10px 16px;">
          <i class="bi bi-check2-circle me-1"></i> Terapkan
        </button>

        <a href="{{ route('admin.analytics') }}" class="btn btn-outline-secondary"
           style="border-radius: 999px; font-weight:900; padding:10px 16px;">
          <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
        </a>
      </form>
    </div>
  </div>

  {{-- KPI --}}
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="metric-card">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="metric-label">Total Antrian ({{ $tanggalLabel }})</div>
            <div class="metric-value">{{ $totalToday }}</div>
          </div>
          <div class="metric-icon" style="background:#0d6efd;">
            <i class="bi bi-people-fill fs-4"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="metric-card">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="metric-label">Pasien Unik ({{ $tanggalLabel }})</div>
            <div class="metric-value">{{ $uniquePatientsToday }}</div>
          </div>
          <div class="metric-icon" style="background:#198754;">
            <i class="bi bi-person-check-fill fs-4"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="metric-card">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="metric-label">Rata-rata Waktu Tunggu</div>
            <div class="metric-value">{{ $avgWait }} <span style="font-size:14px; font-weight:900; color:#6c757d;">menit</span></div>
          </div>
          <div class="metric-icon" style="background:#0dcaf0;">
            <i class="bi bi-clock-fill fs-4"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="metric-card">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="metric-label">Jam Tersibuk</div>
            <div class="metric-value">{{ $busiestHour ?? '-' }}</div>
          </div>
          <div class="metric-icon" style="background:#ffc107; color:#1f2a37;">
            <i class="bi bi-lightning-charge-fill fs-4"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- CHARTS --}}
  <div class="row g-3">
    <div class="col-md-6">
      <div class="card card-soft p-3 chart-card">
        <h6>Jumlah Pasien per Poli ({{ $tanggalLabel }})</h6>
        <div class="{{ count($perPoli ?? []) ? '' : 'd-none' }}">
          <canvas id="chartPoli"></canvas>
        </div>
        @if(empty($perPoli) || (is_countable($perPoli) && count($perPoli) === 0))
          <div class="empty-box">Tidak ada data untuk ditampilkan pada grafik ini.</div>
        @endif
      </div>
    </div>

    <div class="col-md-6">
      <div class="card card-soft p-3 chart-card">
        <h6>Jam Kunjungan Tersibuk ({{ $tanggalLabel }})</h6>
        <div class="{{ count($perJam ?? []) ? '' : 'd-none' }}">
          <canvas id="chartJam"></canvas>
        </div>
        @if(empty($perJam) || (is_countable($perJam) && count($perJam) === 0))
          <div class="empty-box">Belum ada data kunjungan pada jam tertentu di tanggal ini.</div>
        @endif
      </div>
    </div>

    <div class="col-md-6">
      <div class="card card-soft p-3 chart-card">
        <h6>Tren Harian (7 Hari Terakhir)</h6>
        <div class="{{ count($dailyTrend ?? []) ? '' : 'd-none' }}">
          <canvas id="chartHarian"></canvas>
        </div>
        @if(empty($dailyTrend) || (is_countable($dailyTrend) && count($dailyTrend) === 0))
          <div class="empty-box">Belum ada tren harian pada rentang ini.</div>
        @endif
      </div>
    </div>

    <div class="col-md-6">
      <div class="card card-soft p-3 chart-card">
        <h6>Tren Bulanan (6 Bulan Terakhir)</h6>
        <div class="{{ count($monthlyTrend ?? []) ? '' : 'd-none' }}">
          <canvas id="chartBulanan"></canvas>
        </div>
        @if(empty($monthlyTrend) || (is_countable($monthlyTrend) && count($monthlyTrend) === 0))
          <div class="empty-box">Belum ada tren bulanan pada rentang ini.</div>
        @endif
      </div>
    </div>
  </div>

</div>
@endsection

@section('script')
<script>
  function setQuickDate(iso){
    const el = document.getElementById('dateInput');
    if(el){ el.value = iso; }
    // auto submit biar cepat seperti tombol "Hari ini / Kemarin"
    el.closest('form').submit();
  }
  function focusDate(){
    const el = document.getElementById('dateInput');
    if(el){ el.focus(); el.showPicker && el.showPicker(); }
  }

  // Data dari controller
  const perPoli = @json($perPoli ?? []);
  const perJam  = @json($perJam ?? []);
  const daily   = @json($dailyTrend ?? []);
  const monthly = @json($monthlyTrend ?? []);

  // Guard: Chart.js harus ada
  if (typeof Chart === 'undefined') {
    console.warn('Chart.js belum ter-load. Pastikan kamu load chartjs di layout.');
  } else {

    // 1) Poli
    if (perPoli.length) {
      new Chart(document.getElementById('chartPoli'), {
        type: 'bar',
        data: {
          labels: perPoli.map(x => x.poli ?? '-'),
          datasets: [{
            label: 'Pasien',
            data: perPoli.map(x => x.total ?? 0),
            backgroundColor: 'rgba(54, 162, 235, .45)',
            borderColor: 'rgba(54, 162, 235, .9)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
          }
        }
      });
    }

    // 2) Jam
    if (perJam.length) {
      new Chart(document.getElementById('chartJam'), {
        type: 'bar',
        data: {
          labels: perJam.map(x => String(x.jam).padStart(2,'0') + ':00'),
          datasets: [{
            label: 'Pasien',
            data: perJam.map(x => x.total ?? 0),
            backgroundColor: 'rgba(255, 193, 7, .35)',
            borderColor: 'rgba(255, 193, 7, .9)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
          }
        }
      });
    }

    // 3) Harian
    if (daily.length) {
      new Chart(document.getElementById('chartHarian'), {
        type: 'line',
        data: {
          labels: daily.map(x => x.tanggal),
          datasets: [{
            label: 'Pasien',
            data: daily.map(x => x.total ?? 0),
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,.10)',
            tension: .35,
            fill: true,
            pointRadius: 3
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
          }
        }
      });
    }

    // 4) Bulanan
    if (monthly.length) {
      new Chart(document.getElementById('chartBulanan'), {
        type: 'line',
        data: {
          labels: monthly.map(x => x.bulan),
          datasets: [{
            label: 'Pasien',
            data: monthly.map(x => x.total ?? 0),
            borderColor: '#6610f2',
            backgroundColor: 'rgba(102,16,242,.10)',
            tension: .35,
            fill: true,
            pointRadius: 3
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
          }
        }
      });
    }
  }
</script>
@endsection

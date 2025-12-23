{{-- resources/views/dokter/statistik/index.blade.php --}}
@php
  use Carbon\Carbon;
  Carbon::setLocale('id');
@endphp

@extends('layouts.dokter')

@section('title', 'Statistik Poli')

@section('content')
<style>
  .page-wrap{ max-width:1200px; margin:0 auto; }

  .hero{
    border:0; border-radius:18px;
    background: linear-gradient(135deg, rgba(13,110,253,.10) 0%, rgba(25,135,84,.08) 100%);
    box-shadow: 0 12px 35px rgba(17,24,39,.08);
    padding: 18px;
  }
  .hero h4{ margin:0; font-weight:900; letter-spacing:.2px; }
  .hero .sub{ color:#6c757d; font-size:13px; margin-top:6px; }

  .live-dot{
    width:10px;height:10px;border-radius:999px;
    background: rgba(25,135,84,.85);
    box-shadow: 0 0 0 0 rgba(25,135,84,.5);
    animation:pulse 1.6s infinite;
  }
  @keyframes pulse{
    0%{box-shadow:0 0 0 0 rgba(25,135,84,.45)}
    70%{box-shadow:0 0 0 10px rgba(25,135,84,0)}
    100%{box-shadow:0 0 0 0 rgba(25,135,84,0)}
  }

  .chip{
    display:inline-flex; align-items:center; gap:8px;
    padding:7px 10px; border-radius:999px;
    background:#f1f5ff; color:#1d4ed8;
    font-weight:900; font-size:12px;
    border:1px solid rgba(13,110,253,.15);
  }
  .chip .mini{ width:8px; height:8px; border-radius:999px; background: rgba(29,78,216,.7); }

  /* Filter Bar */
  .filterbar{
    margin-top:12px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
  }
  .seg{
    display:flex; gap:8px; flex-wrap:wrap;
    background: rgba(255,255,255,.65);
    border: 1px solid rgba(17,24,39,.08);
    padding: 6px;
    border-radius: 999px;
  }
  .seg .segbtn{
    border:0;
    border-radius:999px;
    padding:8px 12px;
    font-weight:900;
    font-size:12px;
    background: transparent;
    color:#0b2a63;
  }
  .seg .segbtn.active{
    background: rgba(13,110,253,.12);
    color:#0d6efd;
  }
  .datebox{
    display:flex; gap:8px; align-items:center; flex-wrap:wrap;
  }
  .datebox input[type="date"]{
    border-radius: 12px;
    border: 1px solid rgba(17,24,39,.14);
    padding: 8px 10px;
    font-weight: 800;
    font-size: 12px;
    background:#fff;
  }
  .btn-pill{ border-radius:999px; padding:8px 12px; font-weight:900; font-size:12px; }

  .kpi{
    border:0; border-radius:16px;
    box-shadow:0 10px 30px rgba(17,24,39,.08);
    background:#fff;
    padding:14px 16px;
    overflow:hidden;
    position:relative;
  }
  .kpi .label{
    color:#6c757d;
    font-weight:900;
    font-size:12px;
    letter-spacing:.05em;
    text-transform:uppercase;
    display:flex; align-items:center; gap:8px;
  }
  .kpi .value{
    margin-top:6px;
    font-size:28px;
    font-weight:900;
    line-height:1.1;
  }
  .kpi .hint{
    margin-top:6px;
    font-size:12px;
    color:#6c757d;
  }
  .kpi .accent{
    position:absolute;
    right:-18px; top:-18px;
    width:72px; height:72px;
    border-radius:22px;
    transform:rotate(18deg);
    background: rgba(13,110,253,.10);
  }

  .panel{
    border:0; border-radius:18px;
    box-shadow:0 12px 35px rgba(17,24,39,.08);
    background:#fff;
  }
  .panel .panel-h{
    padding:16px 18px 10px;
    display:flex; justify-content:space-between; align-items:center;
    gap:12px; flex-wrap:wrap;
  }
  .panel .panel-h .ttl{ font-weight:900; margin:0; }
  .panel .panel-h .small{ color:#6c757d; font-size:12px; margin-top:3px; }
  .panel .panel-b{ padding:12px 18px 18px; }

  .donut-wrap{ display:flex; gap:16px; align-items:center; flex-wrap:wrap; }
  .donut{ width:150px; height:150px; position:relative; }
  .donut svg{ width:150px; height:150px; transform: rotate(-90deg); }
  .donut .center{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; flex-direction:column; }
  .donut .center .p{ font-weight:900; font-size:20px; line-height:1.1; }
  .donut .center .t{ font-size:12px; color:#6c757d; font-weight:800; }

  .dist{ flex:1; min-width:280px; }
  .rowbar{ margin-bottom:10px; }
  .rowbar .top{
    display:flex; justify-content:space-between; align-items:center;
    gap:12px; font-size:12px; font-weight:900;
  }
  .rowbar .top span:first-child{ color:#495057; }
  .rowbar .top span:last-child{ color:#6c757d; font-weight:800; }
  .barline{
    height:10px; border-radius:999px;
    background:#edf2f7; overflow:hidden; margin-top:6px;
  }
  .barline>div{
    height:100%; width:0%;
    border-radius:999px;
    transition: width .35s ease;
  }

  .skel{
    color:transparent !important;
    background: linear-gradient(90deg,#eee 25%,#f6f6f6 37%,#eee 63%);
    background-size:400% 100%;
    animation: sk 1.3s ease infinite;
    border-radius:8px;
  }
  @keyframes sk{ 0%{background-position:100% 50%} 100%{background-position:0 50%} }

  @media(max-width:992px){
    .donut-wrap{ justify-content:center; }
    .donut{ margin:0 auto; }
  }
</style>

<div class="page-wrap">
  <div class="hero mb-3">
    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
      <div>
        <h4>Statistik Poli</h4>
        <div class="sub" id="statsSub">Memuat data statistik...</div>

        {{-- FILTER BAR --}}
        <div class="filterbar">
          <div class="seg" role="tablist" aria-label="Filter tanggal">
            <button type="button" class="segbtn" id="btnToday">
              <i class="bi bi-calendar2-check me-1"></i> Hari ini
            </button>
            <button type="button" class="segbtn" id="btnYesterday">
              <i class="bi bi-calendar2-minus me-1"></i> Kemarin
            </button>
            <button type="button" class="segbtn" id="btnPick">
              <i class="bi bi-calendar-event me-1"></i> Pilih tanggal
            </button>
          </div>

          <div class="datebox">
            <input type="date" id="datePick" />
            <button type="button" class="btn btn-primary btn-pill" id="btnApply">
              <i class="bi bi-funnel-fill me-1"></i> Terapkan
            </button>
          </div>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <div class="live-dot" title="Real-time"></div>
        <span class="chip"><span class="mini"></span> Real-time</span>
      </div>
    </div>
  </div>

  {{-- KPI --}}
  <div class="row g-3 mb-3">
    <div class="col-md-4 col-lg-2">
      <div class="kpi">
        <div class="accent"></div>
        <div class="label"><i class="bi bi-list-check"></i> Total</div>
        <div class="value skel" id="k_total">0</div>
        <div class="hint">Total antrian</div>
      </div>
    </div>

    <div class="col-md-4 col-lg-2">
      <div class="kpi">
        <div class="accent" style="background:rgba(255,193,7,.20)"></div>
        <div class="label"><i class="bi bi-hourglass-split"></i> Menunggu</div>
        <div class="value skel" id="k_menunggu">0</div>
        <div class="hint">Belum dipanggil</div>
      </div>
    </div>

    <div class="col-md-4 col-lg-2">
      <div class="kpi">
        <div class="accent" style="background:rgba(13,110,253,.18)"></div>
        <div class="label"><i class="bi bi-telephone-forward"></i> Dipanggil</div>
        <div class="value skel" id="k_dipanggil">0</div>
        <div class="hint">Sudah dipanggil</div>
      </div>
    </div>

    <div class="col-md-4 col-lg-2">
      <div class="kpi">
        <div class="accent" style="background:rgba(25,135,84,.16)"></div>
        <div class="label"><i class="bi bi-person-check"></i> Dilayani</div>
        <div class="value skel" id="k_dilayani">0</div>
        <div class="hint">Sedang dilayani</div>
      </div>
    </div>

    <div class="col-md-4 col-lg-2">
      <div class="kpi">
        <div class="accent" style="background:rgba(25,135,84,.22)"></div>
        <div class="label"><i class="bi bi-check2-circle"></i> Selesai</div>
        <div class="value skel" id="k_selesai">0</div>
        <div class="hint">Sudah selesai</div>
      </div>
    </div>

    <div class="col-md-4 col-lg-2">
      <div class="kpi">
        <div class="accent" style="background:rgba(111,66,193,.18)"></div>
        <div class="label"><i class="bi bi-person-x"></i> Tidak Hadir</div>
        <div class="value skel" id="k_tidak_hadir">0</div>
        <div class="hint">Ditandai tidak hadir</div>
      </div>
    </div>
  </div>

  {{-- CHART --}}
  <div class="panel mb-3">
    <div class="panel-h">
      <div>
        <h6 class="ttl mb-0">Ringkasan Hari Ini</h6>
        <div class="small">Distribusi status + progres selesai</div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <span class="chip" style="background:#f0fdf4; color:#166534; border-color:rgba(22,101,52,.15)">
          <span class="mini" style="background:#16a34a;"></span> Progres Selesai
        </span>
        <span class="chip" style="background:#fff7ed; color:#9a3412; border-color:rgba(154,52,18,.12)">
          <span class="mini" style="background:#f97316;"></span> Distribusi Status
        </span>
      </div>
    </div>

    <div class="panel-b">
      <div class="donut-wrap">
        <div class="donut">
          <svg viewBox="0 0 120 120">
            <circle cx="60" cy="60" r="46" fill="none" stroke="#edf2f7" stroke-width="12"></circle>
            <circle id="donutProg"
                    cx="60" cy="60" r="46" fill="none"
                    stroke="rgba(25,135,84,.85)"
                    stroke-linecap="round"
                    stroke-width="12"
                    stroke-dasharray="0 999"></circle>
          </svg>
          <div class="center">
            <div class="p" id="donutPct">0%</div>
            <div class="t">Selesai</div>
          </div>
        </div>

        <div class="dist">
          <div class="rowbar">
            <div class="top"><span>Menunggu</span><span id="tx_menunggu">0</span></div>
            <div class="barline"><div id="bar_menunggu" style="background: rgba(255,193,7,.9)"></div></div>
          </div>

          <div class="rowbar">
            <div class="top"><span>Dipanggil</span><span id="tx_dipanggil">0</span></div>
            <div class="barline"><div id="bar_dipanggil" style="background: rgba(13,110,253,.9)"></div></div>
          </div>

          <div class="rowbar">
            <div class="top"><span>Dilayani</span><span id="tx_dilayani">0</span></div>
            <div class="barline"><div id="bar_dilayani" style="background: rgba(25,135,84,.75)"></div></div>
          </div>

          <div class="rowbar">
            <div class="top"><span>Selesai</span><span id="tx_selesai">0</span></div>
            <div class="barline"><div id="bar_selesai" style="background: rgba(25,135,84,.95)"></div></div>
          </div>

          <div class="rowbar mb-0">
            <div class="top"><span>Tidak Hadir</span><span id="tx_tidak_hadir">0</span></div>
            <div class="barline"><div id="bar_tidak_hadir" style="background: rgba(111,66,193,.9)"></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // ✅ FIX: gunakan endpoint statistik (bukan statsHariIni antrian)
  const STATS_URL   = "{{ route('dokter.statistik.data') }}";
  const initialDate = "{{ request('date', now()->toDateString()) }}";

  function num(v){ return parseInt(v || 0, 10) || 0; }
  function setText(id, v){
    const el = document.getElementById(id);
    if(!el) return;
    el.textContent = v;
    el.classList.remove("skel");
  }
  function setWidth(id, val, total){
    const el = document.getElementById(id);
    if(!el) return;
    const t = Math.max(1, num(total));
    const p = Math.max(0, Math.min(1, num(val)/t));
    el.style.width = Math.round(p * 100) + "%";
  }
  function setDonutProgress(done, total){
    const c = document.getElementById("donutProg");
    const pctEl = document.getElementById("donutPct");
    if(!c || !pctEl) return;

    const t = Math.max(0, num(total));
    const d = Math.max(0, num(done));
    const pct = t === 0 ? 0 : Math.round((d / t) * 100);

    const r = 46;
    const circum = 2 * Math.PI * r;
    const dash = (pct / 100) * circum;

    c.setAttribute("stroke-dasharray", `${dash} ${circum}`);
    pctEl.textContent = pct + "%";
  }

  // ===== Filter state =====
  let selectedDate = initialDate;

  function yyyy_mm_dd(d){
    const pad = (n)=> String(n).padStart(2,'0');
    return d.getFullYear() + "-" + pad(d.getMonth()+1) + "-" + pad(d.getDate());
  }

  function markActive(mode){
    const bT = document.getElementById("btnToday");
    const bY = document.getElementById("btnYesterday");
    const bP = document.getElementById("btnPick");
    [bT,bY,bP].forEach(b=> b && b.classList.remove("active"));
    if(mode === "today") bT?.classList.add("active");
    if(mode === "yesterday") bY?.classList.add("active");
    if(mode === "pick") bP?.classList.add("active");
  }

  function syncUrl(dateStr){
    const url = new URL(window.location.href);
    url.searchParams.set("date", dateStr);
    window.history.replaceState({}, "", url.toString());
  }

  function normalizeCounts(payload){
    // Support 2 format:
    // A) { ok:true, counts:{...}, poli_label, tanggal_label }
    // B) { ok:true, total:..., menunggu:..., ... , poli:'', date:'' }
    const c = payload?.counts ? payload.counts : payload;

    return {
      total:       num(c?.total),
      menunggu:    num(c?.menunggu),
      dipanggil:   num(c?.dipanggil),
      dilayani:    num(c?.dilayani),
      selesai:     num(c?.selesai),
      tidak_hadir: num(c?.tidak_hadir),
    };
  }

  async function loadStats(dateStr){
    const sub = document.getElementById("statsSub");

    try{
      const res = await fetch(STATS_URL + "?date=" + encodeURIComponent(dateStr) + "&t=" + Date.now(), {
        headers: { "Accept":"application/json" }
      });

      if(!res.ok) throw new Error("HTTP " + res.status);

      const data = await res.json();

      // kalau backend kamu tidak pakai ok:true, kita tetap coba proses
      const counts = normalizeCounts(data);
      const total  = num(counts.total);

      setText("k_total", total);
      setText("k_menunggu", counts.menunggu);
      setText("k_dipanggil", counts.dipanggil);
      setText("k_dilayani", counts.dilayani);
      setText("k_selesai", counts.selesai);
      setText("k_tidak_hadir", counts.tidak_hadir);

      if(sub){
        const poliLabel = data?.poli_label ?? data?.poli ?? "-";
        const tanggalLabel = data?.tanggal_label ?? data?.date ?? dateStr;
        sub.textContent = `Poli ${poliLabel} • ${tanggalLabel}`;
      }

      setText("tx_menunggu", counts.menunggu);
      setText("tx_dipanggil", counts.dipanggil);
      setText("tx_dilayani", counts.dilayani);
      setText("tx_selesai", counts.selesai);
      setText("tx_tidak_hadir", counts.tidak_hadir);

      setWidth("bar_menunggu", counts.menunggu, total);
      setWidth("bar_dipanggil", counts.dipanggil, total);
      setWidth("bar_dilayani", counts.dilayani, total);
      setWidth("bar_selesai", counts.selesai, total);
      setWidth("bar_tidak_hadir", counts.tidak_hadir, total);

      setDonutProgress(counts.selesai, total);

    }catch(e){
      if(sub) sub.textContent = "Gagal memuat statistik (cek route dokternya).";
      // opsional: untuk debug cepat (lihat console)
      // console.error(e);
    }
  }

  // ===== Events =====
  document.getElementById("btnToday")?.addEventListener("click", () => {
    selectedDate = yyyy_mm_dd(new Date());
    document.getElementById("datePick").value = selectedDate;
    markActive("today");
    syncUrl(selectedDate);
    loadStats(selectedDate);
  });

  document.getElementById("btnYesterday")?.addEventListener("click", () => {
    const d = new Date();
    d.setDate(d.getDate() - 1);
    selectedDate = yyyy_mm_dd(d);
    document.getElementById("datePick").value = selectedDate;
    markActive("yesterday");
    syncUrl(selectedDate);
    loadStats(selectedDate);
  });

  document.getElementById("btnPick")?.addEventListener("click", () => {
    markActive("pick");
    document.getElementById("datePick")?.focus();
  });

  document.getElementById("btnApply")?.addEventListener("click", () => {
    const v = document.getElementById("datePick")?.value;
    if(!v) return;
    selectedDate = v;
    markActive("pick");
    syncUrl(selectedDate);
    loadStats(selectedDate);
  });

  document.getElementById("datePick")?.addEventListener("change", () => {
    markActive("pick");
  });

  // ===== Init =====
  document.getElementById("datePick").value = selectedDate;

  const today = yyyy_mm_dd(new Date());
  const yd = (()=>{ const d=new Date(); d.setDate(d.getDate()-1); return yyyy_mm_dd(d); })();
  if(selectedDate === today) markActive("today");
  else if(selectedDate === yd) markActive("yesterday");
  else markActive("pick");

  loadStats(selectedDate);

  // Real-time silent refresh
  setInterval(() => loadStats(selectedDate), 5000);
</script>
@endsection

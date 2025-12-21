{{-- resources/views/tv/index.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>TV Antrian Puskesmas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    * { box-sizing: border-box; }

    body{
      margin: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: radial-gradient(circle at top, #4b6bff 0, #1a237e 35%, #0a102e 70%, #020412 100%);
      color: #fff;
      overflow: hidden;
    }

    header{
      display:flex;align-items:center;justify-content:space-between;
      padding:16px 40px;
      background: linear-gradient(90deg, rgba(10,23,80,0.8), rgba(21,101,192,0.85));
      box-shadow:0 6px 20px rgba(0,0,0,0.4);
      position:relative;z-index:10;
    }

    .header-left{display:flex;align-items:center;gap:14px;}
    .logo-puskesmas{
      width:56px;height:56px;object-fit:contain;border-radius:50%;
      background:#fff;padding:6px;box-shadow:0 4px 10px rgba(0,0,0,0.4);
    }
    header .brand-title{font-size:26px;font-weight:700;letter-spacing:1px;}
    header .brand-subtitle{font-size:16px;opacity:.9;}
    header .right-box{text-align:right;}
    .clock{font-size:22px;font-weight:600;}
    .date-text{font-size:14px;opacity:.8;}

    .main-wrapper{
      display:flex;gap:24px;padding:24px 40px 32px;
      height: calc(100vh - 80px);
    }
    .current-section{
      flex:1.2;display:flex;flex-direction:column;align-items:center;justify-content:center;
    }
    .side-section{flex:1;display:flex;flex-direction:column;gap:18px;justify-content:center;}

    .glass-card{
      background:rgba(255,255,255,.1);
      border-radius:24px;
      box-shadow:0 20px 40px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.06);
      padding:22px 26px;
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
    }

    .current-label{
      font-size:26px;font-weight:600;margin-bottom:12px;
      text-transform:uppercase;letter-spacing:2px;opacity:.95;
    }

    .current-number-wrapper{position:relative;display:inline-block;padding:26px 60px;border-radius:999px;}
    .pulse-ring{
      position:absolute;inset:-30px;border-radius:999px;
      background:radial-gradient(circle, rgba(79,195,247,0.45), transparent 60%);
      opacity:0;animation:pulse 2.5s infinite;z-index:-1;
    }
    @keyframes pulse{
      0%{transform:scale(.9);opacity:0;}
      40%{transform:scale(1);opacity:.75;}
      100%{transform:scale(1.1);opacity:0;}
    }

    .current-number{
      font-size:160px;font-weight:800;letter-spacing:6px;color:#ffeb3b;
      text-shadow:0 0 20px rgba(255,235,59,0.8), 0 0 40px rgba(255,255,255,0.7);
    }
    .current-number--highlight{animation: highlightFlash .9s ease-out;}
    @keyframes highlightFlash{
      0%{transform:scale(1);filter:brightness(1);}
      25%{transform:scale(1.08);filter:brightness(1.4);}
      55%{transform:scale(.95);filter:brightness(1.1);}
      100%{transform:scale(1);filter:brightness(1);}
    }

    .current-poli{margin-top:16px;font-size:34px;text-transform:uppercase;letter-spacing:3px;}

    /* ✅ STATUS BADGE */
    .status-badge{
      margin-top:12px;display:inline-flex;align-items:center;gap:8px;
      padding:6px 14px;border-radius:999px;
      font-size:14px;text-transform:uppercase;letter-spacing:1.2px;
      background: rgba(13,110,253,.18);
      color: #cfe2ff;
    }
    .status-badge.status--dipanggil{background: rgba(255,193,7,.18); color:#ffe8a3;}
    .status-badge.status--dilayani{background: rgba(13,110,253,.18); color:#cfe2ff;}
    .status-badge.status--menunggu{background: rgba(108,117,125,.18); color:#e2e3e5;}
    .status-badge.status--selesai{background: rgba(25,135,84,.18); color:#b7f5d0;}
    .status-badge.status--dilewati{background: rgba(220,53,69,.18); color:#ffd0d6;}
    .status-badge.status--tidak_hadir{background: rgba(220,53,69,.18); color:#ffd0d6;}

    .status-dot{
      width:10px;height:10px;border-radius:999px;
      background:#00e676;box-shadow:0 0 10px rgba(0,230,118,.9);
    }

    .no-data-text{margin-top:16px;font-size:20px;opacity:.9;}

    .card-title{
      font-size:22px;font-weight:600;text-transform:uppercase;
      letter-spacing:1.6px;margin-bottom:14px;
    }

    .next-item{
      display:flex;align-items:center;justify-content:space-between;
      padding:10px 12px;border-radius:14px;background:rgba(0,0,0,0.25);
      margin-bottom:8px;opacity:0;transform:translateY(16px);
      animation: slideUpFade .55s ease-out forwards;
    }
    @keyframes slideUpFade{0%{opacity:0;transform:translateY(16px);}100%{opacity:1;transform:translateY(0);}}
    .next-num{font-size:32px;font-weight:700;}
    .next-poli{font-size:16px;opacity:.85;text-transform:uppercase;}
    .next-badge{
      padding:4px 10px;border-radius:999px;border:1px solid rgba(255,255,255,0.35);
      font-size:12px;text-transform:uppercase;letter-spacing:1px;opacity:.9;
    }

    .info-text{font-size:16px;line-height:1.8;opacity:.95;}
    .info-text ul{margin:0;padding-left:20px;}

    .footer-bar{
      position:fixed;left:0;right:0;bottom:0;height:36px;overflow:hidden;
      background: linear-gradient(90deg, #0d47a1, #1976d2);
      box-shadow: 0 -4px 20px rgba(0,0,0,0.7);
    }
    .footer-bar marquee{color:#fff;font-size:15px;line-height:36px;font-family:inherit;}

    @media (max-width:1024px){
      .main-wrapper{flex-direction:column;height:auto;}
      .current-number{font-size:110px;}
    }
  </style>
</head>

<body>
<header>
  <div class="header-left">
    <img src="{{ asset('assets/img/logo-puskesmas.png') }}" alt="Logo Puskesmas" class="logo-puskesmas">
    <div>
      <div class="brand-title">MONITOR LAYANAN ANTRIAN</div>
      <div class="brand-subtitle">PUSKESMAS KALIGANDU</div>
    </div>
  </div>
  <div class="right-box">
    <div class="clock" id="clock">--:--:--</div>
    <div class="date-text" id="dateText">-</div>
  </div>
</header>

<div class="main-wrapper">

  <section class="current-section">
    <div class="glass-card" style="text-align:center; max-width: 680px; width: 100%;">
      <div class="current-label">Nomor yang Sedang Dipanggil</div>

      <div class="current-number-wrapper">
        <div class="pulse-ring"></div>
        <div class="current-number" id="current-number">
          {{ $current ? $current->no_antrian : '--' }}
        </div>
      </div>

      <div class="current-poli" id="current-poli">
        @if($current)
          POLI {{ strtoupper($current->poli) }}
        @else
          &nbsp;
        @endif
      </div>

      <div class="status-badge" id="status-badge" style="{{ $current ? '' : 'display:none;' }}">
        <span class="status-dot"></span>
        <span id="status-state">STATUS</span>
        <span style="opacity:.85">•</span>
        <span id="status-text">Silakan menuju ruang pelayanan</span>
      </div>

      <div class="no-data-text" id="current-empty-text" style="{{ $current ? 'display:none;' : '' }}">
        Belum ada nomor yang dipanggil.
      </div>
    </div>
  </section>

  <section class="side-section">
    <div class="glass-card">
      <div class="card-title">Nomor Berikutnya</div>
      <div id="next-list">
        @if(isset($next) && $next->count())
          @foreach($next as $index => $row)
            <div class="next-item" style="animation-delay: {{ $index * 0.08 }}s">
              <div>
                <div class="next-num">{{ $row->no_antrian }}</div>
                <div class="next-poli">POLI {{ strtoupper($row->poli) }}</div>
              </div>
              <div class="next-badge">{{ $index === 0 ? 'SEGERA' : 'MENUNGGU' }}</div>
            </div>
          @endforeach
        @else
          <div class="no-data-text">Belum ada antrian berikutnya.</div>
        @endif
      </div>
    </div>

    <div class="glass-card">
      <div class="card-title">Informasi</div>
      <div class="info-text">
        <ul>
          <li>Harap menunggu hingga nomor Anda tampil pada layar dan dipanggil oleh petugas.</li>
          <li>Siapkan kartu identitas dan kartu BPJS (jika ada) sebelum memasuki ruang pelayanan.</li>
          <li>Jika nomor Anda terlewat, segera hubungi petugas di loket informasi.</li>
          <li>Layar ini diperbarui secara otomatis setiap beberapa detik.</li>
        </ul>
      </div>
    </div>
  </section>
</div>

<div class="footer-bar">
  <marquee behavior="scroll" direction="left" scrollamount="4">
    Terima kasih atas kesabaran Anda. Jaga kesehatan, gunakan masker bila sedang kurang sehat, dan ikuti arahan petugas Puskesmas Kaligandu. •
    Tetap jaga jarak dan cuci tangan secara berkala. • Semoga lekas sembuh.
  </marquee>
</div>

<audio id="bell-audio" src="{{ asset('assets/sound/bell.mp3') }}"></audio>

<script>
  // Jam & tanggal
  function updateDateTime() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    const s = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('clock').innerText = `${h}:${m}:${s}`;

    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('dateText').innerText = now.toLocaleDateString('id-ID', options);
  }
  setInterval(updateDateTime, 1000);
  updateDateTime();

  let audioAllowed = false;

  // ✅ triggerKey utama dari backend: called_key (dibuat dari updated_at)
  let lastTriggerKey = null;

  function playBell() {
    const audio = document.getElementById('bell-audio');
    if (!audio || !audioAllowed) return;
    audio.currentTime = 0;
    audio.play().catch(() => {});
  }

  function speakQueue(number, poli) {
    if (!audioAllowed) return;
    if (!('speechSynthesis' in window)) return;

    const text = `Nomor antrian... ${number}... dipersilakan... menuju... poli ${poli}.`;
    const u = new SpeechSynthesisUtterance(text);
    u.lang = 'id-ID';
    u.rate = 0.85;
    u.pitch = 1.05;
    u.volume = 1.0;

    window.speechSynthesis.cancel();
    window.speechSynthesis.speak(u);
  }

  // ✅ klik sekali untuk aktifkan suara (kebijakan browser)
  document.addEventListener('click', () => {
    audioAllowed = true;
  }, { once: true });

  function statusLabel(s) {
    s = String(s || '').toLowerCase();
    if (s === 'dilayani') return 'SEDANG DILAYANI';
    if (s === 'dipanggil') return 'DIPANGGIL';
    if (s === 'selesai') return 'SELESAI';
    if (s === 'dilewati') return 'DILEWATI';
    if (s === 'tidak_hadir') return 'TIDAK HADIR';
    return 'MENUNGGU';
  }

  function statusClass(s) {
    s = String(s || '').toLowerCase();
    if (s === 'dilayani') return 'status--dilayani';
    if (s === 'dipanggil') return 'status--dipanggil';
    if (s === 'selesai') return 'status--selesai';
    if (s === 'dilewati') return 'status--dilewati';
    if (s === 'tidak_hadir') return 'status--tidak_hadir';
    return 'status--menunggu';
  }

  async function fetchQueueData() {
    try {
      const res = await fetch("{{ route('tv.data') }}", { cache: 'no-store' });
      const data = await res.json();

      const currentNumberEl = document.getElementById('current-number');
      const currentPoliEl   = document.getElementById('current-poli');
      const statusBadgeEl   = document.getElementById('status-badge');
      const statusStateEl   = document.getElementById('status-state');
      const statusTextEl    = document.getElementById('status-text');
      const currentEmptyEl  = document.getElementById('current-empty-text');

      if (data.current) {
        const newNumber = data.current.no_antrian;
        const poli = String(data.current.poli || '').toUpperCase();
        const st = String(data.current.status || 'menunggu').toLowerCase();

        // ✅ pakai called_key dari backend (pasti berubah saat panggil ulang karena updated_at berubah)
        const triggerKey = String(data.current.called_key || '');

        currentNumberEl.textContent = newNumber;
        currentPoliEl.textContent   = 'POLI ' + poli;

        statusBadgeEl.className = 'status-badge ' + statusClass(st);
        statusStateEl.textContent = statusLabel(st);
        statusTextEl.textContent = (st === 'dilayani')
          ? 'Sedang dilakukan pelayanan'
          : 'Silakan menuju ruang pelayanan';

        statusBadgeEl.style.display = 'inline-flex';
        currentEmptyEl.style.display = 'none';

        // animasi & suara ketika called_key berubah (bukan hanya nomor)
        if (lastTriggerKey === null) {
          lastTriggerKey = triggerKey; // load pertama: tidak bunyi
        } else if (triggerKey !== '' && triggerKey !== lastTriggerKey) {
          lastTriggerKey = triggerKey;

          currentNumberEl.classList.remove('current-number--highlight');
          void currentNumberEl.offsetWidth;
          currentNumberEl.classList.add('current-number--highlight');

          // 🔔 dan 🔊 hanya untuk status dipanggil
          if (st === 'dipanggil') {
            playBell();
            setTimeout(() => speakQueue(newNumber, poli), 2500);
          }
        }

      } else {
        currentNumberEl.textContent = '--';
        currentPoliEl.innerHTML = '&nbsp;';
        statusBadgeEl.style.display = 'none';
        currentEmptyEl.style.display = 'block';
        lastTriggerKey = null;
      }

      // Next list
      const nextListEl = document.getElementById('next-list');
      if (data.next && data.next.length > 0) {
        let html = '';
        data.next.forEach((row, index) => {
          html += `
            <div class="next-item" style="animation-delay:${index * 0.08}s">
              <div>
                <div class="next-num">${row.no_antrian}</div>
                <div class="next-poli">POLI ${String(row.poli).toUpperCase()}</div>
              </div>
              <div class="next-badge">${index === 0 ? 'SEGERA' : 'MENUNGGU'}</div>
            </div>
          `;
        });
        nextListEl.innerHTML = html;
      } else {
        nextListEl.innerHTML = '<div class="no-data-text">Belum ada antrian berikutnya.</div>';
      }

    } catch (e) {
      console.error('Gagal mengambil data TV antrian:', e);
    }
  }

  fetchQueueData();
  setInterval(fetchQueueData, 5000);
</script>
</body>
</html>

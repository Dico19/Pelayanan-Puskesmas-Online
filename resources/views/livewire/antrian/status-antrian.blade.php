@push('styles')
<style>
    /* =========================================================
       Status Antrian (PK) - LIGHT + DARK (CSS variables)
       FIX:
       - jangan ganggu navbar / toggle gelap-terang (klik)
       - teks & form tetap kebaca pada dark mode
       ========================================================= */

    /* --- Default (LIGHT) --- */
    .pk-queue-status{
        --pk-bg: #f3f6ff;
        --pk-card-bg: #ffffff;
        --pk-border: rgba(15,23,42,.08);

        --pk-text: #0f172a;
        --pk-muted: #64748b;

        --pk-soft-bg: #ffffff;
        --pk-soft-border: rgba(15,23,42,.08);

        --pk-status-bg: #ecfeff;
        --pk-status-border: #a5f3fc;

        --pk-alert-bg: #e0f2fe;
        --pk-alert-border: rgba(2,132,199,.25);
        --pk-alert-text: #0f172a;

        --pk-input-bg: #ffffff;
        --pk-input-border: rgba(15,23,42,.12);

        background: var(--pk-bg);
        min-height: calc(100vh - 170px);

        /* ✅ penting: jangan bikin overlay naik ke navbar */
        padding-top: 120px;
        padding-bottom: 40px;

        color: var(--pk-text);

        /* ✅ pastikan layer konten tidak "menutup" header */
        position: relative;
        z-index: 0;
    }

    /* ✅ kalau ada pseudo element overlay dari theme lain, jangan tangkap klik */
    .pk-queue-status::before,
    .pk-queue-status::after{
        pointer-events: none !important;
    }

    /* --- Override untuk DARK MODE --- */
    body.dark-mode .pk-queue-status,
    html.dark-mode .pk-queue-status{
        /* ✅ background dark halus mirip contoh kanan */
        --pk-bg: radial-gradient(1200px 600px at 50% -10%, rgba(59,130,246,.18), transparent 60%),
                 linear-gradient(180deg, #050b17 0%, #030816 100%);

        --pk-card-bg: rgba(10, 18, 36, 0.88);
        --pk-border: rgba(255,255,255,.10);

        --pk-text: rgba(255,255,255,.92);
        --pk-muted: rgba(255,255,255,.62);

        --pk-soft-bg: rgba(2, 10, 26, .55);
        --pk-soft-border: rgba(255,255,255,.10);

        /* status box */
        --pk-status-bg: rgba(59, 130, 246, .12);
        --pk-status-border: rgba(59, 130, 246, .28);

        --pk-alert-bg: rgba(13, 110, 253, .14);
        --pk-alert-border: rgba(13,110,253,.30);
        --pk-alert-text: rgba(255,255,255,.88);

        --pk-input-bg: rgba(2, 10, 26, .55);
        --pk-input-border: rgba(255,255,255,.14);
    }

    /* --- Card utama --- */
    .pk-status-card{
        background: var(--pk-card-bg);
        border-radius: 18px;
        padding: 28px;
        border: 1px solid var(--pk-border);
        box-shadow: 0 20px 55px rgba(0,0,0,.22);
        backdrop-filter: blur(10px);
    }

    /* --- Judul & subtitle --- */
    .pk-status-title{
        font-weight: 800;
        color: var(--pk-text);
        margin: 0;
        font-size: 24px;
    }
    .pk-status-subtitle{
        color: var(--pk-muted);
        font-size: 14px;
    }

    /* --- Badge nomor --- */
    .pk-ticket-badge{
        background: linear-gradient(135deg,#3b82f6,#1d4ed8);
        color:#fff;
        border-radius:16px;
        padding:14px 18px;
        min-width:150px;
        box-shadow:0 16px 30px rgba(37,99,235,.25);
    }
    .badge-label{
        font-size:11px;
        letter-spacing:.12em;
        text-transform:uppercase;
        opacity:.9;
    }
    .badge-number{
        font-size:36px;
        font-weight:900;
        line-height:1;
    }
    .badge-poli{
        display:inline-block;
        font-size:11px;
        padding:3px 10px;
        border-radius:999px;
        background:rgba(255,255,255,.18);
        letter-spacing:.08em;
    }

    /* --- Info item --- */
    .pk-info-item{
        border:1px solid var(--pk-soft-border);
        background: var(--pk-soft-bg);
        border-radius:12px;
        padding:12px 14px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:10px;
        min-height:54px;
        color: var(--pk-text);
    }
    .pk-info-item .info-label{
        font-size:11px;
        text-transform:uppercase;
        letter-spacing:.06em;
        color: var(--pk-muted);
    }
    .pk-info-item .info-value{
        font-weight:800;
        color: var(--pk-text);
    }

    /* Status khusus */
    .pk-info-status{
        background: var(--pk-status-bg) !important;
        border-color: var(--pk-status-border) !important;
    }

    .dot-live{
        width:10px;
        height:10px;
        border-radius:50%;
        background:#22c55e;
        box-shadow:0 0 0 4px rgba(34,197,94,.15);
        flex:0 0 auto;
    }

    /* Alert */
    .pk-status-alert{
        background: var(--pk-alert-bg);
        border: 1px solid var(--pk-alert-border);
        color: var(--pk-alert-text);
        padding: 12px 14px;
        border-radius: 12px;
        font-size: 14px;
    }

    .pk-status-footer small{ color: var(--pk-muted); }

    /* text-muted bootstrap supaya ikut theme */
    .pk-queue-status .text-muted{ color: var(--pk-muted) !important; }

    /* HR biar ikut theme */
    .pk-status-card hr{
        border-color: var(--pk-border) !important;
        opacity: 1;
    }

    /* Button */
    .pk-queue-status .btn-primary{
        box-shadow: 0 14px 35px rgba(13,110,253,.18);
    }

    @media (max-width: 576px){
        .pk-status-card{ padding:18px; }
        .badge-number{ font-size:30px; }
        .pk-ticket-badge{ min-width: 135px; }
    }
</style>
@endpush

@php
    // ✅ tombol kembali: prioritas dari ?back=..., fallback ke hasil pencarian pakai NIK, terakhir previous
    $backUrl = request('back');

    if (!$backUrl && !empty($antrian->no_ktp)) {
        $backUrl = route('antrian.cari', ['no_ktp' => $antrian->no_ktp]);
    }

    if (!$backUrl) {
        $backUrl = url()->previous();
    }
@endphp

<div class="pk-queue-status">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-9">

                {{-- ✅ tombol kembali (balik ke hasil pencarian) --}}
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="pk-status-card shadow-sm" wire:poll.10s>

                    {{-- HEADER --}}
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                        <div>
                            <h2 class="pk-status-title mb-1">Status Antrian Anda</h2>
                            <p class="pk-status-subtitle mb-0">
                                Terima kasih sudah mendaftar. Mohon menunggu panggilan sesuai nomor antrian.
                            </p>
                        </div>

                        {{-- BADGE NOMOR ANTRIAN --}}
                        <div class="pk-ticket-badge text-center">
                            <span class="badge-label d-block mb-1">Nomor Antrian</span>
                            <div class="badge-number mb-1">
                                {{ $antrian->no_antrian ?? $antrian->nomor_antrian ?? '-' }}
                            </div>
                            <span class="badge-poli">
                                {{ strtoupper($antrian->poli ?? 'POLI') }}
                            </span>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- INFO GRID --}}
                    <div class="row g-3">

                        <div class="col-md-6">
                            <div class="pk-info-item">
                                <span class="info-label">Poli</span>
                                <span class="info-value text-capitalize">
                                    {{ $antrian->poli ?? '-' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="pk-info-item">
                                <span class="info-label">Orang di depan Anda</span>
                                <span class="info-value">
                                    {{ $orangDiDepan }} orang
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="pk-info-item">
                                <span class="info-label">Estimasi waktu tunggu</span>
                                <span class="info-value">
                                    @if($estimasiMenit > 0)
                                        ± {{ $estimasiMenit }} menit
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- STATUS --}}
                        <div class="col-md-6">
                            <div class="pk-info-item pk-info-status d-flex align-items-center gap-2">
                                <span class="dot-live"></span>
                                <div>
                                    <span class="info-label d-block">Status</span>

                                    @php
                                        use Carbon\Carbon;

                                        $tglAntrian = isset($antrian->tanggal_antrian)
                                            ? Carbon::parse($antrian->tanggal_antrian)
                                            : null;

                                        $rawNo  = $antrian->no_antrian ?? $antrian->nomor_antrian ?? null;
                                        $noUrut = $rawNo ? (int) preg_replace('/\D/', '', $rawNo) : null;

                                        $perkiraanJamText = null;

                                        if ($tglAntrian && $tglAntrian->isFuture() && $noUrut) {
                                            $jamBuka   = 8;
                                            $menitSlot = 15;

                                            $startTime    = $tglAntrian->copy()->setTime($jamBuka, 0);
                                            $perkiraanJam = $startTime->copy()->addMinutes(($noUrut - 1) * $menitSlot);

                                            $jamTutup = $tglAntrian->copy()->setTime(17, 0);
                                            if ($perkiraanJam->lessThanOrEqualTo($jamTutup)) {
                                                $perkiraanJamText = $perkiraanJam->format('H:i') . ' WIB';
                                            }
                                        }
                                    @endphp

                                    @if($tglAntrian && $tglAntrian->isToday())
                                        <span class="info-value">
                                            @if ($orangDiDepan === 0)
                                                Antrian Anda akan segera dipanggil.
                                            @else
                                                Sedang menunggu panggilan.
                                            @endif
                                        </span>

                                    @elseif($tglAntrian && $tglAntrian->isFuture())
                                        <span class="info-value d-block">
                                            Jadwal antrian Anda pada
                                            <strong>{{ $tglAntrian->translatedFormat('l, d F Y') }}</strong>
                                        </span>

                                        @if($perkiraanJamText)
                                            <small class="text-muted d-block">
                                                Perkiraan giliran sekitar <strong>{{ $perkiraanJamText }}</strong>
                                            </small>
                                        @endif

                                    @else
                                        <span class="info-value">
                                            Status antrian tidak tersedia.
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- ALERT --}}
                    <div class="pk-status-alert mt-4">
                        <i class="bi bi-info-circle me-2"></i>
                        Silakan menunggu di ruang tunggu. Halaman ini akan memperbarui data secara otomatis.
                    </div>

                    <div class="pk-status-footer mt-3 text-center text-md-start">
                        <small>
                            Tips: Pastikan Anda tidak jauh dari ruang tunggu agar tidak terlewat saat nomor Anda dipanggil.
                        </small>
                    </div>

                    <div class="mt-4 mb-2 text-center">
                        <p style="font-size:14px;color:var(--pk-muted);margin-bottom:6px;">
                            Silakan cetak tiket antrian sebagai bukti dan tunjukkan kepada petugas saat dipanggil.
                        </p>
                    </div>

                    <div class="text-center mt-1">
                        {{-- ✅ FIX UTAMA: route param harus pakai "antrian" (bukan "id") --}}
                        <a href="{{ route('antrian.tiket', ['antrian' => $antrian->id, 'back' => $backUrl]) }}"
                           class="btn btn-primary px-4 py-2">
                            <i class="bi bi-printer me-1"></i> Cetak Tiket Antrian
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

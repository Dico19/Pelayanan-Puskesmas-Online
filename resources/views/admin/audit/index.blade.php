@extends('dashboard.layouts.main')

@section('content')
@php
    use Carbon\Carbon;
    Carbon::setLocale('id');

    $ACTION_LABELS = [
        'dipanggil'     => 'Dipanggil',
        'panggil_ulang' => 'Panggil Ulang',
        'mulai'         => 'Mulai',
        'selesai'       => 'Selesai',
        'lewati'        => 'Lewat',
        'lewat'         => 'Lewat',
    ];

    $ACTION_BADGE = [
        'dipanggil'     => 'primary',
        'panggil_ulang' => 'secondary',
        'mulai'         => 'info',
        'selesai'       => 'success',
        'lewati'        => 'danger',
        'lewat'         => 'danger',
    ];

    $labelAksi = fn($action) => $ACTION_LABELS[(string)$action] ?? ucfirst((string)$action);
    $badgeAksi = fn($action) => $ACTION_BADGE[(string)$action] ?? 'dark';

    $statTotal        = $stats['total'] ?? 0;
    $statDipanggil    = $stats['dipanggil'] ?? 0;
    $statMulai        = $stats['mulai'] ?? 0;
    $statSelesaiLewat = $stats['selesai_lewat'] ?? 0;
@endphp

<style>
    /* ============ AUDIT STYLE (langsung di view biar pasti kebaca) ============ */
    .audit-wrap{ padding: 6px 2px 30px; }
    .audit-hero{
        background: linear-gradient(135deg, rgba(15,76,255,.08), rgba(0,184,255,.06));
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 18px;
        padding: 18px 18px;
        box-shadow: 0 10px 26px rgba(0,0,0,.06);
    }
    .audit-title{ font-weight: 800; letter-spacing: .2px; margin:0; }
    .audit-sub{ color:#6c757d; margin-top: 4px; }

    .audit-card{
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 26px rgba(0,0,0,.06);
        overflow: hidden;
    }

    .audit-stat{
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 26px rgba(0,0,0,.06);
        padding: 16px 16px;
        height: 100%;
        position: relative;
    }
    .audit-stat .lbl{ color:#6c757d; font-size: .78rem; }
    .audit-stat .val{ font-size: 1.7rem; font-weight: 900; line-height: 1.1; margin-top: 3px; }

    .audit-icon{
        width: 46px; height: 46px;
        border-radius: 14px;
        display:flex; align-items:center; justify-content:center;
        position:absolute; right: 14px; top: 14px;
        box-shadow: 0 10px 24px rgba(0,0,0,.08);
        background: rgba(13,110,253,.10);
        color: #0d6efd;
        font-size: 1.2rem;
    }
    .audit-icon.success{ background: rgba(25,135,84,.12); color:#198754; }
    .audit-icon.info{ background: rgba(13,202,240,.14); color:#0dcaf0; }
    .audit-icon.danger{ background: rgba(220,53,69,.12); color:#dc3545; }

    .audit-table thead th{
        font-size:.72rem;
        text-transform:uppercase;
        letter-spacing:.7px;
        color:#6c757d;
        white-space:nowrap;
        background:#fbfbfd;
        border-bottom:1px solid rgba(0,0,0,.06);
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .audit-table tbody tr:hover{ background: rgba(13,110,253,.04); }
    .chip{
        display:inline-flex; align-items:center; gap:6px;
        padding:4px 10px;
        border-radius:999px;
        font-size:.78rem;
        border:1px solid rgba(0,0,0,.08);
        background:#f8f9fa;
        white-space:nowrap;
    }
    .audit-actions{ display:flex; gap: .5rem; justify-content:flex-end; }
</style>

<div class="audit-wrap container-fluid">

    {{-- HERO --}}
    <div class="audit-hero d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
        <div>
            <h2 class="audit-title">Audit Log</h2>
            <div class="audit-sub">
                Catatan aksi dokter: <b>panggil</b> / <b>mulai</b> / <b>selesai</b> / <b>lewat</b>.
            </div>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- STATS --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="audit-stat">
                <div class="audit-icon"><i class="bi bi-collection"></i></div>
                <div class="lbl">Total (Filter)</div>
                <div class="val">{{ $statTotal }}</div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="audit-stat">
                <div class="audit-icon"><i class="bi bi-telephone-outbound"></i></div>
                <div class="lbl">Dipanggil</div>
                <div class="val">{{ $statDipanggil }}</div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="audit-stat">
                <div class="audit-icon info"><i class="bi bi-play-circle"></i></div>
                <div class="lbl">Mulai</div>
                <div class="val">{{ $statMulai }}</div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="audit-stat">
                <div class="audit-icon success"><i class="bi bi-check2-circle"></i></div>
                <div class="lbl">Selesai / Lewat</div>
                <div class="val">{{ $statSelesaiLewat }}</div>
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="audit-card mb-4">
        <div class="p-3 border-bottom">
            <div class="fw-semibold">Filter</div>
            <div class="text-muted small">Gunakan filter untuk mencari catatan audit sesuai kebutuhan.</div>
        </div>

        <div class="p-3">
            <form method="GET" action="{{ route('admin.audit.index') }}" class="row g-2 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label mb-1">Cari</label>
                    <input type="text" class="form-control" name="q" value="{{ $q ?? '' }}"
                           placeholder="No KTP / No antrian / Nama pasien / Nama dokter">
                </div>

                <div class="col-lg-2">
                    <label class="form-label mb-1">Aksi</label>
                    <select name="action" class="form-select">
                        <option value="all" {{ ($action ?? 'all') === 'all' ? 'selected' : '' }}>Semua</option>
                        @foreach($ACTION_LABELS as $k => $v)
                            <option value="{{ $k }}" {{ ($action ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label mb-1">Poli</label>
                    <select name="poli" class="form-select">
                        <option value="all" {{ ($poli ?? 'all') === 'all' ? 'selected' : '' }}>Semua</option>
                        @foreach(($polis ?? []) as $p)
                            <option value="{{ $p }}" {{ ($poli ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label mb-1">Dari</label>
                    <input type="date" class="form-control" name="from" value="{{ $from ?? '' }}">
                </div>

                <div class="col-lg-2">
                    <label class="form-label mb-1">Sampai</label>
                    <input type="date" class="form-control" name="to" value="{{ $to ?? '' }}">
                </div>

                <div class="col-12 mt-2">
                    <div class="audit-actions">
                        <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-secondary">Reset</a>
                        <button class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="audit-card">
        <div class="table-responsive" style="max-height: 520px;">
            <table class="table table-hover align-middle mb-0 audit-table">
                <thead>
                <tr>
                    <th style="width:180px;">Waktu</th>
                    <th>Dokter</th>
                    <th>Poli</th>
                    <th style="width:90px;">No</th>
                    <th>Pasien</th>
                    <th style="width:130px;">Aksi</th>
                    <th style="width:300px;">Perubahan</th>
                </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    @php
                        $before = $log->before ?? [];
                        $after  = $log->after ?? [];

                        $summary = [];
                        foreach(['status','is_call'] as $k){
                            $bv = $before[$k] ?? null;
                            $av = $after[$k] ?? null;
                            if ($bv !== null || $av !== null) {
                                if ((string)$bv !== (string)$av) $summary[] = [$k, $bv, $av];
                            }
                        }

                        $dokter     = $log->dokter_nama ?? optional($log->user)->name ?? '-';
                        $pasienNama = $log->pasien_nama ?? '-';
                        $noKtp      = $log->no_ktp ?? '';
                        $noAntrian  = $log->no_antrian ?? '-';
                    @endphp

                    <tr>
                        <td>
                            <div class="fw-semibold">
                                {{ optional($log->created_at)->translatedFormat('d M Y') }}
                                • {{ optional($log->created_at)->format('H:i') }}
                            </div>
                            <div class="text-muted small">#{{ $log->id }}</div>
                        </td>

                        <td class="fw-semibold">{{ $dokter }}</td>

                        <td>
                            @if(!empty($log->poli))
                                <span class="chip"><i class="bi bi-hospital"></i> {{ $log->poli }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>{{ $noAntrian }}</td>

                        <td>
                            <div class="fw-semibold">{{ $pasienNama }}</div>
                            @if($noKtp !== '')
                                <div class="text-muted small">{{ $noKtp }}</div>
                            @endif
                        </td>

                        <td>
                            <span class="badge bg-{{ $badgeAksi($log->action) }}">
                                {{ $labelAksi($log->action) }}
                            </span>
                        </td>

                        <td>
                            @if(count($summary) === 0)
                                <span class="text-muted small">Tidak ada perubahan ringkas.</span>
                            @else
                                @foreach(array_slice($summary, 0, 2) as $row)
                                    <span class="chip mb-1">
                                        <b>{{ $row[0] }}</b>:
                                        <span class="text-muted">{{ $row[1] ?? '-' }}</span>
                                        <span class="text-muted">→</span>
                                        <span class="fw-semibold">{{ $row[2] ?? '-' }}</span>
                                    </span>
                                @endforeach
                            @endif

                            <button type="button"
                                    class="btn btn-sm btn-outline-primary ms-2 audit-detail-btn"
                                    data-url="{{ route('admin.audit.show', $log->id) }}">
                                <i class="bi bi-eye"></i> Detail
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Belum ada audit log.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top">
            {{ $logs->links() }}
        </div>
    </div>

</div>

{{-- MODAL --}}
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
            <h5 class="modal-title" id="auditDetailTitle">Detail Audit</h5>
            <div class="text-muted small">Before = kondisi sebelum aksi, After = kondisi sesudah aksi.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="fw-bold mb-2">Before</div>
                <div id="auditBeforeBox" class="border rounded p-2 bg-light small"></div>
            </div>
            <div class="col-md-6">
                <div class="fw-bold mb-2">After</div>
                <div id="auditAfterBox" class="border rounded p-2 bg-light small"></div>
            </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const modalEl = document.getElementById('auditDetailModal');
    const modal = new bootstrap.Modal(modalEl);

    const beforeBox = document.getElementById('auditBeforeBox');
    const afterBox  = document.getElementById('auditAfterBox');
    const titleEl   = document.getElementById('auditDetailTitle');

    function esc(str){
        return String(str ?? '').replace(/[&<>"']/g, s => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
        }[s]));
    }

    function renderKV(obj){
        if(!obj || Object.keys(obj).length === 0){
            return '<div class="text-muted">Tidak ada data.</div>';
        }
        let html = '<table class="table table-sm mb-0">';
        for (const k in obj){
            let v = obj[k];
            if (typeof v === 'object' && v !== null) v = JSON.stringify(v);
            html += `<tr><td class="text-muted" style="width:38%">${esc(k)}</td><td class="fw-semibold">${esc(v ?? '-')}</td></tr>`;
        }
        html += '</table>';
        return html;
    }

    document.querySelectorAll('.audit-detail-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const url = btn.getAttribute('data-url');
            beforeBox.innerHTML = 'Memuat...';
            afterBox.innerHTML  = 'Memuat...';

            try{
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                const data = await res.json();

                titleEl.textContent = `${data.pasien_nama ?? '-'} • ${data.no_antrian ?? '-'} • ${data.action ?? ''}`;
                beforeBox.innerHTML = renderKV(data.before || {});
                afterBox.innerHTML  = renderKV(data.after || {});
                modal.show();
            }catch(e){
                beforeBox.innerHTML = '<div class="text-danger">Gagal memuat detail.</div>';
                afterBox.innerHTML  = '<div class="text-danger">Gagal memuat detail.</div>';
                modal.show();
            }
        });
    });
})();
</script>
@endpush

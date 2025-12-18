@extends('layouts.main')

@section('content')
@php
    use Carbon\Carbon;
    Carbon::setLocale('id');

    // ✅ Tidak pakai $actions dari controller (biar tidak error)
    $ACTION_LABELS = [
        'dipanggil'    => 'Dipanggil',
        'panggil_ulang'=> 'Panggil Ulang',
        'mulai'        => 'Mulai',
        'selesai'      => 'Selesai',
        'lewati'       => 'Lewat',
        'lewat'        => 'Lewat',
    ];

    $ACTION_BADGE = [
        'dipanggil'    => 'primary',
        'panggil_ulang'=> 'secondary',
        'mulai'        => 'info',
        'selesai'      => 'success',
        'lewati'       => 'danger',
        'lewat'        => 'danger',
    ];

    $labelAksi = function($action) use ($ACTION_LABELS) {
        $k = (string) $action;
        return $ACTION_LABELS[$k] ?? ucfirst($k);
    };

    $badgeAksi = function($action) use ($ACTION_BADGE) {
        $k = (string) $action;
        return $ACTION_BADGE[$k] ?? 'dark';
    };

    $statTotal = $stats['total'] ?? 0;
    $statDipanggil = $stats['dipanggil'] ?? 0;
    $statMulai = $stats['mulai'] ?? 0;
    $statSelesaiLewat = $stats['selesai_lewat'] ?? 0;
@endphp

<style>
    .audit-page-title{ font-weight:800; letter-spacing:.2px; }
    .audit-sub{ color:#6c757d; font-size:.9rem; }
    .audit-stat{
        border:1px solid rgba(0,0,0,.06);
        border-radius:16px;
        box-shadow:0 8px 20px rgba(0,0,0,.04);
        padding:14px 16px;
        background:#fff;
    }
    .audit-stat .label{ font-size:.72rem; color:#6c757d; text-transform:uppercase; letter-spacing:.6px; }
    .audit-stat .val{ font-size:1.6rem; font-weight:800; margin-top:6px; }
    .audit-table thead th{ font-size:.72rem; letter-spacing:.7px; text-transform:uppercase; color:#6c757d; }
    .chip{
        display:inline-flex; align-items:center; gap:6px;
        padding:3px 10px; border-radius:999px; font-size:.78rem;
        border:1px solid rgba(0,0,0,.08); background:#f8f9fa;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
    <div>
        <h2 class="audit-page-title mb-1">Audit Log</h2>
        <div class="audit-sub">Catatan aksi dokter: <b>panggil</b> / <b>mulai</b> / <b>selesai</b> / <b>lewat</b>.</div>
    </div>

    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Dashboard
    </a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="audit-stat">
            <div class="label">Total (Filter)</div>
            <div class="val">{{ $statTotal }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="audit-stat">
            <div class="label">Dipanggil</div>
            <div class="val">{{ $statDipanggil }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="audit-stat">
            <div class="label">Mulai</div>
            <div class="val">{{ $statMulai }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="audit-stat">
            <div class="label">Selesai / Lewat</div>
            <div class="val">{{ $statSelesaiLewat }}</div>
        </div>
    </div>
</div>

<div class="card card-soft mb-3">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.audit.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1">Cari</label>
                <input type="text" class="form-control" name="q" value="{{ $q ?? '' }}"
                       placeholder="No KTP / No antrian / Nama pasien / Nama dokter">
            </div>

            <div class="col-md-2">
                <label class="form-label mb-1">Aksi</label>
                <select name="action" class="form-select">
                    <option value="all" {{ ($action ?? 'all') === 'all' ? 'selected' : '' }}>Semua</option>
                    @foreach($ACTION_LABELS as $k => $v)
                        <option value="{{ $k }}" {{ ($action ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label mb-1">Poli</label>
                <select name="poli" class="form-select">
                    <option value="all" {{ ($poli ?? 'all') === 'all' ? 'selected' : '' }}>Semua</option>
                    @foreach(($polis ?? []) as $p)
                        <option value="{{ $p }}" {{ ($poli ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label mb-1">Dari</label>
                <input type="date" class="form-control" name="from" value="{{ $from ?? '' }}">
            </div>

            <div class="col-md-2">
                <label class="form-label mb-1">Sampai</label>
                <input type="date" class="form-control" name="to" value="{{ $to ?? '' }}">
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-secondary">Reset</a>
                <button class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Terapkan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card card-soft">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 audit-table">
                <thead>
                <tr>
                    <th style="width:180px;">Waktu</th>
                    <th>Dokter</th>
                    <th>Poli</th>
                    <th style="width:90px;">No</th>
                    <th>Pasien</th>
                    <th style="width:120px;">Aksi</th>
                    <th style="width:260px;">Perubahan</th>
                </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    @php
                        $before = $log->before ?? [];
                        $after  = $log->after ?? [];

                        // ringkasan perubahan singkat
                        $summary = [];
                        foreach(['status','is_call'] as $k){
                            $bv = $before[$k] ?? null;
                            $av = $after[$k] ?? null;
                            if ($bv !== null || $av !== null) {
                                if ((string)$bv !== (string)$av) {
                                    $summary[] = [$k, $bv, $av];
                                }
                            }
                        }

                        $dokter = $log->dokter_nama ?? optional($log->user)->name ?? '-';
                        $pasienNama = $log->pasien_nama ?? '-';
                        $noKtp = $log->no_ktp ?? '';
                        $noAntrian = $log->no_antrian ?? '-';
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">
                                {{ optional($log->created_at)->translatedFormat('d M Y') }} • {{ optional($log->created_at)->format('H:i') }}
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
                                Detail
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

        <div class="p-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
            <h5 class="modal-title" id="auditDetailTitle">Detail Audit</h5>
            <div class="text-muted small" id="auditDetailSub">Before = kondisi sebelum aksi, After = kondisi sesudah aksi.</div>
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

    function renderKV(obj){
        if(!obj || Object.keys(obj).length === 0){
            return '<div class="text-muted">Tidak ada data.</div>';
        }
        let html = '<table class="table table-sm mb-0">';
        for (const k in obj){
            let v = obj[k];
            if (typeof v === 'object' && v !== null) v = JSON.stringify(v);
            html += `<tr><td class="text-muted" style="width:38%">${k}</td><td class="fw-semibold">${v ?? '-'}</td></tr>`;
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

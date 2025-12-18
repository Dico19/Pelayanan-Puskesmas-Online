@php
  use Carbon\Carbon;
  Carbon::setLocale('id');
@endphp

@if(($visits ?? collect())->count() === 0)
  <div class="alert alert-warning mb-0">
    Belum ada riwayat kunjungan untuk pasien ini.
  </div>
@else
  <div class="mb-3">
    <div class="fw-bold">5 Kunjungan Terakhir</div>
    <div class="text-muted" style="font-size:13px">
      Data ditarik dari antrian + diagnosa/resep (jika sudah diisi).
    </div>
  </div>

  <div class="accordion" id="accRiwayat">
    @foreach($visits as $i => $v)
      @php
        $tgl = !empty($v->tanggal_antrian) ? Carbon::parse($v->tanggal_antrian)->translatedFormat('d F Y') : '-';
        $status = strtolower(trim((string)($v->status ?? '')));
        if ($status === '') $status = ((int)($v->is_call ?? 0) === 1) ? 'dipanggil' : 'menunggu';
        if ($status === 'lewat') $status = 'dilewati';

        // Ambil rekam medik kalau relasinya ada
        $rm = null;
        if (method_exists($v, 'rekamMedik')) $rm = $v->rekamMedik;

        $diagnosa = $rm->diagnosa ?? null;
        $catatan  = $rm->catatan ?? null;
        $resep    = $rm->resep ?? null;
      @endphp

      <div class="accordion-item" style="border-radius:14px; overflow:hidden; border:1px solid #e9ecef; margin-bottom:10px;">
        <h2 class="accordion-header" id="h{{ $i }}">
          <button class="accordion-button {{ $i ? 'collapsed' : '' }}" type="button"
                  data-bs-toggle="collapse" data-bs-target="#c{{ $i }}"
                  aria-expanded="{{ $i ? 'false' : 'true' }}" aria-controls="c{{ $i }}">
            <div class="d-flex flex-wrap gap-2 align-items-center w-100">
              <span class="fw-bold">{{ $v->no_antrian ?? '-' }} • {{ $v->nama ?? '-' }}</span>
              <span class="text-muted" style="font-size:13px;">• {{ $tgl }}</span>
              <span class="badge bg-light text-dark" style="border:1px solid #e9ecef;">
                POLI {{ strtoupper($v->poli ?? '-') }}
              </span>
              <span class="badge
                @if($status==='selesai') bg-success
                @elseif($status==='dilayani') bg-primary
                @elseif($status==='dipanggil') bg-info text-dark
                @elseif($status==='dilewati') bg-danger
                @else bg-warning text-dark
                @endif
              ">
                {{ ucfirst($status) }}
              </span>
            </div>
          </button>
        </h2>

        <div id="c{{ $i }}" class="accordion-collapse collapse {{ $i ? '' : 'show' }}"
             aria-labelledby="h{{ $i }}" data-bs-parent="#accRiwayat">
          <div class="accordion-body">

            <div class="row g-3">
              <div class="col-md-4">
                <div class="fw-bold mb-1">Diagnosa</div>
                <div class="p-3 rounded" style="background:#f8fafc; border:1px solid #e9ecef; min-height:74px;">
                  {{ $diagnosa ?: 'Belum ada diagnosa.' }}
                </div>
              </div>

              <div class="col-md-4">
                <div class="fw-bold mb-1">Catatan</div>
                <div class="p-3 rounded" style="background:#f8fafc; border:1px solid #e9ecef; min-height:74px;">
                  {{ $catatan ?: 'Tidak ada catatan.' }}
                </div>
              </div>

              <div class="col-md-4">
                <div class="fw-bold mb-1">Obat / Resep</div>
                <div class="p-3 rounded" style="background:#f8fafc; border:1px solid #e9ecef; min-height:74px;">
                  {{ $resep ?: 'Tidak ada resep.' }}
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    @endforeach
  </div>
@endif

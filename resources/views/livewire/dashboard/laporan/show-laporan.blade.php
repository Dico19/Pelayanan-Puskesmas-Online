<div>
    <div class="container">
        <div class="card mt-3 shadow-sm">
            <div class="card-body">

                {{-- JUDUL --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="card-title mb-0">Laporan Antrian</h5>
                </div>

                {{-- REKAP & EXPORT (ATAS) --}}
                <div class="border rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="fw-semibold">Rekap & Export</div>
                            <div class="text-muted small">
                                Rekap gabungan (antrians + riwayat) — ikut filter rekap di bawah.
                            </div>
                        </div>

                       @php
    $rekapParams = [
        'rekap_tipe' => $rekap_tipe,
        'rekap_from' => $rekap_from,
        'rekap_to'   => $rekap_to,
        'rekap_poli' => $rekap_poli,
    ];
@endphp

<div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-danger"
       target="_blank"
       href="{{ route('admin.rekap.pdf', $rekapParams) }}">
        <i class="bi bi-filetype-pdf me-1"></i> Export Rekap PDF
    </a>

    <a class="btn btn-success"
       href="{{ route('admin.rekap.excel', $rekapParams) }}">
        <i class="bi bi-file-earmark-excel me-1"></i> Export Rekap Excel
    </a>
</div>

                    </div>

                    <div class="row g-2 mt-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Tipe Rekap</label>
                            <select class="form-select" wire:model="rekap_tipe">
                                <option value="today">Hari ini</option>
                                <option value="week">Minggu ini</option>
                                <option value="month">Bulan ini</option>
                                <option value="custom">Range Tanggal</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small mb-1">Dari Tanggal</label>
                            <input type="date"
                                   class="form-control"
                                   wire:model="rekap_from"
                                   @disabled($rekap_tipe !== 'custom')>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small mb-1">Sampai Tanggal</label>
                            <input type="date"
                                   class="form-control"
                                   wire:model="rekap_to"
                                   @disabled($rekap_tipe !== 'custom')>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small mb-1">Poli (Opsional)</label>
                            <select class="form-select" wire:model="rekap_poli">
                                <option value="">Semua Poli</option>
                                <option value="umum">Poli Umum</option>
                                <option value="gigi">Poli Gigi</option>
                                <option value="tht">Poli THT</option>
                                <option value="lansia & disabilitas">Lansia & Disabilitas</option>
                                <option value="balita">Balita</option>
                                <option value="kia & kb">KIA & KB</option>
                                <option value="nifas/pnc">Nifas / PNC</option>
                            </select>
                        </div>

                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-outline-secondary" wire:click="resetRekap">
                                Reset Rekap
                            </button>
                        </div>

                        <div class="col-12">
                            <div class="small text-muted mt-1">
                                Filter aktif:
                                <strong>{{ $rekap_from }}</strong> s/d <strong>{{ $rekap_to }}</strong>,
                                Poli: <strong>{{ $rekap_poli ? strtoupper($rekap_poli) : 'SEMUA' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FILTER TABEL (Bawah) --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select wire:model="tanggal_antrian" class="form-select">
                            <option value="">Semua Tanggal</option>
                            <option value="today">Hari ini</option>
                            <option value="week">Minggu ini</option>
                            <option value="month">Bulan ini</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select wire:model="poli" class="form-select">
                            <option value="">Semua Poli</option>
                            <option value="umum">Poli Umum</option>
                            <option value="gigi">Poli Gigi</option>
                            <option value="tht">Poli THT</option>
                            <option value="lansia & disabilitas">Lansia & Disabilitas</option>
                            <option value="balita">Balita</option>
                            <option value="kia & kb">KIA & KB</option>
                            <option value="nifas/pnc">Nifas / PNC</option>
                        </select>
                    </div>

                    <div class="col">
                        <input wire:model.debounce.500ms="search"
                               type="search"
                               class="form-control"
                               placeholder="Cari Nama / NIK...">
                    </div>
                </div>

                {{-- TABEL --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th>No Antrian</th>
                                <th>Nama</th>
                                <th>Alamat</th>
                                <th>Jenis Kelamin</th>
                                <th>No HP</th>
                                <th>No KTP</th>
                                <th>Tgl Lahir</th>
                                <th>Pekerjaan</th>
                                <th>Poli</th>
                                <th>Tgl Antrian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($laporan as $list)
                                <tr class="text-center">
                                    <td>{{ $list->no_antrian }}</td>
                                    <td>{{ $list->nama }}</td>
                                    <td>{{ $list->alamat }}</td>
                                    <td>{{ $list->jenis_kelamin }}</td>
                                    <td>{{ $list->no_hp }}</td>
                                    <td>{{ $list->no_ktp }}</td>
                                    <td>{{ $list->tgl_lahir ? \Illuminate\Support\Carbon::parse($list->tgl_lahir)->format('d-m-Y') : '-' }}</td>
                                    <td>{{ $list->pekerjaan }}</td>
                                    <td>{{ strtoupper($list->poli) }}</td>
                                    <td>{{ $list->tanggal_antrian ? \Illuminate\Support\Carbon::parse($list->tanggal_antrian)->format('Y-m-d') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        Data laporan belum ada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="mt-3">
                    {{ $laporan->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>
</div>

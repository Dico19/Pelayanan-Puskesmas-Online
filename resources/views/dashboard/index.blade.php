@extends('dashboard.layouts.main')

@section('content')
<div class="container">

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mt-3" style="min-height: 800px">
        <div class="card-body">
            <div class="card-title">Dashboard</div>

            {{-- Welcome --}}
            <div class="col-xxl col-xl-12">
                <div class="card info-card customers-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            Selamat Datang <span>| {{ auth()->user()->name }}</span>
                        </h5>
                        <div class="d-flex align-items-center">
                            <h2 class="mb-0">PELAYANAN ONLINE PUSKESMAS KALIGANDU</h2>
                        </div>
                        <div class="text-muted mt-2">
                            Super Admin hanya mengelola data & laporan. Pengelolaan antrian poli dilakukan oleh akun dokter masing-masing.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ringkasan cepat (mirip dashboard dokter) --}}
            <div class="row g-3 mt-1">
                <div class="col-xxl-3 col-md-6">
                    <div class="card info-card sales-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Pasien</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="ps-3">
                                    <h6 class="mb-0">{{ $totalPasien ?? 0 }}</h6>
                                    <small class="text-muted">Semua data pasien</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6">
                    <div class="card info-card revenue-card">
                        <div class="card-body">
                            <h5 class="card-title">Antrian Hari Ini</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-list-check"></i>
                                </div>
                                <div class="ps-3">
                                    <h6 class="mb-0">{{ $totalAntrianToday ?? 0 }}</h6>
                                    <small class="text-muted">Semua poli</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6">
                    <div class="card info-card customers-card">
                        <div class="card-body">
                            <h5 class="card-title">Sudah Dipanggil</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-telephone-outbound"></i>
                                </div>
                                <div class="ps-3">
                                    <h6 class="mb-0">{{ $calledToday ?? 0 }}</h6>
                                    <small class="text-muted">Hari ini</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6">
                    <div class="card info-card sales-card">
                        <div class="card-body">
                            <h5 class="card-title">Sisa Hari Ini</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <div class="ps-3">
                                    <h6 class="mb-0">{{ $remainingToday ?? 0 }}</h6>
                                    <small class="text-muted">Belum dipanggil</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tautan cepat --}}
            <div class="row g-3 mt-1">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-2">Tautan Cepat</h5>

                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.pasien.index') }}" class="btn btn-primary">
                                    <i class="bi bi-person-vcard me-1"></i> Data Pasien
                                </a>

                                <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-file-earmark-text me-1"></i> Laporan
                                </a>

                                <a href="{{ route('admin.analytics') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-graph-up me-1"></i> Analitik
                                </a>

                                {{-- ✅ Tambahan Audit Log --}}
                                <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-shield-check me-1"></i> Audit Log
                                </a>

                                <a href="{{ route('tv.antrian') }}" class="btn btn-outline-secondary" target="_blank">
                                    <i class="bi bi-tv me-1"></i> TV Antrian
                                </a>
                            </div>

                            <div class="text-muted mt-3">
                                Tips: Kalau ingin melihat daftar antrian poli, login menggunakan akun dokter sesuai poli.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ringkasan poli (read-only, tanpa link) --}}
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-2">Ringkasan Poli (Hari Ini)</h5>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Poli</th>
                                            <th class="text-center">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $p = $perPoliToday ?? []; @endphp

                                        <tr><td>Poli Umum</td><td class="text-center">{{ $p['umum'] ?? 0 }}</td></tr>
                                        <tr><td>Poli Gigi</td><td class="text-center">{{ $p['gigi'] ?? 0 }}</td></tr>
                                        <tr><td>Poli THT</td><td class="text-center">{{ $p['tht'] ?? 0 }}</td></tr>
                                        <tr><td>Poli Lansia & Disabilitas</td><td class="text-center">{{ $p['lansia_disabilitas'] ?? 0 }}</td></tr>
                                        <tr><td>Poli Balita</td><td class="text-center">{{ $p['balita'] ?? 0 }}</td></tr>
                                        <tr><td>Poli KIA & KB</td><td class="text-center">{{ $p['kia_kb'] ?? 0 }}</td></tr>
                                        <tr><td>Poli Nifas/PNC</td><td class="text-center">{{ $p['nifas_pnc'] ?? 0 }}</td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <small class="text-muted d-block mt-2">
                                Ini hanya ringkasan. Aksi panggil/kelola dilakukan di dashboard dokter.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

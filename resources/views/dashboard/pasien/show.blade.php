@extends('dashboard.layouts.main')

@section('content')
<div class="container">
    <div class="card mt-3 mb-4">
        <div class="card-body">

            {{-- HEADER: Judul kiri + tombol kanan --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <h5 class="card-title mb-0">
                    Riwayat Pasien: {{ $patient->nama }}
                </h5>

                {{-- Tombol unblock --}}
                <form method="POST" action="{{ route('admin.pasien.unblock', $patient->id) }}" class="d-flex align-items-center gap-2">
                    @csrf
                    <input type="hidden" name="reason" value="Reset manual oleh admin">

                    <button
                        type="submit"
                        class="btn btn-sm {{ $isBlocked ? 'btn-success' : 'btn-secondary' }}"
                        @disabled(!$isBlocked)
                        onclick="return confirm('Buka blokir NIK pasien ini?')"
                        style="min-width: 140px;"
                        title="{{ $isBlocked ? 'Klik untuk membuka blokir' : 'Pasien tidak diblokir' }}"
                    >
                        {{ $isBlocked ? 'Buka Blokir NIK' : 'Tidak Diblokir' }}
                    </button>
                </form>
            </div>

            {{-- Hint kecil di kanan --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    @if($isBlocked)
                        <span class="badge bg-danger">
                            DIBLOKIR (tidak hadir: {{ $absentCount }})
                        </span>
                    @else
                        <span class="badge bg-success">
                            AMAN (tidak hadir: {{ $absentCount }})
                        </span>
                    @endif
                </div>
            </div>

            {{-- Flash message --}}
            @if(session('success'))
                <div class="alert alert-success py-2">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger py-2">{{ session('error') }}</div>
            @endif

            <p><strong>NIK:</strong> {{ $patient->no_ktp }}</p>
            <p><strong>No HP:</strong> {{ $patient->no_hp }}</p>
            <p><strong>Jenis Kelamin:</strong> {{ $patient->jenis_kelamin }}</p>
            <p><strong>Tgl Lahir:</strong> {{ $patient->tgl_lahir }}</p>

            <hr>

            <h6 class="mb-3">Riwayat Kunjungan</h6>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr class="text-center">
                            <th>No Antrian</th>
                            <th>Poli</th>
                            <th>Tgl Antrian</th>
                            <th>Pekerjaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($riwayat as $antrian)
                            <tr class="text-center">
                                <td>{{ $antrian->no_antrian }}</td>
                                <td>{{ strtoupper($antrian->poli) }}</td>
                                <td>{{ $antrian->tanggal_antrian }}</td>
                                <td>{{ $antrian->pekerjaan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Belum ada riwayat kunjungan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <a href="{{ route('admin.pasien.index') }}" class="btn btn-secondary btn-sm mt-3">
                &laquo; Kembali ke Data Pasien
            </a>
        </div>
    </div>
</div>
@endsection

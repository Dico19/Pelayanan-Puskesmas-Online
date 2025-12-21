@extends('layouts.main')

@section('title', 'Antrianku')

@section('content')
<section id="antrian" class="pk-antrianku">
    <div class="container">

        {{-- JUDUL HALAMAN --}}
        <div class="text-center mb-4">
            <h2 class="pk-antrianku__title text-uppercase">
                ANTRIANKU
            </h2>
            <p class="pk-antrianku__subtitle">
                Cek, ubah, atau hapus antrian Anda dengan memasukkan NIK (No KTP)
                yang digunakan saat pendaftaran.
            </p>

            {{-- TOMBOL AMBIL ANTRIAN BARU --}}
            <div class="d-flex justify-content-center">
                <a href="{{ url('/antrian') }}"
                   class="btn btn-primary d-inline-flex align-items-center px-4 py-2 rounded-pill pk-antrianku__cta">
                    <i class="bi bi-clipboard-plus me-2"></i>
                    Ambil Antrian Baru
                </a>
            </div>
        </div>

        {{-- CARD FORM CARI NIK --}}
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">

                <div class="pk-antrianku__card">
                    <div class="pk-antrianku__cardBody">

                        {{-- ALERT JIKA NIK TIDAK DITEMUKAN --}}
                        @if (session('nik_not_found'))
                            <div class="alert alert-warning mb-4" role="alert">
                                {{ session('nik_not_found') }}
                            </div>
                        @endif

                        {{-- HEADER KECIL DI DALAM CARD --}}
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <div class="pk-antrianku__icon">
                                <i class="bi bi-search"></i>
                            </div>
                            <div>
                                <div class="pk-antrianku__cardTitle">Cari Antrian Anda</div>
                                <div class="pk-antrianku__cardSub">
                                    Masukkan NIK (No KTP) yang digunakan saat mengambil nomor antrian.
                                </div>
                            </div>
                        </div>

                        {{-- FORM --}}
                        <form action="{{ route('antrian.cari.proses') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="no_ktp" class="form-label pk-antrianku__label fw-semibold">
                                    NIK (No KTP)
                                </label>

                                <input
                                    type="text"
                                    name="no_ktp"
                                    id="no_ktp"
                                    class="form-control form-control-lg pk-antrianku__input @error('no_ktp') is-invalid @enderror"
                                    placeholder="3273xxxxxxxxxxxx"
                                    value="{{ old('no_ktp') }}"
                                >

                                @error('no_ktp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit"
                                        class="btn btn-primary px-4 py-2 d-inline-flex align-items-center rounded-pill pk-antrianku__btn">
                                    <i class="bi bi-search me-2"></i>
                                    Cari Antrian
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>

    </div>
</section>
@endsection

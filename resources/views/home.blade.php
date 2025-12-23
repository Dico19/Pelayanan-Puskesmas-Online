@extends('layouts.main')

@section('body-class', 'page-home')
@section('title', 'Puskesmas Kaligandu - Antrian Online')

@push('styles')
    <link href="{{ asset('assets/css/pk-home.css') }}?v={{ filemtime(public_path('assets/css/pk-home.css')) }}" rel="stylesheet">
@endpush

@section('content')
    <!-- ======= Hero Section ======= -->
    <section id="hero" class="pk-hero d-flex align-items-center" data-aos="fade-up">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 col-xl-6 hero-text-wrapper" data-aos="fade-right" data-aos-delay="150">

                    <div class="pk-hero-badge" data-aos="zoom-in" data-aos-delay="250">
                        <span>✔</span> Pelayanan Antrian Online Puskesmas
                    </div>

                    <h1 class="pk-hero-title mb-3" data-aos="fade-right" data-aos-delay="300">
                        Selamat Datang di Puskesmas Kaligandu
                    </h1>

                    <p class="pk-hero-subtitle mb-4" data-aos="fade-right" data-aos-delay="400">
                        Daftar antrian secara online, pilih poliklinik, dan kurangi waktu tunggu di lokasi.
                    </p>

                    <div class="d-flex flex-wrap gap-2 mb-3" data-aos="fade-up" data-aos-delay="500">
                        <a href="{{ url('/antrian') }}" class="btn btn-primary pk-btn-primary">
                            <i class="bi bi-ticket-perforated me-1"></i> Ambil Antrian
                        </a>
                        <a href="#why-us" class="btn pk-btn-ghost">
                            <i class="bi bi-grid-3x3-gap me-1"></i> Lihat Layanan
                        </a>
                    </div>

                    <div class="pk-hero-stats" data-aos="zoom-in" data-aos-delay="550">
                        <div class="pk-hero-stat">
                            <div class="pk-hero-stat-number">25</div>
                            <div class="pk-hero-stat-label">Dokter &amp; Bidan</div>
                        </div>
                        <div class="pk-hero-stat">
                            <div class="pk-hero-stat-number">7</div>
                            <div class="pk-hero-stat-label">Layanan Poli</div>
                        </div>
                        <div class="pk-hero-stat">
                            <div class="pk-hero-stat-number">4</div>
                            <div class="pk-hero-stat-label">Unit Laboratorium</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
    <!-- End Hero -->

    <!-- ======= Why Us Section ======= -->
    <section id="why-us" class="why-us pk-why">
        <div class="container">

            <div class="row">
                <div class="col-lg-4 d-flex align-items-stretch" data-aos="fade-right">
                    <div class="content pk-why-card">
                        <h3>Sistem Antrian Online</h3>
                        <p>
                            Sistem Antrian Online Puskesmas Kaligandu membantu pengunjung mengambil antrian
                            sesuai poliklinik dari rumah.
                        </p>
                        <div class="text-center">
                            <a href="{{ url('/antrian') }}" class="more-btn">
                                Ambil Antrian <i class="bx bx-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 d-flex align-items-stretch" data-aos="fade-left">
                    <div class="icon-boxes d-flex flex-column justify-content-center">
                        <div class="row">

                            <div class="col-xl-4 d-flex align-items-stretch">
                                <div class="icon-box mt-4 mt-xl-0" data-aos="zoom-in" data-aos-delay="100">
                                    <i class="bx bx-plus-medical"></i>
                                    <h4>Poli Umum</h4>
                                    <p>Pelayanan pemeriksaan kesehatan, pengobatan, dan edukasi.</p>
                                </div>
                            </div>

                            <div class="col-xl-4 d-flex align-items-stretch">
                                <div class="icon-box mt-4 mt-xl-0" data-aos="zoom-in" data-aos-delay="200">
                                    <i class="bx bx-dna"></i>
                                    <h4>Poli Gigi</h4>
                                    <p>Pemeriksaan kesehatan gigi dan mulut, tindakan medis dasar, dan edukasi.</p>
                                </div>
                            </div>

                            <div class="col-xl-4 d-flex align-items-stretch">
                                <div class="icon-box mt-4 mt-xl-0" data-aos="zoom-in" data-aos-delay="300">
                                    <i class="bx bxs-first-aid"></i>
                                    <h4>Poli THT</h4>
                                    <p>Pemeriksaan serta pengobatan Telinga, Hidung, dan Tenggorokan.</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 2 -->
            <div class="row mt-4" data-aos="fade-up">
                <div class="col-lg-12 d-flex align-items-stretch">
                    <div class="icon-boxes d-flex flex-column justify-content-center">
                        <div class="row">

                            <div class="col-xl-3 d-flex align-items-stretch">
                                <div class="icon-box" data-aos="zoom-in" data-aos-delay="100">
                                    <i class="bx bx-bed"></i>
                                    <h4>Poli Lansia &amp; Disabilitas</h4>
                                    <p>Pelayanan khusus lansia dan individu dengan disabilitas.</p>
                                </div>
                            </div>

                            <div class="col-xl-3 d-flex align-items-stretch">
                                <div class="icon-box" data-aos="zoom-in" data-aos-delay="200">
                                    <i class="bx bx-child"></i>
                                    <h4>Poli Balita</h4>
                                    <p>Pelayanan rutin, imunisasi, dan pemeriksaan kesehatan balita.</p>
                                </div>
                            </div>

                            <div class="col-xl-3 d-flex align-items-stretch">
                                <div class="icon-box" data-aos="zoom-in" data-aos-delay="300">
                                    <i class="bx bxs-shield-plus"></i>
                                    <h4>Poli KIA &amp; KB</h4>
                                    <p>Kehamilan, persalinan, perawatan ibu dan layanan KB.</p>
                                </div>
                            </div>

                            <div class="col-xl-3 d-flex align-items-stretch">
                                <div class="icon-box" data-aos="zoom-in" data-aos-delay="400">
                                    <i class="bx bxs-clinic"></i>
                                    <h4>Poli Nifas / PNC</h4>
                                    <p>Perawatan kesehatan ibu setelah melahirkan.</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!-- End Why Us Section -->

    <!-- ======= Counts Section ======= -->
    <section id="counts" class="counts">
        <div class="container">
            <div class="row">

                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="count-box">
                        <i class="fas fa-user-md"></i>
                        {{-- Angka pasti tampil --}}
                        <span data-purecounter-start="0"
                              data-purecounter-end="25"
                              data-purecounter-duration="1"
                              class="purecounter">25</span>
                        <p>Dokter &amp; Bidan</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mt-5 mt-md-0" data-aos="fade-up" data-aos-delay="200">
                    <div class="count-box">
                        <i class="far fa-hospital"></i>
                        <span data-purecounter-start="0"
                              data-purecounter-end="7"
                              data-purecounter-duration="1"
                              class="purecounter">7</span>
                        <p>Poliklinik</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mt-5 mt-lg-0" data-aos="fade-up" data-aos-delay="300">
                    <div class="count-box">
                        <i class="fas fa-flask"></i>
                        <span data-purecounter-start="0"
                              data-purecounter-end="4"
                              data-purecounter-duration="1"
                              class="purecounter">4</span>
                        <p>Laboratorium Penelitian</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mt-5 mt-lg-0" data-aos="fade-up" data-aos-delay="400">
                    <div class="count-box">
                        <i class="fas fa-award"></i>
                        <span data-purecounter-start="0"
                              data-purecounter-end="125"
                              data-purecounter-duration="1"
                              class="purecounter">125</span>
                        <p>Penghargaan</p>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- End Counts Section -->

    <!-- ======= Testimonials Section (SWIPER) ======= -->
    <section id="testimonials" class="pk-testimonials py-5">
        <div class="container" data-aos="fade-up">

            <div class="section-title text-center mb-4">
                <h2>Ulasan Pasien</h2>
                <p class="text-muted mb-0">Pendapat pasien yang pernah menggunakan layanan Puskesmas Kaligandu</p>
            </div>

            <div class="swiper pk-testimonials-swiper" data-aos="zoom-in" data-aos-delay="150">
                <div class="swiper-wrapper">

                    <div class="swiper-slide">
                        <div class="pk-testimonial-card">
                            <div class="pk-testimonial-top">
                                <img src="{{ asset('assets/img/patient-1.jpg') }}" class="pk-testimonial-avatar" alt="Ical Sopan">
                                <div class="pk-testimonial-meta">
                                    <div class="pk-testimonial-name">Ical Sopan</div>
                                    <div class="pk-testimonial-role">Pasien Poli Umum</div>
                                    <div class="pk-testimonial-stars">
                                        <i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star-half"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="pk-testimonial-quote">
                                “Pelayanannya sangat cepat dan ramah. Proses antrian jadi jauh lebih mudah.”
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <div class="pk-testimonial-card">
                            <div class="pk-testimonial-top">
                                <img src="{{ asset('assets/img/patient-2.jpg') }}" class="pk-testimonial-avatar" alt="Elzan Pertaruhan">
                                <div class="pk-testimonial-meta">
                                    <div class="pk-testimonial-name">Elzan Pertaruhan</div>
                                    <div class="pk-testimonial-role">Pasien Poli Gigi</div>
                                    <div class="pk-testimonial-stars">
                                        <i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="pk-testimonial-quote">
                                “Antriannya rapi, dokter dan perawat sangat membantu. Recommended!”
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <div class="pk-testimonial-card">
                            <div class="pk-testimonial-top">
                                <img src="{{ asset('assets/img/patient-3.jpg') }}" class="pk-testimonial-avatar" alt="Ara Garpu">
                                <div class="pk-testimonial-meta">
                                    <div class="pk-testimonial-name">Ara Garpu</div>
                                    <div class="pk-testimonial-role">Pasien Poli KIA &amp; KB</div>
                                    <div class="pk-testimonial-stars">
                                        <i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star-half"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="pk-testimonial-quote">
                                “Fasilitas bersih dan nyaman. Sistem antri online sangat membantu.”
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <div class="pk-testimonial-card">
                            <div class="pk-testimonial-top">
                                <img src="{{ asset('assets/img/patient-4.jpg') }}" class="pk-testimonial-avatar" alt="Romo Botak">
                                <div class="pk-testimonial-meta">
                                    <div class="pk-testimonial-name">Romo Botak</div>
                                    <div class="pk-testimonial-role">Pasien Poli Lansia &amp; Disabilitas</div>
                                    <div class="pk-testimonial-stars">
                                        <i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star-half"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="pk-testimonial-quote">
                                “Pelayanan untuk lansia sangat ramah dan penuh perhatian.”
                            </div>
                        </div>
                    </div>

                    <div class="swiper-slide">
                        <div class="pk-testimonial-card">
                            <div class="pk-testimonial-top">
                                <img src="{{ asset('assets/img/patient-5.jpg') }}" class="pk-testimonial-avatar" alt="Wulan Yapit">
                                <div class="pk-testimonial-meta">
                                    <div class="pk-testimonial-name">Wulan Yapit</div>
                                    <div class="pk-testimonial-role">Pasien Poli Balita</div>
                                    <div class="pk-testimonial-stars">
                                        <i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star"></i><i class="bx bxs-star"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="pk-testimonial-quote">
                                “Dokternya baik dan ramah, ruang tunggu nyaman untuk anak-anak.”
                            </div>
                        </div>
                    </div>

                </div>

                <div class="swiper-pagination pk-testimonials-pagination"></div>

                <div class="pk-swiper-nav">
                    <button class="pk-swiper-btn pk-prev" type="button" aria-label="Sebelumnya">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="pk-swiper-btn pk-next" type="button" aria-label="Berikutnya">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>

        </div>
    </section>
    <!-- End Testimonials Section -->

    <!-- ======= Contact Section ======= -->
    <section id="contact" class="contact">
        <div class="container" data-aos="fade-up">
            <div class="section-title">
                <h2>Contact</h2>
            </div>
        </div>

        <div class="container">
            <div class="row mt-5">

                <div class="col-lg-4" data-aos="fade-right" data-aos-delay="150">
                    <div class="info">
                        <div class="address">
                            <i class="bi bi-geo-alt"></i>
                            <h4>Location:</h4>
                            <p>Kaligandu Street, Serang, SRG 535022</p>
                        </div>

                        <div class="email">
                            <i class="bi bi-envelope"></i>
                            <h4>Email:</h4>
                            <p>puskesmaskaligandu@gmail.com</p>
                        </div>

                        <div class="phone">
                            <i class="bi bi-phone"></i>
                            <h4>Call:</h4>
                            <p>+62 8954 0490 5070</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 mt-5 mt-lg-0" data-aos="fade-left" data-aos-delay="200">

                    @if(session('success'))
                        <div class="alert alert-success mt-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('contact.send') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                            </div>

                            <div class="col-md-6 form-group mt-3 mt-md-0">
                                <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                        </div>

                        <div class="form-group mt-3">
                            <textarea name="message" rows="5" class="form-control" placeholder="Message" required></textarea>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary pk-btn-primary">Kirim Pesan</button>
                        </div>

                    </form>

                </div>

            </div>
        </div>
    </section>
    <!-- End Contact Section -->
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
  if (typeof Swiper === "undefined") return;

  new Swiper(".pk-testimonials-swiper", {
    loop: true,
    speed: 650,
    grabCursor: true,
    spaceBetween: 18,
    slidesPerView: 1,
    centeredSlides: false,

    autoplay: {
      delay: 4500,
      disableOnInteraction: false,
    },

    pagination: {
      el: ".pk-testimonials-pagination",
      clickable: true,
    },

    navigation: {
      nextEl: ".pk-next",
      prevEl: ".pk-prev",
    },

    breakpoints: {
      576: { slidesPerView: 1 },
      768: { slidesPerView: 2 },
      992: { slidesPerView: 3 },
    },
  });
});
</script>
@endpush

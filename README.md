# Puskesmas Kaligandu — Sistem Antrian & Layanan Online

Aplikasi web untuk **antrian online Puskesmas** yang membantu pasien mengambil nomor antrian berdasarkan poli & tanggal, serta membantu petugas/dokter mengelola antrian, status layanan, rekam medis, laporan, dan audit aktivitas.

---

## Ringkasan
Project ini terdiri dari 3 area utama:
1. **Halaman Publik (Pasien)**: ambil antrian, lihat daftar antrian (dengan privasi data).
2. **Staff/Admin**: dashboard, data pasien, laporan, analitik, audit log.
3. **Dokter per Poli**: dashboard dokter, kelola antrian, diagnosa/rekam medis, riwayat.

---

## Fitur Utama

### 1) Pasien (Publik)
- **Ambil Antrian Online** berdasarkan:
  - Poli (Umum, Gigi, THT, Balita, KIA & KB, Nifas/PNC, Lansia & Disabilitas)
  - Tanggal layanan (mendukung validasi hari libur/Minggu bila diterapkan)
- **Validasi Input Pasien**
  - NIK 16 digit, No HP 10–15 digit, dsb.
- **Anti Duplikat Antrian**
  - NIK tidak bisa ambil antrian lagi jika masih punya antrian **aktif** pada tanggal yang sama.
- **Blokir Otomatis NIK**
  - Jika tercatat **3× tidak hadir** (threshold dapat disesuaikan).
  - Mendukung reset/unblock oleh admin (jika tabel reset digunakan).
- **Tampilan Daftar Antrian Publik**
  - Mendukung kebijakan privasi: tampilkan data seperlunya / sensor data sensitif.

---

### 2) Staff / Admin
- **Login Staff** dengan tampilan modern + toggle show/hide password.
- **Dashboard Admin/Staff**
- **Data Pasien**
- **Laporan**
  - Mendukung **Export laporan PDF/Excel** (sudah ada di project).
- **Analitik**
- **Audit Log (Super Admin)**
  - Pencatatan aktivitas penting sistem (akses khusus).

---

### 3) Dokter (Per Poli)
- **Dashboard Dokter Per Poli**
- **Kelola Antrian**
  - Panggil
  - Panggil ulang
  - Mulai / Layani
  - Selesai
  - Lewatkan
  - Tidak hadir
  - Riwayat pasien
- **Rekam Medik / Diagnosa**
  - Diagnosa hanya aktif setelah pasien **dipanggil** (validasi server-side).
  - Akses poli dibatasi: dokter hanya dapat mengelola antrian sesuai poli-nya.
- **Riwayat Rekam Medik**
  - Modal (AJAX) + halaman riwayat per pasien.
- **Log Aktivitas Dokter**
  - Aktivitas seperti aksi **panggil/selesai/diagnosa** sudah tercatat (sudah ada di project).

---

## Aturan Bisnis (Business Rules)

### Status Antrian Aktif
Antrian dianggap **aktif** jika status termasuk:
- `menunggu`
- `dipanggil`
- `dilayani`
- `dilewati`

Status yang **tidak aktif**:
- `selesai`
- `tidak_hadir`

### Akses Diagnosa / Rekam Medik
Dokter dapat menyimpan diagnosa jika:
- Pasien sudah dipanggil (`is_call = 1`)
- Poli antrian sesuai poli dokter (akses dibatasi per poli)

### Blokir NIK
- Jika NIK tercatat **3× tidak hadir**, pasien akan diblokir sementara untuk ambil antrian.
- Admin dapat melakukan unblock/reset (jika modul reset diaktifkan).

---

## Privasi Data & Etika Tampilan Publik
Karena halaman publik dapat dilihat umum, disarankan:
- **Minimalisasi data sensitif** yang ditampilkan (alamat, pekerjaan, tanggal lahir).
- **Sensor NIK / No HP** jika perlu sebagai verifikasi pasien:
  - Contoh: tampilkan beberapa digit awal + beberapa digit akhir (bukan full).
- Pastikan data lengkap hanya terlihat oleh staff/dokter yang login.

---

## Role & Akses
- **Super Admin**
  - Semua akses admin + Audit Log.
- **Dokter (per poli)**
  - Kelola antrian poli masing-masing + rekam medis.

---

## Modul / Komponen (Ringkas)
- **Antrian**
  - Ambil antrian, filter poli, pagination, validasi anti duplikat, blokir NIK.
- **Dokter**
  - Kelola antrian per poli, status pasien aktif, tindakan layanan.
- **Rekam Medik**
  - Diagnosa, catatan, resep, riwayat pasien.
- **Laporan**
  - Rekap data + **export PDF/Excel**.
- **Audit & Log Aktivitas**
  - Audit log sistem dan log aktivitas dokter.

---

## Saran Pengembangan Lanjutan (Opsional)
- Notifikasi WhatsApp/SMS saat nomor dipanggil.
- QR Code/Kode booking untuk validasi cepat.
- Pengaturan jam layanan per poli (cutoff otomatis).
- Multi loket / multi dokter per poli.
- Penjadwalan dokter + sinkron statistik real-time.
- Backup & monitoring database (mengurangi risiko error XAMPP/MySQL).

---

## Developer
**Dicoding**

---

## Screenshot Halaman dan Fitur
![Halaman-Hero](public/assets/screenshots/Halaman-Hero.png)
![Halaman-Poli](public/assets/screenshots/Halaman-Poli.png)
![Halaman-Ulasan](public/assets/screenshots/Halaman-Ulasan.png)
![Halaman-Contact](public/assets/screenshots/Halaman-Contact.png)
![Pilih-Poli](public/assets/screenshots/Pilih-Poli.png)
![Pilih-Tanggal](public/assets/screenshots/Pilih-Tanggal.png)
![Form-Antrian](public/assets/screenshots/Form-Antrian.png)
![Status-Antrian](public/assets/screenshots/Status-Antrian.png)
![Ambil-Antrian](public/assets/screenshots/Ambil-Antrian.png)
![Antrianku](public/assets/screenshots/Antrianku.png)
![Hasil-Pencarian](public/assets/screenshots/Hasil-Pencarian.png)
![Hasil-Pencarian-NIK](public/assets/screenshots/Hasil-Pencarian-NIK.png)
![Edit-Antrian](public/assets/screenshots/Edit-Antrian.png)
![Cetak-Antrian](public/assets/screenshots/Cetak-Antrian.png)
![Login-Staff](public/assets/screenshots/Login-Staff.png)
![Monitor](public/assets/screenshots/Monitor.png)
![Pasien-Lihat-Diagnosa](public/assets/screenshots/Pasien-Lihat-Diagnosa.png)
![Dashboard-Super-Admin](public/assets/screenshots/Dashboard-Super-Admin.png)
![Data-Pasien-Super-Admin-1](public/assets/screenshots/Data-Pasien-Super-Admin1.png)
![Data-Pasien-Super-Admin-2](public/assets/screenshots/Data-Pasien-Super-Admin-2.png)
![Laporan-Antrian-Super-Admin](public/assets/screenshots/Laporan-Antrian-Super-Admin.png)
![Audit-Log-Super-Admin-1](public/assets/screenshots/Audit-Log-Super-Admin-1.png)
![Audit-Log-Super-Admin-2](public/assets/screenshots/Audit-Log-Super-Admin-2.png)
![Analitik-Super-Admin](public/assets/screenshots/Analitik-Super-Admin.png)
![Dashboard-Dokter](public/assets/screenshots/Dashboard-Dokter.png)
![Daftar-Antrian-Dokter](public/assets/screenshots/Daftar-Antrian-Dokter.png)
![Statistik-Dokter](public/assets/screenshots/Statistik-Dokter.png)
![Riwayat-Dokter-1](public/assets/screenshots/Riwayat-Dokter-1.png)
![Riwayat-Dokter-2](public/assets/screenshots/Riwayat-Dokter-2.png)
![Reset-Antrian](public/assets/screenshots/Reset-Antrian.png)
![Diagnosa](public/assets/screenshots/Diagnosa.png)

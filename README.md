# SPV-Track &mdash; Logistics & Warehouse Monitoring System
### PT. South Pacific Viscose (Member of Lenzing Group)

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/PostgreSQL-14%2B-316192?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL">
  <img src="https://img.shields.io/badge/TailwindCSS-v3-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white" alt="Alpine.js">
  <img src="https://img.shields.io/badge/DomPDF-Enabled-00994D?style=for-the-badge&logo=adobeacrobatreader&logoColor=white" alt="DomPDF">
  <img src="https://img.shields.io/badge/Standard-Lenzing%20AG-00994D?style=for-the-badge" alt="Lenzing Standard">
</p>

---

## 📌 Tentang SPV-Track

**SPV-Track** adalah sistem monitoring logistik pergudangan dan audit proses *staging* / pemuatan (*loading/stuffing*) kontainer untuk produk serat **Viscose Rayon Fiber** dan **Sodium Sulfate** di **PT. South Pacific Viscose (Purwakarta, Indonesia)** yang berstandar mutu **Lenzing AG**.

Aplikasi ini mendigitalkan kepatuhan SOP pemeriksaan fisik kontainer, pengawasan kondisi kemasan bale, pelacakan operator lapangan, verifikasi independen *4-Eyes Principle*, serta pencatatan bukti foto & video berstempel digital (*timestamp & geotag*) secara *real-time*.

---

## ✨ Fitur-Fitur Utama

### 1. 📱 Field App (Aplikasi Operator Lapangan)
* **Formulir Pengiriman Baru (Create Shipment):**
  * Validasi cerdas 9-digit nomor dokumen (*Packing List / Delivery Note, Shipment Group, Shipment Number*).
  * Auto-complete dan saran riwayat pengetikan (*Forwarder, Sopir, Plat Nomor, Kontainer, Nomor Seal*).
  * Penugasan multi-petugas staging lapangan (*operator checklist picker*).
  * Pemilihan lokasi warehouse:
    * **Warehouse Internal SPV:** Dropdown area spesifik (*Gudang Atas, Gudang Tengah, Gudang Bawah*).
    * **Warehouse Eksternal:** Input teks dinamis nama PT pemilik warehouse eksternal dengan *typing history*.
* **Timeline Bukti Pemeriksaan SOP (27 Titik SOP):**
  * Unggah bukti bertahap per titik pemeriksaan sesuai urutan SOP.
  * Dukungan media **Foto (JPG)** dan **Video Singkat (MP4)**.
  * Pratinjau langsung bukti unggahan melalui **Lightbox Modal** (fitur foto ulang / rekam ulang).
  * Stempel digital otomatis (*digital watermark*) memuat ID Shipment, tanggal, jam WIB, dan koordinat GPS.
* **Waktu Pelaksanaan Staging Terintegrasi:**
  * Pengisian **Waktu Mulai** dan **Waktu Selesai** staging format 24 jam dengan tombol cepat *Set Sekarang*.
  * Nilai waktu ini secara otomatis tersinkronisasi ke seluruh formulir checklist kontainer, bale, dan dokumen laporan PDF tanpa perlu pengisian berulang.

---

### 2. 📋 Formulir Inspeksi Standar Mutu Lenzing

#### A. Form Inspeksi Kondisi Kontainer (Halaman 2 Laporan)
Standar pemeriksaan fisik sebelum pemuatan kontainer:
* **Daftar 6 Titik Pemeriksaan Fisik:**
  1. Pintu (Bagian Luar & Dalam, Karet Segel, Engsel)
  2. Dinding Kanan
  3. Dinding Kiri
  4. Dinding Depan
  5. Atap (*Roof*)
  6. Lantai (*Floorboard*)
* **Aturan Kelaikan:** Bebas lubang, karat, rembesan minyak/kotoran, bau menyengat, paku/benda tajam, dan lantai lapuk.
* **Catatan Wajib Tiap Titik:** Setiap titik pemeriksaan memiliki catatan terverifikasi.
* **Kesimpulan Kelaikan:** *Layak*, *Layak Dengan Catatan*, atau *Tidak Layak*.
* Data nomor kontainer, nomor seal, tanggal loading, dan waktu pelaksanaan otomatis terhubung dari data shipment.

#### B. Form Inspeksi Kondisi Bale & 4-Eyes Principle (Halaman 3 Laporan)
Standar kualitas kondisi kemasan bale serat rayon:
* **Kategori Bale & Sticker:**
  * Jenis Bale: *TX ECOVERO™*, *TX Non-ECOVERO™*, *NW (Non-Woven)*.
  * Jenis Sticker (Reaktif): *Tanpa Sticker*, *FSC*, *ISI*, *N*, *O*, *A*.
* **8 Kriteria Pemeriksaan Ketidaksesuaian / Cacat Kemasan:**
  * Sobek, Tambalan, Kotor/Bernoda, Putus Tali Strapping, Pembungkus Plastik Sobek, Serangga/Hama, Rusak Fisik, dan Label Rusak/Cacat.
  * Pencatatan nomor bale reject awal, nomor bale pengganti, dan tindakan perbaikan.
* **Verifikasi 4-Eyes Principle:**
  * **Pemeriksa Pertama (First Eye):** Operator Staging / Loading Lapangan.
  * **Pemeriksa Kedua (Second Eye):** Supervisor / Verifikator Independen dengan status *Disetujui*, *Perlu Perbaikan*, atau *Tidak Disetujui* serta kolom catatan verifikator yang fleksibel.
* **Peralatan Tambahan:** Verifikasi penggunaan *2 Rubber Cone* dan kondisi cuaca saat staging.

---

### 3. 🖥️ Super Admin Dashboard & Pengaturan Sistem
* **Ringkasan Realtime (KPI):** Total pengiriman harian, kontainer dalam proses staging, dokumen disetujui, dan statistik ekspor vs lokal.
* **Manajemen Master 27 Titik SOP:**
  * Tambah, ubah urutan (*reorder* naik/turun), edit panduan deskripsi titik, target kargo (*Export*, *Lokal*, *Keduanya*).
  * Konfigurasi tipe media strictly **Foto (JPG)** atau **Video (MP4)**.
  * Pengaturan status kewajiban: **Wajib** atau **Opsional**.
  * Modal interaktif *viewport-centered* dengan teleportasi Alpine.js bebas hambatan scrolling.
* **Manajemen Pengguna & Hak Akses:** Konfigurasi role berbasis wewenang (*Superadmin, Supervisor, Operator Warehouse, Verifikator*).
* **Master Karyawan:** Direktori petugas lapangan dan nomor induk karyawan (NIK).

---

### 4. 📄 Dokumen Laporan Resmi & Ekspor PDF
Format cetak resmi terstruktur 4 bagian sesuai kebutuhan audit logistik:
* **Halaman 1:** Data Ringkasan Informasi Pengiriman (Shipment, Kargo, Kendaraan, Pengesahan 3 Pihak).
* **Halaman 2:** Formulir Inspeksi Kondisi Kontainer Standar Lenzing.
* **Halaman 3:** Formulir Inspeksi Kondisi Bale (Loading) & Verifikasi 4-Eyes Principle.
* **Halaman 4 dst:** Galeri Bukti Foto Evidence 27 Titik SOP (Lengkap dengan watermark, timestamp WIB, dan label titik).
* **Opsi Output:** *Direct Print Preview (HTML)* dan *Download Dokumen PDF Resmi (.pdf)*.

---

## 🛠️ Arsitektur & Teknologi

| Komponen | Teknologi yang Digunakan |
|---|---|
| **Framework Backend** | [Laravel 11.x](https://laravel.com/) (PHP 8.2+) |
| **Database** | [PostgreSQL](https://www.postgresql.org/) (Port 5432, Driver `pgsql`) |
| **Styling & UI** | [Tailwind CSS](https://tailwindcss.com/) + Custom Glassmorphism & Lenzing Palette |
| **Interaktivitas Frontend** | [Alpine.js 3.x](https://alpinejs.dev/) |
| **Iconography** | [Phosphor Icons](https://phosphoricons.com/) |
| **Engine PDF** | [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) |
| **Grafik & Statistik** | [Chart.js](https://www.chartjs.org/) |
| **Pengujian Otomatis** | PHPUnit (81 Test Cases / 407 Assertions Passed) |

---

## 🚀 Panduan Instalasi Lokal

### 1. Prasyarat Sistem
* PHP >= 8.2 (dengan ekstensi `pdo_pgsql`, `pgsql`, `mbstring`, `gd`, `openssl`, `fileinfo`)
* Composer >= 2.5
* PostgreSQL >= 14+ (Port 5432)
* Node.js & NPM (opsional untuk asset bundling)

### 2. Kloning Repositori
```bash
git clone https://github.com/prihandiva/spv-track-web.git
cd spv-track-web
```

### 3. Instalasi Dependensi PHP
```bash
composer install
```

### 4. Konfigurasi Lingkungan (.env)
Salin berkas konfigurasi `.env.example`:
```bash
cp .env.example .env
php artisan key:generate
```
Sesuaikan koneksi database PostgreSQL pada `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=spv_track
DB_USERNAME=postgres
DB_PASSWORD=root
```

### 5. Migrasi & Seeder Database
Jalankan migrasi tabel beserta seeder titik SOP dan data default:
```bash
php artisan migrate --seed
```

### 6. Tautan Penyimpanan Media (Storage Symlink)
Pastikan direktori publik terhubung ke folder storage untuk berkas foto bukti evidence:
```bash
php artisan storage:link
```

### 7. Menjalankan Server Lokal
```bash
php artisan serve
```
Akses sistem melalui browser pada: **`http://127.0.0.1:8000`**

---

## 🧪 Pengujian Otomatis (Testing)

Proyek ini dilengkapi dengan rangkaian pengujian fitur lengkap (Unit & Feature Test) untuk memvalidasi alur shipment, checklist formulir inspeksi kontainer, formulir bale, sinkronisasi waktu, dan rendering PDF:

```bash
php artisan test
```
*Status Terakhir:* **81 tests passed (407 assertions passed)**.

---

## 👥 Pengguna Default (Seeder)

| Role | Email / Username | Password Default |
|---|---|---|
| **Super Administrator** | `superadmin@spvtrack.com` / `superadmin` | `password` |
| **Operator Lapangan** | `operator@spvtrack.com` / `operator` | `password` |

---

## 📄 Lisensi & Hak Cipta

Proyek ini dikembangkan khusus untuk keperluan operasional pergudangan dan logistik **PT. South Pacific Viscose (Lenzing AG)**. Seluruh hak cipta dan merek dagang dilindungi oleh perusahaan terkait.

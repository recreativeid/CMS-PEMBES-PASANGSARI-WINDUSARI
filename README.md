# PASANG LAP EMAS — CMS & Pelaporan Pemdes Pasangsari Windusari
> **Pelaporan Elaborasi Kepada Masyarakat — Transparansi Kinerja 19 Perangkat Desa**  
> *Pemerintah Desa Pasangsari, Kecamatan Windusari, Kabupaten Magelang, Jawa Tengah*  
> **Repository**: [https://github.com/recreativeid/CMS-PEMBES-PASANGSARI-WINDUSARI.git](https://github.com/recreativeid/CMS-PEMBES-PASANGSARI-WINDUSARI.git)

![Logo Desa Pasangsari](assets/images/logo-desa-pasangsari.jpg)

Aplikasi web modern berbasis **Mobile Verse** (Mobile-First App Experience & Responsive Desktop) dengan arsitektur **Clean Minimalist**, dirancang untuk transparansi publik, pelaporan kinerja harian 19 perangkat desa, serta kotak aspirasi masyarakat yang terhubung ke cloud database serverless **Supabase (PostgreSQL)**.

---

## 📱 Desain "Mobile Verse" & Fitur Unggulan

- 📲 **Mobile Verse Bottom Navigation Bar**: Navigasi bawah responsif mirip aplikasi native (Publik, Aspirasi, Lapor, Admin) pada layar ponsel dengan transisi mulus dan indikator aktif.
- 🌾 **Identitas Resmi Desa Pasangsari**: Dilengkapi lambang logo resmi Desa Pasangsari (Gunung, Pertanian, Cabai, Masjid, dan Sungai).
- ⚡ **Dual-Engine Persistence**: Berjalan langsung secara offline (*LocalStorage* dengan seed data lengkap) dan mendukung sinkronisasi otomatis ke cloud database **Supabase**.
- 🖨️ **Cetak Lembar SPJ & Dokumen LPJ Resmi**: Kop surat resmi Pemerintah Kabupaten Magelang, Kecamatan Windusari, Desa Pasangsari lengkap dengan kolom tanda tangan Kepala Desa & Pelapor.
- 🔍 **Pencarian Cerdas & Filter Fleksibel**: Filter berdasarkan waktu (Hari Ini, Bulan Ini, Tahun Ini, Pilih Tanggal) dan filter 19 Jabatan.
- 📸 **Kompresi Foto Otomatis**: Foto dokumentasi lapangan dikompresi otomatis via HTML5 Canvas (max 1200px, kualitas 82%) sehingga hemat kuota dan tidak pernah gagal upload.

---

## 🔑 Kredensial Login Sistem

### 1. Akun Administrator
- **Username**: `admin`
- **Password**: `windusari2024`

### 2. PIN Login 19 Jabatan Perangkat Desa
| No | Jabatan | PIN | Kategori Wilayah/Tugas |
|---|---|---|---|
| 1 | **KADES** | `1111` | Kepala Desa |
| 2 | **SEKDES** | `2222` | Sekretaris Desa |
| 3 | **KASI PEMERINTAHAN** | `3333` | Seksi Pemerintahan |
| 4 | **KASI KESRA** | `4444` | Seksi Kesejahteraan Rakyat |
| 5 | **KASI PELAYANAN** | `5555` | Seksi Pelayanan |
| 6 | **KAUR KEUANGAN** | `6666` | Urusan Keuangan |
| 7 | **KAUR UMUM DAN PERENCANANAAN** | `7777` | Urusan Umum & Perencanaan |
| 8 | **KADUS JENGGOTAN** | `1234` | Kewilayahan Dusun Jenggotan |
| 9 | **KADUS CENGKIRANGAN SENTOSO** | `2345` | Kewilayahan Dusun Cengkirangan |
| 10 | **KADUS KWAYUHAN** | `3456` | Kewilayahan Dusun Kwayuhan |
| 11 | **KADUS GENANGSARI CONGKRANG** | `4567` | Kewilayahan Dusun Genangsari |
| 12 | **KADUS BRIGASAN** | `5678` | Kewilayahan Dusun Brigasan |
| 13 | **KADUS KARANG SLAMET** | `6789` | Kewilayahan Dusun Karang Slamet |
| 14 | **KADUS DIMIK WONOLELO** | `7890` | Kewilayahan Dusun Dimik Wonolelo |
| 15 | **NON SOTK/KAWIL GENANGSARI** | `0111` | Non-SOTK / Kawil Genangsari |
| 16 | **NON SOTK/KAWIL CONGKRANG** | `0222` | Non-SOTK / Kawil Congkrang |
| 17 | **NON SOTK/KAWIL BRIGASAN KIDUL** | `0333` | Non-SOTK / Kawil Brigasan Kidul |
| 18 | **NON SOTK/KAWIL BRIGASAN LOR** | `0444` | Non-SOTK / Kawil Brigasan Lor |
| 19 | **NON SOTK/KAWIL KARANG SLAMET LOR** | `0555` | Non-SOTK / Kawil Karang Slamet Lor |

---

## 🗄️ Menjalankan di Supabase Database

1. Buka dashboard [https://supabase.com](https://supabase.com).
2. Buat project baru dan buka **SQL Editor**.
3. Jalankan seluruh baris perintah dari file [`supabase_schema.sql`](supabase_schema.sql).
4. Masukkan **Project URL** dan **Anon API Key** pada modal Supabase di aplikasi web (pojok kanan atas atau via Dashboard Admin).
5. Klik **Simpan & Hubungkan** lalu klik **Sinkronkan Data Lokal ke Supabase**.

---

## 💻 Menjalankan di Localhost (XAMPP)

1. Letakkan folder di `C:\xampp\htdocs\pelaporan-kegiatan-pemdes-pasangsari`.
2. Nyalakan modul Apache di XAMPP.
3. Buka browser:
   ```
   http://localhost/pelaporan-kegiatan-pemdes-pasangsari/
   ```

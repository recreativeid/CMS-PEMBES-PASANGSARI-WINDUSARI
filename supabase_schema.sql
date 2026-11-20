-- ==============================================================
-- SKEMA DATABASE SUPABASE: PASANG LAP EMAS
-- Sistem Pelaporan Transparansi Kinerja Pemdes Pasangsari
-- Kecamatan Windusari, Kabupaten Magelang, Jawa Tengah
-- ==============================================================

-- 1. EXTENSIONS
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 2. TABEL: LAPORAN KEGIATAN PERANGKAT DESA
CREATE TABLE IF NOT EXISTS public.laporan (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    judul TEXT NOT NULL,
    jabatan TEXT NOT NULL,
    deskripsi TEXT NOT NULL,
    output TEXT DEFAULT '-',
    outcome TEXT DEFAULT '-',
    foto_url TEXT NOT NULL,
    status TEXT DEFAULT 'Diterima',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Indeks untuk pencarian cepat & filter
CREATE INDEX IF NOT EXISTS idx_laporan_created_at ON public.laporan(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_laporan_jabatan ON public.laporan(jabatan);

-- 3. TABEL: KOTAK SARAN & ASPIRASI WARGA
CREATE TABLE IF NOT EXISTS public.saran (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nama TEXT DEFAULT 'Anonim',
    kontak TEXT DEFAULT '-',
    isi_saran TEXT NOT NULL,
    status TEXT DEFAULT 'Baru', -- 'Baru', 'Ditinjau', 'Selesai'
    catatan_kades TEXT DEFAULT '',
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_saran_created_at ON public.saran(created_at DESC);

-- 4. TABEL: MASTER DATA 19 PERANGKAT DESA & PIN LOGIN
CREATE TABLE IF NOT EXISTS public.perangkat_desa (
    id SERIAL PRIMARY KEY,
    jabatan TEXT UNIQUE NOT NULL,
    kategori TEXT NOT NULL,
    pin_code TEXT NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 5. TABEL: KREDENSIAL ADMINISTRATOR
CREATE TABLE IF NOT EXISTS public.admin_users (
    id SERIAL PRIMARY KEY,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT DEFAULT 'admin',
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 6. ROW LEVEL SECURITY (RLS)
ALTER TABLE public.laporan ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.saran ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.perangkat_desa ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.admin_users ENABLE ROW LEVEL SECURITY;

-- Policy Laporan: Siapapun boleh membaca & submit
CREATE POLICY "Public Read Laporan" ON public.laporan FOR SELECT USING (true);
CREATE POLICY "Public Insert Laporan" ON public.laporan FOR INSERT WITH CHECK (true);
CREATE POLICY "Public Update Laporan" ON public.laporan FOR UPDATE USING (true);
CREATE POLICY "Public Delete Laporan" ON public.laporan FOR DELETE USING (true);

-- Policy Saran: Publik boleh submit, admin boleh membaca & kelola
CREATE POLICY "Public Read Saran" ON public.saran FOR SELECT USING (true);
CREATE POLICY "Public Insert Saran" ON public.saran FOR INSERT WITH CHECK (true);
CREATE POLICY "Public Update Saran" ON public.saran FOR UPDATE USING (true);
CREATE POLICY "Public Delete Saran" ON public.saran FOR DELETE USING (true);

-- Policy Perangkat: Akses baca untuk verifikasi PIN login
CREATE POLICY "Public Read Perangkat" ON public.perangkat_desa FOR SELECT USING (true);
CREATE POLICY "Public Read Admin" ON public.admin_users FOR SELECT USING (true);

-- 7. SEED DATA: AKUN ADMINISTRATOR
INSERT INTO public.admin_users (username, password, role) VALUES
('admin', 'windusari2024', 'admin')
ON CONFLICT (username) DO UPDATE SET password = EXCLUDED.password;

-- 8. SEED DATA: 19 JABATAN RESMI PEMDES PASANGSARI BESERTA PIN
INSERT INTO public.perangkat_desa (jabatan, kategori, pin_code) VALUES
('KADES', 'Pemerintahan', '1111'),
('SEKDES', 'Pemerintahan', '2222'),
('KASI PEMERINTAHAN', 'Pemerintahan', '3333'),
('KASI KESRA', 'Pemerintahan', '4444'),
('KASI PELAYANAN', 'Pemerintahan', '5555'),
('KAUR KEUANGAN', 'Pemerintahan', '6666'),
('KAUR UMUM DAN PERENCANANAAN', 'Pemerintahan', '7777'),
('KADUS JENGGOTAN', 'Kewilayahan/Kadus', '1234'),
('KADUS CENGKIRANGAN SENTOSO', 'Kewilayahan/Kadus', '2345'),
('KADUS KWAYUHAN', 'Kewilayahan/Kadus', '3456'),
('KADUS GENANGSARI CONGKRANG', 'Kewilayahan/Kadus', '4567'),
('KADUS BRIGASAN', 'Kewilayahan/Kadus', '5678'),
('KADUS KARANG SLAMET', 'Kewilayahan/Kadus', '6789'),
('KADUS DIMIK WONOLELO', 'Kewilayahan/Kadus', '7890'),
('NON SOTK/KAWIL GENANGSARI', 'Non SOTK', '0111'),
('NON SOTK/KAWIL CONGKRANG', 'Non SOTK', '0222'),
('NON SOTK/KAWIL BRIGASAN KIDUL', 'Non SOTK', '0333'),
('NON SOTK/KAWIL BRIGASAN LOR', 'Non SOTK', '0444'),
('NON SOTK/KAWIL KARANG SLAMET LOR', 'Non SOTK', '0555')
ON CONFLICT (jabatan) DO UPDATE 
SET pin_code = EXCLUDED.pin_code,
    kategori = EXCLUDED.kategori;

-- 9. SEED DATA AWAL: CONTOH KEGIATAN DESA PASANGSARI
INSERT INTO public.laporan (judul, jabatan, deskripsi, output, outcome, foto_url, created_at) VALUES
(
    'Penyaluran BLT Dana Desa Tahap III Tahun 2026',
    'KAUR KEUANGAN',
    'Penyaluran Bantuan Langsung Tunai (BLT) Dana Desa kepada 45 KPM di Balai Desa Pasangsari disaksikan BPD, Babinsa, dan Bhabinkamtibmas berjalan aman, tertib, dan transparan.',
    'Tersalurkannya dana tunai tepat sasaran kepada seluruh KPM penerima.',
    'Meringankan beban ekonomi warga prasejahtera dan menjaga daya beli masyarakat.',
    'assets/images/logo-desa-pasangsari.jpg',
    NOW() - INTERVAL '1 hour'
),
(
    'Kerja Bakti Normalisasi Saluran Irigasi Sawah Dusun Brigasan',
    'KADUS BRIGASAN',
    'Gotong royong bersama warga Dusun Brigasan membersihkan sedimentasi lumpur saluran irigasi sawah sepanjang 400 meter menjelang musim tanam padi dan cabai.',
    'Saluran irigasi lancar dan tanggul penahan tanah diperkuat.',
    'Pasokan air ke persawahan tercukupi merata tanpa potensi luapan ke rumah warga.',
    'assets/images/logo-desa-pasangsari.jpg',
    NOW() - INTERVAL '3 hour'
),
(
    'Pelayanan Administrasi Kependudukan Keliling Dusun Kwayuhan',
    'KASI PELAYANAN',
    'Layanan jemput bola penerbitan surat pengantar KTP-el, surat keterangan domisili, dan pembaruan Kartu Keluarga bagi lansia dan disabilitas di Dusun Kwayuhan.',
    'Terbitnya 28 berkas surat pengantar kependudukan dan pembaruan KK warga.',
    'Masyarakat dusun terjauh terlayani secara cepat dan humanis tanpa biaya.',
    'assets/images/logo-desa-pasangsari.jpg',
    NOW() - INTERVAL '5 hour'
),
(
    'Musyawarah Dusun Sinkronisasi Prioritas RKPDes Dusun Jenggotan',
    'KADUS JENGGOTAN',
    'Rapat musrenbangdus bersama para ketua RT, RW, tokoh pemuda, dan kelompok tani Jenggotan guna pemetaan usulan betonisasi jalan usaha tani.',
    'Draft prioritas pembangunan fisik jalan tani sepanjang 250 meter.',
    'Aspirasi warga terakomodasi dalam rancangan APBDes Pasangsari.',
    'assets/images/logo-desa-pasangsari.jpg',
    NOW() - INTERVAL '20 hour'
),
(
    'Monitoring Posyandu Integrasi Layanan Primer Dusun Cengkirangan',
    'KASI KESRA',
    'Pendampingan kader kesehatan desa dalam penimbangan balita, pemberian vitamin A, dan pemeriksaan tekanan darah lansia di Dusun Cengkirangan Sentoso.',
    'Terperiksanya 42 balita dan 30 lansia dengan catatan gizi lengkap.',
    'Pencegahan stunting balita dan deteksi dini kesehatan lansia desa.',
    'assets/images/logo-desa-pasangsari.jpg',
    NOW() - INTERVAL '28 hour'
),
(
    'Rapat Koordinasi Penataan Batas Wilayah & Administrasi Dusun',
    'SEKDES',
    'Rapat koordinasi bersama para Kepala Seksi dan Kepala Dusun membahas sinkronisasi peta blok PBB dan penataan batas antar RT.',
    'Matriks kesepakatan batas lingkungan dan buku registrasi tanah desa.',
    'Tertib administrasi pertanahan dan batas wilayah desa Pasangsari.',
    'assets/images/logo-desa-pasangsari.jpg',
    NOW() - INTERVAL '48 hour'
);

-- 10. SEED DATA AWAL: CONTOH ASPIRASI KOTAK SARAN
INSERT INTO public.saran (nama, kontak, isi_saran, status, created_at) VALUES
(
    'Bpk. Slamet Raharjo',
    '081234567890',
    'Mohon penambahan lampu penerangan jalan umum (PJU) di tikungan menuju Dusun Dimik Wonolelo karena saat malam hari sangat berkabut dan minim penerangan.',
    'Baru',
    NOW() - INTERVAL '2 hour'
),
(
    'Warga Dusun Jenggotan',
    '085799887766',
    'Usul pengadaan tong sampah terpilah di depan balai dusun agar lingkungan tetap bersih dan rapi terutama saat kegiatan posyandu dan pengajian.',
    'Ditinjau',
    NOW() - INTERVAL '24 hour'
);

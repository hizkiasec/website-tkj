-- ============================================================
-- Nexora — Database Schema
-- Jalankan file ini di MySQL/phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS nexora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nexora;

-- ── TABEL: site_settings ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS site_settings (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_val TEXT         NOT NULL,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO site_settings (setting_key, setting_val) VALUES
  ('hero_badge',       'Versi 2.0 sudah tersedia'),
  ('hero_title',       'Kerja lebih <em>cerdas</em>, bukan lebih keras'),
  ('hero_desc',        'Nexora membantu tim Anda berkolaborasi, mengotomasi alur kerja, dan mencapai hasil lebih cepat — semuanya dalam satu platform yang elegan.'),
  ('stat_teams',       '12k+'),
  ('stat_satisfaction','98%'),
  ('stat_productivity','3.2×'),
  ('cta_title',        'Siap membawa tim Anda ke level <em>berikutnya?</em>'),
  ('cta_desc',         'Bergabunglah dengan lebih dari 12.000 tim yang sudah mempercayakan produktivitas mereka pada Nexora.'),
  ('footer_tagline',   'Platform produktivitas modern untuk tim yang ambisius.'),
  ('footer_copyright', '© 2025 Nexora. Semua hak dilindungi.');

-- ── TABEL: features ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS features (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  icon        VARCHAR(10)  NOT NULL,
  title       VARCHAR(150) NOT NULL,
  description TEXT         NOT NULL,
  sort_order  TINYINT UNSIGNED DEFAULT 0,
  is_active   TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO features (icon, title, description, sort_order) VALUES
  ('📋', 'Manajemen Tugas',         'Buat, delegasikan, dan pantau tugas dalam hitungan detik. Dengan tampilan Kanban dan timeline yang intuitif.', 1),
  ('⚡', 'Otomasi Alur Kerja',       'Hemat hingga 5 jam per minggu dengan otomasi cerdas. Tidak butuh koding — cukup klik dan atur.', 2),
  ('📊', 'Analitik Real-time',       'Dashboard yang memperlihatkan progres tim secara langsung. Ambil keputusan berdasarkan data, bukan asumsi.', 3),
  ('🔗', 'Integrasi 100+ Aplikasi',  'Hubungkan dengan Slack, Google Workspace, Notion, Figma, dan ratusan alat lain yang sudah Anda gunakan.', 4),
  ('🛡️', 'Keamanan Enterprise',      'Enkripsi end-to-end, SSO, dan kontrol akses berbasis peran. Data Anda aman bersama kami.', 5),
  ('🤖', 'Asisten AI Bawaan',        'AI yang membantu meringkas rapat, menyusun laporan, dan menyarankan prioritas kerja tim Anda.', 6);

-- ── TABEL: steps (cara kerja) ─────────────────────────────────
CREATE TABLE IF NOT EXISTS steps (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  step_num    VARCHAR(20)  NOT NULL,
  title       VARCHAR(150) NOT NULL,
  description TEXT         NOT NULL,
  sort_order  TINYINT UNSIGNED DEFAULT 0,
  is_active   TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO steps (step_num, title, description, sort_order) VALUES
  ('01 — Daftar',   'Buat Akun',      'Daftar gratis dengan email bisnis Anda. Tidak perlu kartu kredit untuk memulai.', 1),
  ('02 — Setup',    'Undang Tim',     'Undang anggota tim, buat workspace, dan impor proyek yang sudah ada dalam menit.', 2),
  ('03 — Jalankan', 'Mulai Bekerja',  'Buat tugas, otomasi rutinitas, dan pantau progres tim Anda secara real-time.', 3);

-- ── TABEL: testimonials ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS testimonials (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  quote       TEXT         NOT NULL,
  name        VARCHAR(150) NOT NULL,
  role        VARCHAR(200) NOT NULL,
  avatar      VARCHAR(10)  NOT NULL,
  sort_order  TINYINT UNSIGNED DEFAULT 0,
  is_active   TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO testimonials (quote, name, role, avatar, sort_order) VALUES
  ('Sejak pakai Nexora, meeting mingguan kami berkurang 40%. Semua orang tahu apa yang harus dikerjakan tanpa harus ditanyakan terus.', 'Arini Rahmawati', 'Product Manager · Gojek',       'AR', 1),
  ('Integrasi dengan Figma dan Jira-nya mulus banget. Tim desain dan engineering akhirnya bisa bekerja dalam satu ekosistem.',          'Dian Saputra',    'Engineering Lead · Traveloka',   'DS', 2),
  ('Fitur AI-nya luar biasa. Otomatis merangkum hasil meeting dan langsung buat task list. Hemat waktu banget!',                         'Nadia Kusuma',    'COO · Startup Fintech',           'NK', 3);

-- ── TABEL: pricing_plans ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS pricing_plans (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tier_name    VARCHAR(100) NOT NULL,
  price_label  VARCHAR(100) NOT NULL,
  price_suffix VARCHAR(100) NOT NULL,
  description  VARCHAR(255) NOT NULL,
  btn_label    VARCHAR(100) NOT NULL,
  btn_style    ENUM('outline','accent') DEFAULT 'outline',
  is_featured  TINYINT(1) DEFAULT 0,
  sort_order   TINYINT UNSIGNED DEFAULT 0,
  is_active    TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO pricing_plans (tier_name, price_label, price_suffix, description, btn_label, btn_style, is_featured, sort_order) VALUES
  ('Starter',    'Gratis',   '/ selamanya',  'Untuk tim kecil yang baru memulai.',                                  'Mulai Gratis',          'outline', 0, 1),
  ('Pro',        'Rp 149k',  '/ bulan',      'Untuk tim yang ingin tumbuh lebih cepat.',                             'Coba 14 Hari Gratis',   'accent',  1, 2),
  ('Enterprise', 'Custom',   '/ negosiasi',  'Untuk organisasi skala besar dengan kebutuhan khusus.',                'Hubungi Sales',         'outline', 0, 3);

-- ── TABEL: pricing_features ───────────────────────────────────
CREATE TABLE IF NOT EXISTS pricing_features (
  id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  plan_id  INT UNSIGNED NOT NULL,
  feature  VARCHAR(255) NOT NULL,
  sort_order TINYINT UNSIGNED DEFAULT 0,
  FOREIGN KEY (plan_id) REFERENCES pricing_plans(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Starter features
INSERT INTO pricing_features (plan_id, feature, sort_order) VALUES
  (1, 'Hingga 5 anggota', 1),
  (1, '3 proyek aktif', 2),
  (1, '5 GB penyimpanan', 3),
  (1, 'Integrasi dasar', 4);

-- Pro features
INSERT INTO pricing_features (plan_id, feature, sort_order) VALUES
  (2, 'Hingga 25 anggota', 1),
  (2, 'Proyek tidak terbatas', 2),
  (2, '50 GB penyimpanan', 3),
  (2, 'Semua integrasi', 4),
  (2, 'Fitur AI bawaan', 5),
  (2, 'Prioritas dukungan', 6);

-- Enterprise features
INSERT INTO pricing_features (plan_id, feature, sort_order) VALUES
  (3, 'Anggota tidak terbatas', 1),
  (3, 'Storage tidak terbatas', 2),
  (3, 'SSO & SAML', 3),
  (3, 'SLA 99.9% uptime', 4),
  (3, 'Dedicated support', 5),
  (3, 'On-premise tersedia', 6);

-- ── TABEL: logo_strip ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS logo_strip (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  sort_order TINYINT UNSIGNED DEFAULT 0,
  is_active  TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO logo_strip (name, sort_order) VALUES
  ('Tokopedia', 1), ('Gojek', 2), ('Traveloka', 3),
  ('Tiket.com', 4), ('Bukalapak', 5), ('OVO', 6);

-- ── TABEL: newsletter_subscribers ────────────────────────────
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email      VARCHAR(255) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── TABEL: contact_messages ───────────────────────────────────
CREATE TABLE IF NOT EXISTS contact_messages (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(150) NOT NULL,
  email      VARCHAR(255) NOT NULL,
  message    TEXT         NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_read    TINYINT(1) DEFAULT 0
) ENGINE=InnoDB;

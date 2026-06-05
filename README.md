# Nexora — Setup Guide

## Struktur File
```
nexora/
├── index.php              ← Halaman utama (landing page)
├── database.sql           ← Script database MySQL
├── includes/
│   ├── db.php             ← Konfigurasi koneksi database
│   └── functions.php      ← Helper functions
└── admin/
    └── index.php          ← Panel admin
```

## Langkah Setup

### 1. Import Database
Buka **phpMyAdmin** atau MySQL CLI, lalu jalankan:
```sql
source /path/to/nexora/database.sql
```
Atau buka phpMyAdmin → tab **Import** → pilih file `database.sql`.

### 2. Konfigurasi Koneksi
Edit file `includes/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'nexora');
define('DB_USER', 'root');       // ← ganti username MySQL Anda
define('DB_PASS', '');           // ← ganti password MySQL Anda
```

### 3. Upload ke Server
- Copy seluruh folder `nexora/` ke direktori web Anda (misal: `htdocs/` atau `www/`)
- Pastikan PHP 7.4+ dan ekstensi `pdo_mysql` aktif

### 4. Akses Website
- **Landing Page:** `http://localhost/nexora/`
- **Admin Panel:** `http://localhost/nexora/admin/`
  - Username: `admin`
  - Password: `nexora2025`

### 5. Ganti Password Admin (WAJIB!)
Edit baris berikut di `admin/index.php`:
```php
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'nexora2025');  // ← ganti dengan password kuat
```

## Fitur Dinamis yang Tersedia

| Bagian            | Bisa Diubah dari Database |
|-------------------|--------------------------|
| Hero (badge, judul, deskripsi, statistik) | ✅ |
| Logo Strip        | ✅ |
| Fitur Unggulan    | ✅ (CRUD via admin) |
| Cara Kerja        | ✅ |
| Testimonial       | ✅ (CRUD via admin) |
| Pricing & Fitur   | ✅ |
| CTA & Footer      | ✅ |

## Panel Admin
URL: `/nexora/admin/`

Fitur admin:
- **Settings** — ubah semua teks halaman
- **Fitur** — tambah/edit/hapus fitur unggulan
- **Testimonial** — tambah/hapus ulasan pengguna
- **Pesan Masuk** — lihat form kontak yang masuk
- **Subscriber** — lihat daftar email newsletter

## Keamanan Production
Tambahkan file `.htaccess` di folder `admin/` untuk membatasi akses:
```apache
AuthType Basic
AuthName "Admin Area"
AuthUserFile /path/to/.htpasswd
Require valid-user
```

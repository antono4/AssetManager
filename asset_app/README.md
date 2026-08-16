# Aplikasi Manajemen Aset (AssetManager)

<div align="center">

**Aplikasi manajemen aset IT & umum** berbasis PHP Native dengan UI AdminLTE 3, multi-bahasa (Inggris & Indonesia), dashboard modern, modul patching kuartalan, laporan, dan banyak lagi.

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)
![AdminLTE](https://img.shields.io/badge/AdminLTE-3.2-2b3a55?logo=adminer&logoColor=white)
![Database](https://img.shields.io/badge/DB-MySQL%20%7C%20SQLite-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

</div>

---

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Teknologi](#-teknologi)
- [Struktur Proyek](#-struktur-proyek)
- [Instalasi Cepat](#-instalasi-cepat)
- [Instalasi Lengkap](#-instalasi-lengkap)
- [Panduan Penggunaan](#-panduan-penggunaan)
- [Akun Default](#-akun-default)
- [Konfigurasi](#-konfigurasi)
- [Hak Akses (RBAC)](#-hak-akses-rbac)
- [Multi-Bahasa](#-multi-bahasa)
- [API & Route](#-api--route)
- [Troubleshooting](#-troubleshooting)
- [FAQ](#-faq)

---

## 🚀 Fitur Utama

### Dashboard Modern
- **Welcome header** dengan greeting dinamis (pagi/siang/sore/malam) + emoji animasi
- 4 **kartu statistik gradient** (Total Aset, Tersedia, Dipinjam, Rusak) — klik untuk filter
- **Grafik distribusi aset** per kategori (bar chart) & status (donut chart) — ApexCharts
- **Widget Patching** — progress patching kuartalan dengan progress bar
- **Quick Access** — tombol shortcut ke menu utama
- **Aset Terbaru** — tabel dengan thumbnail foto
- **Aktivitas Terbaru** — timeline modern dengan icon berwarna per aksi

### Manajemen Aset
- CRUD aset lengkap (tambah, edit, hapus, detail)
- **Upload foto** untuk setiap aset (JPG/PNG/GIF/WebP, maks 5MB)
- Pencarian (kode/nama/brand/lokasi) + filter status & kategori
- Pagination
- Detail aset dengan foto, riwayat log, & ubah status cepat
- Kode aset auto-generate (AST-0001, AST-0002, ...)
- **Harga aset hanya tampil untuk admin** (staff disembunyikan)

### Kategori
- CRUD kategori (admin only)
- Proteksi hapus bila kategori masih dipakai aset
- Modal inline untuk tambah/edit

### Manajemen User
- CRUD user (admin only) dengan role `admin`/`staff`
- Aktif/nonaktif, ganti password
- Profil user (semua bisa edit profil sendiri)

### Modul Patching (Kuartalan / per 3 Bulan)
- Buat jadwal patching per kuartal (Q1-Q4) dengan auto-fill tanggal
- **Generate checklist** per aset IT (otomatis exclude kategori "Umum")
- 6 item checklist standar: Update OS/Firmware, Antivirus, Backup, Cek Log, Restart, Verifikasi
- **Input kode patching** per item per komputer (mis: KB5079473)
- Centang item via AJAX — status auto-update (pending → in_progress → completed)
- Status jadwal auto-refresh (draft → ongoing → completed)
- Aksi: skip aset, reset checklist
- **Daftar Komputer Patching** — tabel komputer + kode patching + matriks item
- Penyelesaian patching tercatat di riwayat aktivitas

### Modul Report (Laporan)
- 4 tab: Ringkasan, Per Kategori, Per Lokasi, Detail Aset
- Filter: kategori, status, lokasi, rentang tanggal pembelian
- Grafik bar & donut di tab ringkasan
- **Cetak / Simpan PDF** — halaman print-friendly dengan kop laporan, semua section, tanda tangan
- CSS print khusus dengan `@media print`

### Riwayat Aktivitas
- Log semua perubahan aset (created, updated, status change, patching)
- Catat pelaku & waktu
- Pagination

### Autentikasi & Keamanan
- Login terpisah dari dashboard (halaman login modern split-screen)
- Password di-hash dengan **bcrypt** (`password_hash`)
- Sesi aman (HttpOnly, SameSite cookie)
- Route `/setup` untuk reset password default
- Escape HTML di semua output (anti-XSS)

### Multi-Bahasa (i18n)
- **Default: English**
- Dukung **Bahasa Indonesia**
- Switcher dropdown di navbar (flag icon) + tombol EN/ID di login
- Preferensi disimpan di session + cookie (1 tahun)
- 150+ key terjemahan

---

## 🛠 Teknologi

| Komponen | Teknologi |
|----------|-----------|
| Backend | PHP 8.0+ (native, tanpa framework) |
| Database | MySQL 5.7+ / MariaDB 10+ **atau** SQLite (demo) |
| UI Framework | AdminLTE 3.2 + Bootstrap 4.6 |
| Icon | Font Awesome 6, Bootstrap Icons, Flag Icons |
| Chart | ApexCharts 3.49 |
| Auth | bcrypt (password_hash) |
| Font | Source Sans Pro |

---

## 📁 Struktur Proyek

```
asset_app/
├── config.php                      # Konfigurasi & autoload
├── README.md                       # File ini
├── INSTALL.md                      # Panduan instalasi detail
├── USER_GUIDE.md                   # Panduan penggunaan detail
├── database/
│   ├── assets_app.sql              # Skema MySQL + data dummy
│   └── asset_db.sqlite             # Auto-generated (demo SQLite)
├── public/
│   ├── index.php                   # Entry point + routing
│   ├── .htaccess                   # Apache rewrite
│   └── assets/
│       ├── css/
│       │   ├── app.css             # CSS custom utama
│       │   ├── dashboard.css       # CSS dashboard modern
│       │   ├── login.css           # CSS login split-screen
│       │   └── print.css           # CSS cetak laporan
│       ├── js/
│       │   └── app.js              # JS custom
│       └── uploads/
│           └── assets/             # Foto aset (auto-create)
└── app/
    ├── core/
    │   ├── Database.php             # PDO adapter (MySQL + SQLite) + migrasi
    │   ├── Auth.php                 # Session, login, RBAC
    │   ├── Flash.php                # Pesan flash
    │   ├── Lang.php                 # Internationalization (i18n)
    │   ├── View.php                 # Template engine + helpers
    │   └── Router.php               # Router sederhana
    ├── models/
    │   ├── Asset.php                # Model aset + foto upload
    │   ├── Category.php            # Model kategori
    │   ├── AssetLog.php             # Model riwayat log
    │   ├── User.php                 # Model user
    │   ├── PatchSchedule.php        # Model jadwal patching
    │   └── PatchChecklist.php       # Model checklist + kode patching
    ├── controllers/
    │   ├── AuthController.php       # Login, logout, setup
    │   ├── DashboardController.php  # Dashboard
    │   ├── AssetController.php      # CRUD aset + foto
    │   ├── CategoryController.php   # CRUD kategori
    │   ├── UserController.php       # CRUD user + profil
    │   ├── LogController.php        # Riwayat aktivitas
    │   ├── ReportController.php     # Laporan + cetak
    │   └── PatchController.php      # Jadwal + checklist patching
    ├── lang/
    │   ├── en.php                   # English (default)
    │   └── id.php                   # Bahasa Indonesia
    └── views/
        ├── layouts/
        │   ├── app.php              # Layout utama (sidebar, navbar)
        │   ├── blank.php            # Layout login (split-screen)
        │   └── print.php            # Layout cetak laporan
        └── pages/
            ├── login.php            # Halaman login
            ├── setup.php            # Halaman setup password
            ├── dashboard.php        # Dashboard
            ├── error.php            # Halaman 404
            ├── assets/              # index, show, form
            ├── categories/          # index (modal)
            ├── users/               # index, profile
            ├── logs/                # index
            ├── reports/             # index, print, 4 tab partials
            └── patch/               # index, show, form, checklist, computers
```

---

## ⚡ Instalasi Cepat

### Mode Demo (SQLite — tanpa MySQL, langsung jalan)

```bash
cd asset_app
php -S 0.0.0.0:8080 -t public public/index.php
```

Buka `http://localhost:8080`. Login dengan `admin` / `admin123`.

> Database SQLite & data dummy otomatis dibuat saat pertama dijalankan.

### Mode Produksi (MySQL)

```bash
mysql -u root -p -e "CREATE DATABASE asset_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p asset_db < database/assets_app.sql
DB_DRIVER=mysql DB_HOST=127.0.0.1 DB_NAME=asset_db DB_USER=root DB_PASS=secret php -S 0.0.0.0:8080 -t public public/index.php
```

Akses `http://localhost:8080/setup` sekali untuk reset password default, lalu login.

> 📖 Detail lengkap lihat **[INSTALL.md](INSTALL.md)**

---

## 📖 Panduan Penggunaan

### Login
1. Buka aplikasi → halaman login modern
2. Masukkan username & password
3. Klik **Sign In** → redirect ke dashboard

### Dashboard
- Lihat statistik aset, grafik, patching progress, aset terbaru, aktivitas
- Klik kartu statistik untuk filter aset
- Gunakan **Quick Access** untuk navigasi cepat

### Aset
- **Daftar Aset**: lihat semua aset dengan filter & search
- **Tambah Aset**: isi form + upload foto
- **Detail Aset**: lihat foto, info, riwayat, ubah status cepat
- **Edit Aset**: ubah data + ganti/hapus foto

### Patching
1. Buat jadwal per kuartal
2. Generate checklist aset IT
3. Buka checklist per komputer → centang item + input kode patching
4. Lihat **Daftar Komputer Patching** untuk rekap

### Laporan
- Pilih tab (Ringkasan/Per Kategori/Per Lokasi/Detail)
- Filter sesuai kebutuhan
- Klik **Cetak / PDF** untuk print/simpan PDF

> 📖 Detail lengkap lihat **[USER_GUIDE.md](USER_GUIDE.md)**

---

## 🔑 Akun Default

| Username | Password | Role | Akses |
|----------|----------|------|-------|
| `admin` | `admin123` | admin | Semua fitur |
| `staff` | `staff123` | staff | Lihat aset, ubah status, patching checklist, profil |

> ⚠️ **PENTING**: Ganti password default setelah instalasi! Akses `/setup` untuk reset, atau edit di menu Manajemen User.

---

## ⚙️ Konfigurasi

Semua konfigurasi via environment variable atau edit `config.php`:

| Variable | Default | Keterangan |
|----------|---------|-----------|
| `DB_DRIVER` | `sqlite` | `sqlite` (demo) atau `mysql` (produksi) |
| `DB_HOST` | `127.0.0.1` | Host MySQL |
| `DB_PORT` | `3306` | Port MySQL |
| `DB_NAME` | `asset_db` | Nama database MySQL |
| `DB_USER` | `root` | User MySQL |
| `DB_PASS` | (kosong) | Password MySQL |
| `APP_BASE_URL` | (auto) | Base URL aplikasi |

---

## 🔒 Hak Akses (RBAC)

| Fitur | Admin | Staff |
|-------|:-----:|:-----:|
| Dashboard | ✅ | ✅ |
| Lihat Aset | ✅ | ✅ |
| Lihat Harga Aset | ✅ | ❌ |
| Tambah/Edit/Hapus Aset | ✅ | ❌ |
| Ubah Status Aset | ✅ | ✅ |
| Upload/Hapus Foto | ✅ | ❌ |
| Kategori (CRUD) | ✅ | ❌ |
| Manajemen User (CRUD) | ✅ | ❌ |
| Buat/Edit Jadwal Patching | ✅ | ❌ |
| Generate Checklist | ✅ | ❌ |
| Centang Checklist Patching | ✅ | ✅ |
| Lihat Laporan | ✅ | ✅ |
| Lihat Riwayat | ✅ | ✅ |
| Edit Profil Sendiri | ✅ | ✅ |
| Setup Password | ✅ | ✅ |
| Ganti Bahasa | ✅ | ✅ |

---

## 🌐 Multi-Bahasa

- **Default**: English
- **Tersedia**: English (EN) & Bahasa Indonesia (ID)
- Switcher: dropdown di navbar (icon bendera) + tombol EN/ID di halaman login
- Preferensi tersimpan di session + cookie (1 tahun)
- Route: `/language/set?lang=en` atau `/language/set?lang=id`

---

## 🛣 API & Route

### Auth
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/login` | Halaman login |
| POST | `/login` | Proses login |
| GET | `/logout` | Logout |
| GET | `/setup` | Reset password default |
| GET | `/language/set?lang=xx` | Ganti bahasa |

### Aset
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/assets` | Daftar aset (filter, search, pagination) |
| GET | `/assets/create` | Form tambah aset (admin) |
| POST | `/assets` | Simpan aset baru (admin) |
| GET | `/assets/{id}` | Detail aset |
| GET | `/assets/{id}/edit` | Form edit (admin) |
| POST | `/assets/{id}` | Update aset (admin) |
| POST | `/assets/{id}/delete` | Hapus aset (admin) |
| POST | `/assets/{id}/status` | Ubah status cepat |
| POST | `/assets/{id}/remove-photo` | Hapus foto (admin) |

### Kategori, User, Log
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET/POST | `/categories` | Daftar + CRUD kategori (admin) |
| GET/POST | `/users` | Daftar + CRUD user (admin) |
| GET/POST | `/profile` | Profil saya |
| GET | `/logs` | Riwayat aktivitas |

### Laporan
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/reports` | Halaman laporan (4 tab + filter) |
| GET | `/reports/print` | Cetak/simpan PDF |

### Patching
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/patching` | Daftar jadwal patching |
| GET/POST | `/patching/create` | Buat jadwal (admin) |
| GET | `/patching/{id}` | Detail jadwal + checklist |
| POST | `/patching/{id}/generate` | Generate checklist aset (admin) |
| POST | `/patching/{id}/generate-all` | Generate semua aset IT (admin) |
| GET | `/patching/{id}/computers` | Daftar komputer + kode patching |
| GET | `/patching/checklist/{id}` | Checklist per aset |
| POST | `/patching/checklist/{id}/toggle` | Centang item (AJAX) |
| POST | `/patching/checklist/{id}/save-code` | Simpan kode patching (AJAX) |
| POST | `/patching/checklist/{id}/status` | Skip/reset checklist |
| POST | `/patching/checklist/{id}/delete` | Hapus checklist (admin) |

---

## 🔧 Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Grafik dashboard kosong | Pastikan koneksi internet (CDN ApexCharts) |
| Login gagal | Akses `/setup` untuk reset password |
| Foto tidak upload | Cek folder `public/uploads/assets` writable |
| 404 semua halaman | Jalankan via `php -S ... public/index.php` (router) |
| Error koneksi MySQL | Cek `DB_HOST`, `DB_USER`, `DB_PASS` |
| Bahasa tidak berubah | Clear cookie browser, akses `/language/set?lang=en` |

---

## ❓ FAQ

**Q: Bagaimana ganti password user?**
A: Admin bisa lewat Manajemen User → Edit User. User biasa lewat Profil.

**Q: Bisakah pakai tanpa MySQL?**
A: Ya, mode SQLite (demo) jalan otomatis tanpa instalasi MySQL.

**Q: Bagaimana deploy ke server produksi?**
A: Lihat [INSTALL.md](INSTALL.md) bagian Deploy.

**Q: Apakah foto aset disimpan di mana?**
A: Di folder `public/uploads/assets/`. Tidak di-database.

**Q: Bagaimana backup data?**
A: MySQL: `mysqldump`. SQLite: copy file `database/asset_db.sqlite`.

**Q: Bisakah tambah bahasa lain?**
A: Ya, tambah file di `app/lang/` dan daftar di `Lang::SUPPORTED`.

---

## 📄 Lisensi

MIT License — bebas digunakan, dimodifikasi, dan didistribusikan.

---

<div align="center">

**Dibuat dengan PHP Native & AdminLTE 3**

[📊 GitHub](https://github.com/antono4/AssetManager)

</div>

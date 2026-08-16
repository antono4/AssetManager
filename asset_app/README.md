# Aplikasi Manajemen Aset (AssetManager)

<div align="center">

**Aplikasi manajemen aset IT & umum** — PHP Native, AdminLTE 3, multi-bahasa, dashboard modern, patching kuartalan, laporan, REST API, dan 20+ fitur lengkap.

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)
![AdminLTE](https://img.shields.io/badge/AdminLTE-3.2-2b3a55)
![Database](https://img.shields.io/badge/DB-MySQL%20%7C%20SQLite-4479A1?logo=mysql&logoColor=white)
![PWA](https://img.shields.io/badge/PWA-Installable-5A0FC8?logo=pwa&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

</div>

---

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Teknologi](#-teknologi)
- [Struktur Proyek](#-struktur-proyek)
- [Instalasi Cepat](#-instalasi-cepat)
- [Akun Default](#-akun-default)
- [Hak Akses (RBAC)](#-hak-akses-rbac)
- [Multi-Bahasa](#-multi-bahasa)
- [API & Route](#-api--route)
- [Troubleshooting](#-troubleshooting)
- [FAQ](#-faq)
- [Lisensi](#-lisensi)

> 📖 Dokumentasi detail: **[INSTALL.md](INSTALL.md)** (instalasi) · **[USER_GUIDE.md](USER_GUIDE.md)** (penggunaan)

---

## 🚀 Fitur Utama

### Dashboard Modern
- **Welcome header** dengan greeting dinamis (pagi/siang/sore/malam) + emoji animasi
- 4 **kartu statistik gradient** (Total, Tersedia, Dipinjam, Rusak) — klik untuk filter
- **Grafik ApexCharts** — bar (distribusi kategori) & donut (status dengan total di tengah)
- **Widget Patching** — progress patching kuartalan dengan progress bar
- **Quick Access** — tombol shortcut ke menu utama
- **Aset Terbaru** — tabel dengan thumbnail foto
- **Aktivitas Terbaru** — timeline modern dengan icon berwarna per aksi
- **Dark Mode** — toggle dark/light mode (persisten via cookie)

### Manajemen Aset
- CRUD aset lengkap dengan **upload foto** (JPG/PNG/GIF/WebP, maks 5MB)
- Pencarian + filter status & kategori + pagination
- **QR Code** per aset — scan untuk buka detail aset
- **Soft Delete** — hapus ke trash, restore, atau hapus permanen
- **Export CSV** — download daftar aset dengan filter aktif
- **Import CSV** — tambah banyak aset sekaligus dari file CSV
- **Depreciation** — hitung nilai buku aset (straight-line, umur ekonomis)
- **Multi-Currency** — IDR, USD, EUR
- Kode aset auto-generate (AST-0001, ...)
- **Harga hanya tampil untuk admin**

### Modul Patching (Kuartalan / per 3 Bulan)
- Jadwal patching per kuartal (Q1-Q4) dengan auto-fill tanggal
- Generate checklist per aset IT (exclude kategori "Umum")
- 6 item checklist standar (Update OS, Antivirus, Backup, Cek Log, Restart, Verifikasi)
- **Input kode patching** per item per komputer (mis: KB5079473)
- Centang item via AJAX — status auto-update (pending → in_progress → completed)
- **Daftar Komputer Patching** — tabel komputer + kode patching + matriks item

### Modul Report (Laporan)
- 4 tab: Ringkasan, Per Kategori, Per Lokasi, Detail Aset
- Filter: kategori, status, lokasi, rentang tanggal pembelian
- **Cetak / Simpan PDF** — halaman print-friendly dengan kop laporan & tanda tangan

### Peminjaman Aset (Borrowing)
- Catat peminjaman: borrower, tanggal pinjam, expected return, note
- Auto-set status aset → dipinjam
- Return aset → auto-set status → tersedia
- **Overdue detection** — peminjaman lewat jatuh tempo

### Notifikasi
- In-app notification dengan badge di navbar
- Auto-check patching overdue
- Mark read / mark all read

### Keamanan & Audit
- **Rate Limiting** login — max 5 percobaan, lock 15 menit
- **Audit Trail** — log semua modul (asset, user, kategori, token, borrow) dengan user, IP, waktu
- **REST API Token** — generate/hapus token, endpoint `/api/assets`
- Password bcrypt, session HttpOnly + SameSite, escape HTML (anti-XSS)
- **Activity by User** — lihat aktivitas per user

### Multi-Bahasa (i18n)
- **Default: English** + **Bahasa Indonesia**
- Switcher di navbar (flag icon) + tombol EN/ID di login
- 200+ key terjemahan, preferensi persisten (cookie 1 tahun)

### PWA (Progressive Web App)
- Installable di mobile/desktop (manifest.json)
- Service worker untuk offline cache

### Lainnya
- Login modern split-screen dengan glassmorphism
- Profil user (edit sendiri)
- Riwayat aktivitas (log semua perubahan aset)
- Setup route `/setup` untuk reset password

---

## 🛠 Teknologi

| Komponen | Teknologi |
|----------|-----------|
| Backend | PHP 8.0+ (native, tanpa framework) |
| Database | MySQL 5.7+ / MariaDB 10+ **atau** SQLite (demo) |
| UI | AdminLTE 3.2 + Bootstrap 4.6 |
| Icon | Font Awesome 6, Bootstrap Icons, Flag Icons |
| Chart | ApexCharts 3.49 |
| Auth | bcrypt + Rate Limiting + API Token |
| PWA | manifest.json + Service Worker |
| Font | Source Sans Pro |

---

## 📁 Struktur Proyek

```
asset_app/
├── config.php                      # Konfigurasi & autoload
├── README.md                       # File ini
├── INSTALL.md                      # Panduan instalasi detail
├── USER_GUIDE.md                   # Panduan penggunaan detail
├── LICENSE                         # MIT License
├── database/
│   ├── assets_app.sql              # Skema MySQL + data dummy
│   └── asset_db.sqlite             # Auto-generated (demo SQLite)
├── public/
│   ├── index.php                   # Entry point + routing
│   ├── .htaccess                   # Apache rewrite
│   ├── manifest.json               # PWA manifest
│   ├── sw.js                       # Service Worker
│   └── assets/
│       ├── css/ (app, dashboard, login, print, darkmode)
│       ├── js/app.js
│       └── uploads/assets/         # Foto aset
└── app/
    ├── core/ (Database, Auth, Flash, Lang, View, Router)
    ├── models/ (Asset, Category, AssetLog, User, PatchSchedule,
    │            PatchChecklist, AuditTrail, Notification, Borrowing, ApiToken)
    ├── controllers/ (Auth, Dashboard, Asset, Category, User, Log,
    │                  Report, Patch, Extended)
    ├── lang/ (en.php, id.php)
    └── views/
        ├── layouts/ (app, blank, print)
        └── pages/ (login, dashboard, assets/, categories/, users/,
                    logs/, reports/, patch/, borrowings/, notifications/,
                    audit/, api/, search, trash, import)
```

---

## ⚡ Instalasi Cepat

### Mode Demo (SQLite — tanpa MySQL)

```bash
cd asset_app
php -S 0.0.0.0:8080 -t public public/index.php
```

Buka `http://localhost:8080` → login `admin` / `admin123`.

> Database & data dummy otomatis dibuat. Semua tabel baru (audit, borrow, notif, token) auto-migrate.

### Mode Produksi (MySQL)

```bash
mysql -u root -p -e "CREATE DATABASE asset_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p asset_db < database/assets_app.sql
DB_DRIVER=mysql DB_HOST=127.0.0.1 DB_NAME=asset_db DB_USER=root DB_PASS=secret \
  php -S 0.0.0.0:8080 -t public public/index.php
```

Akses `http://localhost:8080/setup` sekali untuk reset password.

> 📖 Detail lengkap: **[INSTALL.md](INSTALL.md)** (Apache, Nginx, Docker, dll)

---

## 🔑 Akun Default

| Username | Password | Role | Akses |
|----------|----------|------|-------|
| `admin` | `admin123` | admin | Semua fitur |
| `staff` | `staff123` | staff | Lihat aset, borrow, patching checklist, profil |

> ⚠️ Ganti password setelah instalasi via Manajemen User atau `/setup`.

---

## 🔒 Hak Akses (RBAC)

| Fitur | Admin | Staff |
|-------|:-----:|:-----:|
| Dashboard, Dark Mode, Search, Bahasa | ✅ | ✅ |
| Lihat Aset, Export CSV | ✅ | ✅ |
| Lihat Harga/Depreciation | ✅ | ❌ |
| Tambah/Edit/Hapus Aset, Upload Foto | ✅ | ❌ |
| Trash, Import CSV | ✅ | ❌ |
| Borrow/Return Aset | ✅ | ✅ |
| Ubah Status Aset | ✅ | ✅ |
| Kategori, Manajemen User | ✅ | ❌ |
| Audit Trail, API Token | ✅ | ❌ |
| Buat/Edit Jadwal Patching, Generate | ✅ | ❌ |
| Centang Checklist Patching | ✅ | ✅ |
| Lihat Laporan, Riwayat, Notifikasi | ✅ | ✅ |
| Edit Profil | ✅ | ✅ |

---

## 🌐 Multi-Bahasa

- **Default**: English | **Tersedia**: English (EN) & Bahasa Indonesia (ID)
- Switcher: navbar dropdown + tombol di login | Route: `/language/set?lang=xx`

---

## 🛣 API & Route

### Auth & Utility
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET/POST | `/login` | Login |
| GET | `/logout` | Logout |
| GET | `/setup` | Reset password |
| GET | `/language/set?lang=xx` | Ganti bahasa |
| GET | `/dark-mode` | Toggle dark mode |
| GET | `/search?q=xx` | Global search |

### Aset
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/assets` | Daftar aset |
| GET | `/assets/create` | Form tambah (admin) |
| POST | `/assets` | Simpan baru (admin) |
| GET | `/assets/{id}` | Detail + QR code + depreciation |
| GET/POST | `/assets/{id}/edit` | Edit (admin) |
| POST | `/assets/{id}/delete` | Soft delete (admin) |
| POST | `/assets/{id}/status` | Ubah status |
| POST | `/assets/{id}/remove-photo` | Hapus foto (admin) |
| GET | `/assets/export` | Export CSV |
| GET/POST | `/assets/import` | Import CSV (admin) |
| GET | `/assets/csv-template` | Download template CSV |
| GET | `/assets/trash` | Trash (admin) |
| POST | `/assets/{id}/restore` | Restore (admin) |
| POST | `/assets/{id}/force-delete` | Hapus permanen (admin) |
| GET/POST | `/assets/{id}/borrow` | Pinjam aset |

### Borrowing, Notifications, Audit, API
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET | `/borrowings` | Daftar peminjaman |
| POST | `/borrowings/{id}/return` | Kembalikan aset |
| GET | `/notifications` | Notifikasi |
| POST | `/notifications/{id}/read` | Mark read |
| POST | `/notifications/read-all` | Mark all read |
| GET | `/audit` | Audit trail (admin) |
| GET/POST | `/api-tokens` | API token (admin) |
| POST | `/api-tokens/{id}/delete` | Hapus token (admin) |
| GET | `/api/assets` | REST API (token auth) |
| GET | `/users/{id}/activity` | Activity per user |

### Kategori, User, Log, Report, Patching
| Method | Path | Deskripsi |
|--------|------|-----------|
| GET/POST | `/categories` | Kategori (admin) |
| GET/POST | `/users` | User (admin) |
| GET/POST | `/profile` | Profil |
| GET | `/logs` | Riwayat |
| GET | `/reports` | Laporan |
| GET | `/reports/print` | Cetak PDF |
| GET-POST | `/patching/*` | Jadwal & checklist patching |

---

## 🔧 Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Grafik kosong | Cek koneksi internet (CDN ApexCharts) |
| Login gagal/locked | Rate limit: tunggu 15 menit atau `/setup` |
| Foto tidak upload | Folder `public/uploads/assets` writable (775) |
| 404 semua halaman | Jalankan via `php -S ... public/index.php` |
| Dark mode tidak persist | Clear cookie, toggle ulang |
| API 401 | Sertakan token: `?token=YOUR_TOKEN` |

---

## ❓ FAQ

**Q: Bisakah pakai tanpa MySQL?** A: Ya, mode SQLite otomatis.

**Q: Cara deploy?** A: Lihat [INSTALL.md](INSTALL.md) — Apache, Nginx, Docker.

**Q: Cara backup?** A: MySQL: `mysqldump`. SQLite: copy `.sqlite`. Foto: `public/uploads/`.

**Q: Cara tambah bahasa?** A: Tambah file `app/lang/xx.php` + daftar di `Lang::SUPPORTED`.

**Q: Cara pakai REST API?** A: Generate token di `/api-tokens`, lalu `GET /api/assets?token=TOKEN`.

---

## 📄 Lisensi

MIT License — lihat [LICENSE](LICENSE).

---

<div align="center">

**Dibuat dengan PHP Native & AdminLTE 3**

[📊 GitHub](https://github.com/antono4/AssetManager)

</div>

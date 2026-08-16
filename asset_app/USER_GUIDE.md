# 📖 Panduan Penggunaan — AssetManager

Panduan lengkap cara menggunakan semua fitur AssetManager.

---

## 📋 Daftar Isi

- [Login & Logout](#-login--logout)
- [Dashboard](#-dashboard)
- [Dark Mode](#-dark-mode)
- [Global Search](#-global-search)
- [Manajemen Aset](#-manajemen-aset)
- [QR Code](#-qr-code)
- [Export & Import CSV](#-export--import-csv)
- [Soft Delete & Trash](#-soft-delete--trash)
- [Depreciation & Multi-Currency](#-depreciation--multi-currency)
- [Peminjaman Aset (Borrowing)](#-peminjaman-aset-borrowing)
- [Kategori](#-kategori)
- [Manajemen User](#-manajemen-user)
- [Profil & Activity](#-profil--activity)
- [Modul Patching](#-modul-patching)
- [Modul Report](#-modul-report)
- [Riwayat & Audit Trail](#-riwayat--audit-trail)
- [Notifikasi](#-notifikasi)
- [API Token & REST API](#-api-token--rest-api)
- [Multi-Bahasa](#-multi-bahasa)
- [PWA](#-pwa)
- [Admin vs Staff](#-admin-vs-staff)

---

## 🔐 Login & Logout

### Login
1. Buka aplikasi → halaman login split-screen modern
2. Masukkan username & password
3. Klik **Sign In**
4. Toggle password (icon mata) untuk lihat/sembunyikan
5. Centang **Remember me** untuk tetap login

### Logout
Klik icon logout di navbar kanan → konfirmasi.

### Rate Limiting
- Maks 5 percobaan login gagal dalam 5 menit
- Setelah 5x gagal → lock 15 menit
- Reset otomatis setelah lock berakhir

### Lupa Password
- Hubungi admin (Manajemen User → reset)
- Atau akses `/setup` (reset password default)

---

## 📊 Dashboard

- **Welcome header** — greeting dinamis + tanggal
- **4 kartu statistik** — klik untuk filter status
- **Grafik** — distribusi kategori (bar) & status (donut)
- **Widget Patching** — progress patching kuartalan
- **Quick Access** — shortcut ke menu
- **Aset Terbaru** — 5 aset terbaru dengan foto
- **Aktivitas Terbaru** — timeline 8 aktivitas terakhir

---

## 🌙 Dark Mode

- Klik icon **moon** di navbar → dark mode aktif
- Klik icon **sun** → kembali ke light mode
- Preferensi disimpan di cookie (1 tahun)

---

## 🔍 Global Search

- Ketik di kotak **search** di navbar (kiri)
- Cari: aset (kode/nama), user (username/nama), kategori, jadwal patching
- Hasil dikelompokkan per kategori
- Klik hasil → langsung ke halaman terkait

---

## 📦 Manajemen Aset

### Daftar Aset (`/assets`)
- Tabel dengan foto, kode, nama, kategori, lokasi, status, harga (admin)
- Search + filter status/kategori + pagination
- Tombol **CSV** (export) + **Tambah Aset** (admin)

### Tambah Aset (admin)
- Form: foto, nama, kategori, brand, lokasi, status, tanggal beli, harga
- Kode aset auto-generate

### Detail Aset (`/assets/{id}`)
- Foto besar + info lengkap
- **QR Code** — scan untuk buka detail
- **Book Value** (depreciation) — nilai buku saat ini (admin)
- Tombol: Back, **Borrow** (jika tersedia), Edit, Delete (admin)
- Ubah status cepat + riwayat aktivitas

### Edit Aset (admin)
- Ubah data + ganti/hapus foto

### Hapus Aset (admin)
- Klik **Delete** → aset dipindahkan ke **Trash** (soft delete)
- Bisa dipulihkan dari Trash

---

## 📱 QR Code

- Setiap aset punya QR code di halaman detail
- QR berisi URL ke detail aset
- Scan dengan kamera HP → buka detail aset
- Bisa dicetak sebagai stiker

---

## 📤 Export & Import CSV

### Export CSV
- Di daftar aset → klik tombol **CSV**
- Download file CSV dengan filter aktif
- Format: asset_code, name, category, brand, location, status, date, price, currency

### Import CSV (admin)
- Menu **Import CSV** di sidebar
- Upload file CSV (format: name, category, brand_spec, location, status, purchase_date, price)
- Download template contoh
- Aset diimport otomatis dengan kode auto-generate

---

## 🗑 Soft Delete & Trash

### Hapus Aset (Soft Delete)
- Klik **Delete** di detail aset → aset dipindahkan ke Trash
- Tidak dihapus permanen, masih bisa dipulihkan
- Aset di trash tidak muncul di daftar aset normal

### Trash (`/assets/trash`) — admin
- Lihat semua aset yang di-soft-delete
- **Restore** — kembalikan aset ke daftar normal
- **Delete Permanently** — hapus selamanya (tidak bisa dibatalkan, foto juga dihapus)

---

## 💰 Depreciation & Multi-Currency

### Depreciation
- Dihitung otomatis di detail aset (admin)
- Metode: straight-line
- Rumus: (Harga - Nilai Residu 10%) / Umur Ekonomis (5 tahun)
- Menampilkan: Book Value, Accumulated Depreciation, Years Elapsed

### Multi-Currency
- Setiap aset punya kolom currency (IDR/USD/EUR)
- Harga ditampilkan sesuai format currency
- Default: IDR (Rupiah)

---

## 🤝 Peminjaman Aset (Borrowing)

### Pinjam Aset
- Di detail aset (status tersedia) → klik **Borrow**
- Isi: nama peminjam, expected return date, catatan
- Status aset otomatis → **dipinjam**

### Daftar Peminjaman (`/borrowings`)
- Tabel: aset, peminjam, tanggal pinjam, expected return, status
- **Overdue** — peminjaman lewat jatuh tempo (badge merah)

### Kembalikan Aset
- Klik **Return** di daftar peminjaman
- Status aset otomatis → **tersedia**
- Actual return date tercatat

---

## 🏷 Kategori (admin)

- CRUD kategori via modal inline
- Proteksi hapus bila masih dipakai aset

---

## 👥 Manajemen User (admin)

- CRUD user (admin/staff), aktif/nonaktif, ganti password
- **Activity by User** — klik user → lihat aktivitas yang dilakukan

---

## 👤 Profil & Activity

### Profil (`/profile`)
- Edit nama, email, password sendiri
- Username tidak bisa diubah

### Activity by User
- Lihat audit trail per user (apa yang dilakukan user tersebut)

---

## 🛡 Modul Patching

### Buat Jadwal (admin)
- Pilih kuartal (Q1-Q4) & tahun → tanggal auto-fill
- Generate checklist aset IT (exclude "Umum")

### Checklist per Aset
- 6 item standar: Update OS, Antivirus, Backup, Cek Log, Restart, Verifikasi
- Centang item via AJAX
- **Input kode patching** (mis: KB5079473) per item
- Status auto: pending → in_progress → completed

### Daftar Komputer Patching
- Tabel komputer + kode patching (badge)
- Matriks: komputer × item patching

---

## 📄 Modul Report

- 4 tab: Ringkasan, Per Kategori, Per Lokasi, Detail
- Filter: kategori, status, lokasi, tanggal
- **Cetak / PDF** — halaman print-friendly

---

## 📜 Riwayat & Audit Trail

### Riwayat Aktivitas (`/logs`)
- Log perubahan aset: created, updated, status, patching, dll

### Audit Trail (`/audit`) — admin
- Log semua modul: asset, user, kategori, token, borrowing
- Catat: user, aksi, deskripsi, IP, waktu
- Filter per modul + pagination

---

## 🔔 Notifikasi

- **Bell icon** di navbar dengan badge jumlah unread
- Auto-check patching overdue
- Klik → lihat daftar notifikasi
- Mark read / mark all read
- Klik link notifikasi → ke halaman terkait

---

## 🔑 API Token & REST API (admin)

### Generate Token
- Menu **API Token** di sidebar
- Klik **Generate Token** → token dibuat
- Token untuk autentikasi REST API

### REST API
```bash
GET /api/assets
Header: X-Api-Token: YOUR_TOKEN
# atau
GET /api/assets?token=YOUR_TOKEN
```
Response: JSON `{ "data": [...], "count": N }`

### Hapus Token
- Klik tombol hapus di daftar token

---

## 🌐 Multi-Bahasa

- Default: **English**
- Switcher: navbar (icon bendera) + tombol EN/ID di login
- Tersedia: English & Bahasa Indonesia
- Preferensi persisten (cookie 1 tahun)

---

## 📲 PWA

- Aplikasi **installable** di mobile/desktop
- Buka aplikasi di Chrome → icon install di address bar
- Service worker untuk offline cache

---

## 🔒 Admin vs Staff

| Fitur | Admin | Staff |
|-------|:-----:|:-----:|
| Dashboard, Dark Mode, Search | ✅ | ✅ |
| Lihat Aset, Export CSV | ✅ | ✅ |
| Lihat Harga, Depreciation | ✅ | ❌ |
| CRUD Aset, Foto | ✅ | ❌ |
| Trash, Import CSV | ✅ | ❌ |
| Borrow/Return | ✅ | ✅ |
| Kategori, User | ✅ | ❌ |
| Audit, API Token | ✅ | ❌ |
| Buat Jadwal Patching | ✅ | ❌ |
| Centang Checklist | ✅ | ✅ |
| Laporan, Riwayat, Notifikasi | ✅ | ✅ |
| Profil, Bahasa, PWA | ✅ | ✅ |

---

## 💡 Tips

- **Cepat ubah status**: detail aset → Quick Status Change
- **Cari aset**: global search di navbar
- **Export laporan**: CSV untuk data, PDF untuk cetak
- **Backup sebelum hapus permanen**: soft delete dulu, pastikan sebelum force delete
- **Scan QR**: print QR code sebagai stiker di aset fisik
- **API integration**: generate token untuk sync dengan sistem lain

---

<div align="center">

📖 Kembali ke **[README.md](README.md)** | Instalasi: **[INSTALL.md](INSTALL.md)**

</div>

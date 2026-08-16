# 📖 Panduan Penggunaan — AssetManager

Panduan lengkap cara menggunakan aplikasi AssetManager untuk mengelola aset IT & umum.

---

## 📋 Daftar Isi

- [Login & Logout](#-login--logout)
- [Dashboard](#-dashboard)
- [Manajemen Aset](#-manajemen-aset)
- [Kategori](#-kategori)
- [Manajemen User](#-manajemen-user)
- [Profil](#-profil)
- [Modul Patching](#-modul-patching)
- [Modul Report](#-modul-report)
- [Riwayat Aktivitas](#-riwayat-aktivitas)
- [Multi-Bahasa](#-multi-bahasa)
- [Peran Admin vs Staff](#-peran-admin-vs-staff)

---

## 🔐 Login & Logout

### Login
1. Buka aplikasi di browser → halaman login modern akan muncul
2. Masukkan **Username** dan **Password**
3. Klik tombol **Sign In** (atau tekan Enter)
4. Jika benar → redirect ke **Dashboard**
5. Jika salah → pesan error muncul

**Fitur halaman login:**
- **Toggle password** (icon mata) — klik untuk lihat/sembunyikan password
- **Remember me** — centang untuk tetap login
- **Language switcher** (EN/ID) — pilih bahasa di pojok kanan atas
- **Panel info** — menampilkan fitur-fitur aplikasi

### Logout
- Klik icon **logout** (panah keluar) di navbar kanan atas
- Konfirmasi → kembali ke halaman login

### Lupa password?
- Hubungi admin untuk reset via **Manajemen User**
- Atau akses `/setup` untuk reset password default (admin & staff)

---

## 📊 Dashboard

Dashboard adalah halaman utama setelah login. Menampilkan ringkasan aset.

### Welcome Header
- **Greeting dinamis** ("Good morning/afternoon/evening, [Nama]! 👋")
- **Tanggal hari ini** dengan format sesuai bahasa

### Kartu Statistik (4)
| Kartu | Warna | Fungsi |
|-------|-------|--------|
| Total Assets | Biru | Klik → lihat semua aset |
| Available | Hijau | Klik → filter aset tersedia |
| Borrowed | Kuning | Klik → filter aset dipinjam |
| Broken | Merah | Klik → filter aset rusak |

### Grafik
- **Asset Distribution by Category** (bar chart) — jumlah aset per kategori
- **Asset Status** (donut chart) — komposisi status dengan total di tengah

### Widget Patching
- **Active Schedules** — jumlah jadwal aktif
- **Total Checklists** — total checklist semua jadwal
- **Completed** — checklist selesai
- **Progress bar** — persentase completion

### Quick Access
Tombol shortcut ke menu:
- Assets, Patching, Reports, History
- (Admin tambahan: Add Asset, Categories, User Management)

### Aset Terbaru
Tabel 5 aset terbaru dengan foto thumbnail. Klik baris → detail aset.

### Aktivitas Terbaru
Timeline 8 aktivitas terbaru dengan:
- Icon berwarna per aksi (hijau=tersedia, kuning=dipinjam, merah=rusak, biru=created/updated/patching)
- Nama user, badge aksi, waktu, kode & nama aset, catatan

---

## 📦 Manajemen Aset

### Daftar Aset (`/assets`)
- **Tabel** semua aset dengan: foto, kode, nama, kategori, lokasi, status, harga (admin only), aksi
- **Pencarian**: ketik di kotak search (cari kode/nama/brand/lokasi)
- **Filter**: pilih status (tersedia/dipinjam/rusak) & kategori
- **Pagination**: 10 aset per halaman
- **Klik baris** → detail aset
- Tombol **Tambah Aset** (admin only)

### Tambah Aset (`/assets/create`) — Admin only
1. Klik **Tambah Aset**
2. Isi form:
   - **Kode Aset**: otomatis (AST-0001, dst) — tidak perlu isi
   - **Foto**: upload gambar (JPG/PNG/GIF/WebP, maks 5MB)
   - **Nama** *(wajib)*
   - **Kategori** *(wajib)* — pilih dari dropdown
   - **Brand / Spesifikasi**
   - **Lokasi**
   - **Status**: tersedia / dipinjam / rusak
   - **Tanggal Pembelian**
   - **Harga (Rp)**
3. Klik **Simpan**
4. Aset baru dibuat + log "created" tercatat

### Detail Aset (`/assets/{id}`)
- **Foto aset** besar di atas
- **Info lengkap**: kode, kategori, brand, lokasi, tanggal beli, harga (admin), dibuat, diperbarui
- **Tombol**: Kembali, Edit (admin), Hapus (admin)
- **Ubah Status Cepat**: pilih status + catatan → Simpan Status
- **Riwayat Aktivitas Aset**: timeline semua perubahan aset

### Edit Aset (`/assets/{id}/edit`) — Admin only
- Sama seperti form tambah, tapi data sudah terisi
- **Foto Saat Ini**: preview + tombol **Hapus Foto**
- **Upload foto baru** untuk ganti foto lama
- Klik **Simpan** untuk update

### Hapus Aset — Admin only
- Klik tombol **Hapus** di detail aset
- Konfirmasi → aset dihapus + foto dihapus dari disk
> Hapus aset juga menghapus semua riwayat log aset tersebut.

### Ubah Status Cepat
- Di detail aset, section "Ubah Status Cepat"
- Pilih status baru + catatan (opsional)
- Klik **Simpan Status**
- Status berubah + log tercatat

---

## 🏷 Kategori

### Daftar Kategori (`/categories`) — Admin only
- Tabel: nama, deskripsi, jumlah aset, tanggal dibuat, aksi
- Tombol **Tambah Kategori** → modal popup

### Tambah Kategori
1. Klik **Tambah Kategori**
2. Isi **Nama** *(wajib)* dan **Deskripsi**
3. Klik **Simpan**

### Edit Kategori
- Klik icon edit → modal dengan data terisi
- Ubah → **Simpan**

### Hapus Kategori
- Klik icon hapus → konfirmasi
- **Tidak bisa hapus** jika kategori masih dipakai aset
- Hapus semua aset dengan kategori tersebut dulu

---

## 👥 Manajemen User

### Daftar User (`/users`) — Admin only
- Tabel: nama, username, email, role, status (aktif/nonaktif), dibuat, aksi
- Tombol **Tambah User** → modal

### Tambah User
1. Klik **Tambah User**
2. Isi: Nama, Username, Email, Password, Role (admin/staff), Aktif
3. Klik **Simpan**

### Edit User
- Klik icon edit → modal
- Ubah data + password (kosongkan jika tidak ganti)
- Klik **Update**

### Hapus User
- Klik icon hapus → konfirmasi
- **Tidak bisa hapus akun sendiri**

### Nonaktifkan User
- Edit user → uncheck "Aktif" → Simpan
- User nonaktif tidak bisa login

---

## 👤 Profil

### Edit Profil (`/profile`)
- Semua user (admin & staff) bisa edit profil sendiri
- Ubah: Nama, Email, Password (kosongkan jika tidak ganti)
- Username tidak bisa diubah
- Klik **Simpan Perubahan**

---

## 🛡 Modul Patching

Modul untuk jadwal patching & maintenance aset IT setiap **3 bulan (kuartalan)**.

### Daftar Jadwal (`/patching`)
- Tabel: nama jadwal, periode (Q1-Q4), tanggal, status, progress, aksi
- Tombol **Buat Jadwal** (admin)

### Buat Jadwal — Admin only
1. Klik **Buat Jadwal**
2. Isi:
   - **Nama Jadwal** (mis: "Patching Q3 2026") — auto-generate dari kuartal
   - **Kuartal** (Q1-Q4) — pilih dari dropdown
   - **Tahun**
   - **Tanggal Mulai** — kosongkan = awal kuartal (auto)
   - **Batas Akhir** — kosongkan = akhir kuartal (auto)
   - **Status**: draft / ongoing / completed
   - **Deskripsi**
3. Klik **Simpan**
4. Redirect ke detail jadwal

> **Kuartal**: Q1=Jan-Mar, Q2=Apr-Jun, Q3=Jul-Sep, Q4=Okt-Des

### Detail Jadwal (`/patching/{id}`)
- **Statistik**: Total Aset, Selesai, Belum Selesai, Skipped
- **Info jadwal**: periode, rentang tanggal, status, progress bar
- **Generate Checklist**:
  - Pilih aset IT dari tabel → **Buat Checklist Terpilih**
  - Atau **Generate Semua Aset IT** (exclude kategori "Umum")
- **Tabel Checklist Aset**: kode, nama, kategori, lokasi, status, progress item, aksi
- Tombol **View Patch List** → daftar komputer + kode patching

### Checklist per Aset (`/patching/checklist/{id}`)
- **Info aset**: foto, nama, kode, kategori, lokasi, brand
- **Progress**: persentase & bar (X dari Y item selesai)
- **Daftar 6 Item Checklist**:
  1. Update Sistem Operasi / Firmware
  2. Update Antivirus / Security
  3. Backup Data
  4. Cek Log Sistem
  5. Restart Layanan
  6. Verifikasi Konektivitas
- **Centang item**: klik tombol kotak → item tercentang (hijau)
- **Input Kode Patching**: ketik kode (mis: KB5079473) → klik tombol save
  - Kode tersimpan & tampil sebagai badge
- **Aksi**: Kembali ke Jadwal, Skip Aset (dengan alasan), Reset Checklist (admin)

> Saat semua item tercentang → checklist otomatis berstatus **Selesai** + tercatat di riwayat.

### Daftar Komputer Patching (`/patching/{id}/computers`)
- **Tabel utama**: daftar komputer + kode patching (badge) + item terpatch
- **Tabel matriks**: komputer × item patching, setiap sel menampilkan kode patching
- Berguna untuk rekap kode patching semua komputer

### Status Otomatis
| Kondisi | Status Checklist | Status Jadwal |
|---------|-----------------|---------------|
| Baru generate | pending | draft |
| Ada item tercentang | in_progress | ongoing |
| Semua item tercentang | completed | ongoing |
| Semua checklist selesai | — | completed |
| Skip aset | skipped | — |

---

## 📄 Modul Report

### Halaman Laporan (`/reports`)
- **4 Tab**: Ringkasan, Per Kategori, Per Lokasi, Detail Aset
- **Filter**: kategori, status, lokasi, rentang tanggal pembelian
- **Tombol Reset Filter**

### Tab Ringkasan
- 4 kartu statistik (Total, Tersedia, Dipinjam, Rusak, Total Nilai)
- Grafik bar (distribusi kategori) & donut (status)
- Tabel ringkasan nilai aset per status

### Tab Per Kategori
- Tabel rekap: kategori, jumlah per status, total, nilai aset
- Footer total

### Tab Per Lokasi
- Tabel rekap: lokasi, jumlah per status, total, nilai aset

### Tab Detail Aset
- Tabel lengkap: foto, kode, nama, kategori, brand, lokasi, tanggal, status, harga
- Footer total nilai

### Cetak / PDF (`/reports/print`)
1. Klik tombol **Cetak / PDF** (hijau)
2. Tab baru terbuka → halaman print-friendly
3. Klik **Cetak / Simpan PDF** → dialog print browser
4. Pilih printer atau **Save as PDF**

**Isi halaman cetak:**
- Kop laporan (logo, judul, tanggal cetak, filter aktif)
- Ringkasan + kartu nilai
- Rekap per kategori
- Rekap per lokasi
- Daftar detail aset
- Tanda tangan
- Footer

> Nilai/harga hanya tampil untuk admin. Staff melihat laporan tanpa kolom harga.

---

## 📜 Riwayat Aktivitas

### Halaman Riwayat (`/logs`)
- Tabel: waktu, aset (kode+nama), aksi, pelaku, catatan
- Pagination (20 catatan per halaman)
- Semua perubahan aset tercatat: created, updated, status change, patching

### Aksi yang tercatat
| Aksi | Warna | Trigger |
|------|-------|---------|
| created | Biru | Aset baru ditambahkan |
| updated | Biru | Data aset diperbarui |
| tersedia | Hijau | Status diubah ke tersedia |
| dipinjam | Kuning | Status diubah ke dipinjam |
| rusak | Merah | Status diubah ke rusak |
| patching | Biru | Checklist patching selesai |
| perawatan | Abu | Maintenance |
| status_update | Abu | Update status via dashboard |

---

## 🌐 Multi-Bahasa

### Ganti Bahasa
- **Di navbar**: klik icon bahasa (🌐 EN/ID) → dropdown → pilih English / Bahasa Indonesia
- **Di halaman login**: klik tombol EN / ID di pojok kanan atas
- Preferensi tersimpan otomatis (cookie 1 tahun)

### Bahasa yang tersedia
| Kode | Bahasa | Default? |
|------|--------|----------|
| `en` | English | ✅ Ya |
| `id` | Bahasa Indonesia | — |

> Default aplikasi: **English**. Ganti ke Indonesia kapan saja.

---

## 🔒 Peran Admin vs Staff

### Admin (role: admin)
- **Semua fitur** tersedia
- CRUD aset, kategori, user
- Buat/edit/hapus jadwal patching
- Generate checklist
- Lihat harga & nilai aset
- Upload/hapus foto aset
- Akses menu: Dashboard, Aset, Riwayat, Laporan, Patching, **Kategori**, **Manajemen User**, Setup Password

### Staff (role: staff)
- **Lihat aset** (tanpa harga)
- **Ubah status aset** (tersedia/dipinjam/rusak)
- **Centang checklist patching** + input kode patching
- **Skip aset** di checklist
- Lihat laporan (tanpa harga)
- Lihat riwayat
- Edit profil sendiri
- Ganti bahasa
- **TIDAK BISA**: tambah/edit/hapus aset, kategori, user, jadwal patching, generate checklist, lihat harga

### Tampilan menu berbeda
| Menu | Admin | Staff |
|------|:-----:|:-----:|
| Dashboard | ✅ | ✅ |
| Aset | ✅ | ✅ |
| Riwayat | ✅ | ✅ |
| Laporan | ✅ | ✅ |
| Patching | ✅ | ✅ |
| Kategori | ✅ | ❌ |
| Manajemen User | ✅ | ❌ |
| Setup Password | ✅ | ❌ |
| Kolom Harga di tabel | ✅ | ❌ |

---

## 💡 Tips & Trik

### Cepat ubah status aset
- Buka detail aset → "Ubah Status Cepat" → pilih + catatan → Simpan
- Tidak perlu edit full form

### Cari aset spesifik
- Gunakan kotak search di daftar aset (cari kode/nama/brand/lokasi)
- Kombinasi dengan filter status & kategori

### Generate semua checklist sekaligus
- Di detail jadwal → klik **Generate Semua Aset IT**
- Semua aset IT (bukan "Umum") langsung dapat checklist

### Input kode patching KB
- Di checklist per aset → ketik kode (mis: KB5079473) di input per item
- Klik tombol save (icon disket)
- Kode tersimpan & tampil di Daftar Komputer Patching

### Cetak laporan sebagai PDF
- Buka laporan → filter sesuai kebutuhan → Cetak / PDF
- Pilih "Save as PDF" di dialog print browser

### Backup sebelum hapus
- Sebelum hapus aset, pastikan tidak ada data penting
- Hapus aset = hapus foto + semua riwayat log aset tersebut

---

<div align="center">

📖 Kembali ke **[README.md](README.md)** | Instalasi: **[INSTALL.md](INSTALL.md)**

</div>

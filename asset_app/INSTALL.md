# 📦 Panduan Instalasi — AssetManager

Panduan lengkap instalasi aplikasi AssetManager dari nol hingga produksi.

---

## 📋 Daftar Isi

- [Persyaratan Sistem](#-persyaratan-sistem)
- [Opsi A: Mode Produksi (MySQL)](#-opsi-a-mode-produksi-mysql)
- [Opsi B: Deploy Apache](#-opsi-b-deploy-apache)
- [Opsi C: Deploy Nginx](#-opsi-c-deploy-nginx)
- [Opsi D: Deploy Docker](#-opsi-d-deploy-docker)
- [Setup Password](#-setup-password)
- [Konfigurasi](#-konfigurasi)
- [Verifikasi Instalasi](#-verifikasi-instalasi)
- [Update Versi Baru](#-update-versi-baru)
- [Backup & Restore](#-backup--restore)

---

## ✅ Persyaratan Sistem

| Komponen | Versi | Wajib |
|----------|-------|-------|
| PHP | 8.0+ | ✅ |
| PDO MySQL | extension | ✅ |
| MySQL / MariaDB | 5.7+ / 10.3+ | ✅ |
| Browser modern | Chrome/Firefox/Edge | ✅ |

> Aplikasi hanya mendukung MySQL. Koneksi demo SQLite sudah dihapus.

### Ekstensi PHP
```bash
php -m | grep -E "pdo|mysql|mbstring|fileinfo|json"
```
Dibutuhkan: `pdo`, `pdo_mysql`, `mbstring`, `fileinfo`, `json`.

### Folder writable
- `public/uploads/assets/` — untuk upload foto aset (chmod 775)

> ⚠️ Bila deploy di web server (XAMPP/Apache/Nginx), pastikan user web server (`daemon`/`nobody`/`www-data`) bisa menulis ke folder di atas.
> ```bash
> chmod -R 775 public/uploads/assets
> chown -R daemon:daemon public/uploads/assets   # XAMPP Linux
> ```

---

## 🅰 Opsi A: Mode Produksi (MySQL)

### 1. Buat database
```bash
mysql -u root -p
```
```sql
CREATE DATABASE assets_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- (opsional) bila tidak pakai root:
-- CREATE USER 'asset_user'@'localhost' IDENTIFIED BY 'password_kuat';
-- GRANT ALL PRIVILEGES ON assets_app.* TO 'asset_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

> Catatan: skema + data seed (kategori, users, aset, log, patch_items) sudah ada di `database/assets_app.sql`.
> Bila database kosong, aplikasi akan **mengimpor skema tersebut otomatis** saat pertama dijalankan (via `ensureSchema()`).

### 2. Import skema (opsional — otomatis bila DB kosong)
```bash
mysql -u root -p assets_app < database/assets_app.sql
```

### 3. Set konfigurasi (via file `.env` — paling mudah di XAMPP)
Salin template lalu sesuaikan:
```bash
cp .env.example .env
```
Isi `.env` (default XAMPP: root tanpa password):
```ini
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=assets_app
DB_USER=root
DB_PASS=
```
Atau via env var OS:
```bash
export DB_DRIVER=mysql
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=assets_app
export DB_USER=root
export DB_PASS=
```

### 4. Jalankan
```bash
php -S 0.0.0.0:8080 -t public public/index.php
```
(XAMPP Apache: taruh folder `asset_app` di `htdocs`, buka `http://localhost/asset_app/public/`)

### 5. Setup password
Akses `http://localhost:8080/setup` sekali untuk reset password default (skema awal menyimpan hash non-bcrypt yang harus di-reset).

### 6. Login & ganti password
Login `admin` / `admin123` → Manajemen User → ganti password.

> Semua tabel fitur baru (audit_trail, api_tokens, borrowings, notifications) auto-migrate via `migrateExtended()`.

---

## 🅱 Opsi B: Deploy Apache

```bash
sudo cp -r asset_app /var/www/assetmanager
sudo chown -R www-data:www-data /var/www/assetmanager
sudo chmod -R 775 /var/www/assetmanager/public/uploads
```

VirtualHost (DB config juga bisa via file `.env` di root app):
```apache
<VirtualHost *:80>
    ServerName asset.yourdomain.com
    DocumentRoot /var/www/assetmanager/public
    <Directory /var/www/assetmanager/public>
        AllowOverride All
        Require all granted
    </Directory>
    SetEnv DB_DRIVER mysql
    SetEnv DB_HOST 127.0.0.1
    SetEnv DB_NAME assets_app
    SetEnv DB_USER root
    SetEnv DB_PASS=
</VirtualHost>
```

```bash
sudo a2enmod rewrite
sudo a2ensite assetmanager
sudo systemctl reload apache2
```

---

## 🅲 Opsi C: Deploy Nginx

```nginx
server {
    listen 80;
    server_name asset.yourdomain.com;
    root /var/www/assetmanager/public;
    index index.php;
    client_max_body_size 6M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DB_DRIVER mysql;
        fastcgi_param DB_HOST 127.0.0.1;
        fastcgi_param DB_NAME assets_app;
        fastcgi_param DB_USER root;
        fastcgi_param DB_PASS;
        include fastcgi_params;
    }
    location ~ /\.(sql|md|log)$ { deny all; }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/assetmanager /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

---

## 🅳 Opsi D: Deploy Docker

```dockerfile
FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY . /var/www/html/
RUN a2enmod rewrite
RUN chown -R www-data:www-data /var/www/html/public/uploads
RUN chmod -R 775 /var/www/html/public/uploads
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
```

```bash
docker build -t assetmanager .
docker run -d -p 8080:80 \
  -e DB_DRIVER=mysql -e DB_HOST=db-host -e DB_NAME=assets_app \
  -e DB_USER=root -e DB_PASS=secret \
  --name assetmanager assetmanager
```

---

## 🔑 Setup Password

3 cara setelah instalasi MySQL:

1. **Route `/setup`** — buka `http://localhost:8080/setup` (paling mudah)
2. **PHP CLI**: `php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"`
3. **Via aplikasi** — login → Manajemen User → Edit → ganti password

---

## ⚙️ Konfigurasi

Konfigurasi bisa via file `.env` (di root app) atau env var OS. Lihat `.env.example`.

| Variable | Default | Keterangan |
|----------|---------|-----------|
| `DB_DRIVER` | `mysql` | Hanya `mysql` (SQLite demo dihapus) |
| `DB_HOST` | `127.0.0.1` | Host MySQL |
| `DB_PORT` | `3306` | Port MySQL |
| `DB_NAME` | `assets_app` | Nama database |
| `DB_USER` | `root` | User database |
| `DB_PASS` | (kosong) | Password database |
| `APP_BASE_URL` | (auto) | Base URL sub-folder |

### Upload foto
- Maks 5MB, format JPG/PNG/GIF/WebP
- Lokasi: `public/uploads/assets/`
- Edit limit di `app/models/Asset.php` (`handleUpload()`)

### Dark Mode
- Toggle via navbar (icon moon/sun)
- Persisten via cookie `dark_mode` (1 tahun)

---

## ✔️ Verifikasi Instalasi

1. ✅ Login terbuka & berhasil
2. ✅ Dashboard menampilkan statistik + grafik
3. ✅ Bisa tambah aset + upload foto
4. ✅ QR code tampil di detail aset
5. ✅ Bisa buat jadwal patching & checklist
6. ✅ Buka laporan & cetak PDF
7. ✅ Export CSV berfungsi
8. ✅ Dark mode toggle berfungsi
9. ✅ Global search berfungsi
10. ✅ Notifikasi muncul di navbar
11. ✅ Borrow aset berfungsi
12. ✅ Trash & restore berfungsi
13. ✅ API token: `GET /api/assets?token=XXX` → JSON

---

## 🔄 Update Versi Baru

```bash
git pull origin main
# Tabel baru auto-migrate (CREATE TABLE IF NOT EXISTS)
# Data lama tidak terhapus
php -S 0.0.0.0:8080 -t public public/index.php
```

---

## 💾 Backup & Restore

### MySQL
```bash
mysqldump -u root -p assets_app > backup.sql
mysql -u root -p assets_app < backup.sql
```

### Foto aset
```bash
tar -czf backup_photos.tar.gz public/uploads/assets/
```

---

## ❓ Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Could not connect MySQL | Cek MySQL berjalan & kredensial (`.env` atau env var) |
| "PHP tidak ditemukan" | `sudo apt install php php-cli php-mysql php-mbstring` |
| Permission denied uploads | `chmod -R 775 public/uploads/` |
| Login gagal (hash invalid) | Buka `http://localhost:8080/setup` sekali untuk reset password bcrypt |
| 404 semua halaman | Akses via `php -S ... public/index.php` |
| Login locked | Rate limit — tunggu 15 menit atau `/setup` |
| API 401 | Sertakan token di header `X-Api-Token` atau `?token=` |
| Grafik kosong | Cek koneksi internet (CDN) |


---

<div align="center">

📖 Kembali ke **[README.md](README.md)** | Penggunaan: **[USER_GUIDE.md](USER_GUIDE.md)**

</div>

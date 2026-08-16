# 📦 Panduan Instalasi — AssetManager

Panduan lengkap instalasi aplikasi AssetManager dari nol hingga produksi.

---

## 📋 Daftar Isi

- [Persyaratan Sistem](#-persyaratan-sistem)
- [Opsi A: Mode Demo (SQLite)](#-opsi-a-mode-demo-sqlite)
- [Opsi B: Mode Produksi (MySQL)](#-opsi-b-mode-produksi-mysql)
- [Opsi C: Deploy Apache](#-opsi-c-deploy-apache)
- [Opsi D: Deploy Nginx](#-opsi-d-deploy-nginx)
- [Opsi E: Deploy Docker](#-opsi-e-deploy-docker)
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
| PDO SQLite | bundled | Demo |
| PDO MySQL | extension | Produksi |
| Browser modern | Chrome/Firefox/Edge | ✅ |

### Ekstensi PHP
```bash
php -m | grep -E "pdo|sqlite|mbstring|fileinfo|json"
```
Dibutuhkan: `pdo`, `pdo_sqlite` (demo), `pdo_mysql` (produksi), `mbstring`, `fileinfo`, `json`.

### Folder writable
- `public/uploads/assets/` — untuk upload foto aset (chmod 775)

---

## 🅰 Opsi A: Mode Demo (SQLite)

> Tanpa MySQL, langsung jalan. Cocok untuk testing & demo.

```bash
git clone https://github.com/antono4/AssetManager.git
cd AssetManager/asset_app
php -S 0.0.0.0:8080 -t public public/index.php
```

Buka `http://localhost:8080` → login `admin` / `admin123`.

> Database SQLite + semua tabel (assets, users, patching, audit, borrow, notif, tokens) + data dummy **otomatis dibuat** saat pertama dijalankan.

---

## 🅱 Opsi B: Mode Produksi (MySQL)

### 1. Buat database
```bash
mysql -u root -p
```
```sql
CREATE DATABASE asset_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'asset_user'@'localhost' IDENTIFIED BY 'password_kuat';
GRANT ALL PRIVILEGES ON asset_db.* TO 'asset_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Import skema
```bash
mysql -u asset_user -p asset_db < database/assets_app.sql
```

### 3. Set konfigurasi
```bash
export DB_DRIVER=mysql
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=asset_db
export DB_USER=asset_user
export DB_PASS=password_kuat
```

### 4. Jalankan
```bash
php -S 0.0.0.0:8080 -t public public/index.php
```

### 5. Setup password
Akses `http://localhost:8080/setup` sekali untuk reset password default.

### 6. Login & ganti password
Login `admin` / `admin123` → Manajemen User → ganti password.

> Semua tabel fitur baru (audit_trail, api_tokens, borrowings, notifications) auto-migrate via `migrateExtended()`.

---

## 🅲 Opsi C: Deploy Apache

```bash
sudo cp -r asset_app /var/www/assetmanager
sudo chown -R www-data:www-data /var/www/assetmanager
sudo chmod -R 775 /var/www/assetmanager/public/uploads
```

VirtualHost:
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
    SetEnv DB_NAME asset_db
    SetEnv DB_USER asset_user
    SetEnv DB_PASS password_kuat
</VirtualHost>
```

```bash
sudo a2enmod rewrite
sudo a2ensite assetmanager
sudo systemctl reload apache2
```

---

## 🅳 Opsi D: Deploy Nginx

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
        fastcgi_param DB_NAME asset_db;
        fastcgi_param DB_USER asset_user;
        fastcgi_param DB_PASS password_kuat;
        include fastcgi_params;
    }
    location ~ /\.(sql|sqlite|md|log)$ { deny all; }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/assetmanager /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

---

## 🅴 Opsi E: Deploy Docker

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
  -e DB_DRIVER=mysql -e DB_HOST=db-host -e DB_NAME=asset_db \
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

| Variable | Default | Keterangan |
|----------|---------|-----------|
| `DB_DRIVER` | `sqlite` | `sqlite` / `mysql` |
| `DB_HOST` | `127.0.0.1` | Host MySQL |
| `DB_PORT` | `3306` | Port MySQL |
| `DB_NAME` | `asset_db` | Nama database |
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

### SQLite (demo)
```bash
git pull origin main
rm database/asset_db.sqlite  # HATI-HATI: hapus data!
php -S 0.0.0.0:8080 -t public public/index.php
```

### MySQL (produksi)
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
mysqldump -u root -p asset_db > backup.sql
mysql -u root -p asset_db < backup.sql
```

### SQLite
```bash
cp database/asset_db.sqlite backup_$(date +%Y%m%d).sqlite
cp backup.sqlite database/asset_db.sqlite
```

### Foto aset
```bash
tar -czf backup_photos.tar.gz public/uploads/assets/
```

---

## ❓ Troubleshooting

| Masalah | Solusi |
|---------|--------|
| "PHP tidak ditemukan" | `sudo apt install php php-cli php-sqlite3 php-mysql php-mbstring` |
| Permission denied uploads | `chmod -R 775 public/uploads/` |
| Could not connect MySQL | Cek MySQL berjalan & kredensial |
| 404 semua halaman | Akses via `php -S ... public/index.php` |
| Login locked | Rate limit — tunggu 15 menit atau `/setup` |
| API 401 | Sertakan token di header `X-Api-Token` atau `?token=` |
| Grafik kosong | Cek koneksi internet (CDN) |

---

<div align="center">

📖 Kembali ke **[README.md](README.md)** | Penggunaan: **[USER_GUIDE.md](USER_GUIDE.md)**

</div>

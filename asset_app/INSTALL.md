# 📦 Panduan Instalasi — AssetManager

Panduan lengkap instalasi aplikasi AssetManager dari nol hingga berjalan.

---

## 📋 Daftar Isi

- [Persyaratan Sistem](#-persyaratan-sistem)
- [Opsi A: Mode Demo (SQLite)](#-opsi-a-mode-demo-sqlite)
- [Opsi B: Mode Produksi (MySQL)](#-opsi-b-mode-produksi-mysql)
- [Opsi C: Deploy ke Web Server (Apache/Nginx)](#-opsi-c-deploy-ke-web-server-apachenginx)
- [Setup Password](#-setup-password)
- [Konfigurasi Lanjutan](#-konfigurasi-lanjutan)
- [Verifikasi Instalasi](#-verifikasi-instalasi)
- [Update/Aplikasi Versi Baru](#-updateaplikasi-versi-baru)
- [Backup & Restore](#-backup--restore)

---

## ✅ Persyaratan Sistem

### Minimal
| Komponen | Versi | Wajib? |
|----------|-------|--------|
| PHP | 8.0+ | ✅ Wajib |
| PDO SQLite | (bundled PHP) | Mode demo |
| PDO MySQL | (extension) | Mode produksi |
| Web Browser | Modern (Chrome/Firefox/Edge) | ✅ Wajib |

### Ekstensi PHP yang dibutuhkan
```bash
php -m | grep -E "pdo|sqlite|mbstring|fileinfo|json"
```
Pastikan ada: `pdo`, `pdo_sqlite` (demo), `pdo_mysql` (produksi), `mbstring`, `fileinfo`, `json`.

### Untuk upload foto
- PHP `fileinfo` extension (cek MIME type)
- Folder `public/uploads/assets/` harus writable (chmod 775)

---

## 🅰 Opsi A: Mode Demo (SQLite)

> Mode paling cepat — tanpa instalasi MySQL, langsung jalan. Cocok untuk testing & demo.

### Langkah 1: Download/Clone proyek
```bash
git clone https://github.com/antono4/AssetManager.git
cd AssetManager/asset_app
```

### Langkah 2: Jalankan PHP built-in server
```bash
php -S 0.0.0.0:8080 -t public public/index.php
```

### Langkah 3: Buka browser
```
http://localhost:8080
```

### Langkah 4: Login
- Username: `admin`
- Password: `admin123`

### Selesai! 🎉

> Database SQLite & data dummy (5 kategori, 2 user, 10 aset, 6 log) **otomatis dibuat** di `database/asset_db.sqlite` saat pertama dijalankan.

---

## 🅱 Opsi B: Mode Produksi (MySQL)

> Untuk penggunaan nyata dengan database MySQL/MariaDB.

### Langkah 1: Install & pastikan MySQL berjalan
```bash
mysql --version
sudo systemctl status mysql   # atau mariadb
```

### Langkah 2: Buat database
```bash
mysql -u root -p
```
```sql
CREATE DATABASE asset_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'asset_user'@'localhost' IDENTIFIED BY 'password_kuat_anda';
GRANT ALL PRIVILEGES ON asset_db.* TO 'asset_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Langkah 3: Import skema database
```bash
mysql -u asset_user -p asset_db < database/assets_app.sql
```

### Langkah 4: Set konfigurasi via environment variable
```bash
export DB_DRIVER=mysql
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=asset_db
export DB_USER=asset_user
export DB_PASS=password_kuat_anda
```

Atau edit langsung `config.php`:
```php
define('DB_DRIVER', 'mysql');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'asset_db');
define('DB_USER', 'asset_user');
define('DB_PASS', 'password_kuat_anda');
```

### Langkah 5: Jalankan
```bash
php -S 0.0.0.0:8080 -t public public/index.php
```

### Langkah 6: Setup password
Karena hash bcrypt di `assets_app.sql` adalah placeholder, akses:
```
http://localhost:8080/setup
```
Ini akan reset password admin & staff ke default yang valid.

### Langkah 7: Login & ganti password
- Login dengan `admin` / `admin123`
- Buka **Manajemen User** → ganti password admin & staff dengan password kuat

### Selesai! 🎉

---

## 🅲 Opsi C: Deploy ke Web Server (Apache/Nginx)

> Untuk deploy ke server produksi dengan Apache atau Nginx.

### Deploy dengan Apache

#### 1. Copy proyek ke web root
```bash
sudo cp -r asset_app /var/www/assetmanager
sudo chown -R www-data:www-data /var/www/assetmanager
sudo chmod -R 775 /var/www/assetmanager/public/uploads
```

#### 2. Konfigurasi VirtualHost
```apache
# /etc/apache2/sites-available/assetmanager.conf
<VirtualHost *:80>
    ServerName asset.yourdomain.com
    DocumentRoot /var/www/assetmanager/public

    <Directory /var/www/assetmanager/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/assetmanager_error.log
    CustomLog ${APACHE_LOG_DIR}/assetmanager_access.log combined
</VirtualHost>
```

#### 3. Aktifkan
```bash
sudo a2enmod rewrite
sudo a2ensite assetmanager
sudo systemctl reload apache2
```

#### 4. Set environment (Apache)
```apache
<VirtualHost *:80>
    ...
    SetEnv DB_DRIVER mysql
    SetEnv DB_HOST 127.0.0.1
    SetEnv DB_NAME asset_db
    SetEnv DB_USER asset_user
    SetEnv DB_PASS password_kuat_anda
</VirtualHost>
```

File `.htaccess` sudah disediakan di `public/.htaccess` (rewrite semua request ke `index.php`).

---

### Deploy dengan Nginx + PHP-FPM

#### 1. Install PHP-FPM
```bash
sudo apt install php-fpm php-mysql php-mbstring php-xml
```

#### 2. Konfigurasi Nginx
```nginx
# /etc/nginx/sites-available/assetmanager
server {
    listen 80;
    server_name asset.yourdomain.com;
    root /var/www/assetmanager/public;
    index index.php;

    # Upload foto - maks 6MB
    client_max_body_size 6M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Block akses file sensitif
    location ~ /\.(sql|sqlite|md|log)$ {
        deny all;
    }
    location ~ /uploads/ {
        # allow
    }
}
```

#### 3. Aktifkan
```bash
sudo ln -s /etc/nginx/sites-available/assetmanager /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 4. Set environment (Nginx → PHP-FPM)
Tambahkan di block `location ~ \.php$`:
```nginx
fastcgi_param DB_DRIVER mysql;
fastcgi_param DB_HOST 127.0.0.1;
fastcgi_param DB_NAME asset_db;
fastcgi_param DB_USER asset_user;
fastcgi_param DB_PASS password_kuat_anda;
```

---

### Deploy dengan Docker (opsional)

#### Dockerfile
```dockerfile
FROM php:8.2-apache

# Install ekstensi
RUN docker-php-ext-install pdo pdo_mysql

# Copy proyek
COPY . /var/www/html/
COPY public/.htaccess /var/www/html/public/.htaccess

# Enable mod_rewrite
RUN a2enmod rewrite

# Set permission
RUN chown -R www-data:www-data /var/www/html/public/uploads
RUN chmod -R 775 /var/www/html/public/uploads

# DocumentRoot ke public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
```

#### Jalankan
```bash
docker build -t assetmanager .
docker run -d -p 8080:80 \
  -e DB_DRIVER=mysql \
  -e DB_HOST=db-host \
  -e DB_NAME=asset_db \
  -e DB_USER=root \
  -e DB_PASS=secret \
  --name assetmanager assetmanager
```

---

## 🔑 Setup Password

Setelah instalasi MySQL, hash password di database adalah placeholder. Ada 3 cara untuk fix:

### Cara 1: Route `/setup` (paling mudah)
```
http://localhost:8080/setup
```
Halaman akan reset password `admin` → `admin123` dan `staff` → `staff123` ke hash bcrypt yang valid.

### Cara 2: Via PHP CLI
```bash
php -r "echo password_hash('admin123', PASSWORD_BCRYPT);" | xargs -I{} \
  mysql -u root -p asset_db -e "UPDATE users SET password='{}' WHERE username='admin';"
```

### Cara 3: Via aplikasi (sudah login)
1. Login sebagai admin
2. Buka **Manajemen User**
3. Edit user → isi password baru → Simpan

---

## ⚙️ Konfigurasi Lanjutan

### Semua environment variable

| Variable | Default | Keterangan |
|----------|---------|-----------|
| `DB_DRIVER` | `sqlite` | `sqlite` atau `mysql` |
| `DB_HOST` | `127.0.0.1` | Host MySQL |
| `DB_PORT` | `3306` | Port MySQL |
| `DB_NAME` | `asset_db` | Nama database |
| `DB_USER` | `root` | User database |
| `DB_PASS` | (kosong) | Password database |
| `APP_BASE_URL` | (auto-detect) | Base URL untuk sub-folder deploy |

### Timezone
Default `Asia/Jakarta`. Ubah di `config.php`:
```php
date_default_timezone_set('Asia/Jakarta');  // ganti sesuai timezone Anda
```

### Upload foto
- Maks ukuran: 5MB (bisa ubah di `app/models/Asset.php`)
- Format: JPG, PNG, GIF, WebP
- Lokasi: `public/uploads/assets/`
- Nama file: `asset_Ymd_His_random.ext` (unik)

---

## ✔️ Verifikasi Instalasi

Setelah instalasi, pastikan:

1. ✅ Halaman login terbuka (`http://localhost:8080`)
2. ✅ Login dengan admin/admin123 berhasil → redirect dashboard
3. ✅ Dashboard menampilkan statistik & grafik
4. ✅ Bisa tambah aset baru dengan foto
5. ✅ Bisa buat jadwal patching & generate checklist
6. ✅ Bisa akses laporan & cetak
7. ✅ Bisa ganti bahasa (EN/ID)
8. ✅ Staff login tidak melihat harga

---

## 🔄 Update/Aplikasi Versi Baru

### Mode SQLite (demo)
```bash
git pull origin main
# Hapus DB lama untuk re-seed (HATI-HATI: hapus data!)
rm database/asset_db.sqlite
php -S 0.0.0.0:8080 -t public public/index.php
```

### Mode MySQL (produksi)
```bash
git pull origin main
# Tabel baru otomatis di-migrate (idempoten, CREATE TABLE IF NOT EXISTS)
# Tidak perlu import SQL ulang
php -S 0.0.0.0:8080 -t public public/index.php
```

> Aplikasi punya sistem migrasi otomatis (`migratePatching()` di Database.php) yang menambah tabel baru tanpa menghapus data lama.

---

## 💾 Backup & Restore

### MySQL
```bash
# Backup
mysqldump -u root -p asset_db > backup_asset_db.sql

# Restore
mysql -u root -p asset_db < backup_asset_db.sql
```

### SQLite
```bash
# Backup (copy file)
cp database/asset_db.sqlite backup_$(date +%Y%m%d).sqlite

# Restore
cp backup_20260816.sqlite database/asset_db.sqlite
```

### Backup foto aset
```bash
# Foto disimpan di public/uploads/assets/
tar -czf backup_photos.tar.gz public/uploads/assets/
```

---

## ❓ Troubleshooting Instalasi

### "PHP tidak ditemukan"
Install PHP 8.0+:
```bash
sudo apt install php php-cli php-sqlite3 php-mysql php-mbstring php-xml
```

### "Permission denied" pada uploads
```bash
chmod -R 775 public/uploads/assets/
chown -R www-data:www-data public/uploads/  # untuk Apache/Nginx
```

### "Could not connect to MySQL"
- Cek MySQL berjalan: `sudo systemctl status mysql`
- Cek kredensial: `mysql -u asset_user -p`
- Cek port: `mysql -u asset_user -h 127.0.0.1 -P 3306 -p`

### "Page not found" (404 semua halaman)
- Pastikan akses via router: `php -S ... public/index.php`
- Untuk Apache: pastikan `mod_rewrite` aktif & `.htaccess` ada
- Untuk Nginx: pastikan `try_files` mengarah ke `index.php`

### Grafik dashboard tidak muncul
- Pastikan koneksi internet (CDN ApexCharts, AdminLTE, Font Awesome)
- Atau download asset lokal & ubah URL di layout

---

<div align="center">

📖 Kembali ke **[README.md](README.md)** | Panduan Penggunaan: **[USER_GUIDE.md](USER_GUIDE.md)**

</div>

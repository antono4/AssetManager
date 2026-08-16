#!/usr/bin/env python3
"""Generate XAMPP Installation Guide PDF for AssetManager"""
from fpdf import FPDF
import os

class GuidePDF(FPDF):
    def header(self):
        if self.page_no() == 1:
            return
        self.set_fill_color(43, 58, 85)
        self.rect(0, 0, 210, 25, 'F')
        self.set_font('Helvetica', 'B', 11)
        self.set_text_color(255, 255, 255)
        self.set_xy(15, 8)
        self.cell(0, 10, 'AssetManager - Panduan Instalasi XAMPP', 0, 1, 'L')
        self.set_text_color(0, 0, 0)

    def footer(self):
        self.set_y(-15)
        self.set_font('Helvetica', 'I', 8)
        self.set_text_color(136, 152, 170)
        self.cell(0, 10, f'Halaman {self.page_no()}/{{nb}}  -  AssetManager v1.0  -  MIT License', 0, 0, 'C')
        self.set_text_color(0, 0, 0)

    def section_title(self, title):
        self.ln(4)
        self.set_font('Helvetica', 'B', 14)
        self.set_text_color(43, 58, 85)
        self.set_fill_color(238, 241, 246)
        self.cell(0, 10, f'  {title}', 0, 1, 'L', True)
        self.set_text_color(0, 0, 0)
        self.ln(3)

    def sub_title(self, title):
        self.ln(2)
        self.set_font('Helvetica', 'B', 11)
        self.set_text_color(58, 93, 221)
        self.cell(0, 7, title, 0, 1, 'L')
        self.set_text_color(0, 0, 0)

    def body(self, text):
        self.set_font('Helvetica', '', 10)
        self.set_text_color(51, 51, 51)
        self.multi_cell(0, 6, text)
        self.ln(1)

    def code(self, text):
        self.set_font('Courier', '', 9)
        self.set_text_color(0, 110, 0)
        self.set_fill_color(240, 245, 255)
        self.multi_cell(0, 6, text, fill=True, border=1)
        self.ln(2)
        self.set_text_color(0, 0, 0)

    def step(self, num, title):
        self.ln(3)
        self.set_font('Helvetica', 'B', 12)
        self.set_text_color(255, 255, 255)
        self.set_fill_color(58, 93, 221)
        self.cell(8, 8, f' {num} ', 0, 0, 'C', True)
        self.set_text_color(43, 58, 85)
        self.set_font('Helvetica', 'B', 11)
        self.cell(0, 8, f'  {title}', 0, 1, 'L')
        self.set_text_color(0, 0, 0)
        self.ln(1)

    def note_box(self, text, label='Catatan'):
        self.ln(1)
        self.set_font('Helvetica', 'B', 9)
        self.set_fill_color(255, 248, 220)
        self.set_draw_color(240, 200, 80)
        self.cell(0, 6, f'  ! {label}:', 0, 1, 'L', True)
        self.set_font('Helvetica', '', 9)
        self.set_text_color(120, 90, 20)
        self.multi_cell(0, 5, f'  {text}', border=1, fill=True)
        self.ln(2)
        self.set_text_color(0, 0, 0)

    def info_box(self, text):
        self.ln(1)
        self.set_font('Helvetica', '', 9)
        self.set_fill_color(230, 243, 255)
        self.set_draw_color(79, 124, 255)
        self.set_text_color(30, 58, 95)
        self.multi_cell(0, 6, f'  i {text}', border=1, fill=True)
        self.ln(2)
        self.set_text_color(0, 0, 0)

    def table_row(self, col1, col2, header=False):
        if header:
            self.set_font('Helvetica', 'B', 9)
            self.set_fill_color(43, 58, 85)
            self.set_text_color(255, 255, 255)
        else:
            self.set_font('Helvetica', '', 9)
            self.set_fill_color(248, 250, 252)
            self.set_text_color(51, 51, 51)
        self.cell(50, 7, col1, 1, 0, 'L', True)
        self.cell(0, 7, col2, 1, 1, 'L', True)
        self.set_text_color(0, 0, 0)

pdf = GuidePDF()
pdf.alias_nb_pages()
pdf.set_auto_page_break(auto=True, margin=20)
pdf.set_margins(15, 15, 15)

# ===== COVER PAGE =====
pdf.add_page()
pdf.set_fill_color(15, 23, 41)
pdf.rect(0, 0, 210, 297, 'F')
pdf.set_font('Helvetica', 'B', 40)
pdf.set_text_color(79, 124, 255)
pdf.set_xy(0, 80)
pdf.cell(0, 15, 'AM', 0, 1, 'C')
pdf.set_xy(0, 110)
pdf.set_font('Helvetica', 'B', 26)
pdf.set_text_color(255, 255, 255)
pdf.cell(0, 12, 'AssetManager', 0, 1, 'C')
pdf.set_font('Helvetica', '', 14)
pdf.set_text_color(158, 193, 255)
pdf.cell(0, 8, 'Panduan Instalasi XAMPP', 0, 1, 'C')
pdf.set_font('Helvetica', '', 11)
pdf.set_text_color(108, 122, 140)
pdf.cell(0, 6, 'Aplikasi Manajemen Aset IT & Umum', 0, 1, 'C')
pdf.ln(5)
pdf.set_text_color(0, 212, 168)
pdf.set_font('Helvetica', '', 11)
pdf.cell(0, 6, 'Step-by-step complete guide', 0, 1, 'C')
pdf.ln(30)
pdf.set_font('Helvetica', 'B', 9)
pdf.set_text_color(255, 255, 255)
for label, x in [('PHP 8+', 45), ('MySQL', 80), ('AdminLTE 3', 110), ('XAMPP', 145)]:
    pdf.set_fill_color(30, 40, 60)
    pdf.set_xy(x, pdf.get_y())
    pdf.cell(25, 7, label, 0, 0, 'C', True)
pdf.ln(30)
pdf.set_font('Helvetica', '', 9)
pdf.set_text_color(90, 100, 120)
pdf.cell(0, 6, 'Version 1.0  -  2026  -  MIT License', 0, 1, 'C')
pdf.cell(0, 6, 'https://github.com/antono4/AssetManager', 0, 1, 'C')

# ===== TABLE OF CONTENTS =====
pdf.add_page()
pdf.section_title('Daftar Isi')
toc = [
    ('1', 'Persiapan'),
    ('2', 'Download & Install XAMPP'),
    ('3', 'Download AssetManager'),
    ('4', 'Copy ke Folder htdocs'),
    ('5', 'Start Apache & MySQL'),
    ('6', 'Buat Database via phpMyAdmin'),
    ('7', 'Import Skema Database'),
    ('8', 'Konfigurasi config.php'),
    ('9', 'Akses Aplikasi & Setup Password'),
    ('10', 'Login & Verifikasi'),
    ('11', 'Mode Demo (SQLite tanpa MySQL)'),
    ('12', 'Troubleshooting'),
    ('13', 'Akun Default & Keamanan'),
]
pdf.set_font('Helvetica', '', 10)
for num, title in toc:
    pdf.set_text_color(58, 93, 221)
    pdf.cell(10, 7, num, 0, 0, 'L')
    pdf.set_text_color(51, 51, 51)
    pdf.cell(0, 7, title, 0, 1, 'L')
pdf.set_text_color(0, 0, 0)

# ===== STEP 1 =====
pdf.add_page()
pdf.section_title('1. Persiapan')
pdf.body('Sebelum memulai, pastikan komputer Anda memenuhi persyaratan berikut:')
pdf.sub_title('Persyaratan Sistem')
pdf.table_row('Komponen', 'Spesifikasi', header=True)
pdf.table_row('OS', 'Windows 10/11, macOS, atau Linux')
pdf.table_row('RAM', 'Minimal 2GB (rekomendasi 4GB)')
pdf.table_row('Disk', 'Minimal 500MB ruang kosong')
pdf.table_row('XAMPP', 'Versi 8.0+ (include PHP + MySQL)')
pdf.table_row('Browser', 'Chrome, Firefox, atau Edge terbaru')
pdf.ln(3)
pdf.sub_title('Yang Akan Diinstall')
pdf.body('-  XAMPP (berisi Apache, MySQL, PHP, phpMyAdmin)\n-  AssetManager (aplikasi dari GitHub)')

# ===== STEP 2 =====
pdf.section_title('2. Download & Install XAMPP')
pdf.step(1, 'Download XAMPP')
pdf.body('Buka browser dan kunjungi website resmi XAMPP:')
pdf.code('https://www.apachefriends.org/download.html')
pdf.body('Download versi PHP 8.0 atau lebih baru. Pilih sesuai OS:\n-  Windows: XAMPP for Windows (x64)\n-  macOS: XAMPP for OS X\n-  Linux: XAMPP for Linux')

pdf.step(2, 'Install XAMPP')
pdf.body('Jalankan file installer:')
pdf.code('Windows: Double-click xampp-windows-x64-8.x.x-installer.exe')
pdf.body('Ikuti langkah instalasi:\n-  Klik Next, pilih komponen (default: Apache, MySQL, PHP, phpMyAdmin)\n-  Pilih folder (default: C:\\xampp)\n-  Klik Next sampai Finish')
pdf.info_box('XAMPP terinstall di C:\\xampp (Windows) atau /Applications/XAMPP (macOS). Folder penting: htdocs (aplikasi web), phpMyAdmin (database).')

# ===== STEP 3 =====
pdf.add_page()
pdf.section_title('3. Download AssetManager')
pdf.step(3, 'Download dari GitHub')
pdf.body('Ada 2 cara untuk mendownload AssetManager:')
pdf.sub_title('Cara A: Download ZIP (paling mudah)')
pdf.body('1. Buka: https://github.com/antono4/AssetManager\n2. Klik tombol "Code" (hijau) > "Download ZIP"\n3. Extract file ZIP ke folder sementara')
pdf.code('Hasil extract: AssetManager-main/asset_app/')

pdf.sub_title('Cara B: Git Clone (butuh Git)')
pdf.body('Buka Command Prompt / Terminal:')
pdf.code('git clone https://github.com/antono4/AssetManager.git')
pdf.body('Hasil: folder AssetManager/asset_app/')

# ===== STEP 4 =====
pdf.section_title('4. Copy ke Folder htdocs')
pdf.step(4, 'Copy folder asset_app ke htdocs')
pdf.body('Copy folder asset_app ke folder htdocs XAMPP:')
pdf.sub_title('Windows')
pdf.code('Copy folder asset_app ke:\n  C:\\xampp\\htdocs\\\n\nHasil:\n  C:\\xampp\\htdocs\\asset_app\\')
pdf.sub_title('macOS')
pdf.code('Copy folder asset_app ke:\n  /Applications/XAMPP/htdocs/\n\nHasil:\n  /Applications/XAMPP/htdocs/asset_app/')
pdf.sub_title('Linux')
pdf.code('sudo cp -r asset_app /opt/lampp/htdocs/\n\nHasil:\n  /opt/lampp/htdocs/asset_app/')
pdf.info_box('Struktur: C:\\xampp\\htdocs\\asset_app\\ (berisi config.php, public/, app/, database/)')

# ===== STEP 5 =====
pdf.add_page()
pdf.section_title('5. Start Apache & MySQL')
pdf.step(5, 'Buka XAMPP Control Panel')
pdf.body('Buka XAMPP Control Panel:')
pdf.code('Windows: Start Menu > XAMPP Control Panel\n          atau double-click C:\\xampp\\xampp-control.exe')
pdf.body('Pada XAMPP Control Panel:')
pdf.body('1. Cari baris "Apache" > klik tombol Start\n2. Cari baris "MySQL" > klik tombol Start\n3. Pastikan keduanya berwarna hijau (running)')
pdf.info_box('Jika port 80 dipakai aplikasi lain (Skype, IIS), Apache error. Solusi: Config > httpd.conf > ubah "Listen 80" ke "Listen 8080".')

# ===== STEP 6 =====
pdf.section_title('6. Buat Database via phpMyAdmin')
pdf.step(6, 'Buka phpMyAdmin')
pdf.body('Buka browser, akses phpMyAdmin:')
pdf.code('http://localhost/phpmyadmin')
pdf.body('(Jika port 8080: http://localhost:8080/phpmyadmin)')

pdf.step(7, 'Buat Database Baru')
pdf.body('1. Klik tab "Databases" di atas\n2. Pada "Create database", ketik: asset_db\n3. Pilih charset: utf8mb4_unicode_ci\n4. Klik tombol "Create"')
pdf.info_box('Database asset_db berhasil dibuat. Lanjut: import skema tabel.')

# ===== STEP 7 =====
pdf.add_page()
pdf.section_title('7. Import Skema Database')
pdf.step(8, 'Import file SQL')
pdf.body('1. Pilih database asset_db di sidebar kiri phpMyAdmin\n2. Klik tab "Import" di atas\n3. Klik "Choose File", pilih:')
pdf.code('C:\\xampp\\htdocs\\asset_app\\database\\assets_app.sql')
pdf.body('5. Scroll ke bawah > klik "Go" / "Import"\n6. Tunggu sampai "Import has been successfully finished"')

pdf.sub_title('Tabel yang dibuat:')
pdf.body('-  categories, users, assets, asset_logs\n-  patch_items, patch_schedules, patch_checklists\n-  patch_checklist_items (dengan kolom patch_code)\n-  audit_trail, api_tokens, borrowings, notifications')

pdf.note_box('Hash password di file SQL adalah placeholder. Password asli di-set pada langkah Setup Password (langkah 9).', 'Penting')

# ===== STEP 8 =====
pdf.add_page()
pdf.section_title('8. Konfigurasi config.php')
pdf.step(9, 'Edit file konfigurasi')
pdf.body('Buka file config.php:')
pdf.code('C:\\xampp\\htdocs\\asset_app\\config.php')
pdf.body('Cari bagian "Pilihan Database", pastikan konfigurasi MySQL:')
pdf.code("define('DB_DRIVER', 'mysql');\ndefine('DB_HOST', '127.0.0.1');\ndefine('DB_PORT', '3306');\ndefine('DB_NAME', 'asset_db');\ndefine('DB_USER', 'root');\ndefine('DB_PASS', '');  // XAMPP default: kosong")
pdf.info_box('XAMPP default: user=root, password=kosong. Jika Anda men-set password MySQL, isi DB_PASS dengan password tersebut.')

# ===== STEP 9 =====
pdf.section_title('9. Akses Aplikasi & Setup Password')
pdf.step(10, 'Akses aplikasi di browser')
pdf.body('Buka browser, akses:')
pdf.code('http://localhost/asset_app/public/')
pdf.body('(Jika port 8080: http://localhost:8080/asset_app/public/)')

pdf.step(11, 'Setup Password')
pdf.body('Karena hash password masih placeholder, akses route setup:')
pdf.code('http://localhost/asset_app/public/setup')
pdf.body('Halaman setup akan:\n-  Reset password admin menjadi admin123\n-  Reset password staff menjadi staff123\n-  Mengisi hash bcrypt yang valid')
pdf.body('Setelah selesai, klik "Lanjut ke Login".')

# ===== STEP 10 =====
pdf.add_page()
pdf.section_title('10. Login & Verifikasi')
pdf.step(12, 'Login ke aplikasi')
pdf.body('Buka halaman login:')
pdf.code('http://localhost/asset_app/public/login')
pdf.body('Login dengan akun default:')
pdf.table_row('Username', 'Password', header=True)
pdf.table_row('admin', 'admin123')
pdf.table_row('staff', 'staff123')
pdf.ln(2)

pdf.step(13, 'Verifikasi Instalasi')
pdf.body('Pastikan hal-hal berikut berfungsi:')
pdf.body('[ ] Dashboard menampilkan statistik & grafik\n[ ] Bisa melihat daftar aset\n[ ] Bisa tambah aset baru dengan foto\n[ ] Bisa buat jadwal patching & checklist\n[ ] Buka laporan & cetak PDF\n[ ] Ganti bahasa (EN/ID)\n[ ] Dark mode toggle berfungsi\n[ ] Export CSV berfungsi')

# ===== STEP 11 =====
pdf.add_page()
pdf.section_title('11. Mode Demo (SQLite tanpa MySQL)')
pdf.body('Jika ingin mencoba aplikasi TANPA MySQL (mode demo):')
pdf.info_box('Mode demo pakai SQLite. Database & data dummy otomatis dibuat. Tidak perlu phpMyAdmin atau import SQL.')
pdf.sub_title('Langkah Mode Demo:')
pdf.body('1. Copy folder asset_app ke htdocs (seperti langkah 4)\n2. Pastikan config.php (default sudah SQLite):')
pdf.code("define('DB_DRIVER', 'sqlite');  // default")
pdf.body('3. MySQL TIDAK perlu di-start (hanya Apache)\n4. Akses: http://localhost/asset_app/public/')
pdf.body('Database SQLite otomatis dibuat di: asset_app/database/asset_db.sqlite')

# ===== STEP 12 =====
pdf.section_title('12. Troubleshooting')
pdf.sub_title('Apache tidak start (port 80 dipakai)')
pdf.body('Ubah port: XAMPP Control Panel > Apache > Config > httpd.conf. Cari "Listen 80" ubah ke "Listen 8080". Akses: http://localhost:8080/asset_app/public/')

pdf.sub_title('MySQL tidak start')
pdf.body('Cek MySQL lain yang running. Task Manager > cari "mysqld" > End Task. Start MySQL di XAMPP lagi.')

pdf.sub_title('Halaman blank / 404')
pdf.body('Pastikan akses melalui folder public: http://localhost/asset_app/public/ (bukan http://localhost/asset_app/)')

pdf.sub_title('Error "Access denied for user root"')
pdf.body('Password MySQL tidak cocok. Buka config.php, pastikan DB_PASS sesuai. Default XAMPP: kosong.')

pdf.sub_title('Login gagal / password salah')
pdf.body('Akses http://localhost/asset_app/public/setup untuk reset password.')

pdf.sub_title('Foto tidak bisa upload')
pdf.body('Cek folder: C:\\xampp\\htdocs\\asset_app\\public\\uploads\\assets\\. Pastikan ada dan writable.')

# ===== STEP 13 =====
pdf.add_page()
pdf.section_title('13. Akun Default & Keamanan')
pdf.sub_title('Akun Default')
pdf.table_row('Username', 'Password', header=True)
pdf.table_row('admin', 'admin123')
pdf.table_row('staff', 'staff123')
pdf.ln(2)

pdf.note_box('GANTI PASSWORD DEFAULT SEGERA! Login sebagai admin > Manajemen User > Edit > ganti password.', 'PERINGATAN')

pdf.sub_title('Tips Keamanan Produksi')
pdf.body('-  Ganti password root MySQL XAMPP (default kosong)\n-  Ganti password admin & staff di aplikasi\n-  Set password MySQL di config.php\n-  Jangan pakai SQLite untuk produksi\n-  Backup database berkala\n-  Gunakan HTTPS di server publik\n-  Batasi/ nonaktifkan phpMyAdmin')

pdf.sub_title('Fitur Keamanan Aplikasi')
pdf.body('-  Password hash bcrypt\n-  Rate limiting login (max 5x, lock 15 menit)\n-  Session HttpOnly + SameSite\n-  RBAC admin/staff\n-  Harga hanya tampil admin\n-  Audit trail semua perubahan\n-  Escape HTML (anti-XSS)\n-  API token untuk REST API')

# ===== BACK PAGE =====
pdf.add_page()
pdf.set_fill_color(15, 23, 41)
pdf.rect(0, 0, 210, 297, 'F')
pdf.set_y(100)
pdf.set_font('Helvetica', 'B', 22)
pdf.set_text_color(255, 255, 255)
pdf.cell(0, 12, 'Instalasi Selesai!', 0, 1, 'C')
pdf.ln(5)
pdf.set_font('Helvetica', '', 13)
pdf.set_text_color(158, 193, 255)
pdf.cell(0, 8, 'AssetManager siap digunakan', 0, 1, 'C')
pdf.ln(15)
pdf.set_font('Helvetica', '', 10)
pdf.set_text_color(108, 122, 140)
pdf.cell(0, 6, 'Dokumentasi lengkap:', 0, 1, 'C')
pdf.set_text_color(79, 124, 255)
pdf.cell(0, 6, 'README.md  -  INSTALL.md  -  USER_GUIDE.md', 0, 1, 'C')
pdf.ln(10)
pdf.set_text_color(108, 122, 140)
pdf.cell(0, 6, 'GitHub: https://github.com/antono4/AssetManager', 0, 1, 'C')
pdf.ln(20)
pdf.set_text_color(0, 212, 168)
pdf.set_font('Helvetica', 'B', 10)
pdf.cell(0, 6, 'MIT License - Free to use, modify, distribute', 0, 1, 'C')
pdf.ln(40)
pdf.set_text_color(60, 70, 90)
pdf.set_font('Helvetica', '', 8)
pdf.cell(0, 6, '(c) 2026 AssetManager v1.0', 0, 1, 'C')

# Save
out = '/workspace/project/asset_app/XAMPP_Installation_Guide.pdf'
pdf.output(out)
print(f'PDF saved: {out}')
print(f'Size: {os.path.getsize(out)} bytes')

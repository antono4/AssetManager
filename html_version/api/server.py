#!/usr/bin/env python3
"""
AssetManager HTML Version — Live API backend (Python stdlib only).

Menyajikan file statis html_version + REST API dengan data persisten ke
file JSON (di-seed dari data dummy assets_app.sql). Menggantikan backend
PHP untuk mode "live data" — semua perubahan dari klien tersimpan di
server dan terlihat oleh semua sesi/browser.

Endpoints:
  GET  /api/db                 -> seluruh database snapshot (JSON)
  POST /api/db                 -> simpan seluruh database (body: db JSON)
  POST /api/login              -> {username,password} -> {ok,user} | {ok:false}
  POST /api/reset              -> reset DB ke seed awal
  GET  /api/assets             -> daftar aset (JSON, kompatibel dgn PHP app)
  *    (lainnya)               -> file statis dari html_version/

Jalankan:  python3 api/server.py
"""
import http.server, socketserver, os, json, threading, time

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))  # html_version/
DB_FILE = os.path.join(ROOT, 'database', 'live_db.json')
PORT = int(os.environ.get('PORT', '12001'))
HOST = os.environ.get('HOST', '0.0.0.0')

_lock = threading.Lock()


def seed():
    now = time.strftime('%Y-%m-%dT%H:%M:%SZ', time.gmtime())
    return {
        'categories': [
            {'id': 1, 'name': 'Komputer',  'description': 'PC desktop dan workstation', 'created_at': now},
            {'id': 2, 'name': 'Laptop',    'description': 'Laptop dan notebook', 'created_at': now},
            {'id': 3, 'name': 'Printer',   'description': 'Printer dan scanner', 'created_at': now},
            {'id': 4, 'name': 'Jaringan',  'description': 'Switch, router, access point', 'created_at': now},
            {'id': 5, 'name': 'Umum',      'description': 'Aset non-IT lainnya', 'created_at': now},
        ],
        'users': [
            {'id': 1, 'name': 'Administrator', 'username': 'admin', 'email': 'admin@asset.app', 'password': 'admin123', 'role': 'admin', 'is_active': 1, 'created_at': now, 'updated_at': now},
            {'id': 2, 'name': 'Staff Satu',    'username': 'staff', 'email': 'staff@asset.app', 'password': 'staff123', 'role': 'staff', 'is_active': 1, 'created_at': now, 'updated_at': now},
        ],
        'assets': [
            {'id': 1, 'asset_code': 'AST-0001', 'name': 'PC Desktop Dev 01', 'category_id': 1, 'brand_spec': 'Dell OptiPlex 7090 / i7-11700 / 16GB / SSD 512GB', 'location': 'Ruang Server', 'status': 'tersedia', 'purchase_date': '2023-02-10', 'price': 12500000, 'photo': '', 'deleted_at': None, 'currency': 'IDR', 'created_at': now, 'updated_at': now},
            {'id': 2, 'asset_code': 'AST-0002', 'name': 'PC Desktop Dev 02', 'category_id': 1, 'brand_spec': 'HP EliteDesk 800 G6 / i5-10500 / 8GB / SSD 256GB', 'location': 'Ruang Developer', 'status': 'tersedia', 'purchase_date': '2023-03-15', 'price': 9800000, 'photo': '', 'deleted_at': None, 'currency': 'IDR', 'created_at': now, 'updated_at': now},
            {'id': 3, 'asset_code': 'AST-0003', 'name': 'Laptop Marketing', 'category_id': 2, 'brand_spec': 'Lenovo ThinkPad E14 / Ryzen 5 / 8GB / SSD 512GB', 'location': 'Ruang Marketing', 'status': 'dipinjam', 'purchase_date': '2023-05-20', 'price': 11000000, 'photo': '', 'deleted_at': None, 'currency': 'IDR', 'created_at': now, 'updated_at': now},
            {'id': 4, 'asset_code': 'AST-0004', 'name': 'Laptop Direksi', 'category_id': 2, 'brand_spec': 'MacBook Air M2 / 8GB / SSD 256GB', 'location': 'Ruang Direksi', 'status': 'dipinjam', 'purchase_date': '2023-06-01', 'price': 18000000, 'photo': '', 'deleted_at': None, 'currency': 'IDR', 'created_at': now, 'updated_at': now},
            {'id': 5, 'asset_code': 'AST-0005', 'name': 'Printer Laser HR', 'category_id': 3, 'brand_spec': 'Brother HL-L2375DW', 'location': 'Ruang HRD', 'status': 'tersedia', 'purchase_date': '2022-11-12', 'price': 2500000, 'photo': '', 'deleted_at': None, 'currency': 'IDR', 'created_at': now, 'updated_at': now},
            {'id': 6, 'asset_code': 'AST-0006', 'name': 'Printer Inkjet', 'category_id': 3, 'brand_spec': 'Epson EcoTank L3210', 'location': 'Ruang Operasional', 'status': 'rusak', 'purchase_date': '2021-09-08', 'price': 2300000, 'photo': '', 'deleted_at': None, 'currency': 'IDR', 'created_at': now, 'updated_at': now},
            {'id': 7, 'asset_code': 'AST-0007', 'name': 'Switch Core', 'category_id': 4, 'brand_spec': 'Cisco Catalyst 2960 24-Port', 'location': 'Ruang Server', 'status': 'tersedia', 'purchase_date': '2022-07-30', 'price': 15000000, 'photo': '', 'deleted_at': None, 'currency': 'IDR', 'created_at': now, 'updated_at': now},
            {'id': 8, 'asset_code': 'AST-0008', 'name': 'Access Point', 'category_id': 4, 'brand_spec': 'TP-Link EAP670 AX3000', 'location': 'Lobi Utama', 'status': 'tersedia', 'purchase_date': '2023-08-22', 'price': 1800000, 'photo': '', 'deleted_at': None, 'currency': 'IDR', 'created_at': now, 'updated_at': now},
            {'id': 9, 'asset_code': 'AST-0009', 'name': 'AC Split 1 PK', 'category_id': 5, 'brand_spec': 'Daikin R32 inverter', 'location': 'Ruang Server', 'status': 'tersedia', 'purchase_date': '2022-04-18', 'price': 4200000, 'photo': '', 'deleted_at': None, 'currency': 'IDR', 'created_at': now, 'updated_at': now},
            {'id': 10, 'asset_code': 'AST-0010', 'name': 'Proyektor', 'category_id': 5, 'brand_spec': 'Epson EB-X51 2700 lumen', 'location': 'Ruang Rapat', 'status': 'rusak', 'purchase_date': '2020-10-05', 'price': 6500000, 'photo': '', 'deleted_at': None, 'currency': 'IDR', 'created_at': now, 'updated_at': now},
        ],
        'asset_logs': [
            {'id': 1, 'asset_id': 3,  'user_id': 2, 'action': 'dipinjam', 'note': 'Dipinjam oleh tim marketing untuk presentasi klien', 'created_at': now},
            {'id': 2, 'asset_id': 4,  'user_id': 2, 'action': 'dipinjam', 'note': 'Dipinjam oleh direksi untuk perjalanan dinas', 'created_at': now},
            {'id': 3, 'asset_id': 6,  'user_id': 1, 'action': 'rusak', 'note': 'Kerusakan pada modul head printer, menunggu penggantian', 'created_at': now},
            {'id': 4, 'asset_id': 10, 'user_id': 1, 'action': 'rusak', 'note': 'Lampu proyektor mati, perlu penggantian', 'created_at': now},
            {'id': 5, 'asset_id': 7,  'user_id': 1, 'action': 'perawatan', 'note': 'Maintenance switch core bulanan', 'created_at': now},
            {'id': 6, 'asset_id': 3,  'user_id': 1, 'action': 'status_update', 'note': 'Status diperbarui melalui dashboard', 'created_at': now},
        ],
        'patch_items': [
            {'id': 1, 'name': 'Update Sistem Operasi / Firmware', 'description': 'Patch OS terbaru atau firmware perangkat', 'is_active': 1, 'sort_order': 1},
            {'id': 2, 'name': 'Update Antivirus / Security', 'description': 'Update definisi virus & security patch', 'is_active': 1, 'sort_order': 2},
            {'id': 3, 'name': 'Backup Data', 'description': 'Backup konfigurasi & data penting', 'is_active': 1, 'sort_order': 3},
            {'id': 4, 'name': 'Cek Log Sistem', 'description': 'Tinjau log sistem untuk error/anomali', 'is_active': 1, 'sort_order': 4},
            {'id': 5, 'name': 'Restart Layanan', 'description': 'Restart service/daemon kritis', 'is_active': 1, 'sort_order': 5},
            {'id': 6, 'name': 'Verifikasi Konektivitas', 'description': 'Tes koneksi jaringan & fungsi perangkat', 'is_active': 1, 'sort_order': 6},
        ],
        'patch_schedules': [
            {'id': 1, 'name': 'Patching Q3 2026', 'quarter': 3, 'year': 2026, 'start_date': '2026-07-01', 'due_date': '2026-09-30', 'status': 'ongoing', 'description': 'Patch kuartal Q3', 'created_by': 1, 'created_at': now, 'updated_at': now},
        ],
        'patch_checklists': [
            {'id': 1, 'schedule_id': 1, 'asset_id': 1, 'status': 'in_progress', 'patched_by': None, 'patched_at': None, 'notes': '', 'created_at': now, 'updated_at': now},
            {'id': 2, 'schedule_id': 1, 'asset_id': 2, 'status': 'pending', 'patched_by': None, 'patched_at': None, 'notes': '', 'created_at': now, 'updated_at': now},
            {'id': 3, 'schedule_id': 1, 'asset_id': 3, 'status': 'completed', 'patched_by': 2, 'patched_at': now, 'notes': 'Selesai lebih cepat', 'created_at': now, 'updated_at': now},
            {'id': 4, 'schedule_id': 1, 'asset_id': 7, 'status': 'completed', 'patched_by': 1, 'patched_at': now, 'notes': '', 'created_at': now, 'updated_at': now},
            {'id': 5, 'schedule_id': 1, 'asset_id': 8, 'status': 'skipped', 'patched_by': None, 'patched_at': None, 'notes': 'AP non-kritis, skip', 'created_at': now, 'updated_at': now},
        ],
        'patch_checklist_items': [
            {'id': 1,  'checklist_id': 1, 'item_id': 1, 'is_checked': 1, 'checked_by': 2, 'checked_at': now, 'notes': '', 'patch_code': 'KB5079473'},
            {'id': 2,  'checklist_id': 1, 'item_id': 2, 'is_checked': 1, 'checked_by': 2, 'checked_at': now, 'notes': '', 'patch_code': 'Av-Def-2026.08'},
            {'id': 3,  'checklist_id': 1, 'item_id': 3, 'is_checked': 1, 'checked_by': 2, 'checked_at': now, 'notes': '', 'patch_code': ''},
            {'id': 4,  'checklist_id': 1, 'item_id': 4, 'is_checked': 1, 'checked_by': 2, 'checked_at': now, 'notes': '', 'patch_code': ''},
            {'id': 5,  'checklist_id': 1, 'item_id': 5, 'is_checked': 0, 'checked_by': None, 'checked_at': None, 'notes': '', 'patch_code': ''},
            {'id': 6,  'checklist_id': 1, 'item_id': 6, 'is_checked': 0, 'checked_by': None, 'checked_at': None, 'notes': '', 'patch_code': ''},
            {'id': 7,  'checklist_id': 2, 'item_id': 1, 'is_checked': 0, 'checked_by': None, 'checked_at': None, 'notes': '', 'patch_code': ''},
            {'id': 8,  'checklist_id': 2, 'item_id': 2, 'is_checked': 0, 'checked_by': None, 'checked_at': None, 'notes': '', 'patch_code': ''},
            {'id': 9,  'checklist_id': 2, 'item_id': 3, 'is_checked': 0, 'checked_by': None, 'checked_at': None, 'notes': '', 'patch_code': ''},
            {'id': 10, 'checklist_id': 2, 'item_id': 4, 'is_checked': 0, 'checked_by': None, 'checked_at': None, 'notes': '', 'patch_code': ''},
            {'id': 11, 'checklist_id': 2, 'item_id': 5, 'is_checked': 0, 'checked_by': None, 'checked_at': None, 'notes': '', 'patch_code': ''},
            {'id': 12, 'checklist_id': 2, 'item_id': 6, 'is_checked': 0, 'checked_by': None, 'checked_at': None, 'notes': '', 'patch_code': ''},
            {'id': 13, 'checklist_id': 3, 'item_id': 1, 'is_checked': 1, 'checked_by': 2, 'checked_at': now, 'notes': '', 'patch_code': 'KB5079473'},
            {'id': 14, 'checklist_id': 3, 'item_id': 2, 'is_checked': 1, 'checked_by': 2, 'checked_at': now, 'notes': '', 'patch_code': 'Av-Def-2026.08'},
            {'id': 15, 'checklist_id': 3, 'item_id': 3, 'is_checked': 1, 'checked_by': 2, 'checked_at': now, 'notes': '', 'patch_code': ''},
            {'id': 16, 'checklist_id': 3, 'item_id': 4, 'is_checked': 1, 'checked_by': 2, 'checked_at': now, 'notes': '', 'patch_code': ''},
            {'id': 17, 'checklist_id': 3, 'item_id': 5, 'is_checked': 1, 'checked_by': 2, 'checked_at': now, 'notes': '', 'patch_code': ''},
            {'id': 18, 'checklist_id': 3, 'item_id': 6, 'is_checked': 1, 'checked_by': 2, 'checked_at': now, 'notes': '', 'patch_code': ''},
            {'id': 19, 'checklist_id': 4, 'item_id': 1, 'is_checked': 1, 'checked_by': 1, 'checked_at': now, 'notes': '', 'patch_code': 'IOS-15.2'},
            {'id': 20, 'checklist_id': 4, 'item_id': 2, 'is_checked': 1, 'checked_by': 1, 'checked_at': now, 'notes': '', 'patch_code': ''},
            {'id': 21, 'checklist_id': 4, 'item_id': 3, 'is_checked': 1, 'checked_by': 1, 'checked_at': now, 'notes': '', 'patch_code': ''},
            {'id': 22, 'checklist_id': 4, 'item_id': 4, 'is_checked': 1, 'checked_by': 1, 'checked_at': now, 'notes': '', 'patch_code': ''},
            {'id': 23, 'checklist_id': 4, 'item_id': 5, 'is_checked': 1, 'checked_by': 1, 'checked_at': now, 'notes': '', 'patch_code': ''},
            {'id': 24, 'checklist_id': 4, 'item_id': 6, 'is_checked': 1, 'checked_by': 1, 'checked_at': now, 'notes': '', 'patch_code': ''},
            {'id': 25, 'checklist_id': 5, 'item_id': 1, 'is_checked': 0, 'checked_by': None, 'checked_at': None, 'notes': '', 'patch_code': ''},
            {'id': 26, 'checklist_id': 5, 'item_id': 2, 'is_checked': 0, 'checked_by': None, 'checked_at': None, 'notes': '', 'patch_code': ''},
            {'id': 27, 'checklist_id': 5, 'item_id': 3, 'is_checked': 0, 'checked_by': None, 'checked_at': None, 'notes': '', 'patch_code': ''},
            {'id': 28, 'checklist_id': 5, 'item_id': 4, 'is_checked': 0, 'checked_by': None, 'checked_at': None, 'notes': '', 'patch_code': ''},
            {'id': 29, 'checklist_id': 5, 'item_id': 5, 'is_checked': 0, 'checked_by': None, 'checked_at': None, 'notes': '', 'patch_code': ''},
            {'id': 30, 'checklist_id': 5, 'item_id': 6, 'is_checked': 0, 'checked_by': None, 'checked_at': None, 'notes': '', 'patch_code': ''},
        ],
        'borrowings': [
            {'id': 1, 'asset_id': 3, 'borrower_name': 'Tim Marketing', 'user_id': 2, 'borrow_date': '2026-08-01 09:00:00', 'expected_return': '2026-08-10 17:00:00', 'actual_return': None, 'status': 'borrowed', 'note': 'Presentasi klien', 'created_at': now},
            {'id': 2, 'asset_id': 4, 'borrower_name': 'Bapak Direktur', 'user_id': 1, 'borrow_date': '2026-08-05 08:00:00', 'expected_return': '2026-08-12 18:00:00', 'actual_return': None, 'status': 'borrowed', 'note': 'Perjalanan dinas', 'created_at': now},
            {'id': 3, 'asset_id': 7, 'borrower_name': 'Tim Jaringan', 'user_id': 1, 'borrow_date': '2026-06-01 10:00:00', 'expected_return': '2026-06-03 16:00:00', 'actual_return': '2026-06-03 15:30:00', 'status': 'returned', 'note': 'Konfigurasi switch', 'created_at': now},
        ],
        'audit_trail': [
            {'id': 1, 'module': 'auth', 'action': 'login', 'description': 'User admin logged in', 'user_id': 1, 'ip': '127.0.0.1', 'created_at': now},
            {'id': 2, 'module': 'assets', 'action': 'created', 'description': 'Added asset AST-0001', 'user_id': 1, 'ip': '127.0.0.1', 'created_at': now},
            {'id': 3, 'module': 'patching', 'action': 'toggled', 'description': 'Checklist item toggled for AST-0001', 'user_id': 2, 'ip': '127.0.0.1', 'created_at': now},
            {'id': 4, 'module': 'borrowings', 'action': 'borrowed', 'description': 'Borrowed AST-0003', 'user_id': 2, 'ip': '127.0.0.1', 'created_at': now},
        ],
        'notifications': [
            {'id': 1, 'user_id': 0, 'title': 'Patching Q3 2026 sedang berjalan', 'body': 'Selesaikan checklist patching sebelum 30 Sep 2026.', 'is_read': 0, 'link': '#patching/1', 'created_at': now},
        ],
        'api_tokens': [
            {'id': 1, 'name': 'Default Token', 'token': 'am_demo_token_9f3c7b1e2a8d4560', 'user_id': 1, 'last_used_at': None, 'created_at': now},
        ],
        'settings': [
            {'setting_key': 'company_name',    'setting_value': 'AssetManager', 'updated_at': now},
            {'setting_key': 'company_address', 'setting_value': 'Jl. Teknologi No. 1, Jakarta', 'updated_at': now},
            {'setting_key': 'company_phone',   'setting_value': '021-1234567', 'updated_at': now},
            {'setting_key': 'company_email',   'setting_value': 'info@asset.app', 'updated_at': now},
        ],
    }


def load_db():
    if not os.path.exists(DB_FILE):
        db = seed(); save_db(db); return db
    try:
        with open(DB_FILE, 'r', encoding='utf-8') as f:
            return json.load(f)
    except Exception:
        db = seed(); save_db(db); return db


def save_db(db):
    try:
        os.makedirs(os.path.dirname(DB_FILE), exist_ok=True)
        with open(DB_FILE, 'w', encoding='utf-8') as f:
            json.dump(db, f, ensure_ascii=False)
    except Exception as e:
        print('save_db error:', e)


class Handler(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *a, **k):
        super().__init__(*a, directory=ROOT, **k)

    def _cors(self):
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type, X-Api-Token')
        self.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')

    def _no_cache(self):
        self.send_header('Cache-Control', 'no-store, no-cache, must-revalidate')
        self.send_header('Pragma', 'no-cache')

    def _json(self, obj, code=200):
        body = json.dumps(obj, ensure_ascii=False).encode('utf-8')
        self.send_response(code)
        self.send_header('Content-Type', 'application/json; charset=utf-8')
        self.send_header('Content-Length', str(len(body)))
        self._cors(); self._no_cache()
        self.end_headers()
        self.wfile.write(body)

    def _read_body(self):
        try:
            n = int(self.headers.get('Content-Length', '0'))
            if n == 0:
                return {}
            return json.loads(self.rfile.read(n).decode('utf-8'))
        except Exception:
            return {}

    def do_OPTIONS(self):
        self.send_response(204); self._cors(); self.end_headers()

    def do_GET(self):
        path = self.path.split('?')[0]
        if path == '/api/db':
            with _lock:
                self._json(load_db()); return
        if path == '/api/assets':
            with _lock:
                db = load_db()
                cats = {c['id']: c['name'] for c in db.get('categories', [])}
                data = []
                for a in db.get('assets', []):
                    if a.get('deleted_at'):
                        continue
                    data.append({
                        'id': a['id'], 'asset_code': a['asset_code'], 'name': a['name'],
                        'category': cats.get(a.get('category_id')), 'brand_spec': a.get('brand_spec'),
                        'location': a.get('location'), 'status': a.get('status'),
                        'purchase_date': a.get('purchase_date'), 'price': a.get('price'),
                    })
                self._json({'success': True, 'count': len(data), 'data': data}); return
        super().do_GET()

    def do_POST(self):
        path = self.path.split('?')[0]
        body = self._read_body()
        with _lock:
            if path == '/api/db':
                if isinstance(body, dict) and 'assets' in body:
                    save_db(body); self._json({'ok': True}); return
                self._json({'ok': False, 'error': 'invalid db'}, 400); return
            if path == '/api/login':
                db = load_db()
                u = None
                for x in db.get('users', []):
                    if x.get('username') == body.get('username') and x.get('password') == body.get('password') and x.get('is_active') == 1:
                        u = x; break
                if u:
                    self._json({'ok': True, 'user': {'id': u['id'], 'name': u['name'], 'username': u['username'], 'role': u['role'], 'email': u.get('email')}})
                else:
                    self._json({'ok': False}); return
            if path == '/api/reset':
                db = seed(); save_db(db); self._json({'ok': True}); return
        self._json({'ok': False, 'error': 'unknown endpoint'}, 404)

    def end_headers(self):
        self._cors(); self._no_cache()
        super().end_headers()

    def log_message(self, *a):
        pass


class ReuseTCPServer(socketserver.TCPServer):
    allow_reuse_address = True


def main():
    print(f'AssetManager live API serving on http://{HOST}:{PORT}  (root={ROOT})')
    print(f'DB file: {DB_FILE}')
    with ReuseTCPServer((HOST, PORT), Handler) as s:
        s.serve_forever()


if __name__ == '__main__':
    main()

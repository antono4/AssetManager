#!/usr/bin/env python3
"""
AssetManager HTML Version — Live MySQL backend (PyMySQL).

Membaca/menulis langsung ke database MySQL `assets_app` (kompatibel dengan
schema assets_app.sql + alterasi html_version). Menyajikan file statis
html_version + REST API yang kompatibel dengan store.js (mode live).

Endpoints (kompatibel dgn api/server.py versi JSON):
  GET  /api/db       -> snapshot seluruh DB (JSON, format sama dgn store)
  POST /api/db       -> simpan seluruh DB (upsert per tabel)
  POST /api/login    -> {username,password} -> {ok,user} | {ok:false}
  POST /api/reset    -> reset DB ke seed awal
  GET  /api/assets   -> daftar aset (kompatibel dgn PHP app)

Env:
  MYSQL_HOST (127.0.0.1)  MYSQL_PORT (3306)
  MYSQL_USER (asset_app)  MYSQL_PASSWORD (asset_secret)
  MYSQL_DB (assets_app)
  PORT (12001)  HOST (0.0.0.0)

Jalankan:  python3 api/server_mysql.py
"""
import http.server, socketserver, os, json, threading, time
import pymysql

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))  # html_version/
PORT = int(os.environ.get('PORT', '12001'))
HOST = os.environ.get('HOST', '0.0.0.0')

DB_CONF = {
    'host': os.environ.get('MYSQL_HOST', '127.0.0.1'),
    'port': int(os.environ.get('MYSQL_PORT', '3306')),
    'user': os.environ.get('MYSQL_USER', 'asset_app'),
    'password': os.environ.get('MYSQL_PASSWORD', 'asset_secret'),
    'database': os.environ.get('MYSQL_DB', 'assets_app'),
    'charset': 'utf8mb4',
    'cursorclass': pymysql.cursors.DictCursor,
}
_lock = threading.Lock()

# Tables + columns yang dipakai store.js (format JSON).
TABLES = {
    'categories': ['id', 'name', 'description', 'created_at'],
    'users': ['id', 'name', 'username', 'email', 'password', 'role', 'is_active', 'created_at', 'updated_at'],
    'assets': ['id', 'asset_code', 'name', 'category_id', 'brand_spec', 'location', 'status', 'purchase_date', 'price', 'photo', 'deleted_at', 'currency', 'created_at', 'updated_at'],
    'asset_logs': ['id', 'asset_id', 'user_id', 'action', 'note', 'created_at'],
    'patch_items': ['id', 'name', 'description', 'is_active', 'sort_order'],
    'patch_schedules': ['id', 'name', 'quarter', 'year', 'start_date', 'due_date', 'status', 'description', 'created_by', 'created_at', 'updated_at'],
    'patch_checklists': ['id', 'schedule_id', 'asset_id', 'status', 'patched_by', 'patched_at', 'notes', 'created_at', 'updated_at'],
    'patch_checklist_items': ['id', 'checklist_id', 'item_id', 'is_checked', 'checked_by', 'checked_at', 'notes', 'patch_code'],
    'borrowings': ['id', 'asset_id', 'borrower_name', 'user_id', 'borrow_date', 'expected_return', 'actual_return', 'status', 'note', 'created_at'],
    'audit_trail': ['id', 'module', 'action', 'description', 'user_id', 'ip', 'created_at'],
    'notifications': ['id', 'user_id', 'title', 'body', 'is_read', 'link', 'created_at'],
    'api_tokens': ['id', 'name', 'token', 'user_id', 'last_used_at', 'created_at'],
    'settings': ['setting_key', 'setting_value', 'updated_at'],
}

# Primary key per tabel (untuk upsert)
PK = {
    'categories': 'id', 'users': 'id', 'assets': 'id', 'asset_logs': 'id',
    'patch_items': 'id', 'patch_schedules': 'id', 'patch_checklists': 'id',
    'patch_checklist_items': 'id', 'borrowings': 'id', 'audit_trail': 'id',
    'notifications': 'id', 'api_tokens': 'id', 'settings': 'setting_key',
}


def conn():
    return pymysql.connect(**DB_CONF)


def _conv(v):
    """Konversi tipe DB (datetime/date/Decimal/bytes) ke JSON-friendly."""
    import datetime, decimal
    if v is None:
        return None
    if isinstance(v, (datetime.datetime, datetime.date)):
        return v.isoformat() if hasattr(v, 'isoformat') else str(v)
    if isinstance(v, datetime.timedelta):
        return str(v)
    if isinstance(v, decimal.Decimal):
        return float(v) if v % 1 else int(v)
    if isinstance(v, bytes):
        try:
            return v.decode('utf-8')
        except Exception:
            return v.hex()
    return v


def snapshot_db():
    """Baca seluruh DB ke dict {table: [rows]} format JSON (kompatibel store.js)."""
    db = {}
    c = conn()
    try:
        for tbl, cols in TABLES.items():
            col_list = ', '.join('`' + col + '`' for col in cols)
            try:
                with c.cursor() as cur:
                    cur.execute('SELECT ' + col_list + ' FROM `' + tbl + '`')
                    rows = cur.fetchall()
                db[tbl] = [{k: _conv(v) for k, v in row.items()} for row in rows]
            except Exception as e:
                db[tbl] = []
    finally:
        c.close()
    return db


def save_table(c, tbl, rows):
    """Upsert rows ke tabel (DELETE + INSERT untuk simplicity, preserve auto-increment)."""
    cols = TABLES[tbl]
    pk = PK[tbl]
    with c.cursor() as cur:
        cur.execute('DELETE FROM `' + tbl + '`')
        if not rows:
            return
        col_list = ', '.join('`' + col + '`' for col in cols)
        placeholders = ', '.join('%s' for _ in cols)
        sql = 'INSERT INTO `' + tbl + '` (' + col_list + ') VALUES (' + placeholders + ')'
        for row in rows:
            vals = []
            for col in cols:
                v = row.get(col)
                # parse ISO datetime string kembali ke format MySQL (YYYY-MM-DD HH:MM:SS)
                if isinstance(v, str) and col in ('created_at', 'updated_at', 'patched_at', 'checked_at', 'borrow_date', 'expected_return', 'actual_return', 'last_used_at', 'deleted_at'):
                    if v == '' or v is None:
                        vals.append(None)
                    else:
                        # '2026-08-16T22:10:00Z' / '2026-08-16T22:10:00.000Z' -> '2026-08-16 22:10:00'
                        s = v.replace('T', ' ')
                        # strip fractional seconds & timezone marker
                        if '.' in s:
                            s = s.split('.')[0]
                        s = s.replace('Z', '').rstrip()
                        # jika hanya tanggal (YYYY-MM-DD), biarkan
                        vals.append(s)
                else:
                    vals.append(v)
            cur.execute(sql, vals)


def save_db(db):
    c = conn()
    try:
        c.begin()
        with c.cursor() as cur:
            cur.execute('SET FOREIGN_KEY_CHECKS=0')
        for tbl in TABLES:
            if tbl in db:
                save_table(c, tbl, db[tbl])
        with c.cursor() as cur:
            cur.execute('SET FOREIGN_KEY_CHECKS=1')
        c.commit()
    except Exception:
        c.rollback()
        raise
    finally:
        c.close()


def check_login(username, password):
    c = conn()
    try:
        with c.cursor() as cur:
            cur.execute("SELECT id,name,username,role,email,is_active,password FROM users WHERE username=%s", (username,))
            u = cur.fetchone()
        if u and u.get('is_active') == 1 and u.get('password') == password:
            return {'ok': True, 'user': {'id': u['id'], 'name': u['name'], 'username': u['username'], 'role': u['role'], 'email': u.get('email')}}
        return {'ok': False}
    finally:
        c.close()


def run_sql_file(path):
    c = conn()
    try:
        with open(path, 'r', encoding='utf-8') as f:
            sql = f.read()
        # split by statement (sederhana — cukup untuk file setup)
        with c.cursor() as cur:
            for stmt in sql.split(';'):
                stmt = stmt.strip()
                if stmt and not stmt.startswith('--'):
                    try:
                        cur.execute(stmt)
                    except Exception:
                        pass
        c.commit()
    finally:
        c.close()


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
        body = json.dumps(obj, ensure_ascii=False, default=str).encode('utf-8')
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
                try:
                    self._json(snapshot_db())
                except Exception as e:
                    self._json({'error': str(e)}, 500)
            return
        if path == '/api/assets':
            with _lock:
                try:
                    db = snapshot_db()
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
                    self._json({'success': True, 'count': len(data), 'data': data})
                except Exception as e:
                    self._json({'error': str(e)}, 500)
            return
        super().do_GET()

    def do_POST(self):
        path = self.path.split('?')[0]
        body = self._read_body()
        with _lock:
            try:
                if path == '/api/db':
                    if isinstance(body, dict) and 'assets' in body:
                        save_db(body); self._json({'ok': True}); return
                    self._json({'ok': False, 'error': 'invalid db'}, 400); return
                if path == '/api/login':
                    self._json(check_login(body.get('username', ''), body.get('password', ''))); return
                if path == '/api/reset':
                    # re-import schema + setup
                    base = os.path.dirname(os.path.abspath(__file__))
                    run_sql_file(os.path.join(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))), 'asset_app', 'database', 'assets_app.sql'))
                    run_sql_file(os.path.join(base, 'setup_mysql.sql'))
                    run_sql_file(os.path.join(base, 'alter_mysql.sql'))
                    self._json({'ok': True}); return
            except Exception as e:
                self._json({'ok': False, 'error': str(e)}, 500); return
        self._json({'ok': False, 'error': 'unknown endpoint'}, 404)

    def end_headers(self):
        self._cors(); self._no_cache()
        super().end_headers()

    def log_message(self, *a):
        pass


class ReuseTCPServer(socketserver.TCPServer):
    allow_reuse_address = True


def main():
    print(f'AssetManager LIVE MySQL API on http://{HOST}:{PORT}')
    print(f'  DB: {DB_CONF["host"]}:{DB_CONF["port"]}/{DB_CONF["database"]}  user={DB_CONF["user"]}')
    with ReuseTCPServer((HOST, PORT), Handler) as s:
        s.serve_forever()


if __name__ == '__main__':
    main()

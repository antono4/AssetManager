/* ============================================================================
   AssetManager HTML Version — Helpers + Auth
   Port dari app/core/helpers.php dan app/core/Auth.php (klien-side).
   ========================================================================== */
(function (global) {
    'use strict';

    function e(value) { return String(value === null || value === undefined ? '' : value)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

    function url(path) { path = String(path || '').replace(/^\//, ''); return '#' + path; }
    function assetUrl(path) { return 'assets/' + String(path || '').replace(/^\//, ''); }

    function rp(value) {
        if (value === null || value === '' || value === undefined) return '-';
        return 'Rp ' + Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }
    function rpCurrency(value, currency) {
        currency = (currency || 'IDR').toUpperCase();
        if (value === null || value === '' || value === undefined) return '-';
        if (currency === 'IDR') return 'Rp ' + Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });
        if (currency === 'USD') return '$ ' + Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (currency === 'EUR') return '€ ' + Number(value).toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        return currency + ' ' + Number(value).toLocaleString('en-US');
    }

    const BLN = [1, 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    function tgl(date) {
        if (!date) return '-';
        const d = new Date(date.replace(' ', 'T'));
        if (isNaN(d)) return date;
        return d.getDate() + ' ' + (BLN[d.getMonth() + 1] || '') + ' ' + d.getFullYear();
    }
    function tglwaktu(dt) {
        if (!dt) return '-';
        const d = new Date(String(dt).replace(' ', 'T'));
        if (isNaN(d)) return dt;
        return tgl(dt) + ' ' + String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    }

    function statusBadge(status) {
        const map = { tersedia: 'success', dipinjam: 'warning', rusak: 'danger' };
        const cls = map[status] || 'secondary';
        const label = Lang.t('status_' + status) || (status ? status.charAt(0).toUpperCase() + status.slice(1) : '');
        return '<span class="badge badge-' + cls + '">' + e(label) + '</span>';
    }
    function roleBadge(role) {
        const cls = role === 'admin' ? 'danger' : 'info';
        return '<span class="badge badge-' + cls + '">' + (role ? role.charAt(0).toUpperCase() + role.slice(1) : '') + '</span>';
    }
    function patchStatusBadge(status) {
        const map = { draft: 'secondary', ongoing: 'warning', completed: 'success', pending: 'secondary', in_progress: 'primary', skipped: 'dark' };
        const cls = map[status] || 'secondary';
        const labels = { draft: 'Draft', ongoing: 'Berjalan', completed: 'Selesai', pending: 'Menunggu', in_progress: 'Proses', skipped: 'Skip' };
        return '<span class="badge badge-' + cls + '">' + (labels[status] || (status ? status.charAt(0).toUpperCase() + status.slice(1) : '')) + '</span>';
    }

    function assetPhotoImg(photo, size, cls) {
        size = size || 80; cls = cls || '';
        if (photo) {
            return '<img src="' + e(photo) + '" alt="photo" class="asset-photo ' + e(cls) + '" style="width:' + size + 'px;height:' + size + 'px;object-fit:cover;border-radius:8px">';
        }
        return '<div class="asset-photo-placeholder d-inline-flex align-items-center justify-content-center bg-secondary ' + e(cls) + '" style="width:' + size + 'px;height:' + size + 'px;border-radius:8px">'
             + '<i class="fas fa-image text-white" style="font-size:' + Math.round(size * 0.4) + 'px"></i></div>';
    }

    function qrCodeUrl(text, size) { size = size || 120; return 'https://api.qrserver.com/v1/create-qr-code/?size=' + size + 'x' + size + '&data=' + encodeURIComponent(text); }

    function priceVisible() { const s = Store.session(); return s && s.role === 'admin'; }

    function pagination(page, totalPages, baseUrl, total, perPage) {
        if (totalPages <= 1) return '';
        const window = 5;
        const start = Math.max(1, page - window);
        const end = Math.min(totalPages, page + window);
        let html = '<div class="card-footer"><nav><ul class="pagination pagination-sm justify-content-center mb-0">';
        html += '<li class="page-item ' + (page <= 1 ? 'disabled' : '') + '"><a class="page-link" href="' + baseUrl + 'page=' + (page - 1) + '">&laquo;</a></li>';
        if (start > 1) { html += '<li class="page-item"><a class="page-link" href="' + baseUrl + 'page=1">1</a></li>'; if (start > 2) html += '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>'; }
        for (let i = start; i <= end; i++) { html += '<li class="page-item ' + (i === page ? 'active' : '') + '"><a class="page-link" href="' + baseUrl + 'page=' + i + '">' + i + '</a></li>'; }
        if (end < totalPages) { if (end < totalPages - 1) html += '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>'; html += '<li class="page-item"><a class="page-link" href="' + baseUrl + 'page=' + totalPages + '">' + totalPages + '</a></li>'; }
        html += '<li class="page-item ' + (page >= totalPages ? 'disabled' : '') + '"><a class="page-link" href="' + baseUrl + 'page=' + (page + 1) + '">&raquo;</a></li>';
        html += '</ul></nav>';
        if (total > 0 && perPage > 0) {
            const from = (page - 1) * perPage + 1; const to = Math.min(page * perPage, total);
            html += '<div class="text-center text-muted small mt-1">' + Lang.t('showing') + ' ' + from + '–' + to + ' ' + Lang.t('of') + ' ' + total + '</div>';
        }
        html += '</div>';
        return html;
    }

    // ---- Flash messages (transient) ----
    const _flash = { list: [] };
    function flash(type, message) { _flash.list.push({ type: type, message: message }); }
    function flashMessages() {
        let html = '';
        while (_flash.list.length) {
            const f = _flash.list.shift();
            const icon = f.type === 'success' ? 'check-circle' : (f.type === 'error' ? 'exclamation-triangle' : (f.type === 'warning' ? 'exclamation-triangle' : 'info-circle'));
            html += '<div class="alert alert-' + f.type + ' alert-dismissible fade show" role="alert">'
                 + '<i class="icon fas fa-' + icon + '"></i> ' + e(f.message)
                 + '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>';
        }
        return html;
    }

    // ---- Auth ----
    const Auth = {
        check() { return !!Store.session(); },
        user() { return Store.session() || null; },
        id() { const s = Store.session(); return s ? s.user_id : null; },
        role() { const s = Store.session(); return s ? s.role : null; },
        isAdmin() { return Auth.role() === 'admin'; },
        login(username, password) {
            const users = Store.get('users');
            const u = users.find(x => x.username === username && x.password === password && x.is_active == 1);
            if (u) { Store.setSession(u); return { ok: true, user: u }; }
            return { ok: false };
        },
        logout() { Store.clearSession(); },
        requireLogin() {
            if (!Auth.check()) { Router.navigate('login'); return false; }
            return true;
        },
        requireAdmin() {
            if (!Auth.requireLogin()) return false;
            if (!Auth.isAdmin()) { flash('error', Lang.t('access_denied')); return false; }
            return true;
        },
    };

    // ---- Audit logging ----
    function audit(module, action, description) {
        const s = Store.session();
        Store.insert('audit_trail', {
            module: module, action: action, description: description,
            user_id: s ? s.user_id : null, ip: '127.0.0.1',
        });
    }
    function assetLog(assetId, action, note) {
        const s = Store.session();
        Store.insert('asset_logs', {
            asset_id: assetId, action: action, note: note || '',
            user_id: s ? s.user_id : null,
        });
    }

    // ---- Asset code generator ----
    function generateAssetCode() {
        const assets = Store.get('assets');
        let max = 0;
        assets.forEach(a => { if (!a.deleted_at) { const m = String(a.asset_code).match(/(\d+)\s*$/); if (m) max = Math.max(max, parseInt(m[1], 10)); } });
        return 'AST-' + String(max + 1).padStart(4, '0');
    }

    // ---- Depreciation (straight-line) ----
    function depreciation(asset) {
        const price = Number(asset.price) || 0;
        const usefulLife = 5; // years
        const salvage = price * 0.1;
        const purchase = asset.purchase_date ? new Date(asset.purchase_date) : null;
        let yearsElapsed = 0;
        if (purchase) { yearsElapsed = (Date.now() - purchase.getTime()) / (365.25 * 24 * 3600 * 1000); yearsElapsed = Math.floor(yearsElapsed); if (yearsElapsed < 0) yearsElapsed = 0; }
        if (yearsElapsed > usefulLife) yearsElapsed = usefulLife;
        const annualDep = (price - salvage) / usefulLife;
        const accumulated = annualDep * yearsElapsed;
        const bookValue = price - accumulated;
        return { price: price, useful_life: usefulLife, salvage_value: salvage, years_elapsed: yearsElapsed, accumulated_depreciation: accumulated, book_value: bookValue };
    }

    // ---- Settings ----
    const Setting = {
        get(key, def) {
            const rows = Store.get('settings');
            const r = rows.find(s => s.setting_key === key);
            return r ? r.setting_value : (def !== undefined ? def : '');
        },
        set(key, value) {
            const db = Store.load();
            const rows = db.settings || [];
            const i = rows.findIndex(s => s.setting_key === key);
            if (i >= 0) { rows[i].setting_value = value; rows[i].updated_at = new Date().toISOString(); }
            else { rows.push({ setting_key: key, setting_value: value, updated_at: new Date().toISOString() }); }
            db.settings = rows; Store.save(db);
        },
        companyName() { const v = Setting.get('company_name', ''); return v || 'AssetManager'; },
        companyAddress() { return Setting.get('company_address', ''); },
        companyPhone() { return Setting.get('company_phone', ''); },
        companyEmail() { return Setting.get('company_email', ''); },
    };

    global.H = {
        e: e, url: url, assetUrl: assetUrl, rp: rp, rpCurrency: rpCurrency, tgl: tgl, tglwaktu: tglwaktu,
        statusBadge: statusBadge, roleBadge: roleBadge, patchStatusBadge: patchStatusBadge,
        assetPhotoImg: assetPhotoImg, qrCodeUrl: qrCodeUrl, priceVisible: priceVisible,
        pagination: pagination, flash: flash, flashMessages: flashMessages, audit: audit, assetLog: assetLog,
        generateAssetCode: generateAssetCode, depreciation: depreciation,
    };
    global.Auth = Auth;
    global.Setting = Setting;
})(window);

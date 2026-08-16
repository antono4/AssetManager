/* ============================================================================
   AssetManager HTML Version — Pages (implementasi tiap halaman)
   Dipanggil dari Router. Render melalui Views.shell/blank/print.
   ========================================================================== */
(function (global) {
    'use strict';
    const P = {};

    // ========================================================================
    // LOGIN
    // ========================================================================
    P.loginForm = function () {
        Views.layout(Lang.t('login'), 'blank');
        const html =
            '<div class="login-head"><div class="head-ic"><i class="fas fa-fingerprint"></i></div>'
            + '<h3>' + Lang.t('login_welcome') + '</h3><p>' + Lang.t('login_message') + '</p></div>'
            + '<form onsubmit="return Pages.submitLogin(event)" autocomplete="on">'
            + '<div class="login-field"><input type="text" name="username" id="login-username" placeholder="' + Lang.t('username') + '" required autofocus autocomplete="username"><i class="fas fa-user field-ic"></i></div>'
            + '<div class="login-field"><input type="password" name="password" id="login-password" placeholder="' + Lang.t('password') + '" required autocomplete="current-password"><i class="fas fa-lock field-ic"></i>'
            + '<button type="button" class="pw-toggle" tabindex="-1" aria-label="toggle password"><i class="fas fa-eye"></i></button></div>'
            + '<div class="login-row"><label class="login-check"><input type="checkbox" id="remember"><span class="check-box"></span> ' + Lang.t('remember_me') + '</label></div>'
            + '<button type="submit" class="btn-login"><i class="fas fa-arrow-right-to-bracket mr-1"></i> ' + Lang.t('sign_in') + '</button>'
            + '</form>'
            + '<div class="login-divider">' + Lang.t('login_secure') + '</div>'
            + '<div class="login-sec"><i class="fas fa-lock"></i> ' + Lang.t('login_secured_note') + '</div>'
            + '<div class="alert alert-info mt-3" style="color:#9ec1ff;background:rgba(79,124,255,.12);border:1px solid rgba(79,124,255,.25)">'
            + '<i class="fas fa-info-circle"></i> <strong>admin / admin123</strong> &middot; <strong>staff / staff123</strong></div>';
        Views.blank(html);
    };
    P.submitLogin = function (e) {
        e.preventDefault();
        const username = document.querySelector('#login-username').value;
        const password = document.querySelector('#login-password').value;
        if (!username || !password) { H.flash('error', Lang.t('username_password_required')); return false; }
        const res = Auth.login(username, password);
        if (res.ok) {
            H.audit('auth', 'login', 'User ' + username + ' logged in');
            H.flash('success', Lang.t('login_success', { name: res.user.name }));
            Router.navigate('dashboard');
        } else {
            H.flash('error', Lang.t('login_failed'));
            Views.blank(''); // re-render to show flash
            // Actually we need to re-render login with flash. Simpler: navigate to login
            Router.navigate('login');
        }
        return false;
    };

    // ========================================================================
    // LOGOUT
    // ========================================================================
    P.logout = function () {
        const s = Store.session();
        if (s) H.audit('auth', 'logout', 'User ' + s.username + ' logged out');
        Auth.logout();
        Router.navigate('login');
    };

    // ========================================================================
    // DASHBOARD
    // ========================================================================
    P.dashboard = function () {
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('dashboard'), 'app');

        const assets = Store.get('assets').filter(a => !a.deleted_at);
        const stats = {
            total: assets.length,
            tersedia: assets.filter(a => a.status === 'tersedia').length,
            dipinjam: assets.filter(a => a.status === 'dipinjam').length,
            rusak: assets.filter(a => a.status === 'rusak').length,
        };
        // by category
        const cats = Store.get('categories');
        const byCategory = cats.map(c => {
            const items = assets.filter(a => a.category_id === c.id);
            return { name: c.name, total: items.length, nilai: items.reduce((s, a) => s + Number(a.price || 0), 0) };
        }).filter(c => c.total > 0);
        // recent assets
        const recentAssets = assets.slice().sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 5);
        // recent logs
        const recentLogs = Store.get('asset_logs').slice().sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 8);
        const users = Store.get('users');
        const allAssets = Store.get('assets');
        const userById = id => users.find(u => u.id == id) || { name: 'System' };
        const assetById = id => allAssets.find(a => a.id == id) || {};

        // patching
        const schedules = Store.get('patch_schedules');
        const checklists = Store.get('patch_checklists');
        const patching = {
            total: schedules.length,
            ongoing: schedules.filter(s => s.status === 'ongoing').length,
            draft: schedules.filter(s => s.status === 'draft').length,
            checklists: checklists.length,
            done: checklists.filter(c => c.status === 'completed').length,
        };
        const pPct = patching.checklists > 0 ? Math.round((patching.done / patching.checklists) * 100) : 0;

        const h = new Date().getHours();
        const greeting = h < 11 ? (Lang.is('id') ? 'Selamat pagi' : 'Good morning')
            : (h < 15 ? (Lang.is('id') ? 'Selamat siang' : 'Good afternoon')
            : (h < 18 ? (Lang.is('id') ? 'Selamat sore' : 'Good evening')
            : (Lang.is('id') ? 'Selamat malam' : 'Good evening')));
        const usr = Auth.user() ? Auth.user().name : '';
        const monthNamesID = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const dateStr = Lang.is('id')
            ? new Date().getDate() + ' ' + monthNamesID[new Date().getMonth()] + ' ' + new Date().getFullYear()
            : new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

        const actDotMap = { tersedia: 'dot-ok', dipinjam: 'dot-warn', rusak: 'dot-bad', created: 'dot-info', updated: 'dot-info', patching: 'dot-info', perawatan: 'dot-mut', status_update: 'dot-mut' };
        const actBadgeMap = { tersedia: 'success', dipinjam: 'warning', rusak: 'danger', created: 'info', updated: 'info', patching: 'info', perawatan: 'secondary', status_update: 'secondary' };
        const icMap = { tersedia: 'check', dipinjam: 'hand-paper', rusak: 'wrench', created: 'plus', updated: 'pen', patching: 'shield-halved', perawatan: 'gear', status_update: 'arrows-rotate' };

        let html = '';
        // Welcome
        html += '<div class="dash-welcome"><div><h1>' + greeting + ', ' + H.e(usr) + '! <span class="wave">\u{1F44B}</span></h1>'
            + '<div class="sub">' + (Lang.is('id') ? 'Berikut ringkasan aset Anda hari ini' : 'Here is your asset summary for today') + '</div></div>'
            + '<div class="date-pill"><i class="far fa-calendar-alt"></i> ' + dateStr + '</div></div>';
        // Stat cards
        html += '<div class="row">'
            + '<div class="col-lg-3 col-md-6 col-sm-6"><a href="#assets" class="stat-card sc-info"><div class="sc-bg"></div><i class="fas fa-boxes-stacked sc-icon"></i><div class="sc-body"><div class="sc-num">' + stats.total + '</div><div class="sc-label">' + Lang.t('total_assets') + '</div></div><span class="sc-foot">' + Lang.t('view_details') + ' <i class="fas fa-arrow-right"></i></span></a></div>'
            + '<div class="col-lg-3 col-md-6 col-sm-6"><a href="#assets?status=tersedia" class="stat-card sc-success"><div class="sc-bg"></div><i class="fas fa-circle-check sc-icon"></i><div class="sc-body"><div class="sc-num">' + stats.tersedia + '</div><div class="sc-label">' + Lang.t('available') + '</div></div><span class="sc-foot">' + Lang.t('view_details') + ' <i class="fas fa-arrow-right"></i></span></a></div>'
            + '<div class="col-lg-3 col-md-6 col-sm-6"><a href="#assets?status=dipinjam" class="stat-card sc-warning"><div class="sc-bg"></div><i class="fas fa-hand-paper sc-icon"></i><div class="sc-body"><div class="sc-num">' + stats.dipinjam + '</div><div class="sc-label">' + Lang.t('borrowed') + '</div></div><span class="sc-foot">' + Lang.t('view_details') + ' <i class="fas fa-arrow-right"></i></span></a></div>'
            + '<div class="col-lg-3 col-md-6 col-sm-6"><a href="#assets?status=rusak" class="stat-card sc-danger"><div class="sc-bg"></div><i class="fas fa-screwdriver-wrench sc-icon"></i><div class="sc-body"><div class="sc-num">' + stats.rusak + '</div><div class="sc-label">' + Lang.t('broken') + '</div></div><span class="sc-foot">' + Lang.t('view_details') + ' <i class="fas fa-arrow-right"></i></span></a></div>'
            + '</div>';
        // Charts
        html += '<div class="row"><div class="col-md-8"><div class="card dash-card"><div class="card-header"><h3 class="card-title"><i class="fas fa-chart-column mr-1"></i> ' + Lang.t('asset_distribution') + '</h3></div><div class="card-body"><div id="chart-category" style="min-height:340px"></div></div></div></div>'
            + '<div class="col-md-4"><div class="card dash-card"><div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> ' + Lang.t('asset_status') + '</h3></div><div class="card-body"><div id="chart-status" style="min-height:340px"></div></div></div></div></div>';
        // Patching + Quick links
        html += '<div class="row"><div class="col-md-6"><div class="card dash-card"><div class="card-header"><h3 class="card-title"><i class="fas fa-shield-halved mr-1"></i> ' + Lang.t('patching_quarterly') + '</h3><div class="card-tools"><a href="#patching" class="btn btn-tool"><i class="fas fa-arrow-right"></i></a></div></div>'
            + '<div class="patch-widget"><div class="patch-stats">'
            + '<div class="patch-stat ps-warn"><div class="ps-num">' + (patching.ongoing + patching.draft) + '</div><div class="ps-lbl">' + Lang.t('active_schedules') + '</div></div>'
            + '<div class="patch-stat ps-info"><div class="ps-num">' + patching.checklists + '</div><div class="ps-lbl">' + Lang.t('total_checklists') + '</div></div>'
            + '<div class="patch-stat ps-ok"><div class="ps-num">' + patching.done + '</div><div class="ps-lbl">' + Lang.t('completed') + '</div></div>'
            + '</div><div class="patch-progress-wrap"><div class="pp-head"><span>' + Lang.t('patching_progress', { done: patching.done, total: patching.checklists }) + '</span><span><strong>' + pPct + '%</strong></span></div>'
            + '<div class="patch-progress"><div class="bar" style="width:' + pPct + '%"></div></div></div></div></div></div>';
        // Quick links
        let quickLinks = '<div class="quick-links">'
            + '<a href="#assets" class="quick-link"><i class="fas fa-box"></i> ' + Lang.t('assets') + '</a>'
            + '<a href="#patching" class="quick-link"><i class="fas fa-shield-halved"></i> ' + Lang.t('patching') + '</a>'
            + '<a href="#reports" class="quick-link"><i class="fas fa-file-lines"></i> ' + Lang.t('reports') + '</a>'
            + '<a href="#logs" class="quick-link"><i class="fas fa-clock-rotate-left"></i> ' + Lang.t('history') + '</a>';
        if (Auth.isAdmin()) quickLinks += '<a href="#assets/create" class="quick-link"><i class="fas fa-plus"></i> ' + Lang.t('add_asset') + '</a><a href="#categories" class="quick-link"><i class="fas fa-tags"></i> ' + Lang.t('categories') + '</a><a href="#users" class="quick-link"><i class="fas fa-users"></i> ' + Lang.t('user_management') + '</a>';
        quickLinks += '</div>';
        html += '<div class="col-md-6"><div class="card dash-card"><div class="card-header"><h3 class="card-title"><i class="fas fa-bolt mr-1"></i> ' + (Lang.is('id') ? 'Akses Cepat' : 'Quick Access') + '</h3></div><div class="card-body">' + quickLinks + '</div></div></div></div>';
        // Recent assets + Activity
        html += '<div class="row"><div class="col-md-6"><div class="card dash-card"><div class="card-header"><h3 class="card-title"><i class="fas fa-box-open mr-1"></i> ' + Lang.t('recent_assets') + '</h3><div class="card-tools"><a href="#assets" class="btn btn-tool"><i class="fas fa-arrow-right"></i></a></div></div><div class="card-body p-0"><table class="table recent-table"><thead><tr><th width="50">' + Lang.t('photo') + '</th><th>' + Lang.t('asset_code') + '</th><th>' + Lang.t('name') + '</th><th>' + Lang.t('status') + '</th></tr></thead><tbody>';
        if (recentAssets.length === 0) html += '<tr><td colspan="4" class="text-muted text-center">' + Lang.t('no_data') + '</td></tr>';
        recentAssets.forEach(a => {
            html += '<tr style="cursor:pointer" onclick="location.hash=\'#assets/' + a.id + '\'"><td class="text-center">' + H.assetPhotoImg(a.photo, 36) + '</td><td class="asset-code">' + H.e(a.asset_code) + '</td><td>' + H.e(a.name) + '</td><td>' + H.statusBadge(a.status) + '</td></tr>';
        });
        html += '</tbody></table></div></div></div>';
        // Activity
        html += '<div class="col-md-6"><div class="card dash-card"><div class="card-header"><h3 class="card-title"><i class="fas fa-wave-square mr-1"></i> ' + Lang.t('recent_activity') + '</h3><div class="card-tools"><a href="#logs" class="btn btn-tool"><i class="fas fa-arrow-right"></i></a></div></div><div class="card-body"><ul class="act-timeline">';
        if (recentLogs.length === 0) html += '<li class="text-muted">' + Lang.t('no_activity') + '</li>';
        recentLogs.forEach(l => {
            const dot = actDotMap[l.action] || 'dot-mut';
            const bdg = actBadgeMap[l.action] || 'secondary';
            const ic = icMap[l.action] || 'circle';
            const a = assetById(l.asset_id);
            const usrName = userById(l.user_id).name || 'System';
            html += '<li><span class="act-dot ' + dot + '"><i class="fas fa-' + ic + '"></i></span><div class="act-body"><div class="act-top"><span class="act-user">' + H.e(usrName) + '</span><span class="badge badge-' + bdg + ' act-badge">' + H.e(l.action) + '</span><span class="act-time ml-auto"><i class="far fa-clock"></i> ' + H.tglwaktu(l.created_at) + '</span></div>'
                + '<div class="act-asset"><i class="fas fa-barcode text-muted"></i> ' + H.e(a.asset_code || '') + ' &middot; ' + H.e(a.name || '') + '</div>'
                + (l.note ? '<div class="act-note">"' + H.e(l.note) + '"</div>' : '') + '</div></li>';
        });
        html += '</ul></div></div></div></div>';

        // Chart scripts
        const catLabels = JSON.stringify(byCategory.map(c => c.name));
        const catTotals = JSON.stringify(byCategory.map(c => c.total));
        const statusLabels = JSON.stringify([Lang.t('status_tersedia'), Lang.t('status_dipinjam'), Lang.t('status_rusak')]);
        const totalLabel = Lang.t('total_assets');
        const scripts = '<script>\
$(function () {\
  new ApexCharts(document.querySelector("#chart-category"), {\
    chart: { type: "bar", height: 340, toolbar: { show: false }, fontFamily: "Source Sans Pro", animations: { enabled: true } },\
    plotOptions: { bar: { borderRadius: 8, columnWidth: "55%", distributed: true } },\
    series: [{ name: "' + totalLabel + '", data: ' + catTotals + ' }],\
    colors: ["#3a6bdb","#2b4575","#5b7cfa","#1ea87a","#f0a020","#e5484d","#8898aa"],\
    xaxis: { categories: ' + catLabels + ', labels: { style: { colors: "#6c7a8c", fontSize: "12px" } } },\
    yaxis: { labels: { style: { colors: "#8898aa" } } },\
    dataLabels: { enabled: true, style: { colors: ["#fff"] }, fontWeight: 600 },\
    grid: { borderColor: "#eef1f6", strokeDashArray: 4 }, legend: { show: false }, tooltip: { theme: "light" }\
  }).render();\
  new ApexCharts(document.querySelector("#chart-status"), {\
    chart: { type: "donut", height: 340, fontFamily: "Source Sans Pro" },\
    series: [' + stats.tersedia + ',' + stats.dipinjam + ',' + stats.rusak + '],\
    labels: ' + statusLabels + ',\
    colors: ["#1ea87a", "#f0a020", "#e5484d"],\
    legend: { position: "bottom", fontSize: "13px", labels: { colors: "#6c7a8c" } },\
    dataLabels: { enabled: true, formatter: function(v,o){ return o.w.config.series[o.seriesIndex]; }, style: { colors: ["#fff"], fontSize: "14px", fontWeight: 700 } },\
    plotOptions: { pie: { donut: { size: "68%", labels: { show: true, name: { fontSize: "13px", color: "#8898aa" }, total: { show: true, fontSize: "22px", fontWeight: 700, color: "#2b3a55", label: "' + totalLabel + '" } } } } },\
    stroke: { width: 0 }, tooltip: { theme: "light" }\
  }).render();\
});\
<\/script>';
        Views.shell(html, scripts);
    };

    // ========================================================================
    // ASSETS — LIST
    // ========================================================================
    P.assetIndex = function (query) {
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('asset_list'), 'app');
        const search = (query.search || '').toLowerCase();
        const status = query.status || '';
        const category = query.category || '';
        const page = parseInt(query.page) || 1;
        const perPage = 10;

        let assets = Store.get('assets').filter(a => !a.deleted_at);
        const cats = Store.get('categories');
        assets.forEach(a => { a.category_name = (cats.find(c => c.id == a.category_id) || {}).name || ''; });
        if (search) assets = assets.filter(a => (a.asset_code + ' ' + a.name + ' ' + (a.brand_spec || '') + ' ' + (a.location || '')).toLowerCase().includes(search));
        if (status) assets = assets.filter(a => a.status === status);
        if (category) assets = assets.filter(a => String(a.category_id) === String(category));
        assets.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        const total = assets.length;
        const totalPages = Math.max(1, Math.ceil(total / perPage));
        const paged = assets.slice((page - 1) * perPage, page * perPage);

        const q = new URLSearchParams();
        if (search) q.set('search', search); if (status) q.set('status', status); if (category) q.set('category', category);
        const base = '#assets?' + (q.toString() ? q.toString() + '&' : '');

        let html = '<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-box mr-1"></i> ' + Lang.t('asset_list') + ' <small class="text-muted">(' + total + ' ' + Lang.t('data') + ')</small></h3>'
            + '<div class="card-tools"><a href="#assets/export?' + q.toString() + '" class="btn btn-success btn-sm" title="' + Lang.t('export_csv') + '"><i class="fas fa-file-csv"></i> CSV</a>'
            + (Auth.isAdmin() ? '<a href="#assets/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> ' + Lang.t('add_asset') + '</a>' : '')
            + '</div></div><div class="card-body">';
        // filter form
        html += '<form method="get" onsubmit="return Pages.filterAssets(event)" class="search-bar mb-3"><div class="row">'
            + '<div class="col-md-5"><div class="input-group"><input type="text" name="search" class="form-control" placeholder="' + Lang.t('search_placeholder') + '" value="' + H.e(search) + '"><div class="input-group-append"><span class="input-group-text"><i class="fas fa-search"></i></span></div></div></div>'
            + '<div class="col-md-3"><select name="status" class="form-control"><option value="">' + Lang.t('all_status') + '</option>'
            + '<option value="tersedia"' + (status === 'tersedia' ? ' selected' : '') + '>' + Lang.t('status_tersedia') + '</option>'
            + '<option value="dipinjam"' + (status === 'dipinjam' ? ' selected' : '') + '>' + Lang.t('status_dipinjam') + '</option>'
            + '<option value="rusak"' + (status === 'rusak' ? ' selected' : '') + '>' + Lang.t('status_rusak') + '</option>'
            + '</select></div>'
            + '<div class="col-md-3"><select name="category" class="form-control"><option value="">' + Lang.t('all_categories') + '</option>'
            + cats.map(c => '<option value="' + c.id + '"' + (String(category) === String(c.id) ? ' selected' : '') + '>' + H.e(c.name) + '</option>').join('')
            + '</select></div>'
            + '<div class="col-md-1"><button type="submit" class="btn btn-default btn-block"><i class="fas fa-filter"></i></button></div></div></form>';

        if (total === 0) {
            html += '<div class="empty-state"><i class="fas fa-box-open"></i><p class="mt-3">' + Lang.t('no_matching_assets') + '</p></div>';
        } else {
            html += '<div class="table-responsive"><table class="table table-hover table-assets"><thead><tr><th width="60">' + Lang.t('photo') + '</th><th>' + Lang.t('asset_code') + '</th><th>' + Lang.t('name') + '</th><th>' + Lang.t('category') + '</th><th>' + Lang.t('location') + '</th><th>' + Lang.t('status') + '</th>' + (H.priceVisible() ? '<th class="text-right">' + Lang.t('price') + '</th>' : '') + '<th class="text-center">' + Lang.t('action') + '</th></tr></thead><tbody>';
            paged.forEach(a => {
                html += '<tr style="cursor:pointer" onclick="location.hash=\'#assets/' + a.id + '\'"><td class="text-center">' + H.assetPhotoImg(a.photo, 44) + '</td><td class="asset-code">' + H.e(a.asset_code) + '</td><td>' + H.e(a.name) + '<br><small class="text-muted">' + H.e(a.brand_spec || '') + '</small></td><td><span class="badge badge-light">' + H.e(a.category_name) + '</span></td><td><i class="fas fa-map-marker-alt text-muted mr-1"></i>' + H.e(a.location || '-') + '</td><td>' + H.statusBadge(a.status) + '</td>' + (H.priceVisible() ? '<td class="text-right">' + H.rp(a.price) + '</td>' : '') + '<td class="text-center"><a href="#assets/' + a.id + '" class="btn btn-info btn-sm" title="' + Lang.t('asset_detail') + '"><i class="fas fa-eye"></i></a>' + (Auth.isAdmin() ? ' <a href="#assets/' + a.id + '/edit" class="btn btn-warning btn-sm" title="' + Lang.t('edit') + '"><i class="fas fa-edit"></i></a>' : '') + '</td></tr>';
            });
            html += '</tbody></table></div>';
        }
        html += '</div>' + H.pagination(page, totalPages, base, total, perPage) + '</div>';
        Views.shell(html);
    };
    P.filterAssets = function (e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const p = new URLSearchParams();
        for (const [k, v] of fd.entries()) if (v) p.set(k, v);
        Router.navigate('assets?' + p.toString());
        return false;
    };

    // ========================================================================
    // ASSETS — SHOW
    // ========================================================================
    P.assetShow = function (params) {
        const id = params.id;
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('asset_detail'), 'app');
        const asset = Store.get('assets').find(a => a.id == id && !a.deleted_at);
        if (!asset) { Views.error(); return; }
        asset.category_name = (Store.get('categories').find(c => c.id == asset.category_id) || {}).name || '';
        const logs = Store.get('asset_logs').filter(l => l.asset_id == id).sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        const users = Store.get('users');
        const dep = H.depreciation(asset);

        let html = '<div class="row"><div class="col-md-5"><div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> ' + Lang.t('asset_info') + '</h3></div><div class="card-body"><div class="text-center mb-3">'
            + H.assetPhotoImg(asset.photo, 110, 'mb-2') + '<h4 class="mt-2 mb-0">' + H.e(asset.name) + '</h4><p class="text-muted asset-code">' + H.e(asset.asset_code) + '</p>' + H.statusBadge(asset.status) + '</div>'
            + '<dl class="asset-detail row">'
            + '<dt class="col-sm-4">' + Lang.t('category') + '</dt><dd class="col-sm-8">' + H.e(asset.category_name) + '</dd>'
            + '<dt class="col-sm-4">' + Lang.t('brand_spec') + '</dt><dd class="col-sm-8">' + H.e(asset.brand_spec || '-') + '</dd>'
            + '<dt class="col-sm-4">' + Lang.t('location') + '</dt><dd class="col-sm-8">' + H.e(asset.location || '-') + '</dd>'
            + '<dt class="col-sm-4">' + Lang.t('purchase_date') + '</dt><dd class="col-sm-8">' + H.tgl(asset.purchase_date) + '</dd>';
        if (H.priceVisible()) {
            html += '<dt class="col-sm-4">' + Lang.t('price') + '</dt><dd class="col-sm-8"><strong>' + H.rp(asset.price) + '</strong></dd>';
            if (dep.years_elapsed > 0) {
                html += '<dt class="col-sm-4">' + Lang.t('book_value') + '</dt><dd class="col-sm-8"><strong class="text-info">' + H.rp(dep.book_value) + '</strong> <small class="text-muted">(' + Lang.t('depreciation') + ': ' + H.rp(dep.accumulated_depreciation) + ', ' + dep.years_elapsed + 'y/' + dep.useful_life + 'y)</small></dd>';
            }
        }
        html += '<dt class="col-sm-4">' + Lang.t('created_at') + '</dt><dd class="col-sm-8">' + H.tglwaktu(asset.created_at) + '</dd>'
            + '<dt class="col-sm-4">' + Lang.t('updated_at') + '</dt><dd class="col-sm-8">' + H.tglwaktu(asset.updated_at) + '</dd></dl>'
            + '<div class="text-center mt-2"><img src="' + H.qrCodeUrl(location.origin + location.pathname + '#assets/' + asset.id, 120) + '" alt="QR" class="img-fluid" style="width:120px;height:120px;border:1px solid #dee2e6;border-radius:8px"><br><small class="text-muted">' + Lang.t('qr_code') + ' — ' + H.e(asset.asset_code) + '</small></div>'
            + '</div><div class="card-footer"><a href="#assets" class="btn btn-default"><i class="fas fa-arrow-left"></i> ' + Lang.t('back') + '</a>'
            + (asset.status === 'tersedia' ? '<a href="#assets/' + asset.id + '/borrow" class="btn btn-warning"><i class="fas fa-hand-paper"></i> ' + Lang.t('borrow') + '</a>' : '')
            + (Auth.isAdmin() ? '<a href="#assets/' + asset.id + '/edit" class="btn btn-warning"><i class="fas fa-edit"></i> ' + Lang.t('edit') + '</a><form action="#assets/' + asset.id + '/delete" method="post" class="d-inline" onsubmit="return Pages.deleteAsset(event,' + asset.id + ')"><button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> ' + Lang.t('delete') + '</button></form>' : '')
            + '</div></div>';
        // quick status change
        html += '<div class="card card-warning card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-exchange-alt mr-1"></i> ' + Lang.t('change_status_quick') + '</h3></div><div class="card-body"><form onsubmit="return Pages.changeStatus(event,' + asset.id + ')">'
            + '<div class="form-group"><label>' + Lang.t('new_status') + '</label><select name="status" class="form-control">'
            + '<option value="tersedia"' + (asset.status === 'tersedia' ? ' selected' : '') + '>' + Lang.t('status_tersedia') + '</option>'
            + '<option value="dipinjam"' + (asset.status === 'dipinjam' ? ' selected' : '') + '>' + Lang.t('status_dipinjam') + '</option>'
            + '<option value="rusak"' + (asset.status === 'rusak' ? ' selected' : '') + '>' + Lang.t('status_rusak') + '</option>'
            + '</select></div><div class="form-group"><label>' + Lang.t('note_optional') + '</label><textarea name="note" class="form-control" rows="2"></textarea></div>'
            + '<button type="submit" class="btn btn-warning btn-block"><i class="fas fa-save"></i> ' + Lang.t('save_status') + '</button></form></div></div></div>';
        // history
        html += '<div class="col-md-7"><div class="card card-info card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-1"></i> ' + Lang.t('asset_history') + '</h3></div><div class="card-body">';
        if (logs.length === 0) html += '<div class="empty-state"><i class="fas fa-clock"></i><p class="mt-3">' + Lang.t('no_history') + '</p></div>';
        else {
            html += '<ul class="timeline-log">';
            logs.forEach(l => {
                const u = users.find(x => x.id == l.user_id) || { name: 'System' };
                const cls = l.action === 'dipinjam' ? 'warning' : (l.action === 'rusak' ? 'danger' : (l.action === 'tersedia' ? 'success' : 'secondary'));
                html += '<li><div class="d-flex justify-content-between"><strong><span class="badge badge-' + cls + '">' + H.e(l.action) + '</span></strong><small class="text-muted"><i class="far fa-clock"></i> ' + H.tglwaktu(l.created_at) + '</small></div>'
                    + '<div class="mt-1"><small>' + Lang.t('by') + ' <strong>' + H.e(u.name) + '</strong></small></div>'
                    + (l.note ? '<div class="text-muted small mt-1">"' + H.e(l.note) + '"</div>' : '') + '</li>';
            });
            html += '</ul>';
        }
        html += '</div></div></div></div>';
        Views.shell(html);
    };
    P.changeStatus = function (e, id) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const status = fd.get('status'); const note = fd.get('note');
        Store.update('assets', id, { status: status });
        H.assetLog(id, status, note);
        H.audit('assets', 'status_update', 'Status changed for asset ' + id);
        H.flash('success', Lang.t('status_changed'));
        Router.navigate('assets/' + id);
        return false;
    };
    P.deleteAsset = function (e, id) {
        e.preventDefault();
        if (!confirm(Lang.t('delete') + '?')) return false;
        const asset = Store.get('assets').find(a => a.id == id);
        Store.update('assets', id, { deleted_at: new Date().toISOString() });
        H.assetLog(id, 'deleted', 'Asset moved to trash');
        H.audit('assets', 'deleted', 'Soft-deleted ' + (asset ? asset.asset_code : id));
        H.flash('success', Lang.t('soft_deleted'));
        Router.navigate('assets');
        return false;
    };

    // ========================================================================
    // ASSETS — FORM (create/edit)
    // ========================================================================
    P.assetForm = function (params) {
        const id = (typeof params === 'object' && params !== null) ? (params.id || null) : (params || null);
        if (!Auth.requireAdmin()) return;
        const isEdit = !!id;
        let asset = { asset_code: H.generateAssetCode(), name: '', category_id: '', brand_spec: '', location: '', status: 'tersedia', purchase_date: '', price: 0, photo: '' };
        if (isEdit) { asset = Store.get('assets').find(a => a.id == id) || asset; }
        Views.layout(isEdit ? Lang.t('edit_asset') : Lang.t('add_new_asset'), 'app');
        const cats = Store.get('categories');
        let html = '<div class="card card-primary"><div class="card-header"><h3 class="card-title"><i class="fas fa-' + (isEdit ? 'edit' : 'plus') + ' mr-1"></i> ' + (isEdit ? Lang.t('edit_asset') : Lang.t('add_new_asset')) + '</h3></div>'
            + '<form onsubmit="return Pages.saveAsset(event,' + (isEdit ? id : 'null') + ')"><div class="card-body">'
            + '<div class="form-group"><label>' + Lang.t('asset_code') + ' <small class="text-muted">(' + Lang.t('auto') + ')</small></label><input type="text" class="form-control" value="' + H.e(asset.asset_code) + '" disabled></div>';
        if (isEdit && asset.photo) {
            html += '<div class="form-group"><label>' + Lang.t('current_photo') + '</label><div class="d-flex align-items-center">' + H.assetPhotoImg(asset.photo, 100) + '<form onsubmit="return Pages.removePhoto(event,' + id + ')" class="d-inline ml-3"><button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> ' + Lang.t('remove_photo') + '</button></form></div></div>';
        }
        html += '<div class="form-group"><label>' + Lang.t('photo') + '</label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-image"></i></span></div><input type="file" name="photo" id="asset-photo" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp" onchange="Pages.previewPhoto(event)"></div><small class="text-muted">' + Lang.t('photo_hint') + '</small></div>'
            + '<div class="row"><div class="col-md-8"><div class="form-group"><label>' + Lang.t('name') + ' <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required value="' + H.e(asset.name) + '"></div></div>'
            + '<div class="col-md-4"><div class="form-group"><label>' + Lang.t('category') + ' <span class="text-danger">*</span></label><select name="category_id" class="form-control" required>'
            + cats.map(c => '<option value="' + c.id + '"' + (String(asset.category_id) === String(c.id) ? ' selected' : '') + '>' + H.e(c.name) + '</option>').join('')
            + '</select></div></div></div>'
            + '<div class="form-group"><label>' + Lang.t('brand_spec') + '</label><input type="text" name="brand_spec" class="form-control" value="' + H.e(asset.brand_spec || '') + '" placeholder="Mis: Dell OptiPlex 7090 / i7 / 16GB"></div>'
            + '<div class="row"><div class="col-md-6"><div class="form-group"><label>' + Lang.t('location') + '</label><input type="text" name="location" class="form-control" value="' + H.e(asset.location || '') + '"></div></div>'
            + '<div class="col-md-6"><div class="form-group"><label>' + Lang.t('status') + '</label><select name="status" class="form-control">'
            + '<option value="tersedia"' + (asset.status === 'tersedia' ? ' selected' : '') + '>' + Lang.t('status_tersedia') + '</option>'
            + '<option value="dipinjam"' + (asset.status === 'dipinjam' ? ' selected' : '') + '>' + Lang.t('status_dipinjam') + '</option>'
            + '<option value="rusak"' + (asset.status === 'rusak' ? ' selected' : '') + '>' + Lang.t('status_rusak') + '</option>'
            + '</select></div></div></div>'
            + '<div class="row"><div class="col-md-6"><div class="form-group"><label>' + Lang.t('purchase_date') + '</label><input type="date" name="purchase_date" class="form-control" value="' + H.e(asset.purchase_date || '') + '"></div></div>'
            + '<div class="col-md-6"><div class="form-group"><label>' + Lang.t('price') + ' (Rp)</label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="price" class="form-control" min="0" step="1000" value="' + H.e(asset.price || 0) + '"></div></div></div></div>'
            + '</div><div class="card-footer"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> ' + Lang.t('save') + '</button> <a href="#' + (isEdit ? 'assets/' + id : 'assets') + '" class="btn btn-default">' + Lang.t('cancel') + '</a></div></form></div>';
        Views.shell(html);
    };
    let _pendingPhoto = null;
    P.previewPhoto = function (e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = ev => { _pendingPhoto = ev.target.result; };
        reader.readAsDataURL(file);
    };
    P.saveAsset = function (e, id) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const name = fd.get('name'); const category_id = fd.get('category_id');
        if (!name || !category_id) { H.flash('error', Lang.t('name_category_required')); return false; }
        const data = { name: name, category_id: parseInt(category_id), brand_spec: fd.get('brand_spec'), location: fd.get('location'), status: fd.get('status'), purchase_date: fd.get('purchase_date'), price: Number(fd.get('price')) || 0 };
        if (_pendingPhoto) data.photo = _pendingPhoto;
        _pendingPhoto = null;
        if (id) {
            Store.update('assets', id, data);
            H.assetLog(id, 'updated', 'Asset updated');
            H.audit('assets', 'updated', 'Updated asset ' + (Store.get('assets').find(a => a.id == id) || {}).asset_code);
            H.flash('success', Lang.t('asset_updated'));
            Router.navigate('assets/' + id);
        } else {
            data.asset_code = H.generateAssetCode();
            const created = Store.insert('assets', data);
            H.assetLog(created.id, 'created', 'Asset created');
            H.audit('assets', 'created', 'Added asset ' + created.asset_code);
            H.flash('success', Lang.t('asset_added'));
            Router.navigate('assets/' + created.id);
        }
        return false;
    };
    P.removePhoto = function (e, id) {
        e.preventDefault();
        Store.update('assets', id, { photo: '' });
        H.audit('assets', 'photo_removed', 'Removed photo for asset ' + id);
        H.flash('success', Lang.t('photo_removed'));
        Router.navigate('assets/' + id + '/edit');
        return false;
    };

    global.Pages = P;
})(window);

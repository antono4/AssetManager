/* ============================================================================
   AssetManager HTML Version — Views (render halaman)
   Mensimulasikan app/views/*.php di sisi klien. Render ke #app-root via innerHTML.
   Library (jQuery, Bootstrap, AdminLTE, ApexCharts) dimuat sekali di index.html.
   Mengakses: H, Auth, Store, Lang, Router, Setting, Pages (global).
   ========================================================================== */
(function (global) {
    'use strict';

    const V = {};
    let layoutState = { pageTitle: '', layout: 'app' };
    let pendingScripts = '';

    function getRoot() { return document.getElementById('app-root'); }
    function getBoot() { return document.getElementById('boot'); }
    function hideBoot() { const b = getBoot(); if (b) b.classList.add('hidden'); }
    function setTitle(title) { document.title = title + ' \u00b7 ' + Lang.t('app_name'); }
    function ensureDarkmodeLink() {
        let link = document.getElementById('darkmode-link');
        const dark = Store.getPref('dark_mode', '0') === '1';
        if (dark && !link) { link = document.createElement('link'); link.id = 'darkmode-link'; link.rel = 'stylesheet'; link.href = 'assets/css/darkmode.css'; document.head.appendChild(link); }
        else if (!dark && link) { link.remove(); }
        document.body.classList.toggle('dark-mode', dark);
    }

    function runPendingScripts(root) {
        let code = pendingScripts; pendingScripts = '';
        if (!code) return;
        // Strip <script>/<\/script> wrappers if present (views may pass either raw JS or tagged JS)
        code = code.replace(/<script[^>]*>/gi, '').replace(/<\/script>/gi, '');
        try { new Function(code).call(global); } catch (e) { console.error('View script error:', e, '\n', code); }
    }

    // ---- Layout shell (app.php) ----
    function renderShell(innerHtml, scripts) {
        ensureDarkmodeLink();
        hideBoot();
        setTitle(layoutState.pageTitle);
        const u = Auth.user() || {};
        const role = u.role || 'guest';
        const lang = Lang.current();
        const companyName = Setting.companyName();
        const unread = Store.get('notifications').filter(n => !n.is_read).length;
        const overdue = Store.get('borrowings').filter(b => b.status === 'borrowed' && b.expected_return && new Date(b.expected_return.replace(' ', 'T')) < new Date()).length;

        const navItems = [
            { href: 'dashboard', icon: 'tachometer-alt', label: Lang.t('dashboard'), page: Lang.t('dashboard') },
            { href: 'assets', icon: 'box', label: Lang.t('assets'), startsWith: Lang.t('asset_list') },
            { href: 'borrowings', icon: 'hand-paper', label: Lang.t('borrowing'), page: Lang.t('borrowing') },
            { href: 'logs', icon: 'history', label: Lang.t('history'), page: Lang.t('activity_log') },
            { href: 'reports', icon: 'file-alt', label: Lang.t('reports'), page: Lang.t('asset_report') },
            { href: 'patching', icon: 'shield-alt', label: Lang.t('patching'), startWithAny: [Lang.t('patch_schedule'), Lang.t('checklist')] },
        ];
        let navHtml = navItems.map(n => {
            let active = '';
            if (n.startsWith && layoutState.pageTitle && layoutState.pageTitle.startsWith(n.startsWith)) active = 'active';
            else if (n.startWithAny && layoutState.pageTitle && n.startWithAny.some(s => layoutState.pageTitle.startsWith(s))) active = 'active';
            else if (n.page && layoutState.pageTitle === n.page) active = 'active';
            return '<li class="nav-item"><a href="#' + n.href + '" class="nav-link ' + active + '"><i class="nav-icon fas fa-' + n.icon + '"></i><p>' + H.e(n.label) + '</p></a></li>';
        }).join('');

        if (role === 'admin') {
            const adminItems = [
                { href: 'categories', icon: 'tags', label: Lang.t('categories') },
                { href: 'users', icon: 'users', label: Lang.t('user_management') },
                { href: 'assets/trash', icon: 'trash-restore', label: Lang.t('trash') },
                { href: 'audit', icon: 'clipboard-list', label: Lang.t('audit_trail') },
                { href: 'api-tokens', icon: 'key', label: Lang.t('api_token') },
                { href: 'assets/import', icon: 'file-import', label: Lang.t('import_csv') },
                { href: 'settings', icon: 'building', label: Lang.t('company_settings') },
            ];
            navHtml += '<li class="nav-header">' + Lang.t('administration') + '</li>'
                     + adminItems.map(n => '<li class="nav-item"><a href="#' + n.href + '" class="nav-link"><i class="nav-icon fas fa-' + n.icon + '"></i><p>' + H.e(n.label) + '</p></a></li>').join('');
        }

        const html =
            '<div class="wrapper">'
            + '<nav class="main-header navbar navbar-expand navbar-white navbar-light">'
            + '<ul class="navbar-nav">'
            + '<li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>'
            + '<li class="nav-item"><form onsubmit="return Views.doSearch(event)" class="form-inline ml-2"><div class="input-group input-group-sm" style="width:220px">'
            + '<input type="text" id="global-search" class="form-control" placeholder="' + H.e(Lang.t('global_search')) + '">'
            + '<div class="input-group-append"><button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button></div>'
            + '</div></form></li>'
            + '</ul>'
            + '<ul class="navbar-nav ml-auto">'
            + '<li class="nav-item dropdown">'
            + '<a class="nav-link" data-toggle="dropdown" href="#" title="' + H.e(Lang.t('notifications')) + '"><i class="far fa-bell"></i>'
            + (unread > 0 ? '<span class="badge badge-warning navbar-badge">' + unread + '</span>' : '') + '</a>'
            + '<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">'
            + '<span class="dropdown-item dropdown-header">' + unread + ' ' + H.e(Lang.t('notifications')) + '</span><div class="dropdown-divider"></div>'
            + '<a href="#notifications" class="dropdown-item"><i class="fas fa-bell mr-2 text-info"></i> ' + H.e(Lang.t('notifications')) + (unread > 0 ? '<span class="float-right text-muted text-sm">' + unread + ' baru</span>' : '') + '</a>'
            + (overdue > 0 ? '<a href="#borrowings" class="dropdown-item"><i class="fas fa-clock mr-2 text-danger"></i> ' + overdue + ' overdue</a>' : '')
            + '<div class="dropdown-divider"></div><a href="#notifications" class="dropdown-item dropdown-footer">' + H.e(Lang.t('view_details')) + '</a>'
            + '</div></li>'
            + '<li class="nav-item"><a class="nav-link" href="#dark-mode" title="' + H.e(Store.getPref('dark_mode', '0') === '1' ? Lang.t('light_mode') : Lang.t('dark_mode')) + '"><i class="fas fa-' + (Store.getPref('dark_mode', '0') === '1' ? 'sun' : 'moon') + '"></i></a></li>'
            + '<li class="nav-item dropdown"><a class="nav-link" data-toggle="dropdown" href="#" title="' + H.e(Lang.t('language')) + '"><i class="fas fa-language"></i><span class="d-none d-md-inline">' + (lang === 'en' ? 'EN' : 'ID') + '</span></a>'
            + '<div class="dropdown-menu dropdown-menu-right">'
            + '<a href="#language/en" class="dropdown-item ' + (lang === 'en' ? 'active' : '') + '"><span class="flag-icon flag-icon-us mr-2"></span> English' + (lang === 'en' ? '<i class="fas fa-check float-right"></i>' : '') + '</a>'
            + '<a href="#language/id" class="dropdown-item ' + (lang === 'id' ? 'active' : '') + '"><span class="flag-icon flag-icon-id mr-2"></span> Bahasa Indonesia' + (lang === 'id' ? '<i class="fas fa-check float-right"></i>' : '') + '</a>'
            + '</div></li>'
            + '<li class="nav-item"><a class="nav-link" href="#profile" title="' + H.e(Lang.t('profile')) + '"><i class="fas fa-user-circle"></i></a></li>'
            + '<li class="nav-item"><a class="nav-link" href="#logout" title="' + H.e(Lang.t('logout')) + '"><i class="fas fa-sign-out-alt"></i></a></li>'
            + '</ul></nav>'
            + '<aside class="main-sidebar sidebar-dark-info elevation-4">'
            + '<a href="#dashboard" class="brand-link"><i class="nav-icon fas fa-cubes brand-image ml-3 mt-1" style="font-size:1.6rem;color:#fff;opacity:.9"></i><span class="brand-text font-weight-bold">' + H.e(companyName) + '</span></a>'
            + '<div class="sidebar"><div class="user-panel mt-3 pb-3 mb-3 d-flex">'
            + '<div class="image"><span class="brand-image img-circle elevation-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:#fff;color:#343a40;font-weight:700">' + H.e(String(u.name || '?').charAt(0).toUpperCase()) + '</span></div>'
            + '<div class="info"><a href="#profile" class="d-block">' + H.e(u.name || 'Guest') + '</a><span class="badge badge-' + (role === 'admin' ? 'danger' : 'info') + '">' + (role ? role.charAt(0).toUpperCase() + role.slice(1) : '') + '</span></div>'
            + '</div><nav class="mt-2"><ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">' + navHtml + '</ul></nav></div></aside>'
            + '<div class="content-wrapper"><section class="content-header"><div class="container-fluid"><div class="row mb-2">'
            + '<div class="col-sm-6"><h1>' + H.e(layoutState.pageTitle) + '</h1></div>'
            + '<div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="#dashboard">' + H.e(Lang.t('home')) + '</a></li><li class="breadcrumb-item active">' + H.e(layoutState.pageTitle) + '</li></ol></div>'
            + '</div></div></section>'
            + '<section class="content"><div class="container-fluid">' + H.flashMessages() + innerHtml + '</div></section></div>'
            + '<footer class="main-footer"><strong>&copy; ' + new Date().getFullYear() + ' ' + Lang.t('app_name') + ' v1.0.0 (HTML).</strong> ' + Lang.t('built_with')
            + '<div class="float-right d-none d-sm-inline-block"><b>' + Lang.t('database') + ':</b> LocalStorage</div></footer>'
            + '</div>';

        const root = getRoot();
        root.classList.remove('hidden');
        root.innerHTML = html;
        // re-init AdminLTE treeview/pushmenu behaviors on the new DOM
        if (global.jQuery && global.jQuery.fn) {
            try { global.jQuery('[data-widget="pushmenu"]').pushMenu && global.jQuery('[data-widget="pushmenu"]').pushMenu(); } catch (e) {}
            try { global.jQuery('[data-widget="treeview"]').Treeview && global.jQuery('[data-widget="treeview"]').Treeview('init'); } catch (e) {}
        }
        pendingScripts = scripts || '';
        runPendingScripts();
        afterRender();
    }

    // ---- Blank layout (login) ----
    function renderBlank(innerHtml) {
        hideBoot();
        setTitle(layoutState.pageTitle);
        const lang = Lang.current();
        const html =
            '<div class="login-wrap">'
            + '<div class="login-hero"><div class="login-hero-grid"></div><div class="login-particles" id="particles"></div>'
            + '<div class="login-hero-content">'
            + '<a href="#login" class="login-hero-logo"><span class="logo-icon"><i class="fas fa-cubes"></i></span><span>' + Lang.t('app_name') + '</span></a>'
            + '<h2>' + Lang.t('login_hero_title') + '</h2>'
            + '<p class="hero-sub">' + Lang.t('login_hero_sub') + '</p>'
            + '<div class="login-features">'
            + '<div class="login-feature"><span class="feat-ic ic-1"><i class="fas fa-boxes-stacked"></i></span><span class="feat-tx">' + Lang.t('login_feat1_t') + '<small>' + Lang.t('login_feat1_d') + '</small></span></div>'
            + '<div class="login-feature"><span class="feat-ic ic-2"><i class="fas fa-shield-halved"></i></span><span class="feat-tx">' + Lang.t('login_feat2_t') + '<small>' + Lang.t('login_feat2_d') + '</small></span></div>'
            + '<div class="login-feature"><span class="feat-ic ic-3"><i class="fas fa-chart-pie"></i></span><span class="feat-tx">' + Lang.t('login_feat3_t') + '<small>' + Lang.t('login_feat3_d') + '</small></span></div>'
            + '</div></div>'
            + '<div class="login-hero-foot">&copy; ' + new Date().getFullYear() + ' ' + Lang.t('app_name') + ' v1.0.0 (HTML)</div></div>'
            + '<div class="login-form-panel"><div class="login-card">'
            + '<div class="login-lang"><a href="#language/en" class="' + (lang === 'en' ? 'active' : '') + '"><span class="flag-icon flag-icon-us"></span> EN</a>'
            + '<a href="#language/id" class="' + (lang === 'id' ? 'active' : '') + '"><span class="flag-icon flag-icon-id"></span> ID</a></div>'
            + innerHtml
            + '</div></div></div>';
        const root = getRoot();
        root.classList.remove('hidden');
        root.innerHTML = html;
        afterRender();
    }

    // ---- Print layout (open new window) ----
    function renderPrint(innerHtml) {
        const w = window.open('', '_blank');
        if (!w) { H.flash('error', 'Popup blocked — allow popups to print the report.'); return; }
        w.document.open();
        w.document.write('<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' + H.e(layoutState.pageTitle) + '</title>'
            + '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">'
            + '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">'
            + '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">'
            + '<link rel="stylesheet" href="' + location.origin + location.pathname.replace(/index\.html$/, '') + 'assets/css/app.css">'
            + '<link rel="stylesheet" href="' + location.origin + location.pathname.replace(/index\.html$/, '') + 'assets/css/print.css"></head>'
            + '<body class="print-body">'
            + '<div class="print-toolbar no-print"><button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Cetak / Simpan PDF</button>'
            + '<a href="javascript:window.close()" class="btn btn-secondary"><i class="fas fa-times"></i> Tutup</a></div>'
            + '<div class="print-container">' + innerHtml + '</div>'
            + '</body></html>');
        w.document.close();
    }

    // ---- Post-render hooks (particles, pw toggle, alert auto-dismiss) ----
    function afterRender() {
        const c = document.getElementById('particles');
        if (c) {
            c.innerHTML = '';
            for (let i = 0; i < 18; i++) {
                const s = document.createElement('span');
                s.style.left = Math.random() * 100 + '%';
                s.style.width = s.style.height = (3 + Math.random() * 6) + 'px';
                s.style.animationDuration = (8 + Math.random() * 12) + 's';
                s.style.animationDelay = (-Math.random() * 15) + 's';
                s.style.opacity = (.3 + Math.random() * .5);
                c.appendChild(s);
            }
        }
        document.querySelectorAll('.pw-toggle').forEach(btn => {
            btn.addEventListener('click', function () {
                const inp = btn.parentElement.querySelector('input');
                if (!inp) return;
                if (inp.type === 'password') { inp.type = 'text'; btn.innerHTML = '<i class="fas fa-eye-slash"></i>'; }
                else { inp.type = 'password'; btn.innerHTML = '<i class="fas fa-eye"></i>'; }
            });
        });
        setTimeout(function () {
            document.querySelectorAll('.alert-dismissible').forEach(el => {
                el.style.transition = 'opacity .5s'; el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);
    }

    // Expose layout helpers to views
    V.layout = function (pageTitle, layout) { layoutState.pageTitle = pageTitle; layoutState.layout = layout || 'app'; };
    V.shell = renderShell;
    V.blank = renderBlank;
    V.print = renderPrint;
    V.error = function () {
        layoutState.pageTitle = Lang.t('page_not_found') || '404';
        renderShell('<div class="error-page"><div class="text-center"><h1 class="display-1 text-danger">404</h1><h3>' + H.e(Lang.t('page_not_found')) + '</h3><p>' + H.e(Lang.t('not_found_message')) + '</p><a href="#dashboard" class="btn btn-primary mt-3"><i class="fas fa-home"></i> ' + H.e(Lang.t('go_dashboard')) + '</a></div></div>');
    };

    V.doSearch = function (e) {
        e.preventDefault();
        const q = document.getElementById('global-search').value.trim();
        Router.navigate('search?q=' + encodeURIComponent(q));
        return false;
    };

    global.Views = V;
})(window);

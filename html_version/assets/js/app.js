/* ============================================================================
   AssetManager HTML Version — app.js (entry point)
   Memuat semua modul, mendaftarkan route, dan menangani navigasi hash.
   Library (jQuery/Bootstrap/AdminLTE/ApexCharts) sudah dimuat di index.html.
   ========================================================================== */
(function (global) {
    'use strict';

    function registerRoutes() {
        const P = Pages;
        // Auth
        Router.get('/login', P.loginForm);
        Router.get('/logout', P.logout);
        Router.get('/setup', P.setup);
        Router.get('/language/{lang}', P.language);
        Router.get('/dark-mode', P.darkMode);

        // Dashboard
        Router.get('/', P.dashboard);
        Router.get('/dashboard', P.dashboard);

        // Assets
        Router.get('/assets', P.assetIndex);
        Router.get('/assets/export', P.exportCsv);
        Router.get('/assets/import', P.importForm);
        Router.get('/assets/trash', P.trash);
        Router.get('/assets/create', P.assetForm);
        Router.get('/assets/{id}', P.assetShow);
        Router.get('/assets/{id}/edit', function (p) { P.assetForm(p.id); });
        Router.get('/assets/{id}/borrow', P.borrowForm);
        // Borrowings
        Router.get('/borrowings', P.borrowings);

        // Categories
        Router.get('/categories', P.categoryIndex);
        // Users
        Router.get('/users', P.userIndex);
        Router.get('/profile', P.profile);

        // Logs
        Router.get('/logs', P.logs);

        // Settings
        Router.get('/settings', P.settings);

        // Reports
        Router.get('/reports', P.reports);
        Router.get('/reports/print', P.reportPrint);

        // Patching
        Router.get('/patching', P.patching);
        Router.get('/patching/create', P.patchForm);
        Router.get('/patching/{id}', P.patchShow);
        Router.get('/patching/{id}/edit', function (p) { P.patchForm(p.id); });
        Router.get('/patching/{id}/computers', P.patchComputers);
        Router.get('/patching/checklist/{id}', P.patchChecklist);

        // Audit, Notifications, API Tokens
        Router.get('/audit', P.audit);
        Router.get('/notifications', P.notifications);
        Router.get('/api-tokens', P.apiTokens);
        Router.get('/api/assets', P.apiAssets);

        // Search
        Router.get('/search', P.search);

        // 404
        Router.notFound(function () { Views.error(); });
    }

    function init() {
        registerRoutes();
        window.addEventListener('hashchange', function () { Router.dispatch(); });
        // Initial dispatch
        Router.dispatch();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window);

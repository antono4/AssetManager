/* ============================================================================
   AssetManager HTML Version — Hash Router
   Mensimulasikan app/core/Router.php (regex-based, {param} placeholders).
   ========================================================================== */
(function (global) {
    'use strict';

    const routes = [];
    let notFoundHandler = null;

    function compile(pattern) {
        const keys = [];
        const regexStr = '^' + pattern.replace(/\{(\w+)\}/g, (_, k) => { keys.push(k); return '([^/?]+)'; }) + '$';
        return { regex: new RegExp(regexStr), keys: keys };
    }

    function add(method, pattern, handler) {
        routes.push({ method: method.toUpperCase(), compiled: compile(pattern), handler: handler });
    }

    const Router = {
        get(pattern, handler) { add('GET', pattern, handler); },
        post(pattern, handler) { add('POST', pattern, handler); },
        notFound(handler) { notFoundHandler = handler; },
        routes() { return routes; },

        // Parse current hash into {path, query}
        current() {
            let h = location.hash || '#/';
            h = h.replace(/^#/, '');
            const qIdx = h.indexOf('?');
            let path = h, query = {};
            if (qIdx >= 0) { path = h.slice(0, qIdx); const qs = h.slice(qIdx + 1); qs.split('&').forEach(p => { if (!p) return; const [k, v] = p.split('='); query[decodeURIComponent(k)] = decodeURIComponent((v || '').replace(/\+/g, ' ')); }); }
            // Normalize: ensure leading slash, strip trailing slash (except root)
            path = path.replace(/^\/+/, '/');
            if (path.charAt(0) !== '/') path = '/' + path;
            if (path.length > 1 && path.charAt(path.length - 1) === '/') path = path.slice(0, -1);
            return { path: path, query: query };
        },

        navigate(path) {
            if (path.charAt(0) !== '#' && path.charAt(0) !== '/') path = '/' + path;
            if (path.charAt(0) === '/') path = '#' + path;
            if (location.hash === path) { Router.dispatch(); } else { location.hash = path; }
        },

        dispatch() {
            const cur = Router.current();
            const method = (cur.query._method || 'GET').toUpperCase();
            // match
            for (const r of routes) {
                if (r.method !== method) continue;
                const m = r.compiled.regex.exec(cur.path);
                if (m) {
                    const params = {};
                    r.compiled.keys.forEach((k, i) => { params[k] = decodeURIComponent(m[i + 1]); });
                    try {
                        // If the route has no path params, pass query as the single argument
                        // (most list/filter views expect the query object). If it has params,
                        // pass (params, query) so detail views can use params.id.
                        if (r.compiled.keys.length === 0) r.handler(cur.query);
                        else r.handler(params, cur.query);
                    }
                    catch (err) {
                        console.error('Route handler error for', cur.path, err);
                        if (notFoundHandler) notFoundHandler(cur.query); else Views.error();
                    }
                    return;
                }
            }
            if (notFoundHandler) notFoundHandler(cur.query); else Views.error();
        },
    };

    global.Router = Router;
})(window);

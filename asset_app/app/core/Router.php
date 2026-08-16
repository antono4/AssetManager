<?php
// ============================================================================
//  ROUTER - routing sederhana berbasis path info
// ============================================================================

class Router
{
    private static array $routes = [];

    public static function add(string $method, string $pattern, callable $handler): void
    {
        self::$routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public static function get(string $pattern, callable $h): void { self::add('GET', $pattern, $h); }
    public static function post(string $pattern, callable $h): void { self::add('POST', $pattern, $h); }

    public static function dispatch(string $path, string $method): void
    {
        $method = strtoupper($method);

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $pattern = '#^' . preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $route['pattern']) . '$#';
            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                ($route['handler'])($params);
                return;
            }
        }
        http_response_code(404);
        View::render('error', ['pageTitle' => '404 - Tidak Ditemukan', 'code' => 404, 'message' => 'Halaman yang Anda cari tidak ditemukan.']);
    }
}

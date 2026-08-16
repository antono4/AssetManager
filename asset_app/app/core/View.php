<?php
// ============================================================================
//  VIEW - simple template engine dengan layout & escaping
// ============================================================================

class View
{
    public static function render(string $view, array $data = [], string $layout = 'app'): void
    {
        $data['currentUser'] = Auth::user();
        $data['flashes'] = Flash::all();
        $data['pageTitle'] = $data['pageTitle'] ?? ucfirst($view);

        // Layout bisa diberikan via key 'layout' di data (untuk login/setup)
        if (isset($data['layout']) && is_string($data['layout'])) {
            $layout = $data['layout'];
            unset($data['layout']);
        }

        // Pakai prefix underscore untuk nama view & layout agar tidak tertimpa
        // oleh key data bernama sama (mis. 'page' untuk pagination, 'layout').
        $_viewName  = $view;
        $_layoutName = $layout;
        extract($data);
        $contentFile = VIEW_PATH . '/pages/' . $_viewName . '.php';
        if (!file_exists($contentFile)) {
            throw new RuntimeException("View tidak ditemukan: $_viewName");
        }

        ob_start();
        include $contentFile;
        $content = ob_get_clean();

        $layoutFile = VIEW_PATH . '/layouts/' . $_layoutName . '.php';
        if (!file_exists($layoutFile)) {
            echo $content;
            return;
        }
        include $layoutFile;
    }

    public static function partial(string $name, array $data = []): void
    {
        extract($data);
        $file = VIEW_PATH . '/partials/' . $name . '.php';
        if (file_exists($file)) {
            include $file;
        }
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Helper functions global sekarang di app/core/helpers.php (loaded via config.php)
// Fungsi e(), url(), asset_url(), rp(), tgl(), dll sudah tersedia global.

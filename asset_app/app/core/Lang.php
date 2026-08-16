<?php
// ============================================================================
//  LANG - Internationalization (i18n) helper
//  Default: English. Mendukung 'en' & 'id'. Pilihan disimpan di session.
// ============================================================================

class Lang
{
    public const DEFAULT_LANG = 'en';
    public const SUPPORTED = ['en' => 'English', 'id' => 'Bahasa Indonesia'];

    private static ?array $messages = null;
    private static string $current = self::DEFAULT_LANG;

    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            Auth::startSession();
        }
        // Prioritas: session > cookie > default
        $lang = $_SESSION['lang'] ?? ($_COOKIE['lang'] ?? self::DEFAULT_LANG);
        if (!isset(self::SUPPORTED[$lang])) {
            $lang = self::DEFAULT_LANG;
        }
        self::$current = $lang;
        self::load();
    }

    private static function load(): void
    {
        $file = APP_PATH . '/lang/' . self::$current . '.php';
        if (file_exists($file)) {
            self::$messages = require $file;
        } else {
            self::$messages = require APP_PATH . '/lang/' . self::DEFAULT_LANG . '.php';
        }
    }

    public static function set(string $lang): void
    {
        if (!isset(self::SUPPORTED[$lang])) {
            $lang = self::DEFAULT_LANG;
        }
        self::$current = $lang;
        $_SESSION['lang'] = $lang;
        // Cookie 1 tahun agar persisten
        setcookie('lang', $lang, time() + 31536000, '/', '', false, true);
        self::load();
    }

    public static function current(): string
    {
        return self::$current;
    }

    public static function is(string $lang): bool
    {
        return self::$current === $lang;
    }

    // Terjemahkan key dengan placeholder :name
    public static function get(string $key, array $params = []): string
    {
        if (self::$messages === null) {
            self::init();
        }
        $text = self::$messages[$key] ?? $key;
        foreach ($params as $k => $v) {
            $text = str_replace(':' . $k, (string)$v, $text);
        }
        return $text;
    }

    public static function supported(): array
    {
        return self::SUPPORTED;
    }
}

// Helper global: terjemahkan
function t(string $key, array $params = []): string
{
    return Lang::get($key, $params);
}

// Alias singkat
function __(string $key, array $params = []): string
{
    return Lang::get($key, $params);
}

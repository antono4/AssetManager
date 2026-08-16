<?php
// ============================================================================
//  MODEL: Setting — konfigurasi aplikasi (key-value), mis. nama perusahaan
// ============================================================================

class Setting
{
    /** Cache in-memory agar tidak query berulang dalam satu request. */
    private static array $cache = [];

    /** Ambil satu nilai setting (string), atau default bila belum ada. */
    public static function get(string $key, string $default = ''): string
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }
        $row = Database::fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        $val = $row !== null ? (string)$row['setting_value'] : $default;
        self::$cache[$key] = $val;
        return $val;
    }

    /** Simpan / perbarui satu setting (upsert, driver-agnostic). */
    public static function set(string $key, string $value): void
    {
        $exists = Database::fetch("SELECT 1 FROM settings WHERE setting_key = ? LIMIT 1", [$key]);
        if ($exists) {
            Database::query(
                "UPDATE settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?",
                [$value, $key]
            );
        } else {
            Database::query(
                "INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)",
                [$key, $value]
            );
        }
        self::$cache[$key] = $value;
    }

    /** Ambil nama perusahaan (fallback ke APP_NAME bila belum di-set). */
    public static function companyName(): string
    {
        $name = trim(self::get('company_name', ''));
        return $name !== '' ? $name : APP_NAME;
    }

    /** Ambil alamat perusahaan. */
    public static function companyAddress(): string
    {
        return self::get('company_address', '');
    }

    /** Ambil telepon perusahaan. */
    public static function companyPhone(): string
    {
        return self::get('company_phone', '');
    }

    /** Ambil email perusahaan. */
    public static function companyEmail(): string
    {
        return self::get('company_email', '');
    }
}

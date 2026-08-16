<?php
// ============================================================================
//  FLASH - pesan sekali pakai antar request
// ============================================================================

class Flash
{
    public static function set(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            Auth::startSession();
        }
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function all(): array
    {
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }

    public static function has(): bool
    {
        return !empty($_SESSION['flash']);
    }
}

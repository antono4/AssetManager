<?php
// ============================================================================
//  AUTH - Session, login, logout, RBAC
// ============================================================================

class Auth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function attempt(string $username, string $password): bool
    {
        $user = Database::fetch("SELECT * FROM users WHERE username = ? AND is_active = 1", [$username]);
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        self::setUser($user);
        return true;
    }

    public static function setUser(array $user): void
    {
        $_SESSION['user'] = [
            'id'       => $user['id'],
            'name'     => $user['name'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'role'     => $user['role'],
        ];
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? null) === 'admin';
    }

    public static function role(): string
    {
        return self::user()['role'] ?? 'guest';
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Flash::set('error', 'Silakan login terlebih dahulu.');
            self::redirect(BASE_URL . '/login');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            Flash::set('error', 'Akses ditolak. Halaman khusus admin.');
            self::redirect(BASE_URL . '/dashboard');
        }
    }

    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}

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
        // Rate limiting: max 5 percobaan dalam 5 menit, lock 15 menit
        if (session_status() === PHP_SESSION_NONE) {
            self::startSession();
        }
        $key = 'rl_' . md5(strtolower($username));
        $now = time();
        $attempts = $_SESSION[$key] ?? [];
        // buang percobaan lebih dari 5 menit lalu
        $attempts = array_values(array_filter($attempts, fn($t) => $now - $t < 300));
        if (count($attempts) >= 5) {
            $_SESSION[$key] = $attempts;
            $_SESSION['rl_locked'] = $key;
            return false;
        }
        // cek lock 15 menit
        if (isset($_SESSION['rl_locked']) && $_SESSION['rl_locked'] === $key && isset($_SESSION['rl_lock_until']) && $now < $_SESSION['rl_lock_until']) {
            return false;
        }
        $user = Database::fetch("SELECT * FROM users WHERE username = ? AND is_active = 1", [$username]);
        if (!$user || !password_verify($password, $user['password'])) {
            $attempts[] = $now;
            $_SESSION[$key] = $attempts;
            if (count($attempts) >= 5) {
                $_SESSION['rl_lock_until'] = $now + 900; // lock 15 menit
            }
            return false;
        }
        // sukses: reset rate limit
        unset($_SESSION[$key], $_SESSION['rl_locked'], $_SESSION['rl_lock_until']);
        self::setUser($user);
        return true;
    }

    // Cek apakah login di-lock karena rate limiting
    public static function loginLocked(string $username): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            self::startSession();
        }
        $key = 'rl_' . md5(strtolower($username));
        if (isset($_SESSION['rl_locked']) && $_SESSION['rl_locked'] === $key && isset($_SESSION['rl_lock_until']) && time() < $_SESSION['rl_lock_until']) {
            return true;
        }
        // bersihkan lock yang sudah expired
        if (isset($_SESSION['rl_lock_until']) && time() >= $_SESSION['rl_lock_until']) {
            unset($_SESSION['rl_locked'], $_SESSION['rl_lock_until'], $_SESSION[$key]);
        }
        return false;
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
            Flash::set('error', t('login_required'));
            self::redirect(BASE_URL . '/login');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            Flash::set('error', t('access_denied'));
            self::redirect(BASE_URL . '/dashboard');
        }
    }

    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}

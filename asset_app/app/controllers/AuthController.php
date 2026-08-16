<?php
// ============================================================================
//  CONTROLLER: Auth (login, logout, setup)
// ============================================================================

class AuthController
{
    public function loginForm()
    {
        if (Auth::check()) {
            Auth::redirect(BASE_URL . '/dashboard');
        }
        View::render('login', ['pageTitle' => 'Login', 'layout' => 'blank']);
    }

    public function login()
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            Flash::set('error', 'Username dan password wajib diisi.');
            Auth::redirect(BASE_URL . '/login');
        }

        if (Auth::attempt($username, $password)) {
            Flash::set('success', 'Selamat datang kembali, ' . Auth::user()['name'] . '!');
            Auth::redirect(BASE_URL . '/dashboard');
        }
        Flash::set('error', 'Username atau password salah.');
        Auth::redirect(BASE_URL . '/login');
    }

    public function logout()
    {
        Auth::logout();
        Auth::redirect(BASE_URL . '/login');
    }

    // Route /setup - perbarui password default ke hash bcrypt yang valid
    public function setup()
    {
        $db = Database::conn();

        // Pastikan skema ada
        Database::ensureSchema();

        $admin = User::findByUsername('admin');
        $staff = User::findByUsername('staff');

        $updated = [];
        if ($admin) {
            $hash = password_hash(DEFAULT_ADMIN_PASS, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
            $db->prepare("UPDATE users SET password=?, is_active=1 WHERE username=?")->execute([$hash, 'admin']);
            $updated[] = 'admin → admin123';
        }
        if ($staff) {
            $hash = password_hash(DEFAULT_STAFF_PASS, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
            $db->prepare("UPDATE users SET password=?, is_active=1 WHERE username=?")->execute([$hash, 'staff']);
            $updated[] = 'staff → staff123';
        }

        View::render('setup', [
            'pageTitle' => 'Setup',
            'updated'   => $updated,
            'layout'    => 'blank',
        ]);
    }
}

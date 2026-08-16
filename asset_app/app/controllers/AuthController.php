<?php
// ============================================================================
//  CONTROLLER: Auth (login, logout, setup)
// ============================================================================

class AuthController
{
    public function loginForm()
    {
        if (Auth::check()) {
            Auth::redirect(url('/dashboard'));
        }
        View::render('login', ['pageTitle' => t('login'), 'layout' => 'blank']);
    }

    public function login()
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            Flash::set('error', t('username_password_required'));
            Auth::redirect(url('/login'));
        }

        if (Auth::loginLocked($username)) {
            Flash::set('error', t('login_locked'));
            Auth::redirect(url('/login'));
        }

        if (Auth::attempt($username, $password)) {
            Flash::set('success', t('login_success', ['name' => Auth::user()['name']]));
            Auth::redirect(url('/dashboard'));
        }
        Flash::set('error', t('login_failed'));
        Auth::redirect(url('/login'));
    }

    public function logout()
    {
        Auth::logout();
        Auth::redirect(url('/login'));
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

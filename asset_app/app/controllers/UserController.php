<?php
// ============================================================================
//  CONTROLLER: User (CRUD - admin only) & Profil
// ============================================================================

class UserController
{
    public function index()
    {
        Auth::requireAdmin();
        $users = User::all();
        View::render('users/index', [
            'pageTitle' => 'Manajemen User',
            'users'     => $users,
        ]);
    }

    public function store()
    {
        Auth::requireAdmin();
        $d = $_POST;
        if (empty($d['name']) || empty($d['username']) || empty($d['password'])) {
            Flash::set('error', 'Nama, username, dan password wajib diisi.');
            Auth::redirect(BASE_URL . '/users');
        }
        try {
            User::create($d);
            Flash::set('success', 'User berhasil ditambahkan.');
        } catch (PDOException $e) {
            Flash::set('error', 'Gagal: username sudah dipakai.');
        }
        Auth::redirect(BASE_URL . '/users');
    }

    public function update(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $d = $_POST;
        if (empty($d['name']) || empty($d['username'])) {
            Flash::set('error', 'Nama dan username wajib diisi.');
            Auth::redirect(BASE_URL . '/users');
        }
        $changePw = !empty($d['password']);
        try {
            User::update($id, $d, $changePw);
            Flash::set('success', 'User berhasil diperbarui.');
        } catch (PDOException $e) {
            Flash::set('error', 'Gagal: username sudah dipakai.');
        }
        Auth::redirect(BASE_URL . '/users');
    }

    public function delete(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $ok = User::delete($id);
        if ($ok) {
            Flash::set('success', 'User berhasil dihapus.');
        } else {
            Flash::set('error', 'User tidak bisa dihapus (akun sendiri).');
        }
        Auth::redirect(BASE_URL . '/users');
    }

    public function profile()
    {
        Auth::requireLogin();
        $user = User::find(Auth::id());
        View::render('users/profile', [
            'pageTitle' => 'Profil Saya',
            'user'      => $user,
        ]);
    }

    public function updateProfile()
    {
        Auth::requireLogin();
        $d = $_POST;
        if (empty($d['name'])) {
            Flash::set('error', 'Nama wajib diisi.');
            Auth::redirect(BASE_URL . '/profile');
        }
        $id = Auth::id();
        $currentUser = User::find($id);
        $d['username'] = $currentUser['username'];
        $d['role'] = $currentUser['role'];
        $d['is_active'] = $currentUser['is_active'];
        $changePw = !empty($d['password']);
        try {
            User::update($id, $d, $changePw);
            // refresh session
            $_SESSION['user']['name'] = $d['name'];
            $_SESSION['user']['email'] = $d['email'];
            Flash::set('success', 'Profil berhasil diperbarui.');
        } catch (PDOException $e) {
            Flash::set('error', 'Gagal memperbarui profil.');
        }
        Auth::redirect(BASE_URL . '/profile');
    }
}

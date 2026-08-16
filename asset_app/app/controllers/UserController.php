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
            'pageTitle' => t('user_list'),
            'users'     => $users,
        ]);
    }

    public function store()
    {
        Auth::requireAdmin();
        $d = $_POST;
        if (empty($d['name']) || empty($d['username']) || empty($d['password'])) {
            Flash::set('error', t('user_name_username_required'));
            Auth::redirect(BASE_URL . '/users');
        }
        try {
            User::create($d);
            Flash::set('success', t('user_added'));
        } catch (PDOException $e) {
            Flash::set('error', t('user_username_exists'));
        }
        Auth::redirect(BASE_URL . '/users');
    }

    public function update(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $d = $_POST;
        if (empty($d['name']) || empty($d['username'])) {
            Flash::set('error', t('user_name_username_required'));
            Auth::redirect(BASE_URL . '/users');
        }
        $changePw = !empty($d['password']);
        try {
            User::update($id, $d, $changePw);
            Flash::set('success', t('user_updated'));
        } catch (PDOException $e) {
            Flash::set('error', t('user_username_exists'));
        }
        Auth::redirect(BASE_URL . '/users');
    }

    public function delete(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $ok = User::delete($id);
        if ($ok) {
            Flash::set('success', t('user_deleted'));
        } else {
            Flash::set('error', t('user_not_deletable'));
        }
        Auth::redirect(BASE_URL . '/users');
    }

    public function profile()
    {
        Auth::requireLogin();
        $user = User::find(Auth::id());
        View::render('users/profile', [
            'pageTitle' => t('my_profile'),
            'user'      => $user,
        ]);
    }

    public function updateProfile()
    {
        Auth::requireLogin();
        $d = $_POST;
        if (empty($d['name'])) {
            Flash::set('error', t('user_name_username_required'));
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
            $_SESSION['user']['name'] = $d['name'];
            $_SESSION['user']['email'] = $d['email'];
            Flash::set('success', t('profile_updated'));
        } catch (PDOException $e) {
            Flash::set('error', t('profile_update_failed'));
        }
        Auth::redirect(BASE_URL . '/profile');
    }
}

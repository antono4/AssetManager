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
            Auth::redirect(url('/users'));
        }
        try {
            User::create($d);
            Flash::set('success', t('user_added'));
        } catch (PDOException $e) {
            Flash::set('error', t('user_username_exists'));
        }
        Auth::redirect(url('/users'));
    }

    public function update(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $d = $_POST;
        if (empty($d['name']) || empty($d['username'])) {
            Flash::set('error', t('user_name_username_required'));
            Auth::redirect(url('/users'));
        }
        $changePw = !empty($d['password']);
        try {
            User::update($id, $d, $changePw);
            Flash::set('success', t('user_updated'));
        } catch (PDOException $e) {
            Flash::set('error', t('user_username_exists'));
        }
        Auth::redirect(url('/users'));
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
        Auth::redirect(url('/users'));
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
            Auth::redirect(url('/profile'));
        }
        $id = Auth::id();
        $currentUser = User::find($id);
        $d['username'] = $currentUser['username'];
        $d['role'] = $currentUser['role'];
        $d['is_active'] = $currentUser['is_active'];
        $changePw = !empty($d['password']);
        try {
            User::update($id, $d, $changePw);
            $refreshed = User::find($id);
            $_SESSION['user']['name'] = $refreshed['name'];
            $_SESSION['user']['email'] = $refreshed['email'];
            $_SESSION['user']['photo'] = $refreshed['photo'];
            Flash::set('success', t('profile_updated'));
        } catch (PDOException $e) {
            Flash::set('error', t('profile_update_failed'));
        }
        Auth::redirect(url('/profile'));
    }

    // Admin: hapus foto user
    public function removePhoto(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        User::removePhoto($id);
        Flash::set('success', t('photo_removed'));
        Auth::redirect(url('/users'));
    }

    // User sendiri: hapus foto profil
    public function removeProfilePhoto()
    {
        Auth::requireLogin();
        $id = Auth::id();
        User::removePhoto($id);
        $_SESSION['user']['photo'] = null;
        Flash::set('success', t('photo_removed'));
        Auth::redirect(url('/profile'));
    }
}

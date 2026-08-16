<?php
// ============================================================================
//  CONTROLLER: Category (CRUD - admin only)
// ============================================================================

class CategoryController
{
    public function index()
    {
        Auth::requireAdmin();
        $categories = Category::all();
        View::render('categories/index', [
            'pageTitle'  => t('category_list'),
            'categories' => $categories,
        ]);
    }

    public function store()
    {
        Auth::requireAdmin();
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($name === '') {
            Flash::set('error', t('category_name_required'));
            Auth::redirect(url('/categories'));
        }
        try {
            Category::create($name, $description);
            Flash::set('success', t('category_added'));
        } catch (PDOException $e) {
            Flash::set('error', t('category_name_exists'));
        }
        Auth::redirect(url('/categories'));
    }

    public function update(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($name === '') {
            Flash::set('error', t('category_name_required'));
            Auth::redirect(url('/categories'));
        }
        try {
            Category::update($id, $name, $description);
            Flash::set('success', t('category_updated'));
        } catch (PDOException $e) {
            Flash::set('error', t('category_name_exists'));
        }
        Auth::redirect(url('/categories'));
    }

    public function delete(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $ok = Category::delete($id);
        if ($ok) {
            Flash::set('success', t('category_deleted'));
        } else {
            Flash::set('error', t('category_not_deletable'));
        }
        Auth::redirect(url('/categories'));
    }
}

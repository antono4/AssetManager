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
            'pageTitle'  => 'Kategori Aset',
            'categories' => $categories,
        ]);
    }

    public function store()
    {
        Auth::requireAdmin();
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Nama kategori wajib diisi.');
            Auth::redirect(BASE_URL . '/categories');
        }
        try {
            Category::create($name, $description);
            Flash::set('success', 'Kategori berhasil ditambahkan.');
        } catch (PDOException $e) {
            Flash::set('error', 'Gagal: nama kategori sudah ada.');
        }
        Auth::redirect(BASE_URL . '/categories');
    }

    public function update(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Nama kategori wajib diisi.');
            Auth::redirect(BASE_URL . '/categories');
        }
        try {
            Category::update($id, $name, $description);
            Flash::set('success', 'Kategori berhasil diperbarui.');
        } catch (PDOException $e) {
            Flash::set('error', 'Gagal: nama kategori sudah dipakai.');
        }
        Auth::redirect(BASE_URL . '/categories');
    }

    public function delete(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $ok = Category::delete($id);
        if ($ok) {
            Flash::set('success', 'Kategori berhasil dihapus.');
        } else {
            Flash::set('error', 'Kategori tidak bisa dihapus karena masih dipakai aset.');
        }
        Auth::redirect(BASE_URL . '/categories');
    }
}

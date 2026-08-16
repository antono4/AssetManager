<?php
// ============================================================================
//  CONTROLLER: Asset (CRUD)
// ============================================================================

class AssetController
{
    public function index()
    {
        Auth::requireLogin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $category = trim($_GET['category'] ?? '');

        $total = Asset::count($search, $status, $category);
        $assets = Asset::all($search, $status, $category, $perPage, ($page - 1) * $perPage);
        $categories = Category::options();
        $totalPages = max(1, (int)ceil($total / $perPage));

        View::render('assets/index', [
            'pageTitle'  => 'Daftar Aset',
            'assets'     => $assets,
            'categories' => $categories,
            'search'     => $search,
            'status'     => $status,
            'category'   => $category,
            'page'       => $page,
            'totalPages' => $totalPages,
            'total'      => $total,
        ]);
    }

    public function show(array $p)
    {
        Auth::requireLogin();
        $id = (int)$p['id'];
        $asset = Asset::find($id);
        if (!$asset) {
            Flash::set('error', 'Aset tidak ditemukan.');
            Auth::redirect(BASE_URL . '/assets');
        }
        $logs = AssetLog::all(50, 0, $id);
        View::render('assets/show', [
            'pageTitle' => 'Detail Aset: ' . $asset['asset_code'],
            'asset'     => $asset,
            'logs'      => $logs,
        ]);
    }

    public function create()
    {
        Auth::requireAdmin();
        $categories = Category::options();
        View::render('assets/form', [
            'pageTitle'  => 'Tambah Aset',
            'categories' => $categories,
            'action'     => 'create',
            'asset'      => null,
        ]);
    }

    public function store()
    {
        Auth::requireAdmin();
        $d = $_POST;
        if (empty($d['name']) || empty($d['category_id'])) {
            Flash::set('error', 'Nama dan kategori wajib diisi.');
            Auth::redirect(BASE_URL . '/assets/create');
        }
        Asset::create($d);
        Flash::set('success', 'Aset berhasil ditambahkan.');
        Auth::redirect(BASE_URL . '/assets');
    }

    public function edit(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $asset = Asset::find($id);
        if (!$asset) {
            Flash::set('error', 'Aset tidak ditemukan.');
            Auth::redirect(BASE_URL . '/assets');
        }
        $categories = Category::options();
        View::render('assets/form', [
            'pageTitle'  => 'Edit Aset: ' . $asset['asset_code'],
            'categories' => $categories,
            'action'     => 'edit',
            'asset'      => $asset,
        ]);
    }

    public function update(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $d = $_POST;
        if (empty($d['name']) || empty($d['category_id'])) {
            Flash::set('error', 'Nama dan kategori wajib diisi.');
            Auth::redirect(BASE_URL . '/assets/' . $id . '/edit');
        }
        Asset::update($id, $d);
        Flash::set('success', 'Aset berhasil diperbarui.');
        Auth::redirect(BASE_URL . '/assets/' . $id);
    }

    public function delete(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        Asset::delete($id);
        Flash::set('success', 'Aset berhasil dihapus.');
        Auth::redirect(BASE_URL . '/assets');
    }

    // Ubah status cepat (dipinjam/kembali/rusak)
    public function status(array $p)
    {
        Auth::requireLogin();
        $id = (int)$p['id'];
        $status = $_POST['status'] ?? '';
        $note = trim($_POST['note'] ?? '');
        if (!in_array($status, ['tersedia', 'dipinjam', 'rusak'], true)) {
            Flash::set('error', 'Status tidak valid.');
            Auth::redirect(BASE_URL . '/assets/' . $id);
        }
        Asset::setStatus($id, $status, $note);
        Flash::set('success', 'Status aset berhasil diubah.');
        Auth::redirect(BASE_URL . '/assets/' . $id);
    }
}

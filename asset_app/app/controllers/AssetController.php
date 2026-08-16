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
            'pageTitle'  => t('asset_list'),
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
            Flash::set('error', t('asset_not_found'));
            Auth::redirect(BASE_URL . '/assets');
        }
        $logs = AssetLog::all(50, 0, $id);
        View::render('assets/show', [
            'pageTitle' => t('asset_detail') . ': ' . $asset['asset_code'],
            'asset'     => $asset,
            'logs'      => $logs,
        ]);
    }

    public function create()
    {
        Auth::requireAdmin();
        $categories = Category::options();
        View::render('assets/form', [
            'pageTitle'  => t('add_asset'),
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
            Flash::set('error', t('name_category_required'));
            Auth::redirect(BASE_URL . '/assets/create');
        }
        Asset::create($d);
        Flash::set('success', t('asset_added'));
        Auth::redirect(BASE_URL . '/assets');
    }

    public function edit(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $asset = Asset::find($id);
        if (!$asset) {
            Flash::set('error', t('asset_not_found'));
            Auth::redirect(BASE_URL . '/assets');
        }
        $categories = Category::options();
        View::render('assets/form', [
            'pageTitle'  => t('edit_asset') . ': ' . $asset['asset_code'],
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
            Flash::set('error', t('name_category_required'));
            Auth::redirect(BASE_URL . '/assets/' . $id . '/edit');
        }
        Asset::update($id, $d);
        Flash::set('success', t('asset_updated'));
        Auth::redirect(BASE_URL . '/assets/' . $id);
    }

    public function delete(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        Asset::delete($id);
        Flash::set('success', t('asset_deleted'));
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
            Flash::set('error', t('status_invalid'));
            Auth::redirect(BASE_URL . '/assets/' . $id);
        }
        Asset::setStatus($id, $status, $note);
        Flash::set('success', t('status_changed'));
        Auth::redirect(BASE_URL . '/assets/' . $id);
    }

    // Hapus foto aset
    public function removePhoto(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        Asset::removePhoto($id);
        Flash::set('success', t('photo_removed'));
        Auth::redirect(BASE_URL . '/assets/' . $id . '/edit');
    }
}

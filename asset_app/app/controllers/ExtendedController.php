<?php
// ============================================================================
//  CONTROLLER: Extended — fitur tambahan (export, search, trash, audit, dll)
// ============================================================================

class ExtendedController
{
    // === Export CSV ===
    public function exportCsv()
    {
        Auth::requireLogin();
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $category = trim($_GET['category'] ?? '');
        $csv = Asset::exportCsv($search, $status, $category);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="assets_export_' . date('Ymd_His') . '.csv"');
        echo $csv;
        exit;
    }

    // === Download CSV template untuk import ===
    public function csvTemplate()
    {
        Auth::requireAdmin();
        $csv = "name,category,brand_spec,location,status,purchase_date,price\n";
        $csv .= "PC Desktop Test,Komputer,Dell OptiPlex i7,Ruang Server,tersedia,2024-01-15,10000000\n";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="asset_import_template.csv"');
        echo $csv;
        exit;
    }

    // === Import CSV ===
    public function importForm()
    {
        Auth::requireAdmin();
        View::render('assets/import', ['pageTitle' => t('import_assets')]);
    }

    public function import()
    {
        Auth::requireAdmin();
        if (empty($_FILES['csv_file']['name']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Flash::set('error', t('import_failed', ['error' => 'No file uploaded']));
            Auth::redirect(url('/assets/import'));
        }
        $content = file_get_contents($_FILES['csv_file']['tmp_name']);
        $count = Asset::importCsv($content);
        Flash::set('success', t('import_success', ['count' => $count]));
        Auth::redirect(url('/assets'));
    }

    // === Trash (soft delete) ===
    public function trash()
    {
        Auth::requireAdmin();
        $assets = Asset::trashed();
        View::render('assets/trash', ['pageTitle' => t('trash'), 'assets' => $assets]);
    }

    public function restore(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        Asset::restore($id);
        Flash::set('success', t('restored'));
        Auth::redirect(url('/assets/trash'));
    }

    public function forceDelete(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        Asset::forceDelete($id);
        Flash::set('success', t('asset_deleted'));
        Auth::redirect(url('/assets/trash'));
    }

    // === Global Search ===
    public function search()
    {
        Auth::requireLogin();
        $q = trim($_GET['q'] ?? '');
        $results = $q !== '' ? Asset::globalSearch($q) : [];
        View::render('search', [
            'pageTitle' => t('search_results'),
            'q' => $q,
            'results' => $results,
        ]);
    }

    // === Audit Trail ===
    public function audit()
    {
        Auth::requireAdmin();
        $module = trim($_GET['module'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 30;
        $total = AuditTrail::count($module);
        $logs = AuditTrail::all($perPage, ($page - 1) * $perPage, $module);
        $totalPages = max(1, (int)ceil($total / $perPage));
        View::render('audit/index', [
            'pageTitle' => t('audit_trail'),
            'logs' => $logs,
            'modules' => AuditTrail::modules(),
            'module' => $module,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    // === API Token ===
    public function apiTokens()
    {
        Auth::requireAdmin();
        $tokens = ApiToken::all();
        View::render('api/tokens', ['pageTitle' => t('api_token'), 'tokens' => $tokens]);
    }

    public function generateToken()
    {
        Auth::requireAdmin();
        $name = trim($_POST['name'] ?? '');
        $token = ApiToken::create(Auth::id(), $name);
        Flash::set('success', t('token_generated') . ': <code>' . e($token) . '</code>');
        Auth::redirect(url('/api-tokens'));
    }

    public function deleteToken(array $p)
    {
        Auth::requireAdmin();
        ApiToken::delete((int)$p['id']);
        Flash::set('success', t('token_deleted'));
        Auth::redirect(url('/api-tokens'));
    }

    // === API endpoint (JSON) ===
    public function apiAssets()
    {
        $token = $_SERVER['HTTP_X_API_TOKEN'] ?? ($_GET['token'] ?? '');
        $user = ApiToken::validate($token);
        if (!$user) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid or missing API token']);
            return;
        }
        $assets = Asset::all('', '', '', 0, 0);
        header('Content-Type: application/json');
        echo json_encode(['data' => $assets, 'count' => count($assets)]);
    }

    // === Dark Mode toggle ===
    public function darkMode()
    {
        if (session_status() === PHP_SESSION_NONE) {
            Auth::startSession();
        }
        $current = $_COOKIE['dark_mode'] ?? '0';
        $new = $current === '1' ? '0' : '1';
        setcookie('dark_mode', $new, time() + 31536000, '/', '', false, true);
        $_SESSION['dark_mode'] = $new;
        $back = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/dashboard';
        Auth::redirect($back);
    }

    // === Notifications ===
    public function notifications()
    {
        Auth::requireLogin();
        $notifs = Notification::forUser(Auth::id(), 20);
        View::render('notifications/index', [
            'pageTitle' => t('notifications'),
            'notifs' => $notifs,
        ]);
    }

    public function markNotifRead(array $p)
    {
        Auth::requireLogin();
        Notification::markRead((int)$p['id']);
        Auth::redirect(url('/notifications'));
    }

    public function markAllNotifRead()
    {
        Auth::requireLogin();
        Notification::markAllRead(Auth::id());
        Flash::set('success', 'All notifications marked as read.');
        Auth::redirect(url('/notifications'));
    }

    // === Borrowing ===
    public function borrowings()
    {
        Auth::requireLogin();
        $borrowings = Borrowing::all();
        View::render('borrowings/index', [
            'pageTitle' => t('borrowing'),
            'borrowings' => $borrowings,
        ]);
    }

    public function borrowForm(array $p)
    {
        Auth::requireLogin();
        $id = (int)$p['id'];
        $asset = Asset::find($id);
        if (!$asset) {
            Flash::set('error', t('asset_not_found'));
            Auth::redirect(url('/assets'));
        }
        View::render('borrowings/form', [
            'pageTitle' => t('borrow') . ': ' . $asset['asset_code'],
            'asset' => $asset,
        ]);
    }

    public function borrow(array $p)
    {
        Auth::requireLogin();
        $id = (int)$p['id'];
        $asset = Asset::find($id);
        if (!$asset) {
            Flash::set('error', t('asset_not_found'));
            Auth::redirect(url('/assets'));
        }
        Borrowing::create($id, $_POST);
        Flash::set('success', t('asset_borrowed'));
        Auth::redirect(url('/assets/' . $id));
    }

    public function returnAsset(array $p)
    {
        Auth::requireLogin();
        $bid = (int)$p['id'];
        Borrowing::returnAsset($bid);
        Flash::set('success', t('asset_returned'));
        Auth::redirect(url('/borrowings'));
    }

    // === Activity by user ===
    public function activityByUser(array $p)
    {
        Auth::requireLogin();
        $id = (int)$p['id'];
        $user = User::find($id);
        if (!$user) {
            Flash::set('error', 'User not found.');
            Auth::redirect(url('/users'));
        }
        $activities = AuditTrail::byUser($id, 30);
        View::render('users/activity', [
            'pageTitle' => t('activity_by_user') . ': ' . $user['name'],
            'user' => $user,
            'activities' => $activities,
        ]);
    }
}

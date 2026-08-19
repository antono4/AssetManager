<?php
// ============================================================================
//  CONTROLLER: Patch (Jadwal & Checklist Patching kuartalan)
// ============================================================================

class PatchController
{
    // Daftar jadwal patching
    public function index()
    {
        Auth::requireLogin();
        $schedules = PatchSchedule::all();
        View::render('patch/index', [
            'pageTitle' => t('patch_schedule'),
            'schedules' => $schedules,
        ]);
    }

    // Form buat jadwal baru
    public function create()
    {
        Auth::requireAdmin();
        $cur = PatchSchedule::currentQuarter();
        View::render('patch/form', [
            'pageTitle'   => t('create_schedule'),
            'action'      => 'create',
            'schedule'    => null,
            'current'     => $cur,
        ]);
    }

    // Simpan jadwal baru
    public function store()
    {
        Auth::requireAdmin();
        $d = $_POST;
        $d['year'] = PatchSchedule::yearFromPeriod($d['period'] ?? '');
        if (empty($d['name']) || empty($d['quarter']) || $d['year'] < 1900) {
            Flash::set('error', t('name_category_required'));
            Auth::redirect(url('/patching/create'));
        }
        // Auto-fill tanggal bila kosong
        if (empty($d['start_date']) || empty($d['due_date'])) {
            $dates = PatchSchedule::quarterDates((int)$d['quarter'], (int)$d['year']);
            $d['start_date'] = $d['start_date'] ?: $dates['start'];
            $d['due_date'] = $d['due_date'] ?: $dates['end'];
        }
        $id = PatchSchedule::create($d);
        Flash::set('success', t('schedule_added'));
        Auth::redirect(url('/patching/' . $id));
    }

    // Detail jadwal + daftar checklist aset
    public function show(array $p)
    {
        Auth::requireLogin();
        $id = (int)$p['id'];
        $schedule = PatchSchedule::findWithStats($id);
        if (!$schedule) {
            Flash::set('error', t('schedule_not_found'));
            Auth::redirect(url('/patching'));
        }

        // Paginasi daftar checklist (mencegah render ribuan baris sekaligus)
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $totalChecklists = PatchChecklist::countForSchedule($id);
        $totalPages = max(1, (int)ceil($totalChecklists / $perPage));
        $checklists = PatchChecklist::forSchedulePaged($id, $perPage, ($page - 1) * $perPage);

        // Pre-load progress item batch (hindari N+1 query)
        $clIds = array_column($checklists, 'id');
        $progress = PatchChecklist::progressBatch($clIds);

        // Aset IT yang belum punya checklist (untuk tombol generate).
        // availableItAssets() memakai subquery berdasarkan schedule id.
        $availableAssets = $this->availableItAssets($id);

        View::render('patch/show', [
            'pageTitle'       => t('patch_schedule') . ': ' . $schedule['name'],
            'schedule'        => $schedule,
            'checklists'      => $checklists,
            'progress'        => $progress,
            'availableAssets' => $availableAssets,
            'page'            => $page,
            'perPage'         => $perPage,
            'totalChecklists' => $totalChecklists,
            'totalPages'      => $totalPages,
        ]);
    }

    // Form edit jadwal
    public function edit(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $schedule = PatchSchedule::find($id);
        if (!$schedule) {
            Flash::set('error', t('schedule_not_found'));
            Auth::redirect(url('/patching'));
        }
        View::render('patch/form', [
            'pageTitle'   => t('edit_schedule'),
            'action'      => 'edit',
            'schedule'    => $schedule,
            'current'     => PatchSchedule::currentQuarter(),
        ]);
    }

    public function update(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $d = $_POST;
        $d['year'] = PatchSchedule::yearFromPeriod($d['period'] ?? '');
        if (empty($d['name']) || empty($d['quarter']) || $d['year'] < 1900) {
            Flash::set('error', t('name_category_required'));
            Auth::redirect(url('/patching/' . $id . '/edit'));
        }
        if (empty($d['start_date']) || empty($d['due_date'])) {
            $dates = PatchSchedule::quarterDates((int)$d['quarter'], (int)$d['year']);
            $d['start_date'] = $d['start_date'] ?: $dates['start'];
            $d['due_date'] = $d['due_date'] ?: $dates['end'];
        }
        PatchSchedule::update($id, $d);
        Flash::set('success', t('schedule_updated'));
        Auth::redirect(url('/patching/' . $id));
    }

    public function delete(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        PatchSchedule::delete($id);
        Flash::set('success', t('schedule_deleted'));
        Auth::redirect(url('/patching'));
    }

    // Generate checklist untuk aset terpilih
    public function generate(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $assetIds = $_POST['asset_ids'] ?? [];
        if (empty($assetIds)) {
            Flash::set('error', t('select_one_asset'));
            Auth::redirect(url('/patching/' . $id));
        }
        $count = PatchChecklist::generateForSchedule($id, $assetIds);
        Flash::set('success', t('checklists_generated', ['count' => $count]));
        Auth::redirect(url('/patching/' . $id));
    }

    // Generate semua aset IT sekaligus
    public function generateAll(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $assets = $this->availableItAssets($id);
        $ids = array_column($assets, 'id');
        if (empty($ids)) {
            Flash::set('warning', t('no_new_it_assets'));
            Auth::redirect(url('/patching/' . $id));
        }
        $count = PatchChecklist::generateForSchedule($id, $ids);
        Flash::set('success', t('it_checklists_generated', ['count' => $count]));
        Auth::redirect(url('/patching/' . $id));
    }

    // Detail checklist satu aset (centang item)
    public function checklist(array $p)
    {
        Auth::requireLogin();
        $cid = (int)$p['id'];
        $checklist = PatchChecklist::find($cid);
        if (!$checklist) {
            Flash::set('error', t('schedule_not_found'));
            Auth::redirect(url('/patching'));
        }
        $items = PatchChecklist::items($cid);
        $schedule = PatchSchedule::find($checklist['schedule_id']);

        $total = count($items);
        $done = count(array_filter($items, fn($i) => (int)$i['is_checked'] === 1));
        $progress = $total > 0 ? round(($done / $total) * 100) : 0;

        View::render('patch/checklist', [
            'pageTitle' => t('checklist') . ': ' . $checklist['asset_code'],
            'checklist' => $checklist,
            'items'     => $items,
            'schedule'  => $schedule,
            'total'     => $total,
            'done'      => $done,
            'progress'  => $progress,
        ]);
    }

    // Toggle centang item (AJAX-friendly, tapi juga POST biasa)
    public function toggle(array $p)
    {
        Auth::requireLogin();
        $cid = (int)$p['id'];
        $itemId = (int)$_POST['item_id'];
        $checked = ($_POST['checked'] ?? '') === '1';
        $patchCode = trim($_POST['patch_code'] ?? '');
        PatchChecklist::toggleItem($cid, $itemId, $checked, $patchCode);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            $cl = PatchChecklist::find($cid);
            $items = PatchChecklist::items($cid);
            $done = count(array_filter($items, fn($i) => (int)$i['is_checked'] === 1));
            echo json_encode([
                'ok' => true,
                'done' => $done,
                'total' => count($items),
                'status' => $cl ? $cl['status'] : null,
            ]);
            return;
        }
        Flash::set('success', t('item_updated'));
        Auth::redirect(url('/patching/checklist/' . $cid));
    }

    // Simpan kode patching untuk satu item (AJAX)
    public function saveCode(array $p)
    {
        Auth::requireLogin();
        $cid = (int)$p['id'];
        $itemId = (int)($_POST['item_id'] ?? 0);
        $patchCode = trim($_POST['patch_code'] ?? '');
        if ($itemId <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid item']);
            return;
        }
        PatchChecklist::savePatchCode($cid, $itemId, $patchCode);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'patch_code' => $patchCode]);
            return;
        }
        Flash::set('success', t('patch_code_saved'));
        Auth::redirect(url('/patching/checklist/' . $cid));
    }

    // Daftar komputer beserta kode patching untuk sebuah jadwal
    public function computers(array $p)
    {
        Auth::requireLogin();
        $id = (int)$p['id'];
        $schedule = PatchSchedule::findWithStats($id);
        if (!$schedule) {
            Flash::set('error', t('schedule_not_found'));
            Auth::redirect(url('/patching'));
        }
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $total = PatchChecklist::countForSchedule($id);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $computers = PatchChecklist::computersWithPatchCodesPaged($id, $perPage, ($page - 1) * $perPage);
        $items = PatchChecklist::activeItems();

        View::render('patch/computers', [
            'pageTitle'  => t('computer_patch_list') . ' — ' . $schedule['name'],
            'schedule'   => $schedule,
            'computers'  => $computers,
            'items'      => $items,
            'page'       => $page,
            'perPage'    => $perPage,
            'total'      => $total,
            'totalPages' => $totalPages,
        ]);
    }

    // Skip / reset checklist
    public function setChecklistStatus(array $p)
    {
        Auth::requireLogin();
        $cid = (int)$p['id'];
        $status = $_POST['status'] ?? '';
        $note = trim($_POST['note'] ?? '');
        if (!in_array($status, ['skipped', 'pending'], true)) {
            Flash::set('error', t('status_invalid'));
            Auth::redirect(url('/patching/checklist/' . $cid));
        }
        PatchChecklist::setStatus($cid, $status, $note);
        Flash::set('success', t('checklist_status_updated'));
        Auth::redirect(url('/patching/checklist/' . $cid));
    }

    // Hapus checklist satu aset dari jadwal
    public function deleteChecklist(array $p)
    {
        Auth::requireAdmin();
        $cid = (int)$p['id'];
        $cl = PatchChecklist::find($cid);
        $schedId = $cl['schedule_id'] ?? 0;
        PatchChecklist::delete($cid);
        Flash::set('success', t('checklist_deleted'));
        Auth::redirect(url('/patching/' . $schedId));
    }

    // Aset IT yang tersedia untuk generate (kategori non-"Umum") untuk schedule tertentu.
    // Pakai NOT IN (subquery) agar tidak mengikat ribuan parameter placeholder
    // (bisa bermasalah pada beberapa konfigurasi PDO SQLite/MySQL dengan EMULATE_PREPARES off).
    private function availableItAssets(int $scheduleId): array
    {
        $sql = "SELECT a.id, a.asset_code, a.name, a.location, c.name AS category_name
                FROM assets a LEFT JOIN categories c ON c.id = a.category_id
                WHERE (c.name <> 'Umum' OR c.name IS NULL)
                  AND a.id NOT IN (
                      SELECT asset_id FROM patch_checklists WHERE schedule_id = ?
                  )
                ORDER BY a.asset_code";
        return Database::fetchAll($sql, [$scheduleId]);
    }
}

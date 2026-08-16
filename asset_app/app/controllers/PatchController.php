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
        $quarterOpts = PatchSchedule::quarterOptions();
        $cur = PatchSchedule::currentQuarter();
        View::render('patch/form', [
            'pageTitle'   => t('create_schedule'),
            'action'      => 'create',
            'schedule'    => null,
            'quarterOpts' => $quarterOpts,
            'current'     => $cur,
        ]);
    }

    // Simpan jadwal baru
    public function store()
    {
        Auth::requireAdmin();
        $d = $_POST;
        if (empty($d['name']) || empty($d['quarter']) || empty($d['year'])) {
            Flash::set('error', t('name_category_required'));
            Auth::redirect(BASE_URL . '/patching/create');
        }
        // Auto-fill tanggal bila kosong
        if (empty($d['start_date']) || empty($d['due_date'])) {
            $dates = PatchSchedule::quarterDates((int)$d['quarter'], (int)$d['year']);
            $d['start_date'] = $d['start_date'] ?: $dates['start'];
            $d['due_date'] = $d['due_date'] ?: $dates['end'];
        }
        $id = PatchSchedule::create($d);
        Flash::set('success', t('schedule_added'));
        Auth::redirect(BASE_URL . '/patching/' . $id);
    }

    // Detail jadwal + daftar checklist aset
    public function show(array $p)
    {
        Auth::requireLogin();
        $id = (int)$p['id'];
        $schedule = PatchSchedule::findWithStats($id);
        if (!$schedule) {
            Flash::set('error', t('schedule_not_found'));
            Auth::redirect(BASE_URL . '/patching');
        }
        $checklists = PatchChecklist::forSchedule($id);
        $existingIds = array_column($checklists, 'asset_id');
        $availableAssets = $this->availableItAssets($existingIds);

        View::render('patch/show', [
            'pageTitle'       => t('patch_schedule') . ': ' . $schedule['name'],
            'schedule'        => $schedule,
            'checklists'      => $checklists,
            'availableAssets' => $availableAssets,
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
            Auth::redirect(BASE_URL . '/patching');
        }
        View::render('patch/form', [
            'pageTitle'   => t('edit_schedule'),
            'action'      => 'edit',
            'schedule'    => $schedule,
            'quarterOpts' => PatchSchedule::quarterOptions(),
            'current'     => PatchSchedule::currentQuarter(),
        ]);
    }

    public function update(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $d = $_POST;
        if (empty($d['name']) || empty($d['quarter']) || empty($d['year'])) {
            Flash::set('error', t('name_category_required'));
            Auth::redirect(BASE_URL . '/patching/' . $id . '/edit');
        }
        if (empty($d['start_date']) || empty($d['due_date'])) {
            $dates = PatchSchedule::quarterDates((int)$d['quarter'], (int)$d['year']);
            $d['start_date'] = $d['start_date'] ?: $dates['start'];
            $d['due_date'] = $d['due_date'] ?: $dates['end'];
        }
        PatchSchedule::update($id, $d);
        Flash::set('success', t('schedule_updated'));
        Auth::redirect(BASE_URL . '/patching/' . $id);
    }

    public function delete(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        PatchSchedule::delete($id);
        Flash::set('success', t('schedule_deleted'));
        Auth::redirect(BASE_URL . '/patching');
    }

    // Generate checklist untuk aset terpilih
    public function generate(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $assetIds = $_POST['asset_ids'] ?? [];
        if (empty($assetIds)) {
            Flash::set('error', t('select_one_asset'));
            Auth::redirect(BASE_URL . '/patching/' . $id);
        }
        $count = PatchChecklist::generateForSchedule($id, $assetIds);
        Flash::set('success', t('checklists_generated', ['count' => $count]));
        Auth::redirect(BASE_URL . '/patching/' . $id);
    }

    // Generate semua aset IT sekaligus
    public function generateAll(array $p)
    {
        Auth::requireAdmin();
        $id = (int)$p['id'];
        $existing = array_column(PatchChecklist::forSchedule($id), 'asset_id');
        $assets = $this->availableItAssets($existing);
        $ids = array_column($assets, 'id');
        if (empty($ids)) {
            Flash::set('warning', t('no_new_it_assets'));
            Auth::redirect(BASE_URL . '/patching/' . $id);
        }
        $count = PatchChecklist::generateForSchedule($id, $ids);
        Flash::set('success', t('it_checklists_generated', ['count' => $count]));
        Auth::redirect(BASE_URL . '/patching/' . $id);
    }

    // Detail checklist satu aset (centang item)
    public function checklist(array $p)
    {
        Auth::requireLogin();
        $cid = (int)$p['id'];
        $checklist = PatchChecklist::find($cid);
        if (!$checklist) {
            Flash::set('error', t('schedule_not_found'));
            Auth::redirect(BASE_URL . '/patching');
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
        Auth::redirect(BASE_URL . '/patching/checklist/' . $cid);
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
        Auth::redirect(BASE_URL . '/patching/checklist/' . $cid);
    }

    // Daftar komputer beserta kode patching untuk sebuah jadwal
    public function computers(array $p)
    {
        Auth::requireLogin();
        $id = (int)$p['id'];
        $schedule = PatchSchedule::findWithStats($id);
        if (!$schedule) {
            Flash::set('error', t('schedule_not_found'));
            Auth::redirect(BASE_URL . '/patching');
        }
        $computers = PatchChecklist::computersWithPatchCodes($id);
        $items = PatchChecklist::activeItems();

        View::render('patch/computers', [
            'pageTitle' => t('computer_patch_list') . ' — ' . $schedule['name'],
            'schedule'  => $schedule,
            'computers' => $computers,
            'items'     => $items,
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
            Auth::redirect(BASE_URL . '/patching/checklist/' . $cid);
        }
        PatchChecklist::setStatus($cid, $status, $note);
        Flash::set('success', t('checklist_status_updated'));
        Auth::redirect(BASE_URL . '/patching/checklist/' . $cid);
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
        Auth::redirect(BASE_URL . '/patching/' . $schedId);
    }

    // Aset IT yang tersedia untuk generate (kategori non-"Umum")
    private function availableItAssets(array $excludeIds = []): array
    {
        $sql = "SELECT a.id, a.asset_code, a.name, a.location, c.name AS category_name
                FROM assets a LEFT JOIN categories c ON c.id = a.category_id
                WHERE c.name <> 'Umum' OR c.name IS NULL";
        $params = [];
        if (!empty($excludeIds)) {
            $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
            $sql .= " AND a.id NOT IN ($placeholders)";
            $params = $excludeIds;
        }
        $sql .= " ORDER BY a.asset_code";
        return Database::fetchAll($sql, $params);
    }
}

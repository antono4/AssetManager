<?php
// ============================================================================
//  CONTROLLER: Log (riwayat aset)
// ============================================================================

class LogController
{
    public function index()
    {
        Auth::requireLogin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $total = AssetLog::count();
        $logs = AssetLog::all($perPage, ($page - 1) * $perPage);
        $totalPages = max(1, (int)ceil($total / $perPage));

        View::render('logs/index', [
            'pageTitle'  => 'Riwayat Aktivitas',
            'logs'       => $logs,
            'page'       => $page,
            'totalPages' => $totalPages,
            'total'      => $total,
        ]);
    }
}

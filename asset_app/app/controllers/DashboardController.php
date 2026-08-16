<?php
// ============================================================================
//  CONTROLLER: Dashboard
// ============================================================================

class DashboardController
{
    public function index()
    {
        Auth::requireLogin();
        $stats = Asset::stats();
        $byCategory = Asset::countByCategory();
        $byStatus = Asset::countByStatus();
        $recentAssets = Asset::recent(5);
        $recentLogs = AssetLog::recent(8);

        // Statistik patching untuk widget dashboard
        $patching = [
            'total'    => (int)Database::scalar("SELECT COUNT(*) FROM patch_schedules"),
            'ongoing'  => (int)Database::scalar("SELECT COUNT(*) FROM patch_schedules WHERE status='ongoing'"),
            'draft'    => (int)Database::scalar("SELECT COUNT(*) FROM patch_schedules WHERE status='draft'"),
            'checklists' => (int)Database::scalar("SELECT COUNT(*) FROM patch_checklists"),
            'done'     => (int)Database::scalar("SELECT COUNT(*) FROM patch_checklists WHERE status='completed'"),
        ];

        // Persiapkan data grafik
        $statusChart = ['tersedia' => 0, 'dipinjam' => 0, 'rusak' => 0];
        foreach ($byStatus as $row) {
            $statusChart[$row['status']] = (int)$row['total'];
        }

        View::render('dashboard', [
            'pageTitle'    => 'Dashboard',
            'stats'        => $stats,
            'byCategory'   => $byCategory,
            'statusChart'  => $statusChart,
            'recentAssets' => $recentAssets,
            'recentLogs'   => $recentLogs,
            'patching'     => $patching,
        ]);
    }
}

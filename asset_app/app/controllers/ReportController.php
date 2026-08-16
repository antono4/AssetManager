<?php
// ============================================================================
//  CONTROLLER: Report (Laporan Aset)
// ============================================================================

class ReportController
{
    // Tampilkan halaman laporan dengan filter
    public function index()
    {
        Auth::requireLogin();

        $filters = $this->collectFilters();
        $tab = $_GET['tab'] ?? 'summary';

        $data = [
            'pageTitle'    => 'Laporan Aset',
            'filters'      => $filters,
            'tab'          => $tab,
            'categories'   => Category::options(),
            'locations'    => Asset::distinctLocations(),
            'summary'      => Asset::summaryForReport($filters),
            'byCategory'   => Asset::recapByCategory($filters),
            'byLocation'   => Asset::recapByLocation($filters),
            'assets'       => Asset::forReport($filters),
        ];

        // Tambah grafik data untuk tab ringkasan
        $data['chartCategory'] = $this->chartCategoryData($data['byCategory']);
        $data['chartStatus']   = $this->chartStatusData($data['summary']);

        View::render('reports/index', $data);
    }

    // Versi cetak (print-friendly)
    public function print()
    {
        Auth::requireLogin();

        $filters = $this->collectFilters();
        $tab = $_GET['tab'] ?? 'summary';

        View::render('reports/print', [
            'pageTitle'    => 'Laporan Aset',
            'filters'      => $filters,
            'tab'          => $tab,
            'summary'      => Asset::summaryForReport($filters),
            'byCategory'   => Asset::recapByCategory($filters),
            'byLocation'   => Asset::recapByLocation($filters),
            'assets'       => Asset::forReport($filters),
            'layout'       => 'print',
        ]);
    }

    // Kumpulkan filter dari GET
    private function collectFilters(): array
    {
        return [
            'category_id' => trim($_GET['category_id'] ?? ''),
            'status'      => trim($_GET['status'] ?? ''),
            'location'    => trim($_GET['location'] ?? ''),
            'date_from'   => trim($_GET['date_from'] ?? ''),
            'date_to'     => trim($_GET['date_to'] ?? ''),
        ];
    }

    // Deskripsi filter untuk cetak
    public static function describeFilters(array $f): string
    {
        $parts = [];
        if (!empty($f['category_id'])) {
            $cat = Category::find((int)$f['category_id']);
            $parts[] = 'Kategori: ' . ($cat['name'] ?? '?');
        }
        if (!empty($f['status'])) {
            $parts[] = 'Status: ' . ucfirst($f['status']);
        }
        if (!empty($f['location'])) {
            $parts[] = 'Lokasi: "' . $f['location'] . '"';
        }
        if (!empty($f['date_from'])) {
            $parts[] = 'Dari: ' . tgl($f['date_from']);
        }
        if (!empty($f['date_to'])) {
            $parts[] = 'Sampai: ' . tgl($f['date_to']);
        }
        return $parts ? implode(' • ', $parts) : 'Semua aset (tanpa filter)';
    }

    private function chartCategoryData(array $byCategory): array
    {
        return [
            'labels' => array_map(fn($c) => $c['category_name'], $byCategory),
            'totals' => array_map(fn($c) => (int)$c['total'], $byCategory),
            'nilai'  => array_map(fn($c) => (float)$c['nilai'], $byCategory),
        ];
    }

    private function chartStatusData(array $summary): array
    {
        return [
            (int)$summary['tersedia'],
            (int)$summary['dipinjam'],
            (int)$summary['rusak'],
        ];
    }
}

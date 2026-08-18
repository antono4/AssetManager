<?php
// ============================================================================
//  HELPER FUNCTIONS - global helpers (loaded via config.php)
//  Dipisah dari View.php agar tersedia di controller sebelum view render.
// ============================================================================

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function asset_url(string $path = ''): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    // Gunakan index.php?r= format (compat tanpa mod_rewrite, XAMPP, Apache, Nginx)
    if ($path === '') {
        return BASE_URL . '/index.php';
    }
    // Pisahkan query string (setelah '?') bila ada, agar tidak terbentuk dua '?'
    // (mis. url('assets?page=5') -> index.php?r=assets&page=5, bukan ?r=assets?page=5).
    $query = '';
    if (($qpos = strpos($path, '?')) !== false) {
        $query = '&' . substr($path, $qpos + 1);
        $path = substr($path, 0, $qpos);
    }
    return BASE_URL . '/index.php?r=' . $path . $query;
}

function rp($value): string
{
    if ($value === null || $value === '') {
        return '-';
    }
    return 'Rp ' . number_format((float)$value, 0, ',', '.');
}

function tgl(?string $date): string
{
    if (!$date) {
        return '-';
    }
    $ts = strtotime($date);
    if (!$ts) {
        return $date;
    }
    $bln = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return date('j', $ts) . ' ' . ($bln[(int)date('n', $ts)] ?? '') . ' ' . date('Y', $ts);
}

function tglwaktu(?string $datetime): string
{
    if (!$datetime) {
        return '-';
    }
    $ts = strtotime($datetime);
    if (!$ts) {
        return $datetime;
    }
    return tgl($datetime) . ' ' . date('H:i', $ts);
}

function status_badge(string $status): string
{
    $map = [
        'tersedia' => 'success',
        'dipinjam' => 'warning',
        'rusak'    => 'danger',
    ];
    $cls = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . ucfirst($status) . '</span>';
}

function role_badge(string $role): string
{
    $cls = $role === 'admin' ? 'danger' : 'info';
    return '<span class="badge bg-' . $cls . '">' . ucfirst($role) . '</span>';
}

// --- Foto user (profil) ---
// Path foto disimpan relatif (mis. 'uploads/users/user_xxx.jpg').

function user_photo_url(?string $photo): ?string
{
    if (!$photo) {
        return null;
    }
    return BASE_URL . '/' . $photo;
}

// Avatar user: bila ada foto tampilkan <img>, bila tidak lingkaran inisial.
function user_photo_img(?string $photo, ?string $name = '', int $size = 40, string $cls = ''): string
{
    $url = user_photo_url($photo);
    $s = $size;
    if ($url) {
        return '<img src="' . e($url) . '" alt="avatar" class="' . e($cls) . '" style="width:' . $s . 'px;height:' . $s . 'px;object-fit:cover;border-radius:50%">';
    }
    $initial = strtoupper(mb_substr($name ?: '?', 0, 1));
    return '<div class="' . e($cls) . '" style="width:' . $s . 'px;height:' . $s . 'px;border-radius:50%;background:#3a8;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:' . round($s * 0.45) . 'px">' . e($initial) . '</div>';
}

function patch_status_badge(string $status): string
{
    $map = [
        'draft'      => 'secondary',
        'ongoing'    => 'warning',
        'completed'  => 'success',
        'pending'    => 'secondary',
        'in_progress'=> 'primary',
        'skipped'    => 'dark',
    ];
    $cls = $map[$status] ?? 'secondary';
    $labels = [
        'draft' => 'Draft', 'ongoing' => 'Berjalan', 'completed' => 'Selesai',
        'pending' => 'Menunggu', 'in_progress' => 'Proses', 'skipped' => 'Skip',
    ];
    return '<span class="badge badge-' . $cls . '">' . ($labels[$status] ?? ucfirst($status)) . '</span>';
}

function price_visible(): bool
{
    return Auth::isAdmin();
}

function price_hidden($value): string
{
    return Auth::isAdmin() ? rp($value) : '-';
}

function asset_photo_url(?string $photo): ?string
{
    if (!$photo) {
        return null;
    }
    return BASE_URL . '/' . $photo;
}

function asset_photo_img(?string $photo, int $size = 80, string $cls = ''): string
{
    $url = asset_photo_url($photo);
    if ($url) {
        return '<img src="' . e($url) . '" alt="photo" class="asset-photo ' . e($cls) . '" style="width:' . $size . 'px;height:' . $size . 'px;object-fit:cover;border-radius:8px">';
    }
    return '<div class="asset-photo-placeholder d-inline-flex align-items-center justify-content-center bg-secondary ' . e($cls) . '" style="width:' . $size . 'px;height:' . $size . 'px;border-radius:8px">'
         . '<i class="fas fa-image text-white" style="font-size:' . round($size * 0.4) . 'px"></i>'
         . '</div>';
}

function old(string $key, $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function str_getcsv_all(string $csv): array
{
    $rows = [];
    $lines = preg_split('/\r\n|\r|\n/', trim($csv));
    foreach ($lines as $line) {
        if ($line === '') continue;
        $rows[] = str_getcsv($line);
    }
    return $rows;
}

function qr_code_url(string $text, int $size = 200): string
{
    return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($text);
}

function rp_currency($value, string $currency = 'IDR'): string
{
    if ($value === null || $value === '') return '-';
    $currency = strtoupper($currency);
    if ($currency === 'IDR') {
        return 'Rp ' . number_format((float)$value, 0, ',', '.');
    } elseif ($currency === 'USD') {
        return '$ ' . number_format((float)$value, 2, '.', ',');
    } elseif ($currency === 'EUR') {
        return '€ ' . number_format((float)$value, 2, ',', '.');
    }
    return $currency . ' ' . number_format((float)$value, 2, '.', ',');
}

function flash_messages(): string
{
    $html = '';
    foreach (Flash::all() as $f) {
        $icon = $f['type'] === 'success' ? 'check-circle'
              : ($f['type'] === 'error' ? 'exclamation-triangle'
              : ($f['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle'));
        $html .= '<div class="alert alert-' . e($f['type']) . ' alert-dismissible fade show" role="alert">'
               . '<i class="icon fas fa-' . $icon . '"></i> ' . e($f['message'])
               . '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>'
               . '</div>';
    }
    return $html;
}

/**
 * Render pagination windowed (reusable) — hanya tampilkan window halaman di
 * sekitar halaman aktif + ellipsis, agar tetap ringan untuk ribuan halaman.
 *
 * @param int    $page       Halaman aktif (1-based)
 * @param int    $totalPages Total halaman
 * @param string $baseUrl    URL dasar TANPA page param (sudah termasuk ? atau &)
 *                           Contoh: url('assets') . '?status=dipinjam&'
 * @param int    $total      Total record (untuk label "Showing x–y of z")
 * @param int    $perPage    Record per halaman (untuk label)
 */
function pagination(int $page, int $totalPages, string $baseUrl, int $total = 0, int $perPage = 0): string
{
    if ($totalPages <= 1) {
        return '';
    }
    $window = 5;
    $start = max(1, $page - $window);
    $end = min($totalPages, $page + $window);

    $html = '<div class="card-footer"><nav><ul class="pagination pagination-sm justify-content-center mb-0">';
    // Prev
    $html .= '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">'
           . '<a class="page-link" href="' . $baseUrl . 'page=' . ($page - 1) . '">&laquo;</a></li>';
    // First + ellipsis
    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=1">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
        }
    }
    // Window
    for ($i = $start; $i <= $end; $i++) {
        $html .= '<li class="page-item ' . ($i === $page ? 'active' : '') . '">'
               . '<a class="page-link" href="' . $baseUrl . 'page=' . $i . '">' . $i . '</a></li>';
    }
    // Last + ellipsis
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }
    // Next
    $html .= '<li class="page-item ' . ($page >= $totalPages ? 'disabled' : '') . '">'
           . '<a class="page-link" href="' . $baseUrl . 'page=' . ($page + 1) . '">&raquo;</a></li>';
    $html .= '</ul></nav>';
    // Label "Showing x–y of z"
    if ($total > 0 && $perPage > 0) {
        $from = ($page - 1) * $perPage + 1;
        $to = min($page * $perPage, $total);
        $html .= '<div class="text-center text-muted small mt-1">' . t('showing') . ' '
               . number_format($from) . '–' . number_format($to) . ' '
               . t('of') . ' ' . number_format($total) . '</div>';
    }
    $html .= '</div>';
    return $html;
}

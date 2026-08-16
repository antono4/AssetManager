<?php
// ============================================================================
//  VIEW - simple template engine dengan layout & escaping
// ============================================================================

class View
{
    public static function render(string $view, array $data = [], string $layout = 'app'): void
    {
        $data['currentUser'] = Auth::user();
        $data['flashes'] = Flash::all();
        $data['pageTitle'] = $data['pageTitle'] ?? ucfirst($view);

        // Layout bisa diberikan via key 'layout' di data (untuk login/setup)
        if (isset($data['layout']) && is_string($data['layout'])) {
            $layout = $data['layout'];
            unset($data['layout']);
        }

        // Pakai prefix underscore untuk nama view & layout agar tidak tertimpa
        // oleh key data bernama sama (mis. 'page' untuk pagination, 'layout').
        $_viewName  = $view;
        $_layoutName = $layout;
        extract($data);
        $contentFile = VIEW_PATH . '/pages/' . $_viewName . '.php';
        if (!file_exists($contentFile)) {
            throw new RuntimeException("View tidak ditemukan: $_viewName");
        }

        ob_start();
        include $contentFile;
        $content = ob_get_clean();

        $layoutFile = VIEW_PATH . '/layouts/' . $_layoutName . '.php';
        if (!file_exists($layoutFile)) {
            echo $content;
            return;
        }
        include $layoutFile;
    }

    public static function partial(string $name, array $data = []): void
    {
        extract($data);
        $file = VIEW_PATH . '/partials/' . $name . '.php';
        if (file_exists($file)) {
            include $file;
        }
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// --- Helper functions global ---

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
    return BASE_URL . '/' . ltrim($path, '/');
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

// Hanya admin yang boleh melihat harga/nilai aset. Staff melihat '-'.
function price_visible(): bool
{
    return Auth::isAdmin();
}

// Tampilkan harga (Rp) bila admin, '-' bila staff
function price_hidden($value): string
{
    return Auth::isAdmin() ? rp($value) : '-';
}

// URL foto aset (absolute path dari BASE_URL). Bila tidak ada foto, return null.
function asset_photo_url(?string $photo): ?string
{
    if (!$photo) {
        return null;
    }
    return BASE_URL . '/' . $photo;
}

// Tag <img> foto aset dengan fallback icon bila tidak ada foto.
// $size = px (default 80). $cls = class tambahan.
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

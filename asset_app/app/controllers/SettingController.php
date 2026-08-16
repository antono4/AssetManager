<?php
// ============================================================================
//  CONTROLLER: Setting — konfigurasi aplikasi (nama & alamat perusahaan)
//  Admin only. Menyimpan ke tabel settings (key-value).
// ============================================================================

class SettingController
{
    // Daftar field yang diperbolehkan disimpan (key setting => nama field POST)
    private const FIELDS = [
        'company_name'    => 'company_name',
        'company_address'  => 'company_address',
        'company_phone'    => 'company_phone',
        'company_email'    => 'company_email',
    ];

    // Tampilkan form pengaturan perusahaan
    public function index()
    {
        Auth::requireAdmin();
        View::render('settings/index', [
            'pageTitle' => t('company_settings'),
            'company'   => [
                'name'    => Setting::companyName(),
                'address' => Setting::companyAddress(),
                'phone'   => Setting::companyPhone(),
                'email'   => Setting::companyEmail(),
            ],
        ]);
    }

    // Simpan perubahan pengaturan
    public function update()
    {
        Auth::requireAdmin();
        foreach (self::FIELDS as $key => $field) {
            $value = trim($_POST[$field] ?? '');
            Setting::set($key, $value);
        }
        AuditTrail::log('settings', 'update', null, t('company_settings_updated'));
        Flash::set('success', t('company_settings_saved'));
        Auth::redirect(url('/settings'));
    }
}

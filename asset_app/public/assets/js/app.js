/* ============================================================================
   Custom JS - AssetManager
   ========================================================================== */
$(function () {
    // Auto-dismiss alert setelah 5 detik
    setTimeout(function () {
        $('.alert-dismissible').fadeOut('slow', function () { $(this).remove(); });
    }, 5000);

    // Konfirmasi hapus
    $(document).on('click', '.btn-delete', function (e) {
        var msg = $(this).data('confirm') || 'Yakin ingin menghapus data ini?';
        if (!confirm(msg)) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });

    // Baris tabel aset yang bisa diklik (data-href)
    $(document).on('click', 'tr[data-href]', function () {
        window.location.href = $(this).data('href');
    });

    // Toggle sidebar di layar kecil saat klik nav-item
    $('.nav-sidebar .nav-link').on('click', function () {
        if ($(window).width() < 992) {
            $('body').removeClass('sidebar-open').addClass('sidebar-collapse');
        }
    });
});

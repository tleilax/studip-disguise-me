jQuery(function ($) {
    $('body').addClass('disguised');

    $('a[href$="logout.php"]').click(function () {
        location.href = '<?= $link ?>';
        return false;
    });
});

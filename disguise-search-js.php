jQuery(function ($) {
    if ($('#admin-user-index form table.default').length > 0) {
        $('#admin-user-index form table.default tbody tr').each(function () {
            if ($(this).html().toLowerCase().indexOf('gesperrt') !== -1) {
                return;
            }

            var username = $.trim($('td:eq(1) a', this).text()),
                link = '<?= $link ?>'.replace('REPLACE-WITH-USER', username);
            $('<a title="<?= _('Als dieser Nutzer einloggen') ?>"/>').attr('href', link)
                .html('<?= Icon::create('door-enter', 'attention') ?>')
                .prependTo( $('td.actions', this) );
        });
    }
});

jQuery(function ($) {
    if ($('#layout_content form table').length > 1) {
        $('#layout_content form:last tr:not(:first):not(:last)').each(function () {
            if ($(this).html().toLowerCase().indexOf('gesperrt') !== -1) {
                return;
            }

            var username = $.trim($('td:eq(1) a', this).text()),
                link = '<?= $link ?>'.replace('REPLACE-WITH-USER', username);
            $('<a title="<?= _('Als dieser Nutzer einloggen') ?>"/>').attr('href', link)
                .html('<?= Assets::img('icons/16/red/door-enter.png') ?>')
                .prependTo( $('td:last', this) );
        });
    }
});

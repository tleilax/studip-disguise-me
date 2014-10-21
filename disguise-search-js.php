jQuery(function ($) {
    if ($('#layout_content form table').length > 1) {
        $('#layout_content form:last tr:not(:first):not(:last)').each(function () {
            var username = $.trim($('td:eq(1) a', this).text()),
                link = '<?= $link ?>'.replace('REPLACE-WITH-USER', username);
            $('<a title="Als dieser Nutzer einloggen"/>').attr('href', link)
                .html('<?= Assets::img('icons/16/red/door-enter.png') ?>')
                .prependTo( $('td:last', this) );
        });
    }
});

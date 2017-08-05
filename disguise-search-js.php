jQuery(function ($) {
    if ($('#layout_content form table').length > 0) {
        $('#layout_content form:last tr:not(:first):not(:last)').each(function () {
            var username = $.trim($('td:eq(1) a', this).text()),
                link = '<?= $link ?>'.replace('REPLACE-WITH-USER', username);
            $('<a>').text('<?= _('Als dieser Nutzer einloggen') ?>').attr('href', link)
                .prepend('<?= Icon::create('door-enter', 'status-red')->asImg() ?>')
                .wrap('<li class="action-menu-item">')
                .parent()
                .appendTo( $('.action-menu-list', this) );
        });
    }
});

jQuery(function ($) {
    $('<a href="<?= $link ?>">')
        .text(' <?= _('Als dieser Nutzer einloggen') ?>')
        .wrap('<li style="<?= Icon::create('door-enter', 'status-red')->asCSS() ?>">').parent()
        .appendTo('#layout-sidebar .widget-links');
});

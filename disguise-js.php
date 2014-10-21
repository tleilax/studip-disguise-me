jQuery(function ($) {
    $('<a href="<?= $link ?>">')
        .text(' <?= _('Als dieser Nutzer einloggen') ?>')
        .prepend('<?= Assets::img('icons/16/red/door-enter.png', array('class' => 'middle')) ?>')
        .before('<br>')
        .appendTo('#layout_content td:first');
});

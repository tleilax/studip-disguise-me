jQuery(function ($) {
    $('<a href="<?= $link ?>">')
        .text(' <?= _('Als dieser Nutzer einloggen') ?>')
        .prepend('<?= Icon::create('door-enter', 'attention', ['class' => 'middle']) ?>')
        .appendTo('#layout_content td:first')
        .before('<br>');
});

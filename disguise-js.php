jQuery(function ($) {
    $('<a/>').attr('href', '<?= $link ?>')
        .text(" Als dieser Nutzer einloggen".toLocaleString())
        .prepend('<?= Assets::img('icons/16/red/door-enter.png') ?>')
        .before('<br/>')
        .insertAfter('#layout_container > table:first td:first > :not(br):last');
});

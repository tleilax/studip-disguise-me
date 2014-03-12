jQuery(function ($) {
    $('<a>').attr('href', '<?= $link ?>')
        .text(" Als dieser Nutzer einloggen".toLocaleString())
        .prepend('<?= Assets::img('icons/16/red/door-enter.png', array('class' => 'middle')) ?>')
        .before('<br/>')
        .insertAfter('#user_profile td:first > :not(br):last');
});

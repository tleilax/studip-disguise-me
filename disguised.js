jQuery(function ($) {
	$('#navigation_menue').css('background-color', '#f88');
	$('<div>Eingeloggt als <?= htmlReady($username) ?></div>')
	   .css({
	       background: 'rgba(192, 192, 192, 0.6)',
	       border: '1px solid #f00',
	       borderTop: 0,
	       borderBottomLeftRadius: '10px',
           borderBottomRightRadius: '10px',
           boxShadow: '1px 1px 1px #000',
           color: '#fff',
	       left: '25%',
	       padding: '1em 0',
	       position: 'fixed',
	       right: '25%',
	       textAlign: 'center',
	       textShadow: '1px 1px 1px #000',
	       zIndex: 111
	   }).hover(
	       function () { $(this).css('background', 'rgba(192,192,192,0.9)')},
           function () { $(this).css('background', 'rgba(192,192,192,0.6)')}
	   )
	   .prependTo('body');
	$('a[href$="logout.php"]').attr('href', '<?= $link ?>');
});

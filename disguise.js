//<![CDATA[
jQuery(function ($) {	
	$('<a />').attr('href', '<?= $link ?>')
		.text("Als dieser Nutzer einloggen".toLocaleString())
		.before('<br />')
		.insertAfter('#layout_container > table:first td:first > :not(br):last');
});
//]]>
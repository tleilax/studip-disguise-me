//<![CDATA[
jQuery(function ($) {
	$('#navigation_menue').css('background-color', '#f88');

	$('a[href$="logout.php"]').attr('href', '<?= $link ?>');
});
//]]>

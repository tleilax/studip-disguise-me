document.observe('dom:loaded', function () {
<?php if ($normal_login and preg_match('/about\.php$/', $_SERVER['PHP_SELF']) and isset($_REQUEST['username'])): ?>
	var link = new Element('a', {href: '<?=PluginEngine::getURL($this, array('disguise_as' => $_REQUEST['username']))?>'}).update('<?=_('Als dieser Nutzer einloggen')?>');
	$$('#layout_container > table td').first().insert(link);
<?php elseif (!$normal_login): ?>
	$('navigation_menue').setStyle({backgroundColor: '#f88'});
	$$('a[href$="logout.php"]').first().setAttribute('href', '<?=PluginEngine::getURL($this, array('logout' => 1))?>');
<?php endif; ?>
});

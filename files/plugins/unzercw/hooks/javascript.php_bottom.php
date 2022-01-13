<?php 


if (isset($page) && ($page->page_name == 'checkout' || $page->page_name == 'unzercw' || $page == 'unzercw' || $page == 'checkout')) {
	
	require _SRV_WEBROOT . 'plugins/unzercw/init.php';
	require_once 'Customweb/Util/JavaScript.php';
	
	$scriptsToLoad = array(
		_SYSTEM_BASE_URL._SRV_WEB._SRV_WEB_PLUGINS.'unzercw/templates/js/checkout.js',
	);
	if (file_exists(_SRV_WEBROOT . 'templates/' . _STORE_TEMPLATE . '/plugins/unzercw/js/checkout.js')) {
		$scriptsToLoad[] = _SRV_WEB_TEMPLATES . _STORE_TEMPLATE . '/plugins/unzercw/js/checkout.js';
	}
	else {
		$scriptsToLoad[] = _SYSTEM_BASE_URL._SRV_WEB._SRV_WEB_PLUGINS . 'unzercw/templates/js/default.js';
	}
	echo '<script type="text/javascript"> function unzercw_load_after_jquery() { ';
		foreach ($scriptsToLoad as $script) {
			echo "var script = jquery_unzercw('<script/\>').attr('src', '" . $script . "').appendTo('head');";
		}
	echo ' }';
	echo Customweb_Util_JavaScript::getLoadJQueryCode('1.10.2', 'jquery_unzercw', 'unzercw_load_after_jquery');
	echo '</script>';
}

?>

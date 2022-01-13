<?php

if (isset($page) && ($page->page_name == 'checkout' || $page->page_name == 'unzercw' || $page == 'checkout' || $page == 'unzercw')) {
	echo '<link rel="stylesheet" type="text/css" href="'._SYSTEM_BASE_URL._SRV_WEB._SRV_WEB_PLUGINS.'unzercw/templates/css/frontend.css" />';
	
	if (file_exists(_SRV_WEBROOT . 'templates/' . _STORE_TEMPLATE . '/plugins/unzercw/css/frontend.css')) {
		echo '<link rel="stylesheet" type="text/css" href="' . _SRV_WEB_TEMPLATES . _STORE_TEMPLATE . '/plugins/unzercw/css/frontend.css" />';
	}
}


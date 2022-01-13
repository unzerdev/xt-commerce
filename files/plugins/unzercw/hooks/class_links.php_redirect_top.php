<?php 

// When there is a language redirection going on and the target URL the endpoint page we want to prevent this redirect.

if (isset($GLOBALS['unzercw_language_redirect_triggered']) 
		&& $GLOBALS['unzercw_language_redirect_triggered'] === true 
		&& stristr($url, 'endpoint') !== false 
		&& stristr($url, 'unzercw') !== false) {
			$plugin_return_value = "no redirect";
		}
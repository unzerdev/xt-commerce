<?php


// This hook is executed, whenever an e-mail is sent. We
// need to override the language resolution method of xtc,
// because the constants are not resolved in context of the
// current set language. Hence order e-mails may contain otherwise
// not translated strings.

if (isset($GLOBALS['unzercw_force_email_language'])) {

	if (method_exists($this->Template, 'unregister_function')) {
		$this->Template->unregister_function('txt');
	}
	else if(method_exists($this->Template, 'unregisterPlugin')) {
		$this->Template->unregisterPlugin('function', 'txt');
	}

	if (!function_exists('unzercw_txt_implementation')) {
		
		if(defined("Smarty::SMARTY_VERSION") && version_compare(Smarty::SMARTY_VERSION, "3.1") >= 0 ) {
			function unzercw_txt_implementation($params, $smarty) {
				if (!isset($params['key'])) {
					return false;
				}
				
				$txt = strtoupper($params['key']);
				
				$rs = UnzerCw_Language::generalTranslate($txt, $GLOBALS['unzercw_force_email_language']);
				if ($rs !== $txt) {
					$txt = str_replace('\n', '<br />', $rs);
				}
				
				if (isset($params['show']) && ! $params['show']) {
					$smarty->assign('_txt_' . $params['key'], $txt);
				}
				else {
					echo $txt;
				}
			}
		}
		else{
			function unzercw_txt_implementation($params, &$smarty) {
				if (!isset($params['key'])) {
					return false;
				}
				
				$txt = strtoupper($params['key']);
				
				$rs = UnzerCw_Language::generalTranslate($txt, $GLOBALS['unzercw_force_email_language']);
				if ($rs !== $txt) {
					$txt = str_replace('\n', '<br />', $rs);
				}
				
				if (isset($params['show']) && ! $params['show']) {
					$smarty->assign('_txt_' . $params['key'], $txt);
				}
				else {
					echo $txt;
				}
			}
		}
		
		
	}

	if (method_exists($this->Template, 'register_function')) {
		$this->Template->register_function('txt', 'unzercw_txt_implementation');
	}
	else if(method_exists($this->Template, 'registerPlugin')) {
		$this->Template->registerPlugin('function', 'txt', 'unzercw_txt_implementation');
	}

}
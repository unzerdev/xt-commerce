<?php 

try {
	if (!function_exists('unzercw_smarty_translate')) {
		function unzercw_smarty_translate ($params) {
			require _SRV_WEBROOT . 'plugins/unzercw/init.php';
			require_once 'UnzerCw/Language.php';
			
			if (!isset($params['s'])) {
				die("To use the function 't_unzercw' at least the parameter 's' must be defined.");
			}
			$string = $params['s'];
			unset($params['s']);
			
			$args = array();
			foreach ($params as $key => $value) {
				$args['!' . $key] = $value;
			}
			
			return UnzerCw_Language::_($string, $args);
		}
	}
	
	if (isset($this->content_smarty)) {
		if (method_exists($this->content_smarty, 'register_function')) {
			$this->content_smarty->register_function('t_unzercw', 'unzercw_smarty_translate');
		}
		else {
			$this->content_smarty->unregisterPlugin('function', 't_unzercw');
			$this->content_smarty->registerPlugin('function', 't_unzercw', 'unzercw_smarty_translate');
		}
	}

}
catch(Exception $e) {
	echo $e->getMessage();
	echo "<br /><br />";
	echo $e->getTraceAsString();
}

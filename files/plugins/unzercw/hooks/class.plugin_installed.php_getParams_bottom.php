<?php

// We do not anymore use this. We needed this hook to make sure that we do order the settings correct.
// With newer versions of xt:Commerce this is not required anymore. As such we do nothing anymore within
// this hook.

// try {
// 	// We need to include the loader again, because the include path is reset in between.
// 	require _SRV_WEBROOT . 'plugins/unzercw/init.php';
// 	require_once 'UnzerCw/Util.php';
// 	require_once 'UnzerCw/DbProxy.php';

// 	if ($this->_plugin_id == UnzerCw_Util::getPluginId()) {
// 		$GLOBALS['db'] = new UnzerCw_DbProxy($GLOBALS['db']);
// 	}
// }
// catch(Exception $e) {
// 	echo $e->getMessage();
// 	echo "<br /><br />";
// 	echo $e->getTraceAsString();
// }

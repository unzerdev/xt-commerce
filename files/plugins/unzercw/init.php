<?php 
/**
  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2018 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */

/**
 * This file bootstraps the plugin. It adds the include path and assign 
 * the translation resolution.
 */

$pathToLib = realpath(dirname(__FILE__) . '/lib');
if (strpos(get_include_path(), $pathToLib) === false) {
	set_include_path(implode(PATH_SEPARATOR, array(
		get_include_path(),
		$pathToLib,
	)));
	
	// Some server configuration disallow the changing of the include path. We have
	// to provide here a better error message, than simply wait until a require fails.
	if (strpos(get_include_path(), $pathToLib) === false) {
		die("The include path could not be changed. Please change the server configuration to allow changing the include path by using the function 'set_include_path'.");
	}
}


if (!isset($GLOBALS['unzercw_basics_loaded'])) {
	$GLOBALS['unzercw_basics_loaded'] = true;
	require_once 'Customweb/Core/Util/Class.php';
	require_once 'UnzerCw/Language.php';
	require_once 'UnzerCw/LoggingListener.php';
	require_once 'Customweb/I18n/Translation.php';
	
	Customweb_Core_Logger_Factory::addListener(new UnzerCw_LoggingListener());
	// Replace the default resolver 
	Customweb_I18n_Translation::getInstance()->addResolver(new UnzerCw_Language());
	
	
}


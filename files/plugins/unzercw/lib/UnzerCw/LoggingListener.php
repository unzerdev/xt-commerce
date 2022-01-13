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
require_once 'Customweb/Core/Logger/Listener/FileWriter.php';
require_once 'UnzerCw/ConfigurationAdapter.php';
require_once 'Customweb/Core/ILogger.php';


class UnzerCw_LoggingListener extends Customweb_Core_Logger_Listener_FileWriter {

	public function __construct(){
		parent::__construct(_SRV_WEB_LOG.'UnzerCw.log');
	}
	

	public function addLogEntry($loggerName, $level, $message, Exception $e = null, $object = null){
		if(!$this->isLevelActive($level)){
			return;
		}
		parent::addLogEntry($loggerName, $level, $message, $e, $object);	
	}

	private function isLevelActive($level){
		$configuration = new UnzerCw_ConfigurationAdapter();
		switch ($configuration->getConfigurationValue('log_level')){
			case 'debug':
				return true;
			case 'info':
				if ($level == Customweb_Core_ILogger::LEVEL_DEBUG) {
					return false;
				}
				return true;
			case 'error':
				if ($level == Customweb_Core_ILogger::LEVEL_ERROR) {
					return true;
				}
				return false;
			default:
				return false;
		}
	}

}
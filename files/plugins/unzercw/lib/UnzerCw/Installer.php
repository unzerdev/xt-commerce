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

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once 'UnzerCw/Util.php';

class UnzerCw_Installer {
	
	public function install() {
		try {
			$driver = UnzerCw_Util::getDriver();
			
			$fieldExists = false;
			$rs = $driver->query("SHOW COLUMNS FROM " . TABLE_PLUGIN_CONFIGURATION);
			while (($row = $rs->fetch()) !== false) {
				if ($row['Field'] == 'sort_order') {
					$fieldExists = true;
					break;
				}
			}
			if (!$fieldExists) {
				$driver->query("ALTER TABLE " . TABLE_PLUGIN_CONFIGURATION . " ADD sort_order INT(8)")->execute();
			}
			
			$this->migrate();
		}
		catch(Exception $e) {
			echo $e->getMessage();
			echo '<br /><br />';
			echo '<strong>Trace:</strong>';
			echo '<pre>';
			echo $e->getTraceAsString();
			die();
		}
		
	}
	
	public function uninstall() {
		// TODO
	}
	
	
	public function migrate() {
		require_once 'Customweb/Database/Migration/Manager.php';
		$manager = new Customweb_Database_Migration_Manager(UnzerCw_Util::getDriver(), dirname(__FILE__) . '/Migration/', 'unzercw_schema_version');
		$manager->migrate();
		
		require_once 'UnzerCw/PluginManager.php';
		$paymentmethods = new UnzerCw_PluginManager();
		$paymentmethods->updatePlugin();
		
		require_once 'UnzerCw/OrderStatus.php';
		UnzerCw_OrderStatus::updateSettings();
		
	}
	
	
}
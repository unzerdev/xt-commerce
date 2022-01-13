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

require_once 'Customweb/Database/Migration/IScript.php';
require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/OrderStatus.php';

class UnzerCw_Migration_1_1_0 implements Customweb_Database_Migration_IScript{

	public function execute(Customweb_Database_IDriver $driver) {
		$driver->query("ALTER TABLE  `unzercw_transactions` ADD `lastSetOrderStatusSettingKey` VARCHAR( 255 )")->execute();
		$driver->query("ALTER TABLE  `unzercw_transactions` ADD `storeId` VARCHAR( 255 )")->execute();
		
		$query = "INSERT INTO " . TABLE_ADMIN_NAVIGATION . " (
				`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`) 
			VALUES 
				(NULL , 'unzercw_forms', 'images/icons/money_euro.png', '&plugin=unzercw', 'adminHandler.php', '4000', 'config', 'I', 'W');";
		$driver->query($query)->execute();
		
	}

}
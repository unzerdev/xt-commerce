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

class UnzerCw_Migration_1_0_0 implements Customweb_Database_Migration_IScript{

	public function execute(Customweb_Database_IDriver $driver) {

		
		$driver->query("CREATE TABLE IF NOT EXISTS `unzercw_customer_contexts` (
			`contextId` bigint(20) NOT NULL AUTO_INCREMENT,
			`customerId` varchar(255) DEFAULT NULL,
			`context_values` longtext,
			PRIMARY KEY (`contextId`),
			UNIQUE KEY `customerId` (`customerId`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1")->execute();
		
		$driver->query("CREATE TABLE IF NOT EXISTS `unzercw_storage` (
			`keyId` bigint(20) NOT NULL AUTO_INCREMENT,
			`keyName` varchar(165) DEFAULT NULL,
			`keySpace` varchar(165) DEFAULT NULL,
			`keyValue` longtext,
			PRIMARY KEY (`keyId`),
			UNIQUE KEY `keyName_keySpace` (`keyName`,`keySpace`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1")->execute();
		
		
		$driver->query("CREATE TABLE IF NOT EXISTS `unzercw_transactions` (
			`transactionId` bigint(20) NOT NULL AUTO_INCREMENT,
			`transactionExternalId` varchar(255) DEFAULT NULL,
			`orderId` varchar(255) DEFAULT NULL,
			`aliasForDisplay` varchar(255) DEFAULT NULL,
			`aliasActive` char(1) DEFAULT NULL,
			`paymentMachineName` varchar(255) DEFAULT NULL,
			`transactionObject` longtext,
			`authorizationType` varchar(255) DEFAULT NULL,
			`customerId` varchar(255) DEFAULT NULL,
			`updatedOn` datetime DEFAULT NULL,
			`createdOn` datetime DEFAULT NULL,
			`paymentId` varchar(255) DEFAULT NULL,
			`updatable` char(1) DEFAULT NULL,
			`executeUpdateOn` datetime DEFAULT NULL,
			`authorizationAmount` decimal(20,5) DEFAULT NULL,
			`authorizationStatus` varchar(255) DEFAULT NULL,
			`paid` char(1) DEFAULT NULL,
			`currency` varchar(255) DEFAULT NULL,
			`lastSetOrderStatus` varchar(255) DEFAULT NULL,
			PRIMARY KEY (`transactionId`),
			KEY `transactionExternalId_key` (`transactionExternalId`),
			KEY `orderId_key` (`orderId`),
			KEY `paymentId_key` (`paymentId`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1")->execute();

		$query = "INSERT INTO " . TABLE_ADMIN_NAVIGATION . " (
				`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`) 
			VALUES 
				(NULL , 'unzercw_transactions', 'images/icons/money_euro.png', '&plugin=unzercw', 'adminHandler.php', '4000', 'ordertab', 'I', 'W');";
		try {
			$driver->query($query)->execute();
		}
		catch(Exception $e) {
			
		}

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
		
		// Order Status
		UnzerCw_OrderStatus::setupOrderStatus();
		
	}

}
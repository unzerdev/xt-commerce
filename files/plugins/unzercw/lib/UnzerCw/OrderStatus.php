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


require_once 'UnzerCw/Util.php';


class UnzerCw_OrderStatus {

	private static $status = null;

	const STORAGE_SPACE = 'unzercworder_status_ids';

	/**
	 * Creates a new order status.
	 *
	 * @param boolean $visibleForCustomer
	 * @param boolean $visibleForAdmin
	 * @param boolean $downloadable
	 * @param boolean $countForStatistics
	 * @param boolean $reduceStock
	 * @param string $title
	 * @param string $identifier
	 * @return number Order Status ID
	 */
	public static function addOrderStatus($visibleForCustomer, $visibleForAdmin, $downloadable, $countForStatistics, $reduceStock, $title, $identifier) {

		$existingStatus = self::getStatusIdByIdentifier($identifier);
		if ($existingStatus !== null) {
			return $existingStatus;
		}

		$title = 'Unzer: ' . $title;

		$values = array(
			'enable_download' => $downloadable,
			'visible' => $visibleForCustomer,
			'visible_admin' => $visibleForAdmin,
			'calculate_statistic' => $countForStatistics,
			'reduce_stock' => $reduceStock,
		);
		$data = array(
			'data' => $values,
		);
		$status_values = serialize($data);

		$driver = UnzerCw_Util::getDriver();
		$orderStatusId = $driver->insert(TABLE_SYSTEM_STATUS, array(
			'>status_class' => 'order_status',
			'>status_values' => $status_values,
		));

		$driver->insert(TABLE_SYSTEM_STATUS_DESCRIPTION, array(
			'status_id' => $orderStatusId,
			'>language_code' => 'de',
			'>status_name' => $title,
		));

		$driver->insert(TABLE_SYSTEM_STATUS_DESCRIPTION, array(
			'status_id' => $orderStatusId,
			'>language_code' => 'en',
			'>status_name' => $title,
		));

		// Write the status id into the database
		self::getStorageApi()->write(self::STORAGE_SPACE, $identifier, $orderStatusId);

		self::updateSettings();

		return $orderStatusId;
	}

	public static function setupOrderStatus() {
		self::addOrderStatus(0, 1, 0, 0, 1, 'Pending', 'pending');
		self::addOrderStatus(1, 1, 0, 0, 1, 'Uncertain', 'uncertain');
		self::addOrderStatus(0, 1, 0, 0, 0, 'Failed', 'failed');
		self::addOrderStatus(1, 1, 0, 0, 1, 'Cancelled', 'cancelled');

		self::getStorageApi()->write(self::STORAGE_SPACE, 'authorized', '16');
	}

	public static function getPendingStatusId() {
		return self::getStatusIdByIdentifier('pending');
	}

	public static function getFailedStatusId() {
		return self::getStatusIdByIdentifier('failed');
	}

	/**
	 *
	 * @param string $identifier
	 * @return string
	 */
	public static function getStatusIdByIdentifier($identifier) {
		return self::getStorageApi()->read(self::STORAGE_SPACE, $identifier);
	}

	public static function getSettingsOrderStatusList() {
		$identifiers = array(
			'pending',
			'uncertain',
			'failed',
			'cancelled',
			'authorized'
		);
		$map = array();
		foreach ($identifiers as $identifier) {
			$map[self::getStatusIdByIdentifier($identifier)] = $identifier;
		}

		$list = self::getAllOrderStatus();
		$rs = array();
		foreach ($list as $statusId => $status) {
			if (isset($map[$statusId])) {
				$rs[$map[$statusId]] = $status;
			}
			else {
				$rs[$statusId] = $status;
			}
		}

		return $rs;
	}

	public static function isSendMailRequiredOnStatus($statusId) {
		$constant = 'UNZERCW_SEND_MAIL_ON_UPDATE_' . $statusId;
		if (defined($constant) && constant($constant) == '1') {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * This method checks if for any new order status a setting in the main
	 * plugin settings page should be created.
	 *
	 * @return
	 */
	public static function updateSettings() {

		$driver = UnzerCw_Util::getDriver();

		// Add or update all order status
		$sortOrder = 1000;

		// Set cache to get the most current status set.
		self::$status = null;
		$allStatus = self::getAllOrderStatus();
		foreach ($allStatus as $statusId => $status) {
			$val = array(
				'key' => 'UNZERCW_SEND_MAIL_ON_UPDATE_' . $statusId,
				'value' => '',
				'type' => 'status',
				'sort_order' => $sortOrder,
				'de' => array(
					'title' => 'E-Mail versenden bei Status (ID: ' . $statusId . '): ' . $status['de'],
				),
				'en' => array(
					'title' => 'Send Mail on status (ID: ' . $statusId . '): ' . $status['en'],
				),
			);

			$pluginId = UnzerCw_Util::getPluginId();
			$plugin = UnzerCw_Util::getPlugin();
			$stores = $GLOBALS['store_handler']->getStores();
			foreach ($stores as $sdata) {
				$plugin->_addStoreConfig($val, $pluginId, $sdata['id'], 'plugin');
			}
			$plugin->_addLangContentModule('unzercw', $val);

			$driver->query("UPDATE " . TABLE_PLUGIN_CONFIGURATION . " SET sort_order = >sort_order WHERE config_key = >config_key")->execute(array(
				'>sort_order' => $val['sort_order'],
				'>config_key' => $val['key'],
			));
			$sortOrder++;
		}

		// Remove setting for status, that do not exist anymore.
		$rs = $driver->query("SELECT config_key FROM ".TABLE_PLUGIN_CONFIGURATION." WHERE config_key = 'UNZERCW_SEND_MAIL_ON_UPDATE_%'");
		while (($row = $rs->fetch()) !== false) {
			$id = str_replace('UNZERCW_SEND_MAIL_ON_UPDATE_', '', $row['config_key']);
			if (!isset($allStatus[$id])) {
				$driver->query("DELETE FROM ".TABLE_PLUGIN_CONFIGURATION." WHERE config_key = '" . $row['config_key'] . "'")->execute();
			}
		}
	}

	public static function getAllOrderStatus() {
		if (self::$status === null) {
			$driver = UnzerCw_Util::getDriver();
			$rs = $driver->query("SELECT * FROM " . TABLE_SYSTEM_STATUS . " AS s, " . TABLE_SYSTEM_STATUS_DESCRIPTION . " AS d WHERE d.status_id = s.status_id AND status_class = 'order_status'");
			self::$status = array();
			while (($row = $rs->fetch()) !== false) {
				self::$status[$row['status_id']][$row['language_code']] = $row['status_name'];
			}
		}

		return self::$status;
	}



	/**
	 * @return Customweb_Storage_IBackend
	 */
	private static function getStorageApi() {
		return UnzerCw_Util::createContainer()->getBean('Customweb_Storage_IBackend');
	}

}
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

require_once 'Customweb/Core/Stream/Input/File.php';

require_once 'UnzerCw/Util.php';


/**
 * 
 * 
 * @author Thomas Hunziker
 *
 */
class UnzerCw_SettingApi {
	
	private $data = null;
	private $prefix = null;
	
	private static $configCache = array();
	
	private static $storeId = null;
	
	public function __construct(array $data) {
		$this->data = $data;
	}
	
	public function getValue($key, $languageCode = null) {
		$key = strtolower($key);
		if (!isset($this->data[$key])) {
			throw new Exception("The setting with key '" . $key . "' does not exists.");
		}
		
		$type = strtolower($this->data[$key]['type']);
		
		if ($type == 'multiselect') {
			$values = array();
			foreach ($this->data[$key]['options'] as $value => $constant) {
				if ($this->existsValueInDatabase($constant) && $this->getValueFromDatabase($constant) == 'active') {
					$values[] = $value;
				}
			}
			return $values;
		}
		else if ($type == 'file') {
			$constant = $this->data[$key]['constant'];
			if ($this->existsValueInDatabase($constant)) {
				$path = $this->getValueFromDatabase($constant);
			}
			else {
				$path = $this->data[$key]['default'];
			}
			if (file_exists(UnzerCw_Util::getUploadDirectory() . $path)) {
				return new Customweb_Core_Stream_Input_File(UnzerCw_Util::getUploadDirectory() . $path);
			}
			else {
				try {
					return UnzerCw_Util::getAssetResolver()->resolveAssetStream($this->data[$key]['default']);
				}
				catch(Customweb_Asset_Exception_UnresolvableAssetException $e) {
					return null;
				}
			}
		}
		else if ($type == 'multilangfield') {
			if ($languageCode === null) {
				throw new Exception("A multi language field requires always that the language code is supplied.");
			}
			$languageCode = (string)$languageCode;
			if (isset($this->data[$key]['languageConstants'][strtoupper($languageCode)])) {
				$constantName = $this->data[$key]['languageConstants'][strtoupper($languageCode)];
				if ($this->existsValueInDatabase($constantName)) {
					return $this->getValueFromDatabase($constantName);
				}
			}
			foreach ($this->data[$key]['languageConstants'] as $constant) {
				if ($this->existsValueInDatabase($constant)) {
					return $this->getValueFromDatabase($constant);
				}
			}
			return $this->data[$key]['default'];
		}
		else {
			$constant = $this->data[$key]['constant'];
			if ($this->existsValueInDatabase($constant)) {
				return $this->getValueFromDatabase($constant);
			}
			else {
				return $this->data[$key]['default'];
			}
		}
		
	}
	
	public function existsValue($key, $languageCode = null) {
		if (isset($this->data[$key])) {
			return true;
		}
		else {
			return false;
		}
	}
	

	public static function setCurrentStoreId($storeId) {
		self::$storeId = $storeId;
	}
	
	public static function getCurrentStoreId() {
		return self::$storeId;
	}
	
	private function existsValueInDatabase($key) {
		$data = self::loadDataByStore(self::getCurrentStoreId());
		if (isset($data[$key])) {
			return true;
		}
		else if(defined($key)) {
			return true;
		}
		else {
			return false;
		}
	}
	
	
	private function getValueFromDatabase($key) {
		$data = self::loadDataByStore(self::getCurrentStoreId());
		if (isset($data[$key])) {
			return $data[$key];
		}
		else if(defined($key)) {
			return constant($key);
		}
		else {
			return null;
		}
	}
	
	private static function loadDataByStore($storeId) {
		if ($storeId === null) {
			return array();
		}
		$storeId = (int)$storeId;
		
		if (!isset(self::$configCache[$storeId])) {
			self::$configCache[$storeId] = array_merge(
					self::loadDataByTable(TABLE_CONFIGURATION_MULTI . $storeId),
					self::loadDataByTable(TABLE_CONFIGURATION_PAYMENT, 'shop_id=' . $storeId),
					self::loadDataByTable(TABLE_PLUGIN_CONFIGURATION, 'shop_id=' . $storeId)
			);
		}
		
		return self::$configCache[$storeId];
	}
	
	private static function loadDataByTable($tableName, $where = '1=1') {
		$statement = UnzerCw_Util::getDriver()->query("SELECT config_key, config_value FROM " . $tableName . " WHERE " . $where);
		$data = array();
		while (($row = $statement->fetch()) !== false) {
			$data[strtoupper($row['config_key'])] = str_replace(array('"', '\'', '\r\n', '\n'), array('&quot;', '&apos;', PHP_EOL, PHP_EOL), $row['config_value']);
		}
		return $data;
	}
	
}
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

require_once 'Customweb/Payment/IConfigurationAdapter.php';

require_once 'UnzerCw/Language.php';
require_once 'UnzerCw/SettingApi.php';
require_once 'UnzerCw/OrderStatus.php';


/**
 * @Bean
 */
class UnzerCw_ConfigurationAdapter implements Customweb_Payment_IConfigurationAdapter{
	
	private $settingApi = null;
	private static $settingsData = array(
		'operating_mode' => array(
			'type' => 'SELECT',
 			'constant' => 'UNZERCW_OPERATING_MODE',
 			'options' => array(
				'test' => 'Test',
 				'live' => 'Live',
 			),
 			'default' => 'test',
 		),
 		'public_key_live' => array(
			'type' => 'TEXTFIELD',
 			'constant' => 'UNZERCW_PUBLIC_KEY_LIVE',
 			'default' => '',
 		),
 		'private_key_live' => array(
			'type' => 'TEXTFIELD',
 			'constant' => 'UNZERCW_PRIVATE_KEY_LIVE',
 			'default' => '',
 		),
 		'public_key_test' => array(
			'type' => 'TEXTFIELD',
 			'constant' => 'UNZERCW_PUBLIC_KEY_TEST',
 			'default' => '',
 		),
 		'private_key_test' => array(
			'type' => 'TEXTFIELD',
 			'constant' => 'UNZERCW_PRIVATE_KEY_TEST',
 			'default' => '',
 		),
 		'order_id_schema' => array(
			'type' => 'TEXTFIELD',
 			'constant' => 'UNZERCW_ORDER_ID_SCHEMA',
 			'default' => '{id}',
 		),
 		'payment_reference_schema' => array(
			'type' => 'TEXTFIELD',
 			'constant' => 'UNZERCW_PAYMENT_REFERENCE_SCHEMA',
 			'default' => '{id}',
 		),
 		'invoice_id_schema' => array(
			'type' => 'TEXTFIELD',
 			'constant' => 'UNZERCW_INVOICE_ID_SCHEMA',
 			'default' => '{id}',
 		),
 		'log_level' => array(
			'type' => 'SELECT',
 			'constant' => 'UNZERCW_LOG_LEVEL',
 			'options' => array(
				'error' => 'Error',
 				'info' => 'Info',
 				'debug' => 'Debug',
 			),
 			'default' => 'error',
 		),
 	);
	private static $languages = null;
	private static $storeId = null;
	
	public function getConfigurationValue($key, $languageCode = null) {
		return $this->getSettingsApi()->getValue($key, $languageCode);
	}
	
	public function existsConfiguration($key, $language = null) {
		return $this->getSettingsApi()->existsValue($key, $language);
	}
	
	protected function getSettingsApi() {
		if ($this->settingApi === null) {
			$this->settingApi = new UnzerCw_SettingApi(self::$settingsData);
		}
		
		return $this->settingApi;
	}
	
	public function getLanguages($currentStore = false) {
		if (self::$languages === null) {
			self::$languages = array();
			$lang = new language();
			$list = $lang->_getLanguageContentList();
			foreach ($list as $value) {
				self::$languages[$value['code']] = $value['name'];
			}
		}
		
		return self::$languages;
	}
	
	public function getStoreHierarchy() {
		
		if (self::getCurrentStoreId() !== null) {
			if (self::getCurrentStoreId() === 'default') {
				$currentStoreId = null;
			}
			else {
				$currentStoreId = self::getCurrentStoreId();
			}
			$store_handler = new multistore();
		}
		else if (isset($GLOBALS['store_handler'])) {
			$store_handler = $GLOBALS['store_handler'];
			$currentStoreId = $store_handler->shop_id;
		}
		else {
			$store_handler = new multistore();
			$store_handler->determineStoreId();
			$currentStoreId = $store_handler->shop_id;
		}
		
		if ($currentStoreId === null) {
			return NULL;
		}
		
		$stores = $store_handler->getStores();
		if (count($stores) <= 1) {
			return NULL;
		}
		
		$storeName = '';
		foreach ($stores as $store) {
			if ($store['id'] == $currentStoreId) {
				$storeName = $store['text'];
				break;
			}
		}
		return array(
			'default' => UnzerCw_Language::_('Default'),
			$currentStoreId => $storeName,
		);
		
	}
	
	public function useDefaultValue(Customweb_Form_IElement $element, array $formData) {
		$controlName = implode('_', $element->getControl()->getControlNameAsArray());
		return (isset($formData['default'][$controlName]) && $formData['default'][$controlName] == 'default');
	}
		
	public function getOrderStatus() {
		return UnzerCw_OrderStatus::getSettingsOrderStatusList();
	}
	
	public static function setCurrentStoreId($storeId) {
		UnzerCw_SettingApi::setCurrentStoreId($storeId);
		self::$storeId = $storeId;
	}
	
	public static function getCurrentStoreId() {
		return self::$storeId;
	}
	
	
}
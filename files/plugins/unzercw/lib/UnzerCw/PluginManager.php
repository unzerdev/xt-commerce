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
require_once 'UnzerCw/AbstractPaymentMethod.php';


class UnzerCw_PluginManager {
	
	/**
	 * This method installs all payment methods defined or update the existing 
	 * methods.
	 * 
	 * @return void
	 */
	public function installPaymentMethods() {
		foreach ($this->getInstances() as $instance) {
			$instance->install();
		}
	}
	
	/**
	 * Removes all payment methods.
	 * 
	 * @return void
	 */
	public function removeMethods() {
		foreach ($this->getInstances() as $instance) {
			$instance->uninstall();
		}
	}
	
	public function updatePlugin() {
		// We update / add payment methods
		$this->installPaymentMethods();
		
		// We update current plugin language and configurations
		$this->updatePluginConfigurations();
		
		// Update the plugin version to the latest
		$this->updateVersion();
	}
	
	public function updatePluginConfigurations() {
		$xml = file_get_contents(_SRV_WEBROOT . 'plugins/unzercw/installer/unzercw.xml');
		$xml_data = XML_unserialize($xml);
	
		$plugin = UnzerCw_Util::getPlugin();
		$pluginId = UnzerCw_Util::getPluginId();
		$stores = $GLOBALS['store_handler']->getStores();
		$driver = UnzerCw_Util::getDriver();
		
		// Hooks
		if (is_array($xml_data['xtcommerceplugin']['plugin_code']['code'])) {
			foreach ($xml_data['xtcommerceplugin']['plugin_code']['code'] as $key => $arr) {
				$plugin->_addCode($pluginId, $arr, 'unzercw');
			}
		}
		
		// Language Variables
		if (is_array($xml_data['xtcommerceplugin']['language_content']['phrase'])) {
			foreach ($xml_data['xtcommerceplugin']['language_content']['phrase'] as $key => $val) {
				$plugin->_addLangContent('unzercw', $val);
			}
		}
		
		// Configurations
		if (is_array($xml_data['xtcommerceplugin']['configuration']['config'])) {
			foreach ($xml_data['xtcommerceplugin']['configuration']['config'] as $key => $val) {
				foreach ($stores as $sdata) {
					$plugin->_addStoreConfig($val, $pluginId, $sdata['id'], 'plugin');
				}
				$plugin->_addLangContentModule('unzercw', $val);
				
				// Fix sort order
				if (isset($val['sort_order'])) {
					$driver->query("UPDATE " . TABLE_PLUGIN_CONFIGURATION . " SET sort_order = >sort_order WHERE config_key = >config_key")->execute(array(
						'>sort_order' => $val['sort_order'],
						'>config_key' => $val['key'],
					));
				}
			}
		}
	}
	
	public function updateVersion() {
		$driver = UnzerCw_Util::getDriver();
		$driver->query("UPDATE " . TABLE_PLUGIN_PRODUCTS . " SET version = '1.0.87' WHERE code = 'unzercw'")->execute();
	}
	
	
	/**
	 * @throws Exception
	 * @return UnzerCw_AbstractPaymentMethod[]
	 */
	private function getInstances() {
		$instances = array();
		$folder = _SRV_WEBROOT . 'plugins/unzercw/classes/';
	
		if ($handle = opendir($folder)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..' && strpos($file, 'class.cw_UNZ_') === 0) {
					$className = substr(substr($file, 6), 0, -4);
					require_once $folder . $file;
					$instance = new $className();
					if (!($instance instanceof UnzerCw_AbstractPaymentMethod)) {
						throw new Exception("Payment class must implement UnzerCw_AbstractPaymentMethod.");
					}
					$instances[] = $instance;
				}
			}
		}
	
		return $instances;
	}
}


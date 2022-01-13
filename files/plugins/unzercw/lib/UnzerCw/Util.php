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

require_once 'Customweb/Database/Driver/MySQL/Driver.php';
require_once 'Customweb/Core/Url.php';
require_once 'Customweb/DependencyInjection/Container/Default.php';
require_once 'Customweb/Asset/Resolver/Composite.php';
require_once 'Customweb/Mvc/Template/Smarty/ContainerBean.php';
require_once 'Customweb/Payment/Authorization/DefaultPaymentCustomerContext.php';
require_once 'Customweb/Database/Driver/MySQLi/Driver.php';
require_once 'Customweb/Database/Entity/Manager.php';
require_once 'Customweb/Core/Http/ContextRequest.php';
require_once 'Customweb/DependencyInjection/Bean/Provider/Annotation.php';
require_once 'Customweb/DependencyInjection/Bean/Provider/Editable.php';
require_once 'Customweb/Cache/Backend/Memory.php';
require_once 'Customweb/Core/Util/Class.php';
require_once 'Customweb/Payment/Authorization/IAdapterFactory.php';
require_once 'Customweb/Asset/Resolver/Simple.php';

require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/Entity/PaymentCustomerContext.php';
require_once 'UnzerCw/Adapter/IAdapter.php';
require_once 'UnzerCw/EndpointAdapter.php';


class UnzerCw_Util {
	
	private static $driver = null;
	private static $container = null;
	private static $paymentCustomerContexts = array();
	private static $entityManager = null;
	private static $plugin = null;
	private static $pluginId = null;
	private static $endpointAdapter = null;
	private static $resolver = null;
	
	private static $uploadedFiles = array();

	public static function isAliasManagerActive(Customweb_Payment_Authorization_IOrderContext $orderContext) {
		$paymentMethod = $orderContext->getPaymentMethod();
		if ($paymentMethod->existsPaymentMethodConfigurationValue('alias_manager') && strtolower($paymentMethod->getPaymentMethodConfigurationValue('alias_manager')) == 'active') {
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * @return Customweb_Database_IDriver
	 */
	public static function getDriver() {
		if (self::$driver === null) {
			global $db;
			if (isset($db->databaseType)) {
				if (strtolower($db->databaseType) == 'mysql') {
					require_once 'Customweb/Database/Driver/MySQL/Driver.php';
					self::$driver = new Customweb_Database_Driver_MySQL_Driver($db->_connectionID);
				}
				else if (strtolower($db->databaseType) == 'mysqli') {
					require_once 'Customweb/Database/Driver/MySQLi/Driver.php';
					self::$driver = new Customweb_Database_Driver_MySQLi_Driver($db->_connectionID);
				}
				else {
					throw new Exception("Not support database driver.");
				}
			}
			else {
				throw new Exception("Database object does not have any databaseType property.");
			}
		}
		
		return self::$driver;
	}
	
	/**
	 * @return number
	 */
	public static function getPluginId() {
		if (self::$pluginId === null) {
			$driver = self::getDriver();
			$statement = $driver->query('SELECT * FROM ' . TABLE_PLUGIN_PRODUCTS . ' WHERE code = "unzercw"');
			$rs = $statement->fetch();
			if ($rs === false) {
				throw new Exception("The plugin must be installed, before the plugin id can be retrieved.");
			}
			self::$pluginId = $rs['plugin_id'];
		}
		return self::$pluginId;
	}
	
	/**
	 * @return plugin
	 */
	public static function getPlugin() {
		if (self::$plugin === null) {
			self::$plugin = new plugin(self::getPluginId());
		}
		return self::$plugin;		
	}
	
	/**
	 * @return Customweb_DependencyInjection_Container_Default
	 */
	public static function createContainer() {
		require_once 'Customweb/DependencyInjection/Bean/Provider/Annotation.php';

		if (self::$container === null) {
			$packages = array(
			0 => 'Customweb_Unzer',
 			1 => 'Customweb_Payment_Authorization',
 		);
			$packages[] = 'UnzerCw_';
			$packages[] = 'Customweb_Payment_Alias';
			$packages[] = 'Customweb_Payment_Update';
			$packages[] = 'Customweb_Payment_TransactionHandler';
			$packages[] = 'Customweb_Storage_Backend_Database';
			$packages[] = 'UnzerCw_LayoutRenderer';
			$packages[] = 'Customweb_Mvc_Template_Smarty_Renderer';
			$packages[] = 'Customweb_Payment_Update_ScheduledProcessor';
			$packages[] = 'UnzerCw_EndpointAdapter';
			$packages[] = 'Customweb_Payment_SettingHandler';

			$provider = new Customweb_DependencyInjection_Bean_Provider_Editable(new Customweb_DependencyInjection_Bean_Provider_Annotation(
					$packages
			));
			$provider
				->addObject(self::getEntityManager())
				->addObject(Customweb_Core_Http_ContextRequest::getInstance())
				->addObject(self::getDriver())
				->addObject(self::getAssetResolver())
				->add('databaseTransactionClassName', 'UnzerCw_Entity_Transaction')
				->add('storageDatabaseEntityClassName', 'UnzerCw_Entity_Storage');
			
			$smarty = new Smarty();
			$templateRenderer = new Customweb_Mvc_Template_Smarty_ContainerBean($smarty);
			$provider->addObject($templateRenderer);
				
			self::$container = new Customweb_DependencyInjection_Container_Default($provider);
		}

		return self::$container;
	}
	
	/**
	 * @return Customweb_Payment_Alias_Handler
	 */
	public static function getAliasHandler() {
		return UnzerCw_Util::createContainer()->getBean('Customweb_Payment_Alias_Handler');
	}
	
	/**
	 * @return Customweb_Database_Entity_Manager
	 */
	public static function getEntityManager() {
		if (self::$entityManager === null) {
			require_once 'Customweb/Cache/Backend/Memory.php';
			$cache = new Customweb_Cache_Backend_Memory();
			self::$entityManager = new Customweb_Database_Entity_Manager(self::getDriver(), $cache);
		}
		return self::$entityManager;
	}
	
	/**
	 *
	 * @return Customweb_Payment_ITransactionHandler
	 */
	public static function getTransactionHandler(){
		$container = self::createContainer();
		$handler = $container->getBean('Customweb_Payment_ITransactionHandler');
		return $handler;
	}

	/**
	 * Creates a new payment method instance based on the given
	 * method name.
	 *
	 * @param string $methodName
	 * @return UnzerCw_AbstractPaymentMethod
	 */
	public static function getPaymentMethodInstanceByName($methodName) {
		if (empty($methodName)) {
			throw new Exception("Could not load payment method from a empty payment method name.");
		}
		
		$methodName = strtolower($methodName);
		$className = 'cw_UNZ_' . $methodName;
		$classFilePath = _SRV_WEBROOT._SRV_WEB_PLUGINS . 'unzercw/classes/class.' . $className . '.php';
		require_once $classFilePath;
		return new $className();
	}

	/**
	 * Creates a new payment method instance based on the payment code.
	 *
	 * @param string $methodName
	 * @return UnzerCw_AbstractPaymentMethod
	 */
	public static function getPaymentMethodInstanceByCode($code) {
		$methodName = str_replace('cw_UNZ_', '', $code);
		return self::getPaymentMethodInstanceByName($methodName);
	}
	
	/**
	 * @param int $transactionId
	 * @return UnzerCw_Entity_Transaction
	 */
	public static function loadTransaction($transactionId) {
		return UnzerCw_Util::getEntityManager()->fetch('UnzerCw_Entity_Transaction', $transactionId);
	}
	
	public static function getFrontendUrl($page, $parameters = array(), $ssl = true) {
		global $xtLink;
		/* @var $xtLink xtLink */
		
		$connection = 'NOSSL';
		if ($ssl) {
			$connection = 'SSL';
		}
		
		$parameters['noredirect'] = 'true';
		
		return str_replace('xtAdmin/', '', str_replace('&amp;', '&', $xtLink->_link(array('page' => $page, 'conn' => $connection, 'params' => Customweb_Core_Url::parseArrayToString($parameters)))));
	}
	
	/**
	 * This method generates an URL to the controller indicated with the given action and parameters.
	 * 
	 * @param string $controller
	 * @param string (Optional) $action
	 * @param array (Optional) $parameters
	 * @param string (Optional) $ssl Indicates if SSL should be used.
	 * @return String URL to the controller.
	 */
	public static function getControllerUrl($controller, $action = null, array $parameters = array(), $ssl = true) {
		$parameters['controller'] = $controller;
		if ($action !== null) {
			$parameters['action'] = $action;
		}
		return self::getFrontendUrl('unzercw', $parameters, $ssl);
	}
	
	/**
	 * @param int $customerId
	 * @return Customweb_Payment_Authorization_IPaymentCustomerContext
	 */
	public static function getPaymentCustomerContext($customerId) {
		// Handle guest context. This context is not stored.
		if ($customerId === null || $customerId === 0) {
			if (!isset(self::$paymentCustomerContexts['guestContext'])) {
				self::$paymentCustomerContexts['guestContext'] = new Customweb_Payment_Authorization_DefaultPaymentCustomerContext(array());
			}
			
			return self::$paymentCustomerContexts['guestContext'];
		}
		
		if (!isset(self::$paymentCustomerContexts[$customerId])) {
			$entities = self::getEntityManager()->searchByFilterName('UnzerCw_Entity_PaymentCustomerContext', 'loadByCustomerId', array(
				'>customerId' => $customerId,
			));
			if (count($entities) > 0) {
				self::$paymentCustomerContexts[$customerId] = current($entities);
			}
			else {
				$context = new UnzerCw_Entity_PaymentCustomerContext();
				$context->setCustomerId($customerId);
				self::$paymentCustomerContexts[$customerId] = $context;
			}
		}
		return self::$paymentCustomerContexts[$customerId]; 
	}
	
	public static function persistPaymentCustomerContext(Customweb_Payment_Authorization_IPaymentCustomerContext $context) {
		if ($context instanceof UnzerCw_Entity_PaymentCustomerContext) {
			$storedContext = self::getEntityManager()->persist($context);
			self::$paymentCustomerContexts[$storedContext->getCustomerId()] = $storedContext;
		}
	}
	

	/**
	 * @throws Exception
	 * @return Customweb_Payment_Authorization_IAdapterFactory
	 */
	public static function getAuthorizationAdapterFactory() {
		$factory = self::createContainer()->getBean('Customweb_Payment_Authorization_IAdapterFactory');

		if (!($factory instanceof Customweb_Payment_Authorization_IAdapterFactory)) {
			throw new Exception("The payment api has to provide a class which implements 'Customweb_Payment_Authorization_IAdapterFactory' as a bean.");
		}

		return $factory;
	}

	/**
	 * Returns the authorization adapter by the given order context.
	 * 
	 * @param Customweb_Payment_Authorization_IOrderContext $orderContext
	 * @return Customweb_Payment_Authorization_IAdapter
	 */
	public static function getAuthorizationAdapterByContext(Customweb_Payment_Authorization_IOrderContext $orderContext) {
		$adapter = self::getAuthorizationAdapterFactory()->getAuthorizationAdapterByContext($orderContext);
		return $adapter;
	}

	/**
	 * @param string $name
	 * @return Customweb_Payment_Authorization_IAdapter
	 */
	public static function getAuthorizationAdapterByMethod($name) {
		return self::getAuthorizationAdapterFactory()->getAuthorizationAdapterByName($name);
	}
	
	/**
	 * @param Customweb_Payment_Authorization_IOrderContext $orderContext
	 * @return UnzerCw_Adapter_IAdapter
	 */
	public static function getCheckoutAdapterByContext(Customweb_Payment_Authorization_IOrderContext $orderContext) {
		$adapter = self::getAuthorizationAdapterByContext($orderContext);
		return self::getCheckoutAdapter($adapter);
	}

	/**
	 * @param string $method
	 * @return UnzerCw_Adapter_IAdapter
	 */
	public static function getCheckoutAdapterByAuthorizationMethod($method) {
		$adapter = self::getAuthorizationAdapterFactory()->getAuthorizationAdapterByName($method);
		return self::getCheckoutAdapter($adapter);
	}
	
	/**
	 * @param Customweb_Payment_Authorization_IAdapter $paymentAdapter
	 * @throws Exception
	 * @return UnzerCw_Adapter_IAdapter
	 */
	public static function getCheckoutAdapter(Customweb_Payment_Authorization_IAdapter $paymentAdapter) {
		$reflection = new ReflectionClass($paymentAdapter);
		$adapters = self::createContainer()->getBeansByType('UnzerCw_Adapter_IAdapter');
		foreach ($adapters as $adapter) {
			if ($adapter instanceof UnzerCw_Adapter_IAdapter) {
				$inferfaceName = $adapter->getPaymentAdapterInterfaceName();
				try {
					Customweb_Core_Util_Class::loadLibraryClassByName($inferfaceName);
					if ($reflection->implementsInterface($inferfaceName)) {
						$adapter->setInterfaceAdapter($paymentAdapter);
						return $adapter;
					}
				}
				catch(Customweb_Core_Exception_ClassNotFoundException $e) {
					// Ignore
				}
			}
		}
	
		throw new Exception("Could not resolve to checkout adapter.");
	}
	
	public static function getCleanRequestArray() {
		$params = $_REQUEST;
		unset($params['cw_transaction_id']);
		unset($params['controller']);
		unset($params['action']);
		unset($params['unzercw_alias_use_new_card']);
		unset($params['unzercw_alias_use_stored_card']);
		unset($params['unzercw_alias']);
		unset($params['unzercw_create_new_alias']);
		unset($params['unzercw_update_alias']);
		unset($params['payment_method_name']);
		unset($params['ajaxCall']);
		unset($params['conditions_accepted']);
		unset($params['noredirect']);
		
		return $params;
	}
	
	/**
	 * This method returns a list of uploaded files.
	 * 
	 * @return array
	 * 
	 */
	public static function getUploadedFileList() {
		if (self::$uploadedFiles === null) {
			self::$uploadedFiles = array();
			if ($handle = opendir(self::getUploadDirectory())) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != '.htaccess' && $file != 'README.txt') {
						self::$uploadedFiles[$file] = $file;
					}
				}
				closedir($handle);
			}
		}
		return self::$uploadedFiles;
	}
	
	public static function getUploadDirectory() {
		return dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/media/payment/unzercw_uploads/'; 
	}
	
	public static function getEndpointAdapter() {
		if (self::$endpointAdapter === null) {
			self::$endpointAdapter = new UnzerCw_EndpointAdapter();
		}
		
		return self::$endpointAdapter;
	}
	
	/**
	 * @return Customweb_Storage_IBackend
	 */
	public static function getStorageAdapter() {
		return self::createContainer()->getBean('Customweb_Storage_IBackend');
	}

	/**
	 * @return Customweb_Payment_BackendOperation_Form_IAdapter
	 */
	public static function getBackendFormAdapter() {
		$container = self::createContainer();
		if ($container->hasBean('Customweb_Payment_BackendOperation_Form_IAdapter')) {
			return $container->getBean('Customweb_Payment_BackendOperation_Form_IAdapter');
		}
		else {
			return null;
		}
	}
	
	/**
	 * @return Customweb_Asset_IResolver
	 */
	public static function getAssetResolver() {
		if (self::$resolver === null) {
			self::$resolver = new Customweb_Asset_Resolver_Composite(array(
				new Customweb_Asset_Resolver_Simple(
						_SRV_WEBROOT . '/templates/' . _STORE_TEMPLATE . '/plugins/unzercw/snippets/', 
						_SRV_WEB_TEMPLATES . _STORE_TEMPLATE . '/plugins/unzercw/snippets/',
						array('application/x-smarty')
				),
				new Customweb_Asset_Resolver_Simple(
						_SRV_WEBROOT . '/templates/' . _STORE_TEMPLATE . '/plugins/unzercw/css/', 
						_SRV_WEB_TEMPLATES . _STORE_TEMPLATE . '/plugins/unzercw/css/',
						array('text/css')
				),
				new Customweb_Asset_Resolver_Simple(
						_SRV_WEBROOT . '/templates/' . _STORE_TEMPLATE . '/plugins/unzercw/js/', 
						_SRV_WEB_TEMPLATES . _STORE_TEMPLATE . '/plugins/unzercw/js/',
						array('application/javascript')
				),
				new Customweb_Asset_Resolver_Simple(
						_SRV_WEBROOT . '/templates/' . _STORE_TEMPLATE . '/plugins/unzercw/img/', 
						_SRV_WEB_TEMPLATES . _STORE_TEMPLATE . '/plugins/unzercw/img/',
						array('image/png')
				),
				new Customweb_Asset_Resolver_Simple(
						_SRV_WEBROOT . 'plugins/unzercw/templates/snippets/', 
						_SYSTEM_BASE_URL._SRV_WEB._SRV_WEB_PLUGINS.'unzercw/templates/snippets/',
						array('application/x-smarty')
				),
				new Customweb_Asset_Resolver_Simple(
						_SRV_WEBROOT . 'plugins/unzercw/templates/css/', 
						_SYSTEM_BASE_URL._SRV_WEB._SRV_WEB_PLUGINS.'unzercw/templates/css/',
						array('text/css')
				),
				new Customweb_Asset_Resolver_Simple(
						_SRV_WEBROOT . 'plugins/unzercw/templates/js/', 
						_SYSTEM_BASE_URL._SRV_WEB._SRV_WEB_PLUGINS.'unzercw/templates/js/',
						array('application/javascript')
				),
				new Customweb_Asset_Resolver_Simple(
						_SRV_WEBROOT . 'plugins/unzercw/templates/img/', 
						_SYSTEM_BASE_URL._SRV_WEB._SRV_WEB_PLUGINS.'unzercw/templates/img/',
						array('image/png')
				),
				new Customweb_Asset_Resolver_Simple(
						_SRV_WEBROOT . 'plugins/unzercw/assets/', 
						_SYSTEM_BASE_URL._SRV_WEB._SRV_WEB_PLUGINS.'unzercw/assets/'
				),
			));
		}
	
		return self::$resolver;
	}
	
}


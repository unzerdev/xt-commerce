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

require_once 'Customweb/Core/String.php';
require_once 'Customweb/Payment/Authorization/IPaymentMethod.php';

require_once 'UnzerCw/Language.php';
require_once 'UnzerCw/OrderContext/Order.php';
require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/SettingApi.php';
require_once 'UnzerCw/OrderContext/Session.php';
require_once 'UnzerCw/PaymentMethodWrapper.php';


defined('_VALID_CALL') or die('Direct Access is not allowed.');

abstract class UnzerCw_AbstractPaymentMethod implements Customweb_Payment_Authorization_IPaymentMethod
{

	public $external = true;
	public $version = '1.0.87';
	private $settingsApi = null;
	public $subpayments = null;

	/**
	 * Generates the redirection URL. This method is called by the core of
	 * xt:Commerce.
	 *
	 * @param array $order_data
	 * @return string URL
	 */
	public function pspRedirect($order_data) {
		try {
			if (isset($order_data['orders_id'])) {
				$order = new order($order_data['orders_id'], $order_data['customers_id']);

				$orderContext = new UnzerCw_OrderContext_Order(new UnzerCw_PaymentMethodWrapper($this), $order);
				$adapter = UnzerCw_Util::getCheckoutAdapterByContext($orderContext);
				$adapter->prepare($this, $orderContext, $order_data['orders_id']);
				return $adapter->processOrderConfirmationRequest();
			}
			else {
				die("No order id provided.");
			}
		}
		catch(Exception $e) {
			echo '<b>' . $e->getMessage() . '</b>';
			echo "<br /><br /><pre>";
			echo $e->getTraceAsString();
			die();
		}

	}

	public function __get($name) {
		// In case the data property is read in the backend, we return null. To prevent loops.
		if (stristr($_SERVER['SCRIPT_NAME'], 'adminHandler.php') !== false) {
			return null;
		}

		if ($name == 'data') {
			return $this->buildPaymentListVariables();
		}
		else {
			throw new Exception(Customweb_Core_String::_("The property with name '@name' does not exists.")->format(array('@name' => $name)));
		}
	}

	
	public function buildPaymentListVariables() {
		if (false) {
			return array(
				'paymentMachineName' => $this->getPaymentMethodName(),
				'paymentPane' => '<div style="border: 1px solid #ff0000; background: #ffcccc; font-weight: bold;">' .
					UnzerCw_Language::_('We experienced a problem with your sellxed payment extension. For more information, please visit the configuration page of the Unzer plugin.') .
				'</div>'
			);
		}
		return array(
			'paymentMachineName' => $this->getPaymentMethodName(),
		);
	}
	

	final public function getPaymentMethodDisplayName() {

		$languageCode = '';
		if (isset($_SESSION['selected_language'])) {
			$languageCode = $_SESSION['selected_language'];
		}
		else {
			$language = new language();
			$language->_getLanguage();
			$languageCode = $language->code;
		}

		$driver = UnzerCw_Util::getDriver();
		$statement = $driver->query("SELECT * FROM " . TABLE_PAYMENT_DESCRIPTION . " AS d, " . TABLE_PAYMENT . " AS p WHERE payment_code = >payment_code AND d.payment_id = p.payment_id AND language_code = >language_code");
		$statement->execute(array(
			'>payment_code' => $this->getPaymentCode(),
			'>language_code' => $languageCode,
		));

		$paymentMethodName = '';
		if (($row = $statement->fetch()) !== false) {
			$paymentMethodName = $row['payment_name'];
		}

		if (empty($paymentMethodName)) {
			return $this->getDefaultPaymentDisplayName();
		}
		else {
			return $paymentMethodName;
		}
	}

	/**
	 * @return string display name
	 */
	abstract protected function getDefaultPaymentDisplayName();

	/**
	 * @return array Settings data.
	 */
	abstract protected function getSettingsData();

	abstract protected function getInstallSettings();

	final public function getPaymentMethodConfigurationValue($key, $languageCode = null) {
		return $this->getSettingsApi()->getValue($key, $languageCode);
	}

	final public function existsPaymentMethodConfigurationValue($key, $languageCode = null) {
		return $this->getSettingsApi()->existsValue($key, $languageCode);
	}

	final protected function getSettingsApi() {
		if ($this->settingsApi === null) {
			$this->settingsApi = new UnzerCw_SettingApi($this->getSettingsData());
		}

		return $this->settingsApi;
	}

	public function getConfirmationPageHtml() {
		$orderContext = new UnzerCw_OrderContext_Session(new UnzerCw_PaymentMethodWrapper($this));
		$adapter = UnzerCw_Util::getCheckoutAdapterByContext($orderContext);
		$adapter->prepare($this, $orderContext);
		return $adapter->getConfirmationPageHtml();
	}

	public function getPaymentCode() {
		return get_class($this);
	}

	public function install() {

		$data = array(
			'payment_code' => $this->getPaymentCode(),
			'payment_dir' => 'unzercw',
			'payment_tpl' => 'payment_list.html',
			'en' => array(
				'title' => $this->getDefaultPaymentDisplayName(),
				'description' => '',
			),
		);

		$stores = $GLOBALS['store_handler']->getStores();

		$payment = new payment();
		$paymentId = $payment->checkInstall($this->getPaymentCode());
		if ($paymentId === false) {
			$paymentId = $payment->install($data, UnzerCw_Util::getPluginId());

			global $db;

			// Setup price rules. Otherwise the payment method is not shown in front end per default.
			// We execute this only when the payment method has not been installed.
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$paymentId.", 24, '', 0, 10000.00, 0, 1);");
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$paymentId.", 25, '', 0, 10000.00, 0, 1);");
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$paymentId.", 26, '', 0, 10000.00, 0, 1);");
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$paymentId.", 27, '', 0, 10000.00, 0, 1);");
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$paymentId.", 28, '', 0, 10000.00, 0, 1);");
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$paymentId.", 29, '', 0, 10000.00, 0, 1);");
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$paymentId.", 30, '', 0, 10000.00, 0, 1);");
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$paymentId.", 31, '', 0, 10000.00, 0, 1);");
		}

		$plugin = UnzerCw_Util::getPlugin();
		foreach ($this->getInstallSettings() as $key => $val) {
			$plugin->_addLangContentModule('unzercw', $val);
			foreach ($stores as $sdata) {
				$plugin->_addStoreConfig($val, $paymentId, $sdata['id'], 'payment');
			}
		}

	}

	public function uninstall() {
		// TODO:
	}


}

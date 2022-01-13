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

require_once 'Customweb/Core/ILogger.php';
require_once 'Customweb/Payment/Entity/AbstractTransaction.php';
require_once 'Customweb/Core/Logger/Factory.php';

require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/ConfigurationAdapter.php';
require_once 'UnzerCw/OrderStatus.php';


/**
 *
 * @Entity(tableName = 'unzercw_transactions')
 *
 */
class UnzerCw_Entity_Transaction extends Customweb_Payment_Entity_AbstractTransaction
{
	private $paymentMethod = null;

	private $lastSetOrderStatus = null;

	private $storeId = null;

	private $sessionData = array();

	private $sessionDataDeprecated = array();

	/**
	 * @return UnzerCw_AbstractPaymentMethod
	 */
	public function getPaymentMethod() {
		if ($this->paymentMethod === null) {
			$methodName = $this->getPaymentMachineName();
			if (!empty($methodName)) {
				$this->paymentMethod = UnzerCw_Util::getPaymentMethodInstanceByName($this->getPaymentMachineName());
			}
		}

		return $this->paymentMethod;
	}

	public function onBeforeSave(Customweb_Database_Entity_IManager $entityManager){
		if($this->isSkipOnSafeMethods()){
			return;
		}
		parent::onBeforeSave($entityManager);

		// When the authorization has failed we mark the order as well as failed.
		// This way the merchant can identify the failed orders easily. Additionally
		// we re-stock the products when the order is marked as failed. This happens
		// when the order status is changed to failed status. This way the merchant
		// can trigger the re-stocking also manually without requiring to mark the transaction
		// as failed.
		if ($this->getAuthorizationStatus() == 'failed') {
			$order = new order($this->getOrderId(), $this->getCustomerId());
			$orderData = $order->_getOrderData($this->getOrderId());
			$failedOrderStatus = UnzerCw_OrderStatus::getFailedStatusId();
			if (isset($orderData['orders_status_id']) && $orderData['orders_status_id'] != $failedOrderStatus) {
				$order->_updateOrderStatus($failedOrderStatus, '' , 'false', 'false', 'IPN');
			}
		}

		$this->sessionDataDeprecated = array();
	}



	public function onAfterLoad(Customweb_Database_Entity_IManager $entityManager) {
		parent::onAfterLoad($entityManager);
		UnzerCw_ConfigurationAdapter::setCurrentStoreId($this->getStoreId());
	}

	/**
	 * @Column(type = 'varchar')
	 */
	public function getStoreId(){
		return $this->storeId;
	}

	public function setStoreId($storeId){
		$this->storeId = $storeId;
		return $this;
	}

	/**
	 * @Column(type = 'varchar')
	 */
	public function getLastSetOrderStatus(){
		return $this->lastSetOrderStatus;
	}


	/**
	 * @Column(type = 'binaryObject', name='sessionDataBinary')
	 *
	 * @return array
	 */
	public function getSessionData(){
		return $this->sessionData;
	}

	public function setSessionData($data){
		$this->sessionData = $data;
		return $this;
	}

	/**
	 * @Column(type = 'object', name='sessionData')
	 *
	 * @return array
	 */
	public function getSessionDataDeprecated(){

		return $this->sessionDataDeprecated;
	}

	public function setSessionDataDeprecated($data){
		if(!empty($data)){
			$this->sessionData = $data;
		}
		$this->sessionDataDeprecated = $data;
		return $this;
	}




	public function initSessionData() {
		// We need eventually to store some session data, when xt_coupon is enabled.
		if (is_array($_SESSION['sess_coupon'])) {
			$data = array();
			$data['sess_coupon'] = $_SESSION['sess_coupon'];
			$data['cart'] = $_SESSION['cart'];
			$this->setSessionData($data);
		}
	}

	public function setLastSetOrderStatus($lastSetOrderStatus){
		$this->lastSetOrderStatus = $lastSetOrderStatus;
		return $this;
	}

	protected function updateOrderStatus(Customweb_Database_Entity_IManager $entityManager, $currentStatus, $orderStatusSettingKey) {
		$paymentId = '';
		if ($this->getTransactionObject() !== null && $this->getTransactionObject()->getPaymentId() !== null) {
			$paymentId = $this->getTransactionObject()->getPaymentId();
		}

		$callback_id = $this->getTransactionId();
		if (!empty($paymentId)) {
			$callback_id .= '-' . $paymentId;
		}

		$resolvedStatusId = $this->convertOrderStatus($currentStatus);

		$sendMail = 'false';
		if (UnzerCw_OrderStatus::isSendMailRequiredOnStatus($resolvedStatusId)) {
			$sendMail = 'true';
		}

		$this->forceLanguage();
		$order = new order($this->getOrderId(), $this->getCustomerId());
		$order->_updateOrderStatus($resolvedStatusId, '' , $sendMail, 'true', 'IPN', $callback_id);
		$this->resetLanguage();
	}

	protected function authorize(Customweb_Database_Entity_IManager $entityManager) {
		global $xtPlugin;
		if ($this->getTransactionObject() !== null) {
			$transactionObject = $this->getTransactionObject();
			if ($transactionObject->isAuthorized()) {
				$_SESSION['customer'] = new customer($this->getCustomerId());
				$_SESSION['success_order_id'] = $this->getOrderId();

				$this->forceLanguage();

				// Bug in xtc 4.0.16 (store id is not set)
				if (isset($GLOBALS['store_handler'])) {
					$backupStoreId = $GLOBALS['store_handler']->shop_id;
					$GLOBALS['store_handler']->shop_id = $this->getStoreId();
				}

				$order = new order($this->getOrderId(), $this->getCustomerId());

				// We need to call this hook to make sure that xt_coupons is redeem the coupon
				$filePathXtCoupons = _SRV_WEBROOT . 'plugins/xt_coupons/hooks/module_checkout.phppayment_proccess_bottom.php';
				if (file_exists($filePathXtCoupons)) {

					$sessionData = $this->getSessionData();
					if (isset($sessionData['sess_coupon'])) {
						$db = $GLOBALS['db'];
						$_SESSION['sess_coupon'] = $sessionData['sess_coupon'];
						$_SESSION['cart'] = $sessionData['cart'];
						require_once $filePathXtCoupons;
					}
				}

				$order->_sendOrderMail($this->getOrderId());

				// Bug in xtc 4.0.16 (store id is not set)
				if (isset($GLOBALS['store_handler'])) {
					$GLOBALS['store_handler']->shop_id = $backupStoreId;
				}

				$this->resetLanguage();

				($plugin_code = $xtPlugin->PluginCode(__CLASS__.':authorize')) ? eval($plugin_code) : false;
			}
		}
		else {
			Customweb_Core_Logger_Factory::getLogger(get_class($this))->log(Customweb_Core_ILogger::LEVEL_ERROR, 'Try to authorize transaction, which has no transaction object.');
		}
	}

	protected function generateExternalTransactionId(Customweb_Database_Entity_IManager $entityManager) {
		return $this->generateExternalTransactionIdAlwaysAppend($entityManager);
	}

	private function forceLanguage() {
		if ($this->getTransactionObject() !== null && $this->getTransactionObject()->getTransactionContext() !== null) {
			$GLOBALS['unzercw_force_email_language'] = $this->getTransactionObject()->getTransactionContext()->getOrderContext()->getLanguage();
		}
	}

	private function resetLanguage() {
		unset($GLOBALS['unzercw_force_email_language']);
	}

	/**
	 * @return number
	 */
	private function convertOrderStatus($status) {
		if (preg_match('/^[0-9]+$/', $status)) {
			return (int)$status;
		}
		else {
			return (int)UnzerCw_OrderStatus::getStatusIdByIdentifier($status);
		}
	}


}

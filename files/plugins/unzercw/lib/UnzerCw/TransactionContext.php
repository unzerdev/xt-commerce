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

require_once 'Customweb/Payment/Authorization/Hidden/ITransactionContext.php';
require_once 'Customweb/Payment/Authorization/Server/ITransactionContext.php';
require_once 'Customweb/Payment/Authorization/Ajax/ITransactionContext.php';
require_once 'Customweb/Payment/Authorization/PaymentPage/ITransactionContext.php';
require_once 'Customweb/Payment/Authorization/Moto/ITransactionContext.php';
require_once 'Customweb/Payment/Authorization/Iframe/ITransactionContext.php';
require_once 'Customweb/Payment/Authorization/Widget/ITransactionContext.php';

require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/Backend/Controller/Order.php';
require_once 'UnzerCw/Entity/Transaction.php';


class UnzerCw_TransactionContext implements Customweb_Payment_Authorization_PaymentPage_ITransactionContext,
Customweb_Payment_Authorization_Hidden_ITransactionContext, Customweb_Payment_Authorization_Server_ITransactionContext,
Customweb_Payment_Authorization_Iframe_ITransactionContext, Customweb_Payment_Authorization_Ajax_ITransactionContext,
Customweb_Payment_Authorization_Widget_ITransactionContext, Customweb_Payment_Authorization_Moto_ITransactionContext
{
	private $aliasTransactionId = NULL;
	private $paymentCustomerContext = null;
	private $orderContext;
	private $databaseTransactionId = NULL;
	private $customerId = NULL;
	private $databaseTransaction = NULL;

	private $backendSuccessUrl = NULL;
	private $backendFailedUrl = NULL;

	private $backupLanguage = null;

	private $successUrl = null;
	private $failedUrl = null;
	private $notificationUrl = null;
	private $iframeBreakoutUrl = null;

	public function __construct(UnzerCw_OrderContext_Order $orderContext, $aliasTransactionId = NULL) {

		$entityManager = UnzerCw_Util::getEntityManager();

		

		$this->backendFailedUrl =  UnzerCw_Backend_Controller_Order::getControllerUrl('order', 'motoFailed');
		$this->backendSuccessUrl =  UnzerCw_Backend_Controller_Order::getControllerUrl('order', 'motoSuccess');;


		$this->databaseTransaction = new UnzerCw_Entity_Transaction();
		$this->databaseTransaction->setStoreId($orderContext->getStoreId());
		$this->databaseTransaction->setOrderId($orderContext->getOrderId());
		$this->databaseTransaction->initSessionData();
		$entityManager->persist($this->databaseTransaction);
		$this->databaseTransactionId = $this->databaseTransaction->getTransactionId();

		$this->orderContext = $orderContext;

		$this->successUrl = UnzerCw_Util::getControllerUrl('process', 'presuccess', array(), true);
		$this->failedUrl = UnzerCw_Util::getControllerUrl('process', 'failed', array(), true);
		$this->notificationUrl =  UnzerCw_Util::getControllerUrl('process', 'notify', array(), true);
		$this->iframeBreakoutUrl = UnzerCw_Util::getControllerUrl('process', 'iframeBreakout', array(), true);

	}

	public function getSuccessUrl() {
		return $this->successUrl;
	}

	public function getFailedUrl() {
		return $this->failedUrl;
	}

	public function getNotificationUrl() {
		return $this->notificationUrl;
	}

	public function getIframeBreakOutUrl() {
		return $this->iframeBreakoutUrl;
	}

	/**
	 * @return UnzerCw_Entity_Transaction
	 */
	public function getDatabaseTransaction() {
		if ($this->databaseTransaction === NULL) {
			$this->databaseTransaction = UnzerCw_Util::loadTransaction($this->databaseTransactionId);
		}

		return $this->databaseTransaction;
	}

	public function getDatabaseTransactionId() {
		return $this->databaseTransactionId;
	}

	public function getCapturingMode() {
		return null;
	}

	public function getJavaScriptSuccessCallbackFunction() {
		return '
		function (redirectUrl) {
			window.location = redirectUrl
		}';
	}

	public function getJavaScriptFailedCallbackFunction() {
		return '
		function (redirectUrl) {
			window.location = redirectUrl
		}';
	}

	public function __sleep() {
		return array('aliasTransactionId', 'orderContext', 'databaseTransactionId', 'backendSuccessUrl', 'backendFailedUrl', 'iframeBreakoutUrl', 'successUrl', 'failedUrl', 'notificationUrl',);
	}

	public function getOrderContext() {
		return $this->orderContext;
	}

	public function getOrderId() {
		return $this->getDatabaseTransaction()->getOrderId();
	}

	public function isOrderIdUnique() {
		return true;
	}

	public function getTransactionId() {
		return $this->getDatabaseTransaction()->getTransactionId();
	}

	public function createRecurringAlias() {
		return false;
	}

	public function getAlias() {
		
		return null;
	}

	public function getCustomParameters() {
		return array(
			'cw_transaction_id' => $this->getDatabaseTransactionId(),
		);
	}

	public function getBackendSuccessUrl() {
		return $this->backendSuccessUrl;
	}

	public function getBackendFailedUrl() {
		return $this->backendFailedUrl;
	}

	public function getPaymentCustomerContext() {
		return UnzerCw_Util::getPaymentCustomerContext($this->customerId);
	}

	private function setLanguage() {
		$this->backupLanguage = $GLOBALS['language']->code;
		$GLOBALS['language']->_getLanguage($this->getOrderContext()->getLanguage());
	}

	private function resetLanguage() {
		if ($this->backupLanguage !== null) {
			$GLOBALS['language']->_getLanguage($this->backupLanguage);
		}
	}

}
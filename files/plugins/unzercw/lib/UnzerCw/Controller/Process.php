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

require_once 'Customweb/Util/System.php';
require_once 'Customweb/Util/Url.php';
require_once 'Customweb/Payment/Authorization/Server/IAdapter.php';
require_once 'Customweb/Payment/Authorization/IErrorMessage.php';
require_once 'Customweb/Payment/Authorization/PaymentPage/IAdapter.php';
require_once 'Customweb/Core/ILogger.php';
require_once 'Customweb/Util/Html.php';
require_once 'Customweb/Core/Http/Response.php';
require_once 'Customweb/Core/Exception/CastException.php';
require_once 'Customweb/Core/Http/ContextRequest.php';
require_once 'Customweb/Payment/Endpoint/Dispatcher.php';
require_once 'Customweb/Payment/Authorization/Moto/IAdapter.php';
require_once 'Customweb/Core/Logger/Factory.php';

require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/FormDataManager.php';
require_once 'UnzerCw/Adapter/IframeAdapter.php';
require_once 'UnzerCw/Language.php';
require_once 'UnzerCw/Adapter/WidgetAdapter.php';
require_once 'UnzerCw/Controller/Abstract.php';



class UnzerCw_Controller_Process extends UnzerCw_Controller_Abstract {


	public function notifyAction() {
		$dispatcher = new Customweb_Payment_Endpoint_Dispatcher(UnzerCw_Util::getEndpointAdapter(), UnzerCw_Util::createContainer(), array(
			0 => 'Customweb_Unzer',
 			1 => 'Customweb_Payment_Authorization',
 		));
		$response = $dispatcher->invokeControllerAction(Customweb_Core_Http_ContextRequest::getInstance(), 'process', 'index');
		$response = new Customweb_Core_Http_Response($response);
		$response->send();
	}


	public function iframeAction() {
		$transaction = $this->getTransactionFromRequest();

		if ($transaction->getTransactionObject()->isAuthorizationFailed() || $transaction->getTransactionObject()->isAuthorized()) {
			if ($transaction->getTransactionObject()->isAuthorizationFailed()) {
				$redirectionUrl = Customweb_Util_Url::appendParameters(
						$transaction->getTransactionObject()->getTransactionContext()->getFailedUrl(),
						$transaction->getTransactionObject()->getTransactionContext()->getCustomParameters()
						);
			}
			else if($transaction->getTransactionObject()->isAuthorized()) {
				$redirectionUrl = Customweb_Util_Url::appendParameters(
						$transaction->getTransactionObject()->getTransactionContext()->getSuccessUrl(),
						$transaction->getTransactionObject()->getTransactionContext()->getCustomParameters()
						);
			}
			header("Location: " . $redirectionUrl);
			die();
		}

		$adapter = UnzerCw_Util::getCheckoutAdapterByAuthorizationMethod($transaction->getAuthorizationType());
		$adapter->prepare($transaction->getPaymentMethod(), $transaction->getTransactionObject()->getTransactionContext()->getOrderContext(), null, null, $transaction);

		if (!($adapter instanceof UnzerCw_Adapter_IframeAdapter)) {
			throw new Exception("Only supported for widget authorization.");
		}

		$this->assign('iframe', $adapter->getIframe());
		$this->assign('paymentMethodName', $transaction->getTransactionObject()->getPaymentMethod()->getPaymentMethodDisplayName());
		$this->display('iframe.html');
	}

	public function widgetAction() {
		$transaction = $this->getTransactionFromRequest();

		if ($transaction->getTransactionObject()->isAuthorizationFailed() || $transaction->getTransactionObject()->isAuthorized()) {
			if ($transaction->getTransactionObject()->isAuthorizationFailed()) {
				$redirectionUrl = Customweb_Util_Url::appendParameters(
						$transaction->getTransactionObject()->getTransactionContext()->getFailedUrl(),
						$transaction->getTransactionObject()->getTransactionContext()->getCustomParameters()
						);
			}
			else if($transaction->getTransactionObject()->isAuthorized()) {
				$redirectionUrl = Customweb_Util_Url::appendParameters(
						$transaction->getTransactionObject()->getTransactionContext()->getSuccessUrl(),
						$transaction->getTransactionObject()->getTransactionContext()->getCustomParameters()
						);
			}
			header("Location: " . $redirectionUrl);
			die();
		}

		$adapter = UnzerCw_Util::getCheckoutAdapterByAuthorizationMethod($transaction->getAuthorizationType());
		$adapter->prepare($transaction->getPaymentMethod(), $transaction->getTransactionObject()->getTransactionContext()->getOrderContext(), null, null, $transaction);

		if (!($adapter instanceof UnzerCw_Adapter_WidgetAdapter)) {
			throw new Exception("Only supported for widget authorization.");
		}

		$this->assign('widget', $adapter->getWidget());
		$this->assign('paymentMethodName', $transaction->getTransactionObject()->getPaymentMethod()->getPaymentMethodDisplayName());
		$this->display('widget.html');
	}

	public function iframeBreakoutAction() {

		$transaction = $this->getTransactionFromRequest();

		$redirectionUrl = '';
		if ($transaction->getTransactionObject()->isAuthorizationFailed()) {
			$redirectionUrl = Customweb_Util_Url::appendParameters(
				$transaction->getTransactionObject()->getTransactionContext()->getFailedUrl(),
				$transaction->getTransactionObject()->getTransactionContext()->getCustomParameters()
			);
		}
		else {
			$redirectionUrl = Customweb_Util_Url::appendParameters(
				$transaction->getTransactionObject()->getTransactionContext()->getSuccessUrl(),
				$transaction->getTransactionObject()->getTransactionContext()->getCustomParameters()
			);
		}

		$this->assign('breakoutUrl', $redirectionUrl);
		$this->assign('continue', UnzerCw_Language::_('Continue'));

		echo $this->display('iframe-breakout.html');
		die();
	}

	public function serverAuthorizationAction() {
		$transaction = $this->getTransactionFromRequest();

		$adapter = UnzerCw_Util::getAuthorizationAdapterByMethod($transaction->getAuthorizationType());

		if (!($adapter instanceof Customweb_Payment_Authorization_Server_IAdapter)) {
			throw new Customweb_Core_Exception_CastException('Customweb_Payment_Authorization_Server_IAdapter');
		}
		$params = UnzerCw_Util::getCleanRequestArray();
		$transactionObject = $transaction->getTransactionObject();
		$response = $adapter->processAuthorization($transactionObject, $params);

		UnzerCw_Util::getTransactionHandler()->persistTransactionObject($transactionObject);

		$response = new Customweb_Core_Http_Response($response);
		$response->send();
		die();
	}

	public function paymentAction() {

		$transaction = null;
		$failedTransaction = null;
		$errorMessage = '';
		$order = null;
		$paymentMethod = null;


		if (isset($_REQUEST['failed_transaction_id'])) {
			$failedTransaction = UnzerCw_Util::loadTransaction($_REQUEST['failed_transaction_id']);

			$customerId = $failedTransaction->getCustomerId();
			if ((!isset($_SESSION['customer']) || $customerId === null || $_SESSION['customer']->customer_info['customers_id'] !== $customerId) &&
				$failedTransaction->getAuthorizationType() !== Customweb_Payment_Authorization_Moto_IAdapter::AUTHORIZATION_METHOD_NAME) {
				die("You are not allow use this transaction.");
			}
		}

		if ($failedTransaction !== null) {
			$errorMessage = current($failedTransaction->getTransactionObject()->getErrorMessages());
			if ($errorMessage instanceof Customweb_Payment_Authorization_IErrorMessage) {
				$errorMessage = $errorMessage->getUserMessage();
			}
			if (empty($errorMessage)) {
				$errorMessage = UnzerCw_Language::_("Unknown error.");
			}

			$adapter = UnzerCw_Util::getCheckoutAdapterByAuthorizationMethod($failedTransaction->getAuthorizationType());
			$paymentMethod = $failedTransaction->getPaymentMethod();
			$adapter->prepare(
				$paymentMethod,
				$failedTransaction->getTransactionObject()->getTransactionContext()->getOrderContext(),
				$failedTransaction->getOrderId(),
				$failedTransaction
			);
			$transaction = $adapter->getTransaction();

			$this->assign('errorMessage', $errorMessage);
		}

		if ($transaction === null) {
			$transaction = $this->getTransactionFromRequest();
			$paymentMethod = $transaction->getPaymentMethod();
			$authorizationMethod = $transaction->getAuthorizationType();
			$adapter = UnzerCw_Util::getCheckoutAdapterByAuthorizationMethod($authorizationMethod);
			$adapter->prepare($paymentMethod, $transaction->getTransactionObject()->getTransactionContext()->getOrderContext(), null, null, $transaction);
		}

		$this->assign('paymentPage', $adapter->getPaymentPageHtml());
		$this->assign('paymentMethodName', $paymentMethod->getPaymentMethodDisplayName());

		$this->display('payment.html');
	}

	public function failedAction() {
		$this->getTransactionFromRequest();
		$url = UnzerCw_Util::getFrontendUrl('checkout', array('page_action' => 'payment', 'unzercwfailed_transaction_id' => $_REQUEST['cw_transaction_id']));
		header('Location: ' . $url);

		// We reset here the cart. Any invocation of this page should not modify the cart
		// in any way. As such we restore the cart here from the copy when such exists.
		// We need this to avoid that the products are removed from the cart when they
		// are not anymore in the stock.
		if (isset($GLOBALS['originalCart'])) {
			$_SESSION['cart'] = $GLOBALS['originalCart'];
		}

		die();
	}

	/**
	 * This action is invoked before the success controller is invoked. We do this
	 * to avoid showing an error message about too few items left in the stock. This
	 * happens when the order contains items which are not available anymore. By adding
	 * an additional redirect we make sure that the cart in the session gets fixed
	 * before we showing a page which actually can render the error messages.
	 */
	public function presuccessAction() {
		if (!isset($_REQUEST['cw_transaction_id'])) {
			die('No transaction ID provided in the request.');
		}

		$url = UnzerCw_Util::getControllerUrl('process', 'success', array('cw_transaction_id' => $_REQUEST['cw_transaction_id']), true);
		header('Location: ' . $url);
		die();
	}

	public function successAction() {

		$this->getTransactionFromRequest();

		$start = time();
		$maxExecutionTime = Customweb_Util_System::getMaxExecutionTime() - 5;

		// We limit the timeout in case the server has set a very high timeout.
		if ($maxExecutionTime > 30) {
			$maxExecutionTime = 30;
		}

		$transactionId = $_REQUEST['cw_transaction_id'];
		$entityManager =  UnzerCw_Util::getEntityManager();

		// Wait as long as the notification is done in the background
		while (true) {
			$transaction = $entityManager->fetch('UnzerCw_Entity_Transaction', $transactionId, false);
			$transactionObject = $transaction->getTransactionObject();

			$url = null;
			if ($transactionObject->isAuthorizationFailed()) {
				$url = $this->getUrl(array('cw_transaction_id' => $transactionId), 'failed', 'process');
			}
			else if ($transactionObject->isAuthorized()) {
				global $xtLink;
				$_SESSION['success_order_id'] = $transaction->getOrderId();
				$url = $xtLink->_link(array('page'=>'checkout', 'paction'=>'success', 'conn'=>'SSL'));
				unset($_SESSION['last_order_id']);
				unset($_SESSION['selected_shipping']);
				unset($_SESSION['selected_payment']);
				$_SESSION['cart']->_resetCart();
				$formHandler = new UnzerCw_FormDataManager($transactionObject->getTransactionContext()->getOrderContext());
				$formHandler->reset();
			}

			if (time() - $start > $maxExecutionTime) {
				// We run into a timeout. Write a log entry and show the customer a description of the actual situation.
				$message = "The transaction takes too long for processing. May be the callback was not successful in the background. Transaction id: " . (int)$transactionId;
				Customweb_Core_Logger_Factory::getLogger(get_class($this))->log(Customweb_Core_ILogger::LEVEL_ERROR, $message);

				$this->assign("paymentMethodName", $transaction->getTransactionObject()->getPaymentMethod()->getPaymentMethodDisplayName());
				$this->assign("message", UnzerCw_Language::_(
					"Your payment seems to be accepted. However the payment could not be processed correctly with in the given time.
					Please contact us to figure out what happends with your order. As reference use please the transaction id '!transactionId'.", array('!transactionId' => $transactionId)));
				return $this->display('timeout.html');
			}

			else if ($url !== null) {
				header('Location: ' . $url);
				die();
			}
			else {
				// Wait 2 seconds for the next try.
				sleep(2);
			}
		}
	}

	public function redirectionAction() {

		$transaction = $this->getTransactionFromRequest();
		$adapter = UnzerCw_Util::getAuthorizationAdapterByMethod($transaction->getAuthorizationType());

		if (!($adapter instanceof Customweb_Payment_Authorization_PaymentPage_IAdapter)) {
			throw new Exception("Redirection is only supported for payment page authorization.");
		}

		$params = UnzerCw_Util::getCleanRequestArray();
		if (count($params) <= 0) {
			$formHandler = new UnzerCw_FormDataManager($transaction->getTransactionObject()->getTransactionContext()->getOrderContext());
			$params = $formHandler->getFormData();
		}

		$headerRedirection = $adapter->isHeaderRedirectionSupported($transaction->getTransactionObject(), $params);

		if ($headerRedirection) {
			$url = $adapter->getRedirectionUrl($transaction->getTransactionObject(), $params);
			UnzerCw_Util::getEntityManager()->persist($transaction);
			header('Location: ' . $url);
			die();
		}
		else {
			$this->assign('paymentMethodName', $transaction->getTransactionObject()->getPaymentMethod()->getPaymentMethodDisplayName());
			$this->assign('formTargetUrl', $adapter->getFormActionUrl($transaction->getTransactionObject(), $params));
			$this->assign('hiddenFields', Customweb_Util_Html::buildHiddenInputFields($adapter->getParameters($transaction->getTransactionObject(), $params)));
			$this->assign('buttonContinue', UnzerCw_Language::_("Continue"));
			UnzerCw_Util::getEntityManager()->persist($transaction);
			$this->display('redirection.html');
		}
	}

	/**
	 * @throws Exception
	 * @return UnzerCw_Entity_Transaction
	 */
	private function getTransactionFromRequest() {
		if (!isset($_REQUEST['cw_transaction_id'])) {
			throw new Exception("No transaction id given.");
		}

		$transaction = UnzerCw_Util::loadTransaction($_REQUEST['cw_transaction_id']);

		if ($transaction === null) {
			throw new Exception("No transaction found for the ID: " . $_REQUEST['cw_transaction_id']);
		}

		return $transaction;
	}

}





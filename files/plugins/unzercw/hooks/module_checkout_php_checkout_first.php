<?php

// This hook is executed on every checkout page.
$currentPaymentMethod = $_SESSION['selected_payment'];
if ($page->page_action == 'payment' && isset($_REQUEST['selected_payment'])) {
	$currentPaymentMethod = $_REQUEST['selected_payment'];
}


if (strpos($currentPaymentMethod, 'cw_UNZ') === 0 || isset($_GET['unzercwfailed_transaction_id'])) {
	require _SRV_WEBROOT . 'plugins/unzercw/init.php';
	require_once 'Customweb/Payment/Authorization/IErrorMessage.php';
require_once 'Customweb/Core/ILogger.php';
require_once 'Customweb/Payment/Authorization/Moto/IAdapter.php';
require_once 'Customweb/Core/Logger/Factory.php';

	require_once 'UnzerCw/Language.php';
require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/OrderContext/Session.php';
require_once 'UnzerCw/AliasManager.php';
require_once 'UnzerCw/PaymentMethodWrapper.php';

}


if (strpos($currentPaymentMethod, 'cw_UNZ') === 0) {


	$paymentMethod = UnzerCw_Util::getPaymentMethodInstanceByCode($currentPaymentMethod);
	$orderContext = new UnzerCw_OrderContext_Session(new UnzerCw_PaymentMethodWrapper($paymentMethod));
	$adapter = UnzerCw_Util::getAuthorizationAdapterByContext($orderContext);

	$aliasTransaction = null;
	


	if ($page->page_action == 'confirmation') {
		$paymentContext = UnzerCw_Util::getPaymentCustomerContext($orderContext->getCustomerId());
		$adapter = UnzerCw_Util::getAuthorizationAdapterByContext($orderContext);
		$isValidationPossible = true;
		if (method_exists($adapter, 'getVisibleFormFields')) {
			$formElements = $adapter->getVisibleFormFields($orderContext, $aliasTransaction, null, $paymentContext);
			if (count($formElements) > 0) {
				$isValidationPossible = false;
			}
		}
		if ($isValidationPossible) {
			try {
				$adapter->validate($orderContext, $paymentContext, array());
			}
			catch (Exception $e) {
				$GLOBALS['info']->_addInfo($e->getMessage());
				$page->page_action = 'payment';
				$_POST['selected_payment'] = $currentPaymentMethod;
				Customweb_Core_Logger_Factory::getLogger('Validate UnzerCw_')->log(Customweb_Core_ILogger::LEVEL_INFO, $e->getMessage());
			}
			UnzerCw_Util::persistPaymentCustomerContext($paymentContext);
		}
	}
}

if (isset($_GET['unzercwfailed_transaction_id'])) {
	$transaction = UnzerCw_Util::loadTransaction((int) $_GET['unzercwfailed_transaction_id']);

	if ($transaction->getAuthorizationType() !== Customweb_Payment_Authorization_Moto_IAdapter::AUTHORIZATION_METHOD_NAME) {
		$errorMessage = current($transaction->getTransactionObject()->getErrorMessages());
		if ($errorMessage instanceof Customweb_Payment_Authorization_IErrorMessage) {
			$errorMessage = $errorMessage->getUserMessage();
		}
		if (empty($errorMessage)) {
			$errorMessage = UnzerCw_Language::_("Unknown error.");
		}
		$GLOBALS['info']->_addInfo($errorMessage);
	}

}


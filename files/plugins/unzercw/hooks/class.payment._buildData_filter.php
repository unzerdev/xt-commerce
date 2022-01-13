<?php

// This hook is executed before the payment method is shown.

// We filter only when the payment code is one of our payment method and it is not in the backend. Additionally we do not filter on the confirmation page
// again except the payment method is not set. This is the case when the user does not make any decision (no payment method available).
if (strpos($value['payment_code'], 'cw_UNZ') === 0 && stristr($_SERVER['SCRIPT_NAME'], 'adminHandler.php') === false
		&& ($GLOBALS['page']->page_action != 'confirmation' || empty($_SESSION['selected_payment'])) && isset($_SESSION['cart']) && $_SESSION['cart'] !== null
		&& $_SESSION['cart'] instanceof cart) {

	require _SRV_WEBROOT . 'plugins/unzercw/init.php';
	require_once 'Customweb/Core/ILogger.php';
require_once 'Customweb/Core/Logger/Factory.php';

	require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/OrderContext/Session.php';
require_once 'UnzerCw/PaymentMethodWrapper.php';


	try {
		$paymentMethod = UnzerCw_Util::getPaymentMethodInstanceByCode($value['payment_code']);

		$orderContext = new UnzerCw_OrderContext_Session(new UnzerCw_PaymentMethodWrapper($paymentMethod));
		$paymentContext = UnzerCw_Util::getPaymentCustomerContext($orderContext->getCustomerId());
		$adapter = UnzerCw_Util::getAuthorizationAdapterByContext($orderContext);
		try {
			$adapter->preValidate($orderContext, $paymentContext);
		}
		catch(Exception $e) {
			$value = null;
			Customweb_Core_Logger_Factory::getLogger('PreValidate UnzerCw_')->log(Customweb_Core_ILogger::LEVEL_INFO, $e->getMessage());
		}
		UnzerCw_Util::persistPaymentCustomerContext($paymentContext);
	}
	catch(Exception $e) {
		die($e->getMessage());
	}
}
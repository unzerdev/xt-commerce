<?php
if (isset($GLOBALS['unzercw-success_order_id'])) {
	try {
		require_once _SRV_WEBROOT . 'plugins/unzercw/init.php';
		require_once 'UnzerCw/Util.php';
		require_once 'UnzerCw/Entity/Transaction.php';
		require_once 'Customweb/Core/Util/Html.php';
		
		$entityManager = UnzerCw_Util::getEntityManager();
		$entities = $entityManager->search('UnzerCw_Entity_Transaction', '`orderId` = >orderId AND `authorizationStatus` = "successful"', 
				'', array(
					'>orderId' => $GLOBALS['unzercw-success_order_id'] 
				));
		if (count($entities) == 1) {
			$transaction = current($entities);
			$transactionObject = $transaction->getTransactionObject();
			
			$templateData = array(
				'paymentInformation' => (string) $transactionObject->getPaymentInformation() 
			);
			
			$templateName = 'payment_information.html';
			$template = new Template();
			$template->getTemplatePath($templateName, 'unzercw', 'checkout', 'plugin');
			echo ($template->getTemplate('', $templateName, $templateData));
		}
	}
	catch (Exception $exc) {
	}
}
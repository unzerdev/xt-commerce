<?php
try {
	require_once _SRV_WEBROOT . 'plugins/unzercw/init.php';
	require_once 'UnzerCw/Util.php';
	require_once 'UnzerCw/Entity/Transaction.php';
	require_once 'Customweb/Core/Util/Html.php';

	$entityManager = UnzerCw_Util::getEntityManager();
	$entities = $entityManager->search('UnzerCw_Entity_Transaction', '`orderId` = >orderId AND (`authorizationStatus` = "authorizing" OR `authorizationStatus` = "successful")',
			"`transactionId` DESC", array(
				'>orderId' => $this->oID
			));
	if(count($entities) > 0){
		$transaction = current($entities);
		$transactionObject = $transaction->getTransactionObject();


		$smarty = $ordermail->Template;
		$paymentInfoHtml = '';
		$paymentInfoText = '';
		if (method_exists($smarty, 'get_template_vars')) {
			$paymentInfoText = $smarty->get_template_vars('payment_info_txt');
			if (empty($paymentInfoText)) {
				$paymentInfoText = $smarty->get_template_vars('payment_info');
			}
			$paymentInfoHtml = $smarty->get_template_vars('payment_info_html');
		}
		else {
			$paymentInfoText = $smarty->getTemplateVars('payment_info_txt');
			if (empty($paymentInfoText)) {
				$paymentInfoText = $smarty->getTemplateVars('payment_info');
			}
			$paymentInfoHtml = $smarty->getTemplateVars('payment_info_html');
		}

		$paymentInfoHtml .= $transactionObject->getPaymentInformation();
		$paymentInfoText .= Customweb_Core_Util_Html::toText($transactionObject->getPaymentInformation());

		// old payment info
		$ordermail->_assign('payment_info', $paymentInfoText);

		// new payment info
		$ordermail->_assign('payment_info_html', $paymentInfoHtml);
		$ordermail->_assign('payment_info_txt', $paymentInfoText);
	}
}
catch (Exception $exc) {
}
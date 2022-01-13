<?php

// When the last_order_id is set that means that there is still an order
// going on. We want to make sure we have only one order per transaction
// to make sure we handle the stock correctly. Therefore we have to unset
// the last_order_id when it is linked to a transaction which is related
// to our plugin. This way we can make sure that xt:Commerce is not re-
// using the old order and create a new one. This guarantees the order_id
// uniqueness per transaction and also that the stock is reduced when
// the customer reorders again.
// We do also not automatically kill the order / transaction because this
// should be done by the processor.
if (isset($_SESSION['last_order_id'])) {

	require _SRV_WEBROOT . 'plugins/unzercw/init.php';
	
	require_once 'UnzerCw/Util.php';


	$transactions = UnzerCw_Util::getEntityManager()->searchByFilterName(
			'UnzerCw_Entity_Transaction',
			'loadByOrderId',
				array(
					'>orderId' => $_SESSION['last_order_id'],
				)
			);
	// Normally we should get only one transaction.
	if (count($transactions) > 0) {
		unset($_SESSION['last_order_id']);
	}
}

// We make here a copy of the cart to have it later in a untouched version. We
// need the original because xt:Commerce refreshes the cart content which leads
// to removed products when they are not anymore in stock. We may restock on a failure
// and as such the cart would be eventually wrongly reset. We use the copy to restore
// the original state of the cart.
if (isset($_SESSION['cart'])) {
	$GLOBALS['originalCart'] = clone $_SESSION['cart'];
}
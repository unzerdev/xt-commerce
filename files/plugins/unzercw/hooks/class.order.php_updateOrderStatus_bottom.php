<?php

require _SRV_WEBROOT . 'plugins/unzercw/init.php';

require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/OrderStatus.php';


/* @var $this order */
/* @var $status string */

$failedStatusId = (int)UnzerCw_OrderStatus::getFailedStatusId();
if ($status === $failedStatusId) {

	// When the failed status is set on the order we trigger the re-stocking. This
	// guarantees that the products are available again for buying when the transaction
	// has failed or has been cancelled by the buyer.
	// We use this hook to trigger this to allow the merchant to trigger the restocking
	// manually by switching to the failed state.

	$driver = UnzerCw_Util::getDriver();
	$statement = $driver->query('SELECT products_id, products_quantity FROM ' . TABLE_ORDERS_PRODUCTS . ' WHERE orders_id = >orders_id');
	$statement->execute(array(
		'>orders_id' => $this->oID,
	));

	$stock = new stock();
	while (($row = $statement->fetch()) !== false) {
		$stock->addStock($row['products_id'], $row['products_quantity']);
	}
}

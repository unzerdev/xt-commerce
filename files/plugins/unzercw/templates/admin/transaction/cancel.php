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

/* @var $transaction UnzerCw_Entity_Transaction  */
/* @var $item Customweb_Payment_Authorization_IInvoiceItem */

?>


<?php if ($transaction->getTransactionObject()->isCancelPossible()):?>
	<h3><?php echo UnzerCw_Language::_('Cancel'); ?></h3>
	<form action="<?php echo $this->getActionUrl('cancel', array('transaction_id' => $transaction->getTransactionId())); ?>" method="POST" class="unzercw-line-item-grid" 
	id="cancelForm<?php echo $transaction->getTransactionId(); ?>">
		<input type="hidden" name="transaction_id" value="<?php echo $transaction->getTransactionId(); ?>" />
	</form>
<?php endif;?>


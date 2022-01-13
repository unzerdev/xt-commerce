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


<h3><?php echo UnzerCw_Language::_('Refund'); ?></h3>
<form action="<?php echo $this->getActionUrl('refund', array('transaction_id' => $transaction->getTransactionId())); ?>" method="POST" class="table table-striped  unzercw-line-item-grid" 
id="refundForm<?php echo $transaction->getTransactionId(); ?>">
	<input type="hidden" name="transaction_id" value="<?php echo $transaction->getTransactionId(); ?>" />
	<?php if ($transaction->getTransactionObject()->isPartialRefundPossible()):?>
	
		<input type="hidden" id="unzercw-decimal-places" value="<?php echo Customweb_Util_Currency::getDecimalPlaces($transaction->getTransactionObject()->getCurrencyCode()); ?>" />
		<input type="hidden" id="unzercw-currency-code" value="<?php echo strtoupper($transaction->getTransactionObject()->getCurrencyCode()); ?>" />
		<table class="table table-striped  unzercw-form-grid" width="100%" border="0" cellspacing="0" cellpadding="6">
			<thead>
				<tr>
					<th><?php echo UnzerCw_Language::_('Name'); ?></th>
					<th><?php echo UnzerCw_Language::_('SKU'); ?></th>
					<th><?php echo UnzerCw_Language::_('Type'); ?></th>
					<th><?php echo UnzerCw_Language::_('Tax Rate'); ?></th>
					<th><?php echo UnzerCw_Language::_('Quantity'); ?></th>
					<th><?php echo UnzerCw_Language::_('Total Amount (excl. Tax)'); ?></th>
					<th><?php echo UnzerCw_Language::_('Total Amount (incl. Tax)'); ?></th>
					</tr>
			</thead>
		
			<tbody>
			<?php foreach ($transaction->getTransactionObject()->getNonRefundedLineItems() as $index => $item):?>
				<?php 
					$amountExcludingTax = Customweb_Util_Currency::formatAmount($item->getAmountExcludingTax(), $transaction->getTransactionObject()->getCurrencyCode());
					$amountIncludingTax = Customweb_Util_Currency::formatAmount($item->getAmountIncludingTax(), $transaction->getTransactionObject()->getCurrencyCode());
					if ($item->getType() == Customweb_Payment_Authorization_IInvoiceItem::TYPE_DISCOUNT) {
						$amountExcludingTax = $amountExcludingTax * -1;
						$amountIncludingTax = $amountIncludingTax * -1;
					}
				?>
				
				<tr id="line-item-row-<?php echo $index ?>" class="line-item-row" data-line-item-index="<?php echo $index; ?>" >
					<td><?php echo $item->getName(); ?></td>
					<td><?php echo $item->getSku();?></td>
					<td><?php echo $item->getType(); ?></td>
					<td><?php echo $item->getTaxRate();?> %<input type="hidden" class="tax-rate" value="<?php echo $item->getTaxRate(); ?>" /></td>
					<td><input type="text" class="line-item-quantity" name="quantity[<?php echo $index;?>]" value="<?php echo $item->getQuantity(); ?>" /></td>
					<td><input type="text" class="line-item-price-excluding" name="price_excluding[<?php echo $index;?>]" value="<?php echo $amountExcludingTax; ?>" /></td>
					<td><input type="text" class="line-item-price-including" name="price_including[<?php echo $index;?>]" value="<?php echo $amountIncludingTax; ?>" /></td>
				</tr>
			<?php endforeach;?>
				<tr>
					<td colspan="6" class="right"><?php echo UnzerCw_Language::_('Total Refund Amount'); ?>:</td>
					<td id="line-item-total" class="right">
					<?php echo Customweb_Util_Currency::formatAmount($transaction->getTransactionObject()->getRefundableAmount(), $transaction->getTransactionObject()->getCurrencyCode()); ?> 
					<?php echo strtoupper($transaction->getTransactionObject()->getCurrencyCode());?>
				</tr>
			</tbody>
		</table>
		<?php if ($transaction->getTransactionObject()->isRefundClosable()):?>
			<div class="closable-box">
				<label for="close-transaction"><?php echo UnzerCw_Language::_('Close transaction for further refunds'); ?></label>
				<input id="close-transaction" type="checkbox" name="close" value="on" />
			</div>
		<?php endif;?>
	<?php endif;?>
</form>


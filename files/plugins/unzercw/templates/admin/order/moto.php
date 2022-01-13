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

/* @var $orderContext UnzerCw_OrderContext_Order  */
/* @var $item Customweb_Payment_Authorization_IInvoiceItem */
/* @var $transaction UnzerCw_Entity_Transaction */

?>


<h3><?php echo UnzerCw_Language::_('Mail-/Telephone Order'); ?></h3>
<p><?php echo UnzerCw_Language::_('Please select the items you want to charge. '); ?></p>
<form action="<?php echo $this->getActionUrl('moto', array('order_id' => $orderId)); ?>" method="POST" class="table table-striped unzercw-line-item-grid" 
id="motoForm<?php echo $orderId; ?>" target="_blank">

	<input type="hidden" id="unzercw-decimal-places" value="<?php echo Customweb_Util_Currency::getDecimalPlaces($orderContext->getCurrencyCode()); ?>" />
	<input type="hidden" id="unzercw-currency-code" value="<?php echo strtoupper($orderContext->getCurrencyCode()); ?>" />
	<table class="table table-striped unzercw-form-grid" width="100%" border="0" cellspacing="0" cellpadding="6">
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
		<?php foreach ($orderContext->getInvoiceItems() as $index => $item):?>
			<?php 
				$amountExcludingTax = Customweb_Util_Currency::formatAmount($item->getAmountExcludingTax(), $orderContext->getCurrencyCode());
				$amountIncludingTax = Customweb_Util_Currency::formatAmount($item->getAmountIncludingTax(), $orderContext->getCurrencyCode());
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
				<td colspan="6" class="right"><?php echo UnzerCw_Language::_('Total amount to charge'); ?>:</td>
				<td id="line-item-total" class="right">
				<?php echo Customweb_Util_Currency::formatAmount($orderContext->getOrderAmountInDecimals(), $orderContext->getCurrencyCode()); ?> 
				<?php echo strtoupper($orderContext->getCurrencyCode());?>
			</tr>
		</tbody>
	</table>
	
	
	
	<div id="motoFormPanel<?php echo $orderId; ?>"></div>
	
</form>

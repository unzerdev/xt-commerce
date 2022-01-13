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
/* @var $capture Customweb_Payment_Authorization_ITransactionCapture */
/* @var $item Customweb_Payment_Authorization_IInvoiceItem */
?>


<h3><?php echo UnzerCw_Language::_('Capture Data'); ?></h3>
<table class="table table-striped" width="100%">
	<tr>
		<td><?php echo UnzerCw_Language::_('Capture ID') ?></td>
		<td><?php echo $capture->getCaptureId(); ?></td>
	</tr>

	<tr>
		<td><?php echo UnzerCw_Language::_('Capture Date') ?></td>
		<td><?php echo $capture->getCaptureDate()->format('c'); ?></td>
	</tr>
	<tr>
		<td><?php echo UnzerCw_Language::_('Capture Amount') ?></td>
		<td><?php echo $capture->getAmount(); ?></td>
	</tr>
	<tr>
		<td><?php echo UnzerCw_Language::_('Status') ?></td>
		<td><?php echo $capture->getStatus(); ?></td>
	</tr>
	<?php foreach ($capture->getCaptureLabels() as $label): ?>
	<tr>
		<td><?php echo $label['label'];?> 
		<?php if (isset($label['description'])): ?>
			<img src="../xtAdmin/images/icons/information.png" alt="<?php echo $label['description']; ?>" title="<?php echo $label['description']; ?>" style="vertical-align:middle; cursor:help;"> 
		<?php endif; ?>
		</td>
		<td><?php echo $label['value'];?>
		</td>
	</tr>
	<?php endforeach;?>
</table>

			
<h3><?php echo UnzerCw_Language::_('Captured Items'); ?></h3>
<table class="unzercw-info-list table table-striped">
	<thead>
		<tr>
			<th><?php echo UnzerCw_Language::_('Name'); ?></th>
			<th><?php echo UnzerCw_Language::_('SKU'); ?></th>
			<th><?php echo UnzerCw_Language::_('Quantity'); ?></th>
			<th><?php echo UnzerCw_Language::_('Tax Rate'); ?></th>
			<th><?php echo UnzerCw_Language::_('Total Amount (excl. Tax)'); ?></th>
			<th><?php echo UnzerCw_Language::_('Total Amount (incl. Tax)'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($capture->getCaptureItems() as $item):?>
			<tr>
				<td><?php echo $item->getName(); ?></td>
				<td><?php echo $item->getSku(); ?></td>
				<td><?php echo $item->getQuantity(); ?></td>
				<td><?php echo $item->getTaxRate(); ?></td>
				<td><?php echo $item->getAmountExcludingTax(); ?></td>
				<td><?php echo $item->getAmountIncludingTax(); ?></td>
			</tr>
		<?php endforeach;?>
	</tbody>
</table>

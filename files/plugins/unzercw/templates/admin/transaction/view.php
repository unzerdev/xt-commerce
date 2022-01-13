<?php 

/* @var $transaction UnzerCw_Entity_Transaction */
/* @var $relatedTransactionsrelatedTransactions UnzerCw_Entity_Transaction[] */
/* @var $this UnzerCw_Backend_Controller_Transaction */

$transactionObject = $transaction->getTransactionObject();

?>
<br />
<div id="button-container-<?php echo $transaction->getTransactionId(); ?>">
	
</div>
<table class="table table-striped" width="100%">
	<thead>
		<tr>
			<th><?php echo UnzerCw_Language::_('Transaction Information') ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<table class="table table-striped" style="margin: 5px; width: 80%;" >
					<tr>
						<th width="25%"><strong><?php echo UnzerCw_Language::_('Transaction ID') ?></strong></th>
						<td><?php echo $transaction->getTransactionId(); ?></td>
					</tr>
					<tr>
						<th><strong><?php echo UnzerCw_Language::_('Transaction External ID') ?></strong></th>
						<td><?php echo $transaction->getTransactionExternalId(); ?></td>
					</tr>
					<tr>
						<th><strong><?php echo UnzerCw_Language::_('Authorization Status') ?></strong></th>
						<td><?php echo $transaction->getAuthorizationStatus(); ?></td>
					</tr>
						<tr>
						<th><strong><?php echo UnzerCw_Language::_('Order ID') ?></strong></th>
						<td><?php echo $transaction->getOrderId(); ?></td>
					</tr>
					<tr>
						<th><strong><?php echo UnzerCw_Language::_('Created On') ?></strong></th>
						<td><?php echo $transaction->getCreatedOn()->format('c'); ?></td>
					</tr>
					<tr>
						<th><strong><?php echo UnzerCw_Language::_('Updated On') ?></strong></th>
						<td><?php echo $transaction->getUpdatedOn()->format('c'); ?></td>
					</tr>
					<tr>
						<th><strong><?php echo UnzerCw_Language::_('Customer ID') ?></strong></th>
						<td><?php echo $transaction->getCustomerId(); ?></td>
					</tr>
					<tr>
						<th><strong><?php echo UnzerCw_Language::_('Payment ID') ?></strong></th>
						<td><?php echo $transaction->getPaymentId(); ?></td>
					</tr>
					<?php if (is_object($transaction->getTransactionObject())):?>
						<?php foreach ($transactionObject->getTransactionLabels() as $label): ?>	
							<tr>
								<th><strong><?php echo $label['label'];?></strong><?php if (isset($label['description'])): ?> 
										<img src="../xtAdmin/images/icons/information.png" alt="<?php echo $label['description']; ?>" title="<?php echo $label['description']; ?>" style="vertical-align:middle; cursor:help;">
									<?php endif; ?></th>
								<td>
									<?php echo Customweb_Core_Util_Xml::escape($label['value']);?>
								</td>
							</tr>
						<?php endforeach;?>
						
					<?php endif;?>
					<?php if (is_object($transactionObject) && $transactionObject->isAuthorized() && $transactionObject->getPaymentInformation() != null):?>
						<th><strong><?php echo UnzerCw_Language::_('Payment Information') ?></strong></th>
						<td><?php echo $transactionObject->getPaymentInformation(); ?></td>
					<?php endif;?>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<br />

<?php if (is_object($transactionObject) && count($transactionObject->getCaptures()) > 0): ?>
<h3 style="margin-left: 5px"><?php echo UnzerCw_Language::_('Captures for this transaction'); ?></h3>
<table class="table table-striped" width="100%" border="0" cellspacing="0" cellpadding="6">
	<thead>
		<tr>
			<th><?php echo UnzerCw_Language::_('Date'); ?></th>
			<th><?php echo UnzerCw_Language::_('Amount'); ?></th>
			<th><?php echo UnzerCw_Language::_('Status'); ?></th>
			<th> </th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($transactionObject->getCaptures() as $index => $capture):?>
		<tr>
			<td><?php echo $capture->getCaptureDate()->format('c'); ?></td>
			<td><?php echo $capture->getAmount(); ?></td>
			<td><?php echo $capture->getStatus(); ?></td>
			<td>
				<img src="../xtAdmin/images/icons/information.png" onclick="<?php echo str_replace('"', '&quot;', $this->getRemoteWindow(UnzerCw_Language::_('Info'), 'captureInfo', array(
					'transaction_id' => $transaction->getTransactionId(),
					'capture_id' => $capture->getCaptureId(),
				))); ?>" style="cursor:pointer;"/>
			</td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
<br />
<?php endif;?>


<?php if (is_object($transactionObject) && count($transactionObject->getRefunds()) > 0): ?>
<h3 style="margin-left: 5px"><?php echo UnzerCw_Language::_('Refunds for this transaction'); ?></h3>
<table class="table table-striped" width="100%" border="0" cellspacing="0" cellpadding="6">
	<thead>
		<tr>
			<th><?php echo UnzerCw_Language::_('Date'); ?></th>
			<th><?php echo UnzerCw_Language::_('Amount'); ?></th>
			<th><?php echo UnzerCw_Language::_('Status'); ?></th>
			<th> </th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($transactionObject->getRefunds() as $index => $refund):?>
		<tr>
			<td><?php echo $refund->getRefundedDate()->format('c'); ?></td>
			<td><?php echo $refund->getAmount(); ?></td>
			<td><?php echo $refund->getStatus(); ?></td>
			<td>
				<img src="../xtAdmin/images/icons/information.png" onclick="<?php echo str_replace('"', '&quot;', $this->getRemoteWindow(UnzerCw_Language::_('Info'), 'refundInfo', array(
						'transaction_id' => $transaction->getTransactionId(),
						'refund_id' => $refund->getRefundId(),
					))); ?>" style="cursor:pointer;"/>
			</td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
<br />
<?php endif;?>


<?php if (is_object($transactionObject) && count($transactionObject->getHistoryItems()) > 0): ?>
<h3 style="margin-left: 5px"><?php echo UnzerCw_Language::_('Transactions History'); ?></h3>
<table class="table table-striped" width="100%" border="0" cellspacing="0" cellpadding="6">
	<thead>
		<tr>
			<th><?php echo UnzerCw_Language::_('Date'); ?></th>
			<th><?php echo UnzerCw_Language::_('Action'); ?></th>
			<th><?php echo UnzerCw_Language::_('Message'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($transactionObject->getHistoryItems() as $item):?>
		<tr>
			<td><?php echo $item->getCreationDate()->format('c'); ?></td>
			<td><?php echo $item->getActionPerformed(); ?></td>
			<td><?php echo $item->getMessage(); ?></td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
<br />
<?php endif;?>


<?php if (is_object($transactionObject)): ?>
<h3 style="margin-left: 5px"><?php echo UnzerCw_Language::_('Customer Data'); ?></h3>
<table class="table table-striped" width="100%">
	<?php $context = $transactionObject->getTransactionContext()->getOrderContext(); ?>
	<tr>
		<th><?php echo UnzerCw_Language::_('Billing Address') ?></th>
		<th><?php echo UnzerCw_Language::_('Shipping Address') ?></th>
	</tr>
	<tr>
		<td><p>
			<?php echo Customweb_Core_Util_Xml::escape($context->getBillingFirstName() . ' ' . $context->getBillingLastName()); ?><br />
			<?php if ($context->getBillingCompanyName() !== null): ?>
				<?php echo  Customweb_Core_Util_Xml::escape($context->getBillingCompanyName()); ?><br />
			<?php endif;?>
			<?php echo  Customweb_Core_Util_Xml::escape($context->getBillingStreet()); ?><br />
			<?php echo  Customweb_Core_Util_Xml::escape(strtoupper($context->getBillingCountryIsoCode()) . '-' . $context->getBillingPostCode() . ' ' . $context->getBillingCity()); ?><br />
			<?php if ($context->getBillingDateOfBirth() !== null) :?>
				<?php echo UnzerCw_Language::_('Birthday') . ': ' . $context->getBillingDateOfBirth()->format("Y-m-d"); ?><br />
			<?php endif;?>
			<?php if ($context->getBillingPhoneNumber() !== null) :?>
				<?php echo UnzerCw_Language::_('Phone') . ': ' .  Customweb_Core_Util_Xml::escape($context->getBillingPhoneNumber()); ?><br />
			<?php endif;?>
			</p>
		</td>
		<td><p>
			<?php echo  Customweb_Core_Util_Xml::escape($context->getShippingFirstName() . ' ' . $context->getShippingLastName()); ?><br />
			<?php if ($context->getShippingCompanyName() !== null): ?>
				<?php echo  Customweb_Core_Util_Xml::escape($context->getShippingCompanyName()); ?><br />
			<?php endif;?>
			<?php echo Customweb_Core_Util_Xml::escape($context->getShippingStreet()); ?><br />
			<?php echo Customweb_Core_Util_Xml::escape(strtoupper($context->getShippingCountryIsoCode()) . '-' . $context->getShippingPostCode() . ' ' . $context->getShippingCity()); ?><br />
			<?php if ($context->getShippingDateOfBirth() !== null) :?>
				<?php echo UnzerCw_Language::_('Birthday') . ': ' . $context->getShippingDateOfBirth()->format("Y-m-d"); ?><br />
			<?php endif;?>
			<?php if ($context->getShippingPhoneNumber() !== null) :?>
				<?php echo UnzerCw_Language::_('Phone') . ': ' . Customweb_Core_Util_Xml::escape($context->getShippingPhoneNumber()); ?><br />
			<?php endif;?>
			</p>
		</td>
	</tr>
</table>
<br />
<h3 style="margin-left: 5px"><?php echo UnzerCw_Language::_('Products'); ?></h3>
<table class="table table-striped" width="100%" border="0" cellspacing="0" cellpadding="6">
	<thead>
		<tr>
			<th><?php echo UnzerCw_Language::_('Name'); ?></th>
			<th><?php echo UnzerCw_Language::_('SKU'); ?></th>
			<th><?php echo UnzerCw_Language::_('Quantity'); ?></th>
			<th><?php echo UnzerCw_Language::_('Type'); ?></th>
			<th><?php echo UnzerCw_Language::_('Tax Rate'); ?></th>
			<th><?php echo UnzerCw_Language::_('Amount (excl. VAT)'); ?></th>
			<th><?php echo UnzerCw_Language::_('Amount (inkl. VAT)'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($transactionObject->getTransactionContext()->getOrderContext()->getInvoiceItems() as $invoiceItem):?>
		<tr>
			<td><?php echo $invoiceItem->getName() ?></td>
			<td><?php echo $invoiceItem->getSku(); ?></td>
			<td><?php echo $invoiceItem->getQuantity(); ?></td>
			<td><?php echo $invoiceItem->getType(); ?></td>
			<td><?php echo $invoiceItem->getTaxRate(); ?>%</td>
			<td><?php echo Customweb_Util_Currency::roundAmount($invoiceItem->getAmountExcludingTax(), $context->getCurrencyCode()) . ' ' . $context->getCurrencyCode(); ?></td>
			<td><?php echo Customweb_Util_Currency::roundAmount($invoiceItem->getAmountIncludingTax(), $context->getCurrencyCode()) . ' ' . $context->getCurrencyCode(); ?></td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
<br />
<?php endif;?>


<?php if (count($relatedTransactions) > 0): ?>
<h3 style="margin-left: 5px"><?php echo UnzerCw_Language::_('Transactions related to the same order'); ?></h3>
<table class="table table-striped" width="100%" border="0" cellspacing="0" cellpadding="6">
	<thead>
	<tr>
		<th><?php echo UnzerCw_Language::_('Transaction Number'); ?></th>
		<th><?php echo UnzerCw_Language::_('Status'); ?></th>
		<th><?php echo UnzerCw_Language::_('Authorization Amount'); ?></th>
		<th></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($relatedTransactions as $transaction): ?>
		<?php if (is_object($transaction->getTransactionObject())) : ?>
		<tr>
			<td><?php echo $transaction->getTransactionExternalId(); ?></td>
			<td><?php echo $transaction->getTransactionObject()->getAuthorizationStatus() ?></td>
			<td><?php echo $transaction->getTransactionObject()->getAuthorizationAmount(); ?></td>
			<td><img src="../xtAdmin/images/icons/wrench.png" style="cursor:pointer;" onclick="addTab(
				'<?php echo $this->getActionUrl('view', array('transaction_id' => $transaction->getTransactionId())); ?>', 
				'<?php echo UnzerCw_Language::_('Edit Transaction'); ?>');" alt="<?php echo UnzerCw_Language::_('Edit Transaction'); ?>"
				/>
			</td>
		</tr>
		<?php endif; ?>
	<?php endforeach;?>
	</tbody>
	
</table>
<br />
<?php endif; ?>
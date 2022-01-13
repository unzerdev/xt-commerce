<?php 

/* @var $transactions UnzerCw_Entity_Transaction[] */
/* @var $this UnzerCw_Backend_Controller_Order */
/* @var $transaction UnzerCw_Entity_Transaction */

?>

<?php if (count($transactions) > 0):?>
	<h3><?php echo UnzerCw_Language::_('Unzer Transactions'); ?></h3>
	<table class="table table-striped" width="100%" border="0" cellspacing="0" cellpadding="6">
		<thead>
			<tr>
				<th><?php echo UnzerCw_Language::_('Transaction ID'); ?></th>
				<th><?php echo UnzerCw_Language::_('Date'); ?></th>
				<th><?php echo UnzerCw_Language::_('Status'); ?></th>
				<th><?php echo UnzerCw_Language::_('Amount'); ?></th>
				<th><?php echo UnzerCw_Language::_('Action'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($transactions as $transaction):?>
				<tr>
					<td><?php echo $transaction->getTransactionId();?></td>
					<td><?php echo $transaction->getCreatedOn()->format('c'); ?></td>
					<td><?php echo $transaction->getAuthorizationStatus();?></td>
					<td><?php echo Customweb_Util_Currency::formatAmount($transaction->getAuthorizationAmount(), $transaction->getCurrency());?></td>
					<td>
						<img src="../xtAdmin/images/icons/wrench.png" style="cursor:pointer;" onclick="addTab(
							'<?php echo self::getControllerUrl('transaction', 'view', array('transaction_id' => $transaction->getTransactionId())); ?>', 
							'<?php echo UnzerCw_Language::_('Edit Transaction'); ?>');" alt="<?php echo UnzerCw_Language::_('Edit Transaction'); ?>"
						/>
						<?php if ($transaction->getTransactionObject() !== null):?>
						
							<?php if ($transaction->getTransactionObject()->isRefundPossible()):?>
								<img src="../xtAdmin/images/icons/arrow_undo.png" style="cursor:pointer;" onclick="<?php echo str_replace('"', '&quot;', $this->getRemoteWindow(UnzerCw_Language::_('Refund'), 'refund', array(
									'transaction_id' => $transaction->getTransactionId(),
								), 960, 600, 'transaction')); ?>" />
							<?php endif;?>
											
							<?php if ($transaction->getTransactionObject()->isCapturePossible()):?>
								<img src="../xtAdmin/images/icons/report_go.png" style="cursor:pointer;" onclick="<?php echo str_replace('"', '&quot;', $this->getRemoteWindow(UnzerCw_Language::_('Capture'), 'capture', array(
									'transaction_id' => $transaction->getTransactionId(),
								), 960, 600, 'transaction')); ?>" />
							<?php endif;?>
											
							<?php if ($transaction->getTransactionObject()->isCancelPossible()):?>
								<img src="../xtAdmin/images/icons/bin.png" style="cursor:pointer;" onclick="<?php echo str_replace('"', '&quot;', $this->getRemoteWindow(UnzerCw_Language::_('Cancel'), 'cancel', array(
									'transaction_id' => $transaction->getTransactionId(),
								), 960, 600, 'transaction')); ?>" />
							<?php endif;?>
											
						<?php endif;?>
					</td>
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>
<?php endif;?>

<?php if ($isPluginPaymentMethod):?>
	<div id="UnzerCwMotoButtons<?php echo $orderId; ?>"></div>
<?php endif;?>
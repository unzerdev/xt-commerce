<?php

/**
 *  * You are allowed to use this API in your web application.
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

require_once 'Customweb/Payment/Authorization/DefaultInvoiceItem.php';
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICapture.php';
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICancel.php';
require_once 'Customweb/Payment/Authorization/IInvoiceItem.php';
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/IRefund.php';

require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/Language.php';
require_once 'UnzerCw/Backend/Controller/Abstract.php';

class UnzerCw_Backend_Controller_Transaction extends UnzerCw_Backend_Controller_Abstract {

	public function viewAction(){
		$transaction = $this->getTransaction();
		$this->addVariable('transaction', $transaction);
		
		$this->addVariable('relatedTransactions', 
				UnzerCw_Util::getEntityManager()->searchByFilterName('UnzerCw_Entity_Transaction', 'loadByOrderId', 
						array(
							'>orderId' => $transaction->getOrderId() 
						)));
		
		echo $this->fetchView('transaction/view');
		echo "\n";
		echo '<script type="text/javascript">';
		echo "\n";
		echo PhpExt_Ext::onReady($this->getButtonPanel($transaction)->getJavascript(false, 'buttonPanel'));
		echo "\n";
		echo '</script>';
	}

	public function refundAction(){
		$transaction = $this->getTransaction();
		
		if (isset($_POST['transaction_id'])) {
			if (isset($_POST['quantity'])) {
				
				$refundLineItems = array();
				$lineItems = $transaction->getTransactionObject()->getNonRefundedLineItems();
				foreach ($_POST['quantity'] as $index => $quantity) {
					if (isset($_POST['price_including'][$index]) && floatval($_POST['price_including'][$index]) != 0) {
						$originalItem = $lineItems[$index];
						if ($originalItem->getType() == Customweb_Payment_Authorization_IInvoiceItem::TYPE_DISCOUNT) {
							$priceModifier = -1;
						}
						else {
							$priceModifier = 1;
						}
						$refundLineItems[$index] = new Customweb_Payment_Authorization_DefaultInvoiceItem($originalItem->getSku(), 
								$originalItem->getName(), $originalItem->getTaxRate(), $priceModifier * (floatval($_POST['price_including'][$index])), 
								$quantity, $originalItem->getType());
					}
				}
				if (count($refundLineItems) > 0) {
					$adapter = UnzerCw_Util::createContainer()->getBean('Customweb_Payment_BackendOperation_Adapter_Service_IRefund');
					if (!($adapter instanceof Customweb_Payment_BackendOperation_Adapter_Service_IRefund)) {
						throw new Exception("No adapter with interface 'Customweb_Payment_BackendOperation_Adapter_Service_IRefund' provided.");
					}
					
					$close = false;
					if (isset($_POST['close']) && $_POST['close'] == 'on') {
						$close = true;
					}
					try {
						$adapter->partialRefund($transaction->getTransactionObject(), $refundLineItems, $close);
						UnzerCw_Util::getEntityManager()->persist($transaction);
						echo '<div class="success">' . UnzerCw_Language::_("Refund was successful.") . '</div>';
						die();
					}
					catch (Exception $e) {
						UnzerCw_Util::getEntityManager()->persist($transaction);
						echo '<div class="error">' . $e->getMessage() . '</div>';
						die();
					}
				}
			}
			else {
				$adapter = UnzerCw_Util::createContainer()->getBean('Customweb_Payment_BackendOperation_Adapter_Service_IRefund');
				if (!($adapter instanceof Customweb_Payment_BackendOperation_Adapter_Service_IRefund)) {
					throw new Exception("No adapter with interface 'Customweb_Payment_BackendOperation_Adapter_Service_IRefund' provided.");
				}
				
				try {
					$adapter->refund($transaction->getTransactionObject());
					UnzerCw_Util::getEntityManager()->persist($transaction);
					echo '<div class="success">' . UnzerCw_Language::_("Refund was successful.") . '</div>';
					die();
				}
				catch (Exception $e) {
					UnzerCw_Util::getEntityManager()->persist($transaction);
					echo '<div class="error">' . $e->getMessage() . '</div>';
					die();
				}
			}
		}
		
		$this->addVariable('transaction', $transaction);
		echo $this->fetchView('transaction/refund');
		
		$panel = new PhpExt_Panel('refundForm');
		$submitBtn = PhpExt_Button::createTextButton(UnzerCw_Language::_("Refund"), 
				new PhpExt_Handler(
						PhpExt_Javascript::inlineStm(
								"
						var form = $('#refundForm" . $transaction->getTransactionId() . "');
						this.el.dom.style.display = 'none';
						$.ajax({
							type: 'POST',
							url: form[0].action,
							data: form.serialize(),
							success: function (response) {
								form.replaceWith(response);
								contentTabs.getActiveTab().getUpdater().refresh();
							}
						});
					")));
		$submitBtn->setType(PhpExt_Button::BUTTON_TYPE_SUBMIT);
		$panel->addButton($submitBtn);
		$panel->setRenderTo(PhpExt_Javascript::variable("Ext.get('refundFormPanel" . $transaction->getTransactionId() . "')"));
		
		echo '<div id="refundFormPanel' . $transaction->getTransactionId() . '"></div>';
		
		echo '<script type="text/javascript">';
		echo "\n";
		echo PhpExt_Ext::onReady('UnzerCwLineItemGrid.init();', $panel->getJavascript(false, 'buttonPanel'));
		echo "\n";
		echo '</script>';
	}

	public function cancelAction(){
		$transaction = $this->getTransaction();
		
		if (isset($_POST['transaction_id'])) {
			$adapter = UnzerCw_Util::createContainer()->getBean('Customweb_Payment_BackendOperation_Adapter_Service_ICancel');
			if (!($adapter instanceof Customweb_Payment_BackendOperation_Adapter_Service_ICancel)) {
				throw new Exception("No adapter with interface 'Customweb_Payment_BackendOperation_Adapter_Service_ICancel' provided.");
			}
			try {
				$adapter->cancel($transaction->getTransactionObject());
				echo '<div class="success">' . UnzerCw_Language::_("Cancel was successful.") . '</div>';
			}
			catch (Exception $e) {
				echo '<div class="error">' . $e->getMessage() . '</div>';
			}
			UnzerCw_Util::getEntityManager()->persist($transaction);
			die();
		}
		
		$this->addVariable('transaction', $transaction);
		echo $this->fetchView('transaction/cancel');
		
		$panel = new PhpExt_Panel('cancelForm');
		$submitBtn = PhpExt_Button::createTextButton(UnzerCw_Language::_("Cancel"), 
				new PhpExt_Handler(
						PhpExt_Javascript::inlineStm(
								"
						var form = $('#cancelForm" . $transaction->getTransactionId() . "');
						this.el.dom.style.display = 'none';
						$.ajax({
							type: 'POST',
							url: form[0].action,
							data: form.serialize(),
							success: function (response) {
								form.replaceWith(response);
								contentTabs.getActiveTab().getUpdater().refresh();
							}
						});
					")));
		$submitBtn->setType(PhpExt_Button::BUTTON_TYPE_SUBMIT);
		$panel->addButton($submitBtn);
		$panel->setRenderTo(PhpExt_Javascript::variable("Ext.get('cancelFormPanel" . $transaction->getTransactionId() . "')"));
		
		echo '<div id="cancelFormPanel' . $transaction->getTransactionId() . '"></div>';
		
		echo '<script type="text/javascript">';
		echo "\n";
		echo PhpExt_Ext::onReady('UnzerCwLineItemGrid.init();', $panel->getJavascript(false, 'buttonPanel'));
		echo "\n";
		echo '</script>';
	}

	public function captureAction(){
		$transaction = $this->getTransaction();
		if (isset($_POST['transaction_id'])) {
			
			if (isset($_POST['quantity'])) {
				$captureLineItems = array();
				$lineItems = $transaction->getTransactionObject()->getUncapturedLineItems();
				foreach ($_POST['quantity'] as $index => $quantity) {
					if (isset($_POST['price_including'][$index]) && floatval($_POST['price_including'][$index]) != 0) {
						$originalItem = $lineItems[$index];
						if ($originalItem->getType() == Customweb_Payment_Authorization_IInvoiceItem::TYPE_DISCOUNT) {
							$priceModifier = -1;
						}
						else {
							$priceModifier = 1;
						}
						$captureLineItems[$index] = new Customweb_Payment_Authorization_DefaultInvoiceItem($originalItem->getSku(), 
								$originalItem->getName(), $originalItem->getTaxRate(), $priceModifier * (floatval($_POST['price_including'][$index])), 
								$quantity, $originalItem->getType());
					}
				}
				if (count($captureLineItems) > 0) {
					$adapter = UnzerCw_Util::createContainer()->getBean('Customweb_Payment_BackendOperation_Adapter_Service_ICapture');
					if (!($adapter instanceof Customweb_Payment_BackendOperation_Adapter_Service_ICapture)) {
						throw new Exception("No adapter with interface 'Customweb_Payment_BackendOperation_Adapter_Service_ICapture' provided.");
					}
					
					$close = false;
					if (isset($_POST['close']) && $_POST['close'] == 'on') {
						$close = true;
					}
					try {
						$adapter->partialCapture($transaction->getTransactionObject(), $captureLineItems, $close);
						UnzerCw_Util::getEntityManager()->persist($transaction);
						echo '<div class="success">' . UnzerCw_Language::_("Capture was successful.") . '</div>';
						die();
					}
					catch (Exception $e) {
						UnzerCw_Util::getEntityManager()->persist($transaction);
						echo '<div class="error">' . $e->getMessage() . '</div>';
						die();
					}
				}
			}
			else {
				$adapter = UnzerCw_Util::createContainer()->getBean('Customweb_Payment_BackendOperation_Adapter_Service_ICapture');
				if (!($adapter instanceof Customweb_Payment_BackendOperation_Adapter_Service_ICapture)) {
					throw new Exception("No adapter with interface 'Customweb_Payment_BackendOperation_Adapter_Service_ICapture' provided.");
				}
				try {
					$adapter->capture($transaction->getTransactionObject());
					UnzerCw_Util::getEntityManager()->persist($transaction);
					echo '<div class="success">' . UnzerCw_Language::_("Capture was successful.") . '</div>';
					die();
				}
				catch (Exception $e) {
					UnzerCw_Util::getEntityManager()->persist($transaction);
					echo '<div class="error">' . $e->getMessage() . '</div>';
					die();
				}
			}
		}
		
		$this->addVariable('transaction', $transaction);
		echo $this->fetchView('transaction/capture');
		
		$panel = new PhpExt_Panel('captureForm');
		$submitBtn = PhpExt_Button::createTextButton(UnzerCw_Language::_("Capture"), 
				new PhpExt_Handler(
						PhpExt_Javascript::inlineStm(
								"
						var form = $('#captureForm" . $transaction->getTransactionId() . "');
						this.el.dom.style.display = 'none';
						$.ajax({
							type: 'POST',
							url: form[0].action,
							data: form.serialize(),
							success: function (response) {
								form.replaceWith(response);
								contentTabs.getActiveTab().getUpdater().refresh();
							}
						});
					")));
		$submitBtn->setType(PhpExt_Button::BUTTON_TYPE_SUBMIT);
		$panel->addButton($submitBtn);
		$panel->setRenderTo(PhpExt_Javascript::variable("Ext.get('captureFormPanel" . $transaction->getTransactionId() . "')"));
		
		echo '<div id="captureFormPanel' . $transaction->getTransactionId() . '"></div>';
		
		echo '<script type="text/javascript">';
		echo "\n";
		echo PhpExt_Ext::onReady('UnzerCwLineItemGrid.init();', $panel->getJavascript(false, 'buttonPanel'));
		echo "\n";
		echo '</script>';
	}

	public function captureInfoAction(){
		$transaction = $this->getTransaction();
		
		$capture = null;
		foreach ($transaction->getTransactionObject()->getCaptures() as $item) {
			if ($item->getCaptureId() == $_GET['capture_id']) {
				$capture = $item;
				break;
			}
		}
		
		if ($capture == null) {
			die('No capture found with the given id.');
		}
		
		$this->addVariable('transaction', $transaction);
		$this->addVariable('capture', $capture);
		
		echo $this->fetchView('transaction/capture-info');
	}

	public function refundInfoAction(){
		$transaction = $this->getTransaction();
		
		$refund = null;
		foreach ($transaction->getTransactionObject()->getRefunds() as $item) {
			if ($item->getRefundId() == $_GET['refund_id']) {
				$refund = $item;
				break;
			}
		}
		
		if ($refund == null) {
			die('No refund found with the given id.');
		}
		
		$this->addVariable('transaction', $transaction);
		$this->addVariable('refund', $refund);
		
		echo $this->fetchView('transaction/refund-info');
	}

	private function getButtonPanel(UnzerCw_Entity_Transaction $transaction){
		$panel = new PhpExt_Panel('buttonPanel');
		
		
		if ($transaction->getTransactionObject()->isCapturePossible()) {
			$panel->addButton(
					$this->createButton('capture', UnzerCw_Language::_("Capture"), 
							array(
								'transaction_id' => $transaction->getTransactionId() 
							)));
		}
		
		

		
		if ($transaction->getTransactionObject()->isCancelPossible()) {
			$panel->addButton(
					$this->createButton('cancel', UnzerCw_Language::_("Cancel"), 
							array(
								'transaction_id' => $transaction->getTransactionId() 
							)));
		}
		
		

		
		if ($transaction->getTransactionObject()->isRefundPossible()) {
			$panel->addButton(
					$this->createButton('refund', UnzerCw_Language::_("Refund"), 
							array(
								'transaction_id' => $transaction->getTransactionId() 
							)));
		}
		
		$panel->setRenderTo(PhpExt_Javascript::variable("Ext.get('button-container-" . $transaction->getTransactionId() . "')"));
		return $panel;
	}
}
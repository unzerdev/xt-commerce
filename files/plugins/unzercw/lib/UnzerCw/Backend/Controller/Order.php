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

require_once 'Customweb/Payment/Authorization/DefaultInvoiceItem.php';
require_once 'Customweb/Util/Html.php';
require_once 'Customweb/Payment/Authorization/Moto/IAdapter.php';

require_once 'UnzerCw/OrderContext/Order.php';
require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/Backend/MotoFormRenderer.php';
require_once 'UnzerCw/TransactionContext.php';
require_once 'UnzerCw/OrderStatus.php';
require_once 'UnzerCw/PaymentMethodWrapper.php';
require_once 'UnzerCw/Language.php';
require_once 'UnzerCw/Backend/Controller/Abstract.php';


class UnzerCw_Backend_Controller_Order extends UnzerCw_Backend_Controller_Abstract{


	public function orderViewAction($orderData) {

		$orderId = $_GET['edit_id'];
		$transactions = UnzerCw_Util::getEntityManager()->searchByFilterName(
				'UnzerCw_Entity_Transaction',
				'loadByOrderId',
				array(
					'>orderId' => $orderId,
				)
		);


		$this->addVariable('transactions', $transactions);
		$this->addVariable('orderData', $orderData);

		$js = '';
		$isPluginPaymentMethod = false;
		if (strpos($orderData['order_data']['payment_code'], 'cw_UNZ_') === 0) {
			$isPluginPaymentMethod = true;

			
		}

		if (count($transactions) <= 0 && $isPluginPaymentMethod == false) {
			return '';
		}

		return "\n</script>\n" . $this->fetchView('order/view') . "\n".'<script type="text/javascript">' ."\n" . $js;
	}

	

}
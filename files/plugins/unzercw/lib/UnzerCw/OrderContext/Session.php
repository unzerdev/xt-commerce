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

require_once 'Customweb/Util/Currency.php';
require_once 'Customweb/Payment/Authorization/DefaultInvoiceItem.php';
require_once 'Customweb/Payment/Authorization/IInvoiceItem.php';
require_once 'Customweb/Util/Invoice.php';

require_once 'UnzerCw/OrderContext/Abstract.php';


class UnzerCw_OrderContext_Session extends UnzerCw_OrderContext_Abstract
{
	public function __construct(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod) {

		// TODO: Set the federal_state_code_iso on addresses

		if (!isset($_SESSION['customer']) && !($_SESSION['customer'] instanceof customer)) {
			throw new Exception("No customer present in the current session.");
		}
		$customer = $_SESSION['customer'];

		if (!isset($_SESSION['cart']) || !($_SESSION['cart'] instanceof cart)) {
			throw new Exception("No cart present in the current session.");
		}
		$cart = $_SESSION['cart'];

		if (!isset($GLOBALS['currency']) && !($GLOBALS['currency'] instanceof currency)) {
			throw new Exception("No valid currency object present in current context.");
		}

		$shippingAddress = $this->cleanAddress($customer->customer_shipping_address);
		$billingAddress = $this->cleanAddress($customer->customer_payment_address);

		$checkout = new checkout();

		$tmp_shipping_data = $checkout->_getShipping();
		$shipping = 'Shipping';
		if( isset($_SESSION['selected_shipping']) && isset( $tmp_shipping_data[$_SESSION['selected_shipping']])) {
			$shipping_data = $tmp_shipping_data[$_SESSION['selected_shipping']];
			$shipping = $shipping_data['shipping_name'];
		}

		$invoiceItems = $this->getLineItemsFromSession($cart->total['plain'], $GLOBALS['currency']->code);
		parent::__construct(
			$paymentMethod,
			$invoiceItems,
			$shippingAddress,
			$billingAddress,
			$shipping,
			$cart->total['plain'],
			$GLOBALS['currency']->code,
			$this->getIsoLangaugeCode(),
			$customer->customer_info['customers_id'],
			$customer->customer_info['customers_email_address']
		);

	}

	/**
	 * Returns the language in 2-letter iso code.
	 */
	private function getIsoLangaugeCode() {
		if (isset($_SESSION['selected_language']) && !empty($_SESSION['selected_language'])) {
			return $_SESSION['selected_language'];
		}
		else {
			return 'de';
		}
	}


	private function cleanAddress($address) {
		$result = array();
		foreach ($address as $key => $value) {
			$key = str_replace('customers_', '', $key);
			$result[$key] = $value;
		}

		return $result;
	}



	private function getLineItemsFromSession($total, $currency) {
		$items = array();

		/* @var $cart cart */
		$cart = $_SESSION['cart'];
		$cart->_refresh();

		$content = $cart->show_content;
		foreach ($content as $contentItem) {
			$items[] = $this->buildItem($contentItem, Customweb_Payment_Authorization_IInvoiceItem::TYPE_PRODUCT, $currency);
		}

		$content = $cart->_getSubContent();
		foreach ($content['content'] as $content) {
			$items[] = $this->buildItem($content, Customweb_Payment_Authorization_IInvoiceItem::TYPE_FEE, $currency);
		}
		return Customweb_Util_Invoice::cleanupLineItems($items, $total, $currency);
	}

	private function buildItem($contentItem, $defaultType, $currencyCode) {
		$quantity = $contentItem['products_quantity'];
		$name = $contentItem['products_name'];
		$sku = $contentItem['products_model'];

		if (isset($contentItem['products_tax_value'])) {
			$taxRate = $contentItem['products_tax_value'];
		}
		else {
			$taxRate = 0;
		}

		$amountIncludingTax = $contentItem['products_final_price']['plain'];

		if(Customweb_Util_Currency::compareAmount($contentItem['products_final_price']['plain'], $contentItem['products_final_price']['plain_otax'], $currencyCode) == 0 && $taxRate != 0){
			//Items are shown without tax, beacause of customer setting
			$amountIncludingTax =  ($contentItem['products_price']['plain_otax'] + $contentItem['products_tax']['plain']) * $quantity;
		}


		$type = $defaultType;
		if ($amountIncludingTax < 0) {
			$type = Customweb_Payment_Authorization_IInvoiceItem::TYPE_DISCOUNT;
			$amountIncludingTax = $amountIncludingTax * -1;
		}
		else if (isset($contentItem['type']) && $contentItem['type'] == 'shipping') {
			$type = Customweb_Payment_Authorization_IInvoiceItem::TYPE_SHIPPING;
		}
		return new Customweb_Payment_Authorization_DefaultInvoiceItem($sku, $name, $taxRate, $amountIncludingTax, $quantity, $type);
	}


}





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


class UnzerCw_OrderContext_Order extends UnzerCw_OrderContext_Abstract
{
	private $orderId = null;
	private $storeId = null;
	
	public function __construct(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod, order $order, $invoiceItems = null) {
		$this->orderId = $order->oID;
		
		$shippingAddress = $this->getAddressByPrefix($order->order_data, 'delivery_');
		$billingAddress = $this->getAddressByPrefix($order->order_data, 'billing_');
	
		if (!isset($billingAddress['dob'])) {
			$billingAddress['dob'] = $order->order_customer['customers_dob'];
		}
		
		if (!isset($shippingAddress['dob'])) {
			$shippingAddress['dob'] = $order->order_customer['customers_dob'];
		}
		
		if ($invoiceItems === null) {
			
			$invoiceItems = $this->getLineItemsFromOrder($order, $order->order_total['total']['plain'], $order->order_data['currency_code']);
		}
		
		$this->storeId = $order->order_data['shop_id'];
		
		$shippingName = 'Shipping';
		if (isset($order->order_data['shipping_name'])) {
			$shippingName = $order->order_data['shipping_name'];
		}
		else if (isset($order->order_data['shipping_code'])) {
			$shippingName = $order->order_data['shipping_code'];
		}
		
		parent::__construct(
				$paymentMethod,
				$invoiceItems,
				$shippingAddress,
				$billingAddress,
				$shippingName,
				$order->order_total['total']['plain'],
				$order->order_data['currency_code'],
				$order->order_data['language_code'],
				$order->order_data['customers_id'],
				$order->order_data['customers_email_address']
		);
	}
	
	public function getOrderId() {
		return $this->orderId;
	}
	
	public function getOrder() {
		return new order($this->orderId, $this->getCustomerId());
	}
	
	public function getStoreId() {
		if ($this->storeId === null) {
			$order = $this->getOrder();
			$this->storeId = $order->order_data['shop_id'];
		}
		
		return $this->storeId;
	}
	
	private function getAddressByPrefix(array $data, $prefix) {
		$result = array();
		foreach ($data as $key => $value) {
			if (strpos($key, $prefix) === 0) {
				$key = str_replace($prefix, '', $key);
				$result[$key] = $value;
			}
		}
	
		return $result;
	}
	
	private function getLineItemsFromOrder(order $order, $total, $currencyCode) {
		$items = array();
	
		foreach ($order->order_products as $contentItem) {
			$items[] = $this->buildProductItem($contentItem, $currencyCode);
		}
	
		foreach ($order->order_total_data as $contentItem) {
			$items[] = $this->buildTotalItem($contentItem, $currencyCode);
		}
	
		return Customweb_Util_Invoice::cleanupLineItems($items, $total, $currencyCode);
	}

	private function buildProductItem($contentItem, $currencyCode) {
		$quantity = $contentItem['products_quantity'];
		$name = $contentItem['products_name'];
		$sku = $contentItem['products_model'];
		$taxRate = $contentItem['products_tax_rate'];
		$amountIncludingTax = $contentItem['products_final_price']['plain'];
		if(Customweb_Util_Currency::compareAmount($contentItem['products_final_price']['plain'], $contentItem['products_final_price']['plain_otax'], $currencyCode) == 0 && $taxRate != 0){
			//Items are shown without tax, beacause of customer setting
			$amountIncludingTax =  ($contentItem['products_price']['plain_otax'] + $contentItem['products_tax']['plain']) * $quantity;
		}
		
		$type = Customweb_Payment_Authorization_IInvoiceItem::TYPE_PRODUCT;
		return new Customweb_Payment_Authorization_DefaultInvoiceItem($sku, $name, $taxRate, $amountIncludingTax, $quantity, $type);
	}

	private function buildTotalItem($contentItem, $currencyCode) {
		$quantity = $contentItem['orders_total_quantity'];
		$name = $contentItem['orders_total_name'];
		$sku = $contentItem['orders_total_model'];
		$taxRate = $contentItem['orders_total_tax_rate'];
		$amountIncludingTax = $contentItem['orders_total_final_price']['plain'];
		if(Customweb_Util_Currency::compareAmount($contentItem['orders_total_final_price']['plain'], $contentItem['orders_total_final_price']['plain_otax'], $currencyCode) == 0 && $taxRate != 0){
			//Items are shown without tax, beacause of customer setting
			$amountIncludingTax =  ($contentItem['orders_total_price']['plain_otax'] + $contentItem['orders_total_tax']['plain']) * $quantity ;
		}
					
		$type = Customweb_Payment_Authorization_IInvoiceItem::TYPE_FEE;
		if ($amountIncludingTax < 0) {
			$type = Customweb_Payment_Authorization_IInvoiceItem::TYPE_DISCOUNT;
			$amountIncludingTax = $amountIncludingTax * -1;
		}
		else if (isset($contentItem['orders_total_key']) && $contentItem['orders_total_key'] == 'shipping') {
			$type = Customweb_Payment_Authorization_IInvoiceItem::TYPE_SHIPPING;
		}
		
		return new Customweb_Payment_Authorization_DefaultInvoiceItem($sku, $name, $taxRate, $amountIncludingTax, $quantity, $type);
	}
	
}





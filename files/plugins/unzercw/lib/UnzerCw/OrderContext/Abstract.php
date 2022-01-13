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

require_once 'Customweb/Payment/Authorization/OrderContext/AbstractDeprecated.php';
require_once 'Customweb/Date/DateTime.php';
require_once 'Customweb/Payment/Authorization/IOrderContext.php';
require_once 'Customweb/Core/Language.php';
require_once 'Customweb/Core/Util/Rand.php';

require_once 'UnzerCw/Language.php';
require_once 'UnzerCw/OrderStatus.php';


abstract class UnzerCw_OrderContext_Abstract extends Customweb_Payment_Authorization_OrderContext_AbstractDeprecated implements Customweb_Payment_Authorization_IOrderContext {
	
	/**
	 * @var Customweb_Payment_Authorization_DefaultInvoiceItem[]
	 */
	protected $invoiceItems = array();
	
	/**
	 * @var Customweb_Payment_Authorization_IPaymentMethod
	 */
	protected $paymentMethod = null;
	
	/**
	 * @var Address
	 */
	protected $shippingAddress = null;
	
	/**
	 * @var Address
	 */
	protected $billingAddress = null;
	
	/**
	 * @var int
	 */
	protected $customerNumberOfOrders = 0;
	
	/**
	 * @var string
	 */
	protected $shippingMethodName = null;
	
	/**
	 * @var float
	 */
	protected $orderTotal = 0;
	
	/**
	 * @var string
	 */
	protected $currencyCode = 'EUR';
	
	/**
	 * @var string
	 */
	protected $languageCode = 'de';
	
	/**
	 * @var DateTime
	 */
	protected $customerCreationDate = null;
	
	/**
	 * @var int
	 */
	protected $customerId = null;
	
	/**
	 * @var string
	 */
	protected $customerEMailAddress = null;
	
	/**
	 * @var string
	 */
	protected $customerVatId = null;
	
	protected $checkoutId = null;
	
	/**
	 * 
	 * @var boolean
	 */
	protected $isAjaxReloadRequired = false;
	
	public function __construct(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod, array $invoiceItems, $shippingAddress, $billingAddress, $shippingMethodName, $orderTotal, $currencyCode, $languageCode, $customerId, $customerEMailAddress) {
		$this->paymentMethod = $paymentMethod;
		$this->invoiceItems = $invoiceItems;
		$this->shippingAddress = $shippingAddress;
		$this->billingAddress = $billingAddress;
		$this->shippingMethodName = $shippingMethodName;
		$this->customerId = $customerId;
		
		if ($customerId !== null) {
			$customer = new customer($customerId);
			$this->customerNumberOfOrders = $this->getNumberOfSuccessfulOrders();
			$this->customerCreationDate = new Customweb_Date_DateTime($customer->customer_info['date_added']);
			if (!empty($customer->customer_info['customers_vat_id'])) {
				$this->customerVatId = $customer->customer_info['customers_vat_id'];
			}
		}
		$this->customerEMailAddress = $customerEMailAddress;
		
		$this->orderTotal = $orderTotal;
		$this->currencyCode = $currencyCode;
		$this->languageCode = new Customweb_Core_Language($languageCode);
		
		if (!isset($_SESSION['unzercw_checkout_id'])) {
			$_SESSION['unzercw_checkout_id'] = array();
		}
		if (!isset($_SESSION['unzercw_checkout_id'][$paymentMethod->getPaymentMethodName()])) {
			$_SESSION['unzercw_checkout_id'][$paymentMethod->getPaymentMethodName()] = Customweb_Core_Util_Rand::getUuid();
		}
		$this->checkoutId = $_SESSION['unzercw_checkout_id'][$paymentMethod->getPaymentMethodName()];
	}
	
	public function isAjaxReloadRequired() {
		return $this->isAjaxReloadRequired;
	}
	
	private function getNumberOfSuccessfulOrders() {
		global $db,$store_handler;
		
		$customerId = $this->getCustomerId();
		if ($customerId === null) {
			return 0;
		}
		
		$unsuccesfulOrderStatus = array();
		$unsuccesfulOrderStatus[] = UnzerCw_OrderStatus::getStatusIdByIdentifier('failed');
		$unsuccesfulOrderStatus[] = UnzerCw_OrderStatus::getStatusIdByIdentifier('cancelled');
		$unsuccesfulOrderStatus[] = UnzerCw_OrderStatus::getStatusIdByIdentifier('pending');
		
		$query = "SELECT count(*) as count FROM ".TABLE_ORDERS." o, ".TABLE_ORDERS_STATS." os WHERE 
				o.orders_id=os.orders_id and o.customers_id='" . $customerId . "' and o.orders_status NOT IN ('" . implode("', '", $unsuccesfulOrderStatus) . "')";
		
		$rs = $db->Execute($query);
		return $rs->fields['count'];
	}
	
	public function getCheckoutId() {
		return $this->checkoutId;
	}

	public function getOrderParameters() {
		return array(
			'shop_system_version' => _SYSTEM_VERSION
		);
	}

	public function getCustomerRegistrationDate() {
		return $this->customerCreationDate;
	}
	
	public function getCustomerId() {
		return $this->customerId;
	}
	
	public function isNewCustomer() {
		if ($this->customerNumberOfOrders > 0) {
			return 'existing';
		}
		else {
			return 'new';
		}
	}

	public function getCustomerEMailAddress() {
		return $this->customerEMailAddress;
	}
	
	public function getOrderAmountInDecimals() {
		return $this->orderTotal;
	}
	
	public function getCurrencyCode() {
		return $this->currencyCode;
	}
	
	public function getLanguage() {
		return $this->languageCode;
	}
	
	public function getInvoiceItems() {
		return $this->invoiceItems;
	}
	
	public function getShippingMethod() {
		if ($this->shippingMethodName !== null) {
			return $this->shippingMethodName;
		}
		else {
			return UnzerCw_Language::_('No Shipping');
		}
	}
	
	public function getPaymentMethod() {
		return $this->paymentMethod;
	}
	
	public function getBillingDateOfBirth() {
		if (isset($this->billingAddress['dob']) && !empty($this->billingAddress['dob'])) {
			return new Customweb_Date_DateTime($this->billingAddress['dob']);
		}
		else {
			return null;
		}
	}
	
	public function getShippingDateOfBirth() {
		if (isset($this->shippingAddress['dob']) && !empty($this->shippingAddress['dob'])) {
			return new Customweb_Date_DateTime($this->shippingAddress['dob']);
		}
		else {
			return null;
		}
	}
	
	public function getBillingEMailAddress() {
		return $this->getCustomerEMailAddress();
	}
	
	public function getBillingGender() {
		if ($this->getBillingCompanyName() !== null) {
			return 'company';
		}
		else if (isset($this->billingAddress['gender'])) {
			if ($this->billingAddress['gender'] == 'm') {
				return 'male';
			}
			else if($this->billingAddress['gender'] == 'f') {
				return 'female';
			}
			else {
				return 'company';
			}
		}
		else {
			return null;
		}
	}
	
	public function getBillingSalutation() {
		return null;
	}
	
	public function getBillingFirstName() {
		return $this->billingAddress['firstname'];
	}
	
	public function getBillingLastName() {
		return $this->billingAddress['lastname'];
	}
	
	public function getBillingStreet() {
		return $this->billingAddress['street_address'];
	}
	
	public function getBillingCity() {
		return $this->billingAddress['city'];
	}
	
	public function getBillingPostCode() {
		return $this->billingAddress['postcode'];
	}
	
	public function getBillingState() {
		if (isset($this->billingAddress['federal_state_code_iso']) && !empty($this->billingAddress['federal_state_code_iso'])) {
			return $this->billingAddress['federal_state_code_iso'];
		}
	
		return null;
	}
	
	public function getBillingCountryIsoCode() {
		return $this->billingAddress['country_code'];
	}
	
	public function getBillingPhoneNumber() {
		if (isset($this->billingAddress['phone']) && !empty($this->billingAddress['phone'])) {
			return $this->billingAddress['phone'];
		}
		else {
			return null;
		}
	}
	
	public function getBillingMobilePhoneNumber() {
		return null;
	}
	
	public function getBillingCommercialRegisterNumber() {
		return null;
	}
	
	public function getBillingSalesTaxNumber() {
		return $this->customerVatId;
	}
	
	public function getBillingSocialSecurityNumber() {
		return null;
	}
	
	public function getBillingCompanyName() {
		$company = '';
		if (isset($this->billingAddress['company'])) {
			$company .= $this->billingAddress['company'];
		}
		if (isset($this->billingAddress['company_2'])) {
			$company .= ' ' . $this->billingAddress['company_2'];
		}
		if (isset($this->billingAddress['company_3'])) {
			$company .= ' ' . $this->billingAddress['company_3'];
		}
		$company = trim($company);
		if (!empty($company)) {
			return $company;
		}
		else {
			return null;
		}
	}
	
	public function getShippingEMailAddress() {
		return $this->getCustomerEMailAddress();
	}
	
	public function getShippingGender() {
		if ($this->getShippingCompanyName() !== null) {
			return 'company';
		}
		else if (isset($this->shippingAddress['gender'])){
			if ($this->shippingAddress['gender'] == 'm') {
				return 'male';
			}
			elseif ($this->shippingAddress['gender'] == 'f') {
				return 'female';
			}
			else {
				return 'company';
			}
		}
		else {
			return null;
		}
	}
	
	public function getShippingSalutation() {
		return null;
	}
	
	
	public function getShippingFirstName() {
		return $this->shippingAddress['firstname'];
	}
	
	public function getShippingLastName() {
		return $this->shippingAddress['lastname'];
	}
	
	public function getShippingStreet() {
		return $this->shippingAddress['street_address'];
	}
	
	public function getShippingCity() {
		return $this->shippingAddress['city'];
	}
	
	public function getShippingPostCode() {
		return $this->shippingAddress['postcode'];
	}
	
	public function getShippingState() {
		if (isset($this->shippingAddress['federal_state_code_iso']) && !empty($this->shippingAddress['federal_state_code_iso'])) {
			return $this->shippingAddress['federal_state_code_iso'];
		}
		else {
			return null;
		}
	}
	
	public function getShippingCountryIsoCode() {
		return $this->shippingAddress['country_code'];
	}
	
	public function getShippingPhoneNumber() {
		if (!empty($this->shippingAddress['phone']))  {
			return $this->shippingAddress['phone'];
		}	
		else {
			return null;
		}
	}
	
	public function getShippingMobilePhoneNumber() {
		return null;
	}
	
	public function getShippingCompanyName() {
		$company = '';
		if (isset($this->shippingAddress['company'])) {
			$company .= $this->shippingAddress['company'];
		}
		if (isset($this->shippingAddress['company_2'])) {
			$company .= ' ' . $this->shippingAddress['company_2'];
		}
		if (isset($this->shippingAddress['company_3'])) {
			$company .= ' ' . $this->shippingAddress['company_3'];
		}
		$company = trim($company);
		if (!empty($company)) {
			return $company;
		}
		else {
			return null;
		}
	}
	
	public function getShippingCommercialRegisterNumber() {
		return null;
	}
	
	public function getShippingSalesTaxNumber() {
		return $this->customerVatId;
	}
	
	public function getShippingSocialSecurityNumber() {
		return null;
	}
	
}
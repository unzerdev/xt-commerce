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




class UnzerCw_FormDataManager {
	
	/**
	 * @var Customweb_Payment_Authorization_IOrderContext
	 */
	private $orderContext;
	
	public function __construct(Customweb_Payment_Authorization_IOrderContext $orderContext) {
		$this->orderContext = $orderContext;
	}
		
	public function setFormData($data) {
		$key = $this->getSessionKey();
		$_SESSION[$key] = $data;
	}

	public function getFormData() {
		$key = $this->getSessionKey();
		if (isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}
		else {
			return array();
		}
	}
	
	public function reset() {
		$key = $this->getSessionKey();
		unset($_SESSION[$key]);
	}
	
	/**
	 * @return Customweb_Payment_Authorization_IOrderContext
	 */
	final protected function getOrderContext() {
		return $this->orderContext;
	}

	private function getSessionKey() {
		return 'form_data_unzercw_' . $this->getOrderContext()->getPaymentMethod()->getPaymentMethodName();
	}
	
}
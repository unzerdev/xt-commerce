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



require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/Adapter/AbstractAdapter.php';


/**
 * @author Thomas Hunziker
 * @Bean
 *
 */
class UnzerCw_Adapter_PaymentPageAdapter extends UnzerCw_Adapter_AbstractAdapter {
	
	private $visibleFormFields = array();
	private $formActionUrl = null;
	private $hiddenFields = array();
	
	public function getPaymentAdapterInterfaceName() {
		return 'Customweb_Payment_Authorization_PaymentPage_IAdapter';
	}
	
	/**
	 * @return Customweb_Payment_Authorization_PaymentPage_IAdapter
	 */
	public function getInterfaceAdapter() {
		return parent::getInterfaceAdapter();
	}
	
	protected function prepareAdapter() {
		$this->visibleFormFields = $this->getInterfaceAdapter()->getVisibleFormFields(
			$this->getOrderContext(), 
			$this->getAliasTransactionObject(), 
			$this->getFailedTransactionObject(), 
			$this->getPaymentCustomerContext()
		);
		$params = $this->storeAndGetFormParameters();
		if ($this->getTransaction() !== null) {
			$url = UnzerCw_Util::getControllerUrl('process', 'redirection', array(
				'cw_transaction_id' => $this->getTransaction()->getTransactionId())
			);
			$this->formActionUrl = $url;
			
			// Set header redirection URL.
			if ($this->visibleFormFields === null || count($this->visibleFormFields) <= 0 || count($params) > 0) {
				$this->setRedirectionUrl($url);
				if ($this->getInterfaceAdapter()->isHeaderRedirectionSupported($this->getTransaction()->getTransactionObject(), $params)) {
					$this->setRedirectionUrl($this->getInterfaceAdapter()->getRedirectionUrl($this->getTransaction()->getTransactionObject(), $params));
				}
			}
			
			$this->persistTransaction();
		}
	}
	
	protected function getVisibleFormFields() {
		return $this->visibleFormFields;
	}
	
	protected function getFormActionUrl() {
		return $this->formActionUrl;
	}
	
	protected function getHiddenFormFields() {
		return $this->hiddenFields;
	}
	
}
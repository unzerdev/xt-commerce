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


require_once 'Customweb/Core/Http/Response.php';

require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/Adapter/AbstractAdapter.php';


/**
 * @author Thomas Hunziker
 * @Bean
 *
 */
class UnzerCw_Adapter_ServerAdapter extends UnzerCw_Adapter_AbstractAdapter {

	private $visibleFormFields = array();

	public function getPaymentAdapterInterfaceName() {
		return 'Customweb_Payment_Authorization_Server_IAdapter';
	}
	
	/**
	 * @return Customweb_Payment_Authorization_Server_IAdapter
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
		if ($this->getTransaction() !== null) {
			$this->formActionUrl = UnzerCw_Util::getControllerUrl('Process', 'serverAuthorization', array('cw_transaction_id' => $this->getTransaction()->getTransactionId()));
			$this->persistTransaction();
		}
	}
	
	public function processOrderConfirmationRequest() {

		$params = $this->storeAndGetFormParameters();
		if ($this->getTransaction() !== null) {
		
			// Set header redirection URL.
			if ($this->visibleFormFields === null || count($this->visibleFormFields) <= 0 || count($params) > 0) {
				$transactionObject = $this->getTransaction()->getTransactionObject();
				$response = $this->getInterfaceAdapter()->processAuthorization($transactionObject, $params);
				$response = new Customweb_Core_Http_Response($response);
				UnzerCw_Util::getTransactionHandler()->persistTransactionObject($transactionObject);
				$response->send();
				die();
			}
		}
		
		return parent::processOrderConfirmationRequest();
	}
	
	
	protected function getVisibleFormFields() {
		return $this->visibleFormFields;
	}
	
	protected function getFormActionUrl() {
		return $this->formActionUrl;
	}
}
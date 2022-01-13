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


require_once 'UnzerCw/Adapter/AbstractAdapter.php';


/**
 * @author Thomas Hunziker
 * @Bean
 *
 */
class UnzerCw_Adapter_AjaxAdapter extends UnzerCw_Adapter_AbstractAdapter {
	
	private $visibleFormFields = array();
	private $ajaxScriptUrl = null;
	private $javaScriptCallbackFunction = null;
	
	public function getPaymentAdapterInterfaceName() {
		return 'Customweb_Payment_Authorization_Ajax_IAdapter';
	}
	
	/**
	 * @return Customweb_Payment_Authorization_Ajax_IAdapter
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
			$this->ajaxScriptUrl = $this->getInterfaceAdapter()->getAjaxFileUrl($this->getTransaction()->getTransactionObject());
			$this->javaScriptCallbackFunction = $this->getInterfaceAdapter()->getJavaScriptCallbackFunction($this->getTransaction()->getTransactionObject());
			$this->persistTransaction();
		}
	}

	public function getConfirmationPageVariables() {
		$vars = parent::getConfirmationPageVariables();
		if ($this->isConfirmationFormActive()) {
			$content = '';
			if(isset($vars['visibleFormFields'])) {
				$content = urlencode($vars['visibleFormFields']);
				unset($vars['visibleFormFields']);
			}
			$vars['additionalHtml'] = '<script type="text/javascript">
					var unzercwJavaScriptFormContent = "' . $content . '";
					var unzercwJavaScriptFormCallback = "UnzerCwProcessAjaxAuthorization";
			</script>';
		}
		return $vars;
	}

	public function processOrderConfirmationRequest() {
		if (isset($_REQUEST['ajaxCall']) && $_REQUEST['ajaxCall'] == 'true') {
				
			$vars = array(
				'status' => 'success',
				'ajaxScriptUrl' => $this->ajaxScriptUrl,
				'ajaxSubmitCallback' => $this->javaScriptCallbackFunction,
			);
				
			echo json_encode($vars);
			die();
		}
		else {
			return parent::processOrderConfirmationRequest();
		}
	}
	
	protected function getPaymentPageVariables() {
		
		$vars = parent::getPaymentPageVariables();
		
		$vars['ajaxScriptUrl'] = $this->ajaxScriptUrl;
		$vars['ajaxSubmitCallback'] = $this->javaScriptCallbackFunction;
		
		return $vars;
	}
	
	protected function getVisibleFormFields() {
		return $this->visibleFormFields;
	}
		
}
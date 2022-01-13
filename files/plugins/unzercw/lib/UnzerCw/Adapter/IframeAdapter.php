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


require_once 'UnzerCw/Language.php';
require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/Adapter/AbstractAdapter.php';


/**
 * @author Thomas Hunziker
 * @Bean
 *
 */
class UnzerCw_Adapter_IframeAdapter extends UnzerCw_Adapter_AbstractAdapter {
	
	private $visibleFormFields = array();
	private $formActionUrl = null;
	private $iframeHeight = 500;
	private $iframeUrl = null;
	private $errorMessage = '';
	
	public function getPaymentAdapterInterfaceName() {
		return 'Customweb_Payment_Authorization_Iframe_IAdapter';
	}
	
	/**
	 * @return Customweb_Payment_Authorization_Iframe_IAdapter
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
			$this->formActionUrl = UnzerCw_Util::getControllerUrl('process', 'iframe', array(
				'cw_transaction_id' => $this->getTransaction()->getTransactionId())
			);
			
			if ($this->visibleFormFields === null || count($this->visibleFormFields) <= 0 || count($params) > 0) {
				$this->setRedirectionUrl($this->formActionUrl);
			}
			
			$this->persistTransaction();
		}
	}
	
	public function getConfirmationPageVariables() {
		$vars = parent::getConfirmationPageVariables();
		if ($this->isConfirmationFormActive()) {
			$vars['additionalHtml'] = '<script type="text/javascript">
					var unzercwJavaScriptFormContent = "";
					var unzercwJavaScriptFormCallback = "UnzerCwProcessIFrameAuthorization";
			</script>';
		}
		return $vars;
	}

	public function processOrderConfirmationRequest() {
		if (isset($_REQUEST['ajaxCall']) && $_REQUEST['ajaxCall'] == 'true') {
				
			$vars = array(
				'status' => 'success',
				'iframe' => $this->getIframe(),
			);
				
			echo json_encode($vars);
			die();
		}
		else {
			return parent::processOrderConfirmationRequest();
		}
	}
	
	public function getIframe() {
		$params = $this->storeAndGetFormParameters();
		$this->iframeUrl = $this->getInterfaceAdapter()->getIframeUrl($this->getTransaction()->getTransactionObject(), $params);
		$this->iframeHeight = $this->getInterfaceAdapter()->getIframeHeight($this->getTransaction()->getTransactionObject(), $params);
		$this->persistTransaction();
		if ($this->getTransaction()->getTransactionObject()->isAuthorizationFailed()) {
			$this->iframeUrl = null;
			$errorMessage = current($this->getTransaction()->getTransactionObject()->getErrorMessages());
			/* @var $errorMessage Customweb_Payment_Authorization_IErrorMessage */
			if (is_object($errorMessage)) {
				$this->errorMessage = $errorMessage->getUserMessage();
			}
			else {
				$this->errorMessage = UnzerCw_Language::_("Failed to initialize transaction with an unknown error");
			}
		}
		if ($this->iframeUrl !== null) {
			$vars = array(
				'iframeUrl' => $this->iframeUrl,
				'iframeHeight' => $this->iframeHeight,
			);
			return $this->renderTemplate('payment/iframe.html', $vars);
		}
		else {
			return $this->renderErrorMessage($this->errorMessage);
		}
	}
	
	protected function getVisibleFormFields() {
		return $this->visibleFormFields;
	}
	
	protected function getFormActionUrl() {
		return $this->formActionUrl;
	}
	
}
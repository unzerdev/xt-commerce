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
class UnzerCw_Adapter_WidgetAdapter extends UnzerCw_Adapter_AbstractAdapter {

	private $visibleFormFields = array();
	private $formActionUrl = null;
	private $widgetHtml = null;
	private $errorMessage = '';

	public function getPaymentAdapterInterfaceName() {
		return 'Customweb_Payment_Authorization_Widget_IAdapter';
	}

	/**
	 * @return Customweb_Payment_Authorization_Widget_IAdapter
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
			$this->formActionUrl = UnzerCw_Util::getControllerUrl('process', 'widget', array(
				'cw_transaction_id' => $this->getTransaction()->getTransactionId())
			);
				
			if ($this->visibleFormFields === null || count($this->visibleFormFields) <= 0 || count($params) > 0) {
				$this->setRedirectionUrl($this->formActionUrl);
			}
				
			$this->persistTransaction();
		}
	}

	public function getWidget() {
		$params = $this->storeAndGetFormParameters();
		$this->widgetHtml = $this->getInterfaceAdapter()->getWidgetHTML($this->getTransaction()->getTransactionObject(), $params);
		$this->persistTransaction();
		if ($this->getTransaction()->getTransactionObject()->isAuthorizationFailed()) {
			$this->widgetHtml = null;
			$errorMessage = current($this->getTransaction()->getTransactionObject()->getErrorMessages());
			/* @var $errorMessage Customweb_Payment_Authorization_IErrorMessage */
			if (is_object($errorMessage)) {
				$this->errorMessage = $errorMessage->getUserMessage();
			}
			else {
				$this->errorMessage = UnzerCw_Language::_("Failed to initialize transaction with an unknown error");
			}
		}
		if ($this->widgetHtml !== null) {
			$vars = array(
				'widget' => $this->widgetHtml,
			);
			return $this->renderTemplate('payment/widget.html', $vars);
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
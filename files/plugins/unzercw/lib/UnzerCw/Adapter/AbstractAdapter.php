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

require_once 'Customweb/Util/Url.php';
require_once 'Customweb/Util/Html.php';

require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/FormDataManager.php';
require_once 'UnzerCw/Entity/Transaction.php';
require_once 'UnzerCw/Entity/PaymentCustomerContext.php';
require_once 'UnzerCw/TransactionContext.php';
require_once 'UnzerCw/OrderStatus.php';
require_once 'UnzerCw/Adapter/IAdapter.php';
require_once 'UnzerCw/PaymentListFormRenderer.php';
require_once 'UnzerCw/FormRenderer.php';
require_once 'UnzerCw/AliasManager.php';


abstract class UnzerCw_Adapter_AbstractAdapter implements UnzerCw_Adapter_IAdapter {

	/**
	 * @var Customweb_Payment_Authorization_IAdapter
	 */
	private $interfaceAdapter;

	/**
	 * @var Customweb_Payment_Authorization_IOrderContext
	 */
	private $orderContext;

	/**
	 * @var int
	 */
	private $orderId = null;

	/**
	 * @var UnzerCw_AbstractPaymentMethod
	 */
	private $paymentMethod;

	/**
	 * @var UnzerCw_Entity_Transaction
	 */
	private $failedTransaction = null;

	/**
	 * @var UnzerCw_Entity_Transaction
	 */
	private $aliasTransaction = null;

	/**
	 * @var int
	 */
	private $aliasTransactionId = null;

	/**
	 * @var UnzerCw_Entity_Transaction
	 */
	private $transaction = null;

	/**
	 * @var string
	 */
	private $redirectUrl = null;

	/**
	 * @var Customweb_Database_Entity_Manager
	 */
	protected $entityManager = null;

	/**
	 * @var Customweb_payment_authorization_IPaymentCustomerContext
	 */
	private $paymentCustomerContext = null;

	public function __construct() {
		$this->entityManager = UnzerCw_Util::getEntityManager();
	}

	final public function prepare(UnzerCw_AbstractPaymentMethod $paymentMethod, Customweb_Payment_Authorization_IOrderContext $orderContext, $orderId = null, $failedTransaction = null, $transaction = null) {
		$this->transaction = $transaction;
		$this->orderContext = $orderContext;
		$this->paymentMethod = $paymentMethod;
		if ($transaction !== null) {
			if (!($transaction instanceof UnzerCw_Entity_Transaction)) {
				throw new Exception("The transaction must be of instance UnzerCw_Entity_Transaction.");
			}
// 			$this->orderId = $transaction->getOrderId();
		}
		$this->orderId = $orderId;
		$this->failedTransaction = $failedTransaction;


		$this->prepareAliasManager();
		$this->prepareTransaction();
		$this->prepareAdapter();

		// Make sure, that in case of an error a direct redirection is done.
		if ($this->getTransaction() !== null && $this->getTransaction()->getTransactionObject()->isAuthorizationFailed()) {
			$this->setRedirectionUrl(Customweb_Util_Url::appendParameters(
					$this->getTransaction()->getTransactionObject()->getTransactionContext()->getFailedUrl(),
					$this->getTransaction()->getTransactionObject()->getTransactionContext()->getCustomParameters()
			));
		}
	}

	/**
	 * @return UnzerCw_AbstractPaymentMethod
	 */
	final protected function getPaymentMethod() {
		return $this->paymentMethod;
	}

	final protected function prepareAliasManager() {
		$this->aliasTransaction = null;
		$this->aliasTransactionId = null;

		
	}


	final protected function prepareTransaction() {
		if ($this->orderId === null) {
			return;
		}

		if ($this->transaction !== null) {
			return;
		}

		$failedTransaction = null;
		if ($this->failedTransaction !== null) {
			$failedTransaction = $this->failedTransaction->getTransactionObject();
		}

		// New transaction
		$orderContext = $this->getOrderContext();
		$transactionContext = new UnzerCw_TransactionContext($orderContext, $this->aliasTransactionId);
		$order = $orderContext->getOrder();
		$order->_updateOrderStatus(UnzerCw_OrderStatus::getPendingStatusId(), 'Start Processing Payment' , false, false, 'IPN');
		unset($_SESSION['unzercw_checkout_id'][$orderContext->getPaymentMethod()->getPaymentMethodName()]);

		$this->transaction = $transactionContext->getDatabaseTransaction();
		$transactionObject = $this->getInterfaceAdapter()->createTransaction($transactionContext, $failedTransaction);
		$this->transaction->setTransactionObject($transactionObject);
		$this->persistTransaction();
	}

	final protected function persistTransaction() {
		if ($this->transaction !== null) {
			$this->entityManager->persist($this->transaction);
		}
	}

	final protected function persistCustomerContext() {
		if ($this->getPaymentCustomerContext() instanceof UnzerCw_Entity_PaymentCustomerContext) {
			$this->entityManager->persist($this->getPaymentCustomerContext());
		}
	}

	final public function getTransaction() {
		return $this->transaction;
	}

	final public function setInterfaceAdapter(Customweb_Payment_Authorization_IAdapter $interface) {
		$this->interfaceAdapter = $interface;
	}

	public function getInterfaceAdapter() {
		return $this->interfaceAdapter;
	}

	final public function getAliasFormContent() {
		
	}

	public function getConfirmationPageHtml() {
		return $this->renderTemplate('payment/confirmation_form.html', $this->getConfirmationPageVariables());
	}

	public function getConfirmationPageVariables() {
		$templateVars = array(
			'paymentMethodName' => $this->paymentMethod->getPaymentMethodDisplayName(),
			'paymentMachineName' => $this->paymentMethod->getPaymentMethodName(),
		);

		

		if ($this->isConfirmationFormActive()) {
			$visibleFormFields = $this->getVisibleFormFields();
			if ($visibleFormFields !== null && count($visibleFormFields) > 0) {
				$renderer = new UnzerCw_PaymentListFormRenderer($this->paymentMethod->getPaymentCode());
				$templateVars['visibleFormFields'] = $renderer->renderElements($visibleFormFields);
			}
		}

		return $templateVars;
	}

	public function processOrderConfirmationRequest() {
		if ($this->isRedirectionSupported()) {
			return $this->getRedirectionUrl();
		}
		else {
			return UnzerCw_Util::getControllerUrl('process', 'payment', array('cw_transaction_id' => $this->getTransaction()->getTransactionId()));
		}
	}


	
	final public function isRedirectionSupported(){
		if (false) {
			return false;
		}

		if ($this->getRedirectionUrl() === null) {
			return false;
		}
		else {
			return true;
		}
	}
	

	final public function getRedirectionUrl(){
		return $this->redirectUrl;
	}

	final protected function setRedirectionUrl($redirectUrl) {
		$this->redirectUrl = $redirectUrl;
		return $this;
	}

	public function getPaymentPageHtml(){
		return $this->renderTemplate('payment/form.html', $this->getPaymentPageVariables());
	}

	protected function getPaymentPageVariables() {
		if ($this->getTransaction() === null) {
			throw new Exception("The payment page can only be generated with a transaction present.");
		}
		$templateVars = array(
			'paymentMethodName' => $this->paymentMethod->getPaymentMethodDisplayName(),
			'paymentMachineName' => $this->paymentMethod->getPaymentMethodName(),
		);

		$actionUrl = $this->getFormActionUrl();
		if (!empty($actionUrl)){
			$templateVars['formActionUrl'] = $actionUrl;
		}

		

		$visibleFormFields = $this->getVisibleFormFields();
		if ($visibleFormFields !== null && count($visibleFormFields) > 0) {
			$renderer = new UnzerCw_FormRenderer($this->paymentMethod->getPaymentCode());
			$templateVars['visibleFormFields'] = $renderer->renderElements($visibleFormFields);
		}

		$hiddenFormFields = $this->getHiddenFormFields();
		if ($hiddenFormFields !== null && count($hiddenFormFields) > 0) {
			$templateVars['hiddenFields'] = Customweb_Util_Html::buildHiddenInputFields($hiddenFormFields);
		}

		$templateVars['additionalOutput'] = $this->getAdditionalFormHtml();
		$templateVars['buttons'] = $this->getOrderConfirmationButton();

		return $templateVars;
	}

	final protected function isConfirmationFormActive() {
		return $this->getPaymentMethod()->getPaymentMethodConfigurationValue('payment_form_position') == 'confirmation_page' ||
			$this->getPaymentMethod()->getPaymentMethodConfigurationValue('payment_form_position') == 'payment_list_page';
	}

	final protected function isPaymentListFormActive() {
		return false;
	}

	final protected function getOrderContext() {
		return $this->orderContext;
	}

	final protected function getAliasTransactionObject() {
		$aliasTransactionObject = null;
		if($this->aliasTransactionId === 'new') {
			$aliasTransactionObject = 'new';
		}
		if ($this->aliasTransaction !== null) {
			$aliasTransactionObject = $this->aliasTransaction->getTransactionObject();
		}

		return $aliasTransactionObject;
	}

	final protected function getFailedTransactionObject() {
		$failedTransactionObject = null;
		$orderContext = $this->getOrderContext();
		if ($this->failedTransaction !== null && $this->failedTransaction->getCustomerId() !== null && $this->failedTransaction->getCustomerId() == $orderContext->getCustomerId()) {
			$failedTransactionObject = $this->failedTransaction->getTransactionObject();
		}
		return $failedTransactionObject;
	}

	final protected function getPaymentCustomerContext() {
		if ($this->paymentCustomerContext === null) {
			$this->paymentCustomerContext = UnzerCw_Util::getPaymentCustomerContext($this->getOrderContext()->getCustomerId());
		}

		return $this->paymentCustomerContext;
	}

	final protected function renderErrorMessage($message) {
		$vars = array(
			'errorMessage' => $message,
		);
		return $this->renderTemplate('payment/error.html', $vars);
	}

	protected function getFormActionUrl() {
		return null;
	}

	protected function getAdditionalFormHtml() {
		return '';
	}

	protected function prepareAdapter() {
		// Override
	}

	protected function getVisibleFormFields() {
		return null;
	}

	protected function getHiddenFormFields() {
		return null;
	}

	protected function getOrderConfirmationButton() {
		return $this->renderTemplate('payment/buttons.html', array());
	}

	final protected function storeAndGetFormParameters() {
		$params = UnzerCw_Util::getCleanRequestArray();
		if (count($params) <= 0) {
			$formHandler = new UnzerCw_FormDataManager($this->getOrderContext());
			$params = $formHandler->getFormData();
		}
		else if (count($params) > 0 && count($this->getVisibleFormFields()) > 0) {
			$formHandler = new UnzerCw_FormDataManager($this->getOrderContext());
			$formHandler->setFormData($params);
		}
		return $params;
	}

	final protected function renderTemplate($file, array $variables = array()) {
		$template = new Template();
		$template->getTemplatePath($file, 'unzercw', '', 'plugin');

		return $template->getTemplate('unzercw', $file, $variables);
	}


}
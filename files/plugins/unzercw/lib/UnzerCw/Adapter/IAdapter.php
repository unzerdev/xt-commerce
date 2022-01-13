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

interface UnzerCw_Adapter_IAdapter {
	
	/**
	 * This method must be called before any other method is invoked. The method does the following things:
	 * - Sets the inner state depending on the given parameters.
	 * - Handles the input provided by the user and sets the inner state depending on it (e.g. alias manager).
	 *
	 * @param UnzerCw_AbstractPaymentMethod $paymentMethod The payment method to use for creation of the form.
	 * @param Customweb_Payment_Authorization_IOrderContext $orderContext The order context to use for creation of the form.
	 * @param string $orderId (Optional) The order id to use.
	 * @param UnzerCw_Entity_Transaction $failedTransaction (Optional) The previous failed transaction of a series of transactions.
	 * @param UnzerCw_Entity_Transaction $transaction (Optional) Existing transaction. In case a transaction exists already.
	 */
	public function prepare(UnzerCw_AbstractPaymentMethod $paymentMethod, Customweb_Payment_Authorization_IOrderContext $orderContext, $orderId = null, $failedTransaction = null, $transaction = null);
	
	/**
	 * This method returns the alias manager form content. The method does not 
	 * return '<form>' tag. This has to be build by the client.
	 * 
	 * @return string
	 */
	public function getAliasFormContent();
	
	/**
	 * This method returns the transaction created during the preparation phase.
	 * 
	 * This method will return when no order id is provided.
	 * 
	 * @return UnzerCw_Entity_Transaction
	 */
	public function getTransaction();
	
	/**
	 * This method returns the content of displayed on the confirmation page. This method may
	 * contain form fields or JavaScript. The HTML must not contain a '<form>'-tag.
	 * 
	 * @return string
	 */
	public function getConfirmationPageHtml();
	
	/**
	 * This method is called when the customer has confirm the order. The method may return a redirection URL, otherwise
	 * the method must insure that the process is died.
	 * 
	 * @return string|void
	 */
	public function processOrderConfirmationRequest();
	
	/**
	 * This method checks if a redirection is supported and make sense. It this method returns true
	 * a redirection to the indicated URL should be performed.
	 * 
	 * Requires the order id.
	 * 
	 * @return boolean
	 */
	public function isRedirectionSupported();
	
	/**
	 * In case header redirection is supported this method returns the redirection URL. 
	 * 
	 * Requires the order id.
	 * 
	 * @return string
	 */
	public function getRedirectionUrl();

	/**
	 * Create the full payment page including:
	 * - Error Message (if set by failed transaction or during initialization)
	 * - Alias Manager Form
	 * - Payment Form
	 * - Payment Form Buttons
	 * 
	 * Requires the order id.
	 *
	 * @return string Html for the checkout page
	 */
	public function getPaymentPageHtml();
	
	/**
	 * @return string
	 */
	public function getPaymentAdapterInterfaceName();
	
	/**
	 * @return Customweb_Payment_Authorization_IAdapter
	 */
	public function getInterfaceAdapter();
	
	/**
	 * @param Customweb_Payment_Authorization_IAdapter $adapter
	 */
	public function setInterfaceAdapter(Customweb_Payment_Authorization_IAdapter $adapter);

}
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


class UnzerCw_AliasManager {
	
	/**
	 * @var Customweb_Payment_Authorization_IOrderContext
	 */
	private $orderContext;
	
	private $selectedTransactionId = null;
	private $selectedTransaction = null;
	
	public function __construct(Customweb_Payment_Authorization_IOrderContext $orderContext) {
		$this->orderContext = $orderContext;
		$this->loadSelectedTransaction();
	}
	
	/**
	 * Process the user input. The return value indicates if the state changed. Means if the user has done 
	 * some changes regarding the alias.
	 * 
	 * @param array $data
	 * @return boolean
	 */
	public function processUserInput($data) {
		$methodName = $this->getOrderContext()->getPaymentMethod()->getPaymentMethodName();
		if (isset($data['unzercw_alias_use_new_card'][$methodName])) {
			$this->setSelectedAlias('no_alias');
		}
		else if(isset($data['unzercw_alias_use_stored_card'][$methodName])) {
			$this->setSelectedAlias(null);
		}
		else if (isset($data['unzercw_alias'][$methodName]) !== NULL && !empty($data['unzercw_alias'][$methodName])) {
			$this->setSelectedAlias((int)$data['unzercw_alias'][$methodName]);
		}
		else if (isset($data['unzercw_create_new_alias'][$methodName]) && $data['unzercw_create_new_alias'][$methodName] == 'on') {
			$this->setSelectedAlias('new');
		}
		else if(isset($data['unzercw_update_alias'][$methodName])) {
 			$this->setSelectedAlias('no_alias');
		}
		else if (isset($data['unzercw_create_new_alias_present'][$methodName]) == 'active') {
			$this->setSelectedAlias('no_alias');
		}
		$this->loadSelectedTransaction();
		
		if (isset($data['unzercw_update_alias'][$methodName]) || isset($data['unzercw_alias_use_stored_card'][$methodName]) || isset($data['unzercw_alias_use_new_card'][$methodName])) {
			return true;
		}
		else {
			return false;
		}
	}
	
	final public function getSelectedAliasTransactionId() {
		return $this->selectedTransactionId;
	}
	
	/**
	 * @return UnzerCw_Entity_Transaction
	 */
	final public function getSelectedAliasTransaction() {
		return $this->selectedTransaction;
	}
	
	final protected function loadSelectedTransaction() {
		$aliasManagerValue = $this->getSelectedAlias();
		$this->selectedTransactionId = null;
		if (!empty($aliasManagerValue)) {
			if ($aliasManagerValue != 'no_alias') {
				$this->selectedTransactionId = $aliasManagerValue;
			}
		}
		else {
			$aliasTransactions = $this->getAliasTransactions();
			if (count($aliasTransactions) > 0) {
				$current = current($aliasTransactions);
				$this->selectedTransactionId = $current->getTransactionId();
			}
			else {
				$this->selectedTransactionId = 'new';
			}
		}
		if ($this->selectedTransactionId !== null && $this->selectedTransactionId !== 'new') {
			$aliasTransaction = UnzerCw_Util::loadTransaction($this->selectedTransactionId);
			if ($aliasTransaction->getCustomerId() === $this->getOrderContext()->getCustomerId()) {
				$this->selectedTransaction = $aliasTransaction;
			}
			else {
				$this->selectedTransactionId = 'new';
			}
		}
	}
	
	/**
	 * @return object[]
	 */
	final public function getAliasTransactions() {
		$aliasTransactions = UnzerCw_Util::getAliasHandler()->getAliasTransactions($this->getOrderContext());
		return $aliasTransactions;
	}
	
	final public function resetSelectedAlias() {
		$this->setAliasManagerValue(null);
	}

	final protected function setSelectedAlias($value) {
		$key = $this->getSessionKey();
		$_SESSION[$key] = $value;
		return $this;
	}
	
	final protected function getSelectedAlias() {
		$key = $this->getSessionKey();
		if (isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}
		return null;
	}
	
	private function getSessionKey() {
		return 'selected_alias_unzercw_' . $this->getOrderContext()->getPaymentMethod()->getPaymentMethodName();
	}
	
	/**
	 * @return Customweb_Payment_Authorization_IOrderContext
	 */
	final protected function getOrderContext() {
		return $this->orderContext;
	}
}




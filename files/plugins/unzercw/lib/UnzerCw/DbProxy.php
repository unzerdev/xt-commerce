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




/**
 * We need to inheit from ADOConnection to avoid conflicts. If this does not work we may want
 * to remove this completly. We only need this to support in older versions proper setting ordering
 * within our plugin.
 */
class UnzerCw_DbProxy extends ADOConnection{

	/**
	 * @var stdClass
	 */
	private $object = null;

	public function __construct($object) {
		$this->object = $object;
	}


	public function __isset($name) {
		if (isset($this->object->{$name})) {
			return true;
		}
		else {
			return false;
		}
	}

	protected function getProxyObject() {
		return $this->object;
	}

	public function __unset($name) {
		unset($this->object->{$name});
	}

	public function __set($name, $value) {
		$this->object->{$name} = $value;
	}

	public function __get($name) {
		return $this->object->{$name};
	}

	public function __call($method, $args) {
		return call_user_func_array(array($this->object, $method), $args);
	}

	public function __wakeup() {
		return $this->object->__wakeup();
	}

	public function __sleep() {
		return $this->object->__sleep();
	}

	public function Execute($sql,$inputarr=false) {
		if (strpos($sql, "SELECT * FROM " . TABLE_PLUGIN_CONFIGURATION . " where plugin_id = ") === 0 && strpos($sql, 'sort_order') === false ) {
			$sql .= ' ORDER BY sort_order';
		}

		return $this->getProxyObject()->Execute($sql, $inputarr);
	}



}
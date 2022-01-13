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

require_once 'Customweb/Core/Util/System.php';
require_once 'Customweb/Core/Url.php';

require_once 'UnzerCw/Util.php';

require_once 'Customweb/Core/Util/Xml.php';

abstract class UnzerCw_Backend_Controller_Abstract {
	
	private $viewData = array();
	
	protected function getViewData() {
		return $this->viewData;
	}
	
	protected function setViewData(array $data) {
		$this->viewData = $data;
		return $this;
	}
	
	protected function addVariable($key, $value) {
		$this->viewData[$key] = $value;
		return $this;
	}
	
	protected function fetchView($viewName) {
		$fileName = _SRV_WEBROOT . 'plugins/unzercw/templates/admin/' . $viewName . '.php';
		if (!file_exists($fileName)) {
			throw new Exception("Could not fetch view '" . $viewName . "'. Tried with file '" . $fileName . "'.");
		}
		
		extract($this->getViewData());
		ob_start();
		require $fileName;
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	protected function getActionUrl($actionName, array $params = array()) {
		return self::getControllerUrl($this->getControllerName(), $actionName, $params);
	}

	protected function getRemoteWindow($title, $action, $params, $width = 960, $height = 600, $controller = null) {
		if ($controller === null) {
			$controller = $this->getControllerName();
		}
		$extF = new ExtFunctions();
		return $extF->_RemoteWindow($title, $title, self::getControllerUrl($controller, $action, $params), '', array(), $width, $height) . ' new_window.show()';
	}
	

	protected function createButton($action, $title, $params) {
		$extF = new ExtFunctions();
		$submitBtn = PhpExt_Button::createTextButton(
				$title,
				new PhpExt_Handler(
						PhpExt_Javascript::stm(
							$this->getRemoteWindow($title, $action, $params)
						),
						PhpExt_Javascript::stm(
							'window["current_window"] = new_window;'
						)
				)
		);
		$submitBtn->setType(PhpExt_Button::BUTTON_TYPE_SUBMIT);
		return $submitBtn;
	}

	public function getControllerName() {
		$className = get_class($this);
		return str_ireplace('UnzerCw_Backend_Controller_', '', $className);
	}
	
	public static function getControllerUrl($controllerName, $action, array $params = array(), $absolute = true) {
		$params['controller'] = $controllerName;
		$params['action'] = $action;
		$prefix = '';
		if ($absolute) {
			$prefix = dirname(Customweb_Core_Util_System::getRequestUrl()) . '/';
		}
	
		return $prefix . 'row_actions.php?type=unzercw&' . Customweb_Core_Url::parseArrayToString($params);
	}
	
	/**
	 * @throws Exception
	 * @return UnzerCw_Entity_Transaction
	 */
	protected function getTransaction() {
		if (isset($_REQUEST['cw_transaction_id'])) {
			return UnzerCw_Util::loadTransaction($_REQUEST['cw_transaction_id']);
		}
		
		if (isset($_REQUEST['transaction_id'])) {
			return UnzerCw_Util::loadTransaction($_REQUEST['transaction_id']);
		}
		
		throw new Exception("No transaction id given.");
	}
	
}
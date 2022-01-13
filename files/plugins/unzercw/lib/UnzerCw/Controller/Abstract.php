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


abstract class UnzerCw_Controller_Abstract {
	
	private $siteName = null;
	private $currentActionName = null;
	private $currentControllerName = null;
	private $javaScriptFiles = array();
	private $cssFiles = array();
	private $templateVariables = array();
	
	public function __construct() {
		$this->setSiteName(UnzerCw_Language::_("Payment"));
	}
	
	public function setActionName($actionName) {
		$this->currentActionName = $actionName;
		return $this;
	}
	
	public function getActionName() {
		return $this->currentActionName;
	}
	
	public function setControllerName($controllerName) {
		$this->currentControllerName = $controllerName;
		return $this;
	}
	
	public function getControllerName() {
		return $this->currentControllerName;
	}
	
	protected function setSiteName($name) {
		$this->siteName = $name;
		return $this;
	}
	
	protected function addJavaScriptFile($filepath) {
		$this->javaScriptFiles[] = $filepath;
		return $this;
	}
	
	protected function getJavaScriptFiles() {
		return $this->javaScriptFiles;
	}
	
	protected function addCssFile($filepath) {
		$this->cssFiles[] = $filepath;
		return $this;
	}
	
	protected function getCssFiles() {
		return $this->cssFiles;
	}
	
	protected function getSiteName() {
		return $this->siteName;
	}
	
	
	protected function getBreadcrumbItems() {
		return $this->breadcrumbLinks;
	}
	
	protected function getUrl(array $parameters = array(), $action = null, $controller = null) {
		if ($controller === null) {
			$controller = $this->getControllerName();
		}
		if ($action === null) {
			$action = $this->getActionName();
		}
	
		return UnzerCw_Util::getControllerUrl($controller, $action, $parameters);
	}
	
	protected function assign($key, $value) {
		$this->templateVariables[$key] = $value;
	}
	
	protected function display($templateFile) {
		$template = new Template();
		$template->getTemplatePath($templateFile, 'unzercw', '', 'plugin');
		$GLOBALS['page_data'] = $template->getTemplate('unzercw', $templateFile, $this->templateVariables);
		return $GLOBALS['page_data'];
	}
}
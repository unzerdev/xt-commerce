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
 * This class handles a given request and dispatch the reqeust to the 
 * corresponding controller.
 * 
 * @author Thomas Hunziker
 *
 */
class UnzerCw_Dispatcher
{

	private $controllerName = null;
	private $actionName = 'index';
	private $controllersDir = null;
	private $controllerInstance = null;
	private $classPrefix = null;
	
	public function __construct($controllersDir = null, $classPrefix = null) {
		if ($controllersDir === null) {
			$this->controllersDir = dirname(__FILE__) . '/Controller';
		}
		else {
			$this->controllersDir = $controllersDir;
		}
		
		if ($classPrefix === null) {
			$this->classPrefix = 'UnzerCw_Controller_';
		}
		else {
			$this->classPrefix = $classPrefix;
		}
	}
	
	public function dispatch() {
		
		if (isset($_REQUEST['controller'])) {
			$this->controllerName = str_replace("/", "", strip_tags(strtolower($_REQUEST['controller'])));	
		}
		else {
			throw new Exception("No controller given.");
		}
		
		if (isset($_REQUEST['action'])) {
			$this->actionName = strtolower($_REQUEST['action']);
		}
		
		$filePath = $this->getControllerFilePath();
		require_once $filePath;
		
		$this->controllerInstance = $this->getControllerInstance();
		$this->invokeAction();
	}
	
	private function invokeAction() {
		
		$expectedActionMethodName = $this->actionName . 'Action';
		$methods = get_class_methods($this->controllerInstance);
		$effectiveMethodName = null;
		foreach ($methods as $method) {
			if (strtolower($method) == strtolower($expectedActionMethodName)) {
				$effectiveMethodName = $method;
				break;
			}
		}
		
		if ($effectiveMethodName === null) {
			throw new Exception("Could not find action method '" . $expectedActionMethodName . "' in controller '" . get_class($this->controllerInstance) . "'.");
		}
		
		call_user_func(array($this->controllerInstance, $effectiveMethodName));
	}
		
	private function getControllerInstance() {
		$declaredClasses = get_declared_classes();
		$expectedClassName = $this->classPrefix . $this->controllerName;
		$effectiveClass = null;
		foreach($declaredClasses as $class) {
			if (strtolower($class) == strtolower($expectedClassName)) {
				$effectiveClass = $class;
				break;
			}
		}
		
		if ($effectiveClass === null) {
			throw new Exception("Could not find controller class for controller '" . $this->controllerName . "'. Expected name: '" . $expectedClassName . "'.");
		}
		
		$controller = new $effectiveClass();
		
		
		if (method_exists($controller, 'setActionName')) {
			$controller->setActionName($this->actionName);
		}
		
		if (method_exists($controller, 'setControllerName')) {
			$controller->setControllerName($this->controllerName);
		}
		
		return $controller;
	}
	
	private function getControllerFilePath() {
		$filePath = $this->controllersDir . '/' . $this->controllerName . '.php';
		if (!file_exists($filePath)) {
			if ($handle = opendir($this->controllersDir)) {
					
				/* Das ist der korrekte Weg, ein Verzeichnis zu durchlaufen. */
				while (false !== ($file = readdir($handle))) {
					if (strtolower($file) == strtolower($this->controllerName . '.php')) {
						$filePath = $this->controllersDir . '/' . $file;
						break;
					}
				}
				closedir($handle);
			}
			else {
				throw new Exception("Could not open dir '" . $this->controllersDir . "'.");
			}
		}
		
		if (!file_exists($filePath)) {
			throw new Exception("Could not find controller '" . $this->controllerName . "'");
		}
		
		return $filePath;
	}
	
	
}
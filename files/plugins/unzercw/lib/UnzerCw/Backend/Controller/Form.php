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

require_once 'Customweb/Form/IButton.php';
require_once 'Customweb/Payment/BackendOperation/Form.php';
require_once 'Customweb/IForm.php';
require_once 'Customweb/Core/Exception/CastException.php';

require_once 'UnzerCw/Util.php';
require_once 'UnzerCw/Backend/FormRenderer.php';
require_once 'UnzerCw/ConfigurationAdapter.php';
require_once 'UnzerCw/Backend/Controller/Abstract.php';


class UnzerCw_Backend_Controller_Form extends UnzerCw_Backend_Controller_Abstract{

	public function viewAction() {
		$form = $this->getCurrentForm();

		if (isset($_REQUEST['store_id'])) {
			UnzerCw_ConfigurationAdapter::setCurrentStoreId($_REQUEST['store_id']);
		}
		else {
			UnzerCw_ConfigurationAdapter::setCurrentStoreId('default');
		}
		$renderer = new UnzerCw_Backend_FormRenderer();
		$renderer->setAddJs(false);
		$buttons = null;
		if ($form->isProcessable()) {
			$this->renderButtons($form, $form->getMachineName());
			$form = new Customweb_Payment_BackendOperation_Form($form);
			$form->setTargetUrl($this->getActionUrl('save', array('form' => $form->getMachineName())))->setRequestMethod(Customweb_IForm::REQUEST_METHOD_POST)->setButtons(array());

		}
		$this->renderJS($renderer->renderElementsJavaScript($form->getElements(), $form->getMachineName()));
		$this->addVariable('form', $form);
		$this->addVariable('formHtml', $renderer->renderForm($form));

		echo $this->fetchView('form/view');
	}

	public function saveAction() {
		$form = $this->getCurrentForm();

		$params = $_REQUEST;
		if (!isset($params['button_pressed'])) {
			throw new Exception("No button returned.");
		}
		$pressedButton = null;
		foreach ($form->getButtons() as $button) {
			if ($button->getMachineName() == $params['button_pressed']) {
				$pressedButton = $button;
				break;
			}
		}

		if ($pressedButton === null) {
			throw new Exception("Could not find pressed button.");
		}
		UnzerCw_Util::getBackendFormAdapter()->processForm($form, $pressedButton, $params);

		echo 'success';
	}

	protected function renderButtons(Customweb_IForm $form, $jsFunctionPostfix = '') {
		$panel = new PhpExt_Panel('form');
		$panel->setButtonAlign(PhpExt_Ext::HALIGN_LEFT);
		$postfix = $jsFunctionPostfix;


		foreach ($form->getButtons() as $button) {
			if (!($button instanceof Customweb_Form_IButton)) {
				throw new Customweb_Core_Exception_CastException('Customweb_Form_IButton');
			}
			$successAction ="var form = $('#".$form->getId()."');
							$.ajax({
								type: 'POST',
								url: form[0].action+'&button_pressed=" . $button->getMachineName() . "',
								data: form.serialize(),
								success: function (response) {
									form.replaceWith(response);
									contentTabs.getActiveTab().getUpdater().refresh();
								}
							});";
			$validation = '';
			if($button->isJSValidationExecuted()) {
				$validation = "
						if (typeof cwValidateFields".$postfix." != 'undefined') {
							cwValidateFields".$postfix."(function(valid){
								".$successAction."
							}
							,function(errors, valid){alert(errors[Object.keys(errors)[0]]);});
							return false;
						}";
			}
			$exButton = PhpExt_Button::createTextButton((string)$button->getTitle(),
					new PhpExt_Handler(PhpExt_Javascript::inlineStm("

						".$validation.$successAction))
			);
			$exButton->setType(PhpExt_Button::BUTTON_TYPE_SUBMIT);
			$panel->addButton($exButton);

		}
		$panel->setRenderTo(PhpExt_Javascript::variable("Ext.get('form_panel_" . self::cleanId($form->getId()) . "')"));

		echo '<script type="text/javascript">';
		echo "\n";
		echo PhpExt_Ext::onReady(
				'UnzerCwLineItemGrid.init();',
				$panel->getJavascript(false, 'buttonPanel')
		);
		echo "\n";
		echo '</script>';
	}


	protected function renderJS($js){
		echo '<script type="text/javascript">';
		echo "\n";
		echo PhpExt_Ext::onReady($js);
		echo "\n";
		echo '</script>';
	}

	public static function cleanId($id) {
		return preg_replace('/[^a-z0-9_]+/i', '', $id);
	}


	/**
	 * @return Customweb_Payment_BackendOperation_IForm
	 */
	protected function getCurrentForm() {
		$adapter = UnzerCw_Util::getBackendFormAdapter();

		if ($adapter !== null && isset($_GET['form'])) {
			$forms = $adapter->getForms();
			$formName = $_GET['form'];
			$currentForm = null;
			foreach ($forms as $form) {
				if ($form->getMachineName() == $formName) {
					return $form;
				}
			}
		}

		die('No form is set or no backend adapter present in the container.');
	}

}
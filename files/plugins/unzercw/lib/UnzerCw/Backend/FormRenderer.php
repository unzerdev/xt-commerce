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

require_once 'Customweb/Form/Renderer.php';
require_once 'Customweb/Form/WideElement.php';

require_once 'UnzerCw/Util.php';


class UnzerCw_Backend_FormRenderer extends Customweb_Form_Renderer
{
	
	protected function renderElementScope(Customweb_Form_IElement $element)
	{
		if ($this->getConfigurationAdapter()->getStoreHierarchy() === null) {
			return '';
		}
		return parent::renderElementScope($element);
	}
	
	/**
	 * @return UnzerCw_ConfigurationAdapter
	 */
	private function getConfigurationAdapter() {
		return UnzerCw_Util::createContainer()->getBean('UnzerCw_ConfigurationAdapter');
	}
	
	public function getControlCssClass() {
		return 'x-form-element';
	}
	
	public function getElementLabelCssClass() {
		return 'x-form-item-label';
	}
	
	public function renderElementPostfix(Customweb_Form_IElement $element) {
		return '<div class="x-form-clear-left"></div>' . parent::renderElementPostfix($element);
	}
	
	public function renderElementGroupPrefix(Customweb_Form_IElementGroup $elementGroup) {
		return '<div class="x-panel x-form-label-left unzercw-backend-form"><div class="x-panel-header x-unselectable"><span class="x-panel-header-text">' . $elementGroup->getTitle() . '</span></div>
				<div style="background: #FFF; padding: 5px" class="x-panel-body">';
	}
	
	public function renderElementGroupPostfix(Customweb_Form_IElementGroup $elementGroup) {
		return '</div></div>';
	}
	
	public function renderElementGroupTitle(Customweb_Form_IElementGroup $elementGroup) {
		return '';
	}
	
	public function renderElementPrefix(Customweb_Form_IElement $element)
	{
		$classes = 'x-form-item x-tab-item unzercw-element';
		if ($element instanceof Customweb_Form_WideElement) {
			$classes .= ' wide-element';
		}
		
		return '<div class="' . $classes . '" id="' . $element->getElementId() . '">';
	}
	
	protected function renderElementDescription(Customweb_Form_IElement $element)
	{
		return '<div class="x-form-clear-left"></div><div class="' . $this->getCssClassPrefix() . $this->getDescriptionCssClass() . '">' . $element->getDescription() . '</div>';
	}
	
	
}
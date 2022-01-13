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

require_once 'Customweb/Mvc/Layout/IRenderer.php';



/**
 * @Bean
 */
class UnzerCw_LayoutRenderer implements Customweb_Mvc_Layout_IRenderer {

	public function render(Customweb_Mvc_Layout_IRenderContext $context) {
		
		$mainContent = $context->getMainContent();
		foreach ($context->getCssFiles() as $css) {
			$mainContent .= '<link href="' . $css . '" rel="stylesheet" />';
		}
		foreach ($context->getJavaScriptFiles() as $js) {
			$mainContent .= '<script type="text/javascript" src="' . $js . '"></script>';
		}
		
		$template = new Template();
		$tpl_data = array(
			'content' => $mainContent, 
			'message' => $GLOBALS['message_data'], 
			'account' => $GLOBALS['account'], 
			'page' => 'unzercw_endpoint', 
			'show_index_boxes' => $GLOBALS['show_index_boxes'], 
			'registered_customer' => $_SESSION['registered_customer'],
			'top_navigation' => $GLOBALS['brotkrumen']->_output()
		);
		
		ob_start();
		$show_shop_content = $template->getTemplate('smarty', '/index.html', $tpl_data);
		$GLOBALS['show_shop_content'] = $show_shop_content;
		extract($GLOBALS);
		include _SRV_WEBROOT._SRV_WEB_CORE . 'display.php';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

}
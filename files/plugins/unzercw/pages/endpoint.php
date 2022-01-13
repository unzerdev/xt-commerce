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

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require _SRV_WEBROOT . 'plugins/unzercw/init.php';

require_once 'Customweb/Core/Http/ContextRequest.php';
require_once 'Customweb/Payment/Endpoint/Dispatcher.php';
require_once 'Customweb/Core/Http/Response.php';

require_once 'UnzerCw/Util.php';


try {
	$dispatcher = new Customweb_Payment_Endpoint_Dispatcher(UnzerCw_Util::getEndpointAdapter(), UnzerCw_Util::createContainer(), array(
			0 => 'Customweb_Unzer',
 			1 => 'Customweb_Payment_Authorization',
 		));
	$response = $dispatcher->dispatch(Customweb_Core_Http_ContextRequest::getInstance());
	$response = new Customweb_Core_Http_Response($response);

	header_remove("Set-Cookie");

	$response->send();

	// We reset here the cart. Any invocation of this page should not modify the cart
	// in any way. As such we restore the cart here from the copy when such exists.
	// We need this to avoid that the products are removed from the cart when they
	// are not anymore in the stock.
	if (isset($GLOBALS['originalCart'])) {
		$_SESSION['cart'] = $GLOBALS['originalCart'];
	}
	die();
}
catch (Exception $e) {
	echo $e->getMessage();
	echo '<br />';
	echo '<br />';

	echo '<pre>';
	echo $e->getTraceAsString();
	echo '</pre>';
	die();
}




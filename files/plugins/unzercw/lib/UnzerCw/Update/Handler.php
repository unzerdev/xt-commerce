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

require_once 'Customweb/Payment/Update/ContainerHandler.php';
require_once 'Customweb/Core/Logger/Factory.php';

require_once 'UnzerCw/Util.php';



/**
 * 
 * @author Thomas Hunziker
 *
 */
class UnzerCw_Update_Handler extends Customweb_Payment_Update_ContainerHandler {
	
	public function __construct() {
		parent::__construct(UnzerCw_Util::getEntityManager(), UnzerCw_Util::createContainer(), 'UnzerCw_Entity_Transaction', UnzerCw_Util::getDriver());
	}
	
	public function log($message, $type) {
		Customweb_Core_Logger_Factory::getLogger(get_class($this))->log($type, $message);
	}
	
}
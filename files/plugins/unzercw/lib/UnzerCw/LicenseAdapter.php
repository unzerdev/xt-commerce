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

require_once 'Customweb/Core/Http/ContextRequest.php';
require_once 'Customweb/Licensing/UnzerCw/IShopAdapter.php';

require_once 'UnzerCw/Util.php';



final class UnzerCw_LicenseAdapter implements Customweb_Licensing_UnzerCw_IShopAdapter {

	public static function store($data) {
		try {
			UnzerCw_Util::getStorageAdapter()->write('wer1123139sfkl14wer1123139sfkl14kelwelger23434sdf214eard', 'response', $data);
		} catch (Exception $e) {}
	}

	public static function load() {
		try {
			return UnzerCw_Util::getStorageAdapter()->read('wer1123139sfkl14wer1123139sfkl14kelwelger23434sdf214eard', 'response');
		} catch(Exception $e) {}
	}
	
	public static function getDomain() {
		try {
			if (($row = UnzerCw_Util::getDriver()->query('SELECT shop_domain FROM ' . TABLE_MANDANT_CONFIG . ' WHERE shop_id = 1')->fetch()) !== false) {
				if (isset($row['shop_domain'])) {
					return $row['shop_domain'];
				}
			}
		}
		catch(Exception $e) {
			// Ignore
		}
		return Customweb_Core_Http_ContextRequest::getInstance()->getHost();
	}

}

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

class UnzerCw_ErrorHandler {

	private $backup;

	public function start() {
		$this->backup = set_error_handler(array($this, 'convertToException'));
	}

	public function convertToException($num, $str, $file, $line, $context = null) {

		$message = null;
		if ($num == E_NOTICE || $num == E_USER_NOTICE || $num == E_DEPRECATED || $num == E_USER_DEPRECATED || $num == E_WARNING || $num == E_USER_WARNING || $num == E_STRICT) {
			if (stristr($file, 'unzercw') !== false) {
				$message = '[Notice] ' . $str . ' on line ' . $line . ' in file ' . $file;
			}
		}
		else {
			$message = $str . ' on line ' . $line . ' in file ' . $file;
		}

		if ($message !== null) {
			throw new Exception($message);
		}
	}


	public function end() {
		restore_error_handler();
	}

}
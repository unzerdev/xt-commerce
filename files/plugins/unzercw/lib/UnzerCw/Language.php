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

require_once 'Customweb/Util/String.php';
require_once 'Customweb/I18n/ITranslationResolver.php';

require_once 'UnzerCw/Util.php';


class UnzerCw_Language implements Customweb_I18n_ITranslationResolver {
	
	private static $storage = array();
	
	public static function _($string, array $args = array()) {
		$currentLanguageCode = strtolower($GLOBALS['language']->code);
		
		$key = $string;
		if (!preg_match("/^[A-Z_0-9]{5,255}$/", $key)) {
			$key = 'UNZERCW_' . md5($key);
			$key = strtoupper($key);
		}
		
		if (isset(self::$storage[$currentLanguageCode][$key])) {
			$string = self::$storage[$currentLanguageCode][$key];
		}
		else if (defined($key)) {
			$string = constant($key);
		}
		
		return Customweb_Util_String::formatString($string, $args);
	}
	
	public function getTranslation($string) {
		return self::_($string);
	}
	
	public static function generalTranslate($string, $language) {
		$currentLanguageCode = strtolower($language->getIso2LetterCode());
		if (!isset(self::$storage[$currentLanguageCode])) {
			self::loadTranslations($currentLanguageCode);
		}
		
		if (isset(self::$storage[$currentLanguageCode][$string])) {
			return self::$storage[$currentLanguageCode][$string];
		}
		else {
			return $string;
		}
	}
	
	public static function loadTranslations($languageCode) {
		$languageCode = strtolower($languageCode);
		$driver = UnzerCw_Util::getDriver();
		$statement = $driver->query('SELECT language_key, language_value FROM ' . TABLE_LANGUAGE_CONTENT . ' WHERE language_code = >language_code');
		$statement->execute(array(
			'>language_code' => $languageCode,
		));
		
		while (($row = $statement->fetch()) !== false) {
			self::$storage[$languageCode][$row['language_key']] = $row['language_value'];
		}
		
	}
	
	
}
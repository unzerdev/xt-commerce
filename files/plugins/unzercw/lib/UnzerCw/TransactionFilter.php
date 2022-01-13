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

$filterClassPath = _SRV_WEBROOT."xtFramework/admin/filter/class.formFilter.php";

if (file_exists($filterClassPath)) {
	require_once $filterClassPath;
	
	require_once 'UnzerCw/Language.php';
	class UnzerCw_TransactionFilter extends FormFilter{
		public function formFields(){
	
			$eF = new ExtFunctions();
	
			$f1 = PhpExt_Form_NumberField::createNumberField("filter_id_from", TEXT_TRANSACTIONID) ->setEmptyText(TEXT_FROM);
			$f2 = PhpExt_Form_NumberField::createNumberField("filter_id_to","") -> setEmptyText(TEXT_TO);
			$f[] = self::twoCol($f1, $f2);
	
			$f1 = PhpExt_Form_TextField::createTextField("filter_payment_id", 'Payment ID');
			$f[] = self::setWidth($f1);
	
			$f1 = PhpExt_Form_TextField::createTextField("filter_external_id", TEXT_TRANSACTIONEXTERNALID);
			$f[] = self::setWidth($f1);
	
			$f1 = PhpExt_Form_TextField::createTextField("filter_payment_method", TEXT_PAYMENTMACHINENAME);
			$f[] = self::setWidth($f1);
	
			$f1 = PhpExt_Form_NumberField::createNumberField("filter_amount_from",Customweb_Core_Util_String::ucFirst(TEXT_AMOUNT)) ->setEmptyText(TEXT_FROM);
			$f2 = PhpExt_Form_NumberField::createNumberField("filter_amount_to","") -> setEmptyText(TEXT_TO);
			$f[] = self::twoCol($f1, $f2);
	
			$f1 = PhpExt_Form_TextField::createTextField("filter_authorization_status", UnzerCw_Language::_("Auth. Status"));
			$f[] = self::setWidth($f1);
	
			$f1 = PhpExt_Form_DateField::createDateField("filter_last_modify_from",	Customweb_Core_Util_String::ucFirst(TEXT_LAST_MODIFIED)) ->setEmptyText(TEXT_FROM);
			$f1 =  self::setWidth($f1,"52px");
			$f2 = PhpExt_Form_DateField::createDateField("filter_last_modify_to","") -> setEmptyText(TEXT_TO);
			$f2 =  self::setWidth($f2,"52px");
			$f[] = self::twoCol($f1, $f2);
			return $f;
		}
	}
}


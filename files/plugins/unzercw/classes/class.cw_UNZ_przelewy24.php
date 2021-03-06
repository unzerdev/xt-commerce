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

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'unzercw/init.php';
require_once 'UnzerCw/AbstractPaymentMethod.php';
require_once 'Customweb/Core/Charset.php';

class cw_UNZ_przelewy24 extends UnzerCw_AbstractPaymentMethod
{
	private $paymentMethodName = 'przelewy24';
	private $defaultDisplayName = 'Przelewy24';
	private static $settingsDefintions = array(
		'status_authorized' => array(
			'type' => 'ORDERSTATUSSELECT',
 			'constant' => 'UNZERCW_PRZELEWY24_STATUS_AUTHORIZED',
 			'default' => 'authorized',
 		),
 		'status_uncertain' => array(
			'type' => 'ORDERSTATUSSELECT',
 			'constant' => 'UNZERCW_PRZELEWY24_STATUS_UNCERTAIN',
 			'default' => 'uncertain',
 		),
 		'status_cancelled' => array(
			'type' => 'ORDERSTATUSSELECT',
 			'constant' => 'UNZERCW_PRZELEWY24_STATUS_CANCELLED',
 			'options' => array(
				'no_status_change' => 'Don\'t change order status',
 			),
 			'default' => 'cancelled',
 		),
 		'status_captured' => array(
			'type' => 'ORDERSTATUSSELECT',
 			'constant' => 'UNZERCW_PRZELEWY24_STATUS_CAPTURED',
 			'options' => array(
				'no_status_change' => 'Don\'t change order status',
 			),
 			'default' => 'no_status_change',
 		),
 		'send_basket' => array(
			'type' => 'SELECT',
 			'constant' => 'UNZERCW_PRZELEWY24_SEND_BASKET',
 			'options' => array(
				'no' => 'Do not send',
 				'yes' => 'Send Basket',
 			),
 			'default' => 'no',
 		),
 		'send_customer' => array(
			'type' => 'SELECT',
 			'constant' => 'UNZERCW_PRZELEWY24_SEND_CUSTOMER',
 			'options' => array(
				'no' => 'Do not send',
 				'yes' => 'Send Customer',
 			),
 			'default' => 'no',
 		),
 		'authorizationmethod' => array(
			'type' => 'SELECT',
 			'constant' => 'UNZERCW_PRZELEWY24_AUTHORIZATIONMETHOD',
 			'options' => array(
				'AjaxAuthorization' => 'Ajax Authorization',
 			),
 			'default' => 'AjaxAuthorization',
 		),
 		'allow_alias_opt_out' => array(
			'type' => 'SELECT',
 			'constant' => 'UNZERCW_PRZELEWY24_ALLOW_ALIAS_OPT_OUT',
 			'options' => array(
				'yes' => 'Yes',
 				'no' => 'No',
 			),
 			'default' => 'yes',
 		),
 		'payment_form_position' => array(
			'type' => 'SELECT',
 			'constant' => 'UNZERCW_PRZELEWY24_PAYMENT_FORM_POSITION',
 			'options' => array(
				'separate_page' => 'Separate Page',
 				'confirmation_page' => 'Confirmation Page',
 			),
 			'default' => 'confirmation_page',
 		),
 	);
	private static $installSettings = array(
			'UNZERCW_PRZELEWY24_STATUS_AUTHORIZED' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_PRZELEWY24_STATUS_AUTHORIZED',
 				'value' => 'authorized',
 				'url' => 'unzercw_przelewy24___status_authorized:order_status',
 				'de' => array(
					'title' => 'Autorisationsstatus&lt;tooltip&gt;Dieser Status wird vergeben, wenn eine Zahlung erfolgreich war und autorisiert wurde.&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Authorised status&lt;tooltip&gt;This status is set when the payment was successful and it is authorised.&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Authorized Status&lt;tooltip&gt;This status is set, when the payment was successfull and it is authorized. &lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Statut autoris??&lt;tooltip&gt;Cet ??tat est d??fini, lorsque le paiement a ??t?? un succ??s et il est autoris??.&lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Authorized Status&lt;tooltip&gt;This status is set, when the payment was successfull and it is authorized. &lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Authorized Status&lt;tooltip&gt;This status is set, when the payment was successfull and it is authorized. &lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_PRZELEWY24_STATUS_UNCERTAIN' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_PRZELEWY24_STATUS_UNCERTAIN',
 				'value' => 'uncertain',
 				'url' => 'unzercw_przelewy24___status_uncertain:order_status',
 				'de' => array(
					'title' => 'Unsicherer Status&lt;tooltip&gt;Sie k??nnen den Status von Bestellungen mit unsicherem Autorisationsstatus definieren.&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Uncertain status&lt;tooltip&gt;You can specify the order status for new orders that have an uncertain authorisation status.&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Uncertain Status&lt;tooltip&gt;You can specify the order status for new orders that have an uncertain authorisation status. &lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Statut incertain&lt;tooltip&gt;Vous pouvez sp??cifier le statut de la commande pour les nouvelles commandes qui ont un statut d&apos;autorisation incertain.&lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Uncertain Status&lt;tooltip&gt;You can specify the order status for new orders that have an uncertain authorisation status. &lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Uncertain Status&lt;tooltip&gt;You can specify the order status for new orders that have an uncertain authorisation status. &lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_PRZELEWY24_STATUS_CANCELLED' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_PRZELEWY24_STATUS_CANCELLED',
 				'value' => 'cancelled',
 				'url' => 'unzercw_przelewy24___status_cancelled:order_status',
 				'de' => array(
					'title' => 'Status f??r abgebrochene Bestellungen&lt;tooltip&gt;Sie k??nnen den Status von abgebrochenen Bestellungen definieren.&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Cancelled status&lt;tooltip&gt;You can specify the order status for cancelled orders.&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Cancelled Status&lt;tooltip&gt;You can specify the order status when an order is cancelled. &lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Statut Annul??&lt;tooltip&gt;Vous pouvez sp??cifier le statut de la commande quand une commande est annul??e.&lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Cancelled Status&lt;tooltip&gt;You can specify the order status when an order is cancelled. &lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Cancelled Status&lt;tooltip&gt;You can specify the order status when an order is cancelled. &lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_PRZELEWY24_STATUS_CAPTURED' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_PRZELEWY24_STATUS_CAPTURED',
 				'value' => 'no_status_change',
 				'url' => 'unzercw_przelewy24___status_captured:order_status',
 				'de' => array(
					'title' => 'Verbuchungsstatus&lt;tooltip&gt;Sie k??nnen den Status von Bestellungen definieren, die entwerder direkt nach der Bestellung oder manuell im Backend verbucht werden.&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Captured status&lt;tooltip&gt;You can specify the order status for orders that are captured either directly after the order or manually in the back-end.&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Captured Status&lt;tooltip&gt;You can specify the order status for orders that are captured either directly after the order or manually in the backend. &lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Statut de capture&lt;tooltip&gt;Vous pouvez sp??cifier le statut de la commande pour les commandes qui sont captur??s soit directement apr??s la commande ou manuellement depuis le backend.&lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Captured Status&lt;tooltip&gt;You can specify the order status for orders that are captured either directly after the order or manually in the backend. &lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Captured Status&lt;tooltip&gt;You can specify the order status for orders that are captured either directly after the order or manually in the backend. &lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_PRZELEWY24_SEND_BASKET' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_PRZELEWY24_SEND_BASKET',
 				'value' => 'no',
 				'url' => 'unzercw_przelewy24___send_basket',
 				'de' => array(
					'title' => 'Warenkorb ??bermitteln&lt;tooltip&gt;Sollen die Rechnungspositionen an Unzer ??bermittelt werden? Dies kann die Verarbeitungszeit erh??hen und bestimmte Preiskombination k??nnen allenfalls nicht verabeitet werden.&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Send Basket&lt;tooltip&gt;Should the invoice items be transmitted to Unzer? This slightly increases the processing time due to an additional request, and may cause issues for certain quantity / price combinations.&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Send Basket&lt;tooltip&gt;Should the invoice items be transmitted to Unzer? This slightly increases the processing time due to an additional request, and may cause issues for certain quantity / price combinations. &lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Send Basket&lt;tooltip&gt;Should the invoice items be transmitted to Unzer? This slightly increases the processing time due to an additional request, and may cause issues for certain quantity / price combinations. &lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Send Basket&lt;tooltip&gt;Should the invoice items be transmitted to Unzer? This slightly increases the processing time due to an additional request, and may cause issues for certain quantity / price combinations. &lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Send Basket&lt;tooltip&gt;Should the invoice items be transmitted to Unzer? This slightly increases the processing time due to an additional request, and may cause issues for certain quantity / price combinations. &lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_PRZELEWY24_SEND_CUSTOMER' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_PRZELEWY24_SEND_CUSTOMER',
 				'value' => 'no',
 				'url' => 'unzercw_przelewy24___send_customer',
 				'de' => array(
					'title' => 'Kunde ??bermitteln&lt;tooltip&gt;Sollen die Kundendaten ??bermittelt werden an Unzer? Dies erh??ht die Verarbeitungszeit, da zus??tzliche Anfragen ausgef??hrt werden m??ssen. Auf der anderen Seite ist es m??glich allenfalls die Zahlungsdaten zu speichern.&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Send Customer&lt;tooltip&gt;Should customer data be transmitted to Unzer? This slightly increases the processing time due to an additional request, but may allow e.g. saving the payment method to the customer.&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Send Customer&lt;tooltip&gt;Should customer data be transmitted to Unzer? This slightly increases the processing time due to an additional request, but may allow e.g. saving the payment method to the customer. &lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Send Customer&lt;tooltip&gt;Should customer data be transmitted to Unzer? This slightly increases the processing time due to an additional request, but may allow e.g. saving the payment method to the customer. &lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Send Customer&lt;tooltip&gt;Should customer data be transmitted to Unzer? This slightly increases the processing time due to an additional request, but may allow e.g. saving the payment method to the customer. &lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Send Customer&lt;tooltip&gt;Should customer data be transmitted to Unzer? This slightly increases the processing time due to an additional request, but may allow e.g. saving the payment method to the customer. &lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_PRZELEWY24_AUTHORIZATIONMETHOD' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_PRZELEWY24_AUTHORIZATIONMETHOD',
 				'value' => 'AjaxAuthorization',
 				'url' => 'unzercw_przelewy24___authorizationmethod',
 				'de' => array(
					'title' => 'Autorisierungsmethode&lt;tooltip&gt;W??hlen Sie bitte die Autorisierungsmethode f??r diese Zahlweise. (Bitte beachten Sie, dass die Hidden Autorisierung f??r Kreditkarten (sofern vorhanden) zus??tzliche Zertifizerungsanforderungen mit sich bringt SAQ-A-EP. F??r weitere Informationen kontkatieren Sie bitte Unzer).&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Authorisation Method&lt;tooltip&gt;Select the authorisation method to use in order to process this payment method. (Please be aware that the hidden mode for credit cards (if available) requires additional PCI certification requirements. Contact Unzer for additional information).&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Metodo di autorizzazione&lt;tooltip&gt;Seleziona il metodo di autorizzazione da utilizzare per l&apos;elaborazione di questo metodo di pagamento.&lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Mode d&apos;autorisation&lt;tooltip&gt;S??lectionnez un mode d&apos;autorisation pour le traitement de ce mode de paiement.&lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'M??todo de autorizaci??n&lt;tooltip&gt;Selecciona el m??todo de autorizaci??n para este m??todo de pago.&lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Autorisatie Methode&lt;tooltip&gt;Selecteer de Autorisatie Methode voor deze betaalmethode.&lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_PRZELEWY24_ALLOW_ALIAS_OPT_OUT' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_PRZELEWY24_ALLOW_ALIAS_OPT_OUT',
 				'value' => 'yes',
 				'url' => 'unzercw_przelewy24___allow_alias_opt_out',
 				'de' => array(
					'title' => 'Allow Alias Storage&lt;tooltip&gt;Should the customer have the option to select, if he or she wants to store the card details? If you do not give the customer the option, the data will be always stored.&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Allow Alias Storage&lt;tooltip&gt;Should the customer have the option to select, if he or she wants to store the card details? If you do not give the customer the option, the data will be always stored.&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Allow Alias Storage&lt;tooltip&gt;Should the customer have the option to select, if he or she wants to store the card details? If you do not give the customer the option, the data will be always stored.&lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Allow Alias Storage&lt;tooltip&gt;Should the customer have the option to select, if he or she wants to store the card details? If you do not give the customer the option, the data will be always stored.&lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Allow Alias Storage&lt;tooltip&gt;Should the customer have the option to select, if he or she wants to store the card details? If you do not give the customer the option, the data will be always stored.&lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Allow Alias Storage&lt;tooltip&gt;Should the customer have the option to select, if he or she wants to store the card details? If you do not give the customer the option, the data will be always stored.&lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_PRZELEWY24_PAYMENT_FORM_POSITION' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_PRZELEWY24_PAYMENT_FORM_POSITION',
 				'value' => 'confirmation_page',
 				'url' => 'unzercw_przelewy24___payment_form_position',
 				'de' => array(
					'title' => 'Page for Payment Form&lt;tooltip&gt;The payment form may be placed on the confirmation page or on a separate page. With this option the behavior can be controlled. If the user has disabled JavaScript the location may differ.&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Page for Payment Form&lt;tooltip&gt;The payment form may be placed on the confirmation page or on a separate page. With this option the behavior can be controlled. If the user has disabled JavaScript the location may differ.&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Page for Payment Form&lt;tooltip&gt;The payment form may be placed on the confirmation page or on a separate page. With this option the behavior can be controlled. If the user has disabled JavaScript the location may differ.&lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Page for Payment Form&lt;tooltip&gt;The payment form may be placed on the confirmation page or on a separate page. With this option the behavior can be controlled. If the user has disabled JavaScript the location may differ.&lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Page for Payment Form&lt;tooltip&gt;The payment form may be placed on the confirmation page or on a separate page. With this option the behavior can be controlled. If the user has disabled JavaScript the location may differ.&lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Page for Payment Form&lt;tooltip&gt;The payment form may be placed on the confirmation page or on a separate page. With this option the behavior can be controlled. If the user has disabled JavaScript the location may differ.&lt;/tooltip&gt;',
 				),
 			),
 		);

	public function getPaymentMethodName() {
		return $this->paymentMethodName;
	}
	
	
	protected function getDefaultPaymentDisplayName() {
		return Customweb_Core_Charset::convert($this->defaultDisplayName, "UTF-8", 'ASCII');
	}
	
	protected function getSettingsData() {
		return self::$settingsDefintions;
	}
	
	protected function getInstallSettings() {
		return self::$installSettings;
	}
	
	
}

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

class cw_UNZ_creditcard extends UnzerCw_AbstractPaymentMethod
{
	private $paymentMethodName = 'creditcard';
	private $defaultDisplayName = 'Credit / Debit Card';
	private static $settingsDefintions = array(
		'placeholder_size' => array(
			'type' => 'SELECT',
 			'constant' => 'UNZERCW_CREDITCARD_PLACEHOLDER_SIZE',
 			'options' => array(
				'wide' => 'Wide (label from
							Unzer)
						',
 				'narrow' => 'Narrow (label from shop)',
 			),
 			'default' => 'narrow',
 		),
 		'send_basket' => array(
			'type' => 'SELECT',
 			'constant' => 'UNZERCW_CREDITCARD_SEND_BASKET',
 			'options' => array(
				'no' => 'Do not send',
 				'yes' => 'Send Basket',
 			),
 			'default' => 'no',
 		),
 		'capturing' => array(
			'type' => 'SELECT',
 			'constant' => 'UNZERCW_CREDITCARD_CAPTURING',
 			'options' => array(
				'direct' => 'Direct Charge',
 				'deferred' => 'Authorize',
 			),
 			'default' => 'direct',
 		),
 		'status_authorized' => array(
			'type' => 'ORDERSTATUSSELECT',
 			'constant' => 'UNZERCW_CREDITCARD_STATUS_AUTHORIZED',
 			'default' => 'authorized',
 		),
 		'status_uncertain' => array(
			'type' => 'ORDERSTATUSSELECT',
 			'constant' => 'UNZERCW_CREDITCARD_STATUS_UNCERTAIN',
 			'default' => 'uncertain',
 		),
 		'status_cancelled' => array(
			'type' => 'ORDERSTATUSSELECT',
 			'constant' => 'UNZERCW_CREDITCARD_STATUS_CANCELLED',
 			'options' => array(
				'no_status_change' => 'Don\'t change order status',
 			),
 			'default' => 'cancelled',
 		),
 		'status_captured' => array(
			'type' => 'ORDERSTATUSSELECT',
 			'constant' => 'UNZERCW_CREDITCARD_STATUS_CAPTURED',
 			'options' => array(
				'no_status_change' => 'Don\'t change order status',
 			),
 			'default' => 'no_status_change',
 		),
 		'authorizationmethod' => array(
			'type' => 'SELECT',
 			'constant' => 'UNZERCW_CREDITCARD_AUTHORIZATIONMETHOD',
 			'options' => array(
				'AjaxAuthorization' => 'Ajax Authorization',
 			),
 			'default' => 'AjaxAuthorization',
 		),
 		'allow_alias_opt_out' => array(
			'type' => 'SELECT',
 			'constant' => 'UNZERCW_CREDITCARD_ALLOW_ALIAS_OPT_OUT',
 			'options' => array(
				'yes' => 'Yes',
 				'no' => 'No',
 			),
 			'default' => 'yes',
 		),
 		'payment_form_position' => array(
			'type' => 'SELECT',
 			'constant' => 'UNZERCW_CREDITCARD_PAYMENT_FORM_POSITION',
 			'options' => array(
				'separate_page' => 'Separate Page',
 				'confirmation_page' => 'Confirmation Page',
 			),
 			'default' => 'confirmation_page',
 		),
 	);
	private static $installSettings = array(
			'UNZERCW_CREDITCARD_PLACEHOLDER_SIZE' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_CREDITCARD_PLACEHOLDER_SIZE',
 				'value' => 'narrow',
 				'url' => 'unzercw_creditcard___placeholder_size',
 				'de' => array(
					'title' => 'Element Grösse&lt;tooltip&gt;Wie sollen die Elemente von Unzer geladen werden? Mit schmalen Elementen werden die Label vom Shop angezeigt. Wenn die Breiten Elemente verwendet werden, dann werden die Labels durch Unzer erzeugt via Javascript. Die Eingabefelder werden immer von Unzer geladen.&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Element Size&lt;tooltip&gt;How should elements from Unzer be loaded? With narrow elements the element label is displayed by the store, with wide elements it is loaded via javascript by Unzer. The input elements are always loaded from Unzer.&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Element Size&lt;tooltip&gt;How should elements from Unzer be loaded? With narrow elements the element label is displayed by the store, with wide elements it is loaded via javascript by Unzer. The input elements are always loaded from Unzer. &lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Element Size&lt;tooltip&gt;How should elements from Unzer be loaded? With narrow elements the element label is displayed by the store, with wide elements it is loaded via javascript by Unzer. The input elements are always loaded from Unzer. &lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Element Size&lt;tooltip&gt;How should elements from Unzer be loaded? With narrow elements the element label is displayed by the store, with wide elements it is loaded via javascript by Unzer. The input elements are always loaded from Unzer. &lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Element Size&lt;tooltip&gt;How should elements from Unzer be loaded? With narrow elements the element label is displayed by the store, with wide elements it is loaded via javascript by Unzer. The input elements are always loaded from Unzer. &lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_CREDITCARD_SEND_BASKET' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_CREDITCARD_SEND_BASKET',
 				'value' => 'no',
 				'url' => 'unzercw_creditcard___send_basket',
 				'de' => array(
					'title' => 'Warenkorb übermitteln&lt;tooltip&gt;Sollen die Rechnungspositionen an Unzer übermittelt werden? Dies kann die Verarbeitungszeit erhöhen und bestimmte Preiskombination können allenfalls nicht verabeitet werden.&lt;/tooltip&gt;',
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
 			'UNZERCW_CREDITCARD_CAPTURING' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_CREDITCARD_CAPTURING',
 				'value' => 'direct',
 				'url' => 'unzercw_creditcard___capturing',
 				'de' => array(
					'title' => 'Verbuchung&lt;tooltip&gt;Sollte der Betrag direkt verbucht werden (Direct Charge), oder sollte der Betag nur reserviert werden (Authorize)?&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Capturing&lt;tooltip&gt;Should the amount be captured automatically after the order (Direct Charge) or should the amount only be reserved (Authorize)?&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Capturing&lt;tooltip&gt;Should the amount be captured automatically after the order (Direct Charge) or should the amount only be reserved (Authorize)? &lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Comptabiliser&lt;tooltip&gt;Should the amount be captured automatically after the order (Direct Charge) or should the amount only be reserved (Authorize)? &lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Capturing&lt;tooltip&gt;Should the amount be captured automatically after the order (Direct Charge) or should the amount only be reserved (Authorize)? &lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Capturing&lt;tooltip&gt;Should the amount be captured automatically after the order (Direct Charge) or should the amount only be reserved (Authorize)? &lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_CREDITCARD_STATUS_AUTHORIZED' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_CREDITCARD_STATUS_AUTHORIZED',
 				'value' => 'authorized',
 				'url' => 'unzercw_creditcard___status_authorized:order_status',
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
					'title' => 'Statut autorisé&lt;tooltip&gt;Cet état est défini, lorsque le paiement a été un succès et il est autorisé.&lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Authorized Status&lt;tooltip&gt;This status is set, when the payment was successfull and it is authorized. &lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Authorized Status&lt;tooltip&gt;This status is set, when the payment was successfull and it is authorized. &lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_CREDITCARD_STATUS_UNCERTAIN' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_CREDITCARD_STATUS_UNCERTAIN',
 				'value' => 'uncertain',
 				'url' => 'unzercw_creditcard___status_uncertain:order_status',
 				'de' => array(
					'title' => 'Unsicherer Status&lt;tooltip&gt;Sie können den Status von Bestellungen mit unsicherem Autorisationsstatus definieren.&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Uncertain status&lt;tooltip&gt;You can specify the order status for new orders that have an uncertain authorisation status.&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Uncertain Status&lt;tooltip&gt;You can specify the order status for new orders that have an uncertain authorisation status. &lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Statut incertain&lt;tooltip&gt;Vous pouvez spécifier le statut de la commande pour les nouvelles commandes qui ont un statut d&apos;autorisation incertain.&lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Uncertain Status&lt;tooltip&gt;You can specify the order status for new orders that have an uncertain authorisation status. &lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Uncertain Status&lt;tooltip&gt;You can specify the order status for new orders that have an uncertain authorisation status. &lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_CREDITCARD_STATUS_CANCELLED' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_CREDITCARD_STATUS_CANCELLED',
 				'value' => 'cancelled',
 				'url' => 'unzercw_creditcard___status_cancelled:order_status',
 				'de' => array(
					'title' => 'Status für abgebrochene Bestellungen&lt;tooltip&gt;Sie können den Status von abgebrochenen Bestellungen definieren.&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Cancelled status&lt;tooltip&gt;You can specify the order status for cancelled orders.&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Cancelled Status&lt;tooltip&gt;You can specify the order status when an order is cancelled. &lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Statut Annulé&lt;tooltip&gt;Vous pouvez spécifier le statut de la commande quand une commande est annulée.&lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Cancelled Status&lt;tooltip&gt;You can specify the order status when an order is cancelled. &lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Cancelled Status&lt;tooltip&gt;You can specify the order status when an order is cancelled. &lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_CREDITCARD_STATUS_CAPTURED' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_CREDITCARD_STATUS_CAPTURED',
 				'value' => 'no_status_change',
 				'url' => 'unzercw_creditcard___status_captured:order_status',
 				'de' => array(
					'title' => 'Verbuchungsstatus&lt;tooltip&gt;Sie können den Status von Bestellungen definieren, die entwerder direkt nach der Bestellung oder manuell im Backend verbucht werden.&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Captured status&lt;tooltip&gt;You can specify the order status for orders that are captured either directly after the order or manually in the back-end.&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Captured Status&lt;tooltip&gt;You can specify the order status for orders that are captured either directly after the order or manually in the backend. &lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Statut de capture&lt;tooltip&gt;Vous pouvez spécifier le statut de la commande pour les commandes qui sont capturés soit directement après la commande ou manuellement depuis le backend.&lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Captured Status&lt;tooltip&gt;You can specify the order status for orders that are captured either directly after the order or manually in the backend. &lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Captured Status&lt;tooltip&gt;You can specify the order status for orders that are captured either directly after the order or manually in the backend. &lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_CREDITCARD_AUTHORIZATIONMETHOD' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_CREDITCARD_AUTHORIZATIONMETHOD',
 				'value' => 'AjaxAuthorization',
 				'url' => 'unzercw_creditcard___authorizationmethod',
 				'de' => array(
					'title' => 'Autorisierungsmethode&lt;tooltip&gt;Wählen Sie bitte die Autorisierungsmethode für diese Zahlweise. (Bitte beachten Sie, dass die Hidden Autorisierung für Kreditkarten (sofern vorhanden) zusätzliche Zertifizerungsanforderungen mit sich bringt SAQ-A-EP. Für weitere Informationen kontkatieren Sie bitte Unzer).&lt;/tooltip&gt;',
 				),
 				'en' => array(
					'title' => 'Authorisation Method&lt;tooltip&gt;Select the authorisation method to use in order to process this payment method. (Please be aware that the hidden mode for credit cards (if available) requires additional PCI certification requirements. Contact Unzer for additional information).&lt;/tooltip&gt;',
 				),
 				'it' => array(
					'title' => 'Metodo di autorizzazione&lt;tooltip&gt;Seleziona il metodo di autorizzazione da utilizzare per l&apos;elaborazione di questo metodo di pagamento.&lt;/tooltip&gt;',
 				),
 				'fr' => array(
					'title' => 'Mode d&apos;autorisation&lt;tooltip&gt;Sélectionnez un mode d&apos;autorisation pour le traitement de ce mode de paiement.&lt;/tooltip&gt;',
 				),
 				'es' => array(
					'title' => 'Método de autorización&lt;tooltip&gt;Selecciona el método de autorización para este método de pago.&lt;/tooltip&gt;',
 				),
 				'nl' => array(
					'title' => 'Autorisatie Methode&lt;tooltip&gt;Selecteer de Autorisatie Methode voor deze betaalmethode.&lt;/tooltip&gt;',
 				),
 			),
 			'UNZERCW_CREDITCARD_ALLOW_ALIAS_OPT_OUT' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_CREDITCARD_ALLOW_ALIAS_OPT_OUT',
 				'value' => 'yes',
 				'url' => 'unzercw_creditcard___allow_alias_opt_out',
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
 			'UNZERCW_CREDITCARD_PAYMENT_FORM_POSITION' => array(
				'type' => 'dropdown',
 				'key' => 'UNZERCW_CREDITCARD_PAYMENT_FORM_POSITION',
 				'value' => 'confirmation_page',
 				'url' => 'unzercw_creditcard___payment_form_position',
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

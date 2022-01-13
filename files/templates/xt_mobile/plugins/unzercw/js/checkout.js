
(function($) {
	$(document).ready(function() {
		window['unzercw_checkout_processor'].getPaymentListFormPaneSelector = function() {
			return '.unzercw-payment-block[data-module-code="' + this.currentSelectedPaymentMethod + '"]';
		};
		
		window['unzercw_checkout_processor'].refreshInsertedElement = function(element) {
			element.trigger("create");
		};
		
		window['unzercw_checkout_processor'].getConfirmationPaneSelector = function() {
			return '.unzercw-payment-form';
		};
		
		window['unzercw_checkout_processor'].getBlockingElement = function() {
			if (this.currentSelectedPaymentMethod !== false) {
				var paymentBlock = $('.unzercw-payment-block[data-module-code="' + this.currentSelectedPaymentMethod + '"]');
				if (paymentBlock.length) {
					return paymentBlock;
				}
			}
			
			return this.getConfirmationFormElement();
			
		};
		
		window['unzercw_checkout_processor'].init();
	});
}(jquery_unzercw));
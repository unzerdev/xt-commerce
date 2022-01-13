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


(function ($) {
	window['unzercw_checkout_processor'] = {
			
		/**
		 * Contains the element, on which the last click event was executed.
		 * 
		 * @return boolean|object
		 */
		lastPressedButton: false,
		
		/**
		 * The method name of the currently selected method.
		 * 
		 * @return boolean|string
		 */
		currentSelectedPaymentMethod: false,
		
		/**
		 * The content of the form fields.
		 * 
		 * @return boolean|array
		 */
		dynamicFormFields: false,
		
		/**
		 * Enforce skipping the form validation.
		 * 
		 * @return boolean
		 */
		skipValidation: false,
		
		/**
		 * List of button names, which do not lead to a validation of the input form.
		 * 
		 * @return array
		 */
		aliasManagerButtons: [
			'unzercw_alias_use_new_card', 
			'unzercw_alias_use_stored_card', 
			'unzercw_create_new_alias', 
			'unzercw_update_alias', 
			'unzercw_create_new_alias'
		],
		
		/**
		 * This method should be called to bind this object to the checkout
		 * form. 
		 * 
		 * @return void
		 */
		init: function() {
			console.log('init');
			if (typeof window['unzercw_backup_object'] !== 'undefined') {
				var backup = window['unzercw_backup_object'];
				this.currentSelectedPaymentMethod = backup.currentSelectedPaymentMethod;
				this.skipValidation = backup.skipValidation;
				this.dynamicFormFields = backup.dynamicFormFields;
			}
			this.attachListeners();
			
			// In case we use payment seleciton page for the form, we can hide this now.
			if (this.dynamicFormFields !== false) {
				$('.unzercw-payment-form').hide();
			}
		},
		
		/**
		 * This method attaches all listeners. This method should be called
		 * when the HTML is updated or changed.
		 * 
		 * @return void
		 */
		attachListeners: function() {
			$('*').unbind('.unzercw');
			this.attachClickListener();
			
			this.attachAliasElementChangeListeners();
			this.attachRegularAjaxFormHandler();
			this.embedJavaScriptForm();
			this.attachConfirmationSubmitHandler();
			this.attachPaymentListSubmitHandler();
			this.attachPaymentListPaymentSelectHandler();
			this.removeFormFieldNames();
			this.lastPressedButton = false;
		},
		
		/**
		 * Removes on the payment list selection page all form field names to prevent the sending of the directly to the server.
		 * 
		 * @return void
		 */
		removeFormFieldNames: function() {
			var submittableTypes = ['select', 'input', 'button', 'textarea'];
			for(var i = 0; i < submittableTypes.length; i++) {
				$('.unzercw-payment-pane .unzercw-payment-list-form .visible-form-fields ' + submittableTypes[i] + '[name]').each(function (element) {
					$(this).attr('data-field-name', $(this).attr('name'));
					$(this).removeAttr('name');
				});
			}
		},
		
		/**
		 * Register a listener to store the pressed button.
		 * 
		 * @return void
		 */
		attachClickListener: function() {
			$('input[type="submit"]').bind('click.unzercw', $.proxy(function(event){ 
				this.lastPressedButton = event.target;
				if ($(event.target).val() == 'next'  && (typeof window['unzercwListAliasUpdated'] !== 'undefined') && window['unzercwListAliasUpdated']) {
					if ($('.unzercw-payment-list-form').length) {
						this.getPaymentListFormElement().submit();
					}
				}
				if ((typeof window['unzercwConfirmAliasUpdated'] !== 'undefined') && window['unzercwConfirmAliasUpdated']) {
					if ($('.unzercw-payment-form').length) {
						this.getConfirmationFormElement().submit();
					}
				}
				
			}, this));
		},
		
		/**
		 * Register listeners on the alias manager fields. On change the form is submitted.
		 * 
		 * @return void
		 */
		attachAliasElementChangeListeners: function() {
			$('.unzercw-alias-form').find("input[type='checkbox']").bind('change.unzercw', $.proxy(function(event) {
				var paymentMethodName = $(event.target).attr('data-payment-method-name');
				this.lastPressedButton = {
					name: 'unzercw_update_alias[' + paymentMethodName + ']',
					value: 'true',
				};
				this.getConfirmationFormElement().append($('[name="unzercw_create_new_alias_present[' + paymentMethodName + ']"]'));
				this.getConfirmationFormElement().append($('["name=unzercw_create_new_alias[' + paymentMethodName + ']"]'));		
				this.getConfirmationFormElement().submit();
				this.getPaymentListFormElement().submit();
			}, this));
			$('.unzercw-alias-form').find("select").bind('change.unzercw', $.proxy(function(event) {
				var paymentMethodName = $(event.target).attr('data-payment-method-name');
				this.lastPressedButton = {
					name: 'unzercw_update_alias[' + paymentMethodName + ']',
					value: 'true',
				};
				this.getConfirmationFormElement().append($('["name=unzercw_alias[' + paymentMethodName + ']"]'));
				this.getConfirmationFormElement().submit();
				this.getPaymentListFormElement().submit();
			}, this));
		},
		
		
		
		/**
		 * This method attaches the listener to handle the regular AJAX form. The regular AJAX form is 
		 * displayed on a separate page and not on the confirmation page.
		 * 
		 * @return void
		 */
		attachRegularAjaxFormHandler: function () {
			var processor = this;
			$('.unzercw-ajax-authorization-form').each(function() {
				var ajaxForm = $(this);
				ajaxForm.parents('.unzercw-payment-form').find('[name="processPayment"]').bind('click.unzercw', function(event) {
					$(this).hide();
					var methodName = ajaxForm.attr('data-method-name');
					var callback = window['unzercw_ajax_submit_callback_' + methodName];
					var hasNoErrors = false;
					
					var validationCallback = window['cwValidateFields'];
					
					if (typeof validationCallback != 'undefined') {
						validationCallback(function(valid) {
							processor.attachRegularAjaxFormHandlerValidationSuccess(ajaxForm);
						}, function(errors, valid) {
							alert(errors[Object.keys(errors)[0]]);
							ajaxForm.parents('.unzercw-payment-form').find('[name="processPayment"]').show();
						});
					}
					else{
						processor.attachRegularAjaxFormHandlerValidationSuccess(ajaxForm);
					}
				});
				
			});
		},
		
		attachRegularAjaxFormHandlerValidationSuccess: function(ajaxForm) {
			var methodName = ajaxForm.attr('data-method-name');
			var callback = window['unzercw_ajax_submit_callback_' + methodName];
			var hasNoErrors = false;
			if (typeof callback == 'undefined') {
				alert("No Ajax callback found.");
			}
			else {
				var fields = {};
				var data = ajaxForm.serializeArray();
				$(data).each(function(index, value) {
					fields[value.name] = value.value;
				});
				callback(fields);
			}
			
			
		},
		
		
		/**
		 * This method attaches the submit handler of the confirmation form.
		 * 
		 * @return void
		 */
		attachConfirmationSubmitHandler: function() {
			if (this.isConfirmationFormRequiresJavaScript() || $('.unzercw-alias-form').length > 0) {
				this.getConfirmationFormElement().bind('submit.unzercw', $.proxy(function(event) {
					var rs = false;
					this.addRequiredAttribute();
					if(this.getConfirmationFormElement()[0].reportValidity()) {
						rs = this.handleConfirmationFormSubmitEvent();
					}
					if (rs === false) {
						event.preventBubble = true;
					}
					return rs;
				}, this));
			}
		},

		/**
		 * Adds required attribute on any field which has the xt-form-required class set.
		 *
		 * @return void
		 */
		addRequiredAttribute: function() {
			$('.xt-form-required').each(function(idx, elem) {
				$(elem).attr('required', 'required');
			});
		},


		/**
		 * This method attach the submit handler to the payment list form.
		 * 
		 * @return void
		 */
		attachPaymentListSubmitHandler: function() {
			if ($('.unzercw-payment-list-form').length) {
				this.getPaymentListFormElement().bind('submit.unzercw', $.proxy(function(event) {
					var selector = "[data-module-code='"+this.currentSelectedPaymentMethod+"']";
					var selectedMain = $(selector);
					if($(selectedMain).has('.unzercw-payment-form').length){
						var rs = this.handlePaymentListFormSubmitEvent();
						if (rs === false) {
							event.preventBubble = true;
							return false;
						}
					}
					return true;
				}, this));
			}
		},
		
		/**
		 * This method attaches a listener on the payment method radio button.
		 * 
		 * @return void
		 */
		attachPaymentListPaymentSelectHandler: function() {
			this.getPaymentListFormElement().find('input[name="selected_payment"][type="radio"]').bind('change.unzercw', $.proxy(function(event){
				this.handlePaymentMethodSelection(event.target);
			}, this));
			
			// Execute on the pre selected elements the selection process
			this.getPaymentListFormElement().find('input[name="selected_payment"][checked="checked"], input[name="selected_payment"][checked="1"]').each($.proxy(function(key, element){
				this.handlePaymentMethodSelection(element);
			}, this));
		},
		
		/**
		 * Handle the selection of a payment method.
		 * 
		 * @return void
		 */
		handlePaymentMethodSelection: function(selectedInputElement) {
			$('.unzercw-payment-pane').addClass('unzercw-payment-pane-hidden');
			this.currentSelectedPaymentMethod = false;
			var paymentBlock = $(selectedInputElement).closest('.unzercw-payment-block');
			if (paymentBlock.length) {
				this.currentSelectedPaymentMethod = $(paymentBlock[0]).attr('data-module-code');
				paymentBlock.find('.unzercw-payment-pane').removeClass('unzercw-payment-pane-hidden');
			}
		},
		
		/**
		 * This method handles the event when the confirmation form is submitted.
		 * 
		 * @return boolean
		 */
		handleConfirmationFormSubmitEvent: function() {
			this.blockUI();
			this.cleanUpErrorMessages();
			var processor = this;
			if (this.isAliasManagerButtonPressed()) {
				this.handleConfirmationFormSubmitEventValidationSuccess();
				return false;
			}
		 	this.validatePaymentForm(
		 			function(valid){processor.handleConfirmationFormSubmitEventValidationSuccess();},
		 			function(errors, valid){processor.handleConfirmationFormSubmitEventValidationFailure(errors, valid);});
			return false;
		},
		
		
		handleConfirmationFormSubmitEventValidationSuccess : function(){
			var callbackFunction = window['unzercwJavaScriptFormCallback'];
			if (this.dynamicFormFields !== false &&  typeof callbackFunction === 'undefined') {
				var hiddenForm = this.renderDataAsHiddenFields(this.dynamicFormFields);
				this.getConfirmationFormElement().append(hiddenForm);
				this.unblockUI();
			}
			else {
				$.ajax({
					type: 		'POST',
					url: 		this.getConfirmationFormElement()[0].getAttribute('action'),
					data: 		this.createDataForConfirmationAjaxCall(),
					success: 	$.proxy(function (response) {
						if (this.isAliasManagerButtonPressed()) {
							this.handleAliasManagerFormUpdateOnConfirmationPage(response);
						}
						else {
							this.handleConfirmationAjaxResponse(response);
						}
						this.unblockUI();
					}, this),
				});
				return false;
			}
		},
		
		handleConfirmationFormSubmitEventValidationFailure : function(errors, valid){
			alert(errors[Object.keys(errors)[0]]);
			this.unblockUI();
		
		},
		
		/**
		 * This method handles the event when the payment list page form is submitted.
		 * 
		 * @return boolean
		 */
		handlePaymentListFormSubmitEvent: function() {
			if (this.currentSelectedPaymentMethod !== false) {
				this.blockUI();
				this.cleanUpErrorMessages();
				if (this.isAliasManagerButtonPressed()) {
					this.handlePaymentListFormSubmitEventValidationSuccess();
					return false;
				}
				
				var processor = this;
			 	this.validatePaymentForm(
			 			function(valid){processor.handlePaymentListFormSubmitEventValidationSuccess();},
			 			function(errors, valid){processor.handlePaymentListFormSubmitEventValidationFailure(errors, valid);});
				return false;
			}
		},
		
		
		handlePaymentListFormSubmitEventValidationSuccess: function(){
			this.dynamicFormFields = this.getDynamicFormValuesPaymentList();
			
			$.ajax({
				type: "POST",
				url: this.getPaymentListFormElement().action,
				data: this.createDataForPaymentListFormAjaxCall(),
				success: $.proxy(function (response) {
					if (this.isAliasManagerButtonPressed()) {
						this.handleAliasManagerFormUpdateOnPaymentListPage(response);
					}
					else {
						this.handlePaymentListFormSubmitResponse(response);
					}
					
				}, this),
			});
		},
		
		handlePaymentListFormSubmitEventValidationFailure: function(errors, valid){
			alert(errors[Object.keys(errors)[0]]);
			this.unblockUI();
			
		},
		
		/**
		 * Returns true, when a alias manager button was pressed.
		 * 
		 * @return boolean
		 */
		isAliasManagerButtonPressed: function() {
			if (this.lastPressedButton != false) {
				var buttonName = this.lastPressedButton.name;
				if (buttonName.indexOf("[") > -1) {
					buttonName.substring(0, buttonName.indexOf("["));
				}
				if (buttonName != '' && $.inArray(buttonName, this.aliasManagerButtons)) {
					return true;
				}
			}
			return false;
		},
		
		handleAliasManagerFormUpdateOnPaymentListPage: function(response) {
			var selector = this.getPaymentListFormPaneSelector();;
			var data = $(response);
			var html = data.find(selector);
			if (html.length > 0) {
				html.find('[data-module-code="' + this.currentSelectedPaymentMethod + '"] input[name="selected_payment"]').attr('checked', 'checked');
				window['unzercwListAliasUpdated'] = true;
				$(selector).html(html.children());
				
				// The default version of jQuery delivered by xtc does not support evalulation of js scripts.
				data.each(function(k, e) {
					if(typeof e === 'object' && e.nodeName == 'SCRIPT') {
						jQuery.globalEval(e.innerHTML);
					}
				});
				
				this.refreshInsertedElement($(selector));
				
				this.attachListeners();
				
			}
			else {
				console.log('response is invalid, the form could not be updated.');
			}
			this.unblockUI();
		},
		
		handleAliasManagerFormUpdateOnConfirmationPage: function(response) {
			var data = $(response);
			var html = data.find(this.getConfirmationPaneSelector());
			if (html.length > 0) {
				window['unzercwConfirmAliasUpdated'] = true;
				$(this.getConfirmationPaneSelector()).html(html.children());
				// The default version of jQuery delivered by xtc does not support evalulation of js scripts.
				data.each(function(k, e) {
					if(typeof e === 'object' && e.nodeName == 'SCRIPT') {
						jQuery.globalEval(e.innerHTML);
					}
				});
				
				this.refreshInsertedElement($(this.getConfirmationPaneSelector()));
				
				this.attachListeners();
			} 
			else {
				console.log('response is invalid, the form could not be updated.');
			}
			this.unblockUI();
		},
		
		handlePaymentListFormSubmitResponse: function (response) {
			this.skipValidation = true;
			backup = this;
			document.open('text/html');
			document.write(response);
			window['unzercw_backup_object'] = backup;
			document.close();
		},
	
		/**
		 * This method removes all error messages displayed on the confirmation page.
		 * 
		 * @return void
		 */
		cleanUpErrorMessages: function() {
			$('.unzercw-error-list').remove();
		},
		
		/**
		 * This method calls the provided success and failure callback, after the confirmation form is validated.
		 * 
		 */
		validatePaymentForm: function(successCallback, failureCallback) {
			if (this.skipValidation) {
				successCallback();
				return;
			}
			if (this.currentSelectedPaymentMethod !== false) {
				var validationCallback = window['cwValidateFields'+this.currentSelectedPaymentMethod];
				if (typeof validationCallback !== 'undefined') {
					validationCallback(successCallback, failureCallback);
					return;
				}
				else {
					successCallback(new Array());
					return;
				}
			}
			else {
				if (typeof cwValidateFields !== 'undefined') {
					cwValidateFields(successCallback, failureCallback);
					return;
				}
				successCallback(new Array());
				return;
			}			
		},
		
		/**
		 * This method builds the data string send to the confirmation page on submit.
		 * 
		 * @return string
		 */
		createDataForConfirmationAjaxCall: function() {
			var data = this.getConfirmationFormElement().serialize() + "&ajaxCall=true";
			if (this.lastPressedButton != false && typeof this.lastPressedButton.name !== 'undefined' && this.lastPressedButton.name != '') {
				data = data + '&' + this.lastPressedButton.name + '=' + this.lastPressedButton.value;
			}
			return data;
		},
		
		/**
		 * This method builds the data string send to the confirmation page on submit.
		 * 
		 * @return string
		 */
		createDataForPaymentListFormAjaxCall: function() {
			var data = this.getPaymentListFormElement().serialize() + "&ajaxCall=true";
			if (this.lastPressedButton != false && typeof this.lastPressedButton.name !== 'undefined' && this.lastPressedButton.name != '') {
				data = data + '&' + this.lastPressedButton.name + '=' + this.lastPressedButton.value;
			}
			return data;
		},
		
		/**
		 * This method handles the AJAX response given by the form submit of the confirmation form.
		 * 
		 * @return boolean
		 */
		handleConfirmationAjaxResponse: function(response) {
			try {
				var objects = $.parseJSON(response);
			}
			catch(e) {
				this.handleConfirmationAjaxFailure(response);
				this.unblockUI();
				return false;
			}
			
			// Handle Success case
			var callbackFunction = window['unzercwJavaScriptFormCallback'];
			if (typeof callbackFunction === 'string') {
				window[callbackFunction](this.getDynamicFormValues(), objects);
			}
			else {
				callbackFunction(this.getDynamicFormValues(), objects);
			}
			
			return false;
		},
		
		/**
		 * This method handles the case when the parsing of the AJAX response of the confirmation form submit 
		 * was invalid. We try to extract any error message in the response.
		 * 
		 * @return void
		 */
		handleConfirmationAjaxFailure: function(response) {
			var content = $(response);
			var errors = content.find('.error li');
			if (errors.length > 0) {
				var errorContent = errors.innerHTML;
				if (typeof errorContent == 'undefined') {
					errorContent = $(errors).html();
				}
				var html = '<div class="row unzercw-error-list"><div class="column xt-grid-16"><ul class="error">' + errorContent + '</ul></div></div>';
				$(this.getConfirmationPaneSelector()).before(html);
			}
			else {
				alert(response);
			}
		},
		
		
		/**
		 * This method embeds the form provided over the JS variables.
		 * 
		 * @return void
		 */
		embedJavaScriptForm: function() {
			if (this.isConfirmationFormRequiresJavaScript()) {
				var formContent = decodeURIComponent((window['unzercwJavaScriptFormContent']+'').replace(/\+/g, '%20'));
				var formContentDom = $(formContent);
				var submittableTypes = ['select', 'input', 'button', 'textarea'];
				for(var i = 0; i < submittableTypes.length; i++) {
					formContentDom.find(submittableTypes[i] + '[name]').each(function (element) {
						$(this).attr('data-field-name', $(this).attr('name'));
						$(this).removeAttr('name');
					});
				}
				
				if (this.getPaymentFormPane().find('.visible-form-fields').length > 0) {
					this.getPaymentFormPane().append(formContentDom);
					if (this.getPaymentFormPane().length > 0) {
						this.refreshInsertedElement(this.getPaymentFormPane());
					}
				}
				else {
					this.getPaymentFormPane().children('.js-generated-fields').remove();
					this.getPaymentFormPane().append('<div class="js-generated-fields"></div>');
					this.getPaymentFormPane().children('.js-generated-fields').append(formContentDom);
					if (this.getPaymentFormPane().children('.js-generated-fields').length > 0) {
						this.refreshInsertedElement(this.getPaymentFormPane().children('.js-generated-fields'));
					}
				}
				
			}
		},
		
		/**
		 * Checks if any form handling is required on the confirmation page.
		 * 
		 * @return boolean
		 */
		isConfirmationFormRequiresJavaScript: function () {
			if (this.currentSelectedPaymentMethod !== false) {
				return true;
			}
			else if (typeof window['unzercwJavaScriptFormContent'] !== 'undefined') {
				return true;
			}
			else {
				return false;
			}
		},
		
		/**
		 * Returns the selector which contains all panes and forms of the confirmation page.
		 * 
		 * @return string
		 */
		getConfirmationPaneSelector: function() {
			if ($('#checkout-confirmation').length > 0) {
				return '#checkout-confirmation';
			}
			else {
				return 'body';
			}
		},
		
		/**
		 * Returns the selctor which contains all panes and forms of the payment list page.
		 * 
		 * @return string
		 */
		getPaymentListFormPaneSelector: function() {
			if ($('#checkout-payment').length > 0) {
				return '#checkout-payment';
			}
			else {
				return 'body';
			}
		},
		
		/**
		 * Returns the form element of the confirmation page.
		 * 
		 * @return Object
		 */
		getConfirmationFormElement: function() {
			return $('input[type="hidden"][name="action"][value="process"]').parents('form');
		},
		
		/**
		 * Returns the form element of the payment list page.
		 * 
		 * @return Object
		 */
		getPaymentListFormElement: function() {
			return $('input[type="hidden"][name="action"][value="payment"]').parents('form');
		},
		
		/**
		 * This method returns an element which should be blocked during AJAX calls.
		 * 
		 * @return Object
		 */
		getBlockingElement: function() {
			if (this.currentSelectedPaymentMethod !== false) {
				var paymentBlock = $('.unzercw-payment-block[data-module-code="' + this.currentSelectedPaymentMethod + '"]');
				if (paymentBlock.length) {
					return paymentBlock;
				}
			}
			
			var blockingElement = this.getConfirmationFormElement().closest('div.column');
			
			if (typeof blockingElement === 'undefined' || blockingElement.length <= 0) {
				return $('body');
			}
			else {
				return blockingElement;
			}
		},
		
		/**
		 * This method blocks the user from entering other data. This method should be called
		 * before any AJAX call is executed.
		 * 
		 * @return void
		 */
		blockUI: function() {
			var element = this.getBlockingElement();
			var height = element.outerHeight();
			var width = element.outerWidth();
			var offset = element.position();
			element.append('<div class="unzercw-ajax-overlay"></div>');
			element.find('.unzercw-ajax-overlay').css('border-radius', element.css('border-radius')).height(height).width(width).css({top: offset.top, left: offset.left, position:'absolute'}).fadeTo(100, 0.3);
		},
		
		/**
		 * This method unblocks the user interface and allows the user do do any user action.
		 * 
		 * @return void
		 */
		unblockUI: function() {
			this.getBlockingElement().find('.unzercw-ajax-overlay').remove();
		},
		
		/**
		 * This method returns the form fields loaded by JavaScript. These fields should not be
		 * send to the shopping cart.
		 * 
		 * @return List<Object>
		 */
		getDynamicFormValues: function() {
			if (this.dynamicFormFields !== false) {
				return this.dynamicFormFields;
			}
			var output = {};
			this.getPaymentFormPane().find('*[data-field-name]').each(function (element) {
				var name = $(this).attr('data-field-name');
				output[name] = $(this).val();
			});
			return output;
		},
		
		getDynamicFormValuesPaymentList: function() {
			var output = {};
			if (this.currentSelectedPaymentMethod !== false) {
				this.getPaymentFormPane().find('*[data-field-name]').each($.proxy(function (key, element) {
					var name = $(element).attr('data-field-name');
					if (name.indexOf(this.currentSelectedPaymentMethod) === 0) {
						var fieldName = name.substring(this.currentSelectedPaymentMethod.length + 1, name.length).replace(']', '');
						output[fieldName] = $(element).val();
					}
					
				}, this));
			}
			
			return output;
		},
		
		/**
		 * Returns the element which contains the payment form pane.
		 * 
		 * @return Object
		 */
		getPaymentFormPane: function() {
			return $('.unzercw-payment-form');
		},
		
		renderDataAsHiddenFields: function(data) {
			var output = '';
			$.each(data, function(key, value) {
				output += '<input type="hidden" name="' + key + '" value="' + value.replace('"', '&quot;') + '" />';
			});
			return output;
		},
		
		/**
		 * This method is called, whenever an element is added to the dom tree. This can be used to update the 
		 * layout. (e.g. for jQuery Mobile)
		 */
		refreshInsertedElement: function(element) {
			
		},
		
	};
	
	// The following methods are standard callback methods for handling the different authorization methods.
	window['UnzerCwProcessHiddenAuthorization'] = function(formData, confirmationResponse) {
		var newForm = '<form id="unzercw_hidden_authorization_redirect_form" action="' + confirmationResponse.formActionUrl + '" method="POST">';
		newForm += confirmationResponse.hiddenFields;
		newForm += window['unzercw_checkout_processor'].renderDataAsHiddenFields(formData);
		newForm += '</form>';
		$('body').append(newForm);
		$('#unzercw_hidden_authorization_redirect_form').submit();	
	};
	
	window['UnzerCwProcessIFrameAuthorization'] = function(formData, confirmationResponse) {
		var button = $(window['unzercw_checkout_processor'].lastPressedButton);
		var box = button.closest('div');
		button.remove();
		box.append(confirmationResponse.iframe);
		
		// Apply the styles (required by jQuery mobile)
		window['unzercw_checkout_processor'].refreshInsertedElement(box);
		
		window['unzercw_checkout_processor'].unblockUI();
	};
	
	window['UnzerCwProcessAjaxAuthorization'] = function(formData, confirmationResponse) {
		$.getScript(confirmationResponse.ajaxScriptUrl, function(){
			eval("var callbackFunction = " + confirmationResponse.ajaxSubmitCallback);
			callbackFunction(formData);
		});
	};
	
}(jquery_unzercw));
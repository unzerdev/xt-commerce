
// This is the default handler of the checkout. If a file is placed in /templates/[currentTemplate]/plugins/unzercw/js/checkout.js then the specified 
// file in the template folder is used and not this file. This allows to use a dedicated file per template.
(function($) {
	$(document).ready(function() {
		var defaultTemplateCheckoutHandler = window['unzercw_checkout_processor'];
		defaultTemplateCheckoutHandler.init();
		
		// IE 11 reportValidity polyFill
		if (!HTMLFormElement.prototype.reportValidity) {
		    HTMLFormElement.prototype.reportValidity = function() {
		        if (this.checkValidity()) return true;
		        var btn = document.createElement('button');
		        this.appendChild(btn);
		        btn.click();
		        this.removeChild(btn);
		        return false;
		    };
		}
		if (!HTMLInputElement.prototype.reportValidity) {
		    HTMLInputElement.prototype.reportValidity = function(){
		        if (this.checkValidity()) return true
		        var tmpForm;
		        if (!this.form) {
		            tmpForm = document.createElement('form');
		            tmpForm.style.display = 'inline';
		            this.before(tmpForm);
		            tmpForm.append(this);
		        }
		        var siblings = Array.from(this.form.elements).filter(function(input){
		            return input !== this && !!input.checkValidity && !input.disabled;
		        },this);
		        siblings.forEach(function(input){
		            input.disabled = true;
		        });
		        this.form.reportValidity();
		        siblings.forEach(function(input){
		            input.disabled = false;
		        });
		        if (tmpForm) {
		            tmpForm.before(this);
		            tmpForm.remove();
		        }
		        this.focus();
		        this.selectionStart = 0;
		        return false;
		    };
		}

	});
}(jquery_unzercw));
<?php 

// This hook is called when the confirmation form is shown.
try {
	if (isset($_SESSION['selected_payment'])) {
		$paymentMethodCode = $_SESSION['selected_payment'];
		if (strpos($paymentMethodCode, 'cw_UNZ') === 0) {
			require _SRV_WEBROOT . 'plugins/unzercw/init.php';
			require_once 'UnzerCw/ErrorHandler.php';
			
			$handler = new UnzerCw_ErrorHandler();
			$handler->start();
			try {
				$paymentMethod = UnzerCw_Util::getPaymentMethodInstanceByCode($paymentMethodCode);
				echo $paymentMethod->getConfirmationPageHtml();
			}
			catch(Exception $e) {
				echo $e->getMessage();
				echo "<br /><br />";
				echo '<pre>';
					echo $e->getTraceAsString();
				echo '</pre>';
			}
			$handler->end();
		}
	}
}
catch(Exception $e) {
	echo $e->getMessage();
	echo "<br /><br />";
	echo $e->getTraceAsString();
}

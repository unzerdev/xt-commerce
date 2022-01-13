<?php

try {
	if (isset($_GET['load_section']) && $_GET['load_section'] == 'plugin_installed' && isset($_GET['edit_id']) && isset($_POST['plugin_id'])) {
		require _SRV_WEBROOT . 'plugins/unzercw/init.php';
		require_once 'UnzerCw/Util.php';
	
		// In case the plugin is opened, then start the migration
		if ($_GET['edit_id'] == UnzerCw_Util::getPluginId()) {
			require_once 'UnzerCw/Installer.php';
			$installer = new UnzerCw_Installer();
			$installer->migrate();
		}
	}
	else if (isset($_GET['load_section']) && $_GET['load_section'] == 'order_status' && $_POST['status_class'] == 'order_status') {
		// Check if we need to update the settings for sending e-mails
		
		require _SRV_WEBROOT . 'plugins/unzercw/init.php';
		require_once 'UnzerCw/OrderStatus.php';
		
		UnzerCw_OrderStatus::updateSettings();
	}
	else if (isset($_GET['load_section']) && $_GET['load_section'] == 'plugin_installed' && isset($_GET['edit_id']) && !isset($_POST['plugin_id']) && !isset($_REQUEST['save'])) {
		require _SRV_WEBROOT . 'plugins/unzercw/init.php';
		require_once 'UnzerCw/Util.php';
	
		if ($_GET['edit_id'] == UnzerCw_Util::getPluginId()) {
			
			if (false) {
				require_once 'Customweb/Licensing/UnzerCw/License.php';
				$reason = Customweb_Licensing_UnzerCw_License::getValidationErrorMessage();
				if ($reason === null) {
					$reason = 'Unknown error.';
				}
				$token = Customweb_Licensing_UnzerCw_License::getCurrentToken();
				
				echo '<div style="border: 1px solid #ff0000; background: #ffcccc; font-weight: bold; padding: 5px; margin: 10px;">' .
						UnzerCw_Language::_('There is a problem with your license. Please contact us (www.sellxed.com/support). Reason: !reason Current Token: !token', array('!reason' => $reason, '!token' => $token)) .
						'</div>';
			}
			
		}
	}
	
}
catch(Exception $e) {
	echo $e->getMessage();
	echo "<br /><br />";
	echo $e->getTraceAsString();
}




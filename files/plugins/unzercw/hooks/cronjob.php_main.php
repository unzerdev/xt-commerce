<?php 

require _SRV_WEBROOT . 'plugins/unzercw/init.php';
require_once 'Customweb/Cron/Processor.php';

require_once 'UnzerCw/Util.php';

$packages = array(
			0 => 'Customweb_Unzer',
 			1 => 'Customweb_Payment_Authorization',
 		);
$packages[] = 'UnzerCw_';

$cron = new Customweb_Cron_Processor(UnzerCw_Util::createContainer(), $packages);
$cron->run();


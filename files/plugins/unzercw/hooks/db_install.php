<?php 

require _SRV_WEBROOT . 'plugins/unzercw/init.php';
require_once 'UnzerCw/Installer.php';

$installer = new UnzerCw_Installer();
$installer->install();


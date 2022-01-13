<?php 

require _SRV_WEBROOT . 'plugins/unzercw/init.php';
require_once 'UnzerCw/Backend/Controller/Order.php';

$controller = new UnzerCw_Backend_Controller_Order();
$js .= $controller->orderViewAction($this->order_data);


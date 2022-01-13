<?php 

if ($_GET['type'] == 'unzercw') {
	include (_SRV_WEBROOT_ADMIN.'page_includes.php');
	require _SRV_WEBROOT . 'plugins/unzercw/init.php';
	require_once 'UnzerCw/Dispatcher.php';
	require_once 'UnzerCw/ErrorHandler.php';
	
	try {
		$handler = new UnzerCw_ErrorHandler();
		$handler->start();
		$dispatcher = new UnzerCw_Dispatcher(
			_SRV_WEBROOT . 'plugins/unzercw/lib/UnzerCw/Backend/Controller',
			'UnzerCw_Backend_Controller_'
		);
		$dispatcher->dispatch();
		die();
	}
	catch (Exception $e) {
		echo $e->getMessage();
		echo '<br />';
		echo '<br />';
	
		echo '<pre>';
		echo $e->getTraceAsString();
		echo '</pre>';
		die();
	}
	
}

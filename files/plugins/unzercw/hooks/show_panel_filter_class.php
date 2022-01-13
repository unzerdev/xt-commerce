<?php 

if ($table === 'unzercw_transactions') {
	require _SRV_WEBROOT . 'plugins/unzercw/init.php';
	require_once 'UnzerCw/TransactionFilter.php';
	$a = new UnzerCw_TransactionFilter();
	$formFields = $a->formFields();
}
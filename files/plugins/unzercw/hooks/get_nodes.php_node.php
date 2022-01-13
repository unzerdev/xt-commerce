<?php 


// We need to include the loader again, because the include path is reset in between.
require _SRV_WEBROOT . 'plugins/unzercw/init.php';
require_once 'UnzerCw/Util.php';

if (!function_exists('getUnzerCwStoreNodes')) {
	function getUnzerCwStoreNodes() {
		if ($parent=='stores') {
			$store_id = 0;
		}
		if (strstr($parent,'stores_')) {
			$tmp = explode('_',$parent);
			$store_id = (int)$tmp[1];
		}
		$storeHandler = new multistore();
		$stores = $storeHandler->getStores();
		$nodes = array();
		if (is_array($stores) && count($stores) > 1) {
			foreach ($stores as $key => $store)  {
				$icon='images/icons/server.png';
				if ($store['status']==0) {
					$icon='images/icons/server_delete.png';
				}
				$url_d = 'adminHandler.php?load_section=unzercw_forms&plugin=unzercw&parentNode=node_unzercw_forms&store_id='.$store['id'];
				$nodes[] = array('text' => '____paymentServiceProvider____: ' . $store['text']
					,'url_i' => ''
					,'url_d' => $url_d
					,'tabtext' => $store['text']
					,'id' => 'store_'.$store['id']
					,'type'=>'I'
					,'leaf'=>'1'
					,'icon'=>$icon
				);
			}
	
		}
		return $nodes;
	}
	
}

if (strstr($pid, 'node_unzercw_forms')) {
	$arr = getUnzerCwStoreNodes();
}

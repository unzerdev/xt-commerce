<?php 


if ($request["get"] == "cw_active_inactive") {
	$result = array(
		array('id' => 'active', 'name' => 'Active', 'desc' => '' ),
		array('id' => 'inactive', 'name' => 'Inactive', 'desc' => '' ),
	);
}
else if (strpos($request["get"], 'unzercw') === 0) {
	
	$isOrderStatus = false;
	
	if (strpos($request["get"], ':file') !== false) {
		$result = array(
			array('id' => 'default:path', 'name' => 'Use default File'),
		);
		foreach (UnzerCw_Util::getUploadedFileList() as $file) {
			$result[] = array('id' => $file, 'name' => $file);
		}
		
	}
	else {
		if (strpos($request["get"], ':order_status') !== false) {
			$request["get"] = str_replace(':order_status', '', $request["get"]);
			$isOrderStatus = true;
		}
		
		$prefix = substr($request["get"], 0, strpos($request["get"], '___'));
		require dirname(__FILE__) . '/dropdowns/' . $prefix . '.php';
		if (isset($UnzerCwDropDownArray) && isset($UnzerCwDropDownArray[$request["get"]])) {
			$result = array();
			foreach ($UnzerCwDropDownArray[$request["get"]] as $id => $name) {
				$result[] = array('id' => $id, 'name' => $name);
			}
		}
		
		if ($isOrderStatus) {
			require _SRV_WEBROOT . 'plugins/unzercw/init.php';
			require_once 'UnzerCw/OrderStatus.php';
			$status = UnzerCw_OrderStatus::getSettingsOrderStatusList();
			$lang = $_SESSION['selected_language'];
			foreach ($status as $statusId => $names) {
				$name = $statusId;
				if (isset($names[$lang]) && !empty($names[$lang])) {
					$name = $names[$lang];
				}
				else if (isset($names['en']) && !empty($names['en'])){
					$names['en'];
				}
				else if (isset($names['de']) && !empty($names['de'])){
					$names['de'];
				}
				$result[] = array('id' => $statusId, 'name' => $name);
			}
		}
	}
	
}

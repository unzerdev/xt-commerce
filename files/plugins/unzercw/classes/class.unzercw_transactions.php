<?php 

require _SRV_WEBROOT . 'plugins/unzercw/init.php';
require_once 'UnzerCw/TransactionFilter.php';
require_once 'UnzerCw/Util.php';

class unzercw_transactions
{
	protected $_table = 'unzercw_transactions';
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'transactionId';
	


	function setPosition ($position) {
		$this->position = $position;
	}
	
	
	
	function _getParams ()
	{
		$params = array();
	
		$header = array();
		$header['transactionId'] = array('disabled' => 'true');
		$header['transactionExternalId'] = array('disabled' => 'true');
		$header['orderId'] = array('disabled' => 'true');
		$header['paymentMachineName'] = array('disabled' => 'true');
		$header['updatedOn'] = array('disabled' => 'true', 'readonly' => true);
		$header['paymentId'] = array('disabled' => 'true');
		$header['updatable'] = array('disabled' => 'true');
		$header['authorizationAmount'] = array('disabled' => 'true');
		$header['authorizationStatus'] = array('disabled' => 'true');
		$header['paid'] = array('disabled' => 'true');
		$header['currency'] = array('disabled' => 'true', );
		
		$header['aliasForDisplay'] = array('type' => 'hidden');
		$header['aliasActive'] = array('type' => 'hidden');
		$header['transactionObject'] = array('type' => 'hidden');
		$header['updatable'] = array('type' => 'hidden');
		$header['executeUpdateOn'] = array('type' => 'hidden');
		$header['lastSetOrderStatus'] = array('type' => 'hidden');
		
		$params['header'] = $header;
		$params['master_key'] = $this->_master_key;
		$params['default_sort'] = $this->_master_key;

		$params['default_sort']   = 'transactionId';
		$params['RemoteSort']   = true;
		
	
		$params['display_newBtn'] = false;
		$params['display_deleteBtn'] = false;
		$params['display_editBtn'] = false;
	
		$params['exclude'] = array('aliasForDisplay', 'aliasActive', 'transactionObject', 'updatable', 'executeUpdateOn', 'lastSetOrderStatus',
			'customerId','createdOn','authorizationType', 'lastSetOrderStatusSettingKey','storeId', 'sessionData', 'versionNumber', 
			'liveTransaction', 'transactionObjectBinary', 'sessionDataBinary');
	
		
		
		$rowActions[] = array('iconCls' => 'edit', 'qtipIndex' => 'qtip1', 'tooltip' => UnzerCw_Language::_('View Transaction'));
		if ($this->url_data['edit_id']) {
			$js = "var edit_id = " . $this->url_data['edit_id'] . ";";
		}
		else {
			$js = "var edit_id = record.id;";
		}
		$js .= "addTab('row_actions.php?type=unzercw&controller=transaction&action=view&transaction_id='+edit_id,'" . UnzerCw_Language::_('View Transaction') . "')";
		$rowActionsFunctions['edit'] = $js;
		
		$params['rowActions'] = $rowActions;
		$params['rowActionsFunctions'] = $rowActionsFunctions;
		
		
		return $params;
	}
	
	
	public function _get ($ID = 0)
	{
		global $xtPlugin, $db, $language;
	
		if ($this->position != 'admin') {
			return false;
		}
	
	
		$ID = (int)$ID;
		
		if ($this->url_data['get_data']) {
			
			// Handle Filter:
			$where = '';
			if (Customweb_Core_Util_Class::isClassLoaded('UnzerCw_TransactionFilter')) {
				$where = $this->getFilter() . $this->getSortOrder();
			}
			
			$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $master_qry='', $master_limit='', $perm_data='', $filter_data = '', $where);
			$data = $table_data->getData();
		} elseif ($ID) {
			$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);
			$data = $table_data->getData($ID);
	
		} else {
			$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);
			$data = $table_data->getHeader();
		}
	
		$obj = new stdClass;
		$obj->totalCount = count($data);

		// Clean the data.
		foreach ($data as $key => $item) {
			unset($item['transactionObject']);
			unset($item['transactionObjectBinary']);
			unset($item['sessionDataBinary']);
			$data[$key] = $item;
		}
		
		$obj->data = $data;
	
		return $obj;
	}
	
	private function getSortOrder() {
		$sort_order = '';
		$sort_by = 'transactionId';
		$sort_dir = 'DESC';
		if(isset($this->url_data['dir'])) {
			if ($this->url_data['dir'] == 'ASC') {
				$sort_dir = 'ASC';
			}
			else {
				$sort_dir = 'DESC';
			}
		}
			
		$data_read = new adminDB_DataRead($this->_table,'','', $this->_master_key);
		$fields = $data_read->getTableFields($this->_table);
		if (!isset($fields[$sort_by])) {
			$sort_by = 'transactionId';
		}
		$sort_order = ' ORDER BY ' . $this->_table . '.' . $sort_by . ' ' . $sort_dir;
		
		return $sort_order;
	}
	
	private function getFilter() {

		$driver = UnzerCw_Util::getDriver();
		$where_ar = array();
		if(UnzerCw_TransactionFilter::setTxt('filter_id_from') ){
			$where_ar[] = $this->_table . ".transactionId >='". (int)$_SESSION['filter_id_from']."'";
		}
		if( UnzerCw_TransactionFilter::setTxt('filter_id_to') ){
			$where_ar[] = $this->_table . ".transactionId <= '". (int)$_SESSION['filter_id_to']."'";
		}

		if( UnzerCw_TransactionFilter::setTxt('filter_payment_id') ){
			$where_ar[] = $this->_table . ".paymentId LIKE ". $driver->quote( '%' . $_SESSION['filter_payment_id'] . '%')."";
		}

		if( UnzerCw_TransactionFilter::setTxt('filter_external_id') ){
			$where_ar[] = $this->_table . ".transactionExternalId LIKE ". $driver->quote('%' . $_SESSION['filter_external_id'] . '%')."";
		}

		if( UnzerCw_TransactionFilter::setTxt('filter_payment_method') ){
			$where_ar[] = $this->_table . ".paymentMachineName LIKE ". $driver->quote('%' . $_SESSION['filter_payment_method'] . '%')."";
		}
		
		if(UnzerCw_TransactionFilter::setTxt('filter_amount_from')){
			$where_ar[] = " authorizationAmount >='". (float)$_SESSION['filter_amount_from']."'";
		}
		if( UnzerCw_TransactionFilter::setTxt('filter_amount_to')){
			$where_ar[] = " authorizationAmount <='". (float)$_SESSION['filter_amount_to']."'";
		}

		if( UnzerCw_TransactionFilter::setTxt('filter_authorization_status') ){
			$where_ar[] = $this->_table . ".authorizationStatus LIKE ". $driver->quote('%' . $_SESSION['filter_authorization_status'] . '%')."";
		}
		
		if (UnzerCw_TransactionFilter::setTxt('filter_last_modify_from')) {
			$where_ar[] = " updatedOn >='" . UnzerCw_TransactionFilter::date_trans($_SESSION['filter_last_modify_from']) . "'";
		}
		
		if (UnzerCw_TransactionFilter::setTxt('filter_last_modify_to')) {
			$where_ar[] = " updatedOn <= '" . UnzerCw_TransactionFilter::date_trans($_SESSION['filter_last_modify_to']) . "'";
		}
		
		if(count($where_ar) > 0 ){
			return " WHERE " . implode(" AND ", $where_ar) . ' ';
		}
		else {
			return '';
		}
	}
	
	public function _set($data, $set_type = 'edit') {
		
	
		return null;
	}
	
}
<?php 

require _SRV_WEBROOT . 'plugins/unzercw/init.php';
require_once 'Customweb/Core/Exception/CastException.php';
require_once 'Customweb/Payment/BackendOperation/Form/IAdapter.php';

require_once 'UnzerCw/Language.php';
require_once 'UnzerCw/Util.php';


class unzercw_forms
{
	protected $_master_key = 'machineName';
	
	function setPosition ($position) {
		$this->position = $position;
	}
	
	
	
	function _getParams ()
	{
		$params = array();
	
		$header = array();
		$header['title'] = array('disabled' => 'true');
		$header['machineName'] = array('type' => 'hidden');
		
		$params['header'] = $header;
		$params['master_key'] = $this->_master_key;
		$params['default_sort'] = $this->_master_key;

		$params['default_sort']   = 'title';
		$params['RemoteSort']   = true;
		
	
		$params['display_newBtn'] = false;
		$params['display_deleteBtn'] = false;
		$params['display_editBtn'] = false;
		
		$params['exclude'] = array('machineName');
	
		$rowActions[] = array('iconCls' => 'edit', 'qtipIndex' => 'qtip1', 'tooltip' => UnzerCw_Language::_('View'));
		if ($this->url_data['edit_id']) {
			$js = "var edit_id = " . $this->url_data['edit_id'] . ";";
		}
		else {
			$js = "var edit_id = record.id;";
		}
		$js .= "addTab('row_actions.php?type=unzercw&controller=form&action=view&store_id=" . $_GET['store_id'] . "&form='+edit_id,'" . UnzerCw_Language::_('View') . "')";
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
			$data = array();
			$container = UnzerCw_Util::createContainer();
			if ($container->hasBean('Customweb_Payment_BackendOperation_Form_IAdapter')) {
				$adapter = $container->getBean('Customweb_Payment_BackendOperation_Form_IAdapter');
				if (!($adapter instanceof Customweb_Payment_BackendOperation_Form_IAdapter)) {
					throw new Customweb_Core_Exception_CastException('Customweb_Payment_BackendOperation_Form_IAdapter');
				}
				foreach ($adapter->getForms() as $form) {
					$data[] = array('machineName' => $form->getMachineName(), 'title' => (string)$form->getTitle());
				}
			}
		} elseif ($ID) {
			throw new Exception("Not supported operation.");	
		} else {
			$data = array(array(
				'title' => '',
				'machineName' => '',
			));
		}
	
		$obj = new stdClass;
		$obj->totalCount = count($data);
		$obj->data = $data;
	
		return $obj;
	}
	
	
	public function _set($data, $set_type = 'edit') {
		
	
		return null;
	}
	
}
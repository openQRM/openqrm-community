<?php
/**
 * LVM-Storage Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class lvm_storage_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'lvm_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "lvm_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'lvm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'lvm_identifier';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'select' => array (
		'tab' => 'Select lvm-storage',
		'label' => 'Select lvm-storage',
		'action_edit' => 'edit',
		'table_name' => 'Name',
		'table_id' => 'Id',
		'table_recource' => 'Resource',
		'table_type' => 'Type',
		'table_deployment' => 'Deployment',
		'error_no_storage' => '<b>No storage configured yet!</b><br><br>Please create a lvm Storage first!',
		'new_storage' => 'New Storage',
		'please_wait' => 'Loading Volume groups. Please wait ..',
	), 
	'edit' => array (
		'tab' => 'Select LVM Volume group',
		'label' => 'Select LVM Volume group on storage %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'lang_deployment' => 'Deployment',
		'action_edit' => 'select',
		'action_add' => 'Add new Volume Group',
		'action_remove' => 'remove',
		'table_name' => 'Name',
		'table_pv' => 'PV',
		'table_lv' => 'LV',
		'table_sn' => 'SN',
		'table_attr' => 'Attr',
		'table_vsize' => 'Vsize',
		'table_vfree' => 'VFree',
		'error_no_lvm' => 'Storage %s is not of type lvm-storage',
		'please_wait' => 'Loading Volume group. Please wait ..',
	),
	'addvg' => array(
		'tab' => 'Add Volume Group',
		'label' => 'Add Volume Group to storage %s',
		'partition' => 'Partition',
		'name' => 'Name',
		'extend' => 'extend partition',
		'confirm_text' => 'All Data on %s will be erased.<br>Are you sure to continue?',
		'msg_added' => 'Successfully added Volume Group %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
	),
	'removevg' => array(
		'tab' => 'Remove Volume Group',
		'label' => 'Remove Volume Group on storage %s',
		'confirm_text' => 'Do you realy want to remove Volume Group %s?',
		'msg_removed' => 'Successfully removed Volume Group %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
	),
	'volgroup' => array (
		'tab' => 'Edit LVM Volume group',
		'label' => 'Edit LVM Volume group %s on storage %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_deployment' => 'Deployment',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'lang_attr' => 'Attr',
		'lang_pv' => 'PV / LV / SN',
		'lang_size' => 'Vsize / Vfree',
		'action_add' => 'Add new logical volume',
		'action_remove' => 'remove',
		'action_resize' => 'resize',
		'action_snap' => 'snap',
		'action_clone' => 'clone',
		'action_add_image' => 'Add Image',
		'action_remove_image' => 'Remove Image',
		'action_sync_in_progress' => 'Source of synchronisation - Please wait', 
		'action_clone_in_progress' => 'Synchronisation in progress - Please wait',
		'action_clone_finished' => 'Syncronisation finished!',
		'table_name' => 'Lvol',
		'table_deployment' => 'Deployment',
		'table_attr' => 'Attr',
		'table_size' => 'Size',
		'table_source' => 'Source',
		'error_no_lvm' => 'Storage %s is not of type lvm-deployment',
		'please_wait' => 'Loading Volume group. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add Logical Volume',
		'label' => 'Add Logical Volume to LVM Volume group %s',
		'form_name' => 'Name',
		'form_size' => 'Size (max: %s MB)',
		'msg_added' => 'Added Logical Volume %s',
		'msg_add_failed' => 'Failed to add Volume %s',
		'error_exists' => 'Logical Volume %s allready exists',
		'error_name' => 'Name must be %s',
		'error_size' => 'Size must be %s',
		'error_size_exeeded' => 'Size exeeds %s MB',
		'please_wait' => 'Adding Logical Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'image' => array (
		'label' => 'Add/Remove Image for Logical Volume %s',
		'tab' => 'Add/Remove Image',
		'error_exists' => 'Image %s allready exists',
		'error_image_still_in_use' => 'Image id %s is still in use by Server(s) %s',
		'msg_added_image' => 'Added Image %s',
		'msg_removed_image' => 'Removed Image id %s',
		'msg_add_failed' => 'Failed to add Image id %s',
		'please_wait' => 'Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'snap' => array (
		'label' => 'Snap Logical Volume %s',
		'tab' => 'Snap Logical Volume',
		'msg_snaped' => 'Snaped %s to %s',
		'msg_snap_failed' => 'Failed to clone Volume %s',
		'form_name' => 'Name',
		'form_size' => 'Size (max: %s MB)',
		'error_exists' => 'Volume %s allready exists',
		'error_name' => 'Name must be %s',
		'error_size' => 'Size must be %s',
		'error_size_exeeded' => 'Size exeeds %s MB',
		'please_wait' => 'Snaping Logical Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'clone' => array (
		'label' => 'Clone Logical Volume %s',
		'tab' => 'Clone Logical Volume',
		'msg_cloned' => 'Cloned %s as %s',
		'msg_clone_failed' => 'Failed to clone Volume %s',
		'form_name' => 'Name',
		'error_exists' => 'Volume %s allready exists',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Cloning Logical Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'label' => 'Remove Logical Volume(s)',
		'msg_removed' => 'Removed Logical Volume %s',
		'msg_image_still_in_use' => 'Volume %s of Image id %s is still in use by appliance(s) %s',
		'please_wait' => 'Removing Logical Volume(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'resize' => array (
		'label' => 'Resize Logical Volume %s',
		'tab' => 'Resize Logical Volume',
		'size' => 'min. %s MB, max. %s MB',
		'error_size' => 'Size must be %s',
		'error_size_exeeded' => 'Size exeeds %s MB',
		'error_size_undercut' => 'Size undercuts %s MB',
		'msg_resized' => 'Resized Logical Volume %s',
		'please_wait' => 'Resizing Logical Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'custom' => array (
		'tab' => 'Custom Volumes',
		'label' => 'Custom Volumes',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_deployment' => 'Deployment',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'action_add' => 'Add new Custom Volume',
		'action_remove' => 'remove',
		'table_name' => 'Custom Images',
		'table_initiator' => 'Initiator Name',
		'table_target' => 'Target',
		'table_lun' => 'Lun',
		'table_username' => 'Username',
		'table_export' => 'NFS Export',
		'table_deployment' => 'Deployment',
		'table_source' => 'Source',
		'error_no_lvm' => 'Storage %s is not of type lvm-deployment',
		'please_wait' => 'Loading Custom Images. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'customadd' => array (
		'tab' => 'Add Custom Volume',
		'label' => 'Add Custom Volume',
		'form_name' => 'Name',
		'form_target' => 'Target Name',
		'form_lun' => 'Lun Number',
		'form_initiator' => 'Initiator Name',
		'form_username' => 'Username',
		'form_password' => 'Password',
		'form_export' => 'NFS Export',
		'msg_added' => 'Added Custom Volume %s',
		'msg_add_failed' => 'Failed to add Volume %s',
		'error_exists' => 'Custom Volume %s allready exists',
		'error_name' => 'Name must be %s',
		'error_target' => 'Target Name must be %s',
		'error_lun' => 'Lun must be %s',
		'error_initiator' => 'Initiator Name must be %s',
		'error_username' => 'Username must be %s',
		'error_password' => 'Password must be %s',
		'error_export' => 'Export must be %s',
		'please_wait' => 'Adding Custom Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'customremove' => array (
		'label' => 'Remove Custom Volume(s)',
		'msg_removed' => 'Removed Custom Volume %s',
		'msg_image_still_in_use' => 'Volume %s of Image id %s is still in use by appliance(s) %s',
		'please_wait' => 'Removing Logical Volume(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param openqrm $openqrm
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/lvm-storage/lang", 'lvm-storage.ini');
		$this->tpldir   = $this->rootdir.'/plugins/lvm-storage/tpl';
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			if(is_array($ar)) {
				$this->action = key($ar);
			} else {
				$this->action = $ar;
			}
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			if($this->action === 'addvg' || $this->action === 'removevg') {
				$this->action = 'edit';
			} else {
				$this->action = 'volgroup';
			}
		}
		if($this->action === '') {
			$this->action = 'select';
		}
		// Set response and reload statfile
		if($this->action !== 'select') {
			$this->response->params['storage_id'] = $this->response->html->request()->get('storage_id');
			if($this->action !== 'remove' && $this->action !== 'addvg' && $this->action !== 'removevg' && $this->action !== 'custom' && $this->action !== 'customremove' && $this->action !== 'customadd') {
				$this->__reload('vg');
			}
			if($this->action !== 'edit') {
				$this->response->params['volgroup'] = $this->response->html->request()->get('volgroup');
			}
		}
		
		// handle custom storage
		if($this->action === 'edit') {
			$storage = new storage();
			$storage->get_instance_by_id($this->response->html->request()->get('storage_id'));
			$deployment = new deployment();
			$deployment->get_instance_by_id($storage->type);
			if ($deployment->name == 'custom-iscsi-deployment') {
				$this->action = 'custom';
			}
			if ($deployment->name == 'custom-nfs-deployment') {
				$this->action = 'custom';
			}
		}
		
		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'edit':
				$content[] = $this->select(false);
				$content[] = $this->edit(true);
			break;
			case 'addvg':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->addvg(true);
			break;
			case 'removevg':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->removevg(true);
			break;
			case 'volgroup':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(true);
			break;
			case 'add':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->add(true);
			break;
			case 'image':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->image(true);
			break;
			case 'resize':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->resize(true);
			break;
			case 'snap':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->snap(true);
			break;
			case 'clone':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->duplicate(true);
			break;
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->remove(true);
			break;
			case 'custom':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->custom(true);
			break;
			case 'customremove':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->customremove(true);
			break;
			case 'customadd':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->customadd(true);
			break;
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * API
	 *
	 * @access public
	 */
	//--------------------------------------------
	function api() {
		require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.api.class.php');
		$controller = new lvm_storage_api($this);
		$controller->action();
	}
	
	//--------------------------------------------
	/**
	 * Select Storages of type lvm
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.select.class.php');
			$controller = new lvm_storage_select($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['select'];

			$data = $controller->action();
		}
		$content['label']   = $this->lang['select']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Edit lvm-storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.edit.class.php');
			$controller                  = new lvm_storage_edit($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['edit'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['edit']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'edit' );
		$content['onclick'] = false;
		if($this->action === 'edit'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add lvm volume group
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function addvg( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.addvg.class.php');
			$controller                  = new lvm_storage_addvg($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->rootdir.'/plugins/device-manager/tpl/';
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['addvg'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['addvg']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'addvg' );
		$content['onclick'] = false;
		if($this->action === 'addvg'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Remove lvm volume group
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function removevg( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.removevg.class.php');
			$controller                  = new lvm_storage_removevg($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->rootdir.'/plugins/device-manager/tpl/';
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['removevg'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['removevg']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'removevg' );
		$content['onclick'] = false;
		if($this->action === 'removevg'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Edit lvm volgroup
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function volgroup( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->response->html->request()->get('reload') !== 'false') {
				$this->__reload('lv');
			}
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.volgroup.class.php');
			$controller                  = new lvm_storage_volgroup($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['volgroup'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['volgroup']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'volgroup' );
		$content['onclick'] = false;
		if($this->action === 'volgroup'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add new Export
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.add.class.php');
			$controller                = new lvm_storage_add($this->openqrm, $this->response, $this);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['add'];
			$controller->rootdir       = $this->rootdir;
			$controller->prefix_tab    = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['add']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add' );
		$content['onclick'] = false;
		if($this->action === 'add'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add/Remvoe Image object
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function image( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.image.class.php');
			$controller                  = new lvm_storage_image($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['image'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['image']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'image' );
		$content['onclick'] = false;
		if($this->action === 'image'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Remove Export
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.remove.class.php');
			$controller                  = new lvm_storage_remove($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['remove'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Remove';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove' );
		$content['onclick'] = false;
		if($this->action === 'remove' || $this->action === $this->lang['volgroup']['action_remove']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Snapshot Export
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function snap( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.snap.class.php');
			$controller                  = new lvm_storage_snap($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['snap'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['snap']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'snap' );
		$content['onclick'] = false;
		if($this->action === 'snap' || $this->action === $this->lang['edit']['action_snap']){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Clone Export
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function duplicate( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.clone.class.php');
			$controller                  = new lvm_storage_clone($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['clone'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['clone']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'clone' );
		$content['onclick'] = false;
		if($this->action === 'clone' || $this->action === $this->lang['edit']['action_clone']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Resize Export
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function resize( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.resize.class.php');
			$controller                  = new lvm_storage_resize($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['resize'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['resize']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'resize' );
		$content['onclick'] = false;
		if($this->action === 'resize'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Cusdtom lvm-storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function custom( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.custom.class.php');
			$controller                  = new lvm_storage_custom($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['custom'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['custom']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'custom' );
		$content['onclick'] = false;
		if($this->action === 'custom'){
			$content['active']  = true;
		}
		return $content;
	}
	

	
	//--------------------------------------------
	/**
	 * Custom Add
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function customadd( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.customadd.class.php');
			$controller                = new lvm_storage_customadd($this->openqrm, $this->response, $this);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['customadd'];
			$controller->rootdir       = $this->rootdir;
			$controller->prefix_tab    = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['customadd']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'customadd' );
		$content['onclick'] = false;
		if($this->action === 'customadd'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Custom Remove
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function customremove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage.customremove.class.php');
			$controller                  = new lvm_storage_customremove($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['customremove'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'customremove';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'customremove' );
		$content['onclick'] = false;
		if($this->action === 'customremove' || $this->action === $this->lang['volgroup']['action_remove']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Reload Exports
	 *
	 * @access public
	 */
	//--------------------------------------------
	function __reload($mode) {
		$OPENQRM_SERVER_BASE_DIR = $this->openqrm->get('basedir');
		$storage_id = $this->response->html->request()->get('storage_id');
		$volgroup   = $this->response->html->request()->get('volgroup');
		$storage = new storage();
		$resource = new resource();
		$deployment = new deployment();
		$storage->get_instance_by_id($storage_id);
		$resource->get_instance_by_id($storage->resource_id);
		$deployment->get_instance_by_id($storage->type);

		$command = '';
		$file = '';
		// reload volume group
		if($mode === 'vg') {
			$file = $this->openqrm->get('basedir').'/plugins/lvm-storage/web/storage/'.$resource->id.'.vg.stat';
			$command .= $OPENQRM_SERVER_BASE_DIR.'/plugins/lvm-storage/bin/openqrm-lvm-storage post_vg';
		}
		// reload logical volumes
		if($mode === 'lv') {
			$file = $this->openqrm->get('basedir').'/plugins/lvm-storage/web/storage/'.$resource->id.'.'.$volgroup.'.lv.stat';
			$command .= $OPENQRM_SERVER_BASE_DIR.'/plugins/lvm-storage/bin/openqrm-lvm-storage post_lv';
 			$command .=  ' -v '.$volgroup.' -t '.$deployment->type;
		}
		$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
		$command .= ' --openqrm-ui-user '.$this->user->name;
		$command .= ' --openqrm-cmd-mode background';
		if(file_exists($file)) {
			unlink($file);
		}
		$resource->send_command($resource->ip, $command);
		while (!file_exists($file)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		return true;
	}

}

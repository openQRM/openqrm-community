<?php
/**
 * KVM Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kvm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'kvm_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kvm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kvm_identifier';
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
		'tab' => 'Select KVM Host',
		'label' => 'Select KVM Host',
		'action_edit' => 'edit',
		'table_name' => 'Name',
		'table_id' => 'Id',
		'table_recource' => 'Resource',
		'table_type' => 'Type',
		'table_deployment' => 'Deployment',
		'title_vms' => 'Edit Virtual Machines',
		'title_lvm' => 'Edit LVM Storage',
		'title_bf' => 'Edit Blockfile Storage',
		'title_glusterfs' => 'Edit GlusterFS Storage',
		'error_no_storage' => '<b>No storage configured yet!</b><br><br>Please create a KVM first!',
		'new_storage' => 'New Storage',
		'network_manager' => 'Network',
		'please_wait' => 'Loading Volume groups. Please wait ..',
	), 
	'edit' => array (
		'tab' => 'Select Volume group',
		'label' => 'Select Volume group on KVM %s',
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
		'table_attr' => 'Attributes',
		'table_vg' => 'Volue Group Details',
		'table_bricks' => 'Gluster Bricks',
		'table_type' => 'Type',
		'table_status' => 'Status',
		'table_topology' => 'Topology',
		'table_transport' => 'Transport',
		'table_vsize' => 'Size',
		'table_vfree' => 'Free',
		'error_no_kvm' => 'Storage %s is not of type kvm',
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
		'tab' => 'Edit Volume group',
		'label' => 'Edit Volume group %s on KVM %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_deployment' => 'Deployment',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'lang_attr' => 'Attributes',
		'lang_pv' => 'PV / LV / SN',
		'lang_size' => 'Size / Free',
		'action_add' => 'Add new logical volume',
		'action_remove' => 'remove',
		'action_resize' => 'resize',
		'action_snap' => 'snap',
		'action_clone' => 'clone',
		'action_add_image' => 'Add',
		'action_sync' => 'Sync',
		'action_remove_image' => 'Remove',
		'action_sync_in_progress' => 'Source of synchronisation - Please wait', 
		'action_clone_in_progress' => 'Synchronisation in progress - Please wait',
		'action_clone_finished' => 'Syncronisation finished!',
		'table_state' => 'State',
		'table_name' => 'Lvol',
		'table_deployment' => 'Deployment',
		'table_attr' => 'Attributes',
		'table_path' => 'Path',
		'table_size' => 'Size',
		'table_source' => 'Source',
		'table_path_physical' => 'Physical',
		'table_path_glusters' => 'Logical',
		'error_no_kvm' => 'Storage %s is not of type kvm-deployment',
		'please_wait' => 'Loading Volume group. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add Logical Volume',
		'label' => 'Add Logical Volume to Volume group %s',
		'form_name' => 'Name',
		'form_size' => 'Size (max: %s MB)',
		'form_type' => 'Volume Type',
		'msg_added' => 'Added Logical Volume %s',
		'msg_add_failed' => 'Failed adding Logical Volume %s',
		'error_exists' => 'Logical Volume %s allready exists',
		'error_name' => 'Name must be %s',
		'error_size' => 'Size must be %s',
		'error_size_exeeded' => 'Size exeeds %s MB',
		'lang_name_generate' => 'generate name',
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
		'msg_synced_image' => 'Synced Image id %s',
		'please_wait' => 'Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'snap' => array (
		'label' => 'Snap Logical Volume %s',
		'tab' => 'Snap Logical Volume',
		'msg_snaped' => 'Snaped %s to %s',
		'msg_snap_failed' => 'Snapping failed for %s to %s',
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
		'msg_clone_failed' => 'Clone failed for %s as %s',
		'form_name' => 'Name',
		'error_exists' => 'Volume %s allready exists',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Cloning Logical Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'label' => 'Delete Logical Volume(s)',
		'msg_removed' => 'Deleted Logical Volume %s',
		'msg_vm_image_still_in_use' => 'Volume %s of Image id %s is still in use by appliance(s) %s',
		'please_wait' => 'Deleting Logical Volume(s). Please wait ..',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/kvm/lang", 'kvm.ini');
		$this->tpldir   = $this->rootdir.'/plugins/kvm/tpl';
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
			$this->response->add('storage_id', $this->response->html->request()->get('storage_id'));
			if($this->action !== 'remove' && $this->action !== 'addvg' && $this->action !== 'removevg') {
				$this->__reload('vg');
			}
			if($this->action !== 'edit') {
				$this->response->add('volgroup', $this->response->html->request()->get('volgroup'));
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
			case 'resize':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->resize(true);
			break;
			case 'image':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->image(true);
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
		require_once($this->rootdir.'/plugins/kvm/class/kvm.api.class.php');
		$controller = new kvm_api($this);
		$controller->action();
	}
	
	//--------------------------------------------
	/**
	 * Select Storages of type KVM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/kvm/class/kvm-vm.select.class.php');
			$controller = new kvm_vm_select($this->openqrm, $this->response);
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
	 * Edit KVM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/kvm/class/kvm.edit.class.php');
			$controller                  = new kvm_edit($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/kvm/class/kvm.addvg.class.php');
			$controller                  = new kvm_addvg($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/kvm/class/kvm.removevg.class.php');
			$controller                  = new kvm_removevg($this->openqrm, $this->response);
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
	 * Edit KVM volgroup
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
			require_once($this->rootdir.'/plugins/kvm/class/kvm.volgroup.class.php');
			$controller                  = new kvm_volgroup($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/kvm/class/kvm.add.class.php');
			$controller                = new kvm_add($this->openqrm, $this->response, $this);
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
			require_once($this->rootdir.'/plugins/kvm/class/kvm.remove.class.php');
			$controller                  = new kvm_remove($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/kvm/class/kvm.image.class.php');
			$controller                  = new kvm_image($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/kvm/class/kvm.snap.class.php');
			$controller                  = new kvm_snap($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/kvm/class/kvm.clone.class.php');
			$controller                  = new kvm_clone($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/kvm/class/kvm.resize.class.php');
			$controller                  = new kvm_resize($this->openqrm, $this->response);
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
			$file = $OPENQRM_SERVER_BASE_DIR.'/plugins/kvm/web/storage/'.$resource->id.'.vg.stat';
			$command .= $OPENQRM_SERVER_BASE_DIR.'/plugins/kvm/bin/openqrm-kvm post_vg -t '.$deployment->type;
		}
		// reload logical volumes
		if($mode === 'lv') {
			$file = $OPENQRM_SERVER_BASE_DIR.'/plugins/kvm/web/storage/'.$resource->id.'.'.$volgroup.'.lv.stat';
			$command .= $OPENQRM_SERVER_BASE_DIR.'/plugins/kvm/bin/openqrm-kvm post_lv';
 			$command .=  ' -v '.$volgroup.' -t '.$deployment->type;
		}
		$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
		$command .= ' --openqrm-ui-user '.$this->user->name;
		$command .= ' --openqrm-cmd-mode background';
		if($this->file->exists($file)) {
			$this->file->remove($file);
		}
		$resource->send_command($resource->ip, $command);
		while (!$this->file->exists($file)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		return true;
	}

}

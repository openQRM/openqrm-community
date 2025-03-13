<?php
/**
 * KVM-VM Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_vm_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kvm_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'kvm_vm_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kvm_vm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kvm_vm_identifier';
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
		'label' => 'Select KVM Host Appliance',
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
		'error_no_host' => '<b>No KVM Host Appliance configured yet!</b><br><br>Please create a KVM Host Appliance first!',
		'new' => 'New Appliance',
		'network_manager' => 'Network',
		'please_wait' => 'Loading VMs. Please wait ..',
		'new_storage' => 'New Storage',
	), 
	'edit' => array (
		'tab' => 'Select VM',
		'label' => 'KVM VMs on KVM Host Appliance %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'lang_vfree' => 'Free',
		'lang_vsize' => 'Total',
		'lang_password_show' => 'show password',
		'lang_password_hide' => 'hide password',
		'action_add_local_vm' => 'Add local VM',
		'action_add_network_vm' => 'Add network VM',
		'action_remove' => 'delete',
		'action_stop' => 'stop',
		'action_start' => 'start',
		'action_reboot' => 'reboot',
		'action_update' => 'update',
		'action_console' => 'console',
		'action_migrate' => 'migrate',
		'action_clone' => 'clone',
		'action_migrate_in_progress' => 'Migration in progress - Please wait',
		'action_migrate_finished' => 'Migration finished!',
		'table_state' => 'State',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_mac' => 'Mac',
		'table_ram' => 'RAM',
		'table_cpu' => 'CPU',
		'table_nics' => 'NIC',
		'table_id' => 'ID',
		'table_ip' => 'IP',
		'table_vnc' => 'VNC',
		'table_vncpassword' => 'VNC password',
		'table_appliance' => 'Server',
		'table_kernel' => 'Kernel',
		'table_image' => 'Image',
		'table_storage' => 'Storage',
		'error_no_host' => '<b>No KVM Host Appliance configured yet!</b><br><br>Please create a KVM Host Appliance first!',
		'please_wait' => 'Loading VMs. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add VM',
		'label' => 'Add new VM',
		'lang_basic' => 'Basic',
		'lang_hardware' => 'Hardware',
		'lang_net' => 'Network',
		'lang_net_0' => 'Network_0',
		'lang_net_1' => 'Network_1',
		'lang_net_2' => 'Network_2',
		'lang_net_3' => 'Network_3',
		'lang_net_4' => 'Network_4',
		'lang_boot' => 'Boot from',
		'lang_vnc' => 'VNC Password',
		'lang_virtual_disk' => 'Virtual disk image',
		'lang_browse' => 'browse',
		'lang_browser' => 'Filepicker',
		'lang_password_generate' => 'generate password',
		'lang_password_show' => 'show password',
		'lang_password_hide' => 'hide password',
		'form_name' => 'Name',
		'form_cpus' => 'CPU(s)',
		'form_mac' => 'Mac',
		'form_ipaddress' => 'IP Address',
		'form_memory' => 'Memory',
		'form_bridge' => 'Bridge',
		'form_boot_cd' => 'CD',
		'form_boot_iso' => 'Iso',
		'form_iso_path' => 'Path',
		'form_boot_net' => 'Net',
		'form_boot_local' => 'Local',
		'form_existing_disk' => 'Existing disk',
		'form_enable' => 'enable',
		'form_net_virtio' => 'Virtio',
		'form_net_e1000' => 'E1000',
		'form_net_rtl8139' => 'Rtl8139',
		'form_netdevice' => 'Networkcard',
		'form_disk_interface' => 'Disk Interface',
		'form_swap' => 'Swap',
		'form_disk' => 'Disk',
		'form_cdrom' => 'Cdrom (iso)',
		'form_vnc' => 'Password',
		'form_vnc_repeat' => 'Password (repeat)',
		'form_vnc_keymap' => 'VNC Keymap',
		'action_add_vm_image' => 'Add a new VM Image',
		'msg_added' => 'Added VM %s',
		'error_exists' => 'VM %s already exists',
		'error_name' => 'Name must be %s',
		'error_memory' => 'Memory must be %s',
		'error_mac' => 'Mac is not valid',
		'error_bridge' => 'Bridge is not valid',
		'error_nic' => 'Nic is not valid',
		'error_boot' => 'Please select a boot device',
		'error_iso_path' => 'Path must not be empty',
		'error_vnc_password' => 'Password (repeat) does not match Password',
		'error_vnc_password_count' => 'Password must have at least 6 chars',
		'error_no_bridge' => 'Error: No network bridge found.',
		'please_wait' => 'Adding VM. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'update' => array (
		'tab' => 'Update VM',
		'label' => 'Update VM %s',
		'lang_basic' => 'Basic',
		'lang_hardware' => 'Hardware',
		'lang_virtual_disk' => 'Virtual disk image',
		'lang_net' => 'Network',
		'lang_net_0' => 'Network_0',
		'lang_net_1' => 'Network_1',
		'lang_net_2' => 'Network_2',
		'lang_net_3' => 'Network_3',
		'lang_net_4' => 'Network_4',
		'lang_boot' => 'Boot from',
		'lang_vnc' => 'VNC Password',
		'lang_browse' => 'browse',
		'lang_browser' => 'Filepicker',
		'lang_password_generate' => 'generate password',
		'lang_password_show' => 'show password',
		'lang_password_hide' => 'hide password',
		'form_name' => 'Name',
		'form_cpus' => 'CPU(s)',
		'form_mac' => 'Mac',
		'form_memory' => 'Memory',
		'form_bridge' => 'Bridge',
		'form_boot_cd' => 'CD',
		'form_boot_iso' => 'Iso',
		'form_iso_path' => 'Path',
		'form_boot_net' => 'Net',
		'form_boot_local' => 'Local',
		'form_enable' => 'enable',
		'form_net_virtio' => 'Virtio',
		'form_net_e1000' => 'E1000',
		'form_net_rtl8139' => 'Rtl8139',
		'form_netdevice' => 'Networkcard',
		'form_disk_interface' => 'Disk Interface',
		'form_swap' => 'Swap',
		'form_disk' => 'Disk',
		'form_cdrom' => 'Cdrom (iso)',
		'form_vnc' => 'Password',
		'form_vnc_repeat' => 'Password (repeat)',
		'form_vnc_keymap' => 'VNC Keymap',
		'msg_updated' => 'Updated VM %s',
		'error_exists' => 'VM %s already exists',
		'error_name' => 'Name must be %s',
		'error_memory' => 'Memory must be %s',
		'error_mac' => 'Mac is not valid',
		'error_bridge' => 'Bridge is not valid',
		'error_nic' => 'Nic is not valid',
		'error_boot' => 'Please select a boot device',
		'error_iso_path' => 'Path must not be empty',
		'error_vnc_password' => 'Password (repeat) does not match Password',
		'error_vnc_password_count' => 'Password must have at least 6 chars',
		'error_no_bridge' => 'Error: No network bridge found.',
		'please_wait' => 'Updating VM. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'clone' => array (
		'label' => 'Clone VM %s',
		'tab' => 'Clone VM',
		'msg_cloned' => 'Cloned %s as %s',
		'form_name' => 'Name',
		'error_exists' => 'VM %s allready exists',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Cloning VM. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'migrate' => array (
		'label' => 'Migrate VM %s',
		'tab' => 'Migrate VM',
		'msg_migrated' => 'Migrated %s to %s',
		'error_no_hosts' => 'No KVM Host found to migrate to',
		'form_target' => 'Target Host Resource',
		'please_wait' => 'Migrating VM. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'start' => array (
		'msg_started' => 'Started VM %s',
	),
	'stop' => array (
		'msg_stoped' => 'Stopped VM %s',
	),
	'reboot' => array (
		'msg_rebooted' => 'Rebooted VM %s',
	),
	'remove' => array (
		'label' => 'Delete VM(s)',
		'msg_removed' => 'Deleted VM %s',
		'msg_vm_resource_still_in_use' => 'VM %s resource id %s is still in use by appliance(s) %s',
		'please_wait' => 'Removing VM(s). Please wait ..',
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
		$this->user     = $this->openqrm->get('user');
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/kvm/lang", 'kvm-vm.ini');
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
			$this->action = "edit";
		}
		if($this->action !== 'select') {
			$this->response->params['appliance_id'] = $this->response->html->request()->get('appliance_id');
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
			case 'reload':
				$this->action = 'edit';
				$content[] = $this->select(false);
				$content[] = $this->edit(true);
				$this->reload();
			break;
			case 'add':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->add(true);
			break;
			case 'update':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->update(true);
			break;
			case 'clone':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->duplicate(true);
			break;
			case 'migrate':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->migrate(true);
			break;
			case $this->lang['edit']['action_remove']:
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->remove(true);
			break;
			case $this->lang['edit']['action_start']:
			case 'start':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->start(true);
			break;
			case $this->lang['edit']['action_stop']:
			case 'stop':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->stop(true);
			break;
			case $this->lang['edit']['action_reboot']:
			case 'reboot':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->reboot(true);
			break;
			case 'sysinfo':
				$content[] = $this->select(false);
				$content[] = $this->sysinfo(true);
			break;
			// to pick an iso image for boot
			case 'iso':
				$content[] = $this->iso(true);
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
		require_once($this->rootdir.'/plugins/kvm/class/kvm-vm.api.class.php');
		$controller = new kvm_vm_api($this);
		$controller->action();
	}

	
	//--------------------------------------------
	/**
	 * Select KVM VM Host
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
	 * Edit KVM-VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->reload()) {
				require_once($this->rootdir.'/plugins/kvm/class/kvm-vm.edit.class.php');
				$controller                  = new kvm_vm_edit($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['edit'];
				$data = $controller->action();
			}
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
	 * Add new VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->reload() && $this->reload_bridges()) {
				require_once($this->rootdir.'/plugins/kvm/class/kvm-vm.add.class.php');
				$controller                = new kvm_vm_add($this->openqrm, $this->response, $this);
				$controller->actions_name  = $this->actions_name;
				$controller->tpldir        = $this->tpldir;
				$controller->message_param = $this->message_param;
				$controller->lang          = $this->lang['add'];
				$controller->rootdir       = $this->rootdir;
				$controller->prefix_tab    = $this->prefix_tab;
				$data = $controller->action();
			}
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
	 * Update VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function update( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->reload() && $this->reload_bridges()) {
				require_once($this->rootdir.'/plugins/kvm/class/kvm-vm.update.class.php');
				$controller                = new kvm_vm_update($this->openqrm, $this->response);
				$controller->actions_name  = $this->actions_name;
				$controller->tpldir        = $this->tpldir;
				$controller->message_param = $this->message_param;
				$controller->lang          = $this->lang['update'];
				$controller->rootdir       = $this->rootdir;
				$controller->prefix_tab    = $this->prefix_tab;
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['update']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'update' );
		$content['onclick'] = false;
		if($this->action === 'update'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Clone VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function duplicate( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->reload()) {
				require_once($this->rootdir.'/plugins/kvm/class/kvm-vm.clone.class.php');
				$controller                  = new kvm_vm_clone($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->lang            = $this->lang['clone'];
				$controller->rootdir         = $this->rootdir;
				$controller->prefix_tab      = $this->prefix_tab;
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['clone']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'clone' );
		$content['onclick'] = false;
		if($this->action === 'clone'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Migrate VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function migrate( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/kvm/class/kvm-vm.migrate.class.php');
			$controller                  = new kvm_vm_migrate($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['migrate'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['migrate']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'migrate' );
		$content['onclick'] = false;
		if($this->action === 'migrate'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Remove VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/kvm/class/kvm-vm.remove.class.php');
			$controller                  = new kvm_vm_remove($this->openqrm, $this->response);
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
		if($this->action === 'remove' || $this->action === $this->lang['edit']['action_remove']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Start VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function start( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/kvm/class/kvm-vm.start.class.php');
			$controller                  = new kvm_vm_start($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['start'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Start';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'start' );
		$content['onclick'] = false;
		if($this->action === 'start' || $this->action === $this->lang['edit']['action_start']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Stop VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function stop( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/kvm/class/kvm-vm.stop.class.php');
			$controller                  = new kvm_vm_stop($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['stop'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Stop';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'stop' );
		$content['onclick'] = false;
		if($this->action === 'stop' || $this->action === $this->lang['edit']['action_stop']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Reboot VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function reboot( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/kvm/class/kvm-vm.reboot.class.php');
			$controller                  = new kvm_vm_reboot($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['reboot'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Reboot';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'reboot' );
		$content['onclick'] = false;
		if($this->action === 'reboot' || $this->action === $this->lang['edit']['action_reboot']){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Pick iso
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function iso( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    require_once($this->rootdir.'/plugins/kvm/class/kvm-vm.iso.class.php');
		    $controller                  = new kvm_vm_iso($this->openqrm, $this->response);
		    $controller->actions_name    = $this->actions_name;
		    $controller->tpldir          = $this->tpldir;
		    $controller->message_param   = $this->message_param;
		    $controller->identifier_name = $this->identifier_name;
		    $controller->lang            = array();
		    $controller->rootdir         = $this->rootdir;
		    $controller->prefix_tab      = $this->prefix_tab;
		    $data = $controller->action();
		}
		$content['label']   = 'Pick ISO Image';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'iso' );
		$content['onclick'] = false;
		if($this->action === 'iso'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Sysinfo
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function sysinfo( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/kvm/class/kvm-vm.sysinfo.class.php');
			$controller                  = new kvm_vm_sysinfo($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = array();
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Sysinfo';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'sysinfo' );
		$content['onclick'] = false;
		if($this->action === 'sysinfo'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Reload VMs
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload() {
		$OPENQRM_SERVER_BASE_DIR = $this->openqrm->get('basedir');
		$command  = $OPENQRM_SERVER_BASE_DIR.'/plugins/kvm/bin/openqrm-kvm-vm post_vm_list';
		$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
		$command .= ' --openqrm-ui-user '.$this->user->name;
		$command .= ' --openqrm-cmd-mode background';
		$id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$file = $OPENQRM_SERVER_BASE_DIR.'/plugins/kvm/web/kvm-stat/'.$resource->id.'.vm_list';
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

	//--------------------------------------------
	/**
	 * Reload Bridges
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload_bridges() {
		$OPENQRM_SERVER_BASE_DIR = $this->openqrm->get('basedir');
		$command  = $OPENQRM_SERVER_BASE_DIR.'/plugins/kvm/bin/openqrm-kvm-vm post_bridge_config';
		$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
		$command .= ' --openqrm-ui-user '.$this->user->name;
		$command .= ' --openqrm-cmd-mode background';
		$id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$file = $OPENQRM_SERVER_BASE_DIR.'/plugins/kvm/web/kvm-stat/'.$resource->id.'.bridge_config';
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

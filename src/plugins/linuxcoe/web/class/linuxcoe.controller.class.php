<?php
/**
 * LinuxCOE-Storage Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class linuxcoe_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'linuxcoe_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'linuxcoe_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'linuxcoe_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'linuxcoe_identifier';
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
		'tab' => 'Select LinuxCOE-storage',
		'label' => 'Select LinuxCOE-storage',
		'action_edit' => 'edit',
		'table_name' => 'Name',
		'table_id' => 'Id',
		'table_recource' => 'Resource',
		'table_type' => 'Type',
		'table_deployment' => 'Deployment',
		'error_no_storage' => '<b>No storage configured yet!</b><br><br>Please create a LinuxCOE Storage first!',
		'new_storage' => 'New Storage',
		'please_wait' => 'Loading Storage. Please wait ..',
	), 
	'edit' => array (
		'tab' => 'Edit LinuxCOE-storage',
		'label' => 'LinuxCOE Volumes on storage %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'lang_vfree' => 'Free',
		'lang_vsize' => 'Total',
		'action_add' => 'Add new Volume',
		'action_refresh' => 'Reload Page',
		'action_manual' => 'Manual Configuration',
		'action_clone' => 'clone',
		'action_remove' => 'remove',
		'action_auth' => 'auth',
		'action_edit' => 'Edit Image of this Volume',
		'action_clone_in_progress' => 'Synchronisation in progress - Please wait',
		'action_clone_finished' => 'Syncronisation finished!',
		'table_name' => 'Name',
		'table_id' => 'ID',
		'table_root' => 'Root device',
		'table_description' => 'Description',
		'error_no_local' => 'Storage %s is not of type local-deployment',
		'please_wait' => 'Loading Volumes. Please wait ..',
		'manual_configured' => 'Storage is manually configured and can not be be edited by openQRM',
	),
	'add' => array (
		'tab' => 'Add LinuxCOE Volume',
		'label' => 'Add new Volume',
		'form_name' => 'Name',
		'form_root' => 'Device',
		'form_description' => 'Description',
		'msg_added' => 'Added Volume %s',
		'msg_add_failed' => 'Failed to add Volume %s',
		'error_exists' => 'Volume %s already exists',
		'error_image_exists' => 'Image with name %s already exists',
		'error_name' => 'Name must be %s',
		'error_description' => 'Description must be %s',
		'please_wait' => 'Adding Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'clone' => array (
		'tab' => 'Clone LinuxCOE Volume',
		'label' => 'Clone Volume %s',
		'form_name' => 'Name',
		'msg_cloned' => 'Cloned %s as %s',
		'msg_clone_failed' => 'Failed to clone Volume %s',
		'error_exists' => 'Volume %s already exists',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Cloning Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'label' => 'Remove Volume(s)',
		'msg_removed' => 'Removed Volume %s',
		'msg_image_still_in_use' => 'Volume %s of Image id %s is still in use by appliance(s) %s',
		'please_wait' => 'Removing Volume(s). Please wait ..',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/linuxcoe/lang", 'linuxcoe.ini');
		$this->tpldir   = $this->rootdir.'/plugins/linuxcoe/tpl';
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
			$this->response->params['storage_id'] = $this->response->html->request()->get('storage_id');
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
			case 'add':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->add(true);
			break;
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->remove(true);
			break;
			case 'clone':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->duplicate(true);
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
		require_once($this->rootdir.'/plugins/linuxcoe/class/linuxcoe.api.class.php');
		$controller = new linuxcoe_api($this);
		$controller->action();
	}

	
	//--------------------------------------------
	/**
	 * Select LinuxCOE Install Server
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/linuxcoe/class/linuxcoe.select.class.php');
			$controller = new linuxcoe_select($this->openqrm, $this->response);
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
	 * Edit LinuxCOE Server
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    require_once($this->rootdir.'/plugins/linuxcoe/class/linuxcoe.edit.class.php');
		    $controller                  = new linuxcoe_edit($this->openqrm, $this->response);
		    $controller->actions_name    = $this->actions_name;
		    $controller->tpldir          = $this->tpldir;
		    $controller->message_param   = $this->message_param;
		    $controller->identifier_name = $this->identifier_name;
		    $controller->prefix_tab      = $this->prefix_tab;
		    $controller->lang            = $this->lang['edit'];
			$controller->rootdir         = $this->rootdir;
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
		    require_once($this->rootdir.'/plugins/linuxcoe/class/linuxcoe.add.class.php');
		    $controller                = new linuxcoe_add($this->openqrm, $this->response, $this);
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
		    require_once($this->rootdir.'/plugins/linuxcoe/class/linuxcoe.remove.class.php');
		    $controller                  = new linuxcoe_remove($this->openqrm, $this->response);
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
	 * Clone Volume
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function duplicate( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    require_once($this->rootdir.'/plugins/linuxcoe/class/linuxcoe.clone.class.php');
		    $controller                  = new linuxcoe_clone($this->openqrm, $this->response);
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


}

<?php
/**
 * NFS-Storage Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class nfs_storage_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'nfs_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "nfs_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'nfs_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'nfs_identifier';
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
		'tab' => 'Select NFS-storage',
		'label' => 'Select NFS-storage',
		'action_edit' => 'edit',
		'table_name' => 'Name',
		'table_id' => 'Id',
		'table_recource' => 'Resource',
		'table_type' => 'Type',
		'table_deployment' => 'Deployment',
		'error_no_storage' => '<b>No storage configured yet!</b><br><br>Please create a NFS Storage first!',
		'new_storage' => 'New Storage',
		'please_wait' => 'Loading Storage. Please wait ..',
	), 
	'edit' => array (
		'tab' => 'Edit NFS-storage',
		'label' => 'NFS Volumes on storage %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'lang_vfree' => 'Free',
		'lang_vsize' => 'Total',
		'lang_authenticated_to' => 'exported to',
		'action_add' => 'Add new Volume',
		'action_refresh' => 'Reload Page',
		'action_manual' => 'Manual Configuration',
		'action_clone' => 'clone',
		'action_remove' => 'remove',
		'action_auth' => 'auth',
		'action_add_image' => 'Add Image',
		'action_remove_image' => 'Remove Image',
		'action_clone_in_progress' => 'Synchronisation in progress - Please wait',
		'action_clone_finished' => 'Syncronisation finished!',
		'table_name' => 'Name',
		'table_export' => 'Export',
		'error_no_nfs' => 'Storage %s is not of type nfs-deployment',
		'please_wait' => 'Loading Volumes. Please wait ..',
		'manual_configured' => 'Storage is manually configured and will not be administrated by openQRM',
	),
	'add' => array (
		'tab' => 'Add NFS Volume',
		'label' => 'Add new Volume',
		'form_name' => 'Name',
		'msg_added' => 'Added Volume %s',
		'msg_add_failed' => 'Failed to add Volume %s',
		'error_exists' => 'Volume %s already exists',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'image' => array (
		'label' => 'Add/Remove Image for Volume %s',
		'tab' => 'Add/Remove Image',
		'error_exists' => 'Image %s already exists',
		'error_image_still_in_use' => 'Image id %s is still in use by Server(s) %s',
		'msg_added_image' => 'Added Image %s',
		'msg_removed_image' => 'Removed Image id %s',
		'please_wait' => 'Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'manual' => array (
		'tab' => 'Manual NFS Configuration',
		'label' => 'Manual Configuration',
		'explanation_1' => 'In case the NFS Storage server is not managed by openQRM please use this form to manually create the list of exported paths to server-images on the NFS Storage server.',
		'explanation_2' => 'Please notice that in case a manual configuration exist openQRM will not send any automated Storage-authentication commands to this NFS Storage-server!',
		'please_wait' => 'Saving Manual Configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
		'saved' => 'Manual Configuration has been saved',
		'error_config' => 'Error: Please use only %s for Exports',
		'error_image_in_use' => 'Error: Image name %s for %s is already in use!',
		'error_name_empty' => 'Error: Name is empty or a path ends with a /',
	),
	'clone' => array (
		'tab' => 'Clone NFS Volume',
		'label' => 'Clone Volume %s',
		'form_name' => 'Name',
		'msg_cloned' => 'Cloned %s as %s',
		'msg_clone_failed' => 'Failed to clone Volume %s',
		'error_exists' => 'Volume %s already exists',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Cloning Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'auth' => array (
		'tab' => 'Authenticate NFS Volume',
		'label' => 'Authenticate Volume %s',
		'form_ip' => 'IP',
		'msg_authd' => 'Authenticated volume %s',
		'auth_explanation' => 'Please notice: Volumes which are deployed as an Image via a Server are authenticated automatically!',
		'error_ip' => 'IP Adress is not valid',
		'please_wait' => 'Authenticating Volume. Please wait ..',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/nfs-storage/lang", 'nfs-storage.ini');
		$this->tpldir   = $this->rootdir.'/plugins/nfs-storage/tpl';
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
			case 'image':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->image(true);
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
			case 'auth':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->auth(true);
			break;
			case 'manual':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->manual(true);
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
		require_once($this->rootdir.'/plugins/nfs-storage/class/nfs-storage.api.class.php');
		$controller = new nfs_storage_api($this);
		$controller->action();
	}

	
	//--------------------------------------------
	/**
	 * Select Storages of type NFS
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/nfs-storage/class/nfs-storage.select.class.php');
			$controller = new nfs_storage_select($this->openqrm, $this->response);
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
	 * Edit NFS-Storage
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
				require_once($this->rootdir.'/plugins/nfs-storage/class/nfs-storage.edit.class.php');
				$controller                  = new nfs_storage_edit($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/nfs-storage/class/nfs-storage.add.class.php');
			$controller                = new nfs_storage_add($this->openqrm, $this->response, $this);
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
			require_once($this->rootdir.'/plugins/nfs-storage/class/nfs-storage.image.class.php');
			$controller                  = new nfs_storage_image($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/nfs-storage/class/nfs-storage.remove.class.php');
			$controller                  = new nfs_storage_remove($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/nfs-storage/class/nfs-storage.clone.class.php');
			$controller                  = new nfs_storage_clone($this->openqrm, $this->response);
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
	 * Auth Volume
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function auth( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/nfs-storage/class/nfs-storage.auth.class.php');
			$controller                  = new nfs_storage_auth($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['auth'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['auth']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'auth' );
		$content['onclick'] = false;
		if($this->action === 'auth' || $this->action === $this->lang['edit']['action_auth']){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Manual Exports
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function manual( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/nfs-storage/class/nfs-storage.manual.class.php');
			$controller                  = new nfs_storage_manual($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['manual'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['manual']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'manual' );
		$content['onclick'] = false;
		if($this->action === 'manual'){
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
	function reload() {
		$OPENQRM_SERVER_BASE_DIR = $this->openqrm->get('basedir');
		$command  = $OPENQRM_SERVER_BASE_DIR."/plugins/nfs-storage/bin/openqrm-nfs-storage post_exports";
		$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
		$command .= ' --openqrm-ui-user '.$this->user->name;
		$command .= ' --openqrm-cmd-mode background';
		$storage_id = $this->response->html->request()->get('storage_id');
		$storage = new storage();
		$storage->get_instance_by_id($storage_id);
		$resource = new resource();
		$resource->get_instance_by_id($storage->resource_id);
		$file = $this->openqrm->get('basedir').'/plugins/nfs-storage/web/storage/'.$resource->id.'.nfs.stat';
		if($this->file->exists($file)) {
			$this->file->remove($file);
		}
		$resource->send_command($resource->ip, $command);
		while (!$this->file->exists($file))
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		return true;
	}

}

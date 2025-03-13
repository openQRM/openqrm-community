<?php
/**
 * Image Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2014, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2014, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class image_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'image_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "image_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'image_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'image_identifier';
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
		'tab' => 'Images',
		'label' => 'Images',
		'action_remove' => 'remove',
		'action_mgmt' => 'manage',
		'action_edit' => 'edit',
		'action_add' => 'Add a new Image',
		'action_install1' => 'install1',
		'action_install2' => 'install2',
		'table_name' => 'Name',
		'table_id' => 'ID',
		'table_version' => 'Version',
		'table_deployment' => 'Deployment',
		'table_isactive' => 'State',
		'table_comment' => 'Comment',
		'table_storage' => 'Storage',
		'table_edit' => 'Edit',
		'table_name' => 'Name',
		'table_capabilities' => 'Capabilities',
		'table_image' => 'Image',
		'table_root' => 'Root',
		'lang_active' => 'active',
		'lang_inactive' => 'inactive',
		'lang_filter' => 'Filter by Deployment',
		'please_wait' => 'Loading. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add Image',
		'label' => 'Add Image',
		'title' => 'Adding a new Image as',
		'start_storage_plugin' => 'Please enable and start one of the storage plugins!',
		'info' => 'The image represents the &quot;disk&quot; for a virtual machine. Dependent on the virtuallization technology it can be a blockfile (either in a directory on a local HDD or a network directory provided by e.g. NFS) and/or an empty partition (either a partition on a local HDD or network e.g. Iscsi), manged with LVM as partiotion manager.',
		'create_image' => 'a Volume from Storage type %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'tab' => 'Remove Image',
		'label' => 'Remove Images(s)',
		'msg' => 'Removed Image %s',
		'msg_not_removing_active' => 'Not removing Image %s!<br>It is still in use by Server %s !',
		'msg_not_removing' => 'Force-removal not set! Not removing Image %s!',
		'msg_force_remove' => 'Force removal of Image',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'edit' => array (
		'tab' => 'Edit Image',
		'label' => 'Edit Image %s',
		'msg' => 'Edited Image %s',
		'form_comment' => 'Comment',
		'form_install_from_nfs' => 'Install from NAS/NFS',
		'form_transfer_to_nfs' => 'Transfer to NAS/NFS',
		'form_install_from_local' => 'Install from local disk',
		'form_transfer_to_local' => 'Transfer to local disk',
		'form_install_from_template' => 'Automatic Installation',
		'form_image_password' => 'Password',
		'form_image_password_repeat' => 'Password (repeat)',
		'form_image_version' => 'Version',
		'lang_password_generate' => 'generate password',
		'lang_password_show' => 'show password',
		'lang_password_hide' => 'hide password',
		'please_wait' => 'Loading. Please wait ..',
		'error_password' => 'Password (repeat) does not match Password',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'install1' => array (
		'tab' => 'Install Image (1/2)',
		'label' => 'Select Install Server for image %s on storage %s',
		'title' => 'Select Install Server',
		'form_install_server' => 'Select Install Server',
		'msg' => 'Selected Install Server for Image %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'install2' => array (
		'tab' => 'Install Image (2/2)',
		'label' => 'Select Install Template for image %s on storage %s',
		'title' => 'Select Install Template',
		'msg' => 'Selected Install Template %s for Image %s',
		'form_install_template' => 'Selected Install Template',
		'form_install_persistent' => 'Persistent Installation',
		'form_install_parameter' => 'Installation Parameter',
		'please_wait' => 'Loading. Please wait ..',
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
		$this->tpldir   = $this->rootdir.'/server/image/tpl';
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/server/image/lang", 'image.ini');
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
			$this->action = "select";
		}

		// handle table params
		$vars = $this->response->html->request()->get('image');
		if($vars !== '') {
			if(!isset($vars['action'])) {
				foreach($vars as $k => $v) {
					$this->response->add('image['.$k.']', $v);
				}
			} else {
				foreach($vars as $k => $v) {
					unset($this->response->params['image['.$k.']']);
				}
			}
		}


		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'add':
				$content[] = $this->select(false);
				$content[] = $this->add(true);
			break;
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->remove(true);
			break;
			case 'edit':
				$content[] = $this->select(false);
				$content[] = $this->edit(true);
			break;
			case 'install1':
				$content[] = $this->select(false);
				$content[] = $this->install1(true);
			break;
			case 'install2':
				$content[] = $this->select(false);
				$content[] = $this->install2(true);
			break;
			case 'load':
				$content[]     = $this->select(false);
				$tmp           = $this->add(false);
				$tmp['value']  = $this->__loader();
				$tmp['active'] = true;
				$content[]     = $tmp;
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
		require_once($this->rootdir.'/server/image/class/image.api.class.php');
		$controller = new image_api($this);
		$controller->action();
	}

	//--------------------------------------------
	/**
	 * Select image
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/image/class/image.select.class.php');
			$controller = new image_select($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
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
	 * Add image
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/image/class/image.add.class.php');
			$controller                  = new image_add($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['add'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['add']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add' );
		$content['onclick'] = false;
		if($this->action === 'add' || $this->action === $this->lang['select']['action_add']){
			$content['active']  = true;
		}
		return $content;
	}	

	//--------------------------------------------
	/**
	 * Remove image
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/image/class/image.remove.class.php');
			$controller                  = new image_remove($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['remove'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['remove']['tab'];
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove' );
		$content['onclick'] = false;
		if($this->action === 'remove' || $this->action === $this->lang['select']['action_remove']){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Edit image
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/image/class/image.edit.class.php');
			$controller                  = new image_edit($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['edit'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['edit']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'edit' );
		$content['onclick'] = false;
		if($this->action === 'edit' || $this->action === $this->lang['select']['action_edit']){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Install image from template step1
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function install1( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/image/class/image.install1.class.php');
			$controller                  = new image_install1($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['install1'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['install1']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'install1' );
		$content['onclick'] = false;
		if($this->action === 'install1' || $this->action === $this->lang['select']['action_install1']){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Install image from template step2
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function install2( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/image/class/image.install2.class.php');
			$controller                  = new image_install2($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['install2'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['install2']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'install2' );
		$content['onclick'] = false;
		if($this->action === 'install2' || $this->action === $this->lang['select']['action_install2']){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Load Plugin as new tab
	 *
	 * @access public
	 * @return object
	 */
	//--------------------------------------------
	function __loader() {

		$plugin = $this->response->html->request()->get('iplugin');
		$name   = $plugin;
		$class  = $plugin;
		if($this->response->html->request()->get('icontroller') !== '') {
			$class = $this->response->html->request()->get('icontroller');
			$name  = $class;
		}
		$class  = str_replace('-', '_', $class).'_controller';

		// handle new response object
		$response = $this->response->response();
		$response->id = 'iload';
		unset($response->params['image[sort]']);
		unset($response->params['image[order]']);
		unset($response->params['image[limit]']);
		unset($response->params['image[offset]']);
		unset($response->params['image_filter']);
		$response->add('iplugin', $plugin);
		$response->add('icontroller', $name);
		$response->add($this->actions_name, 'load');

		$path   = $this->openqrm->get('webdir').'/plugins/'.$plugin.'/class/'.$name.'.controller.class.php';
		$role = $this->openqrm->role($response);
		$data = $role->get_plugin($class, $path);
		$data->pluginroot = '/plugins/'.$plugin;
		return $data;
	}



}
?>

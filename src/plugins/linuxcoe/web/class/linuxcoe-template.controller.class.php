<?php
/**
 * LinuxCOE-Templates Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
*/


class linuxcoe_template_controller
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
		'tab' => 'LinuxCOE Templates',
		'label' => 'LinuxCOE Automatic Installation Templates',
		'action_edit' => 'edit',
		'action_remove' => 'remove',
		'table_name' => 'Name',
		'table_id' => 'ID',
		'table_comment' => 'Description',
		'please_wait' => 'Loading LinuxCOE Templates. Please wait ..',
	),
	'edit' => array (
		'tab' => 'Edit LinuxCOE Template',
		'label' => 'Edit LinuxCOE Template %s description',
		'form_comment' => 'Description',
		'form_post' => 'Custom POST-Install section',
		'msg_edit' => 'Updated LinuxCOE Template %s',
		'error_comment' => 'Comment must be %s',
		'please_wait' => 'Loading LinuxCOE Templates. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'tab' => 'Remove LinuxCOE Templates',
		'label' => 'Remove LinuxCOE Template(s)',
		'msg_removed' => 'Removed Volume %s',
		'please_wait' => 'Removing Volume(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
);

var $openqrm_base_dir;
var $openqrm;
var $openqrm_ip;
var $event;

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
		$this->basedir  = $this->openqrm->get('basedir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/linuxcoe/lang", 'linuxcoe-template.ini');
		$this->tpldir   = $this->rootdir.'/plugins/linuxcoe/tpl';
		require_once $this->rootdir."/class/folder.class.php";
		$this->folder	= new folder();
		$this->openqrm_server = new openqrm_server();
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
		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->remove(true);
			break;
			case 'edit':
				$content[] = $this->select(false);
				$content[] = $this->edit(true);
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
	 * Select template
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->reload()) {
				require_once($this->rootdir.'/plugins/linuxcoe/class/linuxcoe-template.select.class.php');
				$controller = new linuxcoe_template_select($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->identifier_name = $this->identifier_name;
				$controller->folder          = $this->folder;
				$controller->webdir          = $this->rootdir;
				$controller->basedir         = $this->basedir;
				$controller->lang            = $this->lang['select'];
				$data = $controller->action();
			}
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
	 * Remove template
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/linuxcoe/class/linuxcoe-template.remove.class.php');
			$controller = new linuxcoe_template_remove($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->folder          = $this->folder;
			$controller->webdir          = $this->rootdir;
			$controller->basedir         = $this->basedir;
			$controller->openqrm_server  = $this->openqrm_server;
			$controller->lang            = $this->lang['remove'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['remove']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove' );
		$content['onclick'] = false;
		if($this->action === 'remove'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Edit template
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/linuxcoe/class/linuxcoe-template.edit.class.php');
			$controller = new linuxcoe_template_edit($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->folder          = $this->folder;
			$controller->webdir          = $this->rootdir;
			$controller->basedir         = $this->basedir;
			$controller->openqrm_server  = $this->openqrm_server;
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
	 * Reload Profiles
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload() {
		$command  = $this->basedir.'/plugins/linuxcoe/bin/openqrm-linuxcoe-manager check';
		$command .= ' --openqrm-ui-user '.$this->user->name;
		$command .= ' --openqrm-cmd-mode background';
		$file = $this->basedir.'/plugins/linuxcoe/web/stat/check';
		if($this->file->exists($file)) {
			$this->file->remove($file);
		}
		$this->openqrm_server->send_command($command, NULL, true);
		while (!$this->file->exists($file))
		{
		  usleep(10000);
		  clearstatcache();
		}
		return true;
	}
	

}

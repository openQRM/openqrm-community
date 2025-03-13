<?php
/**
 * Kernel Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kernel_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kernel_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "kernel_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kernel_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kernel_identifier';
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
		'tab' => 'Kernels',
		'label' => 'Kernels',
		'action_remove' => 'remove',
		'action_edit' => 'edit',
		'action_add' => 'Add a new kernel',
		'action_setdefault' => 'Set kernel %s as default',
		'table_name' => 'Name',
		'table_id' => 'ID',
		'table_version' => 'Version',
		'table_comment' => 'Comment',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_kernel' => 'Kernel',
		'please_wait' => 'Loading. Please wait ..',
	),
	'add' => array (
		'label' => 'Add kernel',
		'title' => 'Adding a new kernel to openQRM',
		'kernel_add' => '<b>New kernels should be added on the openqrm server with the following command:</b><br>
			<br>
			<br>/usr/share/openqrm/bin/openqrm kernel add -n name -v version -u username -p password [-l location] [-i initramfs/ext2] [-t path-to-initrd-template-file]<br>
			<br>
			<b>name</b> can be any identifier as long as it has no spaces or other special characters; it is used as part of the filename.<br>
			<b>version</b> should be the version for the kernel you want to install. If the filenames are called vmlinuz-2.6.26-2-amd64 then 2.6.26-2-amd64 is the version of this kernel.<br>
			<b>username</b> and <b>password</b> are the credentials to openqrm itself.<br>
			<b>location</b> is the root directory for the kernel you want to install. The files that are used are \${location}/boot/vmlinuz-\${version}, \${location}/boot/initrd.img-\${version} and \${location}/lib/modules/\${version}/*<br>
			<b>initramfs/ext2</b> should specify the type of initrd image you want to generate. Most people want to use <b>initramfs</b> here.<br>
			<b>path-to-initrd-template-file</b> should point to an openqrm initrd template. These can be found in the openqrm base dir under etc/templates.<br>
			<br>
			Example:<br>
			/usr/share/openqrm/bin/openqrm kernel add -n openqrm-kernel-1 -v 2.6.29 -u openqrm -p openqrm -i initramfs -l / -t /usr/share/openqrm/etc/templates/openqrm-initrd-template.debian.x86_64.tgz<br>
			<br>',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'label' => 'Remove kernel(s)',
		'msg' => 'Removed kernel %s',
		'msg_not_removing_default' => 'Not removing default kernel!',
		'msg_not_removing_active' => 'Not removing kernel %s!<br>It is still in use by Server %s !',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'setdefault' => array (
		'label' => 'Set default kernel',
		'msg' => 'Set default kernel to %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'edit' => array (
		'label' => 'Edit kernels',
		'comment' => 'Comment',
		'msg' => 'Edited kernel %s',
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
		$this->tpldir   = $this->rootdir.'/server/kernel/tpl';
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/server/kernel/lang", 'kernel.ini');
//		$response->html->debug();

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
		$vars = $this->response->html->request()->get('kernel');
		if($vars !== '') {
			if(!isset($vars['action'])) {
				foreach($vars as $k => $v) {
					$this->response->add('kernel['.$k.']', $v);
				}
			} else {
				foreach($vars as $k => $v) {
					unset($this->response->params['kernel['.$k.']']);
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
			case 'edit':
				$content[] = $this->select(false);
				$content[] = $this->edit(true);
			break;
			case $this->lang['select']['action_remove']:
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->remove(true);
			break;
			case 'setdefault':
				$content[] = $this->select(false);
				$content[] = $this->setdefault(true);
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
		require_once($this->rootdir.'/server/kernel/class/kernel.api.class.php');
		$controller = new kernel_api($this);
		$controller->action();
	}

	
	//--------------------------------------------
	/**
	 * Select kernel
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/kernel/class/kernel.select.class.php');
			$controller = new kernel_select($this->openqrm, $this->response);
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
	 * Add kernel
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/kernel/class/kernel.add.class.php');
			$controller                  = new kernel_add($this->openqrm, $this->response);
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
	 * Remove kernel
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/kernel/class/kernel.remove.class.php');
			$controller                  = new kernel_remove($this->openqrm, $this->response);
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
		if($this->action === 'remove' || $this->action === $this->lang['select']['action_remove']){
			$content['active']  = true;
		}
		return $content;
	}




	//--------------------------------------------
	/**
	 * Edit kernel
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/kernel/class/kernel.edit.class.php');
			$controller                  = new kernel_edit($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['edit'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['edit']['label'];
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
	 * Set default kernel
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function setdefault( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/kernel/class/kernel.setdefault.class.php');
			$controller                  = new kernel_setdefault($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['setdefault'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'setdefault';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'setdefault' );
		$content['onclick'] = false;
		if($this->action === 'setdefault' || $this->action === $this->lang['select']['action_setdefault']){
			$content['active']  = true;
		}
		return $content;
	}


}

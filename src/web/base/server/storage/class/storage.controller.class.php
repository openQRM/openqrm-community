<?php
/**
 * Storage Controller
 *
 * openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.
 *
 * All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.
 *
 * This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
 * The latest version of this license can be found here: src/doc/LICENSE.txt
 *
 * By using this software, you acknowledge having read this license and agree to be bound thereby.
 *
 * http://openqrm-enterprise.com
 *
 * Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class storage_controller
{
	/**
	 * name of action buttons
	 * @access public
	 * @var string
	 */
	var $actions_name = 'storage_action';
	/**
	 * message param
	 * @access public
	 * @var string
	 */
	var $message_param = "storage_msg";
	/**
	 * id for tabs
	 * @access public
	 * @var string
	 */
	var $prefix_tab = 'storage_tab';
	/**
	 * identifier name
	 * @access public
	 * @var string
	 */
	var $identifier_name = 'storage_identifier';
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
		'select' => array(
			'tab' => 'Storage',
			'label' => 'Storage',
			'action_remove' => 'remove',
			'action_mgmt' => 'manage',
			'action_edit' => 'edit',
			'action_add' => 'Add new storage',
			'table_state' => 'State',
			'table_id' => 'Id',
			'table_name' => 'Name',
			'table_type' => 'Type',
			'table_resource' => 'Resource',
			'table_deployment' => 'Deployment',
			'lang_filter' => 'Filter by Storage type',
			'please_wait' => 'Loading. Please wait ..',
		),
		'add' => array(
			'label' => 'Add Storage',
			'msg' => 'Added Storage %s',
			'form_name' => 'Name',
			'error_name' => 'Storage name must be %s',
			'form_capabilities' => 'Capabilities',
			'lang_name_generate' => 'generate name',
			'error_capabilities' => 'Capabilities name must be %s',
			'form_deployment' => 'Deployment Type',
			'form_resource' => 'Resource',
			'form_comment' => 'Comment',
			'error_comment' => 'Comment must be %s',
			'error_exists' => 'Storage name must be unique!',
			'please_wait' => 'Loading. Please wait ..',
			'canceled' => 'Operation canceled. Please wait ..',
		),
		'remove' => array(
			'label' => 'Remove Storage',
			'msg' => 'Removed storage %s',
			'msg_not_removing_active' => 'Not removing Storage %s!<br>Image %s are still located on it!',
			'please_wait' => 'Loading. Please wait ..',
			'canceled' => 'Operation canceled. Please wait ..',
		),
		'edit' => array(
			'tab' => 'Edit Storage',
			'label' => 'Edit Storage %s',
			'msg' => 'Edited storage %s',
			'comment' => 'Comment',
			'form_comment' => 'Comment',
			'error_comment' => 'Comment must be %s',
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
	function __construct($openqrm, $response)
	{
		$this->openqrm = $openqrm;
		$this->user = $this->openqrm->user();
		$this->rootdir = $this->openqrm->get('webdir');
		$this->tpldir = $this->rootdir . '/server/storage/tpl';
		$this->response = $response;
		$this->file = $this->openqrm->file();
		$this->lang = $this->user->translate($this->lang, $this->rootdir . "/server/storage/lang", 'storage.ini');
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
	function action($action = null)
	{
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if ($ar !== '') {
			if (is_array($ar)) {
				$this->action = key($ar);
			} else {
				$this->action = $ar;
			}
		} else if (isset($action)) {
			$this->action = $action;
		}
		if ($this->response->cancel()) {
			$this->action = "select";
		}
		$this->response->params['storage_filter'] = $this->response->html->request()->get('storage_filter');
		
		// handle table params
		$vars = $this->response->html->request()->get('storage');
		if ($vars !== '') {
			if (!isset($vars['action'])) {
				foreach ($vars as $k => $v) {
					$this->response->add('storage[' . $k . ']', $v);
				}
			} else {
				foreach ($vars as $k => $v) {
					unset($this->response->params['storage[' . $k . ']']);
				}
			}
		}
		
		$content = array();
		switch ($this->action) {
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
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->remove(true);
				break;
			case 'load':
				$tmp = $this->select(false);
				$tmp['value'] = $this->__loader();
				$tmp['active'] = true;
				$content[] = $tmp;
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
	function api()
	{
		require_once($this->rootdir . '/server/storage/class/storage.api.class.php');
		$controller = new storage_api($this);
		$controller->action();
	}
	
	
	//--------------------------------------------
	/**
	 * Select storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select($hidden = true)
	{
		$data = '';
		if ($hidden === true) {
			require_once($this->rootdir . '/server/storage/class/storage.select.class.php');
			$controller = new storage_select($this->openqrm, $this->response);
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang = $this->lang['select'];
			$data = $controller->action();
		}
		$content['label'] = $this->lang['select']['tab'];
		$content['value'] = $data;
		$content['target'] = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select');
		$content['onclick'] = false;
		if ($this->action === 'select') {
			$content['active'] = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add($hidden = true)
	{
		$data = '';
		if ($hidden === true) {
			require_once($this->rootdir . '/server/storage/class/storage.add.class.php');
			$controller = new storage_add($this->openqrm, $this->response);
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang = $this->lang['add'];
			$controller->rootdir = $this->rootdir;
			$controller->prefix_tab = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label'] = $this->lang['add']['label'];
		$content['value'] = $data;
		$content['target'] = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add');
		$content['onclick'] = false;
		if ($this->action === 'add' || $this->action === $this->lang['select']['action_add']) {
			$content['active'] = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Edit storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit($hidden = true)
	{
		$data = '';
		if ($hidden === true) {
			require_once($this->rootdir . '/server/storage/class/storage.edit.class.php');
			$controller = new storage_edit($this->openqrm, $this->response);
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang = $this->lang['edit'];
			$controller->rootdir = $this->rootdir;
			$controller->prefix_tab = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label'] = $this->lang['edit']['tab'];
		$content['value'] = $data;
		$content['target'] = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'edit');
		$content['onclick'] = false;
		if ($this->action === 'edit' || $this->action === $this->lang['select']['action_edit']) {
			$content['active'] = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Remove storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove($hidden = true)
	{
		$data = '';
		if ($hidden === true) {
			require_once($this->rootdir . '/server/storage/class/storage.remove.class.php');
			$controller = new storage_remove($this->openqrm, $this->response);
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang = $this->lang['remove'];
			$controller->rootdir = $this->rootdir;
			$controller->prefix_tab = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label'] = 'Remove';
		$content['hidden'] = true;
		$content['value'] = $data;
		$content['target'] = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove');
		$content['onclick'] = false;
		if ($this->action === 'remove' || $this->action === $this->lang['select']['action_remove']) {
			$content['active'] = true;
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
	function __loader()
	{
		
		$plugin = $this->response->html->request()->get('splugin');
		$name = $plugin;
		$class = $plugin;
		if ($this->response->html->request()->get('scontroller') !== '') {
			$class = $this->response->html->request()->get('scontroller');
			$name = $class;
		}
		$class = str_replace('-', '_', $class) . '_controller';
		
		// handle new response object
		$response = $this->response->response();
		$response->id = 'sload';
		#unset($response->params['storage[sort]']);
		#unset($response->params['storage[order]']);
		#unset($response->params['storage[limit]']);
		#unset($response->params['storage[offset]']);
		#unset($response->params['storage_filter']);
		$response->add('splugin', $plugin);
		$response->add('scontroller', $name);
		$response->add($this->actions_name, 'load');
		
		$path = $this->openqrm->get('webdir') . '/plugins/' . $plugin . '/class/' . $name . '.controller.class.php';
		$role = $this->openqrm->role($response);
		$data = $role->get_plugin($class, $path);
		$data->pluginroot = '/plugins/' . $plugin;
		return $data;
	}
	
}

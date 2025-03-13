<?php
/**
 * Resource Controller
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

class resource_controller
{
	/**
	 * name of action buttons
	 * @access public
	 * @var string
	 */
	var $actions_name = 'resource_action';
	/**
	 * message param
	 * @access public
	 * @var string
	 */
	var $message_param = "resource_msg";
	/**
	 * id for tabs
	 * @access public
	 * @var string
	 */
	var $prefix_tab = 'resource_tab';
	/**
	 * identifier name
	 * @access public
	 * @var string
	 */
	var $identifier_name = 'resource_identifier';
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
			'tab' => 'Resources',
			'label' => 'Resources',
			'action_remove' => 'remove',
			'action_reboot' => 'reboot',
			'action_poweroff' => 'poweroff',
			'action_mgmt' => 'manage',
			'action_edit' => 'edit',
			'action_add' => 'Add a new resource',
			'action_new' => 'new',
			'table_state' => 'State',
			'table_id' => 'Id',
			'table_name' => 'Name',
			'table_type' => 'Type',
			'table_mac' => 'Mac',
			'table_ip' => 'IP',
			'table_cpu' => 'CPU',
			'table_nics' => 'NIC',
			'table_memory' => 'RAM',
			'table_load' => 'Load',
			'table_resource' => 'Resource',
			'table_deployment' => 'Deployment',
			'table_uptime' => 'Uptime',
			'lang_type_filter' => 'Filter by Resource Type',
			'lang_filter' => 'Filter by Resource',
			'lang_filter_title' => 'Filter by Resource ID, Name or Mac. Use ? as single and * as multi wildcard.',
			'please_wait' => 'Loading. Please wait ..',
		),
		'add' => array(
			'label' => 'Add resource',
			'title' => 'Add a new resource as ',
			'vm_type' => 'a Virtual Machine from type',
			'local' => 'or by integrating an existing, local-installed server',
			'unmanaged' => 'Manual add an un-managed system',
			'manual_new_resource' => 'or manual add an un-managed system',
			'start_local_server' => 'Please enable and start the "local-server" plugin!',
			'integrate_local_server' => 'Integrate an existing local installed Server',
			'start_vm_plugin' => 'Please enable and start one of the virtualization plugins!',
			'create_vm' => 'Create a %s Virtual Machine',
			'vm' => 'Virtual Machine',
			'msg' => 'Added resource %s',
			'please_wait' => 'Loading. Please wait ..',
			'canceled' => 'Operation canceled. Please wait ..',
		),
		'remove' => array(
			'label' => 'Remove resources(s)',
			'msg' => 'Removed resource %s',
			'msg_not_removing' => 'Not removing resource %s!<br>It is still in use by Server %s !',
			'msg_not_removing_vm' => 'Not removing VM resource %s!<br> Please remove it through the %s Manager!',
			'msg_still_active' => 'Not removing resource %s!<br>It is still active or in transition.',
			'msg_force_remove' => 'Force removal of resources',
			'please_wait' => 'Loading. Please wait ..',
			'canceled' => 'Operation canceled. Please wait ..',
		),
		'reboot' => array(
			'label' => 'Reboot resources(s)',
			'msg' => 'Rebooted resource %s',
			'please_wait' => 'Loading. Please wait ..',
			'canceled' => 'Operation canceled. Please wait ..',
		),
		'poweroff' => array(
			'label' => 'Power-off resources(s)',
			'msg' => 'Powered-off resource %s',
			'please_wait' => 'Loading. Please wait ..',
			'canceled' => 'Operation canceled. Please wait ..',
		),
		'new' => array(
			'label' => 'New resource',
			'form_docu' => 'This form is to manually create a new resource which openQRM cannot directly monitor<br>
			via the openQRM-client e.g. NetApp Filers or EqualLogig Storages.<br>
			The resource then can still be used as Storage Server managed by openQRM.',
			'form_auto_resource' => 'Resources (physical systems and virtual machines) intended for rapid deployment<br>
			 are automatically added to openQRM by setting their bios to PXE/Net-Boot.<br>
			 Just have the "dhcpd" and "tftpd" plugin enabled and started.',
			'form_add_resource' => 'Add new Resource (not monitored)',
			'form_name' => 'Hostname',
			'form_ip' => 'IP-Adress',
			'form_mac' => 'MAC-Adress',
			'error_name' => 'Hostname must be %s',
			'error_ip' => 'IP-Adress must be %s',
			'error_mac' => 'MAC-Adress must be %s',
			'msg_mac_in_use' => 'MAC-Adress already in use! Not adding resource',
			'msg_ip_in_use' => 'IP Adress already in use! Not adding resource',
			'msg' => 'Added resource %s',
			'msg_add_failed' => 'Failed adding resource',
			'please_wait' => 'Loading. Please wait ..',
			'canceled' => 'Operation canceled. Please wait ..',
		),
		'edit' => array(
			'label' => 'Edit resource %s',
			'form_docu' => 'This form allows to manually set the IP address for a local booting VM<br>
			if this is not automatically provided e.g. by the "dhcpd plugin".',
			'form_edit_resource' => 'Edit Resource IP address of local booting VM',
			'form_ip' => 'IP-Adress',
			'error_ip' => 'IP-Adress must be %s',
			'msg_ip_in_use' => 'IP Adress already in use! Not adding resource',
			'msg' => 'Edited resource %s',
			'msg_add_failed' => 'Failed editing resource',
			'please_wait' => 'Loading. Please wait ..',
			'canceled' => 'Operation canceled. Please wait ..',
		)
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
		$this->tpldir = $this->rootdir . '/server/resource/tpl';
		$this->response = $response;
		$this->file = $this->openqrm->file();
		$this->lang = $this->user->translate($this->lang, $this->rootdir . "/server/resource/lang", 'resource.ini');
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
		$this->response->params['resource_filter'] = $this->response->html->request()->get('resource_filter');
		
		// handle table params
		$vars = $this->response->html->request()->get('resource');
		if ($vars !== '') {
			if (!isset($vars['action'])) {
				foreach ($vars as $k => $v) {
					$this->response->add('resource[' . $k . ']', $v);
				}
			} else {
				foreach ($vars as $k => $v) {
					unset($this->response->params['resource[' . $k . ']']);
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
			case $this->lang['select']['action_new']:
			case 'new':
				$content[] = $this->select(false);
				$content[] = $this->resource_new(true);
				break;
			case 'edit':
				$content[] = $this->select(false);
				$content[] = $this->edit(true);
				break;
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->remove(true);
				break;
			case 'reboot':
				$content[] = $this->select(false);
				$content[] = $this->reboot(true);
				break;
			case 'poweroff':
				$content[] = $this->select(false);
				$content[] = $this->poweroff(true);
				break;
			case 'load':
				$content[] = $this->select(false);
				$tmp = $this->add(false);
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
		require_once($this->rootdir . '/server/resource/class/resource.api.class.php');
		$controller = new resource_api($this);
		$controller->action();
	}
	
	
	//--------------------------------------------
	/**
	 * Select resource
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
			require_once($this->rootdir . '/server/resource/class/resource.select.class.php');
			$controller = new resource_select($this->openqrm, $this->response);
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
	 * Add resource
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
			require_once($this->rootdir . '/server/resource/class/resource.add.class.php');
			$controller = new resource_add($this->openqrm, $this->response);
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
	 * New resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function resource_new($hidden = true)
	{
		$data = '';
		if ($hidden === true) {
			require_once($this->rootdir . '/server/resource/class/resource.new.class.php');
			$controller = new resource_new($this->openqrm, $this->response);
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang = $this->lang['new'];
			$controller->rootdir = $this->rootdir;
			$controller->prefix_tab = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label'] = $this->lang['new']['label'];
		$content['value'] = $data;
		$content['target'] = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'new');
		$content['onclick'] = false;
		if ($this->action === 'new' || $this->action === $this->lang['select']['action_new']) {
			$content['active'] = true;
		}
		return $content;
	}
	
	
	//--------------------------------------------
	/**
	 * Remove resource
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
			require_once($this->rootdir . '/server/resource/class/resource.remove.class.php');
			$controller = new resource_remove($this->openqrm, $this->response);
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
	 * Reboot resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function reboot($hidden = true)
	{
		$data = '';
		if ($hidden === true) {
			require_once($this->rootdir . '/server/resource/class/resource.reboot.class.php');
			$controller = new resource_reboot($this->openqrm, $this->response);
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang = $this->lang['reboot'];
			$controller->rootdir = $this->rootdir;
			$controller->prefix_tab = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label'] = 'Reboot';
		$content['hidden'] = true;
		$content['value'] = $data;
		$content['target'] = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'reboot');
		$content['onclick'] = false;
		if ($this->action === 'reboot' || $this->action === $this->lang['select']['action_reboot']) {
			$content['active'] = true;
		}
		return $content;
	}
	
	
	//--------------------------------------------
	/**
	 * Poweroff resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function poweroff($hidden = true)
	{
		$data = '';
		if ($hidden === true) {
			require_once($this->rootdir . '/server/resource/class/resource.poweroff.class.php');
			$controller = new resource_poweroff($this->openqrm, $this->response);
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang = $this->lang['poweroff'];
			$controller->rootdir = $this->rootdir;
			$controller->prefix_tab = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label'] = 'Poweroff';
		$content['hidden'] = true;
		$content['value'] = $data;
		$content['target'] = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'poweroff');
		$content['onclick'] = false;
		if ($this->action === 'poweroff' || $this->action === $this->lang['select']['action_poweroff']) {
			$content['active'] = true;
		}
		return $content;
	}
	
	
	
	//--------------------------------------------
	/**
	 * Edit resource
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
			require_once($this->rootdir . '/server/resource/class/resource.edit.class.php');
			$controller = new resource_edit($this->openqrm, $this->response);
			$controller->actions_name = $this->actions_name;
			$controller->tpldir = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang = $this->lang['edit'];
			$controller->rootdir = $this->rootdir;
			$controller->prefix_tab = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label'] = $this->lang['edit']['label'];
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
	 * Load Plugin as new tab
	 *
	 * @access public
	 * @return object
	 */
	//--------------------------------------------
	function __loader()
	{
		
		$plugin = $this->response->html->request()->get('rplugin');
		$name = $plugin;
		$class = $plugin;
		if ($this->response->html->request()->get('rcontroller') !== '') {
			$class = $this->response->html->request()->get('rcontroller');
			$name = $class;
		}
		$class = str_replace('-', '_', $class) . '_controller';
		
		// handle new response object
		$response = $this->response->response();
		$response->id = 'rload';
		unset($response->params['resource[sort]']);
		unset($response->params['resource[order]']);
		unset($response->params['resource[limit]']);
		unset($response->params['resource[offset]']);
		unset($response->params['resource_filter']);
		$response->add('rplugin', $plugin);
		$response->add('rcontroller', $name);
		$response->add($this->actions_name, 'load');
		
		$path = $this->openqrm->get('webdir') . '/plugins/' . $plugin . '/class/' . $name . '.controller.class.php';
		$role = $this->openqrm->role($response);
		$data = $role->get_plugin($class, $path);
		$data->pluginroot = '/plugins/' . $plugin;
		return $data;
	}
	
}

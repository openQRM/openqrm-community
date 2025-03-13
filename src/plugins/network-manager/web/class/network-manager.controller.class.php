<?php
/**
 * Network Manager Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */




class network_manager_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'network_manager_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "network_manager_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'network_manager_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'network_manager_identifier';
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
		'tab' => 'Network Manager',
		'label' => 'Network Devices on Server %s',
		'action_remove' => 'remove',
		'action_add' => 'Add new Network Bridge',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
	),
	'add' => array (
		'tab' => 'Add Network Bridge',
		'label' => 'Add Network Bridge on Server %s',
		'legend_bridge' => 'Bridge',
		'legend_ip' => 'Ip',
		'legend_vlan' => 'Vlan',
		'legend_dnsmasq' => 'Dnsmasq',
		'form_name' => 'Name',
		'form_device' => 'Device',
		'form_ip' => 'IP',
		'form_subnet' => 'Subnet',
		'form_vlan' => 'Vlan',
		'form_gateway' => 'Gateway',
		'form_bridge_fd' => 'FD',
		'form_bridge_hello' => 'Hello',
		'form_bridge_maxage' => 'Maxage',
		'form_bridge_stp' => 'Stp',
		'form_bridge_mac' => 'Mac',
		'form_first_ip' => 'First Ip',
		'form_last_ip' => 'Last Ip',
		'title_name' => 'Name',
		'title_vlan' => 'Vlan',
		'title_device' => 'Network Device',
		'title_ip' => 'Ip',
		'title_subnet' => 'Subnet',
		'title_gateway' => 'Gateway',
		'title_bridge_fd' => 'FD',
		'title_bridge_hello' => 'Hello',
		'title_bridge_maxage' => 'Maxage',
		'title_bridge_stp' => 'Stp',
		'title_bridge_mac' => 'Mac',
		'title_first_ip' => 'First Ip',
		'title_last_ip' => 'Last Ip',
		'error_name' => 'Name must be %s only',
		'error_exists' => 'Name %s is already in use.',
		'error_ip' => 'IP is invalid',
		'error_subnet' => 'Subnet is invalid',
		'error_vlan' => 'Vlan must be %s only',
		'error_empty' => 'Must not be empty',
		'msg_added' => 'Successfully added bridge %s.',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
	), 
	'remove' => array (
		'label' => 'Remove Network Bridge(s) on Server %s',
		'msg_removed' => 'Successfully removed bridge %s.',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/network-manager/lang", 'network-manager.ini');
		$this->tpldir   = $this->rootdir.'/plugins/network-manager/tpl';
		$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
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

		if($this->action === '') {
			$this->action = "select";
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
		require_once($this->openqrm->get('basedir').'/plugins/network-manager/web/class/network-manager.api.class.php');
		$controller = new network_manager_api($this);
		$controller->action();
	}

	
	//--------------------------------------------
	/**
	 * Select Network Devices
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->openqrm->get('basedir').'/plugins/network-manager/web/class/network-manager.select.class.php');
			$controller = new network_manager_select($this->openqrm, $this->response, $this);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
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
	 * Add new Bridge
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->openqrm->get('basedir').'/plugins/network-manager/web/class/network-manager.add.class.php');
			$controller                  = new network_manager_add($this->openqrm, $this->response, $this);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['add'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
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
	 * Remove Bridge
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->openqrm->get('basedir').'/plugins/network-manager/web/class/network-manager.remove.class.php');
			$controller                  = new network_manager_remove($this->openqrm, $this->response);
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
		if($this->action === 'remove'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Reload statfile
	 *
	 * @access protected
	 * @return null
	 */
	//--------------------------------------------
	function __reload( $statfile, $resource ) {
		if(isset($resource) && isset($resource->state) && $resource->state === 'active') {
			sleep(2);
			$command = $this->openqrm->get('basedir').'/plugins/network-manager/bin/openqrm-network-manager post_config';
			$command .= ' -u '.$this->openqrm->admin()->name;
			$command .= ' -p '.$this->openqrm->admin()->password;
			$command .= ' --openqrm-ui-user '.$this->user->name;
			$command .= ' --openqrm-cmd-mode regular';
			if($this->file->exists($statfile)) {
				$this->file->remove($statfile);
			}
			$resource->send_command($resource->ip, $command);
			while (!$this->file->exists($statfile)) // check if the data file has been modified
			{
				usleep(10000); // sleep 10ms to unload the CPU
				clearstatcache();
			}
		}
	}


}

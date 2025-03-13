<?php
/**
 * Datacenter Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class datacenter_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'datacenter_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "datacenter_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'datacenter_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'datacenter_identifier';
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
	'dashboard' => array (
		'tab' => 'Datacenters',
		'label' => 'Datacenters',
		'title' => 'openQRM Datacenter Dashboard',
		'load_headline' => 'Datacenter Load',
		'load_current' => 'current',
		'load_last_hour' => 'last hour',
		'inventory_headline' => 'Inventory',
		'inventory_servers' => 'Server by type',
		'inventory_storages' => 'Storage Pool',
		'events_headline' => 'Events',
		'events_date' => 'Date',
		'events_source' => 'Source',
		'events_description' => 'Description',
		'datacenter_load_overall' => 'Datacenter <small>(overall)</small>',
		'appliance_load_overall' => 'Server',
		'storage_load_overall' => 'Storage',
		'link_server_management' => 'Server Management',
		'link_storage_management' => 'Storage Management',
		'no_data_available' => 'No Data available',
		'please_wait' => 'Loading. Please wait ..',
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
		$this->tpldir   = $this->rootdir.'/server/aa_server/tpl';
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/server/aa_server/lang", 'datacenter.ini');
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
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = "dashboard";
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'dashboard':
				$content[] = $this->dashboard(true);
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
		require_once($this->rootdir.'/server/aa_server/class/datacenter.api.class.php');
		$controller = new datacenter_api($this);
		$controller->action();
	}

	
	//--------------------------------------------
	/**
	 * Datacenter Dashboard
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function dashboard( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/aa_server/class/datacenter.dashboard.class.php');
			$controller = new datacenter_dashboard($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['dashboard'];
			$data = $controller->action();
		}
		$content['label'] = $this->lang['dashboard']['tab'];
		$content['value'] = $data;
		$content['target'] = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'dashboard' );
		$content['onclick'] = false;
		if($this->action === 'dashboard'){
			$content['active']  = true;
		}
		return $content;
	}

}

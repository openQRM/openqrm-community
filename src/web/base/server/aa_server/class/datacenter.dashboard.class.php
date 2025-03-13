<?php
/**
 * Datacenter Dashboard
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class datacenter_dashboard
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
var $lang = array();

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
		$this->response   = $response;
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$t = $this->response->html->template($this->tpldir.'/datacenter-dashboard.tpl.php');
		
		$t->add($this->lang['title'], 'title');
		$t->add($this->lang['load_headline'], 'load_headline');
		$t->add($this->lang['load_current'], 'load_current');
		$t->add($this->lang['load_last_hour'], 'load_last_hour');
		$t->add($this->lang['datacenter_load_overall'], 'datacenter_load_overall');
		$t->add($this->lang['appliance_load_overall'], 'appliance_load_overall');
		$t->add($this->lang['storage_load_overall'], 'storage_load_overall');
		$t->add($this->lang['inventory_headline'], 'inventory_headline');
		$t->add($this->lang['inventory_servers'], 'lang_inventory_servers');
		$t->add($this->lang['inventory_storages'], 'lang_inventory_storages');
		$t->add($this->lang['events_headline'], 'events_headline');
		$t->add($this->lang['events_date'], 'events_date');
		$t->add($this->lang['events_source'], 'events_source');
		$t->add($this->lang['events_description'], 'events_description');
		$t->add($this->lang['no_data_available'], 'no_data_available');

/*
		$t->add($this->lang['resource_overview'], 'resource_overview');
		$t->add($this->lang['resource_load_physical'], 'resource_load_physical');
		$t->add($this->lang['resource_load_vm'], 'resource_load_vm');
		$t->add($this->lang['resource_available_overall'], 'resource_available_overall');
		$t->add($this->lang['resource_available_physical'], 'resource_available_physical');
		$t->add($this->lang['resource_available_vm'], 'resource_available_vm');
		$t->add($this->lang['resource_error_overall'], 'resource_error_overall');
		$t->add($this->lang['appliance_overview'], 'appliance_overview');
		$t->add($this->lang['appliance_load_peak'], 'appliance_load_peak');
		$t->add($this->lang['appliance_error_overall'], 'appliance_error_overall');
		$t->add($this->lang['storage_overview'], 'storage_overview');
		$t->add($this->lang['storage_load_peak'], 'storage_load_peak');
		$t->add($this->lang['storage_error_overall'], 'storage_error_overall');
*/

		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		
		
		$link_server = $this->response->html->a();
		$link_server->label = $this->lang['link_server_management'];
		$link_server->title = 'Server Management';
		$link_server->href = 'index.php?base=appliance';
		$link_server->css = 'btn add';
		$t->add($link_server, 'link_server_management');
		
		$link_storage = $this->response->html->a();
		$link_storage->label = $this->lang['link_storage_management'];
		$link_storage->title = 'Server Management';
		$link_storage->href = 'index.php?base=storage';
		$link_storage->css = 'btn add';
		$t->add($link_storage, 'link_storage_management');

		
		// Get dashboard quicklink from hook files
		$quicklinks = $this->build_quicklinks();
		if(count($quicklinks) > 0) {
			$t->add('<h2>Quicklinks</h2>', 'quicklinks_headline');
			$t->add(implode('', $quicklinks), 'quicklinks');
		} else {
			
			// TODO: find nicer way to 'unset' view markers if not needed
			//		 perhaps htmlobjects can do the job
			$t->add('', 'quicklinks_headline');
			$t->add('', 'quicklinks');
		}
		
		return $t;
	}



	//--------------------------------------------
	/**
	 * build_quicklinks: Scan directories of started plugins for the 
	 * dashboard quicklink hook and call hook method
	 *
	 * @access private
	 * @return array
	 */
	//--------------------------------------------
	private function build_quicklinks() {
	
		$quicklinks = array();
		$plugin = new plugin();
		$started_plugins = $plugin->started();			// get list of running plugins
		
		foreach ($started_plugins as $plugin_name) {
			
			$hook_file = $this->openqrm->get('webdir')."/plugins/".$plugin_name."/openqrm-".$plugin_name."-dashboard-quicklink-hook.php";
			if (file_exists($hook_file)) {
				require_once $hook_file;
				
				$hook_function = 'get_'.$plugin_name.'_dashboard_quicklink';
				if(function_exists($hook_function)) {

					$link = $hook_function($this->response->html);
					if(is_object($link)) {
						$quicklinks[] = $link->get_string();
					}
				}
			}
		}
		return $quicklinks;
	}

}

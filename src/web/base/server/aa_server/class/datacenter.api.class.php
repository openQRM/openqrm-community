<?php
/**
 * Datacenter Api
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
*/

class datacenter_api
{
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $controller
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller = $controller;
		$this->user       = $this->controller->user;
		$this->html       = $this->controller->response->html;
		$this->response   = $this->html->response();
		$this->file       = $this->controller->file;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {
		$action = $this->response->html->request()->get($this->controller->actions_name);
		switch( $action ) {
			case 'dc_status':
				$this->dc_status();
			break;
			case 'eventlist':
				$this->eventlist();
			break;
			case 'statistics':
				$this->statistics();
			break;
			case 'storage_list':
				$this->storage_list();
			break;
			case 'server_list':
				$this->server_list();
			break;
		}
	}

	//--------------------------------------------
	/**
	 * DC Status
	 *
	 * @access public
	 */
	//--------------------------------------------
	function dc_status() {

		// number of idle systems
		$resources_all = 0;
		// active deployed resources
		$resources_active = 0;
		// resources in error state
		$resources_error = 0;
		// physical resources
		$resources_physical = 0;
		// virtual resources
		$resources_virtual = 0;
		// number of idle systems
		$resources_available = 0;
		// physical resource available
		$resources_available_physical = 0;
		// virtal resource available
		$resources_available_virtual = 0;
		// overall load
		$dc_load_overall = 0;
		// active appliance load
		$appliance_load_overall = 0;
		// peak in appliance load
		$appliance_load_peak = 0;
		// active appliances
		$appliance_active = 0;
		// active appliance with resource in error state
		$appliance_error = 0;
		// storage load
		$storage_load_overall = 0;
		// storage peak
		$storage_load_peak = 0;
		// active storages
		$storage_active = 0;
		// storage with resource in error state
		$storage_error = 0;

		// get an array of resources which are assigned to an appliance
		$appliance_resources_array = array();
		$appliance = new appliance();
		$appliance_list = $appliance->get_all_ids();
		foreach ($appliance_list as $app) {
			$app_id = $app['appliance_id'];
			$g_appliance = new appliance();
			$g_appliance->get_instance_by_id($app_id);
			$g_appliance_resource = $g_appliance->resources;
			if ((!strcmp($g_appliance->state, "active")) || ($g_appliance_resource == 0)) {
				if ($g_appliance_resource != "-1") {
					$appliance_resources_array[] .= $g_appliance_resource;
				}
			}
		}
		// get an array of resources which are a storage server
		$storage_resources_array = array();
		$storage = new storage();
		$storage_list = $storage->get_list();
		foreach ($storage_list as $store) {
			$storage_id = $store['value'];
			$g_storage = new storage();
			$g_storage->get_instance_by_id($storage_id);
			$g_storage_resource = $g_storage->resource_id;
			$storage_resources_array[] .= $g_storage_resource;

		}

		$restype = 0;
		$resource = new resource();
		$resource_list = $resource->get_list();
		foreach ($resource_list as $res) {
			$res_id = $res['resource_id'];
			//echo "!! res_id $res_id <br>";
			$g_resource = new resource();
			$g_resource->get_instance_by_id($res_id);
			// start gathering
			$resources_all++;
			// physical or virtual ?
			if ((strlen($g_resource->vtype)) && ($g_resource->vtype != "NULL")) {
				$virtualization = new virtualization();
				$virtualization->get_instance_by_id($g_resource->vtype);
				if (strstr($virtualization->type, "-vm")) {
					// virtual
					$resources_virtual++;
					$restype=1;
				} else {
					// physical
					$resources_physical++;
					$restype=0;
				}
			} else {
				// we treat unknown system types as physical
				$resources_physical++;
				$restype=0;
			}


			// resource load
			// is idle or active ?
			if (("$g_resource->imageid" == "1") && ("$g_resource->state" == "active")) {
				// idle
				$resources_available++;
				// virtual or physical ?
				if ($restype == 0) {
					$resources_available_physical++;
				} else {
					$resources_available_virtual++;
				}
			} else if ("$g_resource->state" == "active") {
				// active
				$resources_active++;
				$dc_load_overall = $dc_load_overall + $g_resource->load;

				// is storage ?
				if (in_array($g_resource->id, $storage_resources_array)) {
					$storage_active++;
					$storage_load_overall = $storage_load_overall + $g_resource->load;
					// is peak ?
					if ($g_resource->load > $storage_load_peak) {
						$storage_load_peak =  $g_resource->load;
					}
				}
				// is appliance ?
				if (in_array($g_resource->id, $appliance_resources_array)) {
					$appliance_active++;
					$appliance_load_overall = $appliance_load_overall + $g_resource->load;
					// is peak ?
					if ($g_resource->load > $appliance_load_peak) {
						$appliance_load_peak =  $g_resource->load;
					}
				}


			} else if ("$g_resource->state" == "error") {
				// error
				$resources_error++;
				// is storage ?
				if (in_array($g_resource->id, $storage_resources_array)) {
					$storage_error++;
				}
				// is appliance ?
				if (in_array($g_resource->id, $appliance_resources_array)) {
					$appliance_error++;
				}
			}
		}
		// end of gathering

		// divide with number of active resources, appliances + storages
		if ($resources_active != 0) {
			$dc_load_overall = $dc_load_overall/$resources_active;
			$dc_load_overall = number_format($dc_load_overall, 2, '.', '');
		}
		if ($appliance_active != 0) {
			$appliance_load_overall = $appliance_load_overall/$appliance_active;
			$appliance_load_overall = number_format($appliance_load_overall, 2, '.', '');
		}
		if ($storage_active != 0) {
			$storage_load_overall = $storage_load_overall/$storage_active;
			$storage_load_overall = number_format($storage_load_overall, 2, '.', '');
		}

		echo "$dc_load_overall,$storage_load_overall,$storage_load_peak,$appliance_load_overall,$appliance_load_peak,$resources_all,$resources_physical,$resources_virtual,$resources_available,$resources_available_physical,$resources_available_virtual,$resources_error,$appliance_error,$storage_error";
		exit(0);
	}

	//--------------------------------------------
	/**
	 * Get the last 10 event log entries
	 * TODO: Add param 'limit'
	 *
	 * @access public
	 */
	//--------------------------------------------
	function eventlist() {
		// check permissions
		$this->controller->openqrm->init();
		$class ='event_controller';
		$path  = $this->controller->openqrm->get('webdir').'/server/event/class/event.controller.class.php';
		$role  = $this->controller->openqrm->role($this->response);
		require_once($path);
		$controller = new $class($this->controller->openqrm, $this->response);
		$_REQUEST[$controller->actions_name] = 'select';
		$data = $role->check_permission($controller, true);
		if($data === true) {
			$event = new event();
			$event_list = $event->display_overview(0,14,'event_time','desc');	// $offset, $limit, $sort, $order, $mode = null
			echo json_encode($event_list);
		} else {
			$msg = array(
						array(
							'event_priority' => '9',
							'event_source' => 'Permissions',
							'event_time' => time(),
							'event_description' => 'Permission denied for action <i>select</i> on controller <i>events</i>',
						)
					);
			echo json_encode($msg);
		}
		exit(0);
	}

	//--------------------------------------------
	/**
	 * Get datacenter load statistics
	 * currently used in the dashboard load chart
	 *
	 * @access public
	 */
	//--------------------------------------------
	function statistics() {
		require_once($this->controller->openqrm->get('basedir').'/web/base/server/aa_server/class/datacenter.class.php');
		$data = new datacenter();
		echo json_encode($data->get());
		exit(0);
	}

	//--------------------------------------------
	/**
	 * Get full storage list.
	 * Storage type names are injected in storageList
	 * used in the dashboard load chart
	 *
	 * @access public
	 */
	//--------------------------------------------
	function storage_list() {
			require_once($this->controller->openqrm->get('basedir').'/web/base/class/storage.class.php');
			require_once($this->controller->openqrm->get('basedir').'/web/base/class/deployment.class.php');
			
			$deploymentObj = new deployment();
			foreach($deploymentObj->get_storagedescription_list() as $deploymentType){
				$deploymentMap[$deploymentType['value']] = $deploymentType['label'];
			}

			$storageObj = new storage();
			$list = $storageObj->get_full_storage_list();
			$storageList = array();
			$i=0;
			foreach($list as $key => $storage) {
				// check deployment type exists
				if(isset($deploymentMap[$storage['storage_type']])) {
					$storageList[$i]['storage_type'] = $deploymentMap[$storage['storage_type']];
					$i++;
				}
			}
			echo json_encode($storageList);
			exit(0);
	}

	//--------------------------------------------
	/**
	 * Get full server list. 
	 * Virtualization names are injected as string in applist
	 * used in the dashboard server donut chart
	 * TODO: Make limit in "display_overview()" call dynamic (e.g. no. of servers)
	 * 
	 * @access public
	 */
	//--------------------------------------------
	function server_list() {
			require_once($this->controller->openqrm->get('basedir').'/web/base/class/appliance.class.php');
			require_once($this->controller->openqrm->get('basedir').'/web/base/class/virtualization.class.php');

			$virtObj = new virtualization();
			$list = $virtObj->get_list();
			foreach($list as $virtualization) {
				$virtualizationMap[$virtualization['value']] = $virtualization['label'];
			}
			
			$applianceObj = new appliance();
			$list = $applianceObj->display_overview(0, 1000, 'appliance_id', '');
			$applist = array();
			$i=0;
			foreach($list as $appliance) {
				// check virtualization type exists
				if(isset($virtualizationMap[$appliance['appliance_virtualization']])) {
					$applist[$i]['appliance_virtualization'] = $virtualizationMap[$appliance['appliance_virtualization']];
					$i++;
				}
			}
			echo json_encode($applist);
			exit(0);
	}

	//--------------------------------------------
	/**
	 * Method to map event_priority to status strings. 
	 * 
	 * @todo	Should be moved to event object
	 * @param	int $event_priority	
	 * @return	string	status string
	 */
	//--------------------------------------------
	function __getEventStatus($event_priority) {
		switch ($event_priority) {
			/*
			case 0: $icon = "off.png"; 	break;
			case 1: $icon = "error.png"; break;
			case 2: $icon = "error.png"; break;
			case 3:	$icon = "error.png"; break;
			case 4:	$icon = "unknown.png"; break;
			case 5:	$icon = "active.png"; break;
			case 6:	$icon = "idle.png"; break;
			case 7:	$icon = "idle.png"; break;
			case 8:	$icon = "idle.png"; break;
			case 9:	$icon = "transition.png"; break;
			case 10:$icon = "active.png"; break;
			*/
		
			case 0: 	
				$status = 'disabled'; 	break;
			case 1: 
			case 2: 
			case 3:	
				$status = 'error'; break;		// error event
			case 4:	
			case 5:	
			case 6:	
			case 7:	
			case 8:	
				$status = 'notice'; break;	// undefined event
			case 9:	
				$status = 'running'; break;	// active event
			case 10:
				$status = 'ok'; break;		// notice event
			default:
				$status = 'unkown'; break;
		
		}
		return $status;
	}
}

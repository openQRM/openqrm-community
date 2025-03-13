<?php
/*
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
*/

$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";



if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'get_dc_status':
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
			break;



		// event status
		case 'get_event_status':
			// how many errors to show in the ui
			$max_show_error = 10;
			// all events
			$events_all = 0;
			// all error events
			$events_error = 0;

			$table = new htmlobject_table_identifiers_checked('event_id');

			$arHead = array();
			$arHead['event_icon'] = array();
			$arHead['event_icon']['title'] ='';

			$arHead['event_id'] = array();
			$arHead['event_id']['title'] ='ID';

			$arHead['event_description'] = array();
			$arHead['event_description']['title'] ='Description';

			$arBody = array();
			$event = new event();
			$event_list = $event->get_list();
			foreach ($event_list as $ev) {
				$events_all++;
				$event_id = $ev['value'];
				$g_event = new event();
				$g_event->get_instance_by_id($event_id);
				if (($g_event->priority <= 2) && ($g_event->status == 0)) {
					// fill in event-error
					$events_error++;
					if ($events_error < $max_show_error) {
						$arBody[] = array(
							'event_icon' => "<img widht='16' height='16' src='/openqrm/base/img/error.png'>",
							'event_id' => $g_event->id,
							'event_description' => $g_event->description,
						);
					}
				}
			}
			$table->id = 'Tabelle';
			$table->css = 'htmlobject_table';
			$table->border = 1;
			$table->cellspacing = 0;
			$table->cellpadding = 3;
			$table->sort = '';
			$table->head = $arHead;
			$table->body = $arBody;
			$table->max = $max_show_error;

			// set template
			$e = new Template_PHPLIB();
			$e->debug = false;
			$e->setFile('tplfile', $RootDir.'/server/aa_server/tpl/events-summary.tpl.php');
			$e->setVar(array(
				'events_all' => $events_all,
				'events_error' => $events_error,
				'events_error_table' => $table->get_string(),
				'max_show_error' => $max_show_error,
			));
			$disp =  $e->parse('out', 'tplfile');
			echo $disp;
			exit(0);
			break;



		// event status
		case 'get_resource_status':
			// how many errors to show in the ui
			$max_show_resources = 10;
			// all resources
			$res_all = 0;
			// all available resources
			$res_available = 0;
			// all active resources
			$res_active = 0;
			// resources in error
			$res_error = 0;

			$table = new htmlobject_table_identifiers_checked('resource_id');

			$arHead = array();
			$arHead['resource_icon'] = array();
			$arHead['resource_icon']['title'] ='';

			$arHead['resource_id'] = array();
			$arHead['resource_id']['title'] ='ID';

			$arHead['resource_hostname'] = array();
			$arHead['resource_hostname']['title'] ='Name';

			$arHead['resource_ip'] = array();
			$arHead['resource_ip']['title'] ='Ip-address';

			$arHead['resource_mac'] = array();
			$arHead['resource_mac']['title'] ='Mac-address';

			$arHead['resource_load'] = array();
			$arHead['resource_load']['title'] ='Load';

			$arBody = array();
			$resource = new resource();
			// find active, error + available
			$in_res_table=0;
			$res_load_array = array();
			$resource_list = $resource->get_list();
			foreach ($resource_list as $res) {
				$res_id = $res['resource_id'];
				$g_resource = new resource();
				$g_resource->get_instance_by_id($res_id);
				// start gathering
				$res_all++;
				// resource load
				// is idle or active ?
				if (("$g_resource->imageid" == "1") && ("$g_resource->state" == "active")) {
					// idle
					$res_available++;
				} else if ("$g_resource->state" == "active") {
					// active
					$res_active++;
					// check load
					$res_load_array[$g_resource->id] = $g_resource->load;
				} else if ("$g_resource->state" == "error") {
					// error
					$res_error++;
				}
			}

			// find the most loaded resources
			arsort($res_load_array, true);
			$sorted_res_load_array_ids = array_keys($res_load_array);
			foreach ($sorted_res_load_array_ids as $ml_res_id) {
				$l_resource = new resource();
				$l_resource->get_instance_by_id($ml_res_id);
				if (("$l_resource->state" == "active") && ("$l_resource->imageid" != "1")) {
					if ($in_res_table < $max_show_resources) {
						// fill in array body
						$arBody[] = array(
							'resource_icon' => "<img widht='16' height='16' src='/openqrm/base/img/active.png'>",
							'resource_id' => $l_resource->id,
							'resource_hostname' => $l_resource->hostname,
							'resource_ip' => $l_resource->ip,
							'resource_mac' => $l_resource->mac,
							'resource_load' => $l_resource->load,
						);
						$in_res_table++;
					} else {
						break;
					}
				}
			}

			$table->id = 'Tabelle';
			$table->css = 'htmlobject_table';
			$table->border = 1;
			$table->cellspacing = 0;
			$table->cellpadding = 3;
			$table->sort = '';
			$table->head = $arHead;
			$table->body = $arBody;
			$table->max = $max_show_resources;

			// set template
			$e = new Template_PHPLIB();
			$e->debug = false;
			$e->setFile('tplfile', $RootDir.'/server/aa_server/tpl/resource-summary.tpl.php');
			$e->setVar(array(
				'resource_all' => $res_all,
				'resource_available' => $res_available,
				'resource_active' => $res_active,
				'resource_error' => $res_error,
				'resource_table' => $table->get_string(),
				'max_show_resources' => $max_show_resources,
			));
			$disp =  $e->parse('out', 'tplfile');
			echo $disp;
			exit(0);
			break;



		// appliance status
		case 'get_appliance_status':
			// how many appliances to show in the ui
			$max_show_appliance = 10;
			// all appliances
			$app_all = 0;
			// all active appliances
			$app_active = 0;
			// all error events
			$app_error = 0;

			$table = new htmlobject_table_identifiers_checked('appliance_id');

			$arHead = array();
			$arHead['appliance_icon'] = array();
			$arHead['appliance_icon']['title'] ='';

			$arHead['appliance_id'] = array();
			$arHead['appliance_id']['title'] ='ID';

			$arHead['appliance_name'] = array();
			$arHead['appliance_name']['title'] ='Name';

			$arHead['appliance_resource'] = array();
			$arHead['appliance_resource']['title'] ='Res.';

			$arHead['appliance_load'] = array();
			$arHead['appliance_load']['title'] ='Load';

			$in_app_table = 0;
			$arBody = array();
			$app_load_array = array();
			$appliance = new appliance();
			$appliance_list = $appliance->get_list();
			foreach ($appliance_list as $ap) {
				$app_all++;
				$appliance_id = $ap['value'];
				$g_appliance = new appliance();
				$g_appliance->get_instance_by_id($appliance_id);
				if ((!strcmp($g_appliance->state, "active")) || ($g_appliance->resources == 0)) {
					// fill in app_all
					$app_active++;
					// check resource state
					$a_resource = new resource();
					$a_resource->get_instance_by_id($g_appliance->resources);
					if ("$a_resource->state" == "active") {
						$app_load = $a_resource->load;
						$app_load_array[$g_appliance->id] = $a_resource->load;
					} else if ("$a_resource->state" == "error") {
						$app_error++;
					}

				}
			}

			// find the most loaded appliance
			arsort($app_load_array, true);
			$sorted_app_load_array_ids = array_keys($app_load_array);
			foreach ($sorted_app_load_array_ids as $ml_app_id) {
				$l_app = new appliance();
				$l_app->get_instance_by_id($ml_app_id);
				if ($in_app_table < $max_show_appliance) {
					$l_res = new resource();
					$l_res->get_instance_by_id($l_app->resources);
					$l_app_load = $l_res->load;
					// fill in array body
					$arBody[] = array(
						'appliance_icon' => "<img widht='16' height='16' src='/openqrm/base/img/active.png'>",
						'appliance_id' => $l_app->id,
						'appliance_name' => $l_app->name,
						'appliance_resource' => $l_app->resources,
						'appliance_load' => $l_app_load,
					);
					$in_app_table++;
				} else {
					break;
				}
			}

			$table->id = 'Tabelle';
			$table->css = 'htmlobject_table';
			$table->border = 1;
			$table->cellspacing = 0;
			$table->cellpadding = 3;
			$table->sort = '';
			$table->head = $arHead;
			$table->body = $arBody;
			$table->max = $max_show_appliance;

			// set template
			$e = new Template_PHPLIB();
			$e->debug = false;
			$e->setFile('tplfile', $RootDir.'/server/aa_server/tpl/appliance-summary.tpl.php');
			$e->setVar(array(
				'appliance_all' => $app_all,
				'appliance_error' => $app_error,
				'appliance_active' => $app_active,
				'appliance_table' => $table->get_string(),
				'max_show_appliance_' => $max_show_appliance,
			));
			$disp =  $e->parse('out', 'tplfile');
			echo $disp;
			exit(0);
			break;



		// storage status
		case 'get_storage_status':
			// how many storages to show in the ui
			$max_show_storage = 10;
			// all storages
			$store_all = 0;
			// all storages in error
			$store_error = 0;

			$table = new htmlobject_table_identifiers_checked('storage_id');

			$arHead = array();
			$arHead['storage_icon'] = array();
			$arHead['storage_icon']['title'] ='';

			$arHead['storage_id'] = array();
			$arHead['storage_id']['title'] ='ID';

			$arHead['storage_name'] = array();
			$arHead['storage_name']['title'] ='Name';

			$arHead['storage_resource'] = array();
			$arHead['storage_resource']['title'] ='Res.';

			$arHead['storage_type'] = array();
			$arHead['storage_type']['title'] ='Type';

			$arHead['storage_load'] = array();
			$arHead['storage_load']['title'] ='Load';


			$arBody = array();
			$store_load_array = array();
			$storage = new storage();
			$storage_list = $storage->get_list();

			foreach ($storage_list as $st) {
				$store_all++;
				$storage_id = $st['value'];
				$g_storage = new storage();
				$g_storage->get_instance_by_id($storage_id);
				// check resource state
				$st_resource = new resource();
				$st_resource->get_instance_by_id($g_storage->resource_id);
				if ("$st_resource->state" == "active") {
					$store_load = $st_resource->load;
					$store_load_array[$g_storage->id] = $st_resource->load;
				} else if ("$st_resource->state" == "error") {
					$store_error++;
				}
			}

			// find the most loaded storage
			$in_store_table = 0;
			arsort($store_load_array, true);
			$sorted_store_load_array_ids = array_keys($store_load_array);
			foreach ($sorted_store_load_array_ids as $ml_store_id) {
				$l_store = new storage();
				$l_store->get_instance_by_id($ml_store_id);
				if ($in_store_table < $max_show_storage) {
					// get resource load
					$l_res = new resource();
					$l_res->get_instance_by_id($l_store->resource_id);
					$l_store_load = $l_res->load;
					// get storage type
					$deployment = new deployment();
					$deployment->get_instance_by_id($l_store->type);

					// fill in array body
					$arBody[] = array(
						'storage_icon' => "<img widht='16' height='16' src='/openqrm/base/img/active.png'>",
						'storage_id' => $l_store->id,
						'storage_name' => $l_store->name,
						'storage_resource' => $l_store->resource_id,
						'storage_type' => $deployment->storagedescription,
						'storage_load' => $l_store_load,
					);
					$in_store_table++;
				} else {
					break;
				}
			}

			$table->id = 'Tabelle';
			$table->css = 'htmlobject_table';
			$table->border = 1;
			$table->cellspacing = 0;
			$table->cellpadding = 3;
			$table->sort = '';
			$table->head = $arHead;
			$table->body = $arBody;
			$table->max = $max_show_storage;

			// set template
			$e = new Template_PHPLIB();
			$e->debug = false;
			$e->setFile('tplfile', $RootDir.'/server/aa_server/tpl/storage-summary.tpl.php');
			$e->setVar(array(
				'storage_all' => $store_all,
				'storage_error' => $store_error,
				'storage_table' => $table->get_string(),
				'max_show_storage' => $max_show_storage,
			));
			$disp =  $e->parse('out', 'tplfile');
			echo $disp;
			exit(0);
			break;



		// cloud status
		case 'get_cloud_status':

			// check if the cloud is enabled
			if (!file_exists($RootDir."plugins/cloud/.running")) {
				echo "<br><br><br><h1>The Cloud is not enabled</h1>";
				echo "<h4>Please enable (and start) it via the Plugin-Manager</h4>";
				exit(0);
			}
			require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
			require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
			// how many cloud requests to show in the ui
			$max_show_cr = 10;
			// all cloud users
			$cloud_users = 0;
			// all cloud requests
			$cr_all = 0;
			// all active cloud requests
			$cr_active = 0;
			// active cloud appliances which resource are in error
			$cloud_errors = 0;

			$table = new htmlobject_table_identifiers_checked('cr_id');

			$arHead = array();

			$arHead['cr_icon'] = array();
			$arHead['cr_icon']['title'] ='';

			$arHead['cr_id'] = array();
			$arHead['cr_id']['title'] ='ID';

			$arHead['cr_user'] = array();
			$arHead['cr_user']['title'] ='User';

			$arHead['cr_state'] = array();
			$arHead['cr_state']['title'] ='State';

			$arHead['cr_app'] = array();
			$arHead['cr_app']['title'] ='Appliance';

			$arHead['cr_res'] = array();
			$arHead['cr_res']['title'] ='Res.';

			$arHead['cr_load'] = array();
			$arHead['cr_load']['title'] ='Load';

			$arBody = array();
			$cloud_load_array = array();
			$cr = new cloudrequest();
			$cr_list = $cr->get_all_ids();

			foreach ($cr_list as $cr_id_arr) {
				$cr_id=$cr_id_arr['cr_id'];
				$cr_all++;
				$g_cr = new cloudrequest();
				$g_cr->get_instance_by_id($cr_id);
				if ($g_cr->status == 3) {
					// fill active
					$cr_active++;
					// find most active cloud appliance
					$c_app = new appliance();
					$c_app->get_instance_by_id($g_cr->appliance_id);
					$c_resource = new resource();
					$c_resource->get_instance_by_id($c_app->resources);
					if ("$c_resource->state" == "active") {
						$cloud_load_array[$cr_id] = $c_resource->load;
					} else if ("$c_resource->state" == "error") {
						$cloud_errors++;
					}
				}
			}

			// find the most loaded cloud appliance
			$in_cr_table = 0;
			arsort($cloud_load_array, true);
			$sorted_cr_load_array_ids = array_keys($cloud_load_array);
			foreach ($sorted_cr_load_array_ids as $ml_cr_id) {
				$l_cr = new cloudrequest();
				$l_cr->get_instance_by_id($ml_cr_id);
				if ($in_cr_table < $max_show_cr) {
					$ml_cr = new cloudrequest();
					$ml_cr->get_instance_by_id($ml_cr_id);
					// get user
					$ml_cr_user = new clouduser();
					$ml_cr_user->get_instance_by_id($ml_cr->cu_id);
					// get appliance
					$ml_app = new appliance();
					$ml_app->get_instance_by_id($ml_cr->appliance_id);
					// get resource load
					$cr_res = new resource();
					$cr_res->get_instance_by_id($ml_app->resources);
					$cr_load = $cr_res->load;
					// fill in array body
					$arBody[] = array(
						'cr_icon' => "<img widht='16' height='16' src='/openqrm/base/img/active.png'>",
						'cr_id' => $ml_cr->id,
						'cr_user' => $ml_cr_user->name,
						'cr_state' => "active",
						'cr_app' => $ml_cr->appliance_id." / ".$ml_app->name,
						'cr_res' => $cr_res->id,
						'cr_load' => $cr_load,
					);
					$in_cr_table++;
				} else {
					break;
				}
			}
			// how many cloud users we have ?
			$cu = new clouduser();
			$cloud_users = $cu->get_count();

			$table->id = 'Tabelle';
			$table->css = 'htmlobject_table';
			$table->border = 1;
			$table->cellspacing = 0;
			$table->cellpadding = 3;
			$table->sort = '';
			$table->head = $arHead;
			$table->body = $arBody;
			$table->max = $max_show_cr;

			// set template
			$e = new Template_PHPLIB();
			$e->debug = false;
			$e->setFile('tplfile', $RootDir.'/server/aa_server/tpl/cloud-summary.tpl.php');
			$e->setVar(array(
				'cr_all' => $cr_all,
				'cr_active' => $cr_active,
				'cloud_users' => $cloud_users,
				'cloud_errors' => $cloud_errors,
				'cloud_table' => $table->get_string(),
				'max_show_cr' => $max_show_cr,
			));
			$disp =  $e->parse('out', 'tplfile');
			echo $disp;
			exit(0);
			break;

		
		case 'get_eventlist':
		
			$event = new event();
			$event_list = $event->display_overview(0,10,'event_time','desc');			// $offset, $limit, $sort, $order, $mode = null
			echo json_encode($event_list);
		
			exit(0);
			break;

	}
}

/**
 * Method to map event_priority to status strings. 
 * 
 * @todo	Should be moved to event object
 * @param	int $event_priority	
 * @return	string	status string
 */
function getEventStatus($event_priority) {
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




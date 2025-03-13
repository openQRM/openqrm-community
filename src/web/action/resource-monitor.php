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

$RootDir = $_SERVER["DOCUMENT_ROOT"] . '/openqrm/base/';

#exit quickly when queries hit an unconfigured openQRM
require_once($RootDir . '/class/file.handler.class.php');
$file = new file_handler();

if ($file->exists($RootDir . '/unconfigured')) {
	return '';
}


require_once $RootDir . "/include/openqrm-database-functions.php";
require_once $RootDir . "/class/resource.class.php";
require_once $RootDir . "/class/image.class.php";
require_once $RootDir . "/class/image_authentication.class.php";
require_once $RootDir . "/class/kernel.class.php";
require_once $RootDir . "/class/plugin.class.php";
require_once $RootDir . "/class/event.class.php";
require_once $RootDir . "/class/openqrm_server.class.php";
require_once $RootDir . '/server/aa_server/class/datacenter.class.php';
require_once $RootDir . '/class/htmlobjects/htmlobject.class.php';
require_once $RootDir . '/include/requestfilter.inc.php';
// filter inputs
$html = new htmlobject($RootDir . '/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;

global $RESOURCE_INFO_TABLE;

$source_ip = $_SERVER["REMOTE_ADDR"];
$resource_id = $request->get('resource_id');
$resource_command = $request->get('resource_command');

$resource_mac = $request->get('resource_mac');
$resource_ip = $request->get('resource_ip');

#TODO should we block empty requests with no $resource_mac / $resource_ip ??
/*
if(empty($resource_mac) || empty($resource_ip)){
	#if resource not found error out
	$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
	header($protocol . ' ' . 400 . ' ' . 'Bad Request');
	echo "<html>";
	exit();
}
*/

$resource_subnet = $request->get('resource_subnet');
$resource_state = $request->get('resource_state');
$resource_event = $request->get('resource_event');
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "resource_", 9) == 0) {
		$resource_fields[$key] = $request->get($key);
	}
}

if (@$resource_fields["resource_command"]) {
	unset($resource_fields["resource_command"]);
}

// set lastgood
$resource_lastgood = $_SERVER['REQUEST_TIME'];
$resource_fields["resource_lastgood"] = $resource_lastgood;

// gather for event vars
$event_name = $request->get('event_name');
$event_priority = $request->get('event_priority');
$event_source = $request->get('event_source');
$event_description = $request->get('event_description');


$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS = $openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

$event = new event();


switch ($resource_command) {
	
	// get_parameter requires :
	// resource_mac
	default:
	case 'get_parameter':
		//check if resource_id exists in db, else set resource_id to -1
		$resource = new resource();
		if($resource_id != -1 && !$resource->exists_by_id($resource_id)){
			$resource_id = -1;
		}
		// if resource-id = -1 we add a new resource first
		if ($resource_id == "-1") {
			// check if resource already exists
			if (!$resource->exists($resource_mac)) {
				// add resource
				$new_resource_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$resource->id = $new_resource_id;
				// 	check if resource_id is free
				if (!$resource->is_id_free($resource->id)) {
					$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 3, "resource-monitor", "Given resource id $resource->id is already in use!", "", "", 0, 1, $resource->id);
					echo "Given resource id $resource->id is already in use!";
					exit();
				}
				$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Adding new resource $new_resource_id ($resource_mac)", "", "", 0, 1, $resource->id);
				# send add resource to openQRM-server
				$openqrm_server->send_command("openqrm_server_add_resource $new_resource_id $resource_mac $resource_ip");
				# add resource to db
				$resource_fields["resource_id"] = $new_resource_id;
				$resource_fields["resource_localboot"] = 0;
				$resource_fields["resource_vtype"] = 1;
				$resource_fields["resource_vhostid"] = $new_resource_id;
				$resource_fields["resource_subnet"] = $resource_subnet;
				$resource->add($resource_fields);
			}
		}
		if (strlen($resource_mac)) {
			$resource->get_instance_by_mac("$resource_mac");
			// update the resource parameter in any way
			$resource->update_info($resource->id, $resource_fields);
			$resource->get_parameter($resource->id);
		} else if (strlen($resource_id)) {
			$resource->get_instance_by_id($resource_id);
			// update the resource parameter in any way
			$resource->update_info($resource->id, $resource_fields);
			$resource->get_parameter($resource->id);
		}
		
		/*
		else{
			#seems like idle resource booted, request via openqrm-client send no variables like mac/ip
		
			#if resource not found error out
			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
			header($protocol . ' ' . 400 . ' ' . 'Bad Request');
			echo "<html>";
		}
		*/
	
		exit();
	
	// update_info requires :
	// resource_id
	// array of resource_fields
	case 'update_info':
		$resource = new resource();
		if (strlen($resource_id)) {
			
			#$resource_instance = $resource->get_instance_by_id($resource_id);
			
			$resource_instance = $resource->get_instance($resource_id,"","");
			if($resource_instance === false){
				$event->log("update_info", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Processing statistics from new resource $resource_id", "", "", 0, 0, $resource_id);
				$resource->update_info($resource_id, $resource_fields);
			} else if (!strcmp($resource->event, "reboot")) {
				// we do not accept this stats since the resource will be rebooted
				// reset the events field
				$resource_reboot_fields = array();
				// change lastgood
				$resource_reboot_fields["resource_lastgood"] = -1;
				$resource_reboot_fields["resource_state"] = "transition";
				$resource_reboot_fields["resource_event"] = "reboot1";
				$event->log("update_info", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Rejecting statistics from rebooting resource $resource_id (1)", "", "", 0, 0, $resource_id);
				$resource->update_info($resource_id, $resource_reboot_fields);
			} else if (!strcmp($resource->event, "reboot1")) {
				$resource_reboot_fields = array();
				// change lastgood
				$resource_reboot_fields["resource_lastgood"] = -1;
				$resource_reboot_fields["resource_state"] = "transition";
				$resource_reboot_fields["resource_event"] = "reboot2";
				$event->log("update_info", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Rejecting statistics from rebooting resource $resource_id (2)", "", "", 0, 0, $resource_id);
				$resource->update_info($resource_id, $resource_reboot_fields);
			} else if (!strcmp($resource->event, "reboot2")) {
				$resource_reboot_fields = array();
				// change lastgood
				$resource_reboot_fields["resource_lastgood"] = -1;
				$resource_reboot_fields["resource_state"] = "transition";
				$resource_reboot_fields["resource_event"] = "reboot3";
				$event->log("update_info", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Rejecting statistics from rebooting resource $resource_id (3)", "", "", 0, 0, $resource_id);
				$resource->update_info($resource_id, $resource_reboot_fields);
			} else if (!strcmp($resource->event, "reboot3")) {
				$resource_reboot_fields = array();
				// change lastgood
				$resource_reboot_fields["resource_lastgood"] = -1;
				$resource_reboot_fields["resource_state"] = "transition";
				$resource_reboot_fields["resource_event"] = "reboot4";
				$event->log("update_info", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Rejecting statistics from rebooting resource $resource_id (4)", "", "", 0, 0, $resource_id);
				$resource->update_info($resource_id, $resource_reboot_fields);
			} else if (!strcmp($resource->event, "reboot4")) {
				$resource_reboot_fields = array();
				// change lastgood
				$resource_reboot_fields["resource_lastgood"] = -1;
				$resource_reboot_fields["resource_state"] = "transition";
				$resource_reboot_fields["resource_event"] = "";
				$event->log("update_info", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Rejecting statistics from rebooting resource $resource_id (5)", "", "", 0, 0, $resource_id);
				$resource->update_info($resource_id, $resource_reboot_fields);
			} else {
				// $event->log("update_info", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Processing statistics from resource $resource_id", "", "", 0, 0, $resource_id);
				$resource->update_info($resource_id, $resource_fields);
			}
		}
		
		
		// in case the openQRM-server sends its stats we check
		// the states of all resources
		if (("$resource_id" == "0") && ("$OPENQRM_SERVER_IP_ADDRESS" == "$source_ip")) {
			// $event->log("update_info", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Checking states of all resources", "", "", 0, 0, 0);
			$resource->check_all_states();
			
			// check if there are any image_authentications to manage
			$image_auth = new image_authentication();
			$image_auth->check_all_image_authentication();
			
			// gather stats
			$datacenter = new datacenter();
			$datacenter->statistics();
			
			// here a plugin hook for things which needs to be done periodically
			$plugin = new plugin();
			$enabled_plugins = $plugin->enabled();
			
			foreach ($enabled_plugins as $index => $plugin_name) {
				$plugin_monitor_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-monitor-hook.php";
				if (file_exists($plugin_monitor_hook)) {
					// $event->log("plugin_monitor_hook", $_SERVER['REQUEST_TIME'], 5, "resource-monitor.php", "Found plugin $plugin_name handling monitor event.", "", "", 0, 0, $resource_id);
					require_once "$plugin_monitor_hook";
					$monitor_function = "openqrm_" . "$plugin_name" . "_monitor";
					$monitor_function = str_replace("-", "_", $monitor_function);
					$monitor_function();
				}
			}
			
		}
		exit();
		break;
	
	// update_status requires :
	// resource_id
	// resource_state
	// resource_event
	case 'update_status':
		if (strlen($resource_id)) {
			$resource = new resource();
			$resource->update_status($resource_id, $resource_state, $resource_event);
		}
		exit();
		break;
	
	// post_event requires :
	// resource_id
	// event_name
	// event_priority
	// event_source
	// event_description
	case 'post_event':
		if (strlen($resource_id) && strlen($event_name)) {
			$event->log($event_name, $_SERVER['REQUEST_TIME'], $event_priority, $event_source, $event_description, "", "", 0, 0, 0);
		}
		exit();
		break;
	
}


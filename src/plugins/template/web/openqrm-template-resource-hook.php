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


// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function openqrm_template_resource($cmd, $resource_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$resource_id=$resource_fields["resource_id"];
	$resource = new resource();
	$resource->get_instance_by_id($resource_id);
	$resource_ip=$resource->ip;
	$resource_name=$resource->hostname;

	$event->log("openqrm_template_resource", $_SERVER['REQUEST_TIME'], 5, "openqrm-template-resource-hook.php", "Handling $cmd event $resource_id/$resource_name/$resource_ip", "", "", 0, 0, $resource_id);

	// we do only care if we serving an appliance
	$appliance = new appliance();
	$appliance_record_set = array();
	$appliance_id_array = array();
	$appliance_record_set = $appliance->get_all_ids();
	// the appliance_array from getlist is a 2-dimensional array
	foreach ($appliance_record_set as $index => $appliance_id_array) {

		foreach ($appliance_id_array as $index => $id) {
			$tapp = new appliance();
			$tapp->get_instance_by_id($id);
			$tapp_state = $tapp->state;
			$tapp_resources = $tapp->resources;

			if (!strcmp($tapp_state, "active")) {

				if ($tapp_resources == $resource_id) {
					// we found the resources active appliance, running the cmd

					$appliance_name = $tapp->name;
					switch($cmd) {
						case "start":
							$openqrm_server = new openqrm_server();
							$openqrm_server->send_command($OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/template/bin/openqrm-template-manager start ".$id." ".$appliance_name." ".$resource_ip." --openqrm-cmd-mode background");
							break;
						case "stop":
							$openqrm_server = new openqrm_server();
							$openqrm_server->send_command($OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/template/bin/openqrm-template-manager stop ".$id." ".$appliance_name." ".$resource_ip." --openqrm-cmd-mode background");
							break;
					}
				}
			}
		}
	}
}




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
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
// special linuxcoe classes
require_once "$RootDir/plugins/linuxcoe/class/linuxcoestate.class.php";
require_once "$RootDir/plugins/linuxcoe/class/linuxcoeresource.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $openqrm_server;
$event = new event();
global $event;


function openqrm_linuxcoe_appliance($cmd, $appliance_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$openqrm_server = new openqrm_server();
	$appliance_id=$appliance_fields["appliance_id"];
	$appliance_name=$appliance_fields["appliance_name"];
	$resource = new resource();
	$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
	$resource_mac=$resource->mac;
	$resource_ip=$resource->ip;
	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);
	$linuxcoe_install_timeout = 240;
	// check appliance values, maybe we are in update and they are incomplete
	if ($appliance->imageid == 1) {
		return;
	}
	if (($resource->id == "-1") || ($resource->id == "")) {
		return;
	}

	// check if image is type linuxcoe-deployment
	$image = new image();
	$image->get_instance_by_id($appliance->imageid);
	// linuxcoe configured in image deployment parameters ?
	$linuxcoe_auto_install_enabled = false;
	$linuxcoe_deployment_parameters = trim($image->get_deployment_parameter("INSTALL_CONFIG"));
	if (strlen($linuxcoe_deployment_parameters)) {
		$linuxcoe_deployment_parameter_arr = explode(":", $linuxcoe_deployment_parameters);
		$local_deployment_persistent = $linuxcoe_deployment_parameter_arr[0];
		$local_deployment_type = $linuxcoe_deployment_parameter_arr[1];
		if (strcmp($local_deployment_type, "linuxcoe-deployment")) {
			$event->log("openqrm_linuxcoe_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-linuxcoe-appliance-hook.php", "Appliance ".$appliance_id."/".$appliance_name." image is not from type linuxcoe-deployment", "", "", 0, 0, $resource->id);
			return;
		}
		$linuxcoe_server_storage_id = $linuxcoe_deployment_parameter_arr[2];
		$linuxcoe_install_profile = $linuxcoe_deployment_parameter_arr[3];
		$linuxcoe_auto_install_enabled = true;
	}


	$event->log("openqrm_linuxcoe_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-linuxcoe-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$resource_ip/$resource_mac", "", "", 0, 0, $resource->id);
	switch($cmd) {
		case "start":
			$event->log("openqrm_linuxcoe_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-linuxcoe-appliance-hook.php", "START event $appliance_id/$appliance_name/$resource_ip/$resource_mac", "", "", 0, 0, $resource->id);

			if ($linuxcoe_auto_install_enabled) {
				// prepare automatic-installation / transfer client to linuxcoe server
				$linuxcoe_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/linuxcoe/bin/openqrm-linuxcoe-manager transfer_to_linuxcoe -x ".$resource->id." -i ".$resource_ip." -m ".$resource_mac." -n ".$linuxcoe_install_profile." --openqrm-cmd-mode background";
				$event->log("openqrm_linuxcoe_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-linuxcoe-appliance-hook.php", "transfer_to_linuxcoe $resource_ip / $linuxcoe_install_profile", "", "", 0, 0, $resource->id);
				$openqrm_server->send_command($linuxcoe_command, NULL, true);
				// Remove image-deployment paramters, if auto-install is a single-shot actions
				if (!strcmp($local_deployment_persistent, "0")) {
					$image->set_deployment_parameters("INSTALL_CONFIG", "");
				}

				// create linuxcoestate-state object to allow to run a late setboot to local command on the vm host
				$linuxcoe_state = new linuxcoestate();
				$linuxcoe_state->remove_by_resource_id($resource->id);
				$linuxcoe_state_fields=array();
				$linuxcoe_state_fields["linuxcoe_id"]=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$linuxcoe_state_fields["linuxcoe_resource_id"]=$resource->id;
				$linuxcoe_state_fields["linuxcoe_install_start"]=$_SERVER['REQUEST_TIME'];
				$linuxcoe_state_fields["linuxcoe_timeout"]=$linuxcoe_install_timeout;
				$linuxcoe_state->add($linuxcoe_state_fields);


			} else {

				if (strcmp($image->type, "linuxcoe-deployment")) {
					$event->log("openqrm_linuxcoe_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-linuxcoe-appliance-hook.php", "Appliance $appliance_id/$appliance_name image is not from type linuxcoe-deployment", "", "", 0, 0, $resource->id);
				} else {
					// we have auto-installed already, if it is VM the linuxcoeresource object will care to set the boot-sequence on the VM Host to local boot
					$linuxcoeresource = new linuxcoeresource();
					$linuxcoeresource->set_boot($resource->id, 1);
					// set pxe config to local-boot
					$event->log("openqrm_linuxcoe_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-linuxcoe-appliance-hook.php", "Setting resource $resource_ip to local-boot", "", "", 0, 0, $resource->id);
					$linuxcoe_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/linuxcoe/bin/openqrm-linuxcoe-manager set_linuxcoe_client_to_local_boot -m ".$resource_mac." --openqrm-cmd-mode background";
					$openqrm_server->send_command($linuxcoe_command, NULL, true);
				}
			}
			break;


		case "stop":

			if (strcmp($image->type, "linuxcoe-deployment")) {
				$event->log("stop", $_SERVER['REQUEST_TIME'], 5, "openqrm-linuxcoe-appliance-hook.php", "Appliance $appliance_id/$appliance_name image is not from type linuxcoe-deployment", "", "", 0, 0, $resource->id);
			} else {
				$event->log("stop", $_SERVER['REQUEST_TIME'], 5, "openqrm-linuxcoe-appliance-hook.php", "Stop event for appliance ".$appliance_id."/".$appliance->name.".", "", "", 0, 0, $resource->id);
				// remove linuxcoestate-state object if existing
				$local_storage_state = new linuxcoestate();
				$local_storage_state->remove_by_resource_id($resource->id);
				// if it is VM the linuxcoeresource object will care to set the boot-sequence on the VM Host to network boot
				$linuxcoeresource = new linuxcoeresource();
				$linuxcoeresource->set_boot($resource->id, 0);

			}
			break;


	}


}



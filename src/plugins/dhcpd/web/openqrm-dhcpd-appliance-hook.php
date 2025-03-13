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

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;


function openqrm_dhcpd_appliance($cmd, $appliance_fields) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_EXEC_PORT;
	$openqrm_server = new openqrm_server();
	$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
	$event = new event();
	$appliance_id=$appliance_fields["appliance_id"];
	$appliance_name=$appliance_fields["appliance_name"];
	$resource = new resource();
	$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
	$resource_mac=$resource->mac;
	$resource_ip=$resource->ip;
	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);
	if (($resource->id == "-1") || ($resource->id == "")) {
		return;
	}

	$event->log("openqrm_dhcpd_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-dhcpd-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$resource_ip/$resource_mac", "", "", 0, 0, $resource->id);
	switch($cmd) {
		case "start":
		    $event->log("openqrm_dhcpd_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-dhcpd-appliance-hook.php", "Adding hostname ".$appliance->name." from resource ".$resource->id.".", "", "", 0, 0, $resource->id);
		    $dhcpd_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/dhcpd/bin/openqrm-dhcpd-appliance add_hostname -m ".$resource_mac." -n ".$appliance->name." --openqrm-cmd-mode background";
		    $openqrm_server->send_command($dhcpd_command);
			break;

		case "stop":
		    $event->log("openqrm_dhcpd_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-dhcpd-appliance-hook.php", "Removing hostname ".$appliance->name." from resource ".$resource->id.".", "", "", 0, 0, $resource->id);
		    $dhcpd_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/dhcpd/bin/openqrm-dhcpd-appliance remove_hostname -m ".$resource_mac." -n ".$appliance->name." --openqrm-cmd-mode background";
		    $openqrm_server->send_command($dhcpd_command);
		    break;
	}


}



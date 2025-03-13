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
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function openqrm_dns_appliance($cmd, $appliance_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$appliance_id=$appliance_fields["appliance_id"];
	$appliance_name=$appliance_fields["appliance_name"];
	$resource = new resource();
	$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
	$appliance_ip=$resource->ip;
	if (($resource->id == "-1") || ($resource->id == "")) {
		return;
	}


	$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-dns-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
	switch($cmd) {
		case "start":
			$openqrm_server = new openqrm_server();
			$openqrm_server->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dns/bin/openqrm-dns-manager start ".$appliance_id." ".$appliance_name." ".$appliance_ip." --openqrm-cmd-mode background");
			break;
		case "stop":
			$openqrm_server = new openqrm_server();
			$openqrm_server->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dns/bin/openqrm-dns-manager stop ".$appliance_id." ".$appliance_name." ".$appliance_ip." --openqrm-cmd-mode background");
			break;

	}
}




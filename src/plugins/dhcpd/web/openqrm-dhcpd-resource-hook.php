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



function openqrm_dhcpd_resource($cmd, $resource_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$resource_id=$resource_fields["resource_id"];
	$resource_ip=$resource_fields["resource_ip"];
	$resource_mac=$resource_fields["resource_mac"];
	if (isset($resource_fields["resource_subnet"])) {
		$resource_subnet=$resource_fields["resource_subnet"];
	} else {
		$resource_subnet="0.0.0.0";
	}
	$openqrm_admin_user = new user("openqrm");
	$openqrm_admin_user->set_user();
	$openqrm_server = new openqrm_server();
	$event->log("openqrm_new_resource", $_SERVER['REQUEST_TIME'], 5, "openqrm-dhcpd-resource-hook.php", "Handling $cmd event $resource_id/$resource_ip/$resource_subnet/$resource_mac", "", "", 0, 0, $resource_id);
	switch($cmd) {
		case "add":
			$openqrm_server->send_command($OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/dhcpd/bin/openqrm-dhcpd-manager add -d ".$resource_id." -m ".$resource_mac." -i ".$resource_ip." -s ".$resource_subnet." -u ".$openqrm_admin_user->name." -p ".$openqrm_admin_user->password." --openqrm-cmd-mode background");
			break;
		case "remove":
			$openqrm_server->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/dhcpd/bin/openqrm-dhcpd-manager remove -d ".$resource_id." -m ".$resource_mac." -i ".$resource_ip." -s ".$resource_subnet." -u ".$openqrm_admin_user->name." -p ".$openqrm_admin_user->password." --openqrm-cmd-mode background");
			break;

	}
}




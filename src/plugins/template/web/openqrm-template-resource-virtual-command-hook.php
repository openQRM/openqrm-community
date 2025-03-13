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
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function openqrm_template_deployment_resource_virtual_command($cmd, $resource_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;

	$resource_id = $resource_fields["resource_id"];
	$resource = new resource();
	$resource->get_instance_by_id($resource_id);
	$host_resource = new resource();
	$host_resource->get_instance_by_id($resource->vhostid);
	$event->log("openqrm_template_deployment_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "openqrm-template_deployment-resource-virtual-command-hook.php", "Handling $cmd command of resource $resource->id on host $host_resource->id", "", "", 0, 0, 0);

	switch($cmd) {
		case "reboot":
			$event->log("openqrm_template_deployment_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "openqrm-template_deployment-resource-virtual-command-hook.php", "Handling $cmd command", "", "", 0, 0, 0);
			$virtual_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/template_deployment/bin/openqrm-template_deployment-vm restart_by_mac -m ".$resource->mac." -d noop --openqrm-cmd-mode background";
			// reboot conflicts with appliance stop, commented out for now
			//$host_resource->send_command($host_resource->ip, $virtual_command);
			break;
		case "halt":
			$event->log("openqrm_template_deployment_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "openqrm-template_deployment-resource-virtual-command-hook.php", "Handling $cmd command", "", "", 0, 0, 0);
			$virtual_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/template_deployment/bin/openqrm-template_deployment-vm stop_by_mac -m ".$resource->mac." --openqrm-cmd-mode background";
			$host_resource->send_command($host_resource->ip, $virtual_command);
			break;

	}
}




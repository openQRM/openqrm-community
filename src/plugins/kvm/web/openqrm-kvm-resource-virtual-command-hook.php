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



function openqrm_kvm_resource_virtual_command($cmd, $resource_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;

	$resource_id = $resource_fields["resource_id"];
	$resource = new resource();
	$resource->get_instance_by_id($resource_id);
	$host_resource = new resource();
	$host_resource->get_instance_by_id($resource->vhostid);
	$virtualization = new virtualization();
	$virtualization->get_instance_by_id($resource->vtype);
	$openqrm_admin_user = new user("openqrm");
	$openqrm_admin_user->set_user();
	
	switch($cmd) {
		case "reboot":
			$event->log("openqrm_kvm_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-resource-virtual-command-hook.php", "Handling $cmd command", "", "", 0, 0, 0);
			if ($virtualization->type == "kvm-vm-local") {
				$virtual_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm-vm restart_by_mac -m ".$resource->mac." -u ".$openqrm_admin_user->name." -p ".$openqrm_admin_user->password." -d noop --openqrm-cmd-mode background";
				$host_resource->send_command($host_resource->ip, $virtual_command);
			}
			if($virtualization->type == "kvm-vm-net") {
				// simply add to cmd queue. do not use resource->send_command(ip, reboot) since this will re-trigger this hook
				$cmd_token = md5(uniqid(rand(), true));
				$resource_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/sbin/openqrm-exec -i ".$resource->ip." -t ".$cmd_token." -c reboot";
				shell_exec($resource_command);
			}
			if($virtualization->type == "kvm") {
				$cmd_token = md5(uniqid(rand(), true));
				$resource_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/sbin/openqrm-exec -i ".$resource->ip." -t ".$cmd_token." -c reboot";
				shell_exec($resource_command);
			}
			$resource_reboot_fields=array();
			$resource_reboot_fields["resource_state"]="transition";
			$resource_reboot_fields["resource_event"]="reboot";
			$resource->update_info($resource->id, $resource_reboot_fields);
			
			break;
		case "halt":
			$event->log("openqrm_kvm_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-resource-virtual-command-hook.php", "Handling $cmd command", "", "", 0, 0, 0);
			if ($virtualization->type == "kvm-vm-local") {
				$virtual_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm-vm stop_by_mac -m ".$resource->mac." -u ".$openqrm_admin_user->name." -p ".$openqrm_admin_user->password." --openqrm-cmd-mode background";
				$host_resource->send_command($host_resource->ip, $virtual_command);
			}
			if($virtualization->type == "kvm-vm-net") {
				// simply add to cmd queue. do not use resource->send_command(ip, reboot) since this will re-trigger this hook
				$cmd_token = md5(uniqid(rand(), true));
				$resource_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/sbin/openqrm-exec -i ".$resource->ip." -t ".$cmd_token." -c halt";
				shell_exec($resource_command);
			}
			if($virtualization->type == "kvm") {
				// simply add to cmd queue. do not use resource->send_command(ip, reboot) since this will re-trigger this hook
				$cmd_token = md5(uniqid(rand(), true));
				$resource_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/sbin/openqrm-exec -i ".$resource->ip." -t ".$cmd_token." -c halt";
				shell_exec($resource_command);
			}
			$resource_reboot_fields=array();
			$resource_reboot_fields["resource_state"]="off";
			$resource_reboot_fields["resource_event"]="reboot";
			$resource->update_info($resource->id, $resource_reboot_fields);
			break;
	}
}




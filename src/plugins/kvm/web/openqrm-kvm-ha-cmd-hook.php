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


// This file implements the virtual machine abstraction in the cloud of openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$event = new event();
global $event;

global $OPENQRM_SERVER_BASE_DIR;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $RESOURCE_INFO_TABLE;

// ---------------------------------------------------------------------------------
// general kvm cloudvm methods
// ---------------------------------------------------------------------------------


// creates a vm
function create_ha_kvm_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $vm_type) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("create_kvm_vm", $_SERVER['REQUEST_TIME'], 5, "kvm-ha-hook", "Creating KVM VM $name on Host resource $host_resource_id", "", "", 0, 0, 0);
	// start the vm on the host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	// we need to have an openQRM server object too since some of the
	// virtualization commands are sent from openQRM directly
	$openqrm = new openqrm_server();
	// send command to create vm
	$vm_create_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm-vm create -n ".$name." -y ".$vm_type." -m ".$mac." -r ".$memory." -c ".$cpu." -b local ".$additional_nic_str;
	$host_resource->send_command($host_resource->ip, $vm_create_cmd);
	$event->log("create_kvm_vm", $_SERVER['REQUEST_TIME'], 5, "kvm-ha-hook", "Running $vm_create_cmd", "", "", 0, 0, 0);
}



// fences a vm
function fence_ha_kvm_vm($host_resource_id, $mac) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	// fences the vm on its host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	$event->log("fence_kvm_vm", $_SERVER['REQUEST_TIME'], 5, "kvm-ha-hook", "Fencing KVM VM $mac from Host resource $host_resource_id", "", "", 0, 0, 0);
	// we need to have an openQRM server object too since some of the
	// virtualization commands are sent from openQRM directly
	$openqrm = new openqrm_server();
	// send command to fence the vm on the host
	$vm_fence_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm-vm fence -m ".$mac." --openqrm-cmd-mode background";
	$event->log("fence_kvm_vm", $_SERVER['REQUEST_TIME'], 5, "kvm-ha-hook", "Running $vm_fence_cmd", "", "", 0, 0, 0);
	$host_resource->send_command($host_resource->ip, $vm_fence_cmd);
}


// HA hook methods

function create_kvm_vm_local($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $origin_resource_id) {
	global $event;
	create_ha_kvm_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, "kvm-vm-local");
}

function create_kvm_vm_net($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $origin_resource_id) {
	global $event;
	create_ha_kvm_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, "kvm-vm-net");
}

function fence_kvm_vm_local($host_resource_id, $mac) {
	global $event;
	fence_ha_kvm_vm($host_resource_id, $mac);
}

function fence_kvm_vm_net($host_resource_id, $mac) {
	global $event;
	fence_ha_kvm_vm($host_resource_id, $mac);
}


// ---------------------------------------------------------------------------------



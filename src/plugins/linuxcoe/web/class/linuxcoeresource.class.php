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


// This class represents a linuxcoeresource object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class linuxcoeresource {

var $id = '';
var $resource_id = '';


//--------------------------------------------------
/**
* Constructor
*/
//--------------------------------------------------
function __construct() {
	$this->init();
}

//--------------------------------------------------
/**
* init storage environment
* @access public
*/
//--------------------------------------------------
function init() {
	global $OPENQRM_SERVER_BASE_DIR;
	$this->_event = new event();
	$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
}


// ---------------------------------------------------------------------------------
// methods to set a resource boot-sequence
// This is especially needed for KVM VMs since the boot-sequence "nc" does
// not use the local disk for boot if set by pxe. -> bug in kvm
// ---------------------------------------------------------------------------------

function set_boot($resource_id, $boot) {
	global $event;
	$event->log("set_boot", $_SERVER['REQUEST_TIME'], 5, "linuxcoeresource.class.php", "Setting boot-sequence of resource ".$resource_id." to ".$boot.".", "", "", 0, 0, 0);
	$boot_sequence = "net";
	switch($boot) {
		case '0':
			// netboot
			$boot_sequence = "net";
			break;
		case '1':
			// local boot
			$boot_sequence = "local";
			break;
	}
	$linuxcoe_resource = new resource();
	$linuxcoe_resource->get_instance_by_id($resource_id);
	// is it a vm ?
	if ($linuxcoe_resource->vhostid == $resource_id) {
		return;
	}
	$linuxcoe_resource_virtualization = new virtualization();
	$linuxcoe_resource_virtualization->get_instance_by_id($linuxcoe_resource->vtype);
	switch($linuxcoe_resource_virtualization->type) {
		case 'kvm-vm-net':
			$linuxcoe_resource_vhost = new resource();
			$linuxcoe_resource_vhost->get_instance_by_id($linuxcoe_resource->vhostid);
			$linuxcoe_resource_set_boot_commmand = $this->_base_dir."/openqrm/plugins/kvm/bin/openqrm-kvm-vm setboot -m ".$linuxcoe_resource->mac." -b ".$boot_sequence." --openqrm-cmd-mode background";
			$linuxcoe_resource_vhost->send_command($linuxcoe_resource_vhost->ip, $linuxcoe_resource_set_boot_commmand);
			$event->log("set_boot", $_SERVER['REQUEST_TIME'], 5, "linuxcoeresource.class.php", "Resource ".$resource_id." is a KVM VM on Host ".$linuxcoe_resource_vhost->id.".", "", "", 0, 0, 0);
			break;
		case 'kvm-vm-local':
			$linuxcoe_resource_vhost = new resource();
			$linuxcoe_resource_vhost->get_instance_by_id($linuxcoe_resource->vhostid);
			$linuxcoe_resource_set_boot_commmand = $this->_base_dir."/openqrm/plugins/kvm/bin/openqrm-kvm-vm setboot -m ".$linuxcoe_resource->mac." -b ".$boot_sequence." --openqrm-cmd-mode background";
			$linuxcoe_resource_vhost->send_command($linuxcoe_resource_vhost->ip, $linuxcoe_resource_set_boot_commmand);
			$event->log("set_boot", $_SERVER['REQUEST_TIME'], 5, "linuxcoeresource.class.php", "Resource ".$resource_id." is a KVM-Storage VM on Host ".$linuxcoe_resource_vhost->id.".", "", "", 0, 0, 0);
			break;
	}

}



// ---------------------------------------------------------------------------------

}


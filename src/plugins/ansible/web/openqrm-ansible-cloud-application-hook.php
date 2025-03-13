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
require_once $RootDir."/include/user.inc.php";
require_once $RootDir."/class/event.class.php";
require_once $RootDir."/class/resource.class.php";
require_once $RootDir."/class/openqrm_server.class.php";
require_once $RootDir."/include/openqrm-server-config.php";
require_once $RootDir."/plugins/ansible/class/ansible.class.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;



// cloud hook to get the available application groups
function openqrm_ansible_get_cloud_applications() {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;

	$ansible_group_list = array();
	$ansible = new ansible();
	$ansible_group_array = $ansible->get_available_playbooks();
	foreach ($ansible_group_array as $index => $ansible_app) {
		$ansible_group_list[] = "ansible/".$ansible_app;
	}
	return $ansible_group_list;
}


// cloud hook to get the supported os version by application
function openqrm_ansible_get_supported_os_version($application) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$ansible = new ansible();
	$ansible_os_array = $ansible->get_supported_os($application);
	return $ansible_os_array;
}



// cloud hook to set applications for a cloud server
function openqrm_ansible_set_cloud_applications($appliance_name, $application_array) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$ansible = new ansible();
	$ansible->set_playbooks($appliance_name, $application_array);
}


// cloud hook to remove applications from a cloud server
function openqrm_ansible_remove_cloud_applications($appliance_name) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$ansible = new ansible();
	$ansible->remove_appliance($appliance_name);
}




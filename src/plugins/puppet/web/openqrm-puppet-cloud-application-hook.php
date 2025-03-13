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
require_once $RootDir."/plugins/puppet/class/puppet.class.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;



// cloud hook to get the available application groups
function openqrm_puppet_get_cloud_applications() {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;

	$puppet_group_list = array();
	$puppet = new puppet();
	$puppet_group_array = $puppet->get_available_groups();
	foreach ($puppet_group_array as $index => $puppet_app) {
		$puppet_group_list[] = "puppet/".$puppet_app;
	}
	return $puppet_group_list;
}



// cloud hook to get the supported os version by application
function openqrm_puppet_get_supported_os_version($application) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$puppet = new puppet();
	$puppet_os_array = $puppet->get_supported_os($application);
	return $puppet_os_array;
}



// cloud hook to set applications for a cloud server
function openqrm_puppet_set_cloud_applications($appliance_name, $application_array) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$puppet = new puppet();
	$puppet->set_groups($appliance_name, $application_array);
}


// cloud hook to remove applications from a cloud server
function openqrm_puppet_remove_cloud_applications($appliance_name) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$puppet = new puppet();
	$puppet->remove_appliance($appliance_name);
}






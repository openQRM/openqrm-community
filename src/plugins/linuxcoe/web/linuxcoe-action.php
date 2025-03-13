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
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
// filter inputs
require_once $RootDir.'/class/htmlobjects/htmlobject.class.php';
require_once $RootDir.'/include/requestfilter.inc.php';
$html = new htmlobject($RootDir.'/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "linuxcoe-action", "Un-Authorized access to linuxcoe-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$linuxcoe_command = $request->get('linuxcoe_command');

// main
$event->log("$linuxcoe_command", $_SERVER['REQUEST_TIME'], 5, "linuxcoe-action", "Processing linuxcoe command $linuxcoe_command", "", "", 0, 0, 0);

switch ($linuxcoe_command) {

	case 'init':
		// create linuxcoe_state
		// -> linuxcoe_state
		// linuxcoe_id BIGINT
		// linuxcoe_resource_id BIGINT
		// linuxcoe_install_start VARCHAR(20)
		// linuxcoe_timeout BIGINT
		$create_linuxcoe_state = "create table linuxcoe_state(linuxcoe_id BIGINT, linuxcoe_resource_id BIGINT, linuxcoe_install_start VARCHAR(20), linuxcoe_timeout BIGINT)";
		$db=openqrm_get_db_connection();
		$recordSet = $db->Execute($create_linuxcoe_state);
		// -> linuxcoe_volumes
		// linuxcoe_volume_id BIGINT
		// linuxcoe_volume_name VARCHAR(50)
		// linuxcoe_volume_size VARCHAR(50)
		// linuxcoe_volume_description VARCHAR(255)
		$create_linuxcoe_volume_table = "create table linuxcoe_volumes(linuxcoe_volume_id BIGINT, linuxcoe_volume_name VARCHAR(50), linuxcoe_volume_root VARCHAR(50), linuxcoe_volume_description VARCHAR(255))";
		$db=openqrm_get_db_connection();
		$recordSet = $db->Execute($create_linuxcoe_volume_table);
		break;

	case 'uninstall':
		// remove linuxcoe_resource
		$remove_linuxcoe_state = "drop table linuxcoe_state;";
		$db=openqrm_get_db_connection();
		$recordSet = $db->Execute($remove_linuxcoe_state);
		// remove volume table
		$drop_linuxcoe_volume_table = "drop table linuxcoe_volumes";
		$db=openqrm_get_db_connection();
		$recordSet = $db->Execute($drop_linuxcoe_volume_table);
		break;



	default:
		$event->log("$linuxcoe_command", $_SERVER['REQUEST_TIME'], 3, "linuxcoe-action", "No such event command ($linuxcoe_command)", "", "", 0, 0, 0);
		break;


}







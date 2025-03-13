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
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "tmpfs-storage-action", "Un-Authorized access to tmpfs-storage-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}
// get command
$tmpfs_storage_command = $request->get('tmpfs_storage_command');

// main
$event->log("$tmpfs_storage_command", $_SERVER['REQUEST_TIME'], 5, "tmpfs-storage-action", "Processing nagios3 command $tmpfs_storage_command", "", "", 0, 0, 0);
switch ($tmpfs_storage_command) {

	case 'init':
		// this command creates the following tables
		// -> tmpfs_storage_volumes
		// tmpfs_storage_volume_id BIGINT
		// tmpfs_storage_volume_name VARCHAR(50)
		// tmpfs_storage_volume_size VARCHAR(50)
		// tmpfs_storage_volume_description VARCHAR(255)
		$create_tmpfs_storage_volume_table = "create table tmpfs_storage_volumes(tmpfs_storage_volume_id BIGINT, tmpfs_storage_volume_name VARCHAR(50), tmpfs_storage_volume_size VARCHAR(50), tmpfs_storage_volume_description VARCHAR(255))";
		$db=openqrm_get_db_connection();
		$recordSet = $db->Execute($create_tmpfs_storage_volume_table);

		$db->Close();
		break;

	case 'uninstall':
		$drop_tmpfs_storage_volume_table = "drop table tmpfs_storage_volumes";
		$db=openqrm_get_db_connection();
		$recordSet = $db->Execute($drop_tmpfs_storage_volume_table);
		$db->Close();
		break;


	default:
		$event->log("$tmpfs_storage_command", $_SERVER['REQUEST_TIME'], 3, "tmpfs-storage-action", "No such event command ($tmpfs_storage_command)", "", "", 0, 0, 0);
		break;


}


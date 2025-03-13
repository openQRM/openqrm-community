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

$network_manager_command = $request->get('network_manager_command');

global $OPENQRM_SERVER_BASE_DIR;

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// place for the storage stat files
$device_statdir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/network-manager/storage';


// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "network-manager-action", "Un-Authorized access to network-manager-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}


// main
$event->log("$network_manager_command", $_SERVER['REQUEST_TIME'], 5, "network-manager-action", "Processing network-manager command $network_manager_command", "", "", 0, 0, 0);

	switch ($network_manager_command) {

		case 'get_network_config':
			if (!file_exists($device_statdir)) {
                if (!mkdir($device_statdir) && !is_dir($device_statdir)) {
                    #throw new \RuntimeException(sprintf('Directory "%s" was not created', $device_statdir));
                }
			}
			$filename = $device_statdir."/".$_POST['filename'];
			$filedata = base64_decode($_POST['filedata']);
			echo "<h1>$filename</h1>";
			$fout = fopen($filename,"wb");
			fwrite($fout, $filedata);
			fclose($fout);
			break;


		default:
			$event->log("$network_manager_command", $_SERVER['REQUEST_TIME'], 3, "network-manager-action", "No such event command ($network_manager_command)", "", "", 0, 0, 0);
			break;


	}



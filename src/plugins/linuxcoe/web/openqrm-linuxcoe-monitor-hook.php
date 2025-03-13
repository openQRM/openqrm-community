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
require_once "$RootDir/class/image_authentication.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/event.class.php";
// special linuxcoe classes
require_once "$RootDir/plugins/linuxcoe/class/linuxcoestate.class.php";
require_once "$RootDir/plugins/linuxcoe/class/linuxcoeresource.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;



// this function is going to manage the linuxcoe installation states
function openqrm_linuxcoe_monitor() {
	global $event;
	global $RootDir;
	global $openqrm_server;
	global $OPENQRM_SERVER_BASE_DIR;

	// $event->log("openqrm_linuxcoe_monitor", $_SERVER['REQUEST_TIME'], 5, "openqrm-linuxcoe-monitor-hook.php", "Checking the linuxcoe states .....", "", "", 0, 0, 0);
	$now = $_SERVER['REQUEST_TIME'];
	$linuxcoe_state_monitor = new linuxcoestate();
	$linuxcoe_state_id_arr = $linuxcoe_state_monitor->get_all_ids();
	foreach($linuxcoe_state_id_arr as $linuxcoe_state_id_db) {
		$linuxcoe_state_id = $linuxcoe_state_id_db['linuxcoe_id'];
		$linuxcoe_state = new linuxcoestate();
		$linuxcoe_state->get_instance_by_id($linuxcoe_state_id);
		$linuxcoe_time_diff = $now - $linuxcoe_state->install_start;
		if ($linuxcoe_time_diff >= $linuxcoe_state->timeout) {
			$event->log("openqrm_linuxcoe_monitor", $_SERVER['REQUEST_TIME'], 5, "openqrm-linuxcoe-monitor-hook.php", "Linuxcoe states resource ".$linuxcoe_state->resource_id." timeout, setting to localboot.....", "", "", 0, 0, 0);
			$linuxcoeresource = new linuxcoeresource();
			$linuxcoeresource->set_boot($linuxcoe_state->resource_id, 1);
			// remove
			$linuxcoe_state->remove($linuxcoe_state->id);
		} else {
			$event->log("openqrm_linuxcoe_monitor", $_SERVER['REQUEST_TIME'], 5, "openqrm-linuxcoe-monitor-hook.php", "Linuxcoe states still waiting for ".$linuxcoe_state->resource_id." timeout to appear .....", "", "", 0, 0, 0);
		}
	}
}




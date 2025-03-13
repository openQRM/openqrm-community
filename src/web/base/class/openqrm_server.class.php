<?php

// This class represents the openQRM-server
/*
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
*/

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/event.class.php";

global $RESOURCE_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXECUTION_LAYER;
$event = new event();
global $event;

class openqrm_server {

var $id = '';


// ---------------------------------------------------------------------------------
// general server methods
// ---------------------------------------------------------------------------------

// returns the ip of the openQRM-server
function get_ip_address() {
	global $RESOURCE_INFO_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select resource_openqrmserver from $RESOURCE_INFO_TABLE where resource_id=0");
	if (!$rs)
		$event->log("get_ip_address", $_SERVER['REQUEST_TIME'], 2, "openqrm_server.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$resource_openqrmserver=$rs->fields["resource_openqrmserver"];
		$rs->MoveNext();
	}
	if (!strlen($resource_openqrmserver)) {
		$event->log("get_ip_address", $_SERVER['REQUEST_TIME'], 2, "openqrm_server.class.php", "Could not find out IP-Address of the openQRM server. Server misconfiguration!", "", "", 0, 0, 0);
	}
	return $resource_openqrmserver;
}


// function to send a command to the openQRM-server
function send_command($server_command, $command_timeout = NULL, $run_local = NULL) {
	global $OPENQRM_EXEC_PORT;
	// global $OPENQRM_SERVER_IP_ADDRESS;
	$OPENQRM_SERVER_IP_ADDRESS=$this->get_ip_address();
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_EXECUTION_LAYER;
	global $event;

	// check which execution layer to use
	switch($OPENQRM_EXECUTION_LAYER) {
		case 'dropbear':
			// generate a random token for the cmd
			$cmd_token = md5(uniqid(rand(), true));
			// custom timeout ?
			if (!is_null($command_timeout)) {
				$cmd_token .= ".".$command_timeout;
			}
			// run local ?
			$run_local_parameter = '';
			if (!is_null($run_local)) {
				$run_local_parameter = "-l true";
			}
			$final_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i $OPENQRM_SERVER_IP_ADDRESS -t $cmd_token $run_local_parameter -c \"$server_command\"";
			// $event->log("send_command", $_SERVER['REQUEST_TIME'], 5, "openqrm_server.class.php", "Running : $final_command", "", "", 0, 0, 0);
			shell_exec($final_command);
			return true;
			break;
		case 'rabbitmq':
			$event->log("send_command", $_SERVER['REQUEST_TIME'], 5, "openqrm_server.class.php", "Sending command to the rabbit queue $OPENQRM_SERVER_IP_ADDRESS", "", "", 0, 0, 0);
			require_once $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/class/rabbit.class.php';
			$rabbit = new rabbit();
			$rabbit->queue($OPENQRM_SERVER_IP_ADDRESS, $server_command);
			break;
	}

}



// ---------------------------------------------------------------------------------

}


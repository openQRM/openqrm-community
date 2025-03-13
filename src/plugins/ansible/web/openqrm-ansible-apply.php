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
require_once $RootDir."/include/user.inc.php";
require_once $RootDir."/class/image.class.php";
require_once $RootDir."/class/resource.class.php";
require_once $RootDir."/class/virtualization.class.php";
require_once $RootDir."/class/appliance.class.php";
require_once $RootDir."/class/deployment.class.php";
require_once $RootDir."/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
// filter inputs
require_once $RootDir.'/class/htmlobjects/htmlobject.class.php';
require_once $RootDir.'/include/requestfilter.inc.php';
$html = new htmlobject($RootDir.'/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;

$ansible_command = $request->get('ansible_command');
$ansible_server_id = $request->get('ansible_id');
$ansible_server_name = $request->get('ansible_name');
$ansible_server_mac = $request->get('ansible_mac');
$ansible_server_ip = $request->get('ansible_ip');

global $OPENQRM_SERVER_BASE_DIR;

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


// main
$event->log($ansible_command, $_SERVER['REQUEST_TIME'], 5, "ansible-apply", "Processing ansible command ".$ansible_command, "", "", 0, 0, 0);

	switch ($ansible_command) {

		case 'apply':
			$appliance = new appliance();
			$appliance->get_instance_by_id($ansible_server_id);
			if ($ansible_server_name == $appliance->name) {
				$resource = new resource();
				$resource->get_instance_by_id($appliance->resources);
				if (($ansible_server_mac == $resource->mac) && ($ansible_server_ip == $resource->ip)) {
					$command  = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/ansible/bin/openqrm-ansible-manager apply ".$appliance->id." ".$appliance->name." ".$resource->ip."  --openqrm-cmd-mode fork";
					$openqrm_server = new openqrm_server();
					$openqrm_server->send_command($command);
				} else {
					$event->log($ansible_command, $_SERVER['REQUEST_TIME'], 3, "ansible-apply", "Request for Ansible apply for server id ".$ansible_server_id." with wrong resource ".$resource->id, "", "", 0, 0, 0);
				}
			} else {
				$event->log($ansible_command, $_SERVER['REQUEST_TIME'], 3, "ansible-apply", "Request for Ansible apply for server id ".$ansible_server_id." with wrong name ".$ansible_server_name, "", "", 0, 0, 0);
			}
			break;

		default:
			$event->log($ansible_command, $_SERVER['REQUEST_TIME'], 3, "ansible-apply", "No such command ".$ansible_command, "", "", 0, 0, 0);
			break;


	}







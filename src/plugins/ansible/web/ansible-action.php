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
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once $RootDir."/include/openqrm-database-functions.php";
require_once $RootDir."/include/user.inc.php";
require_once $RootDir."/include/openqrm-server-config.php";
require_once $RootDir."/class/storage.class.php";
require_once $RootDir."/class/resource.class.php";
require_once $RootDir."/class/event.class.php";
require_once $RootDir."/class/authblocker.class.php";
require_once $RootDir."/class/openqrm_server.class.php";
require_once $RootDir.'/class/htmlobjects/htmlobject.class.php';
require_once $RootDir.'/include/requestfilter.inc.php';
// filter inputs
$html = new htmlobject($RootDir.'/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;
$ansible_command = $request->get('ansible_command');
$cloud_product_hook = $RootDir.'/plugins/ansible/openqrm-ansible-cloud-product-hook.php';
$cloud_selector_class = $RootDir.'/plugins/cloud/class/cloudselector.class.php';

global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;

// global event for logging
$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "ansible-action", "Un-Authorized access to ansible-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}


$event->log("$ansible_command", $_SERVER['REQUEST_TIME'], 5, "ansible-action", "Processing ansible command $ansible_command", "", "", 0, 0, 0);
switch ($ansible_command) {
		case 'init':
			if (file_exists($cloud_selector_class)) {
				if (file_exists($cloud_product_hook)) {
					require_once $cloud_product_hook;
					openqrm_ansible_cloud_product("add", NULL);
				}
			}
			break;

	case 'uninstall':
			if (file_exists($cloud_selector_class)) {
				if (file_exists($cloud_product_hook)) {
					require_once $cloud_product_hook;
					openqrm_ansible_cloud_product("remove", NULL);
				}
			}
			break;
	
	default:
		$event->log("$ansible_command", $_SERVER['REQUEST_TIME'], 3, "ansible-action", "No such ansible command ($ansible_command)", "", "", 0, 0, 0);
		break;


}


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
$TemplateDir = $_SERVER["DOCUMENT_ROOT"].'/template-portal/';
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

$template_command = $request->get('template_command');
$template_domain = $request->get('template_domain');

// special template classes
require_once "$RootDir/plugins/template/class/templateconfig.class.php";
global $TEMPLATE_USER_TABLE;
global $TEMPLATE_REQUEST_TABLE;

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "template-action", "Un-Authorized access to template-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}


// gather request parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "cr_", 3) == 0) {
		$request_fields[$key] = $value;
	}
}



// main
$event->log("$template_command", $_SERVER['REQUEST_TIME'], 5, "template-action", "Processing template command $template_command", "", "", 0, 0, 0);

	switch ($template_command) {

		case 'init':
			// this command creates the following table
			//
			// -> templateconfig
			// cc_id BIGINT
			// cc_key VARCHAR(50)
			// cc_value VARCHAR(50)

			$create_template_config = "create table template_config(cc_id BIGINT, cc_key VARCHAR(50), cc_value VARCHAR(50))";
			$db=openqrm_get_db_connection();
			$recordSet = $db->Execute($create_template_config);

			// create the default configuration
			$create_default_template_config1 = "insert into template_config(cc_id, cc_key, cc_value) values (1, 'ca_auto_sign', 'true')";
            $recordSet = $db->Execute($create_default_template_config1);

			$create_default_template_config2 = "insert into template_config(cc_id, cc_key, cc_value) values (2, 'domain_name', ?)";
			$recordSet = $db->Execute($create_default_template_config2,$template_domain);

			$db->Close();
			break;

		case 'uninstall':
			$drop_template_config = "drop table template_config";
			$db=openqrm_get_db_connection();
			$recordSet = $db->Execute($drop_template_config);
			$db->Close();
			break;


		default:
			$event->log("$template_command", $_SERVER['REQUEST_TIME'], 3, "template-action", "No such event command ($template_command)", "", "", 0, 0, 0);
			break;


	}







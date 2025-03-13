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
$PuppetDir = $_SERVER["DOCUMENT_ROOT"].'/puppet-portal/';
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

$cloud_product_hook = $RootDir.'/plugins/puppet/openqrm-puppet-cloud-product-hook.php';
$cloud_selector_class = $RootDir.'/plugins/cloud/class/cloudselector.class.php';

$html = new htmlobject($RootDir.'/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;

$puppet_command = $request->get('puppet_command');
$puppet_domain = $request->get('puppet_domain');


// special puppet classes
require_once "$RootDir/plugins/puppet/class/puppetconfig.class.php";
global $PUPPET_USER_TABLE;
global $PUPPET_REQUEST_TABLE;

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "puppet-action", "Un-Authorized access to puppet-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}


// gather request parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "cr_", 3) == 0) {
		$request_fields[$key] = $value;
	}
}



// main
$event->log("$puppet_command", $_SERVER['REQUEST_TIME'], 5, "puppet-action", "Processing puppet command $puppet_command", "", "", 0, 0, 0);

	switch ($puppet_command) {

		case 'init':
			// this command creates the following table
			//
			// -> puppetconfig
			// cc_id BIGINT
			// cc_key VARCHAR(50)
			// cc_value VARCHAR(50)

			$create_puppet_config = "create table puppet_config(cc_id BIGINT, cc_key VARCHAR(50), cc_value VARCHAR(50))";
			$db=openqrm_get_db_connection();
			$recordSet = $db->Execute($create_puppet_config);

			// create the default configuration
			$create_default_puppet_config1 = "insert into puppet_config(cc_id, cc_key, cc_value) values (1, 'ca_auto_sign', 'true')";
            $recordSet = $db->Execute($create_default_puppet_config1);

			$create_default_puppet_config2 = "insert into puppet_config(cc_id, cc_key, cc_value) values (2, 'domain_name', ?)";
			$recordSet = $db->Execute($create_default_puppet_config2,$puppet_domain);

			$db->Close();
			
			if (file_exists($cloud_product_hook)) {
				require_once $cloud_product_hook;
				openqrm_puppet_cloud_product("add", NULL);
			}
			break;

		case 'uninstall':
			$drop_puppet_config = "drop table puppet_config";
			$db=openqrm_get_db_connection();
			$recordSet = $db->Execute($drop_puppet_config);
			$db->Close();
			
			if (file_exists($cloud_product_hook)) {
				require_once $cloud_product_hook;
				openqrm_puppet_cloud_product("remove", NULL);
			}
			break;


		default:
			$event->log("$puppet_command", $_SERVER['REQUEST_TIME'], 3, "puppet-action", "No such event command ($puppet_command)", "", "", 0, 0, 0);
			break;


	}







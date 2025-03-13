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
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once $RootDir."/plugins/puppet/class/puppet.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $IMAGE_AUTHENTICATION_TABLE;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $openqrm_server;
$event = new event();
global $event;



function openqrm_puppet_cloud_product($cmd, $cloud_hook_config) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RootDir;

	$openqrm_server = new openqrm_server();

	$event->log("openqrm_puppet_cloud_product", $_SERVER['REQUEST_TIME'], 5, "openqrm-puppet-cloud-product-hook.php", "Handling ".$cmd." event", "", "", 0, 0, 0);
	switch($cmd) {
			case "add":
				$event->log("openqrm_puppet_cloud_product", $_SERVER['REQUEST_TIME'], 5, "openqrm-puppet-cloud-product-hook.php", "Handling cloud-product ".$cmd." event", "", "", 0, 0, 0);
				// create application products
				$next_sort_id = 0;
				$db=openqrm_get_db_connection();
				$puppet = new puppet();
				$puppet_group_array = $puppet->get_available_groups();
				foreach ($puppet_group_array as $index => $puppet_app) {
					$event->log("openqrm_puppet_cloud_product", $_SERVER['REQUEST_TIME'], 5, "openqrm-puppet-cloud-product-hook.php", "Adding application ".$puppet_app." as cloud-product", "", "", 0, 0, 0);
					$cloud_product_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$create_application_cloudselector_config = "insert into cloud_selector (id, type, sort_id, quantity, price, name, description, state) 
VALUES (?, 'application', ?, ?, 1, ?, ?, 1);";
					$recordSet = $db->Execute($create_application_cloudselector_config,[$cloud_product_id,$next_sort_id,'puppet/'.$puppet_app,$puppet_app,$puppet_app.' via puppet']);
					$next_sort_id++;
				}
				break;
				
			case "remove":
				$cloud_product_class = $RootDir."/plugins/cloud/class/cloudselector.class.php";
				if (file_exists($cloud_product_class)) {
					require_once $cloud_product_class;
					$cloud_selector = new cloudselector();
					$cloud_selector_id_ar = $cloud_selector->get_all_ids();
					foreach ($cloud_selector_id_ar as $key => $value) {
						$id = $value['id'];
						$cloud_selector->get_instance_by_id($id);
						$pos = strpos($cloud_selector->quantity, 'puppet/');
						if ($pos !== false) {
							$event->log("openqrm_puppet_cloud_product", $_SERVER['REQUEST_TIME'], 5, "openqrm-puppet-cloud-product-hook.php", "Removing application ".$cloud_selector->quantity." from cloud-products", "", "", 0, 0, 0);
							$cloud_selector->remove($id);
						}
					}
				}
				break;
	}
}




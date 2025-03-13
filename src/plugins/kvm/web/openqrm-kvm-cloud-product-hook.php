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
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $IMAGE_AUTHENTICATION_TABLE;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $openqrm_server;
$event = new event();
global $event;



function openqrm_kvm_cloud_product($cmd, $cloud_hook_config) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RootDir;

	$openqrm_server = new openqrm_server();

	$event->log("openqrm_kvm_cloud_product", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-cloud-product-hook.php", "Handling ".$cmd." event", "", "", 0, 0, 0);
	switch($cmd) {
			case "add":
				$event->log("openqrm_kvm_cloud_product", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-cloud-product-hook.php", "Handling cloud-product ".$cmd." event", "", "", 0, 0, 0);
				// create resource products
				$db=openqrm_get_db_connection();
				$image = new image();
				$checked_virtualization_plugins = array();
				$virtualization = new virtualization();
				$virtualization_id_ar = $virtualization->get_list();
				unset($virtualization_id_ar[0]);
				foreach ($virtualization_id_ar as $key => $value) {
					$next_sort_id = 0;
					$id = $value['value'];
					$virtualization->get_instance_by_id($id);
					$pos = strpos($virtualization->type, 'kvm-vm-');
					if ($pos !== false) {
						$event->log("openqrm_kvm_cloud_product", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-cloud-product-hook.php", "Adding ".$virtualization->type." as cloud-product", "", "", 0, 0, 0);
						$cloud_product_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
						$create_resource_cloudselector_config = "insert into cloud_selector (id, type, sort_id, quantity, price, name, description, state) VALUES (?, 'resource', ?, ?, 1, ?, ?, 1);";
						$recordSet = $db->Execute($create_resource_cloudselector_config,[$cloud_product_id,$next_sort_id,$virtualization->id,$virtualization->type,$virtualization->name]);
						$next_sort_id++;

						// add all existing images for this virtualization type to private images
						$virtualization_plugin_name = $virtualization->get_plugin_name();
						if (!in_array($virtualization_plugin_name, $checked_virtualization_plugins)) {
							$checked_virtualization_plugins[] = $virtualization_plugin_name;
							$deployment = new deployment();
							$deployment_id_ar = $deployment->get_id_by_storagetype($virtualization_plugin_name);
							foreach ($deployment_id_ar as $key => $value) {
								$did = $value['value'];
								$deployment->get_instance_by_id($did);
								$image_id_deployment_ar = $image->get_ids_by_type($deployment->type);
								foreach ($image_id_deployment_ar as $iid_ar) {
									// add to private images
									$image_id = $iid_ar['image_id'];
									$event->log("openqrm_kvm_cloud_product", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-cloud-product-hook.php", "Adding Image ".$image_id." as private image", "", "", 0, 0, 0);
									$cloud_pr_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
									$create_private_image_config = "insert into cloud_private_image (co_id, co_image_id, co_cu_id, co_clone_on_deploy, co_state) VALUES (?, ?, 0, 1, 1);";
									$recordSet = $db->Execute($create_private_image_config,[$cloud_pr_id, $image_id]);
								}
							}
						}
					}
				}
				// add host to admin resource pool
				$admin_project_id = $cloud_hook_config['cloud_admin_procect'];
				$virtualization->get_instance_by_type('kvm');
				$appliance = new appliance();
				$appliance_id_ar = $appliance->get_ids_per_virtualization($virtualization->id);
				foreach ($appliance_id_ar as $key => $value) {
					$appliance_id = $value['appliance_id'];
					$appliance->get_instance_by_id($appliance_id);
					$cloud_respool_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$create_default_resource_pool_config = "insert into cloud_respool(rp_id, rp_resource_id, rp_cg_id) values (?, ?, ?);";
					$recordSet = $db->Execute($create_default_resource_pool_config,[$cloud_respool_id, $appliance->resources, $admin_project_id]);
				}
				
				break;
			case "remove":
				$cloud_product_class = $RootDir."/plugins/cloud/class/cloudselector.class.php";
				if (file_exists($cloud_product_class)) {
					require_once $cloud_product_class;
					$cloud_selector = new cloudselector();
					$virtualization = new virtualization();
					
					$virtualization->get_instance_by_type('kvm-vm-local');
					$event->log("openqrm_kvm_cloud_product", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-cloud-product-hook.php", "Removing resource type ".$virtualization->type." from cloud-products", "", "", 0, 0, 0);
					$cloud_selector->remove_by_quantity($virtualization->id);

					$virtualization->get_instance_by_type('kvm-vm-net');
					$event->log("openqrm_kvm_cloud_product", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-cloud-product-hook.php", "Removing resource type ".$virtualization->type." from cloud-products", "", "", 0, 0, 0);
					$cloud_selector->remove_by_quantity($virtualization->id);
					
				}
				break;
	}
}




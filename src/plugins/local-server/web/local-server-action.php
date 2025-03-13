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
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
// filter inputs
require_once $RootDir.'/class/htmlobjects/htmlobject.class.php';
require_once $RootDir.'/include/requestfilter.inc.php';
$html = new htmlobject($RootDir.'/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;

global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $KERNEL_INFO_TABLE;
global $STORAGETYPE_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "local-server-action", "Un-Authorized access to lvm-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}


$local_server_command = $request->get('local_server_command');
$local_server_id = $request->get('local_server_id');
$local_server_root_device = $request->get('local_server_root_device');
$local_server_root_device_type = $request->get('local_server_root_device_type');
$local_server_kernel_version = $request->get('local_server_kernel_version');
$local_server_name = $request->get('local_server_name');

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

	$event->log("$local_server_command", $_SERVER['REQUEST_TIME'], 5, "local-server-action", "Processing local-server command $local_server_command", "", "", 0, 0, 0);
	switch ($local_server_command) {

		case 'integrate':

			// create storage server
			$storage_fields["storage_name"] = "resource$local_server_id";
			$storage_fields["storage_resource_id"] = "$local_server_id";
			$deployment = new deployment();
			$deployment->get_instance_by_type('local-server');
			$storage_fields["storage_type"] = $deployment->id;
			$storage_fields["storage_comment"] = "Local-server resource $local_server_id";
			$storage_fields["storage_capabilities"] = 'TYPE=local-server';
			$storage = new storage();
			$storage_fields["storage_id"]=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
			$storage->add($storage_fields);

			// create image
			$image_fields["image_id"]=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
			$image_fields["image_name"] = "resource$local_server_id";
			$image_fields["image_type"] = $deployment->type;
			$image_fields["image_rootdevice"] = $local_server_root_device;
			$image_fields["image_rootfstype"] = $local_server_root_device_type;
			$image_fields["image_storageid"] = $storage_fields["storage_id"];
			$image_fields["image_comment"] = "Local-server image resource $local_server_id";
			$image_fields["image_capabilities"] = 'TYPE=local-server';
			$image = new image();
			$image->add($image_fields);

			// create kernel
			$kernel_fields["kernel_id"]=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
			$kernel_fields["kernel_name"]="resource$local_server_id";
			$kernel_fields["kernel_version"]="$local_server_kernel_version";
			$kernel_fields["kernel_capabilities"]='TYPE=local-server';
			$kernel = new kernel();
			$kernel->add($kernel_fields);

			// create appliance
			$next_appliance_id=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
			$appliance_fields["appliance_id"]=$next_appliance_id;
			$appliance_fields["appliance_name"]=$local_server_name;
			$appliance_fields["appliance_kernelid"]=$kernel_fields["kernel_id"];
			$appliance_fields["appliance_imageid"]=$image_fields["image_id"];
			$appliance_fields["appliance_resources"]="$local_server_id";
			$appliance_fields["appliance_capabilities"]='TYPE=local-server';
			$appliance_fields["appliance_comment"]="Local-server appliance resource $local_server_id";
			$appliance = new appliance();
			$appliance->add($appliance_fields);
			// set start time, reset stoptime, set state
			$now=$_SERVER['REQUEST_TIME'];
			$appliance_fields["appliance_starttime"]=$now;
			$appliance_fields["appliance_stoptime"]=0;
			$appliance_fields['appliance_state']='active';
			// set resource type to physical
			$appliance_fields['appliance_virtualization']=1;
			$appliance->update($next_appliance_id, $appliance_fields);

			// set resource to localboot
			$resource = new resource();
			$resource->get_instance_by_id($local_server_id);
			$openqrm_server->send_command("openqrm_server_set_boot local $local_server_id $resource->mac 0.0.0.0");
			$resource->set_localboot($local_server_id, 1);

			// update resource fields with kernel + image
			$kernel->get_instance_by_id($kernel_fields["kernel_id"]);
			$resource_fields["resource_kernel"]=$kernel->name;
			$resource_fields["resource_kernelid"]=$kernel_fields["kernel_id"];
			$image->get_instance_by_id($image_fields["image_id"]);
			$resource_fields["resource_image"]=$image->name;
			$resource_fields["resource_imageid"]=$image_fields["image_id"];
			// set capabilites
			$resource_fields["resource_capabilities"]='TYPE=local-server';
			$resource->update_info($local_server_id, $resource_fields);

			// add + start hook
			$appliance->get_instance_by_id($next_appliance_id);
			$now=$_SERVER['REQUEST_TIME'];
			$appliance_fields = array();
			$appliance_fields['appliance_stoptime']=$now;
			$appliance_fields['appliance_state']='stopped';
			// fill in the rest of the appliance info in the array for the plugin hook
			$appliance_fields["appliance_id"]=$next_appliance_id;
			$appliance_fields["appliance_name"]=$appliance->name;
			$appliance_fields["appliance_kernelid"]=$appliance->kernelid;
			$appliance_fields["appliance_imageid"]=$appliance->imageid;
			$appliance_fields["appliance_cpunumber"]=$appliance->cpunumber;
			$appliance_fields["appliance_cpuspeed"]=$appliance->cpuspeed;
			$appliance_fields["appliance_cpumodel"]=$appliance->cpumodel;
			$appliance_fields["appliance_memtotal"]=$appliance->memtotal;
			$appliance_fields["appliance_swaptotal"]=$appliance->swaptotal;
			$appliance_fields["appliance_nics"]=$appliance->nics;
			$appliance_fields["appliance_capabilities"]=$appliance->capabilities;
			$appliance_fields["appliance_cluster"]=$appliance->cluster;
			$appliance_fields["appliance_ssi"]=$appliance->ssi;
			$appliance_fields["appliance_resources"]=$appliance->resources;
			$appliance_fields["appliance_highavailable"]=$appliance->highavailable;
			$appliance_fields["appliance_virtual"]=$appliance->virtual;
			$appliance_fields["appliance_virtualization"]=$appliance->virtualization;
			$appliance_fields["appliance_virtualization_host"]=$appliance->virtualization_host;
			$appliance_fields["appliance_comment"]=$appliance->comment;
			$appliance_fields["appliance_event"]=$appliance->event;

			$plugin = new plugin();
			$enabled_plugins = $plugin->enabled();
			foreach ($enabled_plugins as $index => $plugin_name) {
				$plugin_start_appliance_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-appliance-hook.php";
				if (file_exists($plugin_start_appliance_hook)) {
					$event->log("integrate", $_SERVER['REQUEST_TIME'], 5, "local-server-action", "Found plugin $plugin_name handling add-appliance event.", "", "", 0, 0, $appliance->resources);
					require_once "$plugin_start_appliance_hook";
					$appliance_function="openqrm_"."$plugin_name"."_appliance";
					$appliance_function=str_replace("-", "_", $appliance_function);
					// add
					$appliance_function("add", $appliance_fields);
					// start
					$appliance_fields['appliance_stoptime']='';
					$appliance_fields['appliance_starttime']=$now;
					$appliance_fields['appliance_state']='active';
					$appliance->update($next_appliance_id, $appliance_fields);
					$appliance_function("start", $appliance_fields);

				}
			}


			break;

		case 'remove':
			// remove all appliance with resource set to localserver id
			$appliance = new appliance();
			$appliance_id_list = $appliance->get_all_ids();
			foreach($appliance_id_list as $appliance_list) {
				$appliance_id = $appliance_list['appliance_id'];
				$app_resource_remove_check = new appliance();
				$app_resource_remove_check->get_instance_by_id($appliance_id);
				if ($app_resource_remove_check->resources == $local_server_id) {

					// stop + remove hooks
					$now=$_SERVER['REQUEST_TIME'];
					$appliance_fields = array();
					$appliance_fields['appliance_stoptime']=$now;
					$appliance_fields['appliance_state']='stopped';
					// fill in the rest of the appliance info in the array for the plugin hook
					$appliance_fields["appliance_id"]=$appliance_id;
					$appliance_fields["appliance_name"]=$app_resource_remove_check->name;
					$appliance_fields["appliance_kernelid"]=$app_resource_remove_check->kernelid;
					$appliance_fields["appliance_imageid"]=$app_resource_remove_check->imageid;
					$appliance_fields["appliance_cpunumber"]=$app_resource_remove_check->cpunumber;
					$appliance_fields["appliance_cpuspeed"]=$app_resource_remove_check->cpuspeed;
					$appliance_fields["appliance_cpumodel"]=$app_resource_remove_check->cpumodel;
					$appliance_fields["appliance_memtotal"]=$app_resource_remove_check->memtotal;
					$appliance_fields["appliance_swaptotal"]=$app_resource_remove_check->swaptotal;
					$appliance_fields["appliance_nics"]=$app_resource_remove_check->nics;
					$appliance_fields["appliance_capabilities"]=$app_resource_remove_check->capabilities;
					$appliance_fields["appliance_cluster"]=$app_resource_remove_check->cluster;
					$appliance_fields["appliance_ssi"]=$app_resource_remove_check->ssi;
					$appliance_fields["appliance_resources"]=$app_resource_remove_check->resources;
					$appliance_fields["appliance_highavailable"]=$app_resource_remove_check->highavailable;
					$appliance_fields["appliance_virtual"]=$app_resource_remove_check->virtual;
					$appliance_fields["appliance_virtualization"]=$app_resource_remove_check->virtualization;
					$appliance_fields["appliance_virtualization_host"]=$app_resource_remove_check->virtualization_host;
					$appliance_fields["appliance_comment"]=$app_resource_remove_check->comment;
					$appliance_fields["appliance_event"]=$app_resource_remove_check->event;

					$plugin = new plugin();
					$enabled_plugins = $plugin->enabled();
					foreach ($enabled_plugins as $index => $plugin_name) {
						$plugin_start_appliance_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-appliance-hook.php";
						if (file_exists($plugin_start_appliance_hook)) {
							$event->log("remove", $_SERVER['REQUEST_TIME'], 5, "local-server-action", "Found plugin $plugin_name handling add-appliance event.", "", "", 0, 0, $app_resource_remove_check->resources);
							require_once "$plugin_start_appliance_hook";
							$appliance_function="openqrm_"."$plugin_name"."_appliance";
							$appliance_function=str_replace("-", "_", $appliance_function);
							// stop
							$appliance_function("stop", $appliance_fields);
							// remove
							$appliance_function("remove", $appliance_fields);

						}
					}
					// remove appliance
					$appliance->remove($appliance_id);
				}
			}

			// remove kernel
			$kernel = new kernel();
			$kernel->remove_by_name("resource$local_server_id");
			// remove image
			$image = new image();
			$image->remove_by_name("resource$local_server_id");
			// remove storage serveer
			$storage = new storage();
			$storage->remove_by_name("resource$local_server_id");

			break;


		default:
			$event->log("$local_server_command", $_SERVER['REQUEST_TIME'], 3, "local-server-action", "No such local-server command ($local_server_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>

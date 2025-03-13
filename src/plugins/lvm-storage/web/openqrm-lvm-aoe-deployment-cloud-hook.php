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


// This file implements the cloud storage methods

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/cloudimage.class.php";

$event = new event();
global $event;

global $OPENQRM_SERVER_BASE_DIR;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $RESOURCE_INFO_TABLE;
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';

// ---------------------------------------------------------------------------------
// general cloudstorage methods
// ---------------------------------------------------------------------------------


// clones the volume of an image
function create_clone_lvm_aoe_deployment($cloud_image_id, $image_clone_name, $disk_size) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	global $BaseDir;
	$event->log("create_clone", $_SERVER['REQUEST_TIME'], 5, "lvm-aoe-deployment-cloud-hook", "Creating clone of image on storage", "", "", 0, 0, 0);

	// we got the cloudimage id here, get the image out of it
	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	// get image, this is already the new logical clone
	// we just need to physical snapshot it and update the rootdevice
	$image = new image();
	$image->get_instance_by_id($cloudimage->image_id);
	$image_id = $image->id;
	$image_name = $image->name;
	$image_type = $image->type;
	$image_version = $image->version;
	$image_rootdevice = $image->rootdevice;
	$image_rootfstype = $image->rootfstype;
	$image_storageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($image_storageid);
	$storage_resource_id = $storage->resource_id;
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;

	// parse the volume group info in the identifier
	$ident_separate=strpos($image_rootdevice, ":");
	$volume_group=substr($image_rootdevice, 0, $ident_separate);
	$image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
	$ident_separate2=strpos($image_rootdevice_rest, ":");
	$image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
	$root_device=substr($image_rootdevice_rest, $ident_separate2+1);
	// set default snapshot size
	if (!strlen($disk_size)) {
		$disk_size=5000;
	}
	$image_clone_cmd=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage snap -n ".$image_location_name." -v ".$volume_group." -t lvm-aoe-deployment -s ".$image_clone_name." -m ".$disk_size." --openqrm-cmd-mode background";
	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "lvm-aoe-deployment-cloud-hook", "Running : $image_clone_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_clone_cmd);
	// wait for clone
	sleep(4);
	// find the new rootdevice of the snapshot, get it via the storage-ident hook
	$rootdevice_identifier_hook = "$BaseDir/boot-service/image.lvm-aoe-deployment.php";
	// require once
	require_once "$rootdevice_identifier_hook";
	$rootdevice_identifier_arr = array();
	$rootdevice_identifier_arr = get_lvm_aoe_deployment_image_rootdevice_identifier($image->storageid);
	foreach($rootdevice_identifier_arr as $id) {
		foreach($id as $aoe_identifier_string) {
			if (strstr($aoe_identifier_string, $image_clone_name)) {
				$aoe_clone_rootdevice_tmp=strrchr($aoe_identifier_string, ":");
				$aoe_clone_rootdevice=trim(str_replace(":", "", $aoe_clone_rootdevice_tmp));
				break;
			}
		}
	}
	// update the image rootdevice parameter
	$ar_image_update = array(
		'image_rootdevice' => "$volume_group:$image_clone_name:$aoe_clone_rootdevice",
	);
	$image->update($image_id, $ar_image_update);
	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "lvm-aoe-deployment-cloud-hook", "Updating rootdevice of image $image_id / $image_name with $volume_group:$image_clone_name:$aoe_clone_rootdevice", "", "", 0, 0, 0);
}



// removes the volume of an image
function remove_lvm_aoe_deployment($cloud_image_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("remove_lvm_aoe_deployment", $_SERVER['REQUEST_TIME'], 5, "lvm-aoe-deployment-cloud-hook", "Removing image on storage", "", "", 0, 0, 0);

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	// get image
	$image = new image();
	$image->get_instance_by_id($cloudimage->image_id);
	$image_id = $image->id;
	$image_name = $image->name;
	$image_type = $image->type;
	$image_version = $image->version;
	$image_rootdevice = $image->rootdevice;
	$image_rootfstype = $image->rootfstype;
	$image_storageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($image_storageid);
	$storage_resource_id = $storage->resource_id;
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;

	// parse the volume group info in the identifier
	$ident_separate=strpos($image_rootdevice, ":");
	$volume_group=substr($image_rootdevice, 0, $ident_separate);
	$image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
	$ident_separate2=strpos($image_rootdevice_rest, ":");
	$image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
	$root_device=substr($image_rootdevice_rest, $ident_separate2+1);
	$image_remove_clone_cmd=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage remove -n ".$image_location_name." -v ".$volume_group." -t lvm-aoe-deployment --openqrm-cmd-mode background";
	$event->log("remove_lvm_aoe_deployment", $_SERVER['REQUEST_TIME'], 5, "lvm-aoe-deployment-cloud-hook", "Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_remove_clone_cmd);
}


// resizes the volume of an image
function resize_lvm_aoe_deployment($cloud_image_id, $resize_value) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("resize_lvm_aoe_deployment", $_SERVER['REQUEST_TIME'], 5, "lvm-aoe-deployment-cloud-hook", "Resize image on storage", "", "", 0, 0, 0);

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	// get image
	$image = new image();
	$image->get_instance_by_id($cloudimage->image_id);
	$image_id = $image->id;
	$image_name = $image->name;
	$image_type = $image->type;
	$image_version = $image->version;
	$image_rootdevice = $image->rootdevice;
	$image_rootfstype = $image->rootfstype;
	$image_storageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($image_storageid);
	$storage_resource_id = $storage->resource_id;
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;

	// parse the volume group info in the identifier
	$ident_separate=strpos($image_rootdevice, ":");
	$volume_group=substr($image_rootdevice, 0, $ident_separate);
	$image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
	$ident_separate2=strpos($image_rootdevice_rest, ":");
	$image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
	$root_device=substr($image_rootdevice_rest, $ident_separate2+1);
	$image_resize_cmd=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage resize -n ".$image_location_name." -v ".$volume_group." -m ".$resize_value." -t lvm-aoe-deployment --openqrm-cmd-mode background";
	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "lvm-aoe-deployment-cloud-hook", "Running : $image_resize_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_resize_cmd);
}



// creates a private copy of the volume of an image
function create_private_lvm_aoe_deployment($cloud_image_id, $private_disk, $private_image_name) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("create_private_lvm_aoe_deployment", $_SERVER['REQUEST_TIME'], 5, "lvm-aoe-deployment-cloud-hook", "Creating private image on storage", "", "", 0, 0, 0);

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	// get image
	$image = new image();
	$image->get_instance_by_id($cloudimage->image_id);
	$image_id = $image->id;
	$image_name = $image->name;
	$image_type = $image->type;
	$image_version = $image->version;
	$image_rootdevice = $image->rootdevice;
	$image_rootfstype = $image->rootfstype;
	$image_storageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($image_storageid);
	$storage_resource_id = $storage->resource_id;
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;
	// create an admin user to post when cloning has finished
	$openqrm_admin_user = new user("openqrm");
	$openqrm_admin_user->set_user();
	// parse the volume group info in the identifier
	$ident_separate=strpos($image_rootdevice, ":");
	$volume_group=substr($image_rootdevice, 0, $ident_separate);
	$image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
	$ident_separate2=strpos($image_rootdevice_rest, ":");
	$image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
	$root_device=substr($image_rootdevice_rest, $ident_separate2+1);
	$image_resize_cmd=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage clone -n ".$image_location_name." -s ".$private_image_name." -v ".$volume_group." -m ".$private_disk." -t lvm-aoe-deployment -u ".$openqrm_admin_user->name." -p ".$openqrm_admin_user->password." --openqrm-cmd-mode background";
	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "lvm-aoe-deployment-cloud-hook", "Running : $image_resize_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_resize_cmd);
	// set the storage specific image root_device parameter
	$new_rootdevice = str_replace($image_location_name, $private_image_name, $image->rootdevice);
	return $new_rootdevice;
}



// ---------------------------------------------------------------------------------



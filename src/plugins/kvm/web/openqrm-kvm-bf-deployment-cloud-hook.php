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
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
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


// ---------------------------------------------------------------------------------
// general cloudstorage methods
// ---------------------------------------------------------------------------------


// clones the volume of an image
function create_clone_kvm_bf_deployment($cloud_image_id, $image_clone_name, $disk_size) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	// we got the cloudimage id here, get the image out of it
	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("create_clone_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Creating clone ".$image_clone_name." of image ".$cloudimage->image_id." on the storage", "", "", 0, 0, 0);
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
	$imageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;
	$openqrm_admin_user = new user("openqrm");
	$openqrm_admin_user->set_user();

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($imageid);
	$storage_resource_id = $storage->resource_id;
	// get deployment type
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;
	// kvm-bf-deployment
	$image->get_instance_by_id($image_id);
	// parse the identifiers
	// origin image volume name
	$origin_volume_name=basename($image_rootdevice);
	// location of the volume (path)
	$image_location_name=dirname($image_rootdevice);
	// set default snapshot size
	if (!strlen($disk_size)) {
		$disk_size=5000;
	}
	// update the image rootdevice parameter
	$ar_image_update = array(
		'image_rootdevice' => $image_location_name."/".$image_clone_name,
	);

	// For kvm vms we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a NAS/Glusterfs backend with all volumes accessible for all hosts
	// get the vm resource
	$vm_resource = new resource();
	$vm_resource->get_instance_by_id($cloudimage->resource_id);
	// get the lxc host
	$vm_host_resource = new resource();
	$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
	// san backend ?
	if ($vm_host_resource->id != $resource->id) {
		$event->log("create_clone_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Image ".$image_id." IS NOT available on this kvm host, ".$resource->id." not equal ".$vm_host_resource->id." !! Assuming SAN Backend", "", "", 0, 0, 0);

		// update the image storage id with the vm-host-resource
		$image_deployment = new deployment();
		$image_deployment->get_instance_by_type($image_type);
		// loop over all storage id from type $image_type
		$found_image=0;
		$storage_list_by_type = new storage();
		$storage_id_list_by_type = $storage_list_by_type->get_ids_by_storage_type($image_deployment->id);
		foreach($storage_id_list_by_type as $storage_id_list) {
			$storage_id = $storage_id_list['storage_id'];
			$tstorage = new storage();
			$tstorage->get_instance_by_id($storage_id);
			if ($tstorage->resource_id == $vm_host_resource->id) {
				// re-create update array + new storage id
				$ar_image_update = array(
					'image_rootdevice' => $image_location_name."/".$image_clone_name,
					'image_storageid' => $tstorage->id,
				);
				$event->log("create_clone_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Updating Image ".$image_id." / ".$image_name." with storage id ".$tstorage->id.".", "", "", 0, 0, 0);
				$found_image=1;
				break;
			}
		}
		if ($found_image == 0) {
			$event->log("create_clone_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 2, "openqrm-kvm-bf-deployment-cloud-hook.php", "SETUP ERROR: Could not find a storage server type ".$image_type." using resource ".$vm_host_resource->id.". Please create one!", "", "", 0, 0, 0);
			$event->log("create_clone_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 2, "openqrm-kvm-bf-deployment-cloud-hook.php", "SETUP ERROR: Not cloning image ".$image_id.".", "", "", 0, 0, 0);
			return;
		}

	} else {
		$event->log("create_clone_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Image ".$image_id." IS available on this kvm host, ".$resource->id." equal ".$vm_host_resource->id.".", "", "", 0, 0, 0);
	}

	$event->log("create_clone_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Updating rootdevice of image ".$image_id." / ".$image_name." with ".$image_location_name."/".$image_clone_name, "", "", 0, 0, 0);
	$image->update($image_id, $ar_image_update);
	$image_clone_cmd=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/kvm/bin/openqrm-kvm snap -n ".$origin_volume_name." -v ".$image_location_name." -s ".$image_clone_name." -m ".$disk_size." -t ".$deployment->type." -u ".$openqrm_admin_user->name." -p ".$openqrm_admin_user->password." --openqrm-cmd-mode background";
	$event->log("create_clone_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Running : ".$image_clone_cmd, "", "", 0, 0, 0);
	$resource->send_command($vm_host_resource->ip, $image_clone_cmd);
}



// removes the volume of an image
function remove_kvm_bf_deployment($cloud_image_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("remove_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Removing image ".$cloudimage->image_id." from storage.", "", "", 0, 0, 0);
	// get image
	$image = new image();
	$image->get_instance_by_id($cloudimage->image_id);
	$image_id = $image->id;
	$image_name = $image->name;
	$image_type = $image->type;
	$image_version = $image->version;
	$image_rootdevice = $image->rootdevice;
	$image_rootfstype = $image->rootfstype;
	$imageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;
	$openqrm_admin_user = new user("openqrm");
	$openqrm_admin_user->set_user();

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($imageid);
	$storage_resource_id = $storage->resource_id;
	// get deployment type
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;
	// parse the identifiers
	// origin image volume name
	$origin_volume_name=basename($image_rootdevice);
	// location of the volume (path)
	$image_location_name=dirname($image_rootdevice);

	// For kvm vms we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a NAS/Glusterfs backend with all volumes accessible for all hosts
	//
	// Still we need to send the remove command to the storage resource since the
	// create-phase automatically adapted the image->storageid, we cannot use the vm-resource here
	// because cloudimage->resource_id will be set to -1 when the cloudapp is in paused/resize/private state
	//
	if ($cloudimage->resource_id > 0) {
		// try to get the vm resource
		$vm_resource = new resource();
		$vm_resource->get_instance_by_id($cloudimage->resource_id);
		// get the lxc host
		$vm_host_resource = new resource();
		$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
		// san backend ?
		if ($vm_host_resource->id != $resource->id) {
			$event->log("remove_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Image ".$image_id." IS NOT available on this kvm host, ".$resource->id." not equal ".$vm_host_resource->id." !! Assuming SAN Backend", "", "", 0, 0, 0);
		} else {
			$event->log("remove_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Image ".$image_id." IS available on this kvm host, ".$resource->id." equal ".$vm_host_resource->id.".", "", "", 0, 0, 0);
		}
	}
	$image_remove_clone_cmd=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/kvm/bin/openqrm-kvm remove -n ".$origin_volume_name." -v ".$image_location_name." -t ".$deployment->type." -u ".$openqrm_admin_user->name." -p ".$openqrm_admin_user->password." --openqrm-cmd-mode background";
	$event->log("remove_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Running : ".$image_remove_clone_cmd, "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_remove_clone_cmd);
}


// resizes the volume of an image
function resize_kvm_bf_deployment($cloud_image_id, $resize_value) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("resize_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Resize image ".$cloudimage->image_id." on storage.", "", "", 0, 0, 0);
	// get image
	$image = new image();
	$image->get_instance_by_id($cloudimage->image_id);
	$image_id = $image->id;
	$image_name = $image->name;
	$image_type = $image->type;
	$image_version = $image->version;
	$image_rootdevice = $image->rootdevice;
	$image_rootfstype = $image->rootfstype;
	$imageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;
	$openqrm_admin_user = new user("openqrm");
	$openqrm_admin_user->set_user();

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($imageid);
	$storage_resource_id = $storage->resource_id;
	// get deployment type
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;
	// parse the identifiers
	// origin image volume name
	$origin_volume_name=basename($image_rootdevice);
	// location of the volume (path)
	$image_location_name=dirname($image_rootdevice);

	// For kvm vms we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a NAS/Glusterfs backend with all volumes accessible for all hosts
	//
	// Still we need to send the remove command to the storage resource since the
	// create-phase automatically adapted the image->storageid, we cannot use the vm-resource here
	// because cloudimage->resource_id will be set to -1 when the cloudapp is in paused/resize/private state
	//
	if ($cloudimage->resource_id > 0) {
		// try to get the vm resource
		$vm_resource = new resource();
		$vm_resource->get_instance_by_id($cloudimage->resource_id);
		// get the lxc host
		$vm_host_resource = new resource();
		$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
		// san backend ?
		if ($vm_host_resource->id != $resource->id) {
			$event->log("resize_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Image ".$image_id." IS NOT available on this kvm host, ".$resource->id." not equal ".$vm_host_resource->id." !! Assuming SAN Backend", "", "", 0, 0, 0);
		} else {
			$event->log("resize_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Image ".$image_id." IS available on this kvm host, ".$resource->id." equal ".$vm_host_resource->id, "", "", 0, 0, 0);
		}
	}

	$image_resize_cmd=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/kvm/bin/openqrm-kvm resize -n ".$origin_volume_name." -v ".$image_location_name." -m ".$resize_value." -t ".$deployment->type." -u ".$openqrm_admin_user->name." -p ".$openqrm_admin_user->password." --openqrm-cmd-mode background";
	$event->log("resize_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Running : ".$image_resize_cmd, "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_resize_cmd);
}



// creates a private copy of the volume of an image
function create_private_kvm_bf_deployment($cloud_image_id, $private_disk, $private_image_name) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("create_private_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Creating private image ".$cloudimage->image_id." on storage.", "", "", 0, 0, 0);
	// get image
	$image = new image();
	$image->get_instance_by_id($cloudimage->image_id);
	$image_id = $image->id;
	$image_name = $image->name;
	$image_type = $image->type;
	$image_version = $image->version;
	$image_rootdevice = $image->rootdevice;
	$image_rootfstype = $image->rootfstype;
	$imageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($imageid);
	$storage_resource_id = $storage->resource_id;
	// get deployment type
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;
	// create an admin user to post when cloning has finished
	$openqrm_admin_user = new user("openqrm");
	$openqrm_admin_user->set_user();
	// parse the identifiers
	// origin image volume name
	$origin_volume_name=basename($image_rootdevice);
	// location of the volume (path)
	$image_location_name=dirname($image_rootdevice);

	// For kvm vms we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a NAS/Glusterfs backend with all volumes accessible for all hosts
	//
	// Still we need to send the remove command to the storage resource since the
	// create-phase automatically adapted the image->storageid, we cannot use the vm-resource here
	// because cloudimage->resource_id will be set to -1 when the cloudapp is in paused/resize/private state
	//
	if ($cloudimage->resource_id > 0) {
		// try to get the vm resource
		$vm_resource = new resource();
		$vm_resource->get_instance_by_id($cloudimage->resource_id);
		// get the lxc host
		$vm_host_resource = new resource();
		$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
		// san backend ?
		if ($vm_host_resource->id != $resource->id) {
			$event->log("create_private_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Image ".$image_id." IS NOT available on this kvm host, ".$resource->id." not equal ".$vm_host_resource->id." !! Assuming SAN Backend", "", "", 0, 0, 0);
		} else {
			$event->log("create_private_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Image ".$image_id." IS available on this kvm host, ".$resource->id." equal ".$vm_host_resource->id.".", "", "", 0, 0, 0);
		}
	}

	$image_resize_cmd=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/kvm/bin/openqrm-kvm clone -n ".$origin_volume_name." -s ".$private_image_name." -v ".$image_location_name." -m ".$private_disk." -u ".$openqrm_admin_user->name." -p ".$openqrm_admin_user->password." -t ".$deployment->type." --openqrm-cmd-mode background";
	$event->log("create_private_kvm_bf_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-kvm-bf-deployment-cloud-hook.php", "Running : $image_resize_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_resize_cmd);
	// set the storage specific image root_device parameter
	$new_rootdevice = str_replace($origin_volume_name, $private_image_name, $image->rootdevice);
	return $new_rootdevice;
}



// ---------------------------------------------------------------------------------



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


// ---------------------------------------------------------------------------------
// general cloudstorage methods
// ---------------------------------------------------------------------------------


// clones the volume of an image
function create_clone_lvm_nfs_deployment($cloud_image_id, $image_clone_name, $disk_size) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("create_clone", $_SERVER['REQUEST_TIME'], 5, "lvm-nfs-deployment-cloud-hook", "Creating clone of image on storage", "", "", 0, 0, 0);

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

	$full_vol_name=$image_rootdevice;
	$vol_dir=dirname($full_vol_name);
	$vol=str_replace("/", "", $vol_dir);
	$image_location_name=basename($full_vol_name);
	// set default snapshot size
	if (!strlen($disk_size)) {
		$disk_size=5000;
	}
	// update the image rootdevice parameter
	$image->get_instance_by_id($image_id);
	$ar_image_update = array(
		'image_rootdevice' => "/$vol/$image_clone_name",
	);
	$image->update($image_id, $ar_image_update);
	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "lvm-nfs-deployment-cloud-hook", "Updating rootdevice of image $image_id / $image_name with /$vol/$image_clone_name", "", "", 0, 0, 0);
	$image_clone_cmd=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage snap -n ".$image_location_name." -v ".$vol." -t lvm-nfs-deployment -s ".$image_clone_name." -m ".$disk_size." --openqrm-cmd-mode background";
	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "lvm-nfs-deployment-cloud-hook", "Running : $image_clone_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_clone_cmd);

}



// removes the volume of an image
function remove_lvm_nfs_deployment($cloud_image_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("remove_lvm_nfs_deployment", $_SERVER['REQUEST_TIME'], 5, "lvm-nfs-deployment-cloud-hook", "Removing image on storage", "", "", 0, 0, 0);

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

	$full_vol_name=$image_rootdevice;
	$vol_dir=dirname($full_vol_name);
	$vol=str_replace("/", "", $vol_dir);
	$image_location_name=basename($full_vol_name);
	$image_remove_clone_cmd=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage remove -n ".$image_location_name." -v ".$vol." -t lvm-nfs-deployment --openqrm-cmd-mode background";
	$event->log("remove_lvm_nfs_deployment", $_SERVER['REQUEST_TIME'], 5, "lvm-nfs-deployment-cloud-hook", "Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_remove_clone_cmd);

}


// resizes the volume of an image
function resize_lvm_nfs_deployment($cloud_image_id, $resize_value) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("resize_lvm_nfs_deployment", $_SERVER['REQUEST_TIME'], 5, "lvm-nfs-deployment-cloud-hook", "Resize image on storage", "", "", 0, 0, 0);

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

	$full_vol_name=$image_rootdevice;
	$vol_dir=dirname($full_vol_name);
	$vol=str_replace("/", "", $vol_dir);
	$image_location_name=basename($full_vol_name);
	$image_resize_cmd=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage resize -n ".$image_location_name." -v ".$vol." -m ".$resize_value." -t lvm-nfs-deployment --openqrm-cmd-mode background";
	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "lvm-nfs-deployment-cloud-hook", "Running : $image_resize_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_resize_cmd);

}



// creates a private copy of the volume of an image
function create_private_lvm_nfs_deployment($cloud_image_id, $private_disk, $private_image_name) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("create_private_lvm_nfs_deployment", $_SERVER['REQUEST_TIME'], 5, "lvm-nfs-deployment-cloud-hook", "Creating private image on storage", "", "", 0, 0, 0);

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

	$full_vol_name=$image_rootdevice;
	$vol_dir=dirname($full_vol_name);
	$vol=str_replace("/", "", $vol_dir);
	$image_location_name=basename($full_vol_name);
	$image_resize_cmd=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage clone -n ".$image_location_name." -s ".$private_image_name." -v ".$vol." -m ".$private_disk." -t lvm-nfs-deployment -u ".$openqrm_admin_user->name." -p ".$openqrm_admin_user->password." --openqrm-cmd-mode background";
	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "lvm-nfs-deployment-cloud-hook", "Running : $image_resize_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_resize_cmd);
	// set the storage specific image root_device parameter
	$new_rootdevice = "/".$vol."/".$private_image_name;
	return $new_rootdevice;

}



// ---------------------------------------------------------------------------------



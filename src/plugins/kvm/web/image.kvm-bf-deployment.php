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
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $OPENQRM_SERVER_BASE_DIR;

// global event for logging
$event = new event();
global $event;

function kvm_bf_deployment_wait_for_identfile($sfile) {
	$refresh_delay=1;
	$refresh_loop_max=20;
	$refresh_loop=0;
	while (!file_exists($sfile)) {
		sleep($refresh_delay);
		$refresh_loop++;
		flush();
		if ($refresh_loop > $refresh_loop_max)  {
			return false;
		}
	}
	return true;
}


function get_kvm_bf_deployment_image_rootdevice_identifier($kvm_bf_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_ADMIN;
	global $event;

	// place for the storage stat files
	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/kvm/storage';
	$rootdevice_identifier_array = array();
	$storage = new storage();
	$storage->get_instance_by_id($kvm_bf_id);
	// get deployment type
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);
	// get storage resource
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_resource_id = $storage_resource->id;
	$ident_file = $StorageDir."/".$storage_resource_id.".lv.kvm-bf-deployment.ident";
	if (file_exists($ident_file)) {
		unlink($ident_file);
	}
	// send command
	$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/kvm/bin/openqrm-kvm post_identifier -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password." -t ".$deployment->type;
	$storage_resource->send_command($storage_resource->ip, $resource_command);
	if (!kvm_bf_deployment_wait_for_identfile($ident_file)) {
		$event->log("get_image_rootdevice_identifier", $_SERVER['REQUEST_TIME'], 2, "image.kvm-bf-deployment", "Timeout while requesting image identifier from storage id $storage->id", "", "", 0, 0, 0);
		return;
	}
	$fcontent = file($ident_file);
	foreach($fcontent as $lun_info) {
		$ident_params = explode(":", $lun_info);
		$timage_name = trim($ident_params[0]);
		$troot_device = trim($ident_params[1]);
		$rootdevice_identifier_array[] = array("value" => "$troot_device", "label" => "$timage_name");
	}
	return $rootdevice_identifier_array;
}


function get_kvm_bf_deployment_image_default_rootfs() {
	return "local";
}

function get_kvm_bf_deployment_rootfs_transfer_methods() {
	return false;
}

function get_kvm_bf_deployment_rootfs_set_password_method() {
	return true;
}

function get_kvm_bf_deployment_is_network_deployment() {
	return false;
}

function get_kvm_bf_deployment_local_deployment_enabled() {
	return true;
}

function get_kvm_bf_deployment_is_resizable() {
	return false;
}



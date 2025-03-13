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


function custom_iscsi_deployment_wait_for_identfile($sfile) {
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


function get_custom_iscsi_deployment_image_rootdevice_identifier($custom_iscsi_storage_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_ADMIN;
	global $event;

	// place for the storage stat files
	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/lvm-storage/storage';
	$rootdevice_identifier_array = array();
	return $rootdevice_identifier_array;
}

function get_custom_iscsi_deployment_image_default_rootfs() {
	return "ext3";
}


function get_custom_iscsi_deployment_rootfs_transfer_methods() {
	return true;
}

function get_custom_iscsi_deployment_rootfs_set_password_method() {
	return true;
}

function get_custom_iscsi_deployment_is_network_deployment() {
	return true;
}

function get_custom_iscsi_deployment_local_deployment_enabled() {
	return false;
}



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

function wait_for_linuxcoe_netboot_product_list($sfile) {
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

function get_linuxcoe_deployment_templates($local_storage_storage_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_ADMIN;
	global $event;
	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/linuxcoe/profiles';
	$local_deployment_tepmplates_identifier_array = array();
	if ($handle = opendir($StorageDir)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				$openqrm_profile_info = "";
				$openqrm_info_file = $StorageDir."/".$file."/openqrm.info";
				if (file_exists($openqrm_info_file)) {
					$openqrm_profile_info = file_get_contents($openqrm_info_file);
				} else {
					$openqrm_profile_info = file_get_contents($file);
				}
				$openqrm_install_parameter = "linuxcoe-deployment:0:".$file;
				$local_deployment_tepmplates_identifier_array[] = array("value" => $openqrm_install_parameter, "label" => $openqrm_profile_info);
			}
		}
		closedir($handle);
	}
	return $local_deployment_tepmplates_identifier_array;
}


function get_linuxcoe_deployment_methods() {
	// select template
	$linuxcoe_deployment_methods_array = array("value" => "linuxcoe-deployment", "label" => "Automatic Linux Installation (LinuxCOE)");
	return $linuxcoe_deployment_methods_array;
}


function get_linuxcoe_deployment_additional_parameters() {
	// possible 4 additional inputs for addtional parameters
	$local_deployment_additional_parameters[] = '';
//	$local_deployment_additional_parameters[] = array("value" => "", "label" => 'Product-Key');
//	$local_deployment_additional_parameters[] = array("value" => "", "label" => 'Key2');
//	$local_deployment_additional_parameters[] = array("value" => "", "label" => 'Key3');
//	$local_deployment_additional_parameters[] = array("value" => "", "label" => 'Key4');
	return $local_deployment_additional_parameters;
}


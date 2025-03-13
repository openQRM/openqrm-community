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


// This class represents a puppet user in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/folder.class.php";
require_once "$RootDir/class/file.class.php";
require_once "$RootDir/plugins/puppet/class/puppetconfig.class.php";

$event = new event();
global $event;

$puppet_group_dir = "$RootDir/plugins/puppet/puppet/manifests/groups";
global $puppet_group_dir;
$puppet_appliance_dir = "$RootDir/plugins/puppet/puppet/manifests/appliances";
global $puppet_appliance_dir;
$PUPPET_CONFIG_TABLE="puppet_config";
global $PUPPET_CONFIG_TABLE;

class puppet {

		function __construct() {
			$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
			$this->group_dir = $RootDir.'/plugins/puppet/puppet/manifests/groups';
			$this->appliance_dir = $RootDir.'/plugins/puppet/puppet/manifests/appliances';
			$this->event = new event();
		}

// ---------------------------------------------------------------------------------
// general puppetconfig methods
// ---------------------------------------------------------------------------------


function get_available_groups() {
	$app_dir = new folder();
	$app_dir->getFolderContent($this->group_dir);
	$puppet_groups = array();
	$puppet_groups = $app_dir->files;
	$puppet_group_array = array();
	foreach($puppet_groups as $puppet) {
		$puppet_group = str_replace(".pp", "", $puppet->name);
		$puppet_group_array[] .= $puppet_group;
	}
	sort($puppet_group_array);
	return $puppet_group_array;
}



function get_group_info($group_name) {

	$filename = $this->group_dir."/$group_name.pp";
	if (file_exists($filename)) {
		if (!$handle = fopen($filename, 'r')) {
			$this->event->log("get_group_info", $_SERVER['REQUEST_TIME'], 2, "puppet.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
			exit;
		}
		while (!feof($handle)) {
			$info = fgets($handle, 4096);
			if (strstr($info, "#")) {
				$info = str_replace("#", "", $info);
				fclose($handle);
				return $info;
			}
		}
	}
}



function get_supported_os($group_name) {
	$filename = $this->group_dir."/$group_name.pp";
	if (file_exists($filename)) {
		if (!$handle = fopen($filename, 'r')) {
			$this->event->log("get_supported_os", $_SERVER['REQUEST_TIME'], 2, "puppet.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
			exit;
		}
		while (!feof($handle)) {
			$os_version = fgets($handle, 4096);
			if (strstr($os_version, "# os:")) {
				$os_version = str_replace("# os:", "", $os_version);
				fclose($handle);
				$os_version_arr = explode(",", $os_version);
				return $os_version_arr;
			}
		}
	}
}


function get_domain() {
	$puppetconfig = new puppetconfig();
	$puppet_domain = $puppetconfig->get_value(2);  // 2 is the domain-name
	return $puppet_domain;
}



function set_groups($appliance_name, $puppet_group_array) {
	$puppet_domain = $this->get_domain();
	$filename = $this->appliance_dir."/$appliance_name.$puppet_domain.pp";
	if (!$handle = fopen($filename, 'w+')) {
		$this->event->log("set_groups", $_SERVER['REQUEST_TIME'], 2, "puppet.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
		exit;
	}
	// header
	fwrite($handle, "\nnode '$appliance_name.$puppet_domain' {\n");
	// body with groups
	foreach($puppet_group_array as $puppet_group) {
		$puppet_include = "     include $puppet_group\n";
		fwrite($handle, $puppet_include);
	}
	// base
	fwrite($handle, "}\n\n");
	fclose($handle);
}


function get_groups($appliance_name) {
	$puppet_group_array = array();
	$puppet_domain = $this->get_domain();
	$filename = $this->appliance_dir."/$appliance_name.$puppet_domain.pp";

	if (file_exists($filename)) {
		if (!$handle = fopen($filename, 'r')) {
			$this->event->log("get_groups", $_SERVER['REQUEST_TIME'], 2, "puppet.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
			exit;
		}
		while (!feof($handle)) {
			$buffer = fgets($handle, 4096);
			if (strstr($buffer, "include")) {
				$buffer = str_replace("include", "", $buffer);
				$buffer = trim($buffer);
				$puppet_group_array[] .= $buffer;
			}
		}
		fclose($handle);
	}


	return $puppet_group_array;
}

function remove_appliance($appliance_name) {
	$puppet_domain = $this->get_domain();
	$filename = $this->appliance_dir."/$appliance_name.$puppet_domain.pp";
	if (file_exists($filename)) {
		unlink($filename);
	}
}


// ---------------------------------------------------------------------------------

}


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


// This class represents a template user in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/folder.class.php";
require_once "$RootDir/class/file.class.php";
require_once "$RootDir/plugins/template/class/templateconfig.class.php";

$event = new event();
global $event;

$template_group_dir = "$RootDir/plugins/template/template/manifests/groups";
global $template_group_dir;
$template_appliance_dir = "$RootDir/plugins/template/template/manifests/appliances";
global $template_appliance_dir;
$TEMPLATE_CONFIG_TABLE="template_config";
global $TEMPLATE_CONFIG_TABLE;

class template {

		function __construct() {
			$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
			$this->group_dir = $RootDir.'/plugins/template/template/manifests/groups';
			$this->appliance_dir = $RootDir.'/plugins/template/template/manifests/appliances';
			$this->event = new event();
		}

// ---------------------------------------------------------------------------------
// general templateconfig methods
// ---------------------------------------------------------------------------------


function get_available_groups() {
	$template_group_array = array();
	return $template_group_array;
}



function get_group_info($group_name) {

	$filename = $this->group_dir."/$group_name.pp";
	if (file_exists($filename)) {
		if (!$handle = fopen($filename, 'r')) {
			$this->event->log("get_group_info", $_SERVER['REQUEST_TIME'], 2, "template.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
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



function get_domain() {
	$templateconfig = new templateconfig();
	$template_domain = $templateconfig->get_value(2);  // 2 is the domain-name
	return $template_domain;
}



function set_groups($appliance_name, $template_group_array) {
	$template_domain = $this->get_domain();
	$filename = $this->appliance_dir."/$appliance_name.$template_domain.pp";
	if (!$handle = fopen($filename, 'w+')) {
		$this->event->log("set_groups", $_SERVER['REQUEST_TIME'], 2, "template.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
		exit;
	}
	// header
	fwrite($handle, "\nnode '$appliance_name.$template_domain' {\n");
	// body with groups
	foreach($template_group_array as $template_group) {
		$template_include = "     include $template_group\n";
		fwrite($handle, $template_include);
	}
	// base
	fwrite($handle, "}\n\n");
	fclose($handle);
}


function get_groups($appliance_name) {
	$template_group_array = array();
	$template_domain = $this->get_domain();
	$filename = $this->appliance_dir."/$appliance_name.$template_domain.pp";

	if (file_exists($filename)) {
		if (!$handle = fopen($filename, 'r')) {
			$this->event->log("get_groups", $_SERVER['REQUEST_TIME'], 2, "template.class.php", "Cannot open file ($filename)", "", "", 0, 0, 0);
			exit;
		}
		while (!feof($handle)) {
			$buffer = fgets($handle, 4096);
			if (strstr($buffer, "include")) {
				$buffer = str_replace("include", "", $buffer);
				$buffer = trim($buffer);
				$template_group_array[] .= $buffer;
			}
		}
		fclose($handle);
	}


	return $template_group_array;
}

function remove_appliance($appliance_name) {
	$template_domain = $this->get_domain();
	$filename = $this->appliance_dir."/$appliance_name.$template_domain.pp";
	if (file_exists($filename)) {
		unlink($filename);
	}
}


// ---------------------------------------------------------------------------------

}


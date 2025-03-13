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
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$TEMPLATE_CONFIG_TABLE="template_config";
global $TEMPLATE_CONFIG_TABLE;
$event = new event();
global $event;

class templateconfig {

var $id = '';
var $key = '';
var $value = '';

	function __construct() {
		$this->template_config_table = "template_config";
		$this->event = new event();
	}


// ---------------------------------------------------------------------------------
// methods to create an instance of a templateconfig object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $name) {
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$templateconfig_array = $db->Execute("select * from ".$this->template_config_table." where cc_id=$id");
	} else if ("$name" != "") {
		$templateconfig_array = $db->Execute("select * from ".$this->template_config_table." where cc_key='$name'");
	} else {
		$this->event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "templateconfig.class.php", "Could not create instance of templateconfig without data", "", "", 0, 0, 0);
		return;
	}

	foreach ($templateconfig_array as $index => $templateconfig) {
		$this->id = $templateconfig["cc_id"];
		$this->key = $templateconfig["cc_key"];
		$this->value = $templateconfig["cc_value"];
	}
	return $this;
}

// returns an appliance from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an appliance from the db selected by key
function get_instance_by_key($name) {
	$this->get_instance("", $name);
	return $this;
}


// ---------------------------------------------------------------------------------
// general templateconfig methods
// ---------------------------------------------------------------------------------




// checks if given templateconfig id is free in the db
function is_id_free($templateconfig_id) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select cc_id from ".$this->template_config_table." where cc_id=$templateconfig_id");
	if (!$rs)
		$this->event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "templateconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds templateconfig to the database
function add($templateconfig_fields) {
	if (!is_array($templateconfig_fields)) {
		$this->event->log("add", $_SERVER['REQUEST_TIME'], 2, "templateconfig.class.php", "coulduser_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($this->template_config_table, $templateconfig_fields, 'INSERT');
	if (! $result) {
		$this->event->log("add", $_SERVER['REQUEST_TIME'], 2, "templateconfig.class.php", "Failed adding new templateconfig to database", "", "", 0, 0, 0);
	}
}



// removes templateconfig from the database
function remove($templateconfig_id) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from ".$this->template_config_table." where cc_id=$templateconfig_id");
}

// removes templateconfig from the database by key
function remove_by_name($templateconfig_key) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from ".$this->template_config_table." where cc_key='$templateconfig_key'");
}


// returns templateconfig value by templateconfig_id
function get_value($templateconfig_id) {
	$db=openqrm_get_db_connection();
	$templateconfig_set = $db->Execute("select cc_value from ".$this->template_config_table." where cc_id=$templateconfig_id");
	if (!$templateconfig_set) {
		$this->event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "templateconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		if (!$templateconfig_set->EOF) {
			return $templateconfig_set->fields["cc_value"];
		} else {
			return "";
		}
	}
}


// sets a  templateconfig value by templateconfig_id
function set_value($templateconfig_id, $templateconfig_value) {
	$db=openqrm_get_db_connection();
	$templateconfig_set = $db->Execute("update ? set cc_value=? where cc_id=?",[$this->template_config_table,$templateconfig_value,$templateconfig_id]);
	if (!$templateconfig_set) {
		$this->event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "templateconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}


// returns the number of templateconfigs for an templateconfig type
function get_count() {
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(cc_id) as num from ?",$this->template_config_table);
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all templateconfig names
function get_list() {
	$query = "select cc_id, cc_value from ".$this->template_config_table;
	$templateconfig_name_array = array();
	$templateconfig_name_array = openqrm_db_get_result_double ($query);
	return $templateconfig_name_array;
}


// returns a list of all templateconfig ids
function get_all_ids() {
	$templateconfig_list = array();
	$query = "select cc_id from ".$this->template_config_table;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$this->event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "templateconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$templateconfig_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $templateconfig_list;

}




// displays the templateconfig-overview
function display_overview($offset, $limit, $sort, $order) {
	$db=openqrm_get_db_connection();
	$recordSet = $db->SelectLimit("select * from ".$this->template_config_table." order by $sort $order", $limit, $offset);
	$templateconfig_array = array();
	if (!$recordSet) {
		$this->event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "templateconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($templateconfig_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $templateconfig_array;
}


}


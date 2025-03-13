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
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$PUPPET_CONFIG_TABLE="puppet_config";
global $PUPPET_CONFIG_TABLE;
$event = new event();
global $event;

class puppetconfig {

var $id = '';
var $key = '';
var $value = '';

	function __construct() {
		$this->puppet_config_table = "puppet_config";
		$this->event = new event();
	}


// ---------------------------------------------------------------------------------
// methods to create an instance of a puppetconfig object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $name) {
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$puppetconfig_array = $db->Execute("select * from ".$this->puppet_config_table." where cc_id=$id");
	} else if ("$name" != "") {
		$puppetconfig_array = $db->Execute("select * from ".$this->puppet_config_table." where cc_key='$name'");
	} else {
		$this->event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", "Could not create instance of puppetconfig without data", "", "", 0, 0, 0);
		return;
	}

	foreach ($puppetconfig_array as $index => $puppetconfig) {
		$this->id = $puppetconfig["cc_id"];
		$this->key = $puppetconfig["cc_key"];
		$this->value = $puppetconfig["cc_value"];
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
// general puppetconfig methods
// ---------------------------------------------------------------------------------




// checks if given puppetconfig id is free in the db
function is_id_free($puppetconfig_id) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select cc_id from ".$this->puppet_config_table." where cc_id=$puppetconfig_id");
	if (!$rs)
		$this->event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds puppetconfig to the database
function add($puppetconfig_fields) {
	if (!is_array($puppetconfig_fields)) {
		$this->event->log("add", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", "coulduser_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($this->puppet_config_table, $puppetconfig_fields, 'INSERT');
	if (! $result) {
		$this->event->log("add", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", "Failed adding new puppetconfig to database", "", "", 0, 0, 0);
	}
}



// removes puppetconfig from the database
function remove($puppetconfig_id) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from ? where cc_id=?",[$this->puppet_config_table,$puppetconfig_id]);
}

// removes puppetconfig from the database by key
function remove_by_name($puppetconfig_key) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from ? where cc_key=?",[$this->puppet_config_table,$puppetconfig_key]);
}


// returns puppetconfig value by puppetconfig_id
function get_value($puppetconfig_id) {
	$db=openqrm_get_db_connection();
	$puppetconfig_set = $db->Execute("select cc_value from ? where cc_id=?",[$this->puppet_config_table,$puppetconfig_id]);
	if (!$puppetconfig_set) {
		$this->event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		if (!$puppetconfig_set->EOF) {
			return $puppetconfig_set->fields["cc_value"];
		} else {
			return "";
		}
	}
}


// sets a  puppetconfig value by puppetconfig_id
function set_value($puppetconfig_id, $puppetconfig_value) {
	$db=openqrm_get_db_connection();
	$puppetconfig_set = $db->Execute("update ? set cc_value=? where cc_id=?",[$this->puppet_config_table,$puppetconfig_value,$puppetconfig_id]);
	if (!$puppetconfig_set) {
		$this->event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	}
}


// returns the number of puppetconfigs for an puppetconfig type
function get_count() {
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(cc_id) as num from ?",$this->puppet_config_table);
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all puppetconfig names
function get_list() {
	$query = "select cc_id, cc_value from ".$this->puppet_config_table;
	$puppetconfig_name_array = array();
	$puppetconfig_name_array = openqrm_db_get_result_double ($query);
	return $puppetconfig_name_array;
}


// returns a list of all puppetconfig ids
function get_all_ids() {
	$puppetconfig_list = array();
	$query = "select cc_id from ".$this->puppet_config_table;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$this->event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$puppetconfig_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $puppetconfig_list;

}




// displays the puppetconfig-overview
function display_overview($offset, $limit, $sort, $order) {
	$db=openqrm_get_db_connection();
	$recordSet = $db->SelectLimit("select * from ".$this->puppet_config_table." order by $sort $order", $limit, $offset);
	$puppetconfig_array = array();
	if (!$recordSet) {
		$this->event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "puppetconfig.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($puppetconfig_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $puppetconfig_array;
}


}


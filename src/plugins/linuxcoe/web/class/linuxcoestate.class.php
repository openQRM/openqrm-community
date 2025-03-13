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


// This class represents a linuxcoestate object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class linuxcoestate {

var $id = '';
var $resource_id = '';
var $install_start = '';
var $timeout = '';

//--------------------------------------------------
/**
* Constructor
*/
//--------------------------------------------------
function __construct() {
	$this->init();
}

//--------------------------------------------------
/**
* init storage environment
* @access public
*/
//--------------------------------------------------
function init() {
	global $OPENQRM_SERVER_BASE_DIR;
	$this->_event = new event();
	$this->_db_table = "linuxcoe_state";
	$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
}





// ---------------------------------------------------------------------------------
// methods to create an instance of a linuxcoestate object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $resource_id) {
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$linuxcoestate_array = $db->Execute("select * from ".$this->_db_table." where linuxcoe_id=".$id);
	} else if ("$resource_id" != "") {
		$linuxcoestate_array = $db->Execute("select * from ".$this->_db_table." where linuxcoe_resource_id=".$resource_id);
	} else {
		$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "linuxcoestate.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		return;
	}
	foreach ($linuxcoestate_array as $index => $linuxcoestate) {
		$this->id = $linuxcoestate["linuxcoe_id"];
		$this->resource_id = $linuxcoestate["linuxcoe_resource_id"];
		$this->install_start = $linuxcoestate["linuxcoe_install_start"];
		$this->timeout = $linuxcoestate["linuxcoe_timeout"];
	}
	return $this;
}

// returns an linuxcoestate from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an linuxcoestate from the db selected by the resource_id
function get_instance_by_resource_id($resource_id) {
	$this->get_instance("", $resource_id);
	return $this;
}


// ---------------------------------------------------------------------------------
// general linuxcoestate methods
// ---------------------------------------------------------------------------------




// checks if given linuxcoestate id is free in the db
function is_id_free($linuxcoestate_id) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select linuxcoe_id from ".$this->_db_table." where linuxcoe_id=".$linuxcoestate_id);
	if (!$rs)
		$this->_event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "linuxcoestate.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds linuxcoestate to the database
function add($linuxcoestate_fields) {
	if (!is_array($linuxcoestate_fields)) {
		$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "linuxcoestate.class.php", "linuxcoestate_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($this->_db_table, $linuxcoestate_fields, 'INSERT');
	if (! $result) {
		$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "linuxcoestate.class.php", "Failed adding new linuxcoestate to database", "", "", 0, 0, 0);
	}
}



// removes linuxcoestate from the database
function remove($linuxcoestate_id) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from ? where linuxcoe_id=?",[$this->_db_table,$linuxcoestate_id]);
}


// removes linuxcoestate from the database by resource id
function remove_by_resource_id($linuxcoestate_resource_id) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from ? where linuxcoe_resource_id=?",[$this->_db_table,$linuxcoestate_resource_id]);
}



// returns the number of linuxcoestates for an linuxcoestate type
function get_count() {
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(linuxcoe_id) as num from ?",$this->_db_table);
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}




// returns a list of all linuxcoestate ids
function get_all_ids() {
	$linuxcoestate_list = array();
	$query = "select linuxcoe_id from ?";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query,$this->_db_table);
	if (!$rs)
		$this->_event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "linuxcoestate.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$linuxcoestate_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $linuxcoestate_list;

}





// ---------------------------------------------------------------------------------

}


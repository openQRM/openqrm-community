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

// This class represents boot-image (kernel) 
// A Kernel can be used to deploy an (server-)image (image.class)
// to a resource (resource.class) via an appliance (appliance.class)

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/event.class.php";

global $KERNEL_INFO_TABLE;


class kernel {

var $id = '';
var $name = '';
var $version = '';
var $comment = '';
var $capabilities = '';


// ---------------------------------------------------------------------------------
// methods to create an instance of a kernel object filled from the db
// ---------------------------------------------------------------------------------

//--------------------------------------------------
/**
* Constructor
*/
//--------------------------------------------------
function __construct() {
	global $KERNEL_INFO_TABLE;
	$this->__event = new event();
}


// returns a kernel from the db selected by id or name
function get_instance($id, $name) {
	global $KERNEL_INFO_TABLE;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$kernel_array = $db->Execute("select * from $KERNEL_INFO_TABLE where kernel_id=$id");
	} else if ("$name" != "") {
		$kernel_array = $db->Execute("select * from $KERNEL_INFO_TABLE where kernel_name='$name'");
	} else {
		$this->__event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", "Could not create instance of kernel without data", "", "", 0, 0, 0);
		foreach(debug_backtrace() as $key => $msg) {
			syslog(LOG_ERR, $msg['function'].'() '.basename($msg['file']).':'.$msg['line']);
		}
		return;
	}
	foreach ($kernel_array as $index => $kernel) {
		$this->id = $kernel["kernel_id"];
		$this->name = $kernel["kernel_name"];
		$this->version = $kernel["kernel_version"];
		$this->capabilities = $kernel["kernel_capabilities"];
		$this->comment = $kernel["kernel_comment"];
	}
	return $this;
}

// returns a kernel from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns a kernel from the db selected by iname
function get_instance_by_name($name) {
	$this->get_instance("", $name);
	return $this;
}




// ---------------------------------------------------------------------------------
// general kernel methods
// ---------------------------------------------------------------------------------


// checks if given kernel id is free in the db
function is_id_free($kernel_id) {
	global $KERNEL_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select kernel_id from $KERNEL_INFO_TABLE where kernel_id=$kernel_id");
	if (!$rs)
		$this->__event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds kernel to the database
function add($kernel_fields) {
	global $KERNEL_INFO_TABLE;
	if (!is_array($kernel_fields)) {
		$this->__event->log("add", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", "Kernel_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($KERNEL_INFO_TABLE, $kernel_fields, 'INSERT');
	if (! $result) {
		$this->__event->log("add", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", "Failed adding new kernel to database", "", "", 0, 0, 0);
	}
}

// updates kernel in the database
function update($kernel_id, $kernel_fields) {
	global $KERNEL_INFO_TABLE;
	if ($kernel_id < 0 || ! is_array($kernel_fields)) {
		$this->__event->log("update", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", "Unable to update kernel $kernel_id", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	unset($kernel_fields["kernel_id"]);
	$result = $db->AutoExecute($KERNEL_INFO_TABLE, $kernel_fields, 'UPDATE', "kernel_id = $kernel_id");
	if (! $result) {
		$this->__event->log("update", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", "Failed updating kernel $kernel_id", "", "", 0, 0, 0);
	}
}

// removes kernel from the database
function remove($kernel_id) {
	// do not remove the openqrm + default kernel
	if (($kernel_id == 0) || ($kernel_id == 1))  {
		return;
	}
	global $KERNEL_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $KERNEL_INFO_TABLE where kernel_id=$kernel_id");
}

// removes kernel from the database by name
function remove_by_name($kernel_name) {
	// do not remove the idle + openqrm image
	if (($kernel_name == "openqrm") || ($kernel_name == "default"))  {
		return;
	}
	global $KERNEL_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $KERNEL_INFO_TABLE where kernel_name='$kernel_name'");
}


// returns kernel_name by kernel_id
function get_name($kernel_id) {
	global $KERNEL_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$kernel_set = $db->Execute("select kernel_name from $KERNEL_INFO_TABLE where kernel_id=$kernel_id");
	if (!$kernel_set) {
		$this->__event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		if (!$kernel_set->EOF) {
			return $kernel_set->fields["kernel_name"];
		}
	}
}



// returns the number of available kernels
function get_count() {
	global $KERNEL_INFO_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(kernel_id) as num from $KERNEL_INFO_TABLE");
	if (!$rs) {
		$this->__event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}




// returns a list of all kernel names
function get_list() {
	global $KERNEL_INFO_TABLE;
	$query = "select kernel_id, kernel_name from $KERNEL_INFO_TABLE";
	
	return openqrm_db_get_result_double ($query);
	
}



// displays the kernel-overview
function display_overview($offset, $limit, $sort, $order) {
	global $KERNEL_INFO_TABLE;
	$db=openqrm_get_db_connection();
	$recordSet = $db->SelectLimit("select * from $KERNEL_INFO_TABLE where kernel_id > 0 order by $sort $order", $limit, $offset);
	$kernel_array = array();
	if (!$recordSet) {
		$this->__event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "kernel.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($kernel_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $kernel_array;
}







// ---------------------------------------------------------------------------------

}


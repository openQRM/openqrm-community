<?php

// This class represents a virtualization type
/*
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
*/

$RootDir = $_SERVER["DOCUMENT_ROOT"] . '/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/event.class.php";

global $VIRTUALIZATION_INFO_TABLE;
$event = new event();
global $event;

class virtualization
{
	
	var $id = '';
	var $name = '';
	var $type = '';
	var $mapping = '';


// ---------------------------------------------------------------------------------
// methods to create an instance of an virtualization object filled from the db
// ---------------------------------------------------------------------------------

// returns an virtualization from the db selected by id, type or name
	function get_instance($id, $name, $type)
	{
		global $VIRTUALIZATION_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		if ("$id" != "") {
			$virtualization_array = $db->Execute("select * from $VIRTUALIZATION_INFO_TABLE where virtualization_id=$id");
		} else if ("$name" != "") {
			$virtualization_array = $db->Execute("select * from $VIRTUALIZATION_INFO_TABLE where virtualization_name='$name'");
		} else if ("$type" != "") {
			$virtualization_array = $db->Execute("select * from $VIRTUALIZATION_INFO_TABLE where virtualization_type='$type'");
		} else {
			$error = '';
			foreach (debug_backtrace() as $key => $msg) {
				if ($key === 1) {
					$error .= '( ' . basename($msg['file']) . ' ' . $msg['line'] . ' )';
				}
				syslog(LOG_ERR, $msg['function'] . '() ' . basename($msg['file']) . ':' . $msg['line']);
			}
			$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "virtualization.class.php", "Could not create instance of virtualization without data - <br>" . $error, "", "", 0, 0, 0);
			return;
		}
		foreach ($virtualization_array as $index => $virtualization) {
			$this->id = $virtualization["virtualization_id"];
			$this->name = $virtualization["virtualization_name"];
			$this->type = $virtualization["virtualization_type"];
			$this->mapping = $virtualization["virtualization_mapping"];
		}
		return $this;
	}

// returns an virtualization from the db selected by id
	function get_instance_by_id($id)
	{
		$this->get_instance($id, "", "");
		return $this;
	}

// returns an virtualization from the db selected by name
	function get_instance_by_name($name)
	{
		$this->get_instance("", $name, "");
		return $this;
	}

// returns an virtualization from the db selected by type
	function get_instance_by_type($type)
	{
		$this->get_instance("", "", $type);
		return $this;
	}

// ---------------------------------------------------------------------------------
// general virtualization methods
// ---------------------------------------------------------------------------------


// checks if given virtualization id is free in the db
	function is_id_free($virtualization_id)
	{
		global $VIRTUALIZATION_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select virtualization_id from $VIRTUALIZATION_INFO_TABLE where virtualization_id=$virtualization_id");
		if (!$rs)
			$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "virtualization.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			if ($rs->EOF) {
				return true;
			} else {
				return false;
			}
	}


// adds virtualization to the database
	function add($virtualization_fields)
	{
		global $VIRTUALIZATION_INFO_TABLE;
		global $event;
		if (!is_array($virtualization_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "virtualization.class.php", "Deployment_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db = openqrm_get_db_connection();
		$result = $db->AutoExecute($VIRTUALIZATION_INFO_TABLE, $virtualization_fields, 'INSERT');
		if (!$result) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "virtualization.class.php", "Failed adding new virtualization to database", "", "", 0, 0, 0);
		}
	}


// removes virtualization from the database
	function remove($virtualization_id)
	{
		global $VIRTUALIZATION_INFO_TABLE;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from $VIRTUALIZATION_INFO_TABLE where virtualization_id=$virtualization_id");
	}

// removes virtualization from the database by virtualization_type
	function remove_by_type($type)
	{
		global $VIRTUALIZATION_INFO_TABLE;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from $VIRTUALIZATION_INFO_TABLE where virtualization_type='$type'");
	}


// returns a list of all virtualization names
	function get_list()
	{
		global $VIRTUALIZATION_INFO_TABLE;
		$query = "select virtualization_id, virtualization_name from $VIRTUALIZATION_INFO_TABLE";
		
		return openqrm_db_get_result_double($query);
		
	}


// returns the virtualization plugin name
	function get_plugin_name()
	{
		$virtualization_plugin_name = str_replace("-vm-local", "", $this->type);
		$virtualization_plugin_name = str_replace("-vm-net", "", $virtualization_plugin_name);
		$virtualization_plugin_name = str_replace("-vm", "", $virtualization_plugin_name);
		return $virtualization_plugin_name;
	}


// ---------------------------------------------------------------------------------

}


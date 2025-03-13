<?php

// This class represents a global lock type
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

$event = new event();
global $event;

class lock
{
	
	var $id = '';
	var $time = '';
	var $section = '';
	var $resource_id = '';
	var $token = '';
	var $description = '';
	
	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 */
	//--------------------------------------------
	function __construct()
	{
		$this->db_name = "lock_info";
		$this->lock_timeout = 200000;
		// 100000 = 10 secs
	}
	
	
	// ---------------------------------------------------------------------------------
	// methods to create an instance of an lock object filled from the db
	// ---------------------------------------------------------------------------------
	
	// returns an lock from the db selected by id, resource_id or section
	function get_instance($id, $resource_id, $section)
	{
		global $event;
		$db = openqrm_get_db_connection();
		if ($id != "") {
			$lock_array = $db->Execute("select * from " . $this->db_name . " where lock_id=$id");
		} else if ($resource_id != "") {
			$lock_array = $db->Execute("select * from " . $this->db_name . " where lock_resource_id=$resource_id");
		} else if ($section != "") {
			$lock_array = $db->Execute("select * from " . $this->db_name . " where lock_section='$section'");
		} else {
			$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "lock.class.php", "Could not create instance of lock without data", "", "", 0, 0, 0);
			foreach (debug_backtrace() as $key => $msg) {
				syslog(LOG_ERR, $msg['function'] . '() ' . basename($msg['file']) . ':' . $msg['line']);
			}
			return;
		}
		foreach ($lock_array as $index => $lock) {
			$this->id = $lock["lock_id"];
			$this->time = $lock["lock_time"];
			$this->section = $lock["lock_section"];
			$this->resource_id = $lock["lock_resource_id"];
			$this->token = $lock["lock_token"];
			$this->description = $lock["lock_description"];
		}
		return $this;
	}
	
	// returns an lock from the db selected by id
	function get_instance_by_id($id)
	{
		$this->get_instance($id, "", "");
		return $this;
	}
	
	// returns an lock from the db selected by resource_id
	function get_instance_by_resource_id($resource_id)
	{
		$this->get_instance("", $resource_id, "");
		return $this;
	}
	
	// returns an lock from the db selected by section
	function get_instance_by_section($section)
	{
		$this->get_instance("", "", $section);
		return $this;
	}
	
	// ---------------------------------------------------------------------------------
	// general lock methods
	// ---------------------------------------------------------------------------------
	
	
	// checks if given lock id is free in the db
	function is_id_free($lock_id)
	{
		global $event;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select lock_id from " . $this->db_name . " where lock_id=$lock_id");
		if (!$rs)
			$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "lock.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			if ($rs->EOF) {
				return true;
			} else {
				return false;
			}
	}
	
	
	// adds lock to the database
	function add($lock_fields)
	{
		global $event;
		if (!is_array($lock_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "lock.class.php", "array fields not well defined", "", "", 0, 0, 0);
			return;
		}
		$section = $lock_fields['lock_section'];
		if (!strlen($section)) {
			return;
		}
		$lock_array = array();
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select lock_time from " . $this->db_name . " where lock_section='$section'");
		if (!$rs) {
			$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "lock.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
			return;
		} else {
			if (!$rs->EOF) {
				array_push($lock_array, $rs->fields);
			}
		}
		$new_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
		// timeout
		$current_lock_time = "";
		if (isset($lock_array[0]['lock_time'])) {
			$current_lock_time = $lock_array[0]['lock_time'];
		}
		if (strlen($current_lock_time)) {
			if ($new_id > ($current_lock_time + $this->lock_timeout)) {
				$event->log("add", $_SERVER['REQUEST_TIME'], 2, "lock.class.php", "Timeout for lock " . $current_lock_time . "! Force remove lock.", "", "", 0, 0, 0);
				$this->remove_by_section($section);
			} else {
				$event->log("add", $_SERVER['REQUEST_TIME'], 2, "lock.class.php", "Still awaiting imeout for lock " . $current_lock_time . "! Not adding new lock " . $new_id, "", "", 0, 0, 0);
				return;
			}
		}
		$lock_fields['lock_id'] = $new_id;
		$lock_fields['lock_time'] = $new_id;
		$result = $db->AutoExecute($this->db_name, $lock_fields, 'INSERT');
		if (!$result) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "lock.class.php", "Failed adding new lock to database", "", "", 0, 0, 0);
			return;
		}
		return $new_id;
	}
	
	
	// removes lock from the database
	function remove($lock_id)
	{
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from " . $this->db_name . " where lock_id=$lock_id");
	}
	
	// removes lock from the database by section
	function remove_by_section($section)
	{
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from " . $this->db_name . " where lock_section='$section'");
	}


// ---------------------------------------------------------------------------------

}


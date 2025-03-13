<?php
/**
 * @package openQRM
 */
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
require_once "$RootDir/class/plugin.class.php";


/**
 * This class represents an event in the openQRM engine
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 * @author M. Rechenburg, A. Kuballa
 * @version 1.1 added documentation
 * @copyright Copyright 2011, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */
class event
{
	
	/**
	 * event id
	 * @access protected
	 * @var int
	 */
	var $id = '';
	/**
	 * event name
	 * @access protected
	 * @var string
	 */
	var $name = '';
	/**
	 * event date
	 * @access protected
	 * @var date
	 */
	var $time = '';
	/**
	 * event priority
	 * @access protected
	 * @var int
	 */
	var $priority = '';
	/**
	 * event source
	 * @access protected
	 * @var string
	 */
	var $source = '';
	/**
	 * event description
	 * @access protected
	 * @var string
	 */
	var $description = '';
	/**
	 * event comment
	 * @access protected
	 * @var string
	 */
	var $comment = '';
	/**
	 * event description
	 * @access protected
	 * @var string
	 */
	var $capabilities = '';
	/**
	 * event status (acknowledged etc.)
	 * @access protected
	 * @var string
	 */
	var $status = '';
	/**
	 * event image id
	 * @access protected
	 * @var int
	 */
	var $image_id = '';
	/**
	 * event resource id
	 * @access protected
	 * @var int
	 */
	var $resource_id = '';
	
	/**
	 * name of database table
	 * @access protected
	 * @var string
	 */
	var $_db_table;
	
	/** thanks for the patch by tvs
	 * Time of running cron.daily
	 */
	var $crondaily_time = '943939500';
	
	//--------------------------------------------------
	/**
	 * Constructor
	 */
	//--------------------------------------------------
	function __construct()
	{
		$this->init();
	}
	
	//--------------------------------------------------
	/**
	 * init event environment
	 * @access public
	 */
	//--------------------------------------------------
	function init()
	{
		global $EVENT_INFO_TABLE;
		// priorities :
		if (defined('LOG_EMERG') == false) {
			define("LOG_EMERG", 0);
		}
		if (defined('LOG_ALERT') == false) {
			define("LOG_ALERT", 1);
		}
		if (defined('LOG_CRIT') == false) {
			define("LOG_CRIT", 2);
		}
		if (defined('LOG_ERR') == false) {
			define("LOG_ERR", 3);
		}
		if (defined('LOG_WARNING') == false) {
			define("LOG_WARNING", 4);
		}
		if (defined('LOG_NOTICE') == false) {
			define("LOG_NOTICE", 5);
		}
		if (defined('LOG_INFO') == false) {
			define("LOG_INFO", 6);
		}
		if (defined('LOG_DEBUG') == false) {
			define("LOG_DEBUG", 7);
		}
		$this->_db_table = $EVENT_INFO_TABLE;
		$this->RootDir = $_SERVER["DOCUMENT_ROOT"] . '/openqrm/base/';
	}
	
	//--------------------------------------------------
	/**
	 * get an instance of an event object from db
	 * @access public
	 * @param int $id
	 * @param string $name
	 * @return object
	 */
	//--------------------------------------------------
	function get_instance($id, $name)
	{
		$db = openqrm_get_db_connection();
		if ("$id" != "") {
			$event_array = $db->Execute("select * from $this->_db_table where event_id=$id");
		} else if ("$name" != "") {
			$event_array = $db->Execute("select * from $this->_db_table where event_name='$name'");
		} else {
			$this->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "event.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
			array_walk(debug_backtrace(), create_function('$a,$b', 'syslog(LOG_ERR, "{$a[\'function\']}()(".basename($a[\'file\']).":{$a[\'line\']}); ");'));
			return;
		}
		foreach ($event_array as $index => $event) {
			$this->id = $event["event_id"];
			$this->name = $event["event_name"];
			$this->time = $event["event_time"];
			$this->priority = $event["event_priority"];
			$this->source = $event["event_source"];
			$this->description = $event["event_description"];
			$this->comment = $event["event_comment"];
			$this->capabilities = $event["event_capabilities"];
			$this->status = $event["event_status"];
			$this->image_id = $event["event_image_id"];
			$this->resource_id = $event["event_resource_id"];
		}
		return $this;
	}
	
	//--------------------------------------------------
	/**
	 * get an event object by id
	 * @access public
	 * @param int $id
	 * @return object
	 */
	//--------------------------------------------------
	function get_instance_by_id($id)
	{
		$this->get_instance($id, "");
		return $this;
	}
	
	//--------------------------------------------------
	/**
	 * get an event object by name
	 * @access public
	 * @param string $name
	 * @return object
	 */
	//--------------------------------------------------
	function get_instance_by_name($name)
	{
		$this->get_instance("", $name);
		return $this;
	}
	
	//--------------------------------------------------
	/**
	 * add a new event
	 * @access public
	 * @param array $event_fields
	 */
	//--------------------------------------------------
	function add($event_fields)
	{
		if (!is_array($event_fields)) {
			$this->log("add", $_SERVER['REQUEST_TIME'], 2, "event.class.php", "Event_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db = openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $event_fields, 'INSERT');
		// patch by tvs
		if (!$result) {
			if ((strncmp(strftime("%X", $this->crondaily_time), strftime("%X", $event_fields["event_time"]), 5)) != 0) {
				// try again
				sleep(1);
				$result = $db->AutoExecute($this->_db_table, $event_fields, 'INSERT');
			}
		}
	}
	
	//--------------------------------------------------
	/**
	 * update an event
	 * <code>
	 * $fields = array();
	 * $fields['event_name'] = 'somename';
	 * $fields['event_time'] = time();
	 * $fields['event_priority'] = 5;
	 * $fields['event_source'] = 'kernel-action';
	 * $fields['event_comment'] = 'some comment';
	 * $fields['event_description'] = 'some description';
	 * $fields['event_capabilities'] = 'sometext';
	 * $fields['event_status'] = 1;
	 * $fields['event_image_id'] = 1;
	 * $fields['event_resource_id'] = 1;
	 * $event = new event();
	 * $event->update(1, $fields);
	 * </code>
	 * @access public
	 * @param int $event_id
	 * @param array $event_fields
	 * @return bool
	 */
	//--------------------------------------------------e
	function update($event_id, $event_fields)
	{
		if ($event_id < 0 || !is_array($event_fields)) {
			$this->log("update", $_SERVER['REQUEST_TIME'], 2, "event.class.php", "Unable to update event $event_id", "", "", 0, 0, 0);
			return 1;
		}
		$db = openqrm_get_db_connection();
		unset($event_fields["event_id"]);
		$result = $db->AutoExecute($this->_db_table, $event_fields, 'UPDATE', "event_id = $event_id");
		if (!$result) {
			$this->log("update", $_SERVER['REQUEST_TIME'], 2, "event.class.php", "Failed updating event $event_id", "", "", 0, 0, 0);
		}
	}
	
	//--------------------------------------------------
	/**
	 * add a new event to db and syslog
	 * @access public
	 * @param string $name
	 * @param date $time unixtimestamp
	 * @param int $priority
	 * @param string $source
	 * @param string $description
	 * @param string $comment
	 * @param string $capabilities
	 * @param int $status
	 * @param int $image_id
	 * @param int $resource_id
	 */
	//--------------------------------------------------
	function log($name, $time, $priority, $source, $description, $comment, $capabilities, $status, $image_id, $resource_id)
	{
		$db = openqrm_get_db_connection();
		// if the priority is == 10 then this is a finished event from a long term action event
		// we have to set this event to prio 5 == notice
		if ($priority == 10) {
			$rs = $db->Execute("select event_id from $this->_db_table where event_priority=9 and event_source='$source' and event_name='$name' order by event_id DESC");
			if (!$rs)
			{
				if(strlen($db->ErrorMsg()))
				{
					$this->log("log", $_SERVER['REQUEST_TIME'], 2, "event.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
				}
			}
			else
				if (!$rs->EOF) {
					// found origin active event, udpate to prio 5
					$event_fields = array();
					while (!$rs->EOF) {
						$event_fields["event_priority"] = 5;
						$this->update($rs->fields["event_id"], $event_fields);
						$rs->MoveNext();
					}
				}
			// set prio to normal event
			$priority = 5;
		}
		
		// check if log already exists, if yes, just update the date
		$rs = $db->Execute("select event_id from $this->_db_table where event_priority=$priority and event_description='$description' and event_source='$source' and event_name='$name' order by event_id DESC");
		if (!$rs){
			if(strlen($db->ErrorMsg()))
			{
				$this->log("log", $_SERVER['REQUEST_TIME'], 2, "event.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
			}
		}
		else
			if ($rs->EOF) {
				// log does not yet exists, add it
				// $new_event_id=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$new_event_id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$event_fields = array();
				$event_fields["event_id"] = $new_event_id;
				$event_fields["event_name"] = "$name";
				$event_fields["event_time"] = "$time";
				$event_fields["event_priority"] = $priority;
				$event_fields["event_source"] = "$source";
				$event_fields["event_description"] = substr($description, 0, 255);
				$event_fields["event_comment"] = substr($comment, 0, 255);
				$event_fields["event_capabilities"] = "$capabilities";
				$event_fields["event_status"] = $status;
				$event_fields["event_image_id"] = $image_id;
				$event_fields["event_resource_id"] = $resource_id;
				$this->add($event_fields);
				// event hook
				$this->trigger_event_hook('add', $new_event_id);
				
			} else {
				// log already exists, just update the date
				$event_fields = array();
				while (!$rs->EOF) {
					$event_fields["event_id"] = $rs->fields["event_id"];
					$rs->MoveNext();
				}
				$event_id = $event_fields["event_id"];
				$event_fields["event_time"] = "$time";
				$this->update($event_id, $event_fields);
				
				// event hook
				//$this->trigger_event_hook('update', $event_id);
			}
		
		// add to syslog
		$syslog_str = "openQRM $source: ($name) $description";
		$syslog_prio = "LOG_ERR";
		switch ($priority) {
			case 0:
				$syslog_prio = LOG_WARNING;
				break;
			case 1:
				$syslog_prio = LOG_WARNING;
				break;
			case 2:
				$syslog_prio = LOG_WARNING;
				break;
			case 3:
				$syslog_prio = LOG_WARNING;
				break;
			case 4:
				$syslog_prio = LOG_WARNING;
				break;
			case 5:
				$syslog_prio = LOG_NOTICE;
				break;
			case 6:
				$syslog_prio = LOG_INFO;
				break;
			case 7:
				$syslog_prio = LOG_DEBUG;
				break;
			case 8:
				$syslog_prio = LOG_NOTICE;
				break;
			// long-term action started
			case 9:
				$syslog_prio = LOG_NOTICE;
				break;
			// long term action finished
			case 10:
				$syslog_prio = LOG_NOTICE;
				break;
		}
		syslog($syslog_prio, $syslog_str);
	}
	
	//--------------------------------------------------
	/**
	 * remove an event by id
	 * @access public
	 * @param int $event_id
	 */
	//--------------------------------------------------
	function remove($event_id)
	{
		$this->trigger_event_hook('remove', $event_id);
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where event_id=$event_id");
	}
	
	//--------------------------------------------------
	/**
	 * remove an event by name
	 * @access public
	 * @param int $event_name
	 */
	//--------------------------------------------------
	function remove_by_name($event_name)
	{
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where event_name='$event_name'");
	}
	
	
	//--------------------------------------------------
	/**
	 * remove an event by description
	 * @access public
	 * @param int $event_name
	 */
	//--------------------------------------------------
	function remove_by_description($event_description)
	{
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where event_description like '%" . $event_description . "%';");
	}
	
	
	// resolves error event from the database by resource-id
	function resolve_by_resource($event_name, $resource_id)
	{
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where event_resource_id=$resource_id and event_priority<3 and event_name='$event_name'");
	}
	
	
	// returns event_name by event_id
	function get_name($event_id)
	{
		$db = openqrm_get_db_connection();
		$event_set = $db->Execute("select event_name from $this->_db_table where event_id=$event_id");
		if (!$event_set) {
			$this->log("get_name", $_SERVER['REQUEST_TIME'], 2, "event.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$event_set->EOF) {
				return $event_set->fields["event_name"];
			}
		}
	}
	
	//--------------------------------------------------
	/**
	 * get number of events
	 * @access public
	 * @return int
	 */
	//--------------------------------------------------
	function get_count($mode = null)
	{
		$count = 0;
		switch ($mode) {
			case '':
			case 'all':
				$sql = "select count(event_id) as num from " . $this->_db_table;
				break;
			case 'error':
				$sql = "select count(event_id) as num from " . $this->_db_table . " where event_priority < 4 and event_status <> 1";
				break;
			case 'acknowledge':
				$sql = "select count(event_id) as num from " . $this->_db_table . " where event_status = 1";
				break;
			case 'warning':
				$sql = "select count(event_id) as num from " . $this->_db_table . " where event_priority = 4 and event_status <> 1";
				break;
			case 'active':
				$sql = "select count(event_id) as num from " . $this->_db_table . " where event_priority = 9 and event_status <> 1";
				break;
		}
		$db = openqrm_get_db_connection();
		$rs = $db->Execute($sql);
		if (!$rs) {
			$this->log("get_count", $_SERVER['REQUEST_TIME'], 2, "event.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}
	
	
	//--------------------------------------------------
	/**
	 * get an array of all event names
	 * <code>
	 * $event = new event();
	 * $arr = $event->get_list();
	 * // $arr[0]['value']
	 * // $arr[0]['label']
	 * </code>
	 * @access public
	 * @return array
	 */
	//--------------------------------------------------
	function get_list()
	{
		$query = "select event_id, event_name from $this->_db_table";
		return openqrm_db_get_result_double($query);
	}
	
	
	//--------------------------------------------------
	/**
	 * get an array of events
	 * @access public
	 * @param int $offset
	 * @param int $limit
	 * @param string $sort
	 * @param enum $order [ASC/DESC]
	 * @return array
	 */
	//--------------------------------------------------
	function display_overview($offset, $limit, $sort, $order, $mode = null)
	{
		switch ($mode) {
			case '':
			case 'all':
				$sql = "select * from " . $this->_db_table . " order by " . $sort . " " . $order;
				break;
			case 'error':
				$sql = "select * from " . $this->_db_table . " where event_priority < 4 and event_status <> 1 order by " . $sort . " " . $order;
				break;
			case 'acknowledge':
				$sql = "select * from " . $this->_db_table . " where event_status = 1 order by " . $sort . " " . $order;
				break;
			case 'warning':
				$sql = "select * from " . $this->_db_table . " where event_priority = 4 and event_status <> 1 order by " . $sort . " " . $order;
				break;
			case 'active':
				$sql = "select * from " . $this->_db_table . " where event_priority = 9 and event_status <> 1 order by " . $sort . " " . $order;
				break;
		}
		$db = openqrm_get_db_connection();
		$recordSet = $db->SelectLimit($sql, $limit, $offset);
		$event_array = array();
		if (!$recordSet) {
			$this->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "event.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($event_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $event_array;
	}
	
	
	function trigger_event_hook($action, $id)
	{
		// add event hook for plugins
		$plugin = new plugin();
		$enabled_plugins = $plugin->enabled();
		foreach ($enabled_plugins as $index => $plugin_name) {
			$plugin_event_hook = $this->RootDir . "/plugins/" . $plugin_name . "/openqrm-" . $plugin_name . "-event-hook.php";
			if (file_exists($plugin_event_hook)) {
				require_once "$plugin_event_hook";
				$event_hook_function = "openqrm_" . "$plugin_name" . "_event";
				$event_hook_function = str_replace("-", "_", $event_hook_function);
				$event_hook_function($action, $id);
			}
		}
		
	}
	
	
}

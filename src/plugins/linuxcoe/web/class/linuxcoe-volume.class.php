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


	$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
	require_once "$RootDir/include/openqrm-server-config.php";
	require_once "$RootDir/include/openqrm-database-functions.php";
	require_once "$RootDir/class/event.class.php";

/**
 * This class represents an linuxcoe_volume object
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class linuxcoe_volume
{

/**
* linuxcoe_volume id
* @access protected
* @var int
*/
var $id = '';
/**
* linuxcoe_volume name
* @access protected
* @var string
*/
var $name = '';
/**
* linuxcoe_volume root
* @access protected
* @var string
*/
var $root = '';
/**
* linuxcoe_volume description
* @access protected
* @var string
*/
var $description = '';


/**
* name of database table
* @access protected
* @var string
*/
var $_db_table;
/**
* path to openqrm basedir
* @access protected
* @var string
*/
var $_base_dir;
/**
* event object
* @access protected
* @var object
*/
var $_event;

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
		$this->_db_table = "linuxcoe_volumes";
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* get an instance of an linuxcoe_volume object from db
	* @access public
	* @param int $id
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $name) {
		$linuxcoe_volume_array = array();
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$linuxcoe_volume_array = $db->Execute("select * from ".$this->_db_table." where linuxcoe_volume_id=".$id);
		} else if ("$name" != "") {
			$linuxcoe_volume_array = $db->Execute("select * from ".$this->_db_table." where linuxcoe_volume_name='".$name."'");
		}
		foreach ($linuxcoe_volume_array as $index => $linuxcoe_volume) {
			$this->id = $linuxcoe_volume["linuxcoe_volume_id"];
			$this->name = $linuxcoe_volume["linuxcoe_volume_name"];
			$this->root = $linuxcoe_volume["linuxcoe_volume_root"];
			$this->description = $linuxcoe_volume["linuxcoe_volume_description"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an linuxcoe_volume by id
	* @access public
	* @param int $id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_id($id) {
		$this->get_instance($id, "");
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an linuxcoe_volume by name
	* @access public
	* @param int $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_name($name) {
		$this->get_instance("", $name);
		return $this;
	}



	//--------------------------------------------------
	/**
	* add a new linuxcoe_volume
	* @access public
	* @param array $linuxcoe_volume_fields
	*/
	//--------------------------------------------------
	function add($linuxcoe_volume_fields) {
		if (!is_array($linuxcoe_volume_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "linuxcoe-volume.class.php", "Fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $linuxcoe_volume_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "linuxcoe-volume.class.php", "Failed adding new linuxcoe_volume to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an linuxcoe_volume
	* <code>
	* $fields = array();
	* $fields['linuxcoe_volume_name'] = 'somename';
	* $fields['linuxcoe_volume_uri'] = 'some-uri';
	* $linuxcoe_volume = new linuxcoe_volume();
	* $linuxcoe_volume->update(1, $fields);
	* </code>
	* @access public
	* @param int $linuxcoe_volume_id
	* @param array $linuxcoe_volume_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($linuxcoe_volume_id, $linuxcoe_volume_fields) {
		if ($linuxcoe_volume_id < 0 || ! is_array($linuxcoe_volume_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "linuxcoe-volume.class.php", "Unable to update linuxcoe_volume $linuxcoe_volume_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($linuxcoe_volume_fields["linuxcoe_volume_id"]);
		$result = $db->AutoExecute($this->_db_table, $linuxcoe_volume_fields, 'UPDATE', "linuxcoe_volume_id = $linuxcoe_volume_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "linuxcoe-volume.class.php", "Failed updating linuxcoe_volume $linuxcoe_volume_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an linuxcoe_volume by id
	* @access public
	* @param int $linuxcoe_volume_id
	*/
	//--------------------------------------------------
	function remove($linuxcoe_volume_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from ? where linuxcoe_volume_id=?",[$this->_db_table,$linuxcoe_volume_id]);
	}

	//--------------------------------------------------
	/**
	* remove an linuxcoe_volume by name
	* @access public
	* @param string $linuxcoe_volume_name
	*/
	//--------------------------------------------------
	function remove_by_name($linuxcoe_volume_name) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from ? where linuxcoe_volume_name=?",[$this->_db_table,$linuxcoe_volume_name]);

	}


	//--------------------------------------------------
	/**
	* get linuxcoe_volume name by id
	* @access public
	* @param int $linuxcoe_volume_id
	* @return string
	*/
	//--------------------------------------------------
	function get_name($linuxcoe_volume_id) {
		$db=openqrm_get_db_connection();
		$linuxcoe_volume_set = $db->Execute("select linuxcoe_volume_name from ? where linuxcoe_volume_id=?",[$this->_db_table,$linuxcoe_volume_id]);
		if (!$linuxcoe_volume_set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "linuxcoe-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$linuxcoe_volume_set->EOF) {
				return $linuxcoe_volume_set->fields["linuxcoe_volume_name"];
			} else {
				return "not found";
			}
		}
	}



	//--------------------------------------------------
	/**
	* get an array of all linuxcoe_volume names
	* <code>
	* $linuxcoe_volume = new linuxcoe_volume();
	* $arr = $linuxcoe_volume->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list() {
		$query = "select linuxcoe_volume_id, linuxcoe_volume_name from ".$this->_db_table." order by linuxcoe_volume_id ASC";
		$linuxcoe_volume_name_array = array();
		$linuxcoe_volume_name_array = openqrm_db_get_result_double ($query);
		return $linuxcoe_volume_name_array;
	}


	//--------------------------------------------------
	/**
	* get an array of all linuxcoe_volume ids
	* <code>
	* $linuxcoe_volume = new linuxcoe_volume();
	* $arr = $linuxcoe_volume->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$linuxcoe_volume_array = array();
		$query = "select linuxcoe_volume_id from ".$this->_db_table;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "linuxcoe-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$linuxcoe_volume_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $linuxcoe_volume_array;
	}

	//--------------------------------------------------
	/**
	* get number of linuxcoe_volume accounts
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(linuxcoe_volume_id) as num from ".$this->_db_table);
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "linuxcoe-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	//--------------------------------------------------
	/**
	* get an array of linuxcoe_volumes
	* @access public
	* @param int $offset
	* @param int $limit
	* @param string $sort
	* @param enum $order [ASC/DESC]
	* @return array
	*/
	//--------------------------------------------------
	function display_overview($offset, $limit, $sort, $order) {
		$db=openqrm_get_db_connection();
		$recordSet = $db->SelectLimit("select * from ".$this->_db_table." order by $sort $order", $limit, $offset);
		$linuxcoe_volume_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "linuxcoe-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($linuxcoe_volume_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $linuxcoe_volume_array;
	}


}

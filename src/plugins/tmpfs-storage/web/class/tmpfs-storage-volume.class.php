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
 * This class represents an tmpfs_storage_volume object
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class tmpfs_storage_volume
{

/**
* tmpfs_storage_volume id
* @access protected
* @var int
*/
var $id = '';
/**
* tmpfs_storage_volume name
* @access protected
* @var string
*/
var $name = '';
/**
* tmpfs_storage_volume size
* @access protected
* @var string
*/
var $size = '';
/**
* tmpfs_storage_volume description
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
		$this->_db_table = "tmpfs_storage_volumes";
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* get an instance of an tmpfs_storage_volume object from db
	* @access public
	* @param int $id
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $name) {
		$tmpfs_storage_volume_array = array();
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$tmpfs_storage_volume_array = $db->Execute("select * from ".$this->_db_table." where tmpfs_storage_volume_id=".$id);
		} else if ("$name" != "") {
			$tmpfs_storage_volume_array = $db->Execute("select * from ".$this->_db_table." where tmpfs_storage_volume_name='".$name."'");
		}
		foreach ($tmpfs_storage_volume_array as $index => $tmpfs_storage_volume) {
			$this->id = $tmpfs_storage_volume["tmpfs_storage_volume_id"];
			$this->name = $tmpfs_storage_volume["tmpfs_storage_volume_name"];
			$this->size = $tmpfs_storage_volume["tmpfs_storage_volume_size"];
			$this->description = $tmpfs_storage_volume["tmpfs_storage_volume_description"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an tmpfs_storage_volume by id
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
	* get an instance of an tmpfs_storage_volume by name
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
	* add a new tmpfs_storage_volume
	* @access public
	* @param array $tmpfs_storage_volume_fields
	*/
	//--------------------------------------------------
	function add($tmpfs_storage_volume_fields) {
		if (!is_array($tmpfs_storage_volume_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "tmpfs-storage-volume.class.php", "Fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $tmpfs_storage_volume_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "tmpfs-storage-volume.class.php", "Failed adding new tmpfs_storage_volume to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an tmpfs_storage_volume
	* <code>
	* $fields = array();
	* $fields['tmpfs_storage_volume_name'] = 'somename';
	* $fields['tmpfs_storage_volume_uri'] = 'some-uri';
	* $tmpfs_storage_volume = new tmpfs_storage_volume();
	* $tmpfs_storage_volume->update(1, $fields);
	* </code>
	* @access public
	* @param int $tmpfs_storage_volume_id
	* @param array $tmpfs_storage_volume_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($tmpfs_storage_volume_id, $tmpfs_storage_volume_fields) {
		if ($tmpfs_storage_volume_id < 0 || ! is_array($tmpfs_storage_volume_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "tmpfs-storage-volume.class.php", "Unable to update tmpfs_storage_volume $tmpfs_storage_volume_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($tmpfs_storage_volume_fields["tmpfs_storage_volume_id"]);
		$result = $db->AutoExecute($this->_db_table, $tmpfs_storage_volume_fields, 'UPDATE', "tmpfs_storage_volume_id = $tmpfs_storage_volume_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "tmpfs-storage-volume.class.php", "Failed updating tmpfs_storage_volume $tmpfs_storage_volume_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an tmpfs_storage_volume by id
	* @access public
	* @param int $tmpfs_storage_volume_id
	*/
	//--------------------------------------------------
	function remove($tmpfs_storage_volume_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from ".$this->_db_table." where tmpfs_storage_volume_id=".$tmpfs_storage_volume_id);
	}

	//--------------------------------------------------
	/**
	* remove an tmpfs_storage_volume by name
	* @access public
	* @param string $tmpfs_storage_volume_name
	*/
	//--------------------------------------------------
	function remove_by_name($tmpfs_storage_volume_name) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from ".$this->_db_table." where tmpfs_storage_volume_name='".$tmpfs_storage_volume_name."'");

	}


	//--------------------------------------------------
	/**
	* get tmpfs_storage_volume name by id
	* @access public
	* @param int $tmpfs_storage_volume_id
	* @return string
	*/
	//--------------------------------------------------
	function get_name($tmpfs_storage_volume_id) {
		$db=openqrm_get_db_connection();
		$tmpfs_storage_volume_set = $db->Execute("select tmpfs_storage_volume_name from ".$this->_db_table." where tmpfs_storage_volume_id=".$tmpfs_storage_volume_id);
		if (!$tmpfs_storage_volume_set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "tmpfs-storage-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$tmpfs_storage_volume_set->EOF) {
				return $tmpfs_storage_volume_set->fields["tmpfs_storage_volume_name"];
			} else {
				return "not found";
			}
		}
	}



	//--------------------------------------------------
	/**
	* get an array of all tmpfs_storage_volume names
	* <code>
	* $tmpfs_storage_volume = new tmpfs_storage_volume();
	* $arr = $tmpfs_storage_volume->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list() {
		$query = "select tmpfs_storage_volume_id, tmpfs_storage_volume_name from ".$this->_db_table." order by tmpfs_storage_volume_id ASC";
		$tmpfs_storage_volume_name_array = array();
		$tmpfs_storage_volume_name_array = openqrm_db_get_result_double ($query);
		return $tmpfs_storage_volume_name_array;
	}


	//--------------------------------------------------
	/**
	* get an array of all tmpfs_storage_volume ids
	* <code>
	* $tmpfs_storage_volume = new tmpfs_storage_volume();
	* $arr = $tmpfs_storage_volume->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$tmpfs_storage_volume_array = array();
		$query = "select tmpfs_storage_volume_id from ".$this->_db_table;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "tmpfs-storage-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$tmpfs_storage_volume_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $tmpfs_storage_volume_array;
	}

	//--------------------------------------------------
	/**
	* get number of tmpfs_storage_volume accounts
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(tmpfs_storage_volume_id) as num from ".$this->_db_table);
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "tmpfs-storage-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	//--------------------------------------------------
	/**
	* get an array of tmpfs_storage_volumes
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
		$tmpfs_storage_volume_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "tmpfs-storage-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($tmpfs_storage_volume_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $tmpfs_storage_volume_array;
	}


}

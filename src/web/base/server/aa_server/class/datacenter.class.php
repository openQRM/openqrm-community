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
require_once $RootDir."/include/openqrm-database-functions.php";
require_once $RootDir."/class/resource.class.php";
require_once $RootDir."/class/storage.class.php";
require_once $RootDir."/class/appliance.class.php";
require_once $RootDir."/class/event.class.php";


/**
 * This class represents the datacenter statistics for the openQRM Dashboard
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 * @author M. Rechenburg, A. Kuballa
 * @version 1.1 added documentation
 * @copyright Copyright 2013, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */
class datacenter
{

/**
* datacenter id
* @access protected
* @var int
*/
var $id = '';
/**
* datacenter load_overall
* @access protected
* @var string
*/
var $load_overall = '';
/**
* datacenter load_server
* @access protected
* @var date
*/
var $load_server = '';
/**
* datacenter load_storage
* @access protected
* @var int
*/
var $load_storage = '';
/**
* datacenter cpu_total
* @access protected
* @var string
*/
var $cpu_total = '';
/**
* datacenter mem_total
* @access protected
* @var string
*/
var $mem_total = '';
/**
* datacenter mem_used
* @access protected
* @var string
*/
var $mem_used = '';
/**
* datacenter resolution
* @access protected
* @var string
*/
var $resolution = 60;
/**
* datacenter last statistic id
* @access protected
* @var string
*/
var $oldest_statistics_id = '';
/**
* datacenter time of the last statistics
* @access protected
* @var string
*/
var $last_statistics = '';




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
	* init datacenter environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		$this->_db_table = 'datacenter_info';
		$this->RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		$this->event = new event();
	}

	
		//--------------------------------------------------
	/**
	* get number of statistics in datacenter
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$sql = "select count(datacenter_id) as num from ".$this->_db_table;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($sql);
		if (!$rs) {
			$this->log("get_count", $_SERVER['REQUEST_TIME'], 2, "datacenter.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}




	//--------------------------------------------------
	/**
	* add statistics to datacenter
	* <code>
	* $datacenter = new datacenter();
	* $datacenter->statistics();
	* </code>
	* @access public
	* @param int $datacenter_id
	* @param array $datacenter_fields
	* @return bool
	*/
	//--------------------------------------------------e
	function statistics() {
		$resources_all = 0;
		$resources_active = 0;
		$resources_available = 0;
		$dc_load_overall = 0;
		$appliance_load_overall = 0;
		$appliance_active = 0;
		$storage_load_overall = 0;
		$storage_active = 0;
		$cpu_total = 0;
		$mem_used = 0;
		$mem_total = 0;

		// run only each minute
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select MAX(datacenter_id) from $this->_db_table");
		foreach ($rs as $index => $dc) {
			if (isset($dc['MAX(datacenter_id)'])) {
				$this->last_statistics = $dc['MAX(datacenter_id)'];
			} else if (isset($dc['max'])) {
				$this->last_statistics = $dc['max'];
			}
		}
		// get an array of resources which are assigned to an appliance
		$appliance_resources_array = array();
		$appliance = new appliance();
		$appliance_list = $appliance->get_all_ids();
		foreach ($appliance_list as $app) {
			$app_id = $app['appliance_id'];
			$g_appliance = new appliance();
			$g_appliance->get_instance_by_id($app_id);
			$g_appliance_resource = $g_appliance->resources;
			if ((!strcmp($g_appliance->state, "active")) || ($g_appliance_resource == 0)) {
				if ($g_appliance_resource != "-1") {
					$appliance_resources_array[] .= $g_appliance_resource;
				}
			}
		}
		// get an array of resources which are a storage server
		$storage_resources_array = array();
		$storage = new storage();
		$storage_list = $storage->get_list();
		foreach ($storage_list as $store) {
			$storage_id = $store['value'];
			$g_storage = new storage();
			$g_storage->get_instance_by_id($storage_id);
			$g_storage_resource = $g_storage->resource_id;
			$storage_resources_array[] .= $g_storage_resource;

		}

		$resource = new resource();
		$resource_list = $resource->get_list();
		foreach ($resource_list as $res) {
			$res_id = $res['resource_id'];
			//echo "!! res_id $res_id <br>";
			$g_resource = new resource();
			$g_resource->get_instance_by_id($res_id);
			// start gathering
			$resources_all++;
			$cpu_total = $cpu_total + $g_resource->cpunumber;
			$mem_used = $mem_used + $g_resource->memused;
			$mem_total = $mem_total + $g_resource->memtotal;
			// resource load
			if (("$g_resource->imageid" == "1") && ("$g_resource->state" == "active")) {
				// idle
				$resources_available++;
			} else if ("$g_resource->state" == "active") {
				// active
				$resources_active++;
				$dc_load_overall = $dc_load_overall + $g_resource->load;
				// is storage ?
				if (in_array($g_resource->id, $storage_resources_array)) {
					$storage_active++;
					$storage_load_overall = $storage_load_overall + $g_resource->load;
				}
				// is appliance ?
				if (in_array($g_resource->id, $appliance_resources_array)) {
					$appliance_active++;
					$appliance_load_overall = $appliance_load_overall + $g_resource->load;
				}
			}
		}
		if ($resources_active != 0) {
			$dc_load_overall = $dc_load_overall/$resources_active;
			$dc_load_overall = number_format($dc_load_overall, 2, '.', '');
		}
		if ($appliance_active != 0) {
			$appliance_load_overall = $appliance_load_overall/$appliance_active;
			$appliance_load_overall = number_format($appliance_load_overall, 2, '.', '');
		}
		if ($storage_active != 0) {
			$storage_load_overall = $storage_load_overall/$storage_active;
			$storage_load_overall = number_format($storage_load_overall, 2, '.', '');
		}
		
		$datacenter_fields = array();
		$datacenter_fields['datacenter_load_overall'] = $dc_load_overall;
		$datacenter_fields['datacenter_load_server'] = $appliance_load_overall;
		$datacenter_fields['datacenter_load_storage'] = $storage_load_overall;
		$datacenter_fields['datacenter_cpu_total'] = $cpu_total;
		$datacenter_fields['datacenter_mem_total'] = $mem_total;
		$datacenter_fields['datacenter_mem_used'] = $mem_used;
		
		$stats_count = $this->get_count();
		if ($stats_count >= $this->resolution) {
			$rs = $db->Execute("select MIN(datacenter_id) from $this->_db_table");
			foreach ($rs as $index => $dc) {
				if (isset($dc['MIN(datacenter_id)'])) {
					$this->oldest_statistics_id = $dc['MIN(datacenter_id)'];
				} else if (isset($dc['min'])) {
					$this->oldest_statistics_id = $dc['min'];
				}
			}
			$rs = $db->Execute("delete from $this->_db_table where datacenter_id=$this->oldest_statistics_id");
			$stats_count = $this->get_count();
		}
		$datacenter_fields['datacenter_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
		$result = $db->AutoExecute($this->_db_table, $datacenter_fields, 'INSERT');
		if (! $result) {
			$this->event->log("add", $_SERVER['REQUEST_TIME'], 2, "datacenter.class.php", "Failed updating datacenter ".$datacenter_fields['datacenter_id'], "", "", 0, 0, 0);
		}
	}


	//--------------------------------------------------
	/**
	* get an array of datacenter statistics
	* <code>
	* $datacenter = new datacenter();
	* $arr = $datacenter->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get() {
		$sql = "select * from ".$this->_db_table." order by datacenter_id ASC";
		$db=openqrm_get_db_connection();
		$recordSet = $db->SelectLimit($sql, 60, 0);
		$datacenter_array = array();
		if (!$recordSet) {
			$this->log("get", $_SERVER['REQUEST_TIME'], 2, "datacenter.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($datacenter_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $datacenter_array;
		
	}




}

<?php

// This class represents a resource in openQRM (physical hardware or virtual machine)
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
$BootServiceDir = $_SERVER["DOCUMENT_ROOT"] . '/openqrm/boot-service/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/user.class.php";

global $RESOURCE_INFO_TABLE;
$RESOURCE_TIME_OUT = 240;
global $RESOURCE_TIME_OUT;
$event = new event();
global $event;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXECUTION_LAYER;
global $OPENQRM_WEB_PROTOCOL;


class resource
{
	
	var $id = '';
	var $localboot = '';
	var $kernel = '';
	var $kernelid = '';
	var $image = '';
	var $imageid = '';
	var $openqrmserver = '';
	var $basedir = '';
	var $applianceid = '';
	var $ip = '';
	var $subnet = '';
	var $broadcast = '';
	var $network = '';
	var $mac = '';
	var $nics = '';
	var $uptime = '';
	var $cpunumber = '';
	var $cpuspeed = '';
	var $cpumodel = '';
	var $memtotal = '';
	var $memused = '';
	var $swaptotal = '';
	var $swapused = '';
	var $hostname = '';
	var $vtype = '';
	var $vhostid = '';
	var $vname = '';
	var $vnc = '';
	var $load = '';
	var $execdport = '';
	var $senddelay = '';
	var $capabilities = '';
	var $lastgood = '';
	var $state = '';
	var $event = '';

// ---------------------------------------------------------------------------------
// methods to create an instance of a resource object filled from the db
// ---------------------------------------------------------------------------------

// returns a resource from the db selected by id, mac or ip
	function get_instance($id, $mac="", $ip="")
	{
		global $RESOURCE_INFO_TABLE;
		global $event;
		if (strlen($mac)) {
			// check for both, lowercase + uppercase mac
			$mac_lowercase = strtolower($mac);
			$mac_uppercase = strtoupper($mac);
		}
  
		$db = openqrm_get_db_connection();
  
		if ("$id" != "") {
			$resource_array = $db->GetAll("select * from $RESOURCE_INFO_TABLE where resource_id=$id");
		} else if ("$mac" != "") {
			$resource_array = $db->GetAll("select * from $RESOURCE_INFO_TABLE where resource_mac='$mac_lowercase' or resource_mac='$mac_uppercase'");
		} else if ("$ip" != "") {
			$resource_array = $db->GetAll("select * from $RESOURCE_INFO_TABLE where resource_ip='$ip'");
		} else {
			$error = '';
			foreach (debug_backtrace() as $key => $msg) {
				if ($key === 1) {
					$error .= '( ' . basename($msg['file']) . ' ' . $msg['line'] . ' )';
				}
				syslog(LOG_ERR, $msg['function'] . '() ' . basename($msg['file']) . ':' . $msg['line']);
			}
			$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "Could not create instance of event without data " . $error, "", "", 0, 0, 0);
		}
  
		if (is_array($resource_array)) {
			foreach ($resource_array as $index => $resource) {
				$this->id = $resource["resource_id"];
				$this->localboot = $resource["resource_localboot"];
				$this->kernel = $resource["resource_kernel"];
				$this->kernelid = $resource["resource_kernelid"];
				$this->image = $resource["resource_image"];
				$this->imageid = $resource["resource_imageid"];
				$this->openqrmserver = $resource["resource_openqrmserver"];
				$this->basedir = $resource["resource_basedir"];
				$this->applianceid = $resource["resource_applianceid"];
				$this->ip = $resource["resource_ip"];
				$this->subnet = $resource["resource_subnet"];
				$this->broadcast = $resource["resource_broadcast"];
				$this->network = $resource["resource_network"];
				$this->mac = $resource["resource_mac"];
				$this->nics = $resource["resource_nics"];
				$this->uptime = $resource["resource_uptime"];
				$this->cpunumber = $resource["resource_cpunumber"];
				$this->cpuspeed = $resource["resource_cpuspeed"];
				$this->cpumodel = $resource["resource_cpumodel"];
				$this->memtotal = $resource["resource_memtotal"];
				$this->memused = $resource["resource_memused"];
				$this->swaptotal = $resource["resource_swaptotal"];
				$this->swapused = $resource["resource_swapused"];
				$this->hostname = $resource["resource_hostname"];
				$this->vtype = $resource["resource_vtype"];
				$this->vhostid = $resource["resource_vhostid"];
				$this->vname = $resource["resource_vname"];
				$this->vnc = $resource["resource_vnc"];
				$this->load = $resource["resource_load"];
				$this->execdport = $resource["resource_execdport"];
				$this->senddelay = $resource["resource_senddelay"];
				$this->capabilities = $resource["resource_capabilities"];
				$this->lastgood = $resource["resource_lastgood"];
				$this->state = $resource["resource_state"];
				$this->event = $resource["resource_event"];
			}
			return $this;
		}
		return false;
	}

// returns a resource from the db selected by id
	function get_instance_by_id($id)
	{
		$this->get_instance($id, "", "");
		return $this;
	}

// returns a resource from the db selected by ip
	function get_instance_by_ip($ip)
	{
		$this->get_instance("", "", $ip);
		return $this;
	}

// returns a resource from the db selected by mac
	function get_instance_by_mac($mac)
	{
		$this->get_instance("", $mac, "");
		return $this;
	}


// returns a resource with just the id set by the resource_hostname
	function get_instance_id_by_hostname($resource_hostname)
	{
		global $RESOURCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		if ($resource_hostname != "") {
			$resource_array = $db->GetAll("select * from " . $RESOURCE_INFO_TABLE . " where resource_hostname='" . $resource_hostname . "'");
		} else {
			$event->log("get_instance_id_by_hostname", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
			foreach (debug_backtrace() as $key => $msg) {
				syslog(LOG_ERR, $msg['function'] . '() ' . basename($msg['file']) . ':' . $msg['line']);
			}
			return;
		}
		foreach ($resource_array as $index => $resource) {
			$this->id = $resource["resource_id"];
		}
		return $this;
	}




// ---------------------------------------------------------------------------------
// getter + setter
// ---------------------------------------------------------------------------------
	
	function get_id()
	{
		return $this->id;
	}
	
	function set_id($id)
	{
		$this->id = $id;
	}



// ---------------------------------------------------------------------------------
// general resource methods
// ---------------------------------------------------------------------------------

// checks if a resource exists in the database
	function exists($mac_address)
	{
		global $RESOURCE_INFO_TABLE;
		global $event;
		// check for both, lowercase + uppercase mac
		$mac_lowercase = strtolower($mac_address);
		$mac_uppercase = strtoupper($mac_address);
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select resource_id from $RESOURCE_INFO_TABLE where resource_mac='$mac_lowercase' or resource_mac='$mac_uppercase'");
		if (!$rs)
			$event->log("exists", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			if ($rs->EOF) {
				return false;
			} else {
				return true;
			}
	}


	// checks if a resource exists in the database by name
	function exists_by_name($resource_hostname)
	{
		global $RESOURCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select resource_id from " . $RESOURCE_INFO_TABLE . " where resource_hostname='" . $resource_hostname . "'");
		if (!$rs)
			$event->log("exists_by_name", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			if ($rs->EOF) {
				return false;
			} else {
				return true;
			}
	}

	// checks if a resource exists in the database by id
	function exists_by_id($resource_id)
	{
		global $RESOURCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select resource_id from " . $RESOURCE_INFO_TABLE . " where resource_id='" . $resource_id . "'");
		if (!$rs) {
			$event->log("exists_by_id", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		}
		else {
			if ($rs->EOF) {
				return false;
			} else {
				return true;
			}
		}
	}


// checks if given resource id is free in the db
	function is_id_free($resource_id)
	{
		global $RESOURCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select resource_id from $RESOURCE_INFO_TABLE where resource_id=$resource_id");
		if (!$rs)
			$event->log("exists", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			if ($rs->EOF) {
				return true;
			} else {
				return false;
			}
	}


// adds resource to the database
	function add($resource_fields)
	{
		global $OPENQRM_EXEC_PORT;
		global $RESOURCE_INFO_TABLE;
		global $OPENQRM_RESOURCE_BASE_DIR;
		global $event;
		global $RootDir;
		$openqrm_server = new openqrm_server();
		$OPENQRM_SERVER_IP_ADDRESS = $openqrm_server->get_ip_address();
		if (!is_array($resource_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "Resource_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		# set defaults
		$resource_fields["resource_basedir"] = $OPENQRM_RESOURCE_BASE_DIR;
		$resource_fields["resource_openqrmserver"] = $OPENQRM_SERVER_IP_ADDRESS;
		$resource_fields["resource_execdport"] = $OPENQRM_EXEC_PORT;
		$resource_fields["resource_kernel"] = 'default';
		$resource_fields["resource_kernelid"] = '1';
		$resource_fields["resource_image"] = 'idle';
		$resource_fields["resource_imageid"] = '1';
		$resource_fields["resource_senddelay"] = 10;
		$resource_fields["resource_lastgood"] = $_SERVER['REQUEST_TIME'];
		// add mac address always in lowercase
		$macl = strtolower($resource_fields["resource_mac"]);
		$resource_fields["resource_mac"] = $macl;
		if ($this->exists($resource_fields["resource_mac"])) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "Resource mac address " . $resource_fields["resource_mac"] . " already existing! Not adding.", "", "", 0, 0, 0);
			return 1;
		}
		$db = openqrm_get_db_connection();
		$result = $db->AutoExecute($RESOURCE_INFO_TABLE, $resource_fields, 'INSERT');
		if (!$result) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "Failed adding new resource to database", "", "", 0, 0, 0);
		}
		// new resource hook
		$plugin = new plugin();
		$enabled_plugins = $plugin->enabled();
		foreach ($enabled_plugins as $index => $plugin_name) {
			$plugin_new_resource_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-resource-hook.php";
			if (file_exists($plugin_new_resource_hook)) {
				$event->log("add", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found plugin $plugin_name handling new-resource event.", "", "", 0, 0, $resource_fields["resource_id"]);
				require_once "$plugin_new_resource_hook";
				$resource_function = "openqrm_" . "$plugin_name" . "_resource";
				$resource_function = str_replace("-", "_", $resource_function);
				$resource_function("add", $resource_fields);
			}
		}
		
	}

// removes resource from the database
	function remove($resource_id, $resource_mac)
	{
		global $OPENQRM_EXEC_PORT;
		global $RESOURCE_INFO_TABLE;
		global $RootDir;
		global $event;
		// never remove the openQRM server resource
		if ($resource_id == 0) {
			return;
		}
		// never remove an auto-select resource
		if ($resource_id == -1) {
			return;
		}
		$openqrm_server = new openqrm_server();
		$OPENQRM_SERVER_IP_ADDRESS = $openqrm_server->get_ip_address();
		
		// remove resource hook
		$plugin = new plugin();
		$enabled_plugins = $plugin->enabled();
		foreach ($enabled_plugins as $index => $plugin_name) {
			$plugin_new_resource_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-resource-hook.php";
			if (file_exists($plugin_new_resource_hook)) {
				$event->log("remove", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found plugin $plugin_name handling remove-resource event.", "", "", 0, 0, $resource_id);
				require_once "$plugin_new_resource_hook";
				$resource_fields = $this->get_fields($resource_id);
				// run remove hook
				$resource_function = "openqrm_" . "$plugin_name" . "_resource";
				$resource_function = str_replace("-", "_", $resource_function);
				$resource_function("remove", $resource_fields);
			}
		}
		
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from $RESOURCE_INFO_TABLE where resource_id=$resource_id and resource_mac='$resource_mac'");
	}


// assigns a kernel and fs-image to a resource
	function assign($resource_id, $resource_kernelid, $resource_kernel, $resource_imageid, $resource_image)
	{
		global $RESOURCE_INFO_TABLE;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("update $RESOURCE_INFO_TABLE set
		 resource_kernelid=$resource_kernelid,
		 resource_kernel='$resource_kernel',
		 resource_imageid=$resource_imageid,
		 resource_image='$resource_image' where resource_id=$resource_id");
	}



// set a resource to net- or local boot
// resource_localboot = 0 -> netboot / 1 -> localboot
	function set_localboot($resource_id, $resource_localboot)
	{
		global $RESOURCE_INFO_TABLE;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("update $RESOURCE_INFO_TABLE set resource_localboot='$resource_localboot' where resource_id=$resource_id");
	}


// displays resource parameter for resource_id
	function get_parameter($resource_id)
	{
		global $RESOURCE_INFO_TABLE;
		global $KERNEL_INFO_TABLE;
		global $IMAGE_INFO_TABLE;
		global $APPLIANCE_INFO_TABLE;
		global $STORAGE_INFO_TABLE;
		global $DEPLOYMENT_INFO_TABLE;
		global $BootServiceDir;
		global $event;
		global $OPENQRM_EXECUTION_LAYER;
		global $OPENQRM_WEB_PROTOCOL;
		$db = openqrm_get_db_connection();
		// resource parameter
		if (!strlen($resource_id)) {
			return;
		}
		$recordSet = $db->Execute("select * from $RESOURCE_INFO_TABLE where resource_id=$resource_id");
		if (!$recordSet)
			$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", 'Db Error: ' . $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			while (!$recordSet->EOF) {
				array_walk($recordSet->fields, 'print_array');
				$image_id = $recordSet->fields["resource_imageid"];
				$kernel_id = $recordSet->fields["resource_kernelid"];
				$recordSet->MoveNext();
			}
		$recordSet->Close();
		// kernel-parameter
		$recordSet = $db->Execute("select * from $KERNEL_INFO_TABLE where kernel_id=$kernel_id");
		if (!$recordSet)
			$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			while (!$recordSet->EOF) {
				array_walk($recordSet->fields, 'print_array');
				$recordSet->MoveNext();
			}
		$recordSet->Close();
		// image-parameter
		$recordSet = $db->Execute("select * from $IMAGE_INFO_TABLE where image_id=$image_id");
		if (!$recordSet)
			$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			while (!$recordSet->EOF) {
				array_walk($recordSet->fields, 'print_array');
				$image_storageid = $recordSet->fields["image_storageid"];
				$recordSet->MoveNext();
			}
		$recordSet->Close();
		// image storage parameter
		if (strlen($image_storageid)) {
			$storage = new storage();
			$storage->get_instance_by_id($image_storageid);
			$storage_resource_id = $storage->resource_id;
			$recordSet = $db->Execute("select resource_ip from $RESOURCE_INFO_TABLE where resource_id=$storage_resource_id");
			if (!$recordSet)
				$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
			else
				while (!$recordSet->EOF) {
					//array_walk($recordSet->fields, 'print_array');
					$image_storage_server_ip = $recordSet->fields['resource_ip'];
					$recordSet->MoveNext();
				}
			$recordSet->Close();
			echo "image_storage_server_ip=$image_storage_server_ip\n";
		}
		// appliance parameter
		$appliance_virtualization = 0;
		$recordSet = $db->Execute("select * from $APPLIANCE_INFO_TABLE where appliance_resources=$resource_id and appliance_stoptime='0'");
		if (!$recordSet)
			$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			while (!$recordSet->EOF) {
				array_walk($recordSet->fields, 'print_array');
				$appliance_virtualization = $recordSet->fields["appliance_virtualization"];
				$recordSet->MoveNext();
			}
		$recordSet->Close();
		
		// virtualization parameter
		if ($appliance_virtualization > 0) {
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($appliance_virtualization);
			echo "virtualization_type=\"$virtualization->type\"\n";
			echo "virtualization_name=\"$virtualization->name\"\n";
		}
		// storage server parameter
		if ($image_id <> 1) {
			$recordSet = $db->Execute("select * from $STORAGE_INFO_TABLE where storage_resource_id=$resource_id");
			if (!$recordSet)
				$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
			else
				while (!$recordSet->EOF) {
					$storage_type = $recordSet->fields["storage_type"];
					$recordSet1 = $db->Execute("select deployment_storagetype from $DEPLOYMENT_INFO_TABLE where deployment_id='$storage_type'");
					if (!$recordSet1)
						$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
					else
						while (!$recordSet1->EOF) {
							$deployment_storagetype = $recordSet1->fields["deployment_storagetype"];
							echo "deployment_storagetype=$deployment_storagetype\n";
							$recordSet1->MoveNext();
						}
					$recordSet1->Close();
					$recordSet->MoveNext();
				}
			$recordSet->Close();
		}
		
		$db->Close();
		
		// command execution layer
		echo "openqrm_execution_layer=\"$OPENQRM_EXECUTION_LAYER\"\n";
		// openQRM server web protocol
		echo "openqrm_web_protocol=\"$OPENQRM_WEB_PROTOCOL\"\n";
		
		// plugin and bootservice list
		$plugin_list = '';
		$boot_service_list = '';
		$plugin = new plugin();
		$enabled_plugins = $plugin->enabled();
		foreach ($enabled_plugins as $index => $plugin_name) {
			$plugin_list = "$plugin_list$plugin_name ";
			// add to list of boot-services only if boot-services for the resource exists
			$plugin_boot_service = "$BootServiceDir/boot-service-$plugin_name.tgz";
			if (file_exists($plugin_boot_service)) {
				$boot_service_list = "$boot_service_list$plugin_name ";
			}
		}
		echo "openqrm_plugins=\"$plugin_list\"\n";
		echo "openqrm_boot_services=\"$boot_service_list\"\n";
		// here the appliance resouce got active
		// now we remove the iscsi password from the deployment parameters
		// TODO: this is not reboot save !
//    $image = new image();
//    $image->get_instance_by_id($image_id);
//    if (strstr($image->deployment_parameter, "IMAGE_ISCSI_AUTH")) {
//        $image->set_deployment_parameters("IMAGE_ISCSI_AUTH", "");
//        $event->log("get_parameter", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Resource $resource_id gets active. Removing authentication token from image $image_id", "", "", 0, 0, $resource_id);
//    }
	
	}
	
	function get_parameter_array($resource_id)
	{
		global $RESOURCE_INFO_TABLE;
		$db = openqrm_get_db_connection();
		$resource_array = $db->GetAll("select * from $RESOURCE_INFO_TABLE where resource_id=$resource_id");
		return $resource_array;
	}
	
	function get_list()
	{
		global $RESOURCE_INFO_TABLE;
		global $event;
		$resource_list = array();
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select resource_id, resource_ip, resource_state from $RESOURCE_INFO_TABLE ORDER BY resource_id ASC");
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			while (!$rs->EOF) {
				$resource_list[] = $rs->fields;
				$rs->MoveNext();
			}
		return $resource_list;
	}
	
	
	function update_info($resource_id, $resource_fields)
	{
		global $RESOURCE_INFO_TABLE;
		global $event;
		if (!is_array($resource_fields)) {
			$event->log("update_info", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "Unable to update resource $resource_id", "", "", 0, 0, 0);
			return 1;
		}
		if (!strlen($resource_id)) {
			return 1;
		}
		$db = openqrm_get_db_connection();
		unset($resource_fields["resource_id"]);
		if (isset($resource_fields["resource_mac"])) {
			// add mac address always in lowercase
			$macl = strtolower($resource_fields["resource_mac"]);
			$resource_fields["resource_mac"] = $macl;
		}
		//check if resource exists, then update else insert
		if($this->exists_by_id($resource_id)){
			$result = $db->AutoExecute($RESOURCE_INFO_TABLE, $resource_fields, 'UPDATE', "resource_id = $resource_id");
			//$result = $db->AutoExecute($RESOURCE_INFO_TABLE, $resource_fields, 'REPLACE', "");
			if (!$result) {
				$event->log("update_info", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "Failed updating resource $resource_id", "", "", 0, 0, 0);
			}
		}else{
			$result = $this->add($resource_fields);
			if(!$result){
				$event->log("update_info", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", "Adding new resource $resource_id", "", "", 0, 0, 0);
			}
		}
	}
	
	function update_status($resource_id, $resource_state, $resource_event)
	{
		global $RESOURCE_INFO_TABLE;
		$db = openqrm_get_db_connection();
		$query = "update $RESOURCE_INFO_TABLE set
			resource_state='$resource_state',
			resource_event='$resource_event'
			where resource_id=$resource_id";
		$rs = $db->Execute("$query");
	}


// returns an array of resource fields by id
	function get_fields($which)
	{
		$resource = new resource();
		$resource->get_instance_by_id($which);
		$resource_fields = array();
		$resource_fields["resource_id"] = $resource->id;
		$resource_fields["resource_localboot"] = $resource->localboot;
		$resource_fields["resource_kernel"] = $resource->kernel;
		$resource_fields["resource_kernelid"] = $resource->kernelid;
		$resource_fields["resource_image"] = $resource->image;
		$resource_fields["resource_imageid"] = $resource->imageid;
		$resource_fields["resource_openqrmserver"] = $resource->openqrmserver;
		$resource_fields["resource_basedir"] = $resource->basedir;
		$resource_fields["resource_applianceid"] = $resource->applianceid;
		$resource_fields["resource_ip"] = $resource->ip;
		$resource_fields["resource_subnet"] = $resource->subnet;
		$resource_fields["resource_broadcast"] = $resource->broadcast;
		$resource_fields["resource_network"] = $resource->network;
		$resource_fields["resource_mac"] = $resource->mac;
		$resource_fields["resource_nics"] = $resource->nics;
		$resource_fields["resource_uptime"] = $resource->uptime;
		$resource_fields["resource_cpunumber"] = $resource->cpunumber;
		$resource_fields["resource_cpuspeed"] = $resource->cpuspeed;
		$resource_fields["resource_cpumodel"] = $resource->cpumodel;
		$resource_fields["resource_memtotal"] = $resource->memtotal;
		$resource_fields["resource_memused"] = $resource->memused;
		$resource_fields["resource_swaptotal"] = $resource->swaptotal;
		$resource_fields["resource_swapused"] = $resource->swapused;
		$resource_fields["resource_hostname"] = $resource->hostname;
		$resource_fields["resource_vtype"] = $resource->vtype;
		$resource_fields["resource_vhostid"] = $resource->vhostid;
		$resource_fields["resource_load"] = $resource->load;
		$resource_fields["resource_execdport"] = $resource->execdport;
		$resource_fields["resource_senddelay"] = $resource->senddelay;
		$resource_fields["resource_capabilities"] = $resource->capabilities;
		$resource_fields["resource_lastgood"] = $resource->lastgood;
		$resource_fields["resource_state"] = $resource->state;
		$resource_fields["resource_event"] = $resource->event;
		return $resource_fields;
	}


// function to send a command to a resource by resource_ip
	function send_command($resource_ip, $resource_command, $command_timeout = NULL)
	{
		global $OPENQRM_EXEC_PORT;
		global $OPENQRM_SERVER_BASE_DIR;
		global $OPENQRM_EXECUTION_LAYER;
		global $event;
		global $RootDir;
		// here we assume that we are the resource
		
		// plugin hook in case a resource gets rebooted or halted
		switch ($resource_command) {
			case 'reboot':
				// start the hook
				$plugin = new plugin();
				$enabled_plugins = $plugin->enabled();
				foreach ($enabled_plugins as $index => $plugin_name) {
					$plugin_start_resource_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-resource-hook.php";
					if (file_exists($plugin_start_resource_hook)) {
						$event->log("start", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found plugin $plugin_name handling start-resource event.", "", "", 0, 0, $this->id);
						// prepare resource_fields array
						$resource_fields = $this->get_fields($this->id);
						// include the plugin function file and run it
						require_once "$plugin_start_resource_hook";
						$resource_function = "openqrm_" . "$plugin_name" . "_resource";
						$resource_function = str_replace("-", "_", $resource_function);
						$resource_function("start", $resource_fields);
					}
				}
				// here we check if the resource is virtual or a host and if
				// the virtualization plugin wants to reboot it via the host
				$virtualization = new virtualization();
				$virtualization->get_instance_by_id($this->vtype);
				$virtualization_plugin_name = $virtualization->get_plugin_name();
				$plugin_resource_virtual_command_hook_vm_type = "$RootDir/plugins/$virtualization_plugin_name/openqrm-$virtualization_plugin_name-resource-virtual-command-hook.php";
				// we also give the deployment type a chance to implement virtual commands
				$deployment_plugin_name = '';
				if ($this->imageid != 1) {
					$image = new image();
					$image->get_instance_by_id($this->imageid);
					$storage = new storage();
					$storage->get_instance_by_id($image->storageid);
					$deployment = new deployment();
					$deployment->get_instance_by_id($storage->type);
					$deployment_plugin_name = $deployment->storagetype;
				}
				$plugin_resource_virtual_command_hook = '';
				$plugin_resource_virtual_command_hook_image_type = "$RootDir/plugins/$deployment_plugin_name/openqrm-$deployment_plugin_name-resource-virtual-command-hook.php";
				//            $plugin_resource_virtual_command_hook_image_type = "$RootDir/plugins/sanboot-storage/openqrm-sanboot-storage-resource-virtual-command-hook.php";
				if (file_exists($plugin_resource_virtual_command_hook_vm_type)) {
					$event->log("start", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found virtualization $virtualization_plugin_name managing virtual command.", "", "", 0, 0, $this->id);
					$plugin_resource_virtual_command_hook = $plugin_resource_virtual_command_hook_vm_type;
				} else if (file_exists($plugin_resource_virtual_command_hook_image_type)) {
					// check if IMAGE_VIRTUAL_RESOURCE_COMMAND=true
					$event->log("start", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found deploymetn $deployment_plugin_name managing virtual command.", "", "", 0, 0, $this->id);
					$image = new image();
					$image->get_instance_by_id($this->imageid);
					$virtual_command_enabled = $image->get_deployment_parameter("IMAGE_VIRTUAL_RESOURCE_COMMAND");
					if (!strcmp($virtual_command_enabled, "true")) {
						$event->log("start", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found IMAGE_VIRTUAL_RESOURCE_COMMAND enabled, using virtual command.", "", "", 0, 0, $this->id);
						$plugin_resource_virtual_command_hook = $plugin_resource_virtual_command_hook_image_type;
						$virtualization_plugin_name = $deployment_plugin_name;
					} else {
						$event->log("start", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found IMAGE_VIRTUAL_RESOURCE_COMMAND disabled, using regular command.", "", "", 0, 0, $this->id);
					}
				}
				if (strlen($plugin_resource_virtual_command_hook)) {
					$event->log("start", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found plugin $virtualization_plugin_name virtually handling the reboot command.", "", "", 0, 0, $this->id);
					// prepare resource_fields array
					$resource_fields = $this->get_fields($this->id);
					// include the plugin function file and run it
					require_once "$plugin_resource_virtual_command_hook";
					$resource_function = "openqrm_" . "$virtualization_plugin_name" . "_resource_virtual_command";
					$resource_function = str_replace("-", "_", $resource_function);
					$resource_function("reboot", $resource_fields);
					// the virtual reboot function can only be
					// implemented by a single plugin depending on the
					// resource type. So we return after that and
					// do not try to reboot the resource via its ip
					return;
				}
				break;
			
			case 'halt':
				// stop hook
				$plugin = new plugin();
				$enabled_plugins = $plugin->enabled();
				foreach ($enabled_plugins as $index => $plugin_name) {
					$plugin_start_resource_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-resource-hook.php";
					if (file_exists($plugin_start_resource_hook)) {
						$event->log("stop", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found plugin $plugin_name handling start-resource event.", "", "", 0, 0, $this->id);
						// prepare resource_fields array
						$resource_fields = $this->get_fields($this->id);
						// include the plugin function file and run it
						require_once "$plugin_start_resource_hook";
						$resource_function = "openqrm_" . "$plugin_name" . "_resource";
						$resource_function = str_replace("-", "_", $resource_function);
						$resource_function("stop", $resource_fields);
					}
				}
				// here we check if the resource is virtual or a host and if
				// the virtualization plugin wants to reboot it via the host
				$virtualization = new virtualization();
				$virtualization->get_instance_by_id($this->vtype);
				$virtualization_plugin_name = $virtualization->get_plugin_name();
				$plugin_resource_virtual_command_hook_vm_type = "$RootDir/plugins/$virtualization_plugin_name/openqrm-$virtualization_plugin_name-resource-virtual-command-hook.php";
				// we also give the deployment type a chance to implement virtual commands
				$deployment_plugin_name = '';
				if ($this->imageid != 1) {
					$image = new image();
					$image->get_instance_by_id($this->imageid);
					$storage = new storage();
					$storage->get_instance_by_id($image->storageid);
					$deployment = new deployment();
					$deployment->get_instance_by_id($storage->type);
					$deployment_plugin_name = $deployment->storagetype;
				}
				$plugin_resource_virtual_command_hook = '';
				$plugin_resource_virtual_command_hook_image_type = "$RootDir/plugins/$deployment_plugin_name/openqrm-$deployment_plugin_name-resource-virtual-command-hook.php";
				if (file_exists($plugin_resource_virtual_command_hook_vm_type)) {
					$event->log("stop", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found virtualization $virtualization_plugin_name managing virtual command.", "", "", 0, 0, $this->id);
					$plugin_resource_virtual_command_hook = $plugin_resource_virtual_command_hook_vm_type;
				} else if (file_exists($plugin_resource_virtual_command_hook_image_type)) {
					// check if IMAGE_VIRTUAL_RESOURCE_COMMAND=true
					$event->log("stop", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found deploymetn $deployment_plugin_name managing virtual command.", "", "", 0, 0, $this->id);
					$image = new image();
					$image->get_instance_by_id($this->imageid);
					$virtual_command_enabled = $image->get_deployment_parameter("IMAGE_VIRTUAL_RESOURCE_COMMAND");
					if (!strcmp($virtual_command_enabled, "true")) {
						$event->log("stop", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found IMAGE_VIRTUAL_RESOURCE_COMMAND enabled, using virtual command.", "", "", 0, 0, $this->id);
						$plugin_resource_virtual_command_hook = $plugin_resource_virtual_command_hook_image_type;
						$virtualization_plugin_name = $deployment_plugin_name;
					} else {
						$event->log("stop", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found IMAGE_VIRTUAL_RESOURCE_COMMAND disabled, using regular command.", "", "", 0, 0, $this->id);
					}
				}
				if (strlen($plugin_resource_virtual_command_hook)) {
					$event->log("stop", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Found plugin $virtualization_plugin_name virtually handling the reboot command.", "", "", 0, 0, $this->id);
					// prepare resource_fields array
					$resource_fields = $this->get_fields($this->id);
					// include the plugin function file and run it
					require_once "$plugin_resource_virtual_command_hook";
					$resource_function = "openqrm_" . "$virtualization_plugin_name" . "_resource_virtual_command";
					$resource_function = str_replace("-", "_", $resource_function);
					$resource_function("halt", $resource_fields);
					// the virtual halt function can only be
					// implemented by a single plugin depending on the
					// resource type. So we return after that and
					// do not try to halt the resource via its ip
					return;
				}
				break;
		}
		
		// check which execution layer to use
		switch ($OPENQRM_EXECUTION_LAYER) {
			case 'dropbear':
				// generate a random token for the cmd
				$cmd_token = md5(uniqid(rand(), true));
				// custom timeout ?
				if (!is_null($command_timeout)) {
					$cmd_token .= "." . $command_timeout;
				}
				$final_resource_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i $resource_ip -t $cmd_token -c \"$resource_command\"";
				//$event->log("start", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Running : $final_resource_command", "", "", 0, 0, 0);
				shell_exec($final_resource_command);
				break;
			case 'rabbitmq':
				$event->log("send_command", $_SERVER['REQUEST_TIME'], 5, "resource.class.php", "Sending command to the rabbit queue $resource_ip", "", "", 0, 0, 0);
				require_once "$RootDir/class/rabbit.class.php";
				$rabbit = new rabbit();
				$rabbit->queue($resource_ip, $resource_command);
				break;
		}
	}




//--------------------------------------------------
	/**
	 * set the capabilities of a resource
	 * @access public
	 * @param string $key
	 * @param string $value
	 */
//--------------------------------------------------
	function set_resource_capabilities($key, $value)
	{
		$this->get_instance_by_id($this->id);
		$resource_capabilites_parameter = $this->capabilities;
		$key = trim($key);
		if (strstr($resource_capabilites_parameter, $key)) {
			// change
			$cp1 = trim($resource_capabilites_parameter);
			$cp2 = strstr($cp1, $key);
			$keystr = "$key=\"";
			$endmark = "\"";
			$cp3 = str_replace($keystr, "", $cp2);
			$endpos = strpos($cp3, $endmark);
			$cp = substr($cp3, 0, $endpos);
			$new_resource_capabilites_parameter = str_replace("$key=\"$cp\"", "$key=\"$value\"", $resource_capabilites_parameter);
		} else {
			// add
			$new_resource_capabilites_parameter = "$resource_capabilites_parameter $key=\"$value\"";
		}
		$resource_fields = array();
		$resource_fields["resource_capabilities"] = "$new_resource_capabilites_parameter";
		$this->update_info($this->id, $resource_fields);
	}



//--------------------------------------------------
	/**
	 * gets a deployment parameter of an image
	 * @access public
	 * @param string $key
	 * @return string $value
	 */
//--------------------------------------------------
	function get_resource_capabilities($key)
	{
		$resource_capabilites_parameter = $this->capabilities;
		$key = trim($key);
		if (strstr($resource_capabilites_parameter, $key)) {
			// change
			$cp1 = trim($resource_capabilites_parameter);
			$cp2 = strstr($cp1, $key);
			$keystr = "$key=\"";
			$endmark = "\"";
			$cp3 = str_replace($keystr, "", $cp2);
			$endpos = strpos($cp3, $endmark);
			$cp = substr($cp3, 0, $endpos);
			return $cp;
		} else {
			return "";
		}
	}


// returns the number of managed resource
	function get_count($which)
	{
		global $RESOURCE_INFO_TABLE;
		global $event;
		$count = 0;
		$db = openqrm_get_db_connection();
		
		$sql = "select count(resource_id) as num from $RESOURCE_INFO_TABLE where ";
		switch ($which) {
			case 'all':
				$sql .= " resource_id>=0";
				break;
			case 'online':
				$sql .= " resource_state='active'";
				break;
			case 'offline':
				$sql .= " resource_state!='active'";
				break;
			case 'idle':
				$sql .= " resource_state!='active' and resource_imageid=1";
				break;
			case 'error':
				$sql .= " resource_state='error'";
				break;
			case 'phys':
				$sql .= " resource_id=resource_vhostid or resource_vtype=1";
				break;
		}
		$rs = $db->Execute($sql);
		if (!$rs) {
			$event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


// generates a mac address
	function generate_mac()
	{
		$CMD = "(date; cat /proc/interrupts) | md5sum | sed -r 's/^(.{10}).*\$/\\1/; s/([0-9a-f]{2})/\\1:/g; s/:\$//;' | tr '[:lower:]:' '[:upper:]-' | sed -e 's/-/:/g'";
		$GEN_MAC = exec($CMD);
		$GEN_MAC = "00:" . $GEN_MAC;
		$this->mac = strtolower($GEN_MAC);
	}


// returns the next free vnc port number for a specific vm host
	function generate_vnc_port($host_id)
	{
		global $RESOURCE_INFO_TABLE;
		global $RootDir;
		global $event;
		
		$db = openqrm_get_db_connection();
		$recordSet = $db->Execute("select resource_vnc from " . $RESOURCE_INFO_TABLE . " where resource_vnc != '' and resource_vhostid=" . $host_id);
		if (!$recordSet)
			print $db->ErrorMsg();
		else {
			$ar_ids = array();
			while ($arr = $recordSet->FetchRow()) {
				foreach ($arr as $val) {
					$colon = strpos($val, ':');
					if ($colon !== false) {
						$vnc_info_arr = explode(":", $val);
						$val = $vnc_info_arr[1];
					}
					$ar_ids[] = $val;
				}
			}
			$i = 1;
			while ($i > 0) {
				if (in_array($i, $ar_ids) == false) {
					return $i;
					break;
				}
				$i++;
			}
		}
		$db->Close();
		
		
	}


// check when resources last send their statistics
// update state in case of timeout
	function check_all_states()
	{
		global $RESOURCE_INFO_TABLE;
		global $RESOURCE_TIME_OUT;
		global $RootDir;
		global $event;
		$resource_list = array();
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select resource_id, resource_lastgood, resource_state from $RESOURCE_INFO_TABLE");
		if (!$rs)
			$event->log("check_all_states", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			while (!$rs->EOF) {
				$resource_id = $rs->fields['resource_id'];
				$resource_lastgood = $rs->fields['resource_lastgood'];
				$resource_state = $rs->fields['resource_state'];
				$check_time = $_SERVER['REQUEST_TIME'];
				// get the HA-timeout per resource from the capabilites
				$custom_resource_ha_timeout = "";
				$resource_hat = new resource();
				$resource_hat->get_instance_by_id($resource_id);
				$custom_resource_ha_timeout = $resource_hat->get_resource_capabilities("HAT");
				if (strlen($custom_resource_ha_timeout)) {
					$RESOURCE_TIME_OUT = $custom_resource_ha_timeout;
				}
				
				// resolve errors for all active resources
				if (("$resource_state" == "active") && ($resource_id != 0)) {
					if (($check_time - $resource_lastgood) < $RESOURCE_TIME_OUT) {
						// resolve error event
						$event->resolve_by_resource("check_all_states", $resource_id);
					}
				}
				
				// check for statistics (errors) for all resources which are not offline
				// exclude manual added resources from the check !
				if (("$resource_state" != "off") && ("$resource_lastgood" != "-1")) {
					if (($check_time - $resource_lastgood) > $RESOURCE_TIME_OUT) {
						$resource_fields = array();
						$resource_fields["resource_state"] = "error";
						$resource_error = new resource();
						$resource_error->update_info($resource_id, $resource_fields);
						// log error event
						$event->log("check_all_states", $_SERVER['REQUEST_TIME'], 1, "resource.class.php", "Resource $resource_id is in error state", "", "", 0, 0, $resource_id);
						
						// check for plugin which may want to handle the error event
						$plugin = new plugin();
						$enabled_plugins = $plugin->started();
						foreach ($enabled_plugins as $index => $plugin_name) {
							$plugin_ha_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-ha-hook.php";
							if (file_exists($plugin_ha_hook)) {
								$event->log("check_all_states", $_SERVER['REQUEST_TIME'], 1, "resource.class.php", "Found $plugin_name handling the resource error.", "", "", 0, 0, $resource_id);
								require_once "$plugin_ha_hook";
								$ha_function = "openqrm_" . "$plugin_name" . "_ha_hook";
								$ha_function = str_replace("-", "_", $ha_function);
								$ha_function($resource_id);
							}
						}
					}
				}
				
				$rs->MoveNext();
			}
	}


// displays only idle resources
	function display_idle_overview($offset, $limit, $sort, $order)
	{
		global $RESOURCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		$recordSet = $db->SelectLimit("select * from $RESOURCE_INFO_TABLE where resource_state='active' and resource_imageid=1 order by $sort $order", $limit, $offset);
		$resource_array = array();
		if (!$recordSet) {
			$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($resource_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $resource_array;
	}


// displays the resource-overview
	function display_overview($offset, $limit, $sort, $order)
	{
		global $RESOURCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		$recordSet = $db->SelectLimit("select * from $RESOURCE_INFO_TABLE order by $sort $order", $limit, $offset);
		$resource_array = array();
		if (!$recordSet) {
			$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($resource_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $resource_array;
	}

//--------------------------------------------
	/**
	 * find a resource by id, name or mac
	 *
	 * @access public
	 * @param string $search
	 * @return array
	 */
//--------------------------------------------
	function find_resource($search)
	{
		global $RESOURCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		
		// replace glob wildcards with sql wildcards
		$search = str_replace('_', '\_', $search);
		$search = str_replace('%', '\%', $search);
		$search = str_replace('?', '_', $search);
		$search = str_replace('*', '%', $search);
		
		$sql = 'SELECT * FROM ' . $RESOURCE_INFO_TABLE;
		$sql .= ' WHERE resource_id LIKE ?';
		$sql .= ' OR resource_hostname LIKE ?';
		$sql .= ' OR resource_mac LIKE ?';
		
		// handle sql injection
		$sql = $db->db->Prepare($sql);
		
		$recordSet = $db->db->Execute($sql, array($search, $search, $search));
		$resource_array = array();
		if (!$recordSet) {
			$event->log("find_resource", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($resource_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $resource_array;
	}

//--------------------------------------------
	/**
	 * Get all resources assigned to a host
	 *
	 * @access public
	 * @param string $hostid
	 * @return array
	 */
//--------------------------------------------
	function get_resources_by_vhostid($hostid)
	{
		global $RESOURCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		
		$sql = 'SELECT resource_id FROM ' . $RESOURCE_INFO_TABLE;
		$sql .= ' WHERE resource_vhostid=?';
		$sql .= ' AND resource_id!=?';
		
		// handle sql injection
		$sql = $db->db->Prepare($sql);
		
		$recordSet = $db->db->Execute($sql, array($hostid, $hostid));
		$resource_array = array();
		if (!$recordSet) {
			$event->log("get_resource_by_vhostid", $_SERVER['REQUEST_TIME'], 2, "resource.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($resource_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $resource_array;
	}


// ---------------------------------------------------------------------------------

}

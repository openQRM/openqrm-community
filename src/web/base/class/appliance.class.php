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

// This class represents a applicance managed by openQRM
// The applicance abstrations consists of the combination of 
// - 1 boot-image (kernel.class)
// - 1 (or more) server-filesystem/rootfs (image.class)
// - requirements (cpu-number, cpu-speed, memory needs, etc)
// - configuration (clustered, high-available, deployment type, etc)
// - available and required resources (resource.class)


$RootDir = $_SERVER["DOCUMENT_ROOT"] . '/openqrm/base/';
require_once $RootDir . "/include/openqrm-database-functions.php";
require_once $RootDir . "/class/resource.class.php";
require_once $RootDir . "/class/virtualization.class.php";
require_once $RootDir . "/class/image.class.php";
require_once $RootDir . "/class/deployment.class.php";
require_once $RootDir . "/class/kernel.class.php";
require_once $RootDir . "/class/plugin.class.php";
require_once $RootDir . "/class/event.class.php";
require_once $RootDir . "/class/authblocker.class.php";

global $APPLIANCE_INFO_TABLE;
$event = new event();
global $event;

$appliance_start_timeout = 360;
global $appliance_start_timeout;

class appliance
{
	
	var $id = '';
	var $name = '';
	var $kernelid = '';
	var $imageid = '';
	var $starttime = '';
	var $stoptime = '';
	var $cpunumber = '';
	var $cpuspeed = '';
	var $cpumodel = '';
	var $memtotal = '';
	var $swaptotal = '';
	var $nics = '';
	var $capabilities = '';
	var $cluster = '';
	var $ssi = '';
	var $resources = '';
	var $highavailable = '';
	var $virtual = '';
	var $virtualization = '';
	var $virtualization_host = '';
	var $state = '';
	var $comment = '';
	var $wizard = '';
	var $event = '';


// ---------------------------------------------------------------------------------
// methods to create an instance of an appliance object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
	function get_instance($id, $name)
	{
		global $APPLIANCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		if ("$id" != "") {
			$appliance_array = $db->Execute("select * from $APPLIANCE_INFO_TABLE where appliance_id=$id");
		} else if ("$name" != "") {
			$appliance_array = $db->Execute("select * from $APPLIANCE_INFO_TABLE where appliance_name='$name'");
		} else {
			$error = '';
			foreach (debug_backtrace() as $key => $msg) {
				if ($key === 1) {
					$error .= '( ' . basename($msg['file']) . ' ' . $msg['line'] . ' )';
				}
				syslog(LOG_ERR, $msg['function'] . '() ' . basename($msg['file']) . ':' . $msg['line']);
			}
			$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Could not create instance of appliance without data " . $error, "", "", 0, 0, 0);
			return;
		}
		foreach ($appliance_array as $index => $appliance) {
			$this->id = $appliance["appliance_id"];
			$this->name = $appliance["appliance_name"];
			$this->kernelid = $appliance["appliance_kernelid"];
			$this->imageid = $appliance["appliance_imageid"];
			$this->starttime = $appliance["appliance_starttime"];
			$this->stoptime = $appliance["appliance_stoptime"];
			$this->cpunumber = $appliance["appliance_cpunumber"];
			$this->cpuspeed = $appliance["appliance_cpuspeed"];
			$this->cpumodel = $appliance["appliance_cpumodel"];
			$this->memtotal = $appliance["appliance_memtotal"];
			$this->swaptotal = $appliance["appliance_swaptotal"];
			$this->nics = $appliance["appliance_nics"];
			$this->capabilities = $appliance["appliance_capabilities"];
			$this->cluster = $appliance["appliance_cluster"];
			$this->ssi = $appliance["appliance_ssi"];
			$this->resources = $appliance["appliance_resources"];
			$this->highavailable = $appliance["appliance_highavailable"];
			$this->virtual = $appliance["appliance_virtual"];
			$this->virtualization = $appliance["appliance_virtualization"];
			$this->virtualization_host = $appliance["appliance_virtualization_host"];
			$this->state = $appliance["appliance_state"];
			$this->comment = $appliance["appliance_comment"];
			$this->wizard = $appliance["appliance_wizard"];
			$this->event = $appliance["appliance_event"];
		}
		return $this;
	}

// returns an appliance from the db selected by id
	function get_instance_by_id($id)
	{
		$this->get_instance($id, "");
		return $this;
	}

// returns an appliance from the db selected by iname
	function get_instance_by_name($name)
	{
		$this->get_instance("", $name);
		return $this;
	}

// special get_instance by virtualilzation type and resource
// avoiding performance loss by looping over all appliances
// returns an appliance from the db selected by id or name
	function get_instance_by_virtualization_and_resource($virtualization_id, $resource_id)
	{
		global $APPLIANCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		if (($resource_id == "") || ($virtualization_id == "")) {
			$event->log("get_instance_by_virtualization_and_resource", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Could not create instance of appliance without data", "", "", 0, 0, 0);
			return;
		}
		
		$appliance_array = $db->Execute("select * from " . $APPLIANCE_INFO_TABLE . " where appliance_virtualization='" . $virtualization_id . "' and appliance_resources=" . $resource_id);
		foreach ($appliance_array as $index => $appliance) {
			$this->id = $appliance["appliance_id"];
			$this->name = $appliance["appliance_name"];
			$this->kernelid = $appliance["appliance_kernelid"];
			$this->imageid = $appliance["appliance_imageid"];
			$this->starttime = $appliance["appliance_starttime"];
			$this->stoptime = $appliance["appliance_stoptime"];
			$this->cpunumber = $appliance["appliance_cpunumber"];
			$this->cpuspeed = $appliance["appliance_cpuspeed"];
			$this->cpumodel = $appliance["appliance_cpumodel"];
			$this->memtotal = $appliance["appliance_memtotal"];
			$this->swaptotal = $appliance["appliance_swaptotal"];
			$this->nics = $appliance["appliance_nics"];
			$this->capabilities = $appliance["appliance_capabilities"];
			$this->cluster = $appliance["appliance_cluster"];
			$this->ssi = $appliance["appliance_ssi"];
			$this->resources = $appliance["appliance_resources"];
			$this->highavailable = $appliance["appliance_highavailable"];
			$this->virtual = $appliance["appliance_virtual"];
			$this->virtualization = $appliance["appliance_virtualization"];
			$this->virtualization_host = $appliance["appliance_virtualization_host"];
			$this->state = $appliance["appliance_state"];
			$this->comment = $appliance["appliance_comment"];
			$this->wizard = $appliance["appliance_wizard"];
			$this->event = $appliance["appliance_event"];
		}
		return $this;
	}



// ---------------------------------------------------------------------------------
// general appliance methods
// ---------------------------------------------------------------------------------


// checks if given appliance id is free in the db
	function is_id_free($appliance_id)
	{
		global $APPLIANCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select appliance_id from $APPLIANCE_INFO_TABLE where appliance_id=$appliance_id");
		if (!$rs)
			$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "is_id_free: " . $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			if ($rs->EOF) {
				return true;
			} else {
				return false;
			}
	}


// adds appliance to the database
	function add($appliance_fields)
	{
		global $APPLIANCE_INFO_TABLE;
		global $event;
		global $RootDir;
		if (!is_array($appliance_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Appliance_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		// set stop time and status to now
		$now = $_SERVER['REQUEST_TIME'];
		$appliance_fields['appliance_stoptime'] = $now;
		$appliance_fields['appliance_state'] = 'stopped';
		$db = openqrm_get_db_connection();
		$result = $db->AutoExecute($APPLIANCE_INFO_TABLE, $appliance_fields, 'INSERT');
		if (!$result) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Failed adding new appliance to database", "", "", 0, 0, 0);
		} else {
			$appliance_id = $appliance_fields['appliance_id'];
			// add appliance hook
			$this->get_instance_by_id($appliance_id);
			// fill in the rest of the appliance info in the array for the plugin hook
			$appliance_fields["appliance_id"] = $this->id;
			$appliance_fields["appliance_name"] = $this->name;
			$appliance_fields["appliance_kernelid"] = $this->kernelid;
			$appliance_fields["appliance_imageid"] = $this->imageid;
			$appliance_fields["appliance_cpunumber"] = $this->cpunumber;
			$appliance_fields["appliance_cpuspeed"] = $this->cpuspeed;
			$appliance_fields["appliance_cpumodel"] = $this->cpumodel;
			$appliance_fields["appliance_memtotal"] = $this->memtotal;
			$appliance_fields["appliance_swaptotal"] = $this->swaptotal;
			$appliance_fields["appliance_nics"] = $this->nics;
			$appliance_fields["appliance_capabilities"] = $this->capabilities;
			$appliance_fields["appliance_cluster"] = $this->cluster;
			$appliance_fields["appliance_ssi"] = $this->ssi;
			$appliance_fields["appliance_resources"] = $this->resources;
			$appliance_fields["appliance_highavailable"] = $this->highavailable;
			$appliance_fields["appliance_virtual"] = $this->virtual;
			$appliance_fields["appliance_virtualization"] = $this->virtualization;
			$appliance_fields["appliance_virtualization_host"] = $this->virtualization_host;
			$appliance_fields["appliance_comment"] = $this->comment;
			$appliance_fields["appliance_wizard"] = $this->wizard;
			$appliance_fields["appliance_event"] = $this->event;
			// start the hook
			$plugin = new plugin();
			$enabled_plugins = $plugin->enabled();
			foreach ($enabled_plugins as $index => $plugin_name) {
				$plugin_start_appliance_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-appliance-hook.php";
				if (file_exists($plugin_start_appliance_hook)) {
					$event->log("add", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found plugin $plugin_name handling add-appliance event.", "", "", 0, 0, $this->resources);
					require_once "$plugin_start_appliance_hook";
					$appliance_function = "openqrm_" . "$plugin_name" . "_appliance";
					$appliance_function = str_replace("-", "_", $appliance_function);
					$appliance_function("add", $appliance_fields);
				}
			}
			
		}
	}



// for the wizard we have to provide a add method without running the hook in step 1
// which calls a separated method function to run the add hook after step 4
// adds appliance to the database
	function add_no_hook($appliance_fields)
	{
		global $APPLIANCE_INFO_TABLE;
		global $event;
		global $RootDir;
		if (!is_array($appliance_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Appliance_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		// set stop time and status to now
		$now = $_SERVER['REQUEST_TIME'];
		$appliance_fields['appliance_stoptime'] = $now;
		$appliance_fields['appliance_state'] = 'stopped';
		$db = openqrm_get_db_connection();
		$result = $db->AutoExecute($APPLIANCE_INFO_TABLE, $appliance_fields, 'INSERT');
		if (!$result) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Failed adding new appliance to database", "", "", 0, 0, 0);
		}
	}
	
	function run_add_hook($appliance_id)
	{
		global $APPLIANCE_INFO_TABLE;
		global $event;
		global $RootDir;
		$this->get_instance_by_id($appliance_id);
		// fill in the rest of the appliance info in the array for the plugin hook
		$appliance_fields["appliance_id"] = $this->id;
		$appliance_fields["appliance_name"] = $this->name;
		$appliance_fields["appliance_kernelid"] = $this->kernelid;
		$appliance_fields["appliance_imageid"] = $this->imageid;
		$appliance_fields["appliance_cpunumber"] = $this->cpunumber;
		$appliance_fields["appliance_cpuspeed"] = $this->cpuspeed;
		$appliance_fields["appliance_cpumodel"] = $this->cpumodel;
		$appliance_fields["appliance_memtotal"] = $this->memtotal;
		$appliance_fields["appliance_swaptotal"] = $this->swaptotal;
		$appliance_fields["appliance_nics"] = $this->nics;
		$appliance_fields["appliance_capabilities"] = $this->capabilities;
		$appliance_fields["appliance_cluster"] = $this->cluster;
		$appliance_fields["appliance_ssi"] = $this->ssi;
		$appliance_fields["appliance_resources"] = $this->resources;
		$appliance_fields["appliance_highavailable"] = $this->highavailable;
		$appliance_fields["appliance_virtual"] = $this->virtual;
		$appliance_fields["appliance_virtualization"] = $this->virtualization;
		$appliance_fields["appliance_virtualization_host"] = $this->virtualization_host;
		$appliance_fields["appliance_comment"] = $this->comment;
		$appliance_fields["appliance_wizard"] = $this->wizard;
		$appliance_fields["appliance_event"] = $this->event;
		// start the hook
		$plugin = new plugin();
		$enabled_plugins = $plugin->enabled();
		foreach ($enabled_plugins as $index => $plugin_name) {
			$plugin_start_appliance_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-appliance-hook.php";
			if (file_exists($plugin_start_appliance_hook)) {
				$event->log("add", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found plugin $plugin_name handling add-appliance event.", "", "", 0, 0, $this->resources);
				require_once "$plugin_start_appliance_hook";
				$appliance_function = "openqrm_" . "$plugin_name" . "_appliance";
				$appliance_function = str_replace("-", "_", $appliance_function);
				$appliance_function("add", $appliance_fields);
			}
		}
		
	}


// updates appliance in the database
	function update($appliance_id, $appliance_fields)
	{
		global $APPLIANCE_INFO_TABLE;
		global $RootDir;
		global $event;
		if ($appliance_id < 0 || !is_array($appliance_fields)) {
			$event->log("update", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Unable to update appliance $appliance_id", "", "", 0, 0, 0);
			return 1;
		}
		$db = openqrm_get_db_connection();
		unset($appliance_fields["appliance_id"]);
		$result = $db->AutoExecute($APPLIANCE_INFO_TABLE, $appliance_fields, 'UPDATE', "appliance_id = $appliance_id");
		if (!$result) {
			$event->log("update", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Failed updating appliance $appliance_id", "", "", 0, 0, 0);
		} else {
			// refresh appliance object from db
			$this->get_instance_by_id($appliance_id);
			// if this is virtualization host also update the resource vtype
			if (isset($appliance_fields['appliance_virtualization'])) {
				$host_appliance_virtualization = new virtualization();
				$host_appliance_virtualization->get_instance_by_id($appliance_fields['appliance_virtualization']);
				if (strpos($host_appliance_virtualization->name, " Host")) {
					$resource_fields["resource_vtype"] = $appliance_fields['appliance_virtualization'];
					$resource_fields["resource_vhostid"] = $this->resources;
					$host_appliance_resource = new resource();
					$host_appliance_resource->update_info($this->resources, $resource_fields);
				}
			}
			// update appliance hook
			// fill in the rest of the appliance info in the array for the plugin hook
			$appliance_fields["appliance_id"] = $this->id;
			$appliance_fields["appliance_name"] = $this->name;
			$appliance_fields["appliance_kernelid"] = $this->kernelid;
			$appliance_fields["appliance_imageid"] = $this->imageid;
			$appliance_fields["appliance_cpunumber"] = $this->cpunumber;
			$appliance_fields["appliance_cpuspeed"] = $this->cpuspeed;
			$appliance_fields["appliance_cpumodel"] = $this->cpumodel;
			$appliance_fields["appliance_memtotal"] = $this->memtotal;
			$appliance_fields["appliance_swaptotal"] = $this->swaptotal;
			$appliance_fields["appliance_nics"] = $this->nics;
			$appliance_fields["appliance_capabilities"] = $this->capabilities;
			$appliance_fields["appliance_cluster"] = $this->cluster;
			$appliance_fields["appliance_ssi"] = $this->ssi;
			$appliance_fields["appliance_resources"] = $this->resources;
			$appliance_fields["appliance_highavailable"] = $this->highavailable;
			$appliance_fields["appliance_virtual"] = $this->virtual;
			$appliance_fields["appliance_virtualization"] = $this->virtualization;
			$appliance_fields["appliance_virtualization_host"] = $this->virtualization_host;
			$appliance_fields["appliance_comment"] = $this->comment;
			$appliance_fields["appliance_wizard"] = $this->wizard;
			$appliance_fields["appliance_event"] = $this->event;
			// start the hook
			$plugin = new plugin();
			$enabled_plugins = $plugin->enabled();
			foreach ($enabled_plugins as $index => $plugin_name) {
				$plugin_update_appliance_hook = $RootDir . "/plugins/" . $plugin_name . "/openqrm-" . $plugin_name . "-appliance-hook.php";
				if (file_exists($plugin_update_appliance_hook)) {
					$event->log("update", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found plugin " . $plugin_name . " handling update-appliance event.", "", "", 0, 0, $this->resources);
					require_once "$plugin_update_appliance_hook";
					$appliance_function = "openqrm_" . "$plugin_name" . "_appliance";
					$appliance_function = str_replace("-", "_", $appliance_function);
					$appliance_function("update", $appliance_fields);
				}
			}
		}
	}

// removes appliance from the database
	function remove($appliance_id)
	{
		global $APPLIANCE_INFO_TABLE;
		global $RootDir;
		global $event;
		// remove appliance hook
		$this->get_instance_by_id($appliance_id);
		// fill in the rest of the appliance info in the array for the plugin hook
		$appliance_fields["appliance_id"] = $this->id;
		$appliance_fields["appliance_name"] = $this->name;
		$appliance_fields["appliance_kernelid"] = $this->kernelid;
		$appliance_fields["appliance_imageid"] = $this->imageid;
		$appliance_fields["appliance_cpunumber"] = $this->cpunumber;
		$appliance_fields["appliance_cpuspeed"] = $this->cpuspeed;
		$appliance_fields["appliance_cpumodel"] = $this->cpumodel;
		$appliance_fields["appliance_memtotal"] = $this->memtotal;
		$appliance_fields["appliance_swaptotal"] = $this->swaptotal;
		$appliance_fields["appliance_nics"] = $this->nics;
		$appliance_fields["appliance_capabilities"] = $this->capabilities;
		$appliance_fields["appliance_cluster"] = $this->cluster;
		$appliance_fields["appliance_ssi"] = $this->ssi;
		$appliance_fields["appliance_resources"] = $this->resources;
		$appliance_fields["appliance_highavailable"] = $this->highavailable;
		$appliance_fields["appliance_virtual"] = $this->virtual;
		$appliance_fields["appliance_virtualization"] = $this->virtualization;
		$appliance_fields["appliance_virtualization_host"] = $this->virtualization_host;
		$appliance_fields["appliance_comment"] = $this->comment;
		$appliance_fields["appliance_wizard"] = $this->wizard;
		$appliance_fields["appliance_event"] = $this->event;
		// be sure to free the image
		$appliance_image = new image();
		$appliance_image->get_instance_by_id($this->imageid);
		// only if image still exists
		if ($appliance_image->id > 0) {
			$appliance_image->set_active(0);
		}
		// start the hook
		$plugin = new plugin();
		$enabled_plugins = $plugin->enabled();
		foreach ($enabled_plugins as $index => $plugin_name) {
			$plugin_start_appliance_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-appliance-hook.php";
			if (file_exists($plugin_start_appliance_hook)) {
				$event->log("remove", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found plugin $plugin_name handling remove-appliance event.", "", "", 0, 0, $this->resources);
				require_once "$plugin_start_appliance_hook";
				$appliance_function = "openqrm_" . "$plugin_name" . "_appliance";
				$appliance_function = str_replace("-", "_", $appliance_function);
				$appliance_function("remove", $appliance_fields);
			}
		}
		
		// remove from db
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from $APPLIANCE_INFO_TABLE where appliance_id=$appliance_id");
		
		
	}

// removes appliance from the database by appliance_name
	function remove_by_name($appliance_name)
	{
		global $APPLIANCE_INFO_TABLE;
		global $RootDir;
		global $event;
		// remove appliance hook
		$this->get_instance_by_name($appliance_name);
		// fill in the rest of the appliance info in the array for the plugin hook
		$appliance_fields["appliance_id"] = $this->id;
		$appliance_fields["appliance_name"] = $this->name;
		$appliance_fields["appliance_kernelid"] = $this->kernelid;
		$appliance_fields["appliance_imageid"] = $this->imageid;
		$appliance_fields["appliance_cpunumber"] = $this->cpunumber;
		$appliance_fields["appliance_cpuspeed"] = $this->cpuspeed;
		$appliance_fields["appliance_cpumodel"] = $this->cpumodel;
		$appliance_fields["appliance_memtotal"] = $this->memtotal;
		$appliance_fields["appliance_swaptotal"] = $this->swaptotal;
		$appliance_fields["appliance_nics"] = $this->nics;
		$appliance_fields["appliance_capabilities"] = $this->capabilities;
		$appliance_fields["appliance_cluster"] = $this->cluster;
		$appliance_fields["appliance_ssi"] = $this->ssi;
		$appliance_fields["appliance_resources"] = $this->resources;
		$appliance_fields["appliance_highavailable"] = $this->highavailable;
		$appliance_fields["appliance_virtual"] = $this->virtual;
		$appliance_fields["appliance_virtualization"] = $this->virtualization;
		$appliance_fields["appliance_virtualization_host"] = $this->virtualization_host;
		$appliance_fields["appliance_comment"] = $this->comment;
		$appliance_fields["appliance_wizard"] = $this->wizard;
		$appliance_fields["appliance_event"] = $this->event;
		// be sure to free the image
		$appliance_image = new image();
		$appliance_image->get_instance_by_id($this->imageid);
		// only if image still exists
		if ($appliance_image->id > 0) {
			$appliance_image->set_active(0);
		}
		// start the hook
		$plugin = new plugin();
		$enabled_plugins = $plugin->enabled();
		foreach ($enabled_plugins as $index => $plugin_name) {
			$plugin_start_appliance_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-appliance-hook.php";
			if (file_exists($plugin_start_appliance_hook)) {
				$event->log("remove", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found plugin $plugin_name handling remove-appliance event.", "", "", 0, 0, $this->resources);
				require_once "$plugin_start_appliance_hook";
				$appliance_function = "openqrm_" . "$plugin_name" . "_appliance";
				$appliance_function = str_replace("-", "_", $appliance_function);
				$appliance_function("remove", $appliance_fields);
			}
		}
		
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from $APPLIANCE_INFO_TABLE where appliance_name='$appliance_name'");
	}


// starts an appliance -> assigns it to a resource
	function start($storage_authentication = NULL)
	{
		global $event;
		global $RootDir;
		$appliance_start_timeout = 360;
		if ($this->resources < 1) {
			$event->log("start", $_SERVER['REQUEST_TIME'], 1, "appliance.class.php", "No resource available for appliance " . $this->id, "", "", 0, 0, 0);
			return;
		}
		$start_with_authblocker = true;
		if (isset($storage_authentication)) {
			$start_with_authblocker = $storage_authentication;
		}
		
		$resource = new resource();
		$resource->get_instance_by_id($this->resources);
		$kernel = new kernel();
		$kernel->get_instance_by_id($this->kernelid);
		$image = new image();
		$image->get_instance_by_id($this->imageid);
		// set image to active
		$image->set_active(1);
		
		// update resource state to transition early
		$resource_fields = array();
		$resource_fields["resource_state"] = "transition";
		$resource_fields["resource_event"] = "reboot";
		$resource->update_info($resource->id, $resource_fields);
		
		// assign resource, wait a bit for the kernel to be assigned
		$resource->assign($resource->id, $kernel->id, $kernel->name, $image->id, $image->name);
		sleep(2);
		
		// storage authentication hook
		if ($start_with_authblocker) {
			$deployment = new deployment();
			$deployment->get_instance_by_type($image->type);
			$deployment_type = $deployment->type;
			$deployment_plugin_name = $deployment->storagetype;
			$storage_auth_hook = $RootDir . "/plugins/" . $deployment_plugin_name . "/openqrm-" . $deployment_type . "-auth-hook.php";
			if (file_exists($storage_auth_hook)) {
				$event->log("start", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found deployment type " . $deployment_type . " handling the start auth hook.", "", "", 0, 0, $this->resources);
				// create storage_auth_blocker if not existing already
				$authblocker = new authblocker();
				$authblocker->get_instance_by_image_name($image->name);
				if (!strlen($authblocker->id)) {
					$event->log("start", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Creating new authblocker for image " . $image->name . " / app id " . $this->id . ".", "", "", 0, 0, $this->resources);
					$ab_start_time = $_SERVER['REQUEST_TIME'];
					$ab_create_fields['ab_image_id'] = $this->imageid;
					$ab_create_fields['ab_image_name'] = $image->name;
					$ab_create_fields['ab_start_time'] = $ab_start_time;
					// get a new id
					$ab_create_fields['ab_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$authblocker->add($ab_create_fields);
				}
				$storage_auth_blocker_created = true;
				// run the auth hook
				require_once "$storage_auth_hook";
				storage_auth_function("start", $this->id);
			} else {
				$event->log("start", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "No storage-auth hook (" . $storage_auth_hook . ") available for deployment type " . $deployment_type . " for start auth hook.", "", "", 0, 0, $this->resources);
				$storage_auth_blocker_created = false;
			}
			// delay to be sure to have the storage hook run before the reboot
			if ($storage_auth_blocker_created) {
				$wait_for_storage_auth_loop = 0;
				while (true) {
					unset($check_authblocker);
					$check_authblocker = new authblocker();
					$check_authblocker->get_instance_by_image_name($image->name);
					if (strlen($check_authblocker->id)) {
						// ab still existing, check timeout
						if ($wait_for_storage_auth_loop > $appliance_start_timeout) {
							// remove authblocker
							$check_authblocker->remove($check_authblocker->id);
							// set appliance to stopped
							$image->set_active(0);
							$resource_fields = array();
							$resource_fields["resource_state"] = "active";
							$resource_fields["resource_event"] = "noop";
							$resource->update_info($resource->id, $resource_fields);
							$resource->assign($resource->id, "1", "default", "1", "idle");
							$event->log("start", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Failed starting server " . $this->id . ". Storage-authentication timed out!", "", "", 0, 0, $this->resources);
							sleep(2);
							break;
						}
						sleep(1);
						$wait_for_storage_auth_loop++;
					} else {
						// here we got the remove-auth-blocker message from the storage-auth hook
						// now we can be sure that storage auth ran before rebooting the resource
						$event->log("start", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Storage authentication for image " . $image->name . " succeeded, assigning the resource now.", "", "", 0, 0, $this->resources);
						break;
					}
				}
			}
		} else {
			$event->log("start", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Starting without storage authentication for image " . $image->name . ".", "", "", 0, 0, $this->resources);
		}
		
		// unset stoptime + update starttime + state
		$now = $_SERVER['REQUEST_TIME'];
		$appliance_fields = array();
		$appliance_fields['appliance_stoptime'] = '';
		$appliance_fields['appliance_starttime'] = $now;
		$appliance_fields['appliance_state'] = 'active';
		$this->update($this->id, $appliance_fields);
		
		// start appliance hook
		// fill in the rest of the appliance info in the array for the plugin hook
		$appliance_fields["appliance_id"] = $this->id;
		$appliance_fields["appliance_name"] = $this->name;
		$appliance_fields["appliance_kernelid"] = $this->kernelid;
		$appliance_fields["appliance_imageid"] = $this->imageid;
		$appliance_fields["appliance_cpunumber"] = $this->cpunumber;
		$appliance_fields["appliance_cpuspeed"] = $this->cpuspeed;
		$appliance_fields["appliance_cpumodel"] = $this->cpumodel;
		$appliance_fields["appliance_memtotal"] = $this->memtotal;
		$appliance_fields["appliance_swaptotal"] = $this->swaptotal;
		$appliance_fields["appliance_nics"] = $this->nics;
		$appliance_fields["appliance_capabilities"] = $this->capabilities;
		$appliance_fields["appliance_cluster"] = $this->cluster;
		$appliance_fields["appliance_ssi"] = $this->ssi;
		$appliance_fields["appliance_resources"] = $this->resources;
		$appliance_fields["appliance_highavailable"] = $this->highavailable;
		$appliance_fields["appliance_virtual"] = $this->virtual;
		$appliance_fields["appliance_virtualization"] = $this->virtualization;
		$appliance_fields["appliance_virtualization_host"] = $this->virtualization_host;
		$appliance_fields["appliance_comment"] = $this->comment;
		$appliance_fields["appliance_wizard"] = $this->wizard;
		$appliance_fields["appliance_event"] = $this->event;
		// start the hook
		$plugin = new plugin();
		$enabled_plugins = $plugin->enabled();
		foreach ($enabled_plugins as $index => $plugin_name) {
			$plugin_start_appliance_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-appliance-hook.php";
			if (file_exists($plugin_start_appliance_hook)) {
				$event->log("start", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found plugin $plugin_name handling start-appliance event.", "", "", 0, 0, $this->resources);
				require_once "$plugin_start_appliance_hook";
				$appliance_function = "openqrm_" . "$plugin_name" . "_appliance";
				$appliance_function = str_replace("-", "_", $appliance_function);
				ob_start();
				$appliance_function("start", $appliance_fields);
				$start_error = ob_get_contents();
				ob_end_clean();
				if ($start_error !== '') {
					$event->log("start", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "Plugin " . $plugin_name . " caused " . $start_error, "", "", 0, 0, $this->resources);
				}
				$event->log("start", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Plugin " . $plugin_name . " completed start-appliance event.", "", "", 0, 0, $this->resources);
			}
		}
		// delay a bit to make sure all appliance hooks have been executed or at least arrived in the queue
		sleep(2);
		// reboot resource after the plugin start hook ran
		$resource->get_instance_by_id($this->resources);
		$resource->send_command("$resource->ip", "reboot");
		
		
	}


// stops an appliance -> de-assigns it to idle
	function stop()
	{
		global $event;
		global $RootDir;
		$resource = new resource();
		$resource->get_instance_by_id($this->resources);
		$resource->assign($resource->id, "1", "default", "1", "idle");
		
		// update stoptime + state
		$now = $_SERVER['REQUEST_TIME'];
		$appliance_fields = array();
		$appliance_fields['appliance_stoptime'] = $now;
		$appliance_fields['appliance_state'] = 'stopped';
		$this->update($this->id, $appliance_fields);
		
		// update resource state to transition
		$resource_fields = array();
		$resource_fields["resource_state"] = "transition";
		$resource_fields["resource_event"] = "reboot";
		$resource->update_info($resource->id, $resource_fields);
		
		// stop appliance hook
		// fill in the rest of the appliance info in the array for the plugin hook
		$appliance_fields["appliance_id"] = $this->id;
		$appliance_fields["appliance_name"] = $this->name;
		$appliance_fields["appliance_kernelid"] = $this->kernelid;
		$appliance_fields["appliance_imageid"] = $this->imageid;
		$appliance_fields["appliance_cpunumber"] = $this->cpunumber;
		$appliance_fields["appliance_cpuspeed"] = $this->cpuspeed;
		$appliance_fields["appliance_cpumodel"] = $this->cpumodel;
		$appliance_fields["appliance_memtotal"] = $this->memtotal;
		$appliance_fields["appliance_swaptotal"] = $this->swaptotal;
		$appliance_fields["appliance_nics"] = $this->nics;
		$appliance_fields["appliance_capabilities"] = $this->capabilities;
		$appliance_fields["appliance_cluster"] = $this->cluster;
		$appliance_fields["appliance_ssi"] = $this->ssi;
		$appliance_fields["appliance_resources"] = $this->resources;
		$appliance_fields["appliance_highavailable"] = $this->highavailable;
		$appliance_fields["appliance_virtual"] = $this->virtual;
		$appliance_fields["appliance_virtualization"] = $this->virtualization;
		$appliance_fields["appliance_virtualization_host"] = $this->virtualization_host;
		$appliance_fields["appliance_comment"] = $this->comment;
		$appliance_fields["appliance_wizard"] = $this->wizard;
		$appliance_fields["appliance_event"] = $this->event;
		
		// stop the hook
		$plugin = new plugin();
		$enabled_plugins = $plugin->enabled();
		foreach ($enabled_plugins as $index => $plugin_name) {
			$plugin_stop_appliance_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-appliance-hook.php";
			if (file_exists($plugin_stop_appliance_hook)) {
				$event->log("stop", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found plugin $plugin_name handling stop-appliance event.", "", "", 0, 0, $this->resources);
				require_once "$plugin_stop_appliance_hook";
				$appliance_function = "openqrm_" . "$plugin_name" . "_appliance";
				$appliance_function = str_replace("-", "_", $appliance_function);
				$appliance_function("stop", $appliance_fields);
			}
		}
		
		// delay a bit to make sure all appliance hooks have been executed or at least arrived in the queue
		sleep(2);
		$resource->send_command("$resource->ip", "reboot");
		
		// storage authentication hook
		$image = new image();
		$image->get_instance_by_id($this->imageid);
		$deployment = new deployment();
		$deployment->get_instance_by_type($image->type);
		$deployment_type = $deployment->type;
		$deployment_plugin_name = $deployment->storagetype;
		$storage_auth_hook = "$RootDir/plugins/$deployment_plugin_name/openqrm-$deployment_type-auth-hook.php";
		if (file_exists($storage_auth_hook)) {
			$event->log("stop", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found deployment type $deployment_type handling the stop auth hook.", "", "", 0, 0, $this->resources);
			require_once "$storage_auth_hook";
			storage_auth_function("stop", $this->id);
		}
		// set image to deactive
		$image->set_active(0);
		
		
	}


// returns appliance name by appliance_id
	function get_name($appliance_id)
	{
		global $APPLIANCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		$appliance_set = $db->Execute("select appliance_name from $APPLIANCE_INFO_TABLE where appliance_id=$appliance_id");
		if (!$appliance_set) {
			$event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "get_name: " . $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$appliance_set->EOF) {
				return $appliance_set->fields["appliance_name"];
			} else {
				return "idle";
			}
		}
	}

// returns capabilities string by appliance_id
	function get_capabilities($appliance_id)
	{
		global $APPLIANCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		$appliance_set = $db->Execute("select appliance_capabilities from $APPLIANCE_INFO_TABLE where appliance_id=$appliance_id");
		if (!$appliance_set) {
			$event->log("get_capabilities", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "get_capabilities: " . $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if ((!$appliance_set->EOF) && ($appliance_set->fields["appliance_capabilities"] != "")) {
				return $appliance_set->fields["appliance_capabilities"];
			} else {
				return "0";
			}
		}
	}


// returns the number of appliances per virtualization type
	function get_count_per_virtualization($virtualization_id)
	{
		global $APPLIANCE_INFO_TABLE;
		$count = 0;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select count(appliance_id) as num from $APPLIANCE_INFO_TABLE where appliance_virtualization='$virtualization_id'");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


// returns the number of all appliances
	function get_count()
	{
		global $APPLIANCE_INFO_TABLE;
		$count = 0;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select count(appliance_id) as num from $APPLIANCE_INFO_TABLE");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


// returns the number of active appliances
	function get_count_active()
	{
		global $APPLIANCE_INFO_TABLE;
		$count = 0;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select count(appliance_id) as num from " . $APPLIANCE_INFO_TABLE . " where appliance_state='active' or appliance_resources=0");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


// returns a list of all appliance names
	function get_list()
	{
		global $APPLIANCE_INFO_TABLE;
		$query = "select appliance_id, appliance_name from $APPLIANCE_INFO_TABLE";
		
		return openqrm_db_get_result_double($query);
		
	}


// returns a list of all appliance ids
	function get_all_ids()
	{
		global $APPLIANCE_INFO_TABLE;
		global $event;
		$appliance_list = array();
		$query = "select appliance_id from $APPLIANCE_INFO_TABLE";
		$db = openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "get_all_ids: " . $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			while (!$rs->EOF) {
				$appliance_list[] = $rs->fields;
				$rs->MoveNext();
			}
		return $appliance_list;
	}


// returns a list of all appliance ids used by a specific resource
	function get_ids_per_resource($resource_id)
	{
		global $APPLIANCE_INFO_TABLE;
		global $event;
		$appliance_list = array();
		$query = "select appliance_id from " . $APPLIANCE_INFO_TABLE . " where appliance_resources=" . $resource_id;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			//$event->log("get_ids_per_resource", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "get_ids_per_resource: ".$db->ErrorMsg(), "", "", 0, 0, 0);
			$rs = 0;
		else
			while (!$rs->EOF) {
				$appliance_list[] = $rs->fields;
				$rs->MoveNext();
			}
		return $appliance_list;
	}

// returns a list of all appliance ids used by a specific image
	function get_ids_per_image($image_id)
	{
		global $APPLIANCE_INFO_TABLE;
		global $event;
		if ($image_id == "") {
			return;
		}
		$appliance_list = array();
		$query = "select appliance_id from " . $APPLIANCE_INFO_TABLE . " where appliance_imageid=" . $image_id;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs) {
			$event->log("get_ids_per_image", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "get_ids_per_image: " . $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$rs->EOF) {
				$appliance_list[] = $rs->fields;
				$rs->MoveNext();
			}
		}
		return $appliance_list;
	}


// returns a list of all appliance ids of a specific virtualization type
	function get_ids_per_virtualization($virtualization_id)
	{
		global $APPLIANCE_INFO_TABLE;
		global $event;
		$appliance_list = array();
		$query = "select appliance_id from " . $APPLIANCE_INFO_TABLE . " where appliance_virtualization='$virtualization_id'";
		$db = openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			//$event->log("get_ids_per_virtualization", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "get_ids_per_resource: ".$db->ErrorMsg(), "", "", 0, 0, 0);
			$rs = 0;
		else
			while (!$rs->EOF) {
				$appliance_list[] = $rs->fields;
				$rs->MoveNext();
			}
		return $appliance_list;
	}


// find a resource fitting to the appliance
	function find_resource($appliance_virtualization)
	{
		global $event;
		$found_new_resource = 0;
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($appliance_virtualization);
		$event->log("find_resource", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Trying to find a new resource type $virtualization->name for appliance $this->name .", "", "", 0, 0, 0);
		// we are searching for physical systems when we want to deploy a virtualization host
		if (strstr($virtualization->name, "Host")) {
			$appliance_virtualization = 1;
		}
		$resource_tmp = new resource();
		$resource_list = $resource_tmp->get_list();
		$resource = new resource();
		$appliance = new appliance();
		foreach ($resource_list as $index => $resource_db) {
			$resource->get_instance_by_id($resource_db["resource_id"]);
			if (($resource->id > 0) && ("$resource->imageid" == "1") && ("$resource->state" == "active")) {
				$used_by = $appliance->get_ids_per_resource($resource->id);
				if ($used_by) {
					continue;
				}
				$new_resource_id = $resource->id;
				// check resource-type
				$restype_id = $resource->vtype;
				if ($restype_id == $appliance_virtualization) {
					// check the rest of the required parameters for the appliance
					
					// cpu-number
					if ((strlen($this->cpunumber)) && (strcmp($this->cpunumber, "0"))) {
						if (strcmp($this->cpunumber, $resource->cpunumber)) {
							$event->log("find_resource", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found new resource $resource->id type $virtualization->name for appliance $this->name but it has the wrong CPU-number, skipping.", "", "", 0, 0, 0);
							continue;
						}
					}
					// cpu-speed
					if ((strlen($this->cpuspeed)) && (strcmp($this->cpuspeed, "0"))) {
						if (strcmp($this->cpuspeed, $resource->cpuspeed)) {
							$event->log("find_resource", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found new resource $resource->id type $virtualization->name for appliance $this->name but it has the wrong CPU-speed, skipping.", "", "", 0, 0, 0);
							continue;
						}
					}
					// cpu-model
					if ((strlen($this->cpumodel)) && (strcmp($this->cpumodel, "0"))) {
						if (strcmp($this->cpumodel, $resource->cpumodel)) {
							$event->log("find_resource", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found new resource $resource->id type $virtualization->name for appliance $this->name but it has the wrong CPU-model, skipping.", "", "", 0, 0, 0);
							continue;
						}
					}
					// memtotal
					if ((strlen($this->memtotal)) && (strcmp($this->memtotal, "0"))) {
						if (strcmp($this->memtotal, $resource->memtotal)) {
							$event->log("find_resource", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found new resource $resource->id type $virtualization->name for appliance $this->name but it has the wrong amount of Memory, skipping.", "", "", 0, 0, 0);
							continue;
						}
					}
					// swaptotal
					if ((strlen($this->swaptotal)) && (strcmp($this->swaptotal, "0"))) {
						if (strcmp($this->swaptotal, $resource->swaptotal)) {
							$event->log("find_resource", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found new resource $resource->id type $virtualization->name for appliance $this->name but it has the wrong amount of Swap, skipping.", "", "", 0, 0, 0);
							continue;
						}
					}
					// nics
					if ((strlen($this->nics)) && (strcmp($this->nics, "0"))) {
						if (strcmp($this->nics, $resource->nics)) {
							$event->log("find_resource", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found new resource $resource->id type $virtualization->name for appliance $this->name but it has the wrong nic count, skipping.", "", "", 0, 0, 0);
							continue;
						}
					}
					
					$found_new_resource = 1;
					$event->log("find_resource", $_SERVER['REQUEST_TIME'], 5, "appliance.class.php", "Found new resource $resource->id type $virtualization->name for appliance $this->name .", "", "", 0, 0, 0);
					break;
				}
			}
		}
		// in case no resources are available log another ha-error event !
		if ($found_new_resource == 0) {
			$event->log("find_resource", $_SERVER['REQUEST_TIME'], 4, "appliance.class.php", "Could not find a free resource type $virtualization->name for appliance $this->name !", "", "", 0, 0, 0);
			return $this;
		}
		
		// if we find an resource which fits to the appliance we update it
		$appliance_fields = array();
		$appliance_fields['appliance_resources'] = $new_resource_id;
		$this->update($this->id, $appliance_fields);
		
		return $this;
	}


// displays the appliance-overview per type
	function display_overview_per_virtualization($virtualization_id, $offset, $limit, $sort, $order)
	{
		global $APPLIANCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		$recordSet = $db->SelectLimit("select * from $APPLIANCE_INFO_TABLE where appliance_virtualization='$virtualization_id' order by $sort $order", $limit, $offset);
		$appliance_array = array();
		if (!$recordSet) {
			$event->log("display_overview_per_virtualization", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "display_overview_per_virtualization: " . $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($appliance_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $appliance_array;
	}


// displays the appliance-overview
	function display_overview($offset, $limit, $sort, $order)
	{
		global $APPLIANCE_INFO_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		$recordSet = $db->SelectLimit("select * from $APPLIANCE_INFO_TABLE order by $sort $order", $limit, $offset);
		$appliance_array = array();
		if (!$recordSet) {
			$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "appliance.class.php", "display_overview: " . $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($appliance_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $appliance_array;
	}


// ---------------------------------------------------------------------------------

}


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

// This class represents a image_authentication object in openQRM
// its for registering to set (remove) the authenication of an image
// after its appliance stopped

$RootDir = $_SERVER["DOCUMENT_ROOT"] . '/openqrm/base/';

#exit quickly when queries hit an unconfigured openQRM
require_once($RootDir.'/class/file.handler.class.php');
$file = new file_handler();

if ($file->exists($RootDir . '/unconfigured')) {
	return '';
}

require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

global $IMAGE_AUTHENTICATION_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS = $openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class image_authentication
{
	
	var $id = '';
	var $image_id = '';
	var $resource_id = '';
	var $auth_type = '';
	// -> 0 = image-root
	// -> 1 = image-deployment

	
	var $is_blocked=false;

// ---------------------------------------------------------------------------------
// methods to create an instance of a image_authentication object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
	function get_instance($id, $image_id)
	{
		global $IMAGE_AUTHENTICATION_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		if ("$id" != "") {
			$image_authentication_array = $db->Execute("select * from $IMAGE_AUTHENTICATION_TABLE where ia_id=$id");
		} else if ("$image_id" != "") {
			$image_authentication_array = $db->Execute("select * from $IMAGE_AUTHENTICATION_TABLE where ia_image_id=$image_id");
		} else {
			$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "image_authentication.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
			$debug_backtrace = debug_backtrace();
			array_walk($debug_backtrace, create_function('$a,$b', 'syslog(LOG_ERR, "{$a[\'function\']}()(".basename($a[\'file\']).":{$a[\'line\']}); ");'));
			return;
		}
		
		foreach ($image_authentication_array as $index => $image_authentication) {
			$this->id = $image_authentication["ia_id"];
			$this->image_id = $image_authentication["ia_image_id"];
			$this->resource_id = $image_authentication["ia_resource_id"];
			$this->auth_type = $image_authentication["ia_auth_type"];
		}
		return $this;
	}

// returns an image_authentication from the db selected by id
	function get_instance_by_id($id)
	{
		$this->get_instance($id, "");
		return $this;
	}

// returns an image_authentication from the db selected by the image_id
	function get_instance_by_image_id($image_id)
	{
		$this->get_instance("", $image_id);
		return $this;
	}

// ---------------------------------------------------------------------------------
// general image_authentication methods
// ---------------------------------------------------------------------------------


// checks if given image_authentication id is free in the db
	function is_id_free($image_authentication_id)
	{
		global $IMAGE_AUTHENTICATION_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select ia_id from $IMAGE_AUTHENTICATION_TABLE where ia_id=$image_authentication_id");
		if (!$rs)
			$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "image_authentication.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			if ($rs->EOF) {
				return true;
			} else {
				return false;
			}
	}


// adds image_authentication to the database
	function add($image_authentication_fields)
	{
		global $IMAGE_AUTHENTICATION_TABLE;
		global $event;
		if (!is_array($image_authentication_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "image_authentication.class.php", "image_authentication_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$image_id = $image_authentication_fields['ia_image_id'];
		$auth_type = $image_authentication_fields['ia_auth_type'];
		
		// before we add we check that it is uniq per image
		// so there can just be 2 image_authentications per image, rootfs + deployment
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select ia_id from $IMAGE_AUTHENTICATION_TABLE where ia_image_id=$image_id and ia_auth_type='$auth_type'");
		if (!$rs) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "image_authentication.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if ($rs->EOF) {
				$event->log("add", $_SERVER['REQUEST_TIME'], 5, "image_authentication.class.php", "Adding image_authentication for image id $image_id type $auth_type.", "", "", 0, 0, 0);
				$result = $db->AutoExecute($IMAGE_AUTHENTICATION_TABLE, $image_authentication_fields, 'INSERT');
				if (!$result) {
					$event->log("add", $_SERVER['REQUEST_TIME'], 2, "image_authentication.class.php", "Failed adding new image_authentication to database", "", "", 0, 0, 0);
				}
				
			} else {
				$event->log("add", $_SERVER['REQUEST_TIME'], 2, "image_authentication.class.php", "Not adding image_authentication for image id $image_id type $auth_type since it already is registered.", "", "", 0, 0, 0);
			}
		}
	}


// removes image_authentication from the database
	function remove($image_authentication_id)
	{
		global $IMAGE_AUTHENTICATION_TABLE;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from $IMAGE_AUTHENTICATION_TABLE where ia_id=$image_authentication_id");
	}


// returns the number of image_authentications for an image_authentication type
	function get_count()
	{
		global $IMAGE_AUTHENTICATION_TABLE;
		$count = 0;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select count(id) as num from $IMAGE_AUTHENTICATION_TABLE");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


// returns a list of all image_authentication names
	function get_list()
	{
		global $IMAGE_AUTHENTICATION_TABLE;
		$query = "select id, image_id from $IMAGE_AUTHENTICATION_TABLE";
		
		return openqrm_db_get_result_double($query);
		
	}


// returns a list of all image_authentication ids
	function get_all_ids()
	{
		global $IMAGE_AUTHENTICATION_TABLE;
		global $event;
		$image_authentication_list = array();
		$query = "select ia_id from $IMAGE_AUTHENTICATION_TABLE";
		$db = openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "image_authentication.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			while (!$rs->EOF) {
				$image_authentication_list[] = $rs->fields;
				$rs->MoveNext();
			}
		return $image_authentication_list;
		
	}




// checks all image_authentications and de-authenticates them if 
// its resource (the appliance resource) is idle/active again
	function check_all_image_authentication()
	{
		global $IMAGE_AUTHENTICATION_TABLE;
		global $event;
		global $RootDir;
		
		// $event->log("check_all_image_authentication", $_SERVER['REQUEST_TIME'], 5, "image_authentication.class.php", "Checking all image_authentications", "", "", 0, 0, 0);
		$ia_id_ar = $this->get_all_ids();
		foreach ($ia_id_ar as $ia_list) {
			
			$ia_auth_id = $ia_list['ia_id'];
			$ia_auth = new image_authentication();
			$ia_auth->get_instance_by_id($ia_auth_id);
			$event->log("check_all_image_authentication", $_SERVER['REQUEST_TIME'], 5, "image_authentication.class.php", "Checking image_authentication '$ia_auth_id'", "", "", 0, 0, 0);
			
			// get data
			$image = new image();
			if ($ia_auth->image_id > 0) {
				$image->get_instance_by_id($ia_auth->image_id);
			} else {
				$image->get_instance_by_id($ia_auth_id);
			}
			$image_name = $image->name;
			$image_id = $image->id;
			
			$deployment = new deployment();
			$deployment->get_instance_by_type($image->type);
			$deployment_type = $deployment->type;
			$deployment_plugin_name = $deployment->storagetype;
			$storage_auth_hook = "$RootDir/plugins/$deployment_plugin_name/openqrm-$deployment_type-auth-hook.php";
			
			$resource_id = $ia_auth->resource_id;
			$resource = new resource();
			$resource->get_instance_by_id($resource_id);
			
			// check for root or deployment auth
			switch ($ia_auth->auth_type) {
				case 0:
					// run storage_auth_stop (rootfs)
					if ((!strcmp($resource->state, "active")) && ($resource->imageid == 1)) {
						$event->log("check_all_image_authentication", $_SERVER['REQUEST_TIME'], 5, "image_authentication.class.php", "Resource $resource_id idle again, stop-auth $image_name ($image_id)", "", "", 0, 0, $resource_id);
						// include storage-plugin auth-hook
						if (file_exists($storage_auth_hook)) {
							$event->log("check_all_image_authentication", $_SERVER['REQUEST_TIME'], 5, "image_authentication.class.php", "Deployment type $deployment_type handling stop-auth hook $image_id.", "", "", 0, 0, $resource_id);
							require_once "$storage_auth_hook";
							storage_auth_stop($image->id);
							// remove image_authentication
							$ia_auth->remove($ia_auth_id);
							
						}
					}
					else {
						//if($ia_auth->is_blocked){
						// resource is still on rebooting
						$event->log("check_all_image_authentication", $_SERVER['REQUEST_TIME'], 5, "image_authentication.class.php", "Resource $resource_id still rebooting, waiting for stop-auth $image_name", "", "", 0, 0, $resource_id);
					}
					break;
				
				
				case 1:
					// run storage_auth_stop (deployment export)
					// we stop the deployment hook when the resource is active or active/idle
					// -> in both states it for sure does not need the deployment mount any more
					if (!strcmp($resource->state, "active")) {
						$event->log("check_all_image_authentication", $_SERVER['REQUEST_TIME'], 5, "image_authentication.class.php", "Resource $resource_id active, stop-deployment auth $image_name", "", "", 0, 0, $resource_id);
						// include storage-plugin auth-hook
						if (file_exists($storage_auth_hook)) {
							$event->log("check_all_image_authentication", $_SERVER['REQUEST_TIME'], 5, "image_authentication.class.php", "Deployment type $deployment_type handling stop-deployment auth hook $image_id.", "", "", 0, 0, $resource_id);
							require_once "$storage_auth_hook";
							storage_auth_deployment_stop($image->id);
							
							if ($ia_auth->image_id > 0) {
								$image->get_instance_by_id($ia_auth->image_id);
								$image_name = $image->name;
								$image_id = $image->id;
							}
							
							if ($deployment_type != 'tmpfs-deployment') {
								// remove all install from or transfer to deployment parameters
								$image->set_deployment_parameters("IMAGE_INSTALL_FROM_NFS", "");
								$image->set_deployment_parameters("IMAGE_TRANSFER_TO_NFS", "");
								$image->set_deployment_parameters("IMAGE_INSTALL_FROM_LOCAL", "");
								$image->set_deployment_parameters("IMAGE_TRANSFER_TO_LOCAL", "");
							}
							// remove image_authentication
							$ia_auth->remove($ia_auth_id);
						}
					} else {
						// resource is still on rebooting
						$event->log("check_all_image_authentication", $_SERVER['REQUEST_TIME'], 5, "image_authentication.class.php", "Resource $resource_id is still rebooting, waiting for stop-deployment auth $image_name", "", "", 0, 0, $resource_id);
					}
					break;
				
			}
			
		}
		
	}


// displays the image_authentication-overview
	function display_overview($offset, $limit, $sort, $order)
	{
		global $IMAGE_AUTHENTICATION_TABLE;
		global $event;
		$db = openqrm_get_db_connection();
		$recordSet = $db->SelectLimit("select * from $IMAGE_AUTHENTICATION_TABLE order by $sort $order", $limit, $offset);
		$image_authentication_array = array();
		if (!$recordSet) {
			$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "image_authentication.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($image_authentication_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $image_authentication_array;
	}


// ---------------------------------------------------------------------------------

}


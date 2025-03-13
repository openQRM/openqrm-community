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
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/event.class.php";

$event = new event();
global $event;

/**
 * This class represents a filesystem-image (rootfs)
 * In combination with a kernel it can be deployed to a resource
 * via the appliance.class
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 * @author M. Rechenburg, A. Kuballa
 * @version 1.1 added documentation
 */
class image
{
	
	/**
	 * image id
	 * @access protected
	 * @var int
	 */
	var $id = '';
	/**
	 * image name
	 * @access protected
	 * @var string
	 */
	var $name = '';
	/**
	 * image version
	 * @access protected
	 * @var string
	 */
	var $version = '';
	/**
	 * image type
	 * @access protected
	 * @var string
	 */
	var $type = '';
	/**
	 * image rootdevice
	 * @access protected
	 * @var string
	 */
	var $rootdevice = '';
	/**
	 * image root filesystem
	 * @access protected
	 * @var string
	 */
	var $rootfstype = '';
	/**
	 * image size (MB)
	 * @access protected
	 * @var integer
	 */
	var $size = 0;
	/**
	 * storage id
	 * @access protected
	 * @var int
	 */
	var $storageid = '';
	/**
	 * deployment type
	 * @access protected
	 * @var string
	 */
	var $deployment_type = '';
	/**
	 * deployment parameter
	 * @access protected
	 * @var string
	 */
	var $deployment_parameter = '';
	/**
	 * image is shared?
	 * @access protected
	 * @var bool
	 */
	var $isshared = '';
	/**
	 * image is active?
	 * @access protected
	 * @var bool
	 */
	var $isactive = '';
	/**
	 * image comment
	 * @access protected
	 * @var string
	 */
	var $comment = '';
	/**
	 * image capabilities
	 * @access protected
	 * @var string
	 */
	var $capabilities = '';
	
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
	function __construct($deployment_type = null)
	{
		$this->init($deployment_type);
	}
	
	//--------------------------------------------------
	/**
	 * init storage environment
	 * @access public
	 */
	//--------------------------------------------------
	function init($deployment_type = null)
	{
		global $IMAGE_INFO_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $IMAGE_INFO_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
		
		$this->deployment_type = $deployment_type;
	}
	
	//--------------------------------------------------
	/**
	 * get an instance of an image object from db
	 * @access public
	 * @param int $id
	 * @param string $name
	 * @return object
	 */
	//--------------------------------------------------
	function get_instance($id, $name)
	{
		$db = openqrm_get_db_connection();
		
		if (@strlen(@$this->deployment_type->type) > 0) {
			$where_extra_sql = " and image_type = '" . $this->deployment_type->type . "'";
		} else if (@strlen(@$this->deployment_type) > 0) {
			$where_extra_sql = " and image_type = '" . $this->deployment_type . "'";
		} else {
			$where_extra_sql = null;
		}
		
		if ("$id" != "") {
			$image_array = $db->Execute("select * from $this->_db_table where image_id=$id" . $where_extra_sql);
		} else if ("$name" != "") {
			$image_array = $db->Execute("select * from $this->_db_table where image_name='$name'" . $where_extra_sql);
		} else {
			$error = '';
			foreach (debug_backtrace() as $key => $msg) {
				if ($key === 1) {
					$error .= '( ' . basename($msg['file']) . ' ' . $msg['line'] . ' )';
				}
				syslog(LOG_ERR, $msg['function'] . '() ' . basename($msg['file']) . ':' . $msg['line']);
			}
			$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Could not create instance of image without data " . $error, "", "", 0, 0, 0);
			return;
		}
		foreach ($image_array as $index => $image) {
			$this->id = $image["image_id"];
			$this->name = $image["image_name"];
			$this->version = $image["image_version"];
			$this->type = $image["image_type"];
			$this->rootdevice = $image["image_rootdevice"];
			$this->rootfstype = $image["image_rootfstype"];
			$this->size = $image["image_size"];
			$this->storageid = $image["image_storageid"];
			$this->deployment_parameter = $image["image_deployment_parameter"];
			$this->isshared = $image["image_isshared"];
			$this->isactive = $image["image_isactive"];
			$this->comment = $image["image_comment"];
			$this->capabilities = $image["image_capabilities"];
		}
		return $this;
	}
	
	//--------------------------------------------------
	/**
	 * get an instance of an image by id
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
	 * get an instance of an image by name
	 * @access public
	 * @param int $id
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
	 * add a new image
	 * @access public
	 * @param array $image_fields
	 */
	//--------------------------------------------------
	function add($image_fields)
	{
		if (!is_array($image_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Image_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		// set to not active by default when adding
		$image_fields['image_isactive'] = 0;
		$db = openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $image_fields, 'INSERT');
		if (!$result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Failed adding new image to database", "", "", 0, 0, 0);
		}
	}
	
	//--------------------------------------------------
	/**
	 * update an image
	 * <code>
	 * $fields = array();
	 * $fields['image_name'] = 'somename';
	 * $fields['image_version'] = '1.1';
	 * $fields['image_type'] = 1;
	 * $fields['image_rootdevice'] = 1;
	 * $fields['image_rootfstype'] = 1;
	 * $fields['image_size'] = 0;
	 * $fields['image_storageid'] = 1;
	 * $fields['image_deployment_parameter'] = 1;
	 * $fields['image_isshared'] = 1;
	 * $fields['image_isactive'] = 0;
	 * $fields['image_comment'] = 'sometext';
	 * $fields['image_capabilities'] = 'sometext';
	 * $image = new image();
	 * $image->update(1, $fields);
	 * </code>
	 * @access public
	 * @param int $image_id
	 * @param array $image_fields
	 * @return bool
	 */
	//--------------------------------------------------
	function update($image_id, $image_fields)
	{
		if ($image_id < 0 || !is_array($image_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Unable to update image $image_id", "", "", 0, 0, 0);
			return 1;
		}
		$db = openqrm_get_db_connection();
		unset($image_fields["image_id"]);
		$result = $db->AutoExecute($this->_db_table, $image_fields, 'UPDATE', "image_id = $image_id");
		if (!$result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "image.class.php", "Failed updating image $image_id", "", "", 0, 0, 0);
		}
	}
	
	//--------------------------------------------------
	/**
	 * remove an image by id
	 * @access public
	 * @param int $image_id
	 */
	//--------------------------------------------------
	function remove($image_id)
	{
		// do not remove the idle + openqrm image
		if (($image_id == 0) || ($image_id == 1)) {
			return;
		}
		// remove auth file
		$CMD = "rm -f $this->_base_dir/openqrm/web/action/image-auth/iauth.$image_id";
		exec($CMD);
		// remove from db
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where image_id=$image_id");
	}
	
	//--------------------------------------------------
	/**
	 * remove an image by name
	 * @access public
	 * @param string $image_name
	 */
	//--------------------------------------------------
	function remove_by_name($image_name)
	{
		// do not remove the idle + openqrm image
		if (($image_name == "openqrm") || ($image_name == "idle")) {
			return;
		}
		// remove auth file
		$rem_image = new image();
		$rem_image->get_instance_by_name($image_name);
		$rem_image_id = $rem_image->id;
		$CMD = "rm -f $this->_base_dir/openqrm/web/action/image-auth/iauth.$rem_image_id";
		exec($CMD);
		// remove from db
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where image_name='$image_name'");
		
	}
	
	//--------------------------------------------------
	/**
	 * get image name by id
	 * @access public
	 * @param int $image_id
	 * @return string
	 */
	//--------------------------------------------------
	function get_name($image_id)
	{
		$db = openqrm_get_db_connection();
		$image_set = $db->Execute("select image_name from $this->_db_table where image_id=$image_id");
		if (!$image_set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$image_set->EOF) {
				return $image_set->fields["image_name"];
			} else {
				return "idle";
			}
		}
	}
	
	
	//--------------------------------------------------
	/**
	 * set the image is currently in use
	 * @access public
	 * @param int $active
	 */
	//--------------------------------------------------
	function set_active($active)
	{
		$this->get_instance_by_id($this->id);
		$image_fields = array();
		$image_fields["image_isactive"] = $active;
		$this->update($this->id, $image_fields);
	}
	
	
	//--------------------------------------------------
	/**
	 * set the deployment parameters of an image
	 * @access public
	 * @param string $key
	 * @param string $value
	 */
	//--------------------------------------------------
	function set_deployment_parameters($key, $value)
	{
		$this->get_instance_by_id($this->id);
		$image_deployment_parameter = $this->deployment_parameter;
		$key = trim($key);
		if (strstr($image_deployment_parameter, $key)) {
			// change
			$cp1 = trim($image_deployment_parameter);
			$cp2 = strstr($cp1, $key);
			$keystr = "$key=\"";
			$endmark = "\"";
			$cp3 = str_replace($keystr, "", $cp2);
			$endpos = strpos($cp3, $endmark);
			$cp = substr($cp3, 0, $endpos);
			$new_image_deployment_parameter = str_replace("$key=\"$cp\"", "$key=\"$value\"", $image_deployment_parameter);
		} else {
			// add
			$new_image_deployment_parameter = "$image_deployment_parameter $key=\"$value\"";
		}
		$image_fields = array();
		$image_fields["image_deployment_parameter"] = "$new_image_deployment_parameter";
		$this->update($this->id, $image_fields);
		
	}
	
	
	
	//--------------------------------------------------
	/**
	 * gets a deployment parameter of an image
	 * @access public
	 * @param string $key
	 * @return string $value
	 */
	//--------------------------------------------------
	function get_deployment_parameter($key)
	{
		
		$image_deployment_parameter = $this->deployment_parameter;
		$key = trim($key);
		if (strstr($image_deployment_parameter, $key)) {
			// change
			$cp1 = trim($image_deployment_parameter);
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
	
	
	
	
	
	
	//--------------------------------------------------
	/**
	 * get image capabilities by id
	 * @access public
	 * @param int $image_id
	 * @return string
	 */
	//--------------------------------------------------
	function get_capabilities($image_id)
	{
		$db = openqrm_get_db_connection();
		$image_set = $db->Execute("select image_capabilities from $this->_db_table where image_id=$image_id");
		if (!$image_set) {
			$this->_event->log("get_capabilities", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if ((!$image_set->EOF) && ($image_set->fields["image_capabilities"] != "")) {
				return $image_set->fields["image_capabilities"];
			} else {
				return "0";
			}
		}
	}
	
	//--------------------------------------------------
	/**
	 * get number of images per type
	 * @access public
	 * @param string $deployment_type
	 * @return int
	 */
	//--------------------------------------------------
	function get_count_per_type($deployment_type_str)
	{
		$count = 0;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select count(image_id) as num from $this->_db_table where image_type='" . $deployment_type_str . "'");
		if (!$rs) {
			$this->_event->log("get_count_per_type", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}
	
	
	//--------------------------------------------------
	/**
	 * get number of images
	 * @access public
	 * @return int
	 */
	//--------------------------------------------------
	function get_count()
	{
		$count = 0;
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("select count(image_id) as num from $this->_db_table");
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}
	
	//--------------------------------------------------
	/**
	 * get an array of all image names
	 * <code>
	 * $image = new image();
	 * $arr = $image->get_list();
	 * // $arr[0]['value']
	 * // $arr[0]['label']
	 * </code>
	 * @access public
	 * @return array
	 */
	//--------------------------------------------------
	function get_list()
	{
		$query = "select image_id, image_name from $this->_db_table order by image_id ASC";
		
		return openqrm_db_get_result_double($query);
		
	}
	
	
	//--------------------------------------------------
	/**
	 * get an array of all image ids
	 * <code>
	 * $image = new image();
	 * $arr = $image->get_ids();
	 * // $arr['value']
	 * </code>
	 * @access public
	 * @return array
	 */
	//--------------------------------------------------
	function get_ids()
	{
		global $event;
		$image_array = array();
		$query = "select image_id from $this->_db_table";
		$db = openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			while (!$rs->EOF) {
				$image_array[] = $rs->fields;
				$rs->MoveNext();
			}
		return $image_array;
	}
	
	//--------------------------------------------------
	/**
	 * get an array of all image ids on a storage
	 * <code>
	 * $image = new image();
	 * $arr = $image->get_ids_by_storage($storage_id);
	 * // $arr['value']
	 * </code>
	 * @access public
	 * @return array
	 */
	//--------------------------------------------------
	function get_ids_by_storage($storage_id)
	{
		global $event;
		$image_array = array();
		$query = "select image_id from $this->_db_table where image_storageid=$storage_id";
		$db = openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			while (!$rs->EOF) {
				$image_array[] = $rs->fields;
				$rs->MoveNext();
			}
		return $image_array;
	}
	
	
	//--------------------------------------------------
	/**
	 * get an array of all image ids on a storage
	 * <code>
	 * $image = new image();
	 * $arr = $image->get_ids_by_storage($storage_id);
	 * // $arr['value']
	 * </code>
	 * @access public
	 * @return array
	 */
	//--------------------------------------------------
	function get_ids_by_type($deployment_type_str)
	{
		global $event;
		$image_array = array();
		$query = "select image_id from $this->_db_table where image_type='" . $deployment_type_str . "'";
		$db = openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_ids_by_type", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			while (!$rs->EOF) {
				$image_array[] = $rs->fields;
				$rs->MoveNext();
			}
		return $image_array;
	}
	
	
	
	//--------------------------------------------------
	/**
	 * get an array of images per type
	 * @access public
	 * @param string $deployment_type
	 * @param int $offset
	 * @param int $limit
	 * @param string $sort
	 * @param enum $order [ASC/DESC]
	 * @return array
	 */
	//--------------------------------------------------
	function display_overview_per_type($deployment_type_str, $offset, $limit, $sort, $order)
	{
		$db = openqrm_get_db_connection();
		$recordSet = $db->SelectLimit("select * from $this->_db_table where image_id > 1 and image_type='" . $deployment_type_str . "' order by $sort $order", $limit, $offset);
		$image_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview_per_type", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($image_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $image_array;
	}
	
	
	
	
	//--------------------------------------------------
	/**
	 * get an array of images
	 * @access public
	 * @param int $offset
	 * @param int $limit
	 * @param string $sort
	 * @param enum $order [ASC/DESC]
	 * @return array
	 */
	//--------------------------------------------------
	function display_overview($offset, $limit, $sort, $order)
	{
		$db = openqrm_get_db_connection();
		$recordSet = $db->SelectLimit("select * from $this->_db_table where image_id > 1 order by $sort $order", $limit, $offset);
		$image_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "image.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($image_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $image_array;
	}
	
	//--------------------------------------------------
	/**
	 * generate a random password for images
	 * @access public
	 * @param int $length
	 * @return string
	 */
	//--------------------------------------------------
	function generatePassword($length)
	{
		// start with a blank password
		$password = "";
		// define possible characters
		$possible = "abcdfghjkmnpqrstvwxyzABCDEFGHIYKLMNOPQRSTVWXYZ";
		$numbers = "0123456789";
		$special = "-_";
		// set up a counter
		$i = 0;
		// add random characters to $password until $length is reached
		while ($i < $length) {
			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);
			// we don't want this character if it's already in the password
			if (!strstr($password, $char)) {
				$password .= $char;
				$i++;
			}
			$char = substr($numbers, mt_rand(0, strlen($numbers) - 1), 1);
			if (!strstr($password, $char)) {
				$password .= $char;
				$i++;
			}
			$char = substr($special, mt_rand(0, strlen($special) - 1), 1);
			if (!strstr($password, $char)) {
				$password .= $char;
				$i++;
			}
		}
		// done!
		return $password;
	}
	
	//--------------------------------------------------
	/**
	 * set crypted root-password from string
	 * @access public
	 * @param int $id
	 * @param string $passwd
	 */
	//--------------------------------------------------
	function set_root_password($id, $passwd)
	{
		$this->get_instance_by_id($id);
		if (stripos($this->version, 'Windows') !== false) {
			$content = "<?php\n";
			$content .= "\$thisfile = basename(\$_SERVER['PHP_SELF']);\n";
			$content .= "echo \"" . $passwd . "\";\n";
			$content .= "unlink(\$thisfile);\n";
			$content .= "?>\n";
			file_put_contents($this->_base_dir . "/openqrm/web/action/image-auth/iauth." . $id . ".php", $content);
		} else {
			$CMD = $this->_base_dir . "/openqrm/sbin/openqrm-crypt " . $passwd . " > " . $this->_base_dir . "/openqrm/web/action/image-auth/iauth." . $id . ".php";
			exec($CMD);
		}
	}
	
	//--------------------------------------------------
	/**
	 * check image is network deployment
	 * @access public
	 * @return bool
	 */
	//--------------------------------------------------
	function is_network_deployment()
	{
		$hook = $this->_base_dir . '/openqrm/web/boot-service/image.' . $this->type . '.php';
		if (file_exists($hook)) {
			require_once($hook);
			$function = "get_" . str_replace("-", "_", $this->type) . "_is_network_deployment";
			return $function();
		} else {
			return false;
		}
	}
	
	
	
	
	//--------------------------------------------------
	/**
	 * get an array of the supported image OS versions
	 * @access public
	 * @return bool
	 */
	//--------------------------------------------------
	function get_os_version()
	{
		
		$image_version_arr[] = array("label" => "Linux", "value" => "linux");
		$image_version_arr[] = array("label" => "Windows", "value" => "windows");
		
		$image_version_arr[] = array("label" => "CentOS 7", "value" => "linux_centos7");
		$image_version_arr[] = array("label" => "CentOS 6", "value" => "linux_centos4");
		$image_version_arr[] = array("label" => "CentOS 5", "value" => "linux_centos5");
		
		$image_version_arr[] = array("label" => "Debian 8", "value" => "linux_debian8");
		$image_version_arr[] = array("label" => "Debian 7", "value" => "linux_debian7");
		$image_version_arr[] = array("label" => "Debian 6", "value" => "linux_debian6");
		
		$image_version_arr[] = array("label" => "openSUSE Linux 13.x", "value" => "linux_opensuse13");
		$image_version_arr[] = array("label" => "openSUSE Linux 12.x", "value" => "linux_opensuse12");
		$image_version_arr[] = array("label" => "openSUSE Linux 11.x", "value" => "linux_opensuse11");
		$image_version_arr[] = array("label" => "openSUSE Linux 10.x", "value" => "linux_opensuse10");
		
		$image_version_arr[] = array("label" => "Red Hat Enterprise Linux 7", "value" => "linux_rhel7");
		$image_version_arr[] = array("label" => "Red Hat Enterprise Linux 6", "value" => "linux_rhel6");
		$image_version_arr[] = array("label" => "Red Hat Enterprise Linux 5", "value" => "linux_rhel5");
		$image_version_arr[] = array("label" => "Red Hat Enterprise Linux 4", "value" => "linux_rhel4");
		
		$image_version_arr[] = array("label" => "SUSE Linux Enterprise 12", "value" => "linux_suse12");
		$image_version_arr[] = array("label" => "SUSE Linux Enterprise 11", "value" => "linux_suse11");
		$image_version_arr[] = array("label" => "SUSE Linux Enterprise 10", "value" => "linux_suse10");
		
		$image_version_arr[] = array("label" => "Ubuntu 15.10", "value" => "linux_ubuntu1510");
		$image_version_arr[] = array("label" => "Ubuntu 15.04", "value" => "linux_ubuntu1504");
		$image_version_arr[] = array("label" => "Ubuntu 14.10", "value" => "linux_ubuntu1410");
		$image_version_arr[] = array("label" => "Ubuntu 14.04 LTS", "value" => "linux_ubuntu1404");
		$image_version_arr[] = array("label" => "Ubuntu 13.10", "value" => "linux_ubuntu1310");
		$image_version_arr[] = array("label" => "Ubuntu 13.04", "value" => "linux_ubuntu1304");
		$image_version_arr[] = array("label" => "Ubuntu 12.10", "value" => "linux_ubuntu1210");
		$image_version_arr[] = array("label" => "Ubuntu 12.04 LTS", "value" => "linux_ubuntu1204");
		$image_version_arr[] = array("label" => "Ubuntu 11.10", "value" => "linux_ubuntu1110");
		$image_version_arr[] = array("label" => "Ubuntu 11.04", "value" => "linux_ubuntu1104");
		$image_version_arr[] = array("label" => "Ubuntu 10.10", "value" => "linux_ubuntu1010");
		$image_version_arr[] = array("label" => "Ubuntu 10.04 LTS ", "value" => "linux_ubuntu1004");
		
		$image_version_arr[] = array("label" => "Windows Server 2016", "value" => "windows_server2016");
		$image_version_arr[] = array("label" => "Windows 10", "value" => "windows_10");
		$image_version_arr[] = array("label" => "Windows Server 2012 R2", "value" => "windows_server2012r2");
		$image_version_arr[] = array("label" => "Windows Server 2012", "value" => "windows_server2012");
		$image_version_arr[] = array("label" => "Windows 8.1", "value" => "windows_81");
		$image_version_arr[] = array("label" => "Windows 8", "value" => "windows_8");
		$image_version_arr[] = array("label" => "Windows 7", "value" => "windows_7");
		$image_version_arr[] = array("label" => "Windows Server 2008", "value" => "windows_2008");
		$image_version_arr[] = array("label" => "Windows Vista", "value" => "windows_vista");
		$image_version_arr[] = array("label" => "Windows Server 2003", "value" => "windows_server2003");
		$image_version_arr[] = array("label" => "Windows XP", "value" => "windows_xp");
		
		$image_version_arr[] = array("label" => "Other", "value" => "Other");
		return $image_version_arr;
	}
	
	
}

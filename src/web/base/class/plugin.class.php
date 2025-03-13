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
require_once($RootDir . 'include/openqrm-server-config.php');
require_once "$RootDir/class/folder.class.php";
require_once "$RootDir/class/event.class.php";

/**
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 * @author M. Rechenburg, A. Kuballa
 * @version 1.1 added documentation
 */
class plugin
{
	/**
	 * path to openqrm webdir
	 * @access protected
	 * @var string
	 */
	var $_web_dir;
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
	function __construct()
	{
		$this->init();
	}
	
	//--------------------------------------------------
	/**
	 * init plugin environment
	 * @access public
	 */
	//--------------------------------------------------
	function init()
	{
		global $RootDir, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_web_dir = $RootDir;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}
	
	//--------------------------------------------------
	/**
	 * return a list of available plugins
	 * @access public
	 * @return array
	 */
	//--------------------------------------------------
	// return a list of available plugins
	function available()
	{
		$plugin_array = array();
		$plugins = new Folder();
		$plugins->getFolders("$this->_base_dir/openqrm/plugins/");
		foreach ($plugins->folders as $plugin) {
			array_push($plugin_array, $plugin);
		}
		return $plugin_array;
	}
	
	//--------------------------------------------------
	/**
	 * return a list of enabled plugins
	 * @access public
	 * @return array
	 */
	//--------------------------------------------------
	function enabled()
	{
		$plugin_array = array();
		$plugins = new Folder();
		$plugins->getFolders($this->_web_dir . 'plugins/');
		foreach ($plugins->folders as $plugin) {
			if ("$plugin" != "aa_plugins") {
				$plugin = basename(dirname(realpath($this->_web_dir . 'plugins/' . $plugin)));
				array_push($plugin_array, $plugin);
			}
			
		}
		return $plugin_array;
	}
	
	//--------------------------------------------------
	/**
	 * return a list of started plugins
	 * @access public
	 * @return array
	 */
	//--------------------------------------------------
	function started()
	{
		$plugin_array = array();
		$plugins = new Folder();
		$plugins->getFolders($this->_web_dir . 'plugins/');
		foreach ($plugins->folders as $plugin) {
			if ("$plugin" != "aa_plugins") {
				$plugin = basename(dirname(realpath($this->_web_dir . 'plugins/' . $plugin)));
				$plugin_status = "$this->_web_dir/plugins/$plugin/.running";
				if (file_exists($plugin_status)) {
					array_push($plugin_array, $plugin);
				}
			}
			
		}
		return $plugin_array;
	}
	
	//--------------------------------------------------
	/**
	 * get plugin config
	 * @access public
	 * @param string $plugin_name
	 * @return array
	 */
	//--------------------------------------------------
	function get_config($plugin_name)
	{
		$ar_Return = array();
		
		// values not configurable
		$blacklist[] = 'OPENQRM_PLUGIN_VERSION';
		$blacklist[] = 'OPENQRM_PLUGIN_BUILD_REQUIREMENTS';
		$blacklist[] = 'OPENQRM_PLUGIN_DESCRIPTION';
		$blacklist[] = 'OPENQRM_PLUGIN_TYPE';
		$blacklist[] = 'OPENQRM_PLUGIN_DEPENDENCIES';
		$blacklist[] = 'OPENQRM_PLUGIN_PLUGIN_DEPENDENCIES';
		$blacklist[] = 'OPENQRM_PLUGIN_STATE_DIRS';
		$blacklist[] = 'OPENQRM_PLUGIN_STATE_FILES';
		
		$plugin_config = "$this->_base_dir/openqrm/plugins/$plugin_name/etc/openqrm-plugin-$plugin_name.conf";
		$plugin_description = "";
		$plugin_type = "";
		$config_array = file($plugin_config);
		foreach ($config_array as $index => $line) {
			if (strstr($line, "OPENQRM_PLUGIN_DESCRIPTION")) {
				$plugin_description = str_replace("OPENQRM_PLUGIN_DESCRIPTION=", "", $line);
				$plugin_description = str_replace("\"", "", $plugin_description);
				$plugin_description = trim($plugin_description);
			} else if (strstr($line, "OPENQRM_PLUGIN_TYPE")) {
				$plugin_type = str_replace("OPENQRM_PLUGIN_TYPE=", "", $line);
				$plugin_type = str_replace("\"", "", $plugin_type);
				$plugin_type = trim($plugin_type);
			} else if (strstr($line, "OPENQRM_PLUGIN_VERSION")) {
				$plugin_version = str_replace("OPENQRM_PLUGIN_VERSION=", "", $line);
				$plugin_version = str_replace("\"", "", $plugin_version);
				$plugin_version = trim($plugin_version);
			} // check plugin is configurable
			else if ((strpos($line, 'OPENQRM_') === 0 || stripos($line, str_replace('-', '_', $plugin_name)) === 0) && !in_array(substr($line, 0, strpos($line, '=')), $blacklist)) {
				$ar_Return['configurable'] = true;
			}
		}
		$ar_Return['type'] = $plugin_type;
		$ar_Return['version'] = $plugin_version;
		$ar_Return['description'] = $plugin_description;
		return $ar_Return;
	}
	
	//--------------------------------------------------
	/**
	 * get plugin dependencies
	 * @access public
	 * @param string $plugin_name
	 * @return array
	 */
	//--------------------------------------------------
	function get_dependencies($plugin_name)
	{
		$ar_Return = array();
		$plugin_config = "$this->_base_dir/openqrm/plugins/$plugin_name/etc/openqrm-plugin-$plugin_name.conf";
		$plugin_dependencies = "";
		$config_array = file($plugin_config);
		foreach ($config_array as $index => $line) {
			// plugin deps
			if (strstr($line, "OPENQRM_PLUGIN_PLUGIN_DEPENDENCIES")) {
				$plugin_dependencies = str_replace("OPENQRM_PLUGIN_PLUGIN_DEPENDENCIES=", "", $line);
				$plugin_dependencies = str_replace("\"", "", $plugin_dependencies);
				$plugin_dependencies = trim($plugin_dependencies);
			}
		}
		$ar_Return['dependencies'] = $plugin_dependencies;
		return $ar_Return;
	}
	
	
}

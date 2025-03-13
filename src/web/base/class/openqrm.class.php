<?php
/**
 * openQRM Class
 *
 * openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.
 *
 * All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.
 *
 * This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
 * The latest version of this license can be found here: src/doc/LICENSE.txt
 *
 * By using this software, you acknowledge having read this license and agree to be bound thereby.
 *
 * http://openqrm-enterprise.com
 *
 * Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class openqrm
{
	/**
	 * Absolute path to istall dir
	 * @access protected
	 * @var string
	 */
	protected $basedir;
	/**
	 * Absolute path to class dir
	 * @access protected
	 * @var string
	 */
	protected $classdir;
	/**
	 * Absolute path to web dir
	 * @access protected
	 * @var string
	 */
	protected $webdir;
	/**
	 * Absolute uri
	 * @access protected
	 * @var string
	 */
	protected $baseurl;
	/**
	 * openQRM config
	 * @access protected
	 * @var string
	 */
	protected $config;
	/**
	 * DB object
	 * @access private
	 * @var object
	 */
	private $db;
	/**
	 * file object
	 * @access private
	 * @var object
	 */
	private $file;
	/**
	 * admin user object
	 * @access private
	 * @var object
	 */
	private $admin;
	/**
	 * current user object
	 * @access private
	 * @var object
	 */
	private $user;
	/**
	 * name of db tables
	 * @access private
	 * @var array
	 */
	private $table;
	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @param object $file
	 * @param object $user
	 * @param object $response
	 * @access public
	 */
	//--------------------------------------------
	function __construct($file, $user, $response)
	{
		if ((file_exists("/etc/init.d/openqrm")) && (is_link("/etc/init.d/openqrm"))) {
			$this->basedir = dirname(dirname(dirname(readlink("/etc/init.d/openqrm"))));
		} else {
			$this->basedir = "/usr/share/openqrm";
		}
		$this->response = $response;
		$this->classdir = $this->basedir . '/web/base/class';
		$this->webdir = $_SERVER["DOCUMENT_ROOT"] . '/openqrm/base';
		$this->baseurl = '/openqrm/base';
		$this->config = $this->parse_conf($this->basedir . '/etc/openqrm-server.conf', 'OPENQRM_');
		
		$this->table['appliance'] = 'appliance_info';
		$this->table['deployment'] = 'deployment_info';
		$this->table['event'] = 'event_info';
		$this->table['image'] = 'image_info';
		$this->table['kernel'] = 'kernel_info';
		$this->table['resource'] = 'resource_info';
		$this->table['storage'] = 'storage_info';
		$this->table['virtualization'] = 'virtualization_info';
		
		// used regexes
		$this->regex['name'] = '~^[a-z0-9]+$~i';
		$this->regex['hostname'] = '~^[a-z0-9]{1,}[a-z0-9-]*[a-z0-9]$~i';
		$this->regex['comment'] = '/^[a-z 0-9()._-]+$/i';
		
		$this->file = $file;
		$this->user = $user;
		// load object
		
	}
	
	//--------------------------------------------
	/**
	 * Init
	 *
	 * @access public
	 */
	//--------------------------------------------
	function init()
	{
		require_once($this->classdir . '/appliance.class.php');
		require_once($this->classdir . '/deployment.class.php');
		require_once($this->classdir . '/event.class.php');
		require_once($this->classdir . '/image.class.php');
		require_once($this->classdir . '/kernel.class.php');
		require_once($this->classdir . '/plugin.class.php');
		require_once($this->classdir . '/resource.class.php');
		require_once($this->classdir . '/storage.class.php');
		require_once($this->classdir . '/virtualization.class.php');
	}
	
	//--------------------------------------------
	/**
	 * Get object attributes
	 *
	 * @access public
	 * @param string $attrib name of attrib to return
	 * @param string $key name of attrib key to return
	 * @return mixed
	 */
	//--------------------------------------------
	function get($attrib, $key = null)
	{
		if (isset($this->$attrib)) {
			$attrib = $this->$attrib;
			if (isset($key)) {
				if (isset($attrib[$key])) {
					return $attrib[$key];
				}
			} else {
				return $attrib;
			}
		}
	}
	
	//--------------------------------------------
	/**
	 * Set object attributes
	 *
	 * @access public
	 * @param string $attrib name of attrib to return
	 * @param string $key name of attrib key to set
	 * @return bool
	 */
	//--------------------------------------------
	function set($attrib, $value, $key = null)
	{
		if (isset($this->$attrib)) {
			if (isset($key)) {
				$tmp = &$this->$attrib;
				if (isset($tmp[$key])) {
					$tmp[$key] = $value;
					return true;
				} else {
					return false;
				}
			} else {
				$this->$attrib = $value;
				return true;
			}
		} else {
			return false;
		}
	}
	
	
	//--------------------------------------------
	/**
	 * converts csv string to associative array
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function string_to_array($string, $element_delimiter = '|', $value_delimiter = '=')
	{
		$results = array();
		$array = explode($element_delimiter, $string);
		foreach ($array as $result) {
			$element = explode($value_delimiter, $result);
			if (isset($element[1])) {
				$results[$element[0]] = $element[1];
			}
		}
		return $results;
	}
	
	
	
	//--------------------------------------------
	/**
	 * Get db object
	 *
	 * @access protected
	 * @return object db
	 */
	//--------------------------------------------
	protected function db()
	{
		if (!isset($this->db)) {
			require_once($this->classdir . '/db.class.php');
			$this->db = new db($this);
		}
		return $this->db;
	}
	
	//--------------------------------------------
	/**
	 * Get file object
	 *
	 * @access public
	 * @return file_handler
	 */
	//--------------------------------------------
	function file()
	{
		return $this->file;
	}
	
	//--------------------------------------------
	/**
	 * Get admin object
	 *
	 * @access public
	 * @return file_handler
	 */
	//--------------------------------------------
	function admin()
	{
		if (!isset($this->admin)) {
			$user = $this->user();
			$class = get_class($user);
			$admin = new $class('openqrm');
			$this->admin = $admin;
		}
		return $this->admin;
	}
	
	//--------------------------------------------
	/**
	 * Get user object
	 *
	 * @access public
	 * @return file_handler
	 */
	//--------------------------------------------
	function user()
	{
		return $this->user;
	}
	
	//--------------------------------------------
	/**
	 * Get role object (plugin loader)
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @return openqrm_role
	 */
	//--------------------------------------------
	function role($response)
	{
		require_once($this->classdir . '/openqrm.role.class.php');
		$role = new openqrm_role($this, $response);
		return $role;
	}
	
	//--------------------------------------------
	/**
	 * Get appliance object
	 *
	 * @access public
	 * @return object appliance
	 */
	//--------------------------------------------
	function appliance()
	{
		require_once($this->classdir . '/appliance.class.php');
		return new appliance();
	}
	
	//--------------------------------------------
	/**
	 * Get deployment object
	 *
	 * @access public
	 * @return object deployment
	 */
	//--------------------------------------------
	function deployment()
	{
		require_once($this->classdir . '/deployment.class.php');
		return new deployment();
	}
	
	//--------------------------------------------
	/**
	 * Get event object
	 *
	 * @access public
	 * @return object event
	 */
	//--------------------------------------------
	function event()
	{
		require_once($this->classdir . '/event.class.php');
		return new event();
	}
	
	//--------------------------------------------
	/**
	 * Get image object
	 *
	 * @access public
	 * @return object image
	 */
	//--------------------------------------------
	function image()
	{
		require_once($this->classdir . '/image.class.php');
		return new image();
	}
	
	//--------------------------------------------
	/**
	 * Get kernel object
	 *
	 * @access public
	 * @return object kernel
	 */
	//--------------------------------------------
	function kernel()
	{
		require_once($this->classdir . '/kernel.class.php');
		return new kernel();
	}
	
	//--------------------------------------------
	/**
	 * Get plugin object
	 *
	 * @access public
	 * @return object plugin
	 */
	//--------------------------------------------
	function plugin()
	{
		require_once($this->classdir . '/plugin.class.php');
		return new plugin();
	}
	
	//--------------------------------------------
	/**
	 * Get resource object
	 *
	 * @access public
	 * @return object resource
	 */
	//--------------------------------------------
	function resource()
	{
		require_once($this->classdir . '/resource.class.php');
		return new resource();
	}
	
	//--------------------------------------------
	/**
	 * Get storage object
	 *
	 * @access public
	 * @return object storage
	 */
	//--------------------------------------------
	function storage()
	{
		require_once($this->classdir . '/storage.class.php');
		return new storage();
	}
	
	//--------------------------------------------
	/**
	 * Get virtualization object
	 *
	 * @access public
	 * @return object virtualization
	 */
	//--------------------------------------------
	function virtualization()
	{
		require_once($this->classdir . '/virtualization.class.php');
		return new virtualization();
	}
	
	//--------------------------------------------
	/**
	 * Get openqrm server object
	 *
	 * @access public
	 * @return object openqrm_server
	 */
	//--------------------------------------------
	function server()
	{
		require_once($this->classdir . '/openqrm_server.class.php');
		return new openqrm_server();
	}
	
	//--------------------------------------------
	/**
	 * Parse an openqrm config file
	 *
	 * @access public
	 * @param string $path
	 * @param string $replace
	 * @return array
	 */
	//--------------------------------------------
	function parse_conf($path, $replace = null)
	{
		if (file_exists($path)) {
			$ini = file($path);
			if (count($ini) == 0) {
				return array();
			}
			$globals = array();
			foreach ($ini as $line) {
				$line = trim($line);
				// Comments
				if ($line == '' || $line[0] != 'O') {
					continue;
				}
				// Key-value pair
				list($key, $value) = explode('=', $line, 2);
				$key = trim($key);
				if (isset($replace)) {
					$key = str_replace($replace, "", $key);
				}
				$value = trim($value);
				$value = str_replace("\"", "", $value);
				$globals[$key] = $value;
			}
			return $globals;
		}
	}
	
	//--------------------------------------------
	/**
	 * lc
	 *
	 * @access public
	 * @param string $path
	 * @param string $replace
	 * @return array
	 */
	//--------------------------------------------
}
?>

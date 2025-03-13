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


$RootDir = $_SERVER["DOCUMENT_ROOT"] . '/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/openqrm_server.class.php";

require_once "$RootDir/class/event.class.php";
$event = new event();

class user
{
	/**
	 * Id
	 * @access public
	 * @var int
	 */
	var $id;
	/**
	 * Nickname
	 * @access public
	 * @var string
	 */
	var $name = '';
	/**
	 * Password
	 * @access public
	 * @var string
	 */
	var $password = '';
	/**
	 * Gender
	 * @access public
	 * @var string
	 */
	var $gender = '';
	/**
	 * Firstname
	 * @access public
	 * @var string
	 */
	var $first_name = '';
	/**
	 * Lastname
	 * @access public
	 * @var string
	 */
	var $last_name = '';
	/**
	 * Department
	 * @access public
	 * @var string
	 */
	var $department = '';
	/**
	 * Office
	 * @access public
	 * @var string
	 */
	var $office = '';
	/**
	 * Role (Group)
	 * @access public
	 * @var string
	 */
	var $role = '';
	/**
	 *
	 * Lang (language)
	 * @access public
	 * @var string
	 */
	var $lang = '';
	/**
	 * Last update
	 * @access public
	 * @var string
	 */
	var $last_update_time = '';
	/**
	 * Description
	 * @access public
	 * @var string
	 */
	var $description = '';
	/**
	 * Capabilities
	 * @access public
	 * @var string
	 */
	var $capabilities = '';
	/**
	 * wizard_name
	 * @access public
	 * @var string
	 */
	var $wizard_name = '';
	/**
	 * wizard_step
	 * @access public
	 * @var string
	 */
	var $wizard_step = '';
	/**
	 * wizard_id
	 * @access public
	 * @var string
	 */
	var $wizard_id = '';
	/**
	 * State
	 * @access public
	 * @var string
	 */
	var $state = '';
	
	/**
	 * Internal use only
	 * @access private
	 * @var string
	 */
	var $_user_table = '';
	/**
	 * Internal use only
	 * @access private
	 * @var string
	 */
	var $_role_table = '';
	
	
	//-----------------------------------------------------------------------------------
	function __construct($name)
	{
		global $USER_INFO_TABLE;
		$this->name = $name;
		$this->get_instance_by_name($name);
		$this->_role_table = 'role_info';
		$this->_user_table = $USER_INFO_TABLE;
	}
	
	
	// returns a user from the db selected by id, mac or ip
	function get_instance($id, $name)
	{
		global $USER_INFO_TABLE;
		$event = new event();
  
		$db = openqrm_get_db_connection();
  
		if ($id != "") {
			$user_array = $db->GetAll("select * from " . $this->_user_table . " where user_id=$id");
		} else if ($name != "") {
			$user_array = $db->GetAll("select * from " . $USER_INFO_TABLE . " where user_name='$name'");
		} else {
			$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "user.class.php", "Could not create instance of user without data", "", "", 0, 0, 0);
			array_walk(debug_backtrace(), create_function('$a,$b', 'syslog(LOG_ERR, "{$a[\'function\']}()(".basename($a[\'file\']).":{$a[\'line\']}); ");'));
			return;
		}
        
        if (is_array($user_array)) {
            foreach ($user_array as $index => $user) {
                $this->id = $user["user_id"];
                $this->name = $user["user_name"];
                $this->password = $user["user_password"];
                $this->gender = $user["user_gender"];
                $this->first_name = $user["user_first_name"];
                $this->last_name = $user["user_last_name"];
                $this->department = $user["user_department"];
                $this->office = $user["user_office"];
                $this->role = $user["user_role"];
                $this->lang = $user["user_lang"];
                $this->last_update_time = $user["user_last_update_time"];
                $this->description = $user["user_description"];
                $this->capabilities = $user["user_capabilities"];
                $this->wizard_name = $user["user_wizard_name"];
                $this->wizard_step = $user["user_wizard_step"];
                $this->wizard_id = $user["user_wizard_id"];
                $this->state = $user["user_state"];
            }
        }
		return $this;
	}
	
	// returns a user from the db selected by id
	function get_instance_by_id($id)
	{
		$this->get_instance($id, "");
		return $this;
	}
	
	
	// returns a user from the db selected by id
	function get_instance_by_name($name)
	{
		$this->get_instance("", $name);
		return $this;
	}
	
	
	
	// for the ldap integration
	
	// adds user just to the database
	function add($user_fields)
	{
		global $event;
		if (!is_array($user_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "user.class.php", "user_fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db = openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_user_table, $user_fields, 'INSERT');
		if (!$result) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "user.class.php", "Failed adding new user to database", "", "", 0, 0, 0);
		}
	}
	
	function get_name_list()
	{
		global $event;
		$user_array = array();
		$query = "select user_name from $this->_user_table";
		$db = openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_name_list", $_SERVER['REQUEST_TIME'], 2, "user.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
			while (!$rs->EOF) {
				$user_array[] = $rs->fields;
				$rs->MoveNext();
			}
		return $user_array;
	}
	
	function remove_by_name($user_name)
	{
		// remove from db
		$db = openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_user_table where user_name='$user_name'");
		return $rs;
	}
	
	// end of ldap integration
	
	
	//--------------------------------------------
	/**
	 * Find out user is admin
	 *
	 * @access public
	 * @return bool
	 */
	//--------------------------------------------
	function isAdmin()
	{
		if (strtolower($this->role) === 'administrator' || $this->role === 0) {
			return true;
		} else {
			return false;
		}
	}
	
	
	//-----------------------------------------------------------------------------------
	function set_user_form()
	{
		
		$query = $this->query_select();
		$result = openqrm_db_get_result($query);
		
		$this->name = array('value' => $this->name, 'label' => 'Login');
		$this->id = $result[0][0];
		$this->gender = $result[0][1];
		$this->first_name = $result[0][2];
		$this->last_name = $result[0][3];
		$this->description = $result[0][4];
		$this->department = $result[0][5];
		$this->office = $result[0][6];
		$this->capabilities = $result[0][7];
		$this->state = $result[0][8];
		$this->role = $result[0][9];
		$this->last_update_time = $result[0][10];
		$this->password = array('value' => '', 'label' => $result[0][11]['label']);
	}
	
	//-----------------------------------------------------------------------------------
	function set_user()
	{
		
		$query = $this->query_select();
		$result = openqrm_db_get_result($query);
		
		$this->id = $result[0][0]['value'];
		$this->gender = $result[0][1]['value'];
		$this->first_name = $result[0][2]['value'];
		$this->last_name = $result[0][3]['value'];
		$this->description = $result[0][4]['value'];
		$this->department = $result[0][5]['value'];
		$this->office = $result[0][6]['value'];
		$this->capabilities = $result[0][7]['value'];
		$this->state = $result[0][8]['value'];
		$this->role = $result[0][9]['value'];
		$this->last_update_time = $result[0][10]['value'];
		$this->password = $result[0][11]['value'];
		
		$this->get_role_name();
		$this->role = $this->role['label'];
	}
	
	//-----------------------------------------------------------------------------------
	function set_user_from_request()
	{
		
		$this->id = $this->http_request('id');
		$this->password = $this->http_request('password');
		$this->gender = $this->http_request('gender');
		$this->first_name = $this->http_request('first_name');
		$this->last_name = $this->http_request('last_name');
		$this->department = $this->http_request('department');
		$this->office = $this->http_request('office');
		$this->role = $this->http_request('role');
		$this->last_update_time = $this->http_request('last_update_time');
		$this->description = $this->http_request('description');
		$this->capabilities = $this->http_request('capabilities');
		$this->state = $this->http_request('state');
		
	}
	
	//-----------------------------------------------------------------------------------
	function query_select()
	{
		$query = "
			SELECT
				user_id,
				user_gender,
				user_first_name,
				user_last_name,
				user_description,
				user_department,
				user_office,
				user_capabilities,
				user_state,
				user_role,
				user_last_update_time,
				user_password
				user_lang
			FROM $this->_user_table
			WHERE user_name = '$this->name'
		";
		return $query;
	}
	
	//-----------------------------------------------------------------------------------
	function query_insert()
	{
		global $USER_INFO_TABLE;
		$this->id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
		$query = "
			INSERT INTO
				$USER_INFO_TABLE (
					user_id,
					user_name,
					user_password,
					user_gender,
					user_first_name,
					user_last_name,
					user_department,
					user_office,
					user_role,
					user_last_update_time,
					user_description,
					user_capabilities,
					user_state,
					user_lang
				)
			VALUES (
					'$this->id',
					'$this->name',
					'$this->password',
					'$this->gender',
					'$this->first_name',
					'$this->last_name',
					'$this->department',
					'$this->office',
					'$this->role',
					'$this->last_update_time',
					'$this->description',
					'$this->capabilities',
					'$this->state.',
					'$this->lang.'
				)
		";
		
		$this->change_htpasswd('insert');
		return openqrm_db_get_result($query);
	}
	
	//-----------------------------------------------------------------------------------
	function query_update()
	{
        global $USER_INFO_TABLE, $event;
		$user_fields = array();
		if ($this->password != '') {
			$user_fields['user_password'] = $this->password;
			$this->change_htpasswd('update');
		}
		$user_fields['user_gender'] = $this->gender;
		$user_fields['user_first_name'] = $this->first_name;
		$user_fields['user_last_name'] = $this->last_name;
		$user_fields['user_department'] = $this->department;
		$user_fields['user_office'] = $this->office;
		$user_fields['user_role'] = $this->role;
		$user_fields['user_last_update_time'] = $this->last_update_time;
		$user_fields['user_description'] = $this->description;
		$user_fields['user_capabilities'] = $this->capabilities;
		$user_fields['user_state'] = $this->state;
		$user_fields['user_lang'] = $this->lang;
		$db = openqrm_get_db_connection();
		$result = $db->AutoExecute($USER_INFO_TABLE, $user_fields, 'UPDATE', "user_name = '$this->name'");
		if (!$result) {
			$event->log("query_update", $_SERVER['REQUEST_TIME'], 2, "user.class.php", "Failed updating user", "", "", 0, 0, 0);
		}
	}
	
	//-----------------------------------------------------------------------------------
	function query_delete()
	{
		$query = "
			DELETE FROM $this->_user_table
			WHERE user_name = '" . $this->name . "'
		";
		$this->change_htpasswd('delete');
		return openqrm_db_get_result($query);
	}
	
	//-----------------------------------------------------------------------------------
	function check_user_exists()
	{
		$query = "
			SELECT user_name
			FROM $this->_user_table
			WHERE user_name = '" . $this->name . "'
		";
		$result = openqrm_db_get_result_single($query);
		
		if ($result['value'] != '') {
			return true;
		} else {
			return false;
		}
		
	}
	
	//-----------------------------------------------------------------------------------
	function get_gender_list()
	{
		$ar_Return = array();
		$ar_Return[] = array("value" => '', "label" => '',);
		$ar_Return[] = array("value" => 'f', "label" => 'female',);
		$ar_Return[] = array("value" => 'm', "label" => 'male',);
		return $ar_Return;
	}
	
	//-----------------------------------------------------------------------------------
	function get_role_name()
	{
		$query = "
			SELECT user_role, role_name
			FROM $this->_user_table, $this->_role_table
			WHERE user_name = '" . $this->name . "'
				AND user_role = role_id
		";
		$result = openqrm_db_get_result_double($query);
		$this->role = $result[0];
	}
	
	//-----------------------------------------------------------------------------------
	function get_role_list()
	{
		$query = "
			SELECT role_id, role_name
			FROM $this->_role_table
		";
		$result = openqrm_db_get_result_double($query);
		return $result;
	}
	
	//-----------------------------------------------------------------------------------
	function check_string_name($name)
	{
		
		if (!preg_match('#^[A-Za-z0-9]*$#', $name)) {
			return '[A-Za-z0-9]';
		} else {
			return '';
		}
	}
	
	//-----------------------------------------------------------------------------------
	function check_string_password($pass)
	{
		if (!preg_match('#^[A-Za-z0-9]*$#', $pass)) {
			return '[A-Za-z0-9_-]';
		} else {
			return '';
		}
	}
	//-----------------------------------------------------------------------------------
	
	/**
	 * Change htpassswd
	 * @access private
	 * @param $mode [update, delete, insert]
	 */
	function change_htpasswd($mode = 'update')
	{
		global $RootDir;
		
		$ar_values = array();
		
		$handle = fopen($RootDir . '/.htpasswd', "r");
		while (!feof($handle)) {
			$tmp = explode(':', fgets($handle, 4096));
			if ($tmp[0] != '') {
				$ar_values[$tmp[0]] = $tmp[1];
			}
		}
		fclose($handle);
		
		$handle = fopen($RootDir . '/.htpasswd', "w+");
		
		if ($mode == 'insert') {
			foreach ($ar_values as $key => $value) {
				fputs($handle, "$key:$value");
			}
			fputs($handle, $this->name . ':' . crypt($this->password) . "\n");
		}
		if ($mode == 'update') {
			foreach ($ar_values as $key => $value) {
				if ($key == $this->name) {
					fputs($handle, $this->name . ':' . crypt($this->password) . "\n");
				} else {
					fputs($handle, "$key:$value");
				}
			}
		}
		if ($mode == 'delete') {
			foreach ($ar_values as $key => $value) {
				if ($key != $this->name) {
					fputs($handle, "$key:$value");
				}
			}
		}
		fclose($handle);
	}
	
	//-----------------------------------------------------------------------------------
	function get_users()
	{
		$query = '
			SELECT
				user_name,
				user_id,
				user_first_name,
				user_last_name,
				role_name
			FROM ' . $this->_user_table . ', ' . $this->_role_table . '
			WHERE user_role = role_id
			ORDER BY user_name
		';
		$ar_db = openqrm_db_get_result($query);
		$ar_headline = array();
		$ar_headline[] = array('Login', 'ID', 'First Name', 'Last Name', 'Role');
		$result = array_merge($ar_headline, $ar_db);
		
		return $result;
	}
	
	//-----------------------------------------------------------------------------------
	function http_request($arg)
	{
		global $_REQUEST;
		if (isset($_REQUEST[$arg]))
			return $_REQUEST[$arg];
		else
			return '';
	}
	
	
	// set users lang
	function set_user_language($name, $lang)
	{
		$db = openqrm_get_db_connection();
		$sql = "update " . $this->_user_table . " set user_lang='" . $lang . "' where user_name='" . $name . "'";
		$rs = $db->Execute($sql);
	}
	
	
	// set wizard fields
	function set_wizard($username, $wizardname, $step, $id)
	{
		$db = openqrm_get_db_connection();
		$sql = "update " . $this->_user_table . " set user_wizard_name='" . $wizardname . "',  user_wizard_step='" . $step . "',user_wizard_id=" . $id . " where user_name='" . $username . "'";
		$rs = $db->Execute($sql);
		return $rs;
	}
	
	//--------------------------------------------
	/**
	 * Translate
	 *
	 * @access public
	 * @param array $text_array array to translate
	 * @param string $dir dir of translation files
	 * @param string $file translation file
	 * @return array
	 */
	//--------------------------------------------
	function translate($text_array, $dir, $file)
	{
		$user_language = $this->lang;
		$path = $dir . '/' . $user_language . '.' . $file;
		if (file_exists($path)) {
			$tmp = parse_ini_file($path, true);
			foreach ($tmp as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $k2 => $v2) {
						$text_array[$k][$k2] = $v2;
					}
				} else {
					$text_array[$k] = $v;
				}
			}
		} // use en file as first fallback
		else if (file_exists($dir . '/en.' . $file)) {
			$tmp = parse_ini_file($dir . '/en.' . $file, true);
			foreach ($tmp as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $k2 => $v2) {
						$text_array[$k][$k2] = $v2;
					}
				} else {
					$text_array[$k] = $v;
				}
			}
		}
		return $text_array;
	}
	
}

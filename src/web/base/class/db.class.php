<?php
/**
 * db Class
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

class db
{
	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param openqrm $openqrm
	 */
	//--------------------------------------------
	function __construct($openqrm)
	{
		$this->openqrm = $openqrm;
		if (file_exists('/usr/share/php/adodb/adodb.inc.php')) {
			require_once('/usr/share/php/adodb/adodb.inc.php');
		} else if (file_exists($this->openqrm->get('basedir') . '/include/adodb/adodb.inc.php')) {
			require_once($this->openqrm->get('basedir') . '/include/adodb/adodb.inc.php');
		} else if (file_exists('/usr/share/adodb/adodb.inc.php')) {
			require_once('/usr/share/adodb/adodb.inc.php');
		} else {
			echo 'ERROR: Could not find adodb on this system!';
		}
		if (!defined("ADODB_ASSOC_CASE")) {
			define('ADODB_ASSOC_CASE', 0);
		}
		if ($this->openqrm->get('config', 'ORACLE_HOME')) {
			putenv('LD_LIBRARY_PATH=' . $this->openqrm->get('config', 'LD_LIBRARY_PATH'));
			putenv('ORACLE_HOME=' . $this->openqrm->get('config', 'ORACLE_HOME'));
			putenv('TNS_ADMIN=' . $this->openqrm->get('config', 'TNS_ADMIN'));
		}
	}
	
	//--------------------------------------------
	/**
	 * Connect to database
	 *
	 * @access public
	 * @return dbobject
	 */
	//--------------------------------------------
	function connect()
	{
		if ($this->openqrm->get('config', 'DATABASE_TYPE') === "db2") {
			$db = &ADONewConnection('odbc');
			$db->PConnect(
				$this->openqrm->get('config', 'DATABASE_NAME'),
				$this->openqrm->get('config', 'DATABASE_USER'),
				$this->openqrm->get('config', 'DATABASE_PASSWORD')
			);
			$db->SetFetchMode(ADODB_FETCH_ASSOC);
			return $db;
		} else if ($this->openqrm->get('config', 'DATABASE_TYPE') === "oracle") {
			$db = NewADOConnection("oci8po");
			$db->Connect(
				$this->openqrm->get('config', 'DATABASE_NAME'),
				$this->openqrm->get('config', 'DATABASE_USER'),
				$this->openqrm->get('config', 'DATABASE_PASSWORD')
			);
		} else {
			if (strlen($this->openqrm->get('config', 'DATABASE_PASSWORD'))) {
				$dsn = $this->openqrm->get('config', 'DATABASE_TYPE') . '://';
				$dsn .= $this->openqrm->get('config', 'DATABASE_USER') . ':';
				$dsn .= $this->openqrm->get('config', 'DATABASE_PASSWORD') . '@';
				$dsn .= $this->openqrm->get('config', 'DATABASE_SERVER') . '/';
				$dsn .= $this->openqrm->get('config', 'DATABASE_NAME') . '?persist';
			} else {
				$dsn = $this->openqrm->get('config', 'DATABASE_TYPE') . '://';
				$dsn .= $this->openqrm->get('config', 'DATABASE_USER') . '@';
				$dsn .= $this->openqrm->get('config', 'DATABASE_SERVER') . '/';
				$dsn .= $this->openqrm->get('config', 'DATABASE_NAME') . '?persist';
			}
			$db = &ADONewConnection($dsn);
		}
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		return $db;
	}
	
	//--------------------------------------------
	/**
	 * Get a free id from a table
	 *
	 * @access public
	 * @param string $fieldname
	 * @param string $tablename
	 * @return int
	 */
	//--------------------------------------------
	function get_free_id($fieldname, $tablename)
	{
		$db = $this->connect();
		$recordSet = $db->Execute("select $fieldname from $tablename");
		if (!$recordSet) {
			print $db->ErrorMsg();
			$db->Close();
			exit(0);
		} else {
			$ids = array();
			while ($arr = $recordSet->FetchRow()) {
				foreach ($arr as $val) {
					$ids[] = $val;
				}
			}
			$i = 1;
			while ($i > 0) {
				if (in_array($i, $ids) == false) {
					$db->Close();
					return $i;
					break;
				}
				$i++;
			}
		}
	}
	
}

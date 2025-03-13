<?php
/**
 * Openqrm Setup
 *
 * openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.
 *
 * All source code and content (c) Copyright 2013, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.
 *
 * This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
 * The latest version of this license can be found here: src/doc/LICENSE.txt
 *
 * By using this software, you acknowledge having read this license and agree to be bound thereby.
 *
 * http://openqrm-enterprise.com
 *
 * Copyright 2013, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class openqrm_configure
{
	/**
	 * absolute path to template dir
	 * @access public
	 * @var string
	 */
	var $configure_timeout = 900;
	/**
	 * absolute path to template dir
	 * @access public
	 * @var string
	 */
	var $tpldir;
	/**
	 * translation
	 * @access public
	 * @var array
	 */
	var $lang = array(
		'step1' => array(
			'tab' => 'Step 1',
			'label' => 'Please select a network card',
			'howto' => 'The selected network card will be used to setup openQRM Server and create
					the openQRM Management Network. All available and configured network interfaces
					on this system are listed on the left.',
			'error_empty' => 'Networkcard may not be empty',
			'saved' => 'Saved networkcard %s',
		),
		'step2' => array(
			'tab' => 'Step 2',
			'label' => 'Please select a database type',
			'howto' => 'Select the database type to use for storing the openQRM data.',
			'error_empty' => 'Database type may not be empty',
			'saved' => 'Saved database type %s',
		),
		'step3' => array(
			'tab' => 'Step 3',
			'label' => 'Configure the database connection and initialize openQRM',
			'form_server' => 'Server',
			'form_db' => 'Database',
			'form_user' => 'User',
			'form_password' => 'Password',
			'form_restore' => 'Restore last backup',
			'howto' => 'Fill in the database name, the database server and a username plus password to setup the database connection.',
			'saved' => 'Successfully initialized openQRM',
			'failed' => 'Failed to initialyze openQRM! Please check syslog!',
		),
	);
	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param file $file
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($response, $file)
	{
		$this->response = $response;
		$this->file = $file;
		
		if (($this->file->exists("/etc/init.d/openqrm")) && (is_link("/etc/init.d/openqrm"))) {
			$this->basedir = dirname(dirname(dirname(readlink("/etc/init.d/openqrm"))));
		} else {
			$this->basedir = "/usr/share/openqrm";
		}
		
		$this->webdir = $_SERVER["DOCUMENT_ROOT"] . '/openqrm/base/';
		
		
	}
	
	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action()
	{
		$this->action = '';
		$ar = $this->response->html->request()->get('step');
		if ($ar !== '') {
			if (is_array($ar)) {
				$this->action = key($ar);
			} else {
				$this->action = $ar;
			}
		}
		$content = array();
		switch ($this->action) {
			case '':
			case '1':
				$content[] = $this->step1(true);
				break;
			case '2':
				$content[] = $this->step1(false);
				$content[] = $this->step2(true);
				break;
			case '3':
				$content[] = $this->step1(false);
				$content[] = $this->step2(false);
				$content[] = $this->step3(true);
				break;
			default:
				$content[] = $this->step1(true);
				break;
		}
		$tab = $this->response->html->tabmenu('configure_tab');
		$tab->message_param = 'configure_msg';
		$tab->css = 'htmlobject_tabs';
		$tab->auto_tab = false;
		$tab->add($content);
		return $tab;
	}
	
	//--------------------------------------------
	/**
	 * Step1
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function step1($visible = false)
	{
		
		$t = '';
		if ($visible === true) {
			$form = $this->response->get_form('step', '1');
			
			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');
			
			if ($this->file->exists($this->webdir . '/unconfigured')) {
				$i = 0;
				$d = array();
				$lines = explode("\n", $this->file->get_contents($this->webdir . '/unconfigured'));
				foreach ($lines as $line) {
					$line = trim($line);
					if ($line !== '') {
						$d['param_nic_' . $i]['label'] = $line;
						$d['param_nic_' . $i]['object']['type'] = 'htmlobject_input';
						$d['param_nic_' . $i]['object']['attrib']['type'] = 'radio';
						$d['param_nic_' . $i]['object']['attrib']['name'] = 'nic';
						$d['param_nic_' . $i]['object']['attrib']['value'] = $line;
						if ($i === 0) {
							$d['param_nic_' . $i]['object']['attrib']['checked'] = true;
						}
						$i++;
					}
				}
				$form->add($d);
			}
			
			if (!$form->get_errors() && $this->response->submit()) {
				$nic = $form->get_request('nic');
				if ($nic === '') {
					$_REQUEST['configure_msg'] = $this->lang['step1']['error_empty'];
				} else {
					$token = md5(uniqid(rand(), true));
					$command = $this->basedir . "/sbin/openqrm-exec -i 127.0.0.1 -l true -t $token -c \"sed -i -e 's/^OPENQRM_SERVER_INTERFACE=.*/OPENQRM_SERVER_INTERFACE=\"$nic\"/g' " . $this->basedir . "/etc/openqrm-server.conf\"";
					shell_exec($command);
					// send to sleep to wait for background command
					sleep(6);
					$this->response->redirect(
						$this->response->get_url('step', '2', 'configure_msg', sprintf($this->lang['step1']['saved'], $nic))
					);
				}
			}
			
			$t = $this->response->html->template($this->tpldir . '/configure1.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($this->lang['step1']['label'], 'label');
			$t->add($this->lang['step1']['howto'], 'howto');
			$t->add($form);
			$t->group_elements(array('param_' => 'form'));
		}
		
		$content['label'] = $this->lang['step1']['tab'];
		$content['value'] = $t;
		$content['target'] = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array('step', '1');
		$content['onclick'] = false;
		if ($this->action === '1') {
			$content['active'] = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Step2
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function step2($visible = false)
	{
		
		$t = '';
		if ($visible === true) {
			$form = $this->response->get_form('step', '2');
			
			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');
			
			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');
			
			$i = 0;
			$d = array();
			$lines = array('mysql', 'postgres');
			foreach ($lines as $line) {
				if ($line !== '') {
					$d['param_db_' . $i]['label'] = $line;
					$d['param_db_' . $i]['object']['type'] = 'htmlobject_input';
					$d['param_db_' . $i]['object']['attrib']['type'] = 'radio';
					$d['param_db_' . $i]['object']['attrib']['name'] = 'db';
					$d['param_db_' . $i]['object']['attrib']['value'] = $line;
					if ($i === 0) {
						$d['param_db_' . $i]['object']['attrib']['checked'] = true;
					}
					$i++;
				}
			}
			$form->add($d);
			
			if ($this->response->cancel()) {
				$this->response->redirect(
					$this->response->get_url('step', '1')
				);
			}
			
			if (!$form->get_errors() && $this->response->submit()) {
				$db = $form->get_request('db');
				if ($db === '') {
					$_REQUEST['configure_msg'] = $this->lang['step2']['error_empty'];
				} else {
					// do redirect adding db type
					$this->response->redirect(
						$this->response->get_url('step', '3', 'configure_msg', sprintf($this->lang['step2']['saved'], $db)) . '&dbtype=' . $db
					);
				}
			}
			
			$t = $this->response->html->template($this->tpldir . '/configure2.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($this->lang['step2']['label'], 'label');
			$t->add($this->lang['step2']['howto'], 'howto');
			$t->add($form);
			$t->group_elements(array('param_' => 'form'));
		}
		
		$content['label'] = $this->lang['step2']['tab'];
		$content['value'] = $t;
		$content['target'] = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array('step', '2');
		$content['onclick'] = false;
		if ($this->action === '2') {
			$content['active'] = true;
		}
		return $content;
	}
	
	
	//--------------------------------------------
	/**
	 * Step3
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function step3($visible = false)
	{
		$t = '';
		$dbtype = $this->response->html->request()->get('dbtype');
		$this->response->add('dbtype', $dbtype);
		if ($visible === true) {
			if ($dbtype === '') {
				$this->response->redirect(
					$this->response->get_url('step', '2', 'configure_msg', $this->lang['step2']['error_empty'])
				);
			} else {
				$form = $this->response->get_form('step', '3');
				
				$submit = $form->get_elements('submit');
				$submit->handler = 'onclick="wait();"';
				$form->add($submit, 'submit');
				
				$submit = $form->get_elements('cancel');
				$submit->handler = 'onclick="cancel();"';
				$form->add($submit, 'cancel');
				
				$d['param_db_1']['label'] = $this->lang['step3']['form_server'];
				$d['param_db_1']['required'] = true;
				$d['param_db_1']['object']['type'] = 'htmlobject_input';
				$d['param_db_1']['object']['attrib']['type'] = 'text';
				$d['param_db_1']['object']['attrib']['name'] = 'db_server';
				$d['param_db_1']['object']['attrib']['value'] = 'localhost';
				
				$d['param_db_2']['label'] = $this->lang['step3']['form_db'];
				$d['param_db_2']['required'] = true;
				$d['param_db_2']['object']['type'] = 'htmlobject_input';
				$d['param_db_2']['object']['attrib']['type'] = 'text';
				$d['param_db_2']['object']['attrib']['name'] = 'db_name';
				$d['param_db_2']['object']['attrib']['value'] = 'openqrm';
				
				$d['param_db_3']['label'] = $this->lang['step3']['form_user'];
				$d['param_db_3']['required'] = true;
				$d['param_db_3']['object']['type'] = 'htmlobject_input';
				$d['param_db_3']['object']['attrib']['type'] = 'text';
				$d['param_db_3']['object']['attrib']['name'] = 'db_user';
				
				$d['param_db_4']['label'] = $this->lang['step3']['form_password'];
				$d['param_db_4']['object']['type'] = 'htmlobject_input';
				$d['param_db_4']['object']['attrib']['type'] = 'password';
				$d['param_db_4']['object']['attrib']['name'] = 'db_password';
				
				$d['param_db_5']['label'] = $this->lang['step3']['form_restore'];
				$d['param_db_5']['object']['type'] = 'htmlobject_input';
				$d['param_db_5']['object']['attrib']['type'] = 'checkbox';
				$d['param_db_5']['object']['attrib']['name'] = 'db_restore';
				
				$form->add($d);
				
				if ($this->response->cancel()) {
					$this->response->redirect(
						$this->response->get_url('step', '2')
					);
				}
				
				if (!$form->get_errors() && $this->response->submit()) {
					$type = $dbtype;
					$server = $form->get_request('db_server');
					$db = $form->get_request('db_name');
					$user = $form->get_request('db_user');
					$pass = $form->get_request('db_password');
					$restore = $form->get_request('db_restore');
					
					$token = md5(uniqid(rand(), true));
					$command = $this->basedir . '/sbin/openqrm-exec -i 127.0.0.1 -l true -t ' . $token . ' -c';
					$command .= ' "sed -i -e \'s/^OPENQRM_DATABASE_TYPE=.*/OPENQRM_DATABASE_TYPE="' . $type . '"/g\'';
					$command .= ' -e \'s/^OPENQRM_DATABASE_SERVER=.*/OPENQRM_DATABASE_SERVER="' . $server . '"/g\'';
					$command .= ' -e \'s/^OPENQRM_DATABASE_NAME=.*/OPENQRM_DATABASE_NAME="' . $db . '"/g\'';
					$command .= ' -e \'s/^OPENQRM_DATABASE_USER=.*/OPENQRM_DATABASE_USER="' . $user . '"/g\'';
					$command .= ' -e \'s/^OPENQRM_DATABASE_PASSWORD=.*/OPENQRM_DATABASE_PASSWORD="' . $pass . '"/g\'';
					if (!strcmp($type, "oracle")) {
						$oqc_db_ld_path = '';
						$oqc_db_home = '';
						$oqc_db_tns = '';
						$command .= ' -e \'s/#OPENQRM_LD_LIBRARY_PATH=.*/OPENQRM_LD_LIBRARY_PATH="' . $oqc_db_ld_path . '"/g\'';
						$command .= ' -e \'s/#OPENQRM_ORACLE_HOME=.*/OPENQRM_ORACLE_HOME="' . $oqc_db_home . '"/g\'';
						$command .= ' -e \'s/#OPENQRM_TNS_ADMIN=.*/OPENQRM_TNS_ADMIN="' . $oqc_db_tns . '"/g\'';
						$command .= ' -e \'s/OPENQRM_LD_LIBRARY_PATH=.*/OPENQRM_LD_LIBRARY_PATH="' . $oqc_db_ld_path . '"/g\'';
						$command .= ' -e \'s/OPENQRM_ORACLE_HOME=.*/OPENQRM_ORACLE_HOME="' . $oqc_db_home . '"/g\'';
						$command .= ' -e \'s/OPENQRM_TNS_ADMIN=.*/OPENQRM_TNS_ADMIN="' . $oqc_db_tns . '"/g\'';
					}
					$command .= ' ' . $this->basedir . '/etc/openqrm-server.conf"';
					shell_exec($command);
					// send to sleep to wait for background command
					sleep(4);
					// init token plus timeout
					$token = md5(uniqid(rand(), true));
					$token .= "." . $this->configure_timeout;
					// restore last backup ?
					$command = $this->basedir . "/sbin/openqrm-exec -i 127.0.0.1 -l true -t " . $token . " -c \"" . $this->basedir . "/bin/openqrm init_config";
					if ($restore !== '') {
						$command .= " restore\"";
					} else {
						$command .= "\"";
					}
					shell_exec($command);
					if (!$this->__check_install($this->webdir . '/unconfigured')) {
						$_REQUEST['configure_msg'] = $this->lang['step3']['failed'];
					} else {
						$this->response->redirect(
							$this->response->get_url('none', 'none', 'datacenter_msg', $this->lang['step3']['saved'])
						);
					}
				}
				$t = $this->response->html->template($this->tpldir . '/configure3.tpl.php');
				$t->add($this->response->html->thisfile, "thisfile");
				$t->add($this->lang['step3']['label'], 'label');
				$t->add($this->lang['step3']['howto'], 'howto');
				$t->add($form);
				$t->group_elements(array('param_' => 'form'));
			}
		}
		
		$content['label'] = $this->lang['step3']['tab'];
		$content['value'] = $t;
		$content['target'] = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array('step', '3');
		$content['onclick'] = false;
		if ($this->action === '3') {
			$content['active'] = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * check if init was successful
	 *
	 * @access protected
	 * @param file
	 * @return null
	 */
	//--------------------------------------------
	function __check_install($file)
	{
		$loop = 0;
		while ($this->file->exists($file)) {
			sleep(1);
			flush();
			$loop++;
			if ($loop > $this->configure_timeout) {
				return false;
			}
		}
		return true;
	}
	
	//--------------------------------------------
	/**
	 * Check db
	 *
	 * @access protected
	 * @param $type
	 * @param $server
	 * @param $db
	 * @param $user
	 * @param $pass
	 * @return empty|string
	 */
	//--------------------------------------------
	
	
	/*
		function __check($type, $server, $db, $user, $pass) {
	
			// try to connect
			// different locations of adodb for suse/redhat/debian
			if (file_exists('/usr/share/php/adodb/adodb.inc.php')) {
				require_once ('/usr/share/php/adodb/adodb.inc.php');
			} else if (file_exists($this->basedir.'/include/adodb/adodb.inc.php')) {
				require_once ($this->basedir.'/include/adodb/adodb.inc.php');
			} else if (file_exists('/usr/share/adodb/adodb.inc.php')) {
				require_once ('/usr/share/adodb/adodb.inc.php');
			} else {
				return 'ERROR: Could not find adodb on this system!';
			}
	
			// cache output
			ob_start();
	
			if ($type == "db2") {
				$db = ADONewConnection('odbc');
				$db->PConnect($db, $user, $pass);
				$db->SetFetchMode(ADODB_FETCH_ASSOC);
				return $db;
	
			} else if ($type == "oracle") {
				// we need to use the oci8po driver because it is the
				// only oracle driver supporting to set the column-names to lowercase
				// via define('ADODB_ASSOC_CASE',0);
				$db = NewADOConnection("oci8po");
				$db->Connect($db, $user, $pass);
	
			} else {
				if (strlen($pass)) {
					$dsn = "$type://$user:$pass@$server/$db?persist";
				} else {
					$dsn = "$type://$user@$server/$db?persist";
				}
			$db = ADONewConnection($dsn);
			}
	
			$error = ob_get_contents();
			// end cache
			ob_end_clean();
			$error = str_replace("<br />", '', trim($error));
			return $error;
	
		}
	*/
	
}

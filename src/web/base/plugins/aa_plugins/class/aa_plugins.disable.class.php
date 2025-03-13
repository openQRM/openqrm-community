<?php
/**
 * disable Plugins
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class aa_plugins_disable
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'aa_plugins_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'aa_plugins_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "aa_plugins_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'aa_plugins_tab';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param file $file
	 */
	//--------------------------------------------
	function __construct($response, $file) {
		$this->response = $response;
		$this->file     = $file;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$msg        = '';
		$event      = new event();
		$server     = new openqrm_server();
		$plugin     = new plugin();
		$identifier = $this->response->html->request()->get($this->identifier_name);
		$enabled    = $plugin->enabled();
		if($identifier !== '') {
			foreach($identifier as $id) {
				if(in_array($id, $enabled)) {
					$error = false;
					// check dependencies
					foreach($enabled as $v) {
						if($v !== $id) {
							$tmp = $plugin->get_dependencies($v);
							if($tmp !== '' && isset($tmp['dependencies']) && $tmp['dependencies'] !== '') {
								if(strpos($tmp['dependencies'], $id) !== false) {
									$msg .= sprintf($this->lang['error_dependencies'], $id, $v).'<br>';
									$error = true;
								}
							}
						}
					}
					// handle plugin type
					if($error === false) {
						$tmp = $plugin->get_config($id);
						switch($tmp['type']) {
							case 'storage':
								$storage = new storage();
								$types = $storage->get_storage_types();
								$deployment = new deployment();
								$dep = $deployment->get_id_by_storagetype($id);
								foreach($dep as $val) {
									if(in_array($val['value'], $types)) {
										$msg .= sprintf($this->lang['error_in_use'], $id).'<br>';
										$error = true;
									}
								}
							break;
						}
					}
					if($error === false) {
						$return = $server->send_command("openqrm_server_plugin_command ".$id." uninstall ".$GLOBALS['OPENQRM_ADMIN']->name.' '.$GLOBALS['OPENQRM_ADMIN']->password);
						if($return === true) {
							if ($this->__check($id)) {
								$msg .= sprintf($this->lang['msg'], $id).'<br>';
							} else {
								$msg .= sprintf($this->lang['error_timeout'], $id).'<br>';
							}
						} else {
							$msg .= sprintf($this->lang['error_disable'], $id).'<br>';
						}
					}
				}
			}
		}
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
		);
	}

	//--------------------------------------------
	/**
	 * Check plugin state
	 *
	 * @access private
	 * @param string $plugin
	 * @return bool
	 */
	//--------------------------------------------
	function __check($plugin) {
		$f = $_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/".$plugin;
		$i = 0;
		while ($this->file->exists($f)) {
			sleep(1);
			$i++;
			if ($i > 20)  {
				return false;
			}
		}
		return true;
	}

}

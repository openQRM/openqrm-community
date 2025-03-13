<?php
/**
 * Remove event
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class event_remove
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'event_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "event_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'event_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'event_identifier';
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
	function __construct($openqrm, $response) {
		$this->response = $response;
		$this->file     = $openqrm->file();
		$this->openqrm  = $openqrm;
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
		$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		$identifiers = $this->response->html->request()->get($this->identifier_name);
		$msg = '';
		if($identifiers !== '') {
			foreach($identifiers as $id) {
				$event = new event();
				$event->get_instance_by_id($id);
				if (strstr($event->description, "ERROR running token")) {
					$error_token = str_replace("ERROR running token ", "", $event->description);
					$cmd_file = $RootDir."/server/event/errors/".$error_token.".cmd";
					$error_file = $RootDir."/server/event/errors/".$error_token.".out";
					if ($this->file->exists($cmd_file)) {
						$error = $this->file->remove($cmd_file);
						if($error !== '') {
							$msg .= $error.'<br>';
						}
					}
					if ($this->file->exists($error_file)) {
						$error = $this->file->remove($error_file);
						if($error !== '') {
							$msg .= $error.'<br>';
						}
					}
				}
				$event->remove($id);
				$msg .= sprintf($this->lang['msg'], $id).'<br>';
			}
		}
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
		);
	}

}

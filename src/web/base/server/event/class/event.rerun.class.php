<?php
/**
 * Rerun event
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class event_rerun
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
		global $OPENQRM_SERVER_BASE_DIR;
		$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		$token = $this->response->html->request()->get('token');
		$event_id = $this->response->html->request()->get('event_id');
		$msg = '';
		if($token !== '' && $event_id !== '') {
			$event = new event();
			$event->get_instance_by_id($event_id);
			$event->log("event-action", $_SERVER['REQUEST_TIME'], 5, "event-overview.php", "Re-Running command $token", "", "", 0, 0, 0);
			$command = "mv -f ".$OPENQRM_SERVER_BASE_DIR."/openqrm/web/base/server/event/errors/".$token.".cmd ".$OPENQRM_SERVER_BASE_DIR."/openqrm/var/spool/openqrm-queue.".$token." && rm -f ".$OPENQRM_SERVER_BASE_DIR."/openqrm/web/base/server/event/errors/".$token.".out";
			shell_exec($command);
			$fields = array();
			$fields["event_priority"] = 4;
			$event->update($event_id, $fields);
			$msg .= "Re-running token ".$token." / Event ID ".$event_id."<br>";
		}
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
		);
	}

}

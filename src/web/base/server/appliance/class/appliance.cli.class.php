<?php
/**
 * Appliance cli
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class appliance_cli
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'appliance_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "appliance_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'appliance_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'appliance_identifier';
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
	 * @param openqrm $openqrm
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller = $controller;
		$this->response   = $controller->response;
		$this->openqrm    = $controller->openqrm;
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
		$action = $this->response->html->request()->get($this->controller->actions_name);
		switch( $action ) {
			case 'stop':
				$this->stop();
			break;
			case 'start':
				$this->start();
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Start
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function start() {
		if($this->response->html->request()->get($this->controller->identifier_name) !== '') {
			$_REQUEST[$this->response->id]['submit'] = 'submit';
			echo 'Starting appliance(s)'."\n";
			$this->response->redirect = false;
			ob_start();
			require_once($this->controller->rootdir.'/server/appliance/class/appliance.start.class.php');
			$controller                  = new appliance_start($this->openqrm, $this->response);
			$controller->actions_name    = $this->controller->actions_name;
			$controller->tpldir          = $this->controller->tpldir;
			$controller->message_param   = $this->controller->message_param;
			$controller->identifier_name = $this->controller->identifier_name;
			$controller->lang            = $this->controller->lang['start'];
			$controller->rootdir         = $this->controller->rootdir;
			$controller->prefix_tab      = $this->controller->prefix_tab;
			$data = $controller->action();
			ob_end_clean();
			if(isset($_REQUEST[$this->controller->message_param])) {
				$msg = str_replace('<br>', "\n", $_REQUEST[$this->controller->message_param]);
				echo $msg."\n";
			} else {
				$msg = str_replace('<br>', "\n", $data->msg);
				echo $msg."\n";
			}
		} else {
			echo 'Missing param '.$this->controller->identifier_name."[]\n";
		}
	}

	//--------------------------------------------
	/**
	 * Stop
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function stop() {
		if($this->response->html->request()->get($this->controller->identifier_name) !== '') {
			$_REQUEST[$this->response->id]['submit'] = 'submit';
			echo 'Stopping appliance(s)'."\n";
			$this->response->redirect = false;
			ob_start();
			require_once($this->controller->rootdir.'/server/appliance/class/appliance.stop.class.php');
			$controller                  = new appliance_stop($this->openqrm, $this->response);
			$controller->actions_name    = $this->controller->actions_name;
			$controller->tpldir          = $this->controller->tpldir;
			$controller->message_param   = $this->controller->message_param;
			$controller->identifier_name = $this->controller->identifier_name;
			$controller->lang            = $this->controller->lang['stop'];
			$controller->rootdir         = $this->controller->rootdir;
			$controller->prefix_tab      = $this->controller->prefix_tab;
			$data = $controller->action();
			ob_end_clean();
			if(isset($_REQUEST[$this->controller->message_param])) {
				$msg = str_replace('<br>', "\n", $_REQUEST[$this->controller->message_param]);
				echo $msg."\n";
			} else {
				$msg = str_replace('<br>', "\n", $data->msg);
				echo $msg."\n";
			}
		} else {
			echo 'Missing param '.$this->controller->identifier_name."[]\n";
		}
	}


}

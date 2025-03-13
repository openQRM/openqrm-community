<?php
/**
 * novnc Select
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class novnc_login
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'novnc_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'novnc_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "novnc_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'novnc_tab';
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
	function __construct($openqrm, $response) {
		$this->response = $response;
		$this->file     = $openqrm->file();
		$this->openqrm  = $openqrm;
		$this->user     = $openqrm->user();
		$this->basedir  = $this->openqrm->get('basedir');
		$this->webdir   = $this->openqrm->get('webdir');
		$this->appliance_id = $this->response->html->request()->get('appliance_id');
		$this->response->add('appliance_id', $this->appliance_id);
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
		$response = $this->login();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'console', $this->message_param, $response->msg).$response->parameters
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/novnc-login.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['info'], "info");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $response->appliance->name), 'label');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function login() {
		
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			if(!$form->get_errors()) {
				$port = $form->get_request('port');
				$appliance = new appliance();
				$resource = new resource();
				$appliance->get_instance_by_id($this->appliance_id);
				$resource->get_instance_by_id($appliance->resources);
				$response->msg = sprintf($this->lang['login_msg'], $appliance->name);
				$response->parameters = '&resource_id='.$resource->id.'&vncport='.$port;
			}
		}
		return $response;
	}

	
	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'login');
		$appliance = $this->openqrm->appliance();
		$appliance->get_instance_by_id($this->appliance_id);
		$response->appliance = $appliance;
		
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d['port']['label']                         = $this->lang['form_port'];
		$d['port']['required']                      = true;
		$d['port']['object']['type']                = 'htmlobject_input';
		$d['port']['object']['attrib']['name']      = 'port';
		$d['port']['object']['attrib']['size']      = 5;
		$d['port']['object']['attrib']['maxlength'] = 2;
		$d['port']['object']['attrib']['minlength'] = 2;

		$form->add($d);
		$response->form = $form;
		return $response;
	}
	
	
}

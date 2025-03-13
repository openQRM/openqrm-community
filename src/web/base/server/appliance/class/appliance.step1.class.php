<?php
/**
 * Appliance step1 (name)
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class appliance_step1
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
	function __construct($openqrm, $response) {
		$this->response   = $response;
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
		$this->user       = $openqrm->user();
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
		$response = $this->add();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'step2', $this->message_param, $response->msg).'&appliance_wizard_id='.$response->appliance_wizard_id
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}

		$t = $this->response->html->template($this->tpldir.'/appliance-step1.tpl.php');
		$t->add($this->lang['title'], 'label');
		$t->add($this->lang['info'], 'info');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['lang_name_generate'], 'lang_name_generate');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Add
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function add() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$name  = $form->get_request('name');
			$comment  = $form->get_request('comment');
			$check = new appliance();
			$check->get_instance_by_name($name);
			if ($check->id > 0) {
				$error = sprintf($this->lang['error_exists'], $name);
			}
			if(isset($error)) {
				$response->error = $error;
			} else {
				$appliance = new appliance();
				$fields['appliance_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$fields['appliance_name'] = $name;
				$fields['appliance_resources'] = '-1';
				$fields['appliance_kernelid'] = '1';
				$fields['appliance_imageid'] = '1';
				$fields["appliance_virtual"]= 0;
				$fields["appliance_virtualization"]='1';
				$fields['appliance_wizard'] = 'wizard=step2,user='.$this->user->name;
				$fields['appliance_comment'] = $comment;
				$appliance->add_no_hook($fields);
				// wizard
				$rs = $this->user->set_wizard($this->user->name, 'appliance', 2, $fields['appliance_id']);
				// long term event
				$event = new event();
				$event_description = sprintf($this->lang['appliance_create_in_progress_event'], $name, 1, $this->user->name);
				$event->log("appliance", $_SERVER['REQUEST_TIME'], 9, "add", $event_description, "", "", 0, 0, 0);

				// set wizard id
				$response->appliance_wizard_id = $fields['appliance_id'];
				$response->msg = sprintf($this->lang['msg'], $name);
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
		$form = $response->get_form($this->actions_name, 'step1');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	
		$d['name']['label']                             = $this->lang['form_name'];
		$d['name']['required']                          = true;
		$d['name']['validate']['regex']                 = '/^[a-z0-9.-]+$/i';
		$d['name']['validate']['errormsg']              = sprintf($this->lang['error_name'], 'a-z0-9.-');
		$d['name']['object']['type']                    = 'htmlobject_input';
		$d['name']['object']['attrib']['name']          = 'name';
		$d['name']['object']['attrib']['id']            = 'name';
		$d['name']['object']['attrib']['type']          = 'text';
		$d['name']['object']['attrib']['css']           = 'namegen';
		$d['name']['object']['attrib']['customattribs'] = 'data-prefix="app"';
		$d['name']['object']['attrib']['value']         = '';
		$d['name']['object']['attrib']['maxlength']     = 50;

		$d['comment']['label']                         = $this->lang['form_comment'];
		$d['comment']['validate']['regex']             = $this->openqrm->regex['comment'];
		$d['comment']['validate']['errormsg']          = sprintf($this->lang['error_comment'], $this->openqrm->regex['comment']);
		$d['comment']['object']['type']                = 'htmlobject_textarea';
		$d['comment']['object']['attrib']['name']      = 'comment';
		$d['comment']['object']['attrib']['id']        = 'comment';
		$d['comment']['object']['attrib']['value']     = '';
		$d['comment']['object']['attrib']['maxlength'] = 255;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}

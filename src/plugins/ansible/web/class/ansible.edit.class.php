<?php
/**
 * ansible Appliance
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class ansible_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'ansible_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "ansible_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'ansible_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'ansible_identifier';
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
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->tpldir   = $this->rootdir.'/plugins/ansible/tpl';

		require_once($this->openqrm->get('basedir').'/plugins/ansible/web/class/ansible.class.php');
		$this->ansible = new ansible();

		$id = $this->response->html->request()->get('appliance_id');
		$this->response->add('appliance_id', $id);

		$appliance = $this->openqrm->appliance();
		$this->appliance = $appliance->get_instance_by_id($id);
		$resource = $this->openqrm->resource();
		$this->resource = $resource->get_instance_by_id($appliance->resources);
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action() {

		$response = $this->edit();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$response->redirect(
					$response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		$data['canceled'] = $this->lang['canceled'];
		$data['please_wait'] = $this->lang['please_wait'];
		$data['prefix_tab'] = $this->prefix_tab;
		$data['label'] = sprintf($this->lang['label'], $this->appliance->name);
		$data['baseurl'] = $this->openqrm->get('baseurl');
		$data['thisfile']    = $response->html->thisfile;
		$t = $response->html->template($this->tpldir.'/ansible-edit.tpl.php');
		$t->add($response->form);
		$t->add($data);
		$t->group_elements(array('param_' => 'form'));

		return $t;

	}


	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function edit() {
		$response = $this->get_response();
		$form = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			$playbooks = $form->get_request('playbooks');
			$this->ansible->remove_appliance($this->appliance->name);
			if(!in_array('{empty}', $playbooks)) {
				$this->ansible->set_playbooks($this->appliance->name, $playbooks);
			}
			// apply directly
			$apply = $form->get_request('apply');
			if ($apply == '1') {
				$command  = $this->openqrm->get('basedir')."/plugins/ansible/bin/openqrm-ansible-manager apply ".$this->appliance->id." ".$this->appliance->name." ".$this->resource->ip;
				$command  .= " --openqrm-cmd-mode fork";
				$openqrm_server = new openqrm_server();
				$openqrm_server->send_command($command);
			}
			$response->msg = sprintf($this->lang['msg_updated'], $this->appliance->name);
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param enum $mode [select|edit]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {

		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'edit');
		$selected = $this->ansible->get_playbooks($this->appliance->name);
		$playbooks   = $this->ansible->get_available_playbooks();

		$select = array('{empty}', '&#160;');
		foreach($playbooks as $v) {
			$o = $response->html->option();
			$o->value = $v;
			$o->label = $v;
			$o->title = $this->ansible->get_playbook_info($v);
			$select[] = $o;
		}
		$d['select']['label']                        = $this->lang['ansible_playbooks'];
		$d['select']['object']['type']               = 'htmlobject_select';
		$d['select']['object']['attrib']['name']     = 'playbooks[]';
		$d['select']['object']['attrib']['index']    = array(0,1);
		$d['select']['object']['attrib']['multiple'] = true;
		$d['select']['object']['attrib']['css']      = 'ansible_select';
		$d['select']['object']['attrib']['options']  = $select;
		$d['select']['object']['attrib']['selected'] = $selected;

		$d['apply']['label']                       = $this->lang['apply_playbooks'];
		$d['apply']['object']['type']              = 'htmlobject_input';
		$d['apply']['object']['attrib']['type']    = 'checkbox';
		$d['apply']['object']['attrib']['name']    = 'apply';
		$d['apply']['object']['attrib']['value']   = '1';
		$d['apply']['object']['attrib']['checked'] = true;

		$form->add($d);

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$response->form = $form;
		return $response;

	}

}

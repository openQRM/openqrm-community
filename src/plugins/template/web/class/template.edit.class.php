<?php
/**
 * template Appliance
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class template_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'template_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "template_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'template_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'template_identifier';
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
		$this->tpldir   = $this->rootdir.'/plugins/template/tpl';

		require_once($this->openqrm->get('basedir').'/plugins/template/web/class/template.class.php');
		$this->template = new template();

		$id = $this->response->html->request()->get('appliance_id');
		$this->response->add('appliance_id', $id);

		$appliance = new appliance();
		$this->appliance = $appliance->get_instance_by_id($id);
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
		$t = $response->html->template($this->tpldir.'/template-edit.tpl.php');
		$t->add($response->form);
		$t->add($data);
		$t->group_elements(array('param_' => 'hidden_fields'));

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
			$groups = $form->get_request('groups');
			$this->template->remove_appliance($this->appliance->name);
			if(!in_array('{empty}', $groups)) {
				$this->template->set_groups($this->appliance->name, $groups);
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
		$selected = $this->template->get_groups($this->appliance->name);
		$groups   = $this->template->get_available_groups();

		$select = array('{empty}', '&#160;');
		foreach($groups as $v) {
			$o = $response->html->option();
			$o->value = $v;
			$o->label = $v;
			$o->title = $this->template->get_group_info($v);
			$select[] = $o;
		}
		$d['select']['label']                        = $this->lang['template_groups'];
		$d['select']['object']['type']               = 'htmlobject_select';
		$d['select']['object']['attrib']['name']     = 'groups[]';
		$d['select']['object']['attrib']['index']    = array(0,1);
		$d['select']['object']['attrib']['multiple'] = true;
		$d['select']['object']['attrib']['css']      = 'template_select';
		$d['select']['object']['attrib']['options']  = $select;
		$d['select']['object']['attrib']['selected'] = $selected;
		$form->add($d);
		
		$customLink = $this->response->html->a();
		$customLink->label = 'my custom button';
		$customLink->name = 'my custom button';			// not used but needs to be set for htmlobject compatibility
		$customLink->css = 'btn edit';
		$form->add($customLink, 'my_custom_link');



		// Create a form row with label and 2 inputfield on one row
		// 1. Create a $box

		$box = $this->response->html->div();
		$box->name = 'unnamed';								// needed, if box should contain a row label

		$input = $this->response->html->input();
		$input->name = 'my-input';
		$input->css = 'input-large';
		$input->setAttributes('placeholder="custom placeholder"');

		$box->add($input);

		$input2 = $this->response->html->input();
		$input2->name = 'my-input2';
		$input2->css = 'input-mini';
		
		$box->add($input2);
		
		$g['bla']['label'] = 'Form row';				// if box has no name, this labels will not be shown in the interface
		$g['bla']['object'] = $box;
		
		$form->add($g);

//		$this->response->html->debug();
		
		
		$submit = $form->get_elements('submit');
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$form->add($submit, 'cancel');

		$response->form = $form;
		return $response;

	}

}

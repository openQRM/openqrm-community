<?php
/**
 * puppet Appliance
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class puppet_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'puppet_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "puppet_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'puppet_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'puppet_identifier';
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
		$this->rootdir  = $this->openqrm->get('basedir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->tpldir   = $this->rootdir.'/plugins/puppet/web/tpl';

		require_once($this->rootdir.'/plugins/puppet/web/class/puppet.class.php');
		$this->puppet = new puppet();

		$id = $this->response->html->request()->get('appliance_id');
		$this->response->add('appliance_id', $id);

		$appliance = $this->openqrm->appliance();
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
		$t = $response->html->template($this->tpldir.'/puppet-edit.tpl.php');
		$t->add($response->form);
		$t->add($data);
		$t->group_elements(array('param_' => 'form', 'input_' => 'input'));

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
			$this->puppet->remove_appliance($this->appliance->name);
			if(is_array($groups)) {
				$this->puppet->set_groups($this->appliance->name, $groups);
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
		$selected = $this->puppet->get_groups($this->appliance->name);
		$groups   = $this->puppet->get_available_groups();

		$i = 0;
		foreach($groups as $v) {
			$d['input_'.$i]['label']                     = '<b>'.$v.'</b> <i>('.$this->puppet->get_group_info($v).')</i>';
			$d['input_'.$i]['object']['type']            = 'htmlobject_input';
			$d['input_'.$i]['object']['attrib']['type']  = 'checkbox';
			$d['input_'.$i]['object']['attrib']['name']  = 'groups[]';
			$d['input_'.$i]['object']['attrib']['value'] = $v;
			if(in_array($v, $selected)) {
				$d['input_'.$i]['object']['attrib']['checked'] = true;
			}
			$i++;
		}
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

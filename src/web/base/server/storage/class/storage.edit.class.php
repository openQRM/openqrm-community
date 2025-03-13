<?php
/**
 * Edit Storage
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class storage_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'storage_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'storage_identifier';
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

		$storage_id = $response->html->request()->get('storage_id');
		$storage = new storage();
		$storage->get_instance_by_id($storage_id);
		$this->storage_id = $storage_id;
		$this->storage_name = $storage->name;
		$this->response->params['storage_id'] = $this->response->html->request()->get('storage_id');

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

		$response = $this->edit();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/storage-edit.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['comment'], 'comment');
		$t->add(sprintf($this->lang['label'], $this->storage_name), 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($response->form);
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
		$form     = $response->form;

		if(!$form->get_errors() && $response->submit()) {
			$errors     = array();
			$message    = array();

			$storage_id		= $this->response->html->request()->get('storage_id');
			$storage_comment		= $this->response->html->request()->get('storage_comment');

			$storage = new storage();
			$storage->get_instance_by_id($storage_id);

			$storage_comment = $response->html->request()->get('storage_comment');
			$fields['storage_comment'] = htmlspecialchars($storage_comment);
			$storage->update($storage_id, $fields);
			$response->msg = sprintf($this->lang['msg'], $storage->name);
		}
		return $response;

	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'edit');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$storage = new storage();
		$storage->get_instance_by_id($this->storage_id);

		$d['storage_comment']['label']                     = $this->lang['form_comment'];
		$d['storage_comment']['validate']['regex']         = '/^[a-z0-9._ -]+$/i';
		$d['storage_comment']['validate']['errormsg']      = sprintf($this->lang['error_comment'], 'a-z0-9._ -');
		$d['storage_comment']['object']['type']            = 'htmlobject_textarea';
		$d['storage_comment']['object']['attrib']['name']  = 'storage_comment';
		$d['storage_comment']['object']['attrib']['value'] = $storage->comment;

		$form->add($d);
		$response->form = $form;
		return $response;
	}


}

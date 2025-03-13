<?php
/**
 * Storage Add
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class storage_add
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
		$this->response   = $response;
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
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
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/storage-add.tpl.php');
		$t->add($this->lang['label'], 'label');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['label'], 'form_add');
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
			$name     = $form->get_request('name');
			$storage_type     = $form->get_request('type');
			$storge_capabilities = $form->get_request('capabilities');
			$resource_id     = $form->get_request('resource');
			$comment     = $form->get_request('comment');

			// check if name already exists
			// check that name is unique
			$storage_name_check = new storage();
			$storage_name_check->get_instance_by_name($name);
			if ($storage_name_check->id > 0) {
				$error = sprintf($this->lang['error_exists'], $name);
			}
			if(isset($error)) {
				$response->error = $error;
			} else {
				$storage = new storage();
				$storage_fields['storage_id']=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$storage_fields['storage_name']=$name;
				$storage_fields['storage_type']=$storage_type;
				$storage_fields['storage_comment']=$comment;
				$storage_fields['storage_resource_id']=$resource_id;
				// unqote capabilities
				$storage_fields['storage_capabilities'] = stripslashes($storge_capabilities);
				$storage->add($storage_fields);
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
		$form = $response->get_form($this->actions_name, 'add');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		// select boxes
		$deployment_type_select = array();
		$deployment = $this->openqrm->deployment();
		$list = $deployment->get_list();
		foreach($list as $v) {
			$deployment_type_select[] = array($v['label'], $v['value']);
		}
		array_splice($deployment_type_select, 0, 1);
		asort($deployment_type_select);

		$resource = $this->openqrm->resource();
		$resource_arr = $resource->get_list();
		$resource_select = array();
		foreach ($resource_arr as $index => $resource_db) {
			$res_id = $resource_db['resource_id'];
			$storage_resource = $this->openqrm->resource();
			$storage_resource->get_instance_by_id($res_id);
			$resource_select[] = array( $res_id, 'ID '.$res_id.' / '.$storage_resource->ip.' '.$storage_resource->hostname);
		}
		asort($resource_select);
	
		$d['name']['label']                             = $this->lang['form_name'];
		$d['name']['required']                          = true;
		$d['name']['validate']['regex']                 = '/^[a-z0-9._-]+$/i';
		$d['name']['validate']['errormsg']              = sprintf($this->lang['error_name'], 'a-z0-9._');
		$d['name']['object']['type']                    = 'htmlobject_input';
		$d['name']['object']['attrib']['name']          = 'name';
		$d['name']['object']['attrib']['id']            = 'name';
		$d['name']['object']['attrib']['type']          = 'text';
		$d['name']['object']['attrib']['css']           = 'namegen';
		$d['name']['object']['attrib']['customattribs'] = 'data-prefix="storage_"';
		$d['name']['object']['attrib']['value']         = '';
		$d['name']['object']['attrib']['maxlength']     = 50;

		$d['capabilities']['label']                         = $this->lang['form_capabilities'];
		$d['capabilities']['validate']['regex']             = '/^[a-z0-9._ =-]+$/i';
		$d['capabilities']['validate']['errormsg']          = sprintf($this->lang['error_capabilities'], 'a-z0-9._ =-');
		$d['capabilities']['object']['type']                = 'htmlobject_textarea';
		$d['capabilities']['object']['attrib']['name']      = 'capabilities';
		$d['capabilities']['object']['attrib']['type']      = 'text';
		$d['capabilities']['object']['attrib']['value']     = '';
		$d['capabilities']['object']['attrib']['maxlength'] = 255;

		$d['comment']['label']                         = $this->lang['form_comment'];
		$d['comment']['validate']['regex']             = '/^[a-z0-9._ -]+$/i';
		$d['comment']['validate']['errormsg']          = sprintf($this->lang['error_comment'], 'a-z0-9._ -');
		$d['comment']['object']['type']                = 'htmlobject_textarea';
		$d['comment']['object']['attrib']['name']      = 'comment';
		$d['comment']['object']['attrib']['type']      = 'text';
		$d['comment']['object']['attrib']['value']     = '';
		$d['comment']['object']['attrib']['maxlength'] = 255;

		// deployment type select
		$d['type']['label']                       = $this->lang['form_deployment'];
		$d['type']['object']['type']              = 'htmlobject_select';
		$d['type']['object']['attrib']['index']   = array(1,0);
		$d['type']['object']['attrib']['id']      = 'form_deployment';
		$d['type']['object']['attrib']['name']    = 'type';
		$d['type']['object']['attrib']['options'] = $deployment_type_select;

		// resource select
		$d['resource']['label']                       = $this->lang['form_resource'];
		$d['resource']['object']['type']              = 'htmlobject_select';
		$d['resource']['object']['attrib']['index']   = array(0, 1);
		$d['resource']['object']['attrib']['id']      = 'form_deployment';
		$d['resource']['object']['attrib']['name']    = 'resource';
		$d['resource']['object']['attrib']['options'] = $resource_select;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}

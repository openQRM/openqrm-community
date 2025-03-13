<?php
/**
 * LVM-Storage Custom Add new Volume
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class lvm_storage_customadd
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'lvm_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "lvm_storage_msg";
/**
* openqrm rootdir
* @access public
* @var string
*/
var $rootdir;
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'lvm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'lvm_identifier';
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
	function __construct($openqrm, $response, $controller) {
		$this->controller = $controller;
		$this->response   = $response;
		$this->openqrm    = $openqrm;
		$this->file       = $this->openqrm->file();
		$this->user	      = $openqrm->user();
		$storage_id       = $this->response->html->request()->get('storage_id');
		$storage          = new storage();
		$resource         = new resource();
		$deployment       = new deployment();
		$this->storage    = $storage->get_instance_by_id($storage_id);
		$this->resource   = $resource->get_instance_by_id($storage->resource_id);
		$this->deployment = $deployment->get_instance_by_id($storage->type);
		$this->response->add('storage_id', $storage_id);
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
		$response = $this->customadd();
		if(isset($response->msg)) {
			// wizard
			if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 3) {
				$this->response->redirect(
					$this->response->html->thisfile.'?base=appliance&appliance_action=step'.$this->user->wizard_step.'&appliance_id='.$this->user->wizard_id.'&image_id='.$response->image_id
				);
			} else {
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'custom', $this->message_param, $response->msg)
				);
			}
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/lvm-storage-customadd.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
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
	function customadd() {

		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			
			$name     = $form->get_request('name');
			if ($this->deployment->name == 'custom-iscsi-deployment') {
				$target     = $form->get_request('target');
				$lun     = $form->get_request('lun');
				$initiator     = $form->get_request('initiator');
				$username     = $form->get_request('username');
				$password     = $form->get_request('password');
				$rootfstype = 'ext3';
				$volume_path = '/dev/'.$target.'/'.$lun;
			}
			if ($this->deployment->name == 'custom-nfs-deployment') {
				$volume_path = $form->get_request('export');
				$rootfstype = 'nfs';
			}			
			// $error = sprintf($this->lang['error_exists'], $name);
			
			if(isset($error)) {
				$response->error = $error;
			} else {
				$tables = $this->openqrm->get('table');
				$image_fields = array();
				$new_image_id  = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$image_fields["image_id"] = $new_image_id;
				$image_fields['image_name'] = $name;
				$image_fields['image_type'] = $this->deployment->type;
				$image_fields['image_rootfstype'] = $rootfstype;
				$image_fields['image_storageid'] = $this->storage->id;
				$image_fields['image_comment'] = "Image Object for volume $name";
				$image_fields['image_rootdevice'] = $volume_path;
				$image = new image();
				$image->add($image_fields);
				$image->get_instance_by_id($new_image_id);
				
				if ($this->deployment->name == 'custom-iscsi-deployment') {
					$image->set_deployment_parameters('INITIATOR', $initiator);
					if (strlen($username)) {
						$image->set_deployment_parameters('USER', $username);
					}
					if (strlen($password)) {
						$image->set_deployment_parameters('PASSWORD', $password);
					}
				}
				$response->msg = sprintf($this->lang['msg_added'], $name);
				// save image id in response for the wizard
				$response->image_id = $image_fields["image_id"];

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
		$form = $response->get_form($this->actions_name, 'customadd');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	
		$d['name']['label']                             = $this->lang['form_name'];
		$d['name']['required']                          = true;
		$d['name']['validate']['regex']                 = '/^[a-z0-9._]+$/i';
		$d['name']['validate']['errormsg']              = sprintf($this->lang['error_name'], 'a-z0-9._');
		$d['name']['object']['type']                    = 'htmlobject_input';
		$d['name']['object']['attrib']['id']            = 'name';
		$d['name']['object']['attrib']['name']          = 'name';
		$d['name']['object']['attrib']['type']          = 'text';
		$d['name']['object']['attrib']['css']           = 'namegen';
		$d['name']['object']['attrib']['customattribs'] = 'data-prefix="custom" data-length="6"';
		$d['name']['object']['attrib']['value']         = '';
		$d['name']['object']['attrib']['maxlength']     = 50;

		if ($this->deployment->name == 'custom-iscsi-deployment') {
			$d['target']['label']                             = $this->lang['form_target'];
			$d['target']['required']                          = true;
			$d['target']['validate']['regex']                 = '';
			$d['target']['validate']['errormsg']              = sprintf($this->lang['error_target'], 'a-z0-9._');
			$d['target']['object']['type']                    = 'htmlobject_input';
			$d['target']['object']['attrib']['id']            = 'target';
			$d['target']['object']['attrib']['name']          = 'target';
			$d['target']['object']['attrib']['type']          = 'text';
			$d['target']['object']['attrib']['value']         = '';
			$d['target']['object']['attrib']['maxlength']     = 50;

			$d['lun']['label']                             = $this->lang['form_lun'];
			$d['lun']['required']                          = true;
			$d['lun']['validate']['regex']                 = '';
			$d['lun']['validate']['errormsg']              = sprintf($this->lang['error_lun'], 'a-z0-9._');
			$d['lun']['object']['type']                    = 'htmlobject_input';
			$d['lun']['object']['attrib']['id']            = 'lun';
			$d['lun']['object']['attrib']['name']          = 'lun';
			$d['lun']['object']['attrib']['type']          = 'text';
			$d['lun']['object']['attrib']['value']         = '';
			$d['lun']['object']['attrib']['maxlength']     = 5;

			$d['initiator']['label']                             = $this->lang['form_initiator'];
			$d['initiator']['required']                          = true;
			$d['initiator']['validate']['regex']                 = '';
			$d['initiator']['validate']['errormsg']              = sprintf($this->lang['error_initiator'], 'a-z0-9._');
			$d['initiator']['object']['type']                    = 'htmlobject_input';
			$d['initiator']['object']['attrib']['id']            = 'initiator';
			$d['initiator']['object']['attrib']['name']          = 'initiator';
			$d['initiator']['object']['attrib']['type']          = 'text';
			$d['initiator']['object']['attrib']['value']         = '';
			$d['initiator']['object']['attrib']['maxlength']     = 200;

			$d['username']['label']                             = $this->lang['form_username'];
			$d['username']['required']                          = false;
			$d['username']['validate']['regex']                 = '';
			$d['username']['validate']['errormsg']              = sprintf($this->lang['error_username'], 'a-z0-9._');
			$d['username']['object']['type']                    = 'htmlobject_input';
			$d['username']['object']['attrib']['id']            = 'username';
			$d['username']['object']['attrib']['name']          = 'username';
			$d['username']['object']['attrib']['type']          = 'text';
			$d['username']['object']['attrib']['value']         = '';
			$d['username']['object']['attrib']['maxlength']     = 50;

			$d['password']['label']                             = $this->lang['form_password'];
			$d['password']['required']                          = false;
			$d['password']['validate']['regex']                 = '';
			$d['password']['validate']['errormsg']              = sprintf($this->lang['error_password'], 'a-z0-9._');
			$d['password']['object']['type']                    = 'htmlobject_input';
			$d['password']['object']['attrib']['id']            = 'password';
			$d['password']['object']['attrib']['name']          = 'password';
			$d['password']['object']['attrib']['type']          = 'password';
			$d['password']['object']['attrib']['value']         = '';
			$d['password']['object']['attrib']['maxlength']     = 50;
			
			$d['export'] = [];
			
		}

		if ($this->deployment->name == 'custom-nfs-deployment') {
			$d['export']['label']                             = $this->lang['form_export'];
			$d['export']['required']                          = true;
			$d['export']['validate']['regex']                 = '';
			$d['export']['validate']['errormsg']              = sprintf($this->lang['error_export'], 'a-z0-9._');
			$d['export']['object']['type']                    = 'htmlobject_input';
			$d['export']['object']['attrib']['id']            = 'export';
			$d['export']['object']['attrib']['name']          = 'export';
			$d['export']['object']['attrib']['type']          = 'text';
			$d['export']['object']['attrib']['value']         = '';
			$d['export']['object']['attrib']['maxlength']     = 250;

			$d['target'] = '';
			$d['lun'] = '';
			$d['initiator'] = '';
			$d['username'] = '';
			$d['password'] = '';
		}		
		
		$form->add($d);
		$response->form = $form;
		return $response;
	}


}

<?php
/**
 * TmpFs-Storage Add new Volume
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class tmpfs_storage_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'tmpfs_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "tmpfs_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'tmpfs_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'tmpfs_identifier';
/**
* openqrm rootdir
* @access public
* @var string
*/
var $rootdir;
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
		$this->openqrm = $openqrm;
		$this->file = $this->openqrm->file();
		$this->user = $openqrm->user();
		$storage_id       = $this->response->html->request()->get('storage_id');
		$storage          = $this->openqrm->storage();
		$resource         = $this->openqrm->resource();
		$deployment       = $this->openqrm->deployment();
		$this->response->params['storage_id'] = $this->response->html->request()->get('storage_id');
		$this->storage    = $storage->get_instance_by_id($storage_id);
		$this->resource   = $resource->get_instance_by_id($storage->resource_id);
		$this->deployment = $deployment->get_instance_by_id($storage->type);
		require_once($this->openqrm->get('basedir').'/plugins/tmpfs-storage/web/class/tmpfs-storage-volume.class.php');
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
			// wizard
			if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 3) {
				$this->response->redirect(
					$this->response->html->thisfile.'?base=appliance&appliance_action=step'.$this->user->wizard_step.'&appliance_id='.$this->user->wizard_id.'&image_id='.$response->image_id
				);
			} else {
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
				);
			}
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/tmpfs-storage-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->lang['lang_name_generate'], 'lang_name_generate');
		$t->add($this->lang['label'], 'label');
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
			if(!$form->get_errors()) {
				$name        = $form->get_request('name');
				$size        = $form->get_request('size');
				$description = $form->get_request('description');

				// check if volume / image name is aleady existing
				$image_check = new image();
				$image_check->get_instance_by_name($name);
				if (isset($image_check->id) && $image_check->id > 0) {
					$error = sprintf($this->lang['error_image_exists'], $name);
				}

				$tmpfs_storage_volume = new tmpfs_storage_volume();
				$tmpfs_storage_volume->get_instance_by_name($name);
				if (isset($tmpfs_storage_volume->id) && $tmpfs_storage_volume->id > 0) {
					$error = sprintf($this->lang['error_exists'], $name);
				}

				if(isset($error)) {
					$response->error = $error;
				} else {
					// add volume
					$volume_fields = array();
					$volume_fields["tmpfs_storage_volume_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$volume_fields['tmpfs_storage_volume_name'] = $name;
					$volume_fields['tmpfs_storage_volume_size'] = $size;
					$volume_fields['tmpfs_storage_volume_description'] = $description;
					$tmpfs_storage_volume->add($volume_fields);
					// add image
					$tables = $this->openqrm->get('table');
					$image_fields = array();
					$image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$image_fields['image_name'] = $name;
					$image_fields['image_type'] = $this->deployment->type;
					$image_fields['image_rootfstype'] = 'tmpfs';
					$image_fields['image_storageid'] = $this->storage->id;
					$image_fields['image_comment'] = "Image Object for volume $name";
					$image_fields['image_rootdevice'] = $size;
					$image_fields['image_size'] = $size;
					$image = new image();
					$image->add($image_fields);
					$response->msg = sprintf($this->lang['msg_added'], $name);
					// save image id in response for the wizard
					$response->image_id = $image_fields["image_id"];
				}
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
	
		$volume_size_arr[] = array("256", "256 MB");
		$volume_size_arr[] = array("512", "512 MB");
		$volume_size_arr[] = array("768", "768 MB");
		$volume_size_arr[] = array("1024", "1 GB");
		$volume_size_arr[] = array("2048", "2 GB");
		$volume_size_arr[] = array("3072", "3 GB");
		$volume_size_arr[] = array("4096", "4 GB");
		$volume_size_arr[] = array("5120", "5 GB");
		$volume_size_arr[] = array("6144", "6 GB");
		$volume_size_arr[] = array("8192", "8 GB");
		$volume_size_arr[] = array("16384", "16 GB");
		$volume_size_arr[] = array("32768", "32 GB");
		$volume_size_arr[] = array("65536", "64 GB");

		$d['name']['label']                             = $this->lang['form_name'];
		$d['name']['required']                          = true;
		$d['name']['validate']['regex']                 = '/^[a-z0-9._]+$/i';
		$d['name']['validate']['errormsg']              = sprintf($this->lang['error_name'], 'a-z0-9._');
		$d['name']['object']['type']                    = 'htmlobject_input';
		$d['name']['object']['attrib']['name']          = 'name';
		$d['name']['object']['attrib']['id']            = 'name';
		$d['name']['object']['attrib']['type']          = 'text';
		$d['name']['object']['attrib']['css']           = 'namegen';
		$d['name']['object']['attrib']['customattribs'] = 'data-prefix="tmpfs_" data-length="8"';
		$d['name']['object']['attrib']['value']         = '';
		$d['name']['object']['attrib']['maxlength']     = 50;

		$d['size']['label']                         = $this->lang['form_size'];
		$d['size']['object']['type']                = 'htmlobject_select';
		$d['size']['object']['attrib']['name']      = 'size';
		$d['size']['object']['attrib']['index']     = array(0,1);
		$d['size']['object']['attrib']['options']   = $volume_size_arr;

		$d['description']['label']                         = $this->lang['form_description'];
		$d['description']['required']                      = true;
		$d['description']['validate']['regex']             = '/^[a-z 0-9._]+$/i';
		$d['description']['validate']['errormsg']          = sprintf($this->lang['error_description'], 'a-z 0-9._');
		$d['description']['object']['type']                = 'htmlobject_input';
		$d['description']['object']['attrib']['name']      = 'description';
		$d['description']['object']['attrib']['type']      = 'text';
		$d['description']['object']['attrib']['value']     = '';
		$d['description']['object']['attrib']['maxlength'] = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}



}

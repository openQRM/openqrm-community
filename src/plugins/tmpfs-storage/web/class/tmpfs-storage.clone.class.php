<?php
/**
 * TmpFs-Storage Clone Volume(s)
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class tmpfs_storage_clone
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
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'tmpfs_tab';
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
		$this->openqrm = $openqrm;
		$this->user = $openqrm->user();
		$this->file = $this->openqrm->file();
		$this->volume = $this->response->html->request()->get('volume');
		$this->response->params['volume'] = $this->volume;
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
		$response = $this->duplicate();
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
		$t = $this->response->html->template($this->tpldir.'/tmpfs-storage-clone.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['form_add'], 'form_add');
		$t->add(sprintf($this->lang['label'], $this->volume), 'label');
		$t->add($response->form);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Clone
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function duplicate() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {

			$storage_id = $this->response->html->request()->get('storage_id');
			$storage    = new storage();
			$resource   = new resource();
			$deployment = new deployment();
			$storage->get_instance_by_id($storage_id);
			$resource->get_instance_by_id($storage->resource_id);
			$deployment->get_instance_by_id($storage->type);
			$name        = $form->get_request('name');

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
				$tmpfs_storage_volume->get_instance_by_name($this->volume);
				$volume_fields = array();
				$volume_fields["tmpfs_storage_volume_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$volume_fields['tmpfs_storage_volume_name'] = $name;
				$volume_fields['tmpfs_storage_volume_size'] = $tmpfs_storage_volume->size;
				$volume_fields['tmpfs_storage_volume_description'] = $tmpfs_storage_volume->description;
				$tmpfs_storage_volume->add($volume_fields);
				// add image
				$tables = $this->openqrm->get('table');
				$image_fields = array();
				$image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$image_fields['image_name'] = $name;
				$image_fields['image_type'] = $deployment->type;
				$image_fields['image_rootfstype'] = 'tmpfs';
				$image_fields['image_storageid'] = $storage->id;
				$image_fields['image_comment'] = "Image Object for volume $name";
				$image_fields['image_rootdevice'] = $tmpfs_storage_volume->size;
				$image_fields['image_size'] = $tmpfs_storage_volume->size;
				$image = new image();
				$image->add($image_fields);

				$response->msg = sprintf($this->lang['msg_cloned'], $this->volume, $name);
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
		$form = $response->get_form($this->actions_name, 'clone');

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
		$d['name']['object']['attrib']['name']          = 'name';
		$d['name']['object']['attrib']['id']            = 'name';
		$d['name']['object']['attrib']['type']          = 'text';
		$d['name']['object']['attrib']['css']           = 'namegen';
		$d['name']['object']['attrib']['customattribs'] = 'data-prefix="tmpfs_" data-length="8"';
		$d['name']['object']['attrib']['value']         = $this->volume.'_clone';
		$d['name']['object']['attrib']['maxlength']     = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}

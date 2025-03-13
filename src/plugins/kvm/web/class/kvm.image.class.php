<?php
/**
 * KVM Adds/Removes an Image from a Volume
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_image
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kvm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'kvm_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kvm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kvm_identifier';
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
		$storage_id       = $this->response->html->request()->get('storage_id');
		$storage          = new storage();
		$resource         = new resource();
		$deployment       = new deployment();
		$this->volgroup   = $this->response->html->request()->get('volgroup');
		$this->storage    = $storage->get_instance_by_id($storage_id);
		$this->resource   = $resource->get_instance_by_id($storage->resource_id);
		$this->deployment = $deployment->get_instance_by_id($storage->type);

		$this->response->add('storage_id', $storage_id);
		$this->response->add('volgroup', $this->volgroup);
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
		$response = $this->image();
		$this->response->params['reload'] = 'false';
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response)
		);
	}

	//--------------------------------------------
	/**
	 * Add/Remove image object
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function image() {
		$response = '';
		$errors       = array();
		$message      = array();
		$image_command = $this->response->html->request()->get('image_command');

		if( $image_command !== '' ) {
			switch ($image_command) {
				case 'add':
					$root_device = $this->response->html->request()->get('root_device');
					if ($this->deployment->type == 'kvm-gluster-deployment') {
						$image_name = $this->response->html->request()->get('image_name');
					} else {
						$image_name = basename($root_device);
					}

					// check if image name is not in use yet
					$image = new image();
					$image->get_instance_by_name($image_name);
					if (strlen($image->id)) {
						$errors[] = sprintf($this->lang['error_exists'], $image_name);
					} else {
						$tables = $this->openqrm->get('table');
						$image_fields = array();
						$image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
						$image_fields['image_name'] = $image_name;
						$image_fields['image_type'] = $this->deployment->type;
						$image_fields['image_rootfstype'] = 'local';
						$image_fields['image_storageid'] = $this->storage->id;
						$image_fields['image_comment'] = "Image Object for volume $image_name";
						$image_fields['image_rootdevice'] = $root_device;
						$image_fields['image_size'] = $this->response->html->request()->get('size');
						$image = new image();
						$image->add($image_fields);
						$message[] = sprintf($this->lang['msg_added_image'], $image_name);
					}
					break;

				case 'remove':
					$image_id = $this->response->html->request()->get('image_id');
					// check if image is not in use any more before removing
					$remove_error = 0;
					$appliance = new appliance();
					$appliance_id_list = $appliance->get_all_ids();
					foreach($appliance_id_list as $appliance_list) {
						$appliance_id = $appliance_list['appliance_id'];
						$app_image_remove_check = new appliance();
						$app_image_remove_check->get_instance_by_id($appliance_id);
						if ($app_image_remove_check->imageid == $image_id) {
							$image_is_used_by_appliance .= $appliance_id." ";
							$remove_error = 1;
						}
					}
					if ($remove_error == 1) {
						$errors[] = sprintf($this->lang['error_image_still_in_use'], $image_id, $image_is_used_by_appliance);
					} else {
						$image_remove = new image();
						$image_remove->remove($image_id);
						$message[] = sprintf($this->lang['msg_removed_image'], $image_id);
					}
					break;
				case 'sync':
					$image_id = $this->response->html->request()->get('image_id');
					$image = $this->openqrm->image();
					$image_fields['image_size'] = $this->response->html->request()->get('size');
					$image->update($image_id, $image_fields);
					$message[] = sprintf($this->lang['msg_synced_image'], $image_id);
					break;
			}
			if(count($errors) === 0) {
				$response = join('<br>', $message);
			} else {
				$msg = array_merge($errors, $message);
				$response = join('<br>', $msg);
			}
		} else {
			$response = '';
		}
		return $response;
	}


}

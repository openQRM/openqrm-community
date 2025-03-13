<?php
/**
 * iSCSI-Storage Snap Volume(s)
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class iscsi_storage_clone
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'iscsi_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "iscsi_storage_msg";
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'iscsi_identifier';
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
var $prefix_tab = 'iscsi_tab';
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
		$this->file = $this->openqrm->file();
		$this->user = $openqrm->user();
		$this->volume = $this->response->html->request()->get('volume');
		$this->response->params['volume'] = $this->volume;
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
		$t = $this->response->html->template($this->tpldir.'/iscsi-storage-clone.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add(sprintf($this->lang['label'], $this->volume), 'label');
		$t->add($response->form);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Snap
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
			$command     = $this->openqrm->get('basedir').'/plugins/iscsi-storage/bin/openqrm-iscsi-storage clone';
			$command    .= ' -n '.$this->volume;
			$command    .= ' -s '.$name;
			$command    .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
			$command	.= ' --openqrm-ui-user '.$this->user->name;
			$command	.= ' --openqrm-cmd-mode background';

			$statfile = $this->openqrm->get('basedir').'/plugins/iscsi-storage/web/storage/'.$storage->resource_id.'.iscsi.stat';
			if (file_exists($statfile)) {
				$lines = explode("\n", file_get_contents($statfile));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							$check = $line[2];
							if($name === $check) {
								$error = sprintf($this->lang['error_exists'], $name);
							}
						}
					}
				}
			}
			if(isset($error)) {
				$response->error = $error;
			} else {
				$file = $this->openqrm->get('basedir').'/plugins/iscsi-storage/web/storage/'.$resource->id.'.iscsi.'.$name.'.sync_progress';
				if($this->file->exists($file)) {
					$this->file->remove($file);
				}
				$resource->send_command($resource->ip, $command);
				while (!$this->file->exists($file)) {
		  			usleep(10000); // sleep 10ms to unload the CPU
		  			clearstatcache();
				}

				$volume_path = '/dev/'.$name.'/1';
				$orgigin_image = new image();
				$orgigin_image->get_instance_by_name($this->volume);

				$tables = $this->openqrm->get('table');
				$image_fields = array();
				$image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$image_fields['image_name'] = $name;
				$image_fields['image_type'] = $deployment->type;
				$image_fields['image_rootfstype'] = 'ext3';
				$image_fields['image_storageid'] = $storage->id;
				$image_fields['image_comment'] = "Image Object for volume $name";
				$image_fields['image_rootdevice'] = $volume_path;
				$image_fields['image_size'] = $orgigin_image->size;
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
	 * @param string $mode
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
		$d['name']['object']['attrib']['customattribs'] = 'data-prefix="iscsi" data-length="6"';
		$d['name']['object']['attrib']['value']         = $this->volume.'c';
		$d['name']['object']['attrib']['maxlength']     = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}

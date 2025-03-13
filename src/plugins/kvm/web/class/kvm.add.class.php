<?php
/**
 * KVM Add new Volume
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_add
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
	function __construct($openqrm, $response, $controller) {
		$this->controller = $controller;
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
		$this->set_max();
		$response = $this->add();
		if(isset($response->msg)) {
			// wizard
			if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 3) {
				$this->controller->__reload('lv');
				$this->response->redirect(
					$this->response->html->thisfile.'?base=appliance&appliance_action=step'.$this->user->wizard_step.'&appliance_id='.$this->user->wizard_id.'&image_id='.$response->image_id
				);
			} else {
				$this->response->params['reload'] = 'false';
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response->msg)
				);
			}
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/kvm-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->volgroup), 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['lang_name_generate'], 'lang_name_generate');
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
			if($form->get_request('size') > $this->max) {
				$form->set_error('size', sprintf($this->lang['error_size_exeeded'], $this->max));
			}
			if(!$form->get_errors()) {
				$name     = $form->get_request('name');
				$create_type	= $form->get_request('type');
				if ($create_type == '') {
					$create_type = 'raw';
				}
				$command  = $this->openqrm->get('basedir').'/plugins/kvm/bin/openqrm-kvm add';
				$command .= ' -n '.$name.' -m '.$form->get_request('size');
				$command .= ' -o '.$create_type;
				$command .= ' -t '.$this->deployment->type.' -v '.$this->volgroup;
				$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
				$command .= ' --openqrm-ui-user '.$this->user->name;
				$command .= ' --openqrm-cmd-mode background';

				$statfile = $this->openqrm->get('basedir').'/plugins/kvm/web/storage/'.$this->resource->id.'.'.$this->volgroup.'.lv.stat';
				if ($this->file->exists($statfile)) {
					$lines = explode("\n", $this->file->get_contents($statfile));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								$check = $line[1];
								if($name === $check) {
									$error = sprintf($this->lang['error_exists'], $name);
								}
							}
						}
					}
				}
				// check for image name
				$image = new image();
				$image->get_instance_by_name($name);
				if ((isset($image->id)) && ($image->id > 1)) {
					$error = sprintf($this->lang['error_exists'], $name);
				}

				if(isset($error)) {
					$response->error = $error;
				} else {
					if($this->file->exists($statfile)) {
						$this->file->remove($statfile);
					}
					$this->resource->send_command($this->resource->ip, $command);
					while (!$this->file->exists($statfile)) {
						usleep(10000); // sleep 10ms to unload the CPU
						clearstatcache();
					}
					// add check that volume $name is now in the statfile
					$created = false;
					$bf_volume_path = "";
					$lines = explode("\n", $this->file->get_contents($statfile));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								$check = $line[1];
								if($name === $check) {
									$created = true;
									$bf_volume_path = $line[2];
									break;
								}
							}
						}
					}

					if ($created) {
						$tables = $this->openqrm->get('table');
						$image_fields = array();
						$image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
						$image_fields['image_name'] = $name;
						$image_fields['image_type'] = $this->deployment->type;
						$image_fields['image_rootfstype'] = 'local';
						$image_fields['image_storageid'] = $this->storage->id;
						$image_fields['image_size'] = $form->get_request('size');
						$image_fields['image_comment'] = "Image Object for volume $name";
						switch($this->deployment->type) {
						case 'kvm-lvm-deployment':
							$image_fields['image_rootdevice'] = '/dev/'.$this->volgroup.'/'.$name;
							break;
						case 'kvm-bf-deployment':
							$image_fields['image_rootdevice'] = $bf_volume_path;
							break;
						case 'kvm-gluster-deployment':
							$image_fields['image_rootdevice'] = "gluster://".$this->resource->ip."/".$this->volgroup."/".$name;
							break;
						case 'kvm-ceph-deployment':
							$image_fields['image_rootdevice'] = "rbd:".$this->volgroup."/".$name;
							break;
						}
						$image = new image();
						$image->add($image_fields);
						$response->msg = sprintf($this->lang['msg_added'], $name);
						// save image id in response for the wizard
						$response->image_id = $image_fields["image_id"];

					} else {
						$response->msg = sprintf($this->lang['msg_add_failed'], $name);
					}
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

		// create the image type select array
		$show_type_select = false;
		switch($this->deployment->type) {
			case 'kvm-bf-deployment':
				$image_types[] = array('qcow2','qcow2');
				$image_types[] = array('raw','raw');
				$image_types[] = array('qcow','qcow');
				$image_types[] = array('cow','cow');
				$show_type_select = true;
				break;
		}

		// if we come from the wizard suggest the server name
		$vm_volume_name_suggestion = '';
		if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 3) {
			$appliance = new appliance();
			$appliance->get_instance_by_id($this->user->wizard_id);
			$vm_volume_name_suggestion = $appliance->name;
		}
		

		$d['name']['label']                             = $this->lang['form_name'];
		$d['name']['required']                          = true;
		$d['name']['validate']['regex']                 = '/^[a-z0-9._]+$/i';
		$d['name']['validate']['errormsg']              = sprintf($this->lang['error_name'], 'a-z0-9._');
		$d['name']['object']['type']                    = 'htmlobject_input';
		$d['name']['object']['attrib']['id']            = 'name';
		$d['name']['object']['attrib']['name']          = 'name';
		$d['name']['object']['attrib']['type']          = 'text';
		$d['name']['object']['attrib']['css']           = 'namegen';
		$d['name']['object']['attrib']['customattribs'] = 'data-prefix="kvm" data-length="6"';
		$d['name']['object']['attrib']['value']         = $vm_volume_name_suggestion;
		$d['name']['object']['attrib']['maxlength']     = 50;

		$d['size']['label']                         = sprintf($this->lang['form_size'], number_format($this->max, 0, '', ''));
		$d['size']['required']                      = true;
		$d['size']['validate']['regex']             = '/^[0-9]+$/i';
		$d['size']['validate']['errormsg']          = sprintf($this->lang['error_size'], '0-9');
		$d['size']['object']['type']                = 'htmlobject_input';
		$d['size']['object']['attrib']['name']      = 'size';
		$d['size']['object']['attrib']['type']      = 'text';
		$d['size']['object']['attrib']['value']     = '';
		$d['size']['object']['attrib']['maxlength'] = 50;

		if ($show_type_select) {
			$d['type']['label']                       = $this->lang['form_type'];
			$d['type']['object']['type']              = 'htmlobject_select';
			$d['type']['object']['attrib']['name']    = 'type';
			$d['type']['object']['attrib']['index']   = array(0,1);
			$d['type']['object']['attrib']['options'] = $image_types;
		} else {
			$d['type'] = '';
		}

		$form->add($d);
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Set max
	 *
	 * @access protected
	 * @return bool
	 */
	//--------------------------------------------
	function set_max() {
		$statfile = $this->openqrm->get('basedir').'/plugins/kvm/web/storage/'.$this->resource->id.'.vg.stat';
		if ($this->file->exists($statfile)) {
			$lines = explode("\n", $this->file->get_contents($statfile));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						if($line[0] === $this->volgroup) {
							$max = $line[6];
							if(strpos($max, 'g') !== false) {
								$max = str_replace('g', '', $max);
								$max = floor(($max * 1000) * 1.0486);
								$this->max = $max;
								return true;
							} else {
								$max = str_replace('m', '', $max);
								$max = (int)$max;
								$this->max = $max;
								return true;
							}
						}
					}
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

}

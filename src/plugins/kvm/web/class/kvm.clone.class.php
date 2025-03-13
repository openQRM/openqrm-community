<?php
/**
 * KVM clone Volume
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_clone
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
	function __construct($openqrm, $response) {
		$this->response                 = $response;
		$this->file                     = $openqrm->file();
		$this->openqrm                  = $openqrm;
		$this->user						= $openqrm->user();
		$this->volgroup                 = $this->response->html->request()->get('volgroup');
		$this->lvol                     = $this->response->html->request()->get('lvol');
		$storage_id                     = $this->response->html->request()->get('storage_id');
		$storage                        = new storage();
		$resource                       = new resource();
		$deployment                     = new deployment();
		$this->storage                  = $storage->get_instance_by_id($storage_id);
		$this->resource                 = $resource->get_instance_by_id($storage->resource_id);
		$this->deployment               = $deployment->get_instance_by_id($storage->type);

		$this->response->add('storage_id', $storage_id);
		$this->response->add('volgroup', $this->volgroup);
		$this->response->add('lvol', $this->lvol);
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
					$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response->msg)
				);
			}
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/kvm-clone.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->lvol), 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * clone
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function duplicate() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$name     = $form->get_request('name');
			$command  = $this->openqrm->get('basedir').'/plugins/kvm/bin/openqrm-kvm clone';
			$command .= ' -t '.$this->deployment->type;
			$command .= ' -v '.$this->volgroup;
			$command .= ' -n '.$this->lvol;
			$command .= ' -s '.$name;
			$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
			$command .= ' --caching false';
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
				$file = $this->openqrm->get('basedir').'/plugins/kvm/web/storage/'.$this->resource->id.'.lvm.'.$name.'.sync_progress';
				if($this->file->exists($file)) {
					$this->file->remove($file);
				}
				$root_device_ident = $this->openqrm->get('basedir').'/plugins/kvm/web/storage/'.$this->resource->id.'.'.$name.'.root_device';
				if($this->file->exists($root_device_ident)) {
					$this->file->remove($root_device_ident);
				}
				$this->resource->send_command($this->resource->ip, $command);
				while (!$this->file->exists($file))
				{
				  usleep(10000);
				  clearstatcache();
				}
				// wait for the root-device identifier
				while (!$this->file->exists($root_device_ident))
				{
				  usleep(10000);
				  clearstatcache();
				}
				$root_device = trim($this->file->get_contents($root_device_ident));
				$this->file->remove($root_device_ident);

				$tables = $this->openqrm->get('table');
				$image_fields = array();
				$image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$image_fields['image_name'] = $name;
				$image_fields['image_type'] = $this->deployment->type;
				$image_fields['image_rootfstype'] = 'local';
				$image_fields['image_storageid'] = $this->storage->id;
				$image_fields['image_comment'] = "Image Object for volume $name";
				$image_fields['image_rootdevice'] = $root_device;
				$image_fields['image_size'] = 0;
				$image = new image();
				$image->add($image_fields);
				$response->image_id = $image_fields["image_id"];
				$response->msg = sprintf($this->lang['msg_cloned'], $this->lvol, $name);
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
	
		$d['name']['label']                         = $this->lang['form_name'];
		$d['name']['required']                      = true;
		$d['name']['validate']['regex']             = '/^[a-z0-9._]+$/i';
		$d['name']['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9._');
		$d['name']['object']['type']                = 'htmlobject_input';
		$d['name']['object']['attrib']['name']      = 'name';
		$d['name']['object']['attrib']['type']      = 'text';
		$d['name']['object']['attrib']['value']     = $this->lvol.'c';
		$d['name']['object']['attrib']['maxlength'] = 50;

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
		$vgmax = '';
		$kvmax = '';
		$statfile = $this->openqrm->get('basedir').'/plugins/kvm/web/storage/'.$this->resource->id.'.'.$this->volgroup.'.lv.stat';
		if ($this->file->exists($statfile)) {
			$lines = explode("\n", $this->file->get_contents($statfile));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						if($line[1] === $this->lvol) {
							$kvmax = str_replace('.', '', $line[4]);
							$kvmax = str_replace('m', '', $kvmax);
							$kvmax = (int)$kvmax / 100;
						}
					}
				}
			}
		}
		return $kvmax;
	}

}

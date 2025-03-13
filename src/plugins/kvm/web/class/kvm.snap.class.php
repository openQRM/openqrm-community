<?php
/**
 * KVM snap Volume
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_snap
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
		$this->response   = $response;
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
		$this->user	  = $openqrm->user();
		$this->lvol       = $this->response->html->request()->get('lvol');
		$this->volgroup   = $this->response->html->request()->get('volgroup');
		$storage_id       = $this->response->html->request()->get('storage_id');
		$storage          = new storage();
		$resource         = new resource();
		$deployment       = new deployment();
		$this->storage    = $storage->get_instance_by_id($storage_id);
		$this->resource   = $resource->get_instance_by_id($storage->resource_id);
		$this->deployment = $deployment->get_instance_by_id($storage->type);

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
		$this->set_max();
		$response = $this->snap();
		if(isset($response->msg)) {
			// wizard
			if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 3) {
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
		$t = $this->response->html->template($this->tpldir.'/kvm-snap.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->lvol), 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * snap
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function snap() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			if($form->get_request('size') > $this->max) {
				$form->set_error('size', sprintf($this->lang['error_size_exeeded'], number_format($this->max, 0, '', '')));
			}
			if(!$form->get_errors()) {

				$name     = $form->get_request('name');
				$name_check = $name;
				$command  = $this->openqrm->get('basedir').'/plugins/kvm/bin/openqrm-kvm snap';
				$command .= ' -t '.$this->deployment->type;
				$command .= ' -v '.$this->volgroup;
				$command .= ' -n '.$this->lvol;
				if ($this->deployment->type == 'kvm-lvm-deployment') {
					// snapshot size is only valid for kvm-lvm-deployment
					$command .= ' -m '.$form->get_request('size');
				}
				$command .= ' -s '.$name;
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
								if ($this->deployment->type == 'kvm-ceph-deployment') {
									$check = str_replace('%', '@', $check);
									$name_check = $this->lvol."@".$name;
								}
								if($name_check === $check) {
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

					// create the image
					$tables = $this->openqrm->get('table');
					$image_fields = array();
					$image_fields["image_id"] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					if ($this->deployment->type == 'kvm-ceph-deployment') {
						$image_fields['image_name'] = $this->lvol . "@" . $name;
						$image_origin = new image();
						$image_origin->get_instance_by_name($this->lvol);
						$image_fields['image_size'] = $image_origin->size;
					} else  {
						$image_fields['image_name'] = $name;
						$image_fields['image_size'] = 0;
					}
					$image_fields['image_type'] = $this->deployment->type;
					$image_fields['image_rootfstype'] = 'local';
					$image_fields['image_storageid'] = $this->storage->id;
					$image_fields['image_comment'] = "Image Object for volume $name";
					$image = $this->openqrm->image();
					$image->add($image_fields);

					if($this->file->exists($statfile)) {
						$this->file->remove($statfile);
					}
					$this->resource->send_command($this->resource->ip, $command);
					while (!$this->file->exists($statfile)) {
						usleep(10000); // sleep 10ms to unload the CPU
						clearstatcache();
					}

					$created = false;
					$bf_volume_path = "";
					$lines = explode("\n", $this->file->get_contents($statfile));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								$check = $line[1];
								if ($this->deployment->type == 'kvm-ceph-deployment') {
									$check = str_replace('%', '@', $check);
									$name_check = $this->lvol."@".$name;
								}
								if($name_check === $check) {
									$created = true;
									$bf_volume_path = $line[2];
									break;
								}
							}
						}
					}

					if ($created) {
						// set image rootdevice
						switch($this->deployment->type) {
							case 'kvm-lvm-deployment':
								$rootdevice = '/dev/'.$this->volgroup.'/'.$name;
								break;
							case 'kvm-bf-deployment':
								$rootdevice = $bf_volume_path;
								break;
							case 'kvm-gluster-deployment':
								$rootdevice = "gluster://".$this->resource->ip."/".$this->volgroup."/".$name;
								break;
							case 'kvm-ceph-deployment':
								$rootdevice = "rbd:".$this->volgroup."/".$this->lvol."@".$name;
								break;
							}
						$image->update($image_fields["image_id"], array('image_rootdevice' => $rootdevice));
						$response->image_id = $image_fields["image_id"];
						$response->msg = sprintf($this->lang['msg_snaped'], $this->lvol, $name);
					} else {
						// if created failed remove the image
						$image->remove_by_name($name);
						$response->msg = sprintf($this->lang['msg_snap_failed'], $this->lvol, $name);
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
		$form = $response->get_form($this->actions_name, 'snap');

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
		$d['name']['object']['attrib']['value']     = $this->lvol.'s';
		$d['name']['object']['attrib']['maxlength'] = 50;

		switch($this->deployment->type) {
			case 'kvm-lvm-deployment':
				$d['size']['label']                         = sprintf($this->lang['form_size'], number_format($this->max, 0, '', ''));
				$d['size']['required']                      = true;
				$d['size']['validate']['regex']             = '/^[0-9]+$/i';
				$d['size']['validate']['errormsg']          = sprintf($this->lang['error_size'], '0-9');
				$d['size']['object']['type']                = 'htmlobject_input';
				$d['size']['object']['attrib']['name']      = 'size';
				$d['size']['object']['attrib']['type']      = 'text';
				$d['size']['object']['attrib']['value']     = '';
				$d['size']['object']['attrib']['maxlength'] = 50;
				break;
			default:
				$d['size'] = '';
				break;
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
		$statfile = $this->openqrm->get('basedir').'/plugins/kvm/web/storage/'.$this->resource->id.'.vg.stat';
		if ($this->file->exists($statfile)) {
			$lines = explode("\n", $this->file->get_contents($statfile));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						if($line[0] === $this->volgroup) {
							$vgmax = str_replace('.', '', $line[6]);
							$vgmax = str_replace('m', '', $vgmax);
							$vgmax = (int)$vgmax / 100;
						}
					}
				}
			}
		}

		if(	$vgmax < $kvmax ) {
			$max = $vgmax;
		} else {
			$max = $kvmax;
		}
		$this->max = $max;
		return true;
	}

}

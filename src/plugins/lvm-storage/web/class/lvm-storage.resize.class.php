<?php
/**
 * lvm-Storage resize Volume
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class lvm_storage_resize
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
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'lvm_identifier';
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
		$this->volgroup = $this->response->html->request()->get('volgroup');
		$this->lvol = $this->response->html->request()->get('lvol');

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
		$response = $this->resize();
		if(isset($response->msg)) {
			$this->response->params['reload'] = 'false';
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/lvm-storage-resize.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add(sprintf($this->lang['label'], $this->lvol), 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * resize
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function resize() {
		$this->set_max();
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			if($form->get_request('size') > $this->max) {
				$form->set_error('size', sprintf($this->lang['error_size_exeeded'], number_format($this->max, 0, '', '')));
			}
			if($form->get_request('size') < $this->min) {
				$form->set_error('size', sprintf($this->lang['error_size_undercut'], number_format($this->min, 0, '', '')));
			}
			if(!$form->get_errors()) {
				$name        = $form->get_request('name');
				$command     = $this->openqrm->get('basedir').'/plugins/lvm-storage/bin/openqrm-lvm-storage resize';
				$command    .= ' -t '.$this->deployment->type;
				$command    .= ' -v '.$this->volgroup;
				$command    .= ' -n '.$this->lvol;
				$command    .= ' -m '.($form->get_request('size') - $this->min);
				$command    .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
				$command .= ' --openqrm-ui-user '.$this->user->name;
				$command .= ' --openqrm-cmd-mode background';
				if($this->deployment->type === 'lvm-iscsi-deployment') {
					$image    = new image();
					$command .= ' -i '.$image->generatePassword(12);
				}
				$statfile = $this->openqrm->get('basedir').'/plugins/lvm-storage/web/storage/'.$this->resource->id.'.'.$this->volgroup.'.lv.stat';
				if(file_exists($statfile)) {
					unlink($statfile);
				}
				$this->resource->send_command($this->resource->ip, $command);
				while (!file_exists($statfile)) {
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
				
				// get origin image object
				$origin_image = new image();
				$origin_image->get_instance_by_name($this->lvol);
				// set image size
				$image_fields = array();
				$image_fields['image_size'] = $form->get_request('size');
				$origin_image->update($origin_image->id, $image_fields);
				$response->msg = sprintf($this->lang['msg_resized'], $this->lvol);
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
		$form = $response->get_form($this->actions_name, 'resize');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d['size']['label']                         = sprintf($this->lang['size'],number_format($this->min, 0, '', ''), number_format($this->max, 0, '', ''));
		$d['size']['required']                      = true;
		$d['size']['validate']['regex']             = '/^[0-9]+$/i';
		$d['size']['validate']['errormsg']          = sprintf($this->lang['error_size'], '0-9');
		$d['size']['object']['type']                = 'htmlobject_input';
		$d['size']['object']['attrib']['name']      = 'size';
		$d['size']['object']['attrib']['type']      = 'text';
		$d['size']['object']['attrib']['value']     = '';
		$d['size']['object']['attrib']['maxlength'] = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Set min and max
	 *
	 * @access protected
	 */
	//--------------------------------------------
	function set_max() {
		$vgmax = '';
		$lvmax = '';
		$statfile = $this->openqrm->get('basedir').'/plugins/lvm-storage/web/storage/'.$this->resource->id.'.'.$this->volgroup.'.lv.stat';
		if (file_exists($statfile)) {
			$lines = explode("\n", file_get_contents($statfile));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						if($line[1] === $this->lvol) {
							$lvmax = str_replace('.', '', $line[4]);
							$lvmax = str_replace('m', '', $lvmax);
							$lvmax = (int)$lvmax / 100;
							$this->min = $lvmax;
						}
					}
				}
			}
		}
		$statfile = $this->openqrm->get('basedir').'/plugins/lvm-storage/web/storage/'.$this->resource->id.'.vg.stat';
		if (file_exists($statfile)) {
			$lines = explode("\n", file_get_contents($statfile));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						if($line[0] === $this->volgroup) {
							$vgmax = str_replace('.', '', $line[6]);
							$vgmax = str_replace('m', '', $vgmax);
							$vgmax = (int)$vgmax / 100;
							$this->max = $vgmax + $lvmax;
						}
					}
				}
			}
		}
	}

}

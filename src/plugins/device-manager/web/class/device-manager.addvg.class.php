<?php
/**
 * device-manager Add LVM Volume Group
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class device_manager_addvg
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name;
/**
* message param
* @access public
* @var string
*/
var $message_param;
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab;
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name;
/**
* path to device-managers
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array(
		'label' => 'Add Volume Group to storage %s',
		'partition' => 'Partition',
		'name' => 'Name',
		'extend' => 'extend partition',
		'confirm_text' => 'All Data on %s will be erased.<br>Are you sure to continue?',
		'msg_added' => 'Successfully added Volume Group %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
		'error_name' => 'Name must be %s only'
	);

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
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->tpldir   = $this->rootdir.'/plugins/device-manager/tpl';

		$id = $this->response->html->request()->get('storage_id');
		$this->response->add('storage_id', $id);

		$storage = $this->openqrm->storage();
		$this->storage = $storage->get_instance_by_id($id);

		$resource = $this->openqrm->resource();
		$this->resource = $resource->get_instance_by_id($this->storage->resource_id);
		
		$this->statfile = $this->openqrm->get('basedir').'/plugins/device-manager/web/storage/'.$this->resource->id.'.device.stat';
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action() {
		$response = $this->addvg();
		$deployment = new deployment();
		$deployment->get_instance_by_id($this->storage->type);

		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}

		$data['plugin'] = $deployment->storagetype;
		$data['label'] = sprintf($this->lang['label'], $this->storage->name);
		$data['baseurl'] = $this->openqrm->get('baseurl');
		$data['thisfile']    = $response->html->thisfile;
		$t = $response->html->template($this->tpldir.'/device-manager-addvg.tpl.php');
		$t->add($response->form);
		$t->add($data);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Ad Volume Group
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function addvg() {
		// load partition file
		$file = $this->statfile;
		$command = $this->openqrm->get('basedir').'/plugins/device-manager/bin/openqrm-device-manager list';
		$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
		if($this->file->exists($file)) {
			$this->file->remove($file);
		}
		$this->resource->send_command($this->resource->ip, $command);
		while (!$this->file->exists($file)) // check if the data file has been modified
		{
			usleep(10000); // sleep 10ms to unload the CPU
			clearstatcache();
		}
		// get response
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			if($response->html->request()->get('confirm') !== '') {
				$file = $this->statfile;
				$command  = $this->openqrm->get('basedir').'/plugins/device-manager/bin/openqrm-device-manager add_vg';
				$command .= ' -d '.$form->get_request('device');
				$command .= ' -v '.$form->get_request('name');
				$command .= ' -u '.$this->openqrm->admin()->name;
				$command .= ' -p '.$this->openqrm->admin()->password;
				$command .= ' --openqrm-ui-user '.$this->user->name;
				$command .= ' --openqrm-cmd-mode regular';
				if($response->html->request()->get('extend') !== '') {
					$command .= ' -a true';
				}
				if($this->file->exists($file)) {
					$this->file->remove($file);
				}
				$this->resource->send_command($this->resource->ip, $command);
				while (!$this->file->exists($file)) // check if the data file has been modified
				{
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
				$result = trim($this->file->get_contents($file));
				if($result === 'ok') {
					echo $command;
					$response->msg = sprintf($this->lang['msg_added'], $form->get_request('name'));
				}
				else if($result !== 'ok') {
					$response->error = $result;
				}
			} else {
				$response = $this->get_response('confirm');
			}
		} else {
			if($form->get_errors()) {
				$_REQUEST[$this->message_param] = implode("<br>", $form->get_errors());
			}
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param enum $mode [confirm]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response($mode = '') {
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'addvg');

		if($mode !== 'confirm') {
			$result   = array();
			if($this->file->exists($this->statfile)) {
				$result = trim($this->file->get_contents($this->statfile));
				$result = explode("\n", $result);
			}
			$select = array();
			foreach($result as $v) {
				$select[] = array($v);
			}
			$d['select']['label']                       = $this->lang['partition'];
			$d['select']['required']                    = true;
			$d['select']['object']['type']              = 'htmlobject_select';
			$d['select']['object']['attrib']['name']    = 'device';
			$d['select']['object']['attrib']['index']   = array(0,0);
			$d['select']['object']['attrib']['options'] = $select;

			$d['name']['label']                    = $this->lang['name'];
			$d['name']['required']                 = true;
			$d['name']['validate']['regex']        = '/^[a-z0-9_-]+$/i';
			// TODO translation in kvm, .., etc
			$d['name']['validate']['errormsg']     = sprintf('Name must be %s only', 'a-z0-9_-');
			$d['name']['object']['type']           = 'htmlobject_input';
			$d['name']['object']['attrib']['name'] = 'name';

			$d['extend']['label']                    =  $this->lang['extend'];
			$d['extend']['object']['type']           = 'htmlobject_input';
			$d['extend']['object']['attrib']['type'] = 'checkbox';
			$d['extend']['object']['attrib']['name'] = 'extend';

			$d['confirm']['object']['type']            = 'htmlobject_input';
			$d['confirm']['object']['attrib']['type']  = 'hidden';
			$d['confirm']['object']['attrib']['name']  = 'confirm';
			$d['confirm']['object']['attrib']['value'] = '';

			$d['confirm_text'] = '';
		}

		if($mode === 'confirm') {
			$d['select']['object']['type']            = 'htmlobject_input';
			$d['select']['object']['attrib']['type']  = 'hidden';
			$d['select']['object']['attrib']['name']  = 'device';
			$d['select']['object']['attrib']['value'] = $response->html->request()->get('device');

			$d['name']['object']['type']           = 'htmlobject_input';
			$d['name']['object']['attrib']['type']  = 'hidden';
			$d['name']['object']['attrib']['name'] = 'name';
			$d['name']['object']['attrib']['value'] = $response->html->request()->get('name');

			$d['extend'] = [];
			if($response->html->request()->get('extend') !== '') {
				$d['extend']['static']                    = true;
				$d['extend']['object']['type']            = 'htmlobject_input';
				$d['extend']['object']['attrib']['type']  = 'hidden';
				$d['extend']['object']['attrib']['name']  = 'extend';
				$d['extend']['object']['attrib']['value'] = 'true';
			}

			$d['confirm']['static']                    = true;
			$d['confirm']['object']['type']            = 'htmlobject_input';
			$d['confirm']['object']['attrib']['type']  = 'hidden';
			$d['confirm']['object']['attrib']['name']  = 'confirm';
			$d['confirm']['object']['attrib']['value'] = 'true';

			$div = $response->html->div();
			$div->name = 'xxx';
			$div->style = 'margin: 0 0 20px 0;';
			$div->add(sprintf($this->lang['confirm_text'], $response->html->request()->get('device')));
			$form->add($div, 'confirm_text');
		}

		$form->add($d);

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$cancel = $form->get_elements('cancel');
		$cancel->handler = 'onclick="cancel();"';
		$form->add($cancel, 'cancel');

		$form->display_errors = false;

		$response->form = $form;
		return $response;
	}

}

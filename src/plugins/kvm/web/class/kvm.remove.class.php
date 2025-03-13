<?php
/**
 * KVM Remove Volume(s)
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_remove
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
		$this->response = $response;
		$this->file     = $openqrm->file();
		$this->openqrm  = $openqrm;
		$this->user     = $openqrm->user();
		$this->volgroup = $this->response->html->request()->get('volgroup');
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
		$response = $this->remove();
		if(isset($response->msg)) {
			$this->response->params['reload'] = 'false';
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/kvm-remove.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Remove
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function remove() {
		$response = $this->get_response();
		$lvols  = $response->html->request()->get($this->identifier_name);
		$form     = $response->form;
		if( $lvols !== '' ) {

			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			foreach($lvols as $ex) {
				$d['param_f'.$i]['label']                       = $ex;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $ex;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;		
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$name       = $this->openqrm->admin()->name;
				$pass       = $this->openqrm->admin()->password;
				$storage    = new storage();
				$resource   = new resource();
				$deployment = new deployment();

				$storage->get_instance_by_id($this->response->html->request()->get('storage_id'));
				$resource->get_instance_by_id($storage->resource_id);
				$deployment->get_instance_by_id($storage->type);

				$errors     = array();
				$message    = array();
				foreach($lvols as $key => $lvol) {
					// check if an appliance is still using the volume as an image
					$image = new image();
					$image->get_instance_by_name($lvol);

					// check if it is still in use
					$appliance = new appliance();
					$appliances_using_resource = $appliance->get_ids_per_image($image->id);
					if (count($appliances_using_resource) > 0) {
						$appliances_using_resource_str = implode(",", $appliances_using_resource[0]);
						$errors[] = sprintf($this->lang['msg_vm_image_still_in_use'], $lvol, $image->id, $appliances_using_resource_str);
					} else {
						$command  = $this->openqrm->get('basedir').'/plugins/kvm/bin/openqrm-kvm remove';
						$command .= ' -n '.$lvol;
						$command .= ' -v '.$this->volgroup;
						$command .= ' -t '.$deployment->type;
						$command .= ' -u '.$name.' -p '.$pass;
						$command .= ' --openqrm-ui-user '.$this->user->name;
						$command .= ' --openqrm-cmd-mode background';

						$file = $this->openqrm->get('basedir').'/plugins/kvm/web/storage/'.$resource->id.'.'.$this->volgroup.'.lv.stat';
						if($this->file->exists($file)) {
							$this->file->remove($file);
						}
						$resource->send_command($resource->ip, $command);
						while (!$this->file->exists($file)) {
							usleep(10000); // sleep 10ms to unload the CPU
							clearstatcache();
						}

						$form->remove($this->identifier_name.'['.$key.']');
						$message[] = sprintf($this->lang['msg_removed'], $lvol);
						// remove the image of the volume
						$image->remove_by_name($lvol);
					}

				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
				}
			}
		} else {
			$response->msg = '';
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
		$this->response->add('storage_id', $this->response->html->request()->get('storage_id'));
		$this->response->add('volgroup', $this->response->html->request()->get('volgroup'));
		$this->response->add($this->identifier_name.'[]', '');

		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'remove');
		$response->form = $form;
		return $response;
	}

}

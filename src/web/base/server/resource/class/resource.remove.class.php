<?php
/**
 * Remove resource
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class resource_remove
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'resource_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "resource_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'resource_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'resource_identifier';
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
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/resource-remove.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
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
		$resources = $response->html->request()->get($this->identifier_name);
		$force_remove = $response->html->request()->get('force_remove');
		$form     = $response->form;
		$resource = new resource();
		if( $resources !== '' ) {

			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			foreach($resources as $id) {
				$resource = $resource->get_instance_by_id($id);
				$d['param_f'.$i]['label']                       = $resource->hostname;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $id;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;		
				$i++;
			}
			
			$d['force_remove']['label']                       = $this->lang['msg_force_remove'];
			$d['force_remove']['object']['type']              = 'htmlobject_input';
			$d['force_remove']['object']['attrib']['type']    = 'checkbox';
			$d['force_remove']['object']['attrib']['name']    = 'force_remove';
			$d['force_remove']['object']['attrib']['value']   = 1;
			$d['force_remove']['object']['attrib']['checked'] = false;
			
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$errors     = array();
				$message    = array();
				foreach($resources as $key => $id) {
					$resource = $resource->get_instance_by_id($id);
					if ($force_remove != 1) {
						// check that the state is poweroff or error
						if (($resource->state != 'error') && ($resource->state != 'off') && ($resource->state != 'unknown')) {
							$message[] = sprintf($this->lang['msg_still_active'], $id);
							continue;
						}
						// check that resource is not still used by an appliance
						$resource_is_used_by_appliance = "";
						$remove_error = 0;
						$appliance = new appliance();
						$appliance_id_list = $appliance->get_all_ids();
						foreach($appliance_id_list as $appliance_list) {
							$appliance_id = $appliance_list['appliance_id'];
							$app_resource_remove_check = new appliance();
							$app_resource_remove_check->get_instance_by_id($appliance_id);
							if ($app_resource_remove_check->resources == $id) {
								$resource_is_used_by_appliance .= $appliance_id." ";
								$remove_error = 1;
							}
						}
						if ($remove_error == 1) {
							$message[] = sprintf($this->lang['msg_not_removing'], $id, $resource_is_used_by_appliance);
							continue;
						}
						// check that this is a physical resource, VMs are removed through their VM Manager
						if ($resource->vtype != 1) {
							$virtualization = new virtualization();
							$virtualization->get_instance_by_id($resource->vtype);
							$virtualization_name = str_replace('-vm', '', $virtualization->type);
							$message[] = sprintf($this->lang['msg_not_removing_vm'], $id, $virtualization_name);
							continue;
						}
					}
					$resource->remove($id, $resource->mac);
					$form->remove($this->identifier_name.'['.$key.']');
					$message[] = sprintf($this->lang['msg'], $id);
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
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'remove');
		$response->form = $form;
		return $response;
	}

}

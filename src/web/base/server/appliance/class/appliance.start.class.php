<?php
/**
 * Appliance Start
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class appliance_start
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'appliance_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "appliance_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'appliance_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'appliance_identifier';
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
		$response = $this->start();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
			return $response;
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}

		$t = $this->response->html->template($this->tpldir.'/appliance-start.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Start
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function start() {

		$response = $this->get_response();
		$appliances = $response->html->request()->get($this->identifier_name);
		$form     = $response->form;
		$appliance = new appliance();
		if( $appliances !== '' ) {

			// auto submit
			// $_REQUEST[$response->id]['submit'] = true;

			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			foreach($appliances as $id) {
				$appliance = $appliance->get_instance_by_id($id);
				$d['param_f'.$i]['label']                       = $appliance->name;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $id;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$errors     = array();
				$message    = array();
				foreach($appliances as $key => $id) {
					$appliance = $appliance->get_instance_by_id($id);

					$resource = new resource();
					if ($appliance->resources <0) {
						// an appliance with resource auto-select enabled
						$appliance_virtualization=$appliance->virtualization;
						$appliance->find_resource($appliance_virtualization);
						$appliance->get_instance_by_id($id);
						if ($appliance->resources <0) {
							$errors[] = sprintf($this->lang['msg_no_resource'], $id);
							continue;
						}
					}
					$resource->get_instance_by_id($appliance->resources);
					if ($appliance->resources == 0) {
						$errors[] = sprintf($this->lang['msg_always_active'], $id);
						continue;
					}
					if (!strcmp($appliance->state, "active"))  {
						$errors[] = sprintf($this->lang['msg_already_active'], $id);
						continue;
					}
					// check that resource is idle
					$app_resource = new resource();
					$app_resource->get_instance_by_id($appliance->resources);
					// resource has ip ?
					if (!strcmp($app_resource->ip, "0.0.0.0")) {
						$errors[] = sprintf($this->lang['msg_reource_no_ip'], $appliance->resources);
						$response->redirect('?base=resource&resource_filter=&resource_type_filter=&resource_action=edit&resource_id='.$app_resource->id);
						continue;
					}
					// resource assinged to imageid 1 ?
					if ($app_resource->imageid != 1) {
						$errors[] = sprintf($this->lang['msg_already_active'], $appliance->resources, $id);
						continue;
					}
					// resource active
					if (strcmp($app_resource->state, "active")) {
						$app_resource_virtualization = new virtualization();
						$app_resource_virtualization->get_instance_by_id($app_resource->vtype);
						// allow waking up physical systems via out-of-band-management plugins
						if (!strstr($app_resource_virtualization->name, "Host")) {
							if ($app_resource_virtualization->id != 1) {
								$errors[] = sprintf($this->lang['msg_already_active'], $appliance->resources, $id);
								continue;
							}
						}
					}
					// if no errors then we start the appliance
					$kernel = new kernel();
					$kernel->get_instance_by_id($appliance->kernelid);
					// send command to the openQRM-server
					$resource->send_command("127.0.0.1", "openqrm_assign_kernel ".$resource->id." ".$resource->mac." ".$kernel->name);
					$appliance->start();
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
		// add $this->identifier_name to response
		$response->add($this->identifier_name.'[]', $response->html->request()->get($this->identifier_name));
		$form = $response->get_form($this->actions_name, 'start');
		$response->form = $form;
		return $response;
	}

}

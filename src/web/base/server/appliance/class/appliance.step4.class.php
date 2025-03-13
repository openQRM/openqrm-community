<?php
/**
 * Appliance step4 (Kernel)
 *
 * openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.
 *
 * All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.
 *
 * This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
 * The latest version of this license can be found here: src/doc/LICENSE.txt
 *
 * By using this software, you acknowledge having read this license and agree to be bound thereby.
 *
 * http://openqrm-enterprise.com
 *
 * Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class appliance_step4
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
	function __construct($openqrm, $response)
	{
		$this->response = $response;
		$this->file = $openqrm->file();
		$this->openqrm = $openqrm;
		$this->user = $openqrm->user();
		
		$wid = $this->response->html->request()->get('appliance_wizard_id');
		if ($wid === '' && $this->response->html->request()->get('appliance_id') !== '') {
			$wid = $this->response->html->request()->get('appliance_id');
		}
		$this->apliance_wizard_id = $wid;
		$this->appliance = new appliance();
		$this->appliance->get_instance_by_id($wid);
		$this->response->add('appliance_wizard_id', $wid);
	}
	
	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action()
	{
		$direkt_redirect = false;
		
		$resource = new resource();
		$resource->get_instance_by_id($this->appliance->resources);
		$virtualization_id = $resource->vtype;
		if ($resource->id == 0) {
			$virtualization_id = 1;
		}
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($virtualization_id);
		$image = new image();
		$image->get_instance_by_id($this->appliance->imageid);
		
		$resourceinfo = '<b>Resource:</b> ' . $resource->id . ' / ' . $resource->ip . ' ' . $resource->hostname . ' - ' . $virtualization->name;
		$imageinfo = '<b>Image:</b> ' . $image->id . ' / ' . $image->name . ' (' . $image->type . ')';
		
		$assign_kernel_id = 0;
		// if not openQRM resource
		if ($resource->id != 0) {
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($resource->vtype);
		}
		if ($resource->id == 0) {
			$assign_kernel_id = 0;
			$direkt_redirect = true;
		} else if (strstr($resource->capabilities, "TYPE=local-server")) {
			$kernel = new kernel();
			$kernel->get_instance_by_name("resource" . $resource->id);
			$assign_kernel_id = $kernel->id;
			$direkt_redirect = true;
			// local-deployment VMs
		} else if (strstr($virtualization->type, "-vm-local")) {
			$assign_kernel_id = 1;
			$direkt_redirect = true;
		}
		
		// provide the kernel select form
		$response = $this->add();
		
		if ($direkt_redirect) {
			$fields['appliance_kernelid'] = $assign_kernel_id;
			$fields['appliance_wizard'] = null;
			$this->appliance->update($this->appliance->id, $fields);
			// reset wizard
			$rs = $this->user->set_wizard($this->user->name, 0, 0, 0);
			// now we have to run the appliance add hook
			$this->appliance->run_add_hook($this->appliance->id);
			$response->msg = sprintf($this->lang['msg'], $assign_kernel_id, $this->appliance->name);
			$event = new event();
			$event_description_step3 = sprintf($this->lang['appliance_create_in_progress_event'], $this->appliance->name, 3, $this->user->name);
			$event_description_step4 = sprintf($this->lang['appliance_created'], $this->appliance->name, $this->user->name);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 10, "add", $event_description_step3, "", "", 0, 0, 0);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 5, "add", $event_description_step4, "", "", 0, 0, 0);
			// redirect
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		
		// provide the kernel select form
		$response = $this->add();

		if (isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if (isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		
		$a = $this->response->html->a();
		$a->title = $this->lang['action_add'];
		$a->label = $this->lang['action_add'];
		$a->handler = 'onclick="wait();"';
		$a->css = 'add';
		$a->href = $this->response->get_url($this->actions_name, 'load_kadd') . '&aplugin=kernel&kernel_action=add&appliance_id=' . $this->appliance->id;
		
		$t = $this->response->html->template($this->tpldir . '/appliance-step4.tpl.php');
		$t->add(sprintf($this->lang['title'], $response->name), 'label');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->lang['info'], 'info');
		$t->add($this->lang['or'], 'or');
		$t->add($a, 'add');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($resourceinfo, 'resource');
		$t->add($imageinfo, 'image');
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
	function add()
	{
		$response = $this->get_response();
		$form = $response->form;
		if (!$form->get_errors() && $this->response->submit()) {
			$kernel_id = $form->get_request('kernel');
			$fields['appliance_kernelid'] = $kernel_id;
			$fields['appliance_wizard'] = null;
			$this->appliance->update($this->appliance->id, $fields);
			// reset wizard
			$rs = $this->user->set_wizard($this->user->name, 0, 0, 0);
			// now we have to run the appliance add hook
			$this->appliance->run_add_hook($this->appliance->id);
			$response->msg = sprintf($this->lang['msg'], $kernel_id, $this->appliance->name);
			$event = new event();
			$event_description_step3 = sprintf($this->lang['appliance_create_in_progress_event'], $this->appliance->name, 3, $this->user->name);
			$event_description_step4 = sprintf($this->lang['appliance_created'], $this->appliance->name, $this->user->name);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 10, "add", $event_description_step3, "", "", 0, 0, 0);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 5, "add", $event_description_step4, "", "", 0, 0, 0);
		}
		$response->name = $this->appliance->name;
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
	function get_response()
	{
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'step4');
		$resource = new resource();
		$resource->get_instance_by_id($this->appliance->resources);
		// if not openQRM resource
		if ($resource->id != 0) {
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($resource->vtype);
		}
		$image = new image();
		$image->get_instance_by_id($this->appliance->imageid);
		$deployment = new deployment();
		$deployment->get_instance_by_type($image->type);
		
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');
		
		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
		
		$kernel = new kernel();
		$list = $kernel->get_list();
		unset($list[0]);
		
		$kernels = array();
		if ($resource->id == 0) {
			$kernels[] = array(0, 'openQRM');
			
		} else if (strstr($resource->capabilities, "TYPE=local-server")) {
			$local_kernel = new kernel();
			$local_kernel->get_instance_by_name("resource" . $resource->id);
			$kernels[] = array($local_kernel->id, 'Local OS Installation');
			
			// local-deployment VMs
		} else if (strstr($virtualization->type, "-vm-local")) {
			
			$kernels[] = array(1, 'Local OS Installation');
			
			// network-deployment - show only network-boot images
		} else if (strstr($virtualization->type, "-vm-net")) {
			
			foreach ($list as $value) {
				$id = $value['value'];
				$kernel->get_instance_by_id($id);
				if (!strstr($kernel->capabilities, "TYPE=local-server")) {
					$kernels[] = array($id, $kernel->id . ' / ' . $kernel->name . ' (' . $kernel->version . ')');
				}
			}
			
			// network deployment - physical systems - show only network-boot images
		} else {
			
			foreach ($list as $value) {
				$id = $value['value'];
				$kernel->get_instance_by_id($id);
				if (!strstr($kernel->capabilities, "TYPE=local-server")) {
					$kernels[] = array($id, $kernel->id . ' / ' . $kernel->name . ' (' . $kernel->version . ')');
				}
			}
			
		}
		
		
		$d['kernel']['label'] = $this->lang['form_kernel'];
		$d['kernel']['required'] = true;
		$d['kernel']['object']['type'] = 'htmlobject_select';
		$d['kernel']['object']['attrib']['index'] = array(0, 1);
		$d['kernel']['object']['attrib']['id'] = 'kernel';
		$d['kernel']['object']['attrib']['name'] = 'kernel';
		$d['kernel']['object']['attrib']['options'] = $kernels;
		
		$form->add($d);
		$response->form = $form;
		return $response;
	}
	
}

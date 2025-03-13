<?php
/**
 * Appliance step3 (image)
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

class appliance_step3
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
		$resourceinfo = '<b>Resource:</b> ' . $resource->id . ' / ' . $resource->ip . ' ' . $resource->hostname . ' - ' . $virtualization->name;
		
		$assign_image_id = 0;
		if ($resource->id == 0) {
			$assign_image_id = 0;
			$direkt_redirect = true;
		} else if (strstr($resource->capabilities, "TYPE=local-server")) {
			// local-server integrated resource
			$assign_image = new image();
			$assign_image->get_instance_by_id($resource->imageid);
			$assign_image_id = $assign_image->id;
			$direkt_redirect = true;
		}
		
		// provide the image select form
		$response = $this->add();
		
		if ($direkt_redirect) {
			$fields['appliance_imageid'] = $assign_image_id;
			$fields['appliance_wizard'] = 'wizard=step4,user=' . $this->user->name;
			$this->appliance->update($this->appliance->id, $fields);
			$response->msg = sprintf($this->lang['msg'], $assign_image_id, $this->appliance->name);
			// wizard
			$rs = $this->user->set_wizard($this->user->name, 'appliance', 3, $this->appliance->id);
			// update long term event, remove old event and add new one
			$event = new event();
			$event_description_step2 = sprintf($this->lang['appliance_create_in_progress_event'], $this->appliance->name, 2, $this->user->name);
			$event_description_step3 = sprintf($this->lang['appliance_create_in_progress_event'], $this->appliance->name, 3, $this->user->name);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 10, "add", $event_description_step2, "", "", 0, 0, 0);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 9, "add", $event_description_step3, "", "", 0, 0, 0);
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'step4', $this->message_param, $response->msg)
			);
		}
		
		if (isset($response->msg)) {
			if (isset($response->image_edit)) {
				$id = $this->apliance_wizard_id;
				$appliance = $this->appliance;
				// image-edit
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'load_iedit') . '&aplugin=image&image_action=edit&image_id=' . $appliance->imageid
				);
			} else {
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'step4', $this->message_param, $response->msg)
				);
			}
		}
		if (isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		
		$a = $this->response->html->a();
		$a->title = $this->lang['action_add'];
		$a->label = $this->lang['action_add'];
		$a->handler = 'onclick="wait();"';
		$a->css = 'add';
		$a->href = $this->response->get_url($this->actions_name, 'load_iadd') . '&aplugin=image&image_action=add&appliance_id=' . $this->appliance->id;
		
		$t = $this->response->html->template($this->tpldir . '/appliance-step3.tpl.php');
		$t->add(sprintf($this->lang['title'], $response->name), 'label');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->lang['or'], 'or');
		$t->add($a, 'add');
		$t->add($this->lang['info'], 'info');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($resourceinfo, 'resource');
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
			$image = $form->get_request('image');
			$fields['appliance_imageid'] = $image;
			$fields['appliance_wizard'] = 'wizard=step4,user=' . $this->user->name;
			$this->appliance->update($this->appliance->id, $fields);
			$response->msg = sprintf($this->lang['msg'], $image, $this->appliance->name);
			// wizard
			$rs = $this->user->set_wizard($this->user->name, 'appliance', 3, $this->appliance->id);
			// update long term event, remove old event and add new one
			$event = new event();
			$event_description_step2 = sprintf($this->lang['appliance_create_in_progress_event'], $this->appliance->name, 2, $this->user->name);
			$event_description_step3 = sprintf($this->lang['appliance_create_in_progress_event'], $this->appliance->name, 3, $this->user->name);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 10, "add", $event_description_step2, "", "", 0, 0, 0);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 9, "add", $event_description_step3, "", "", 0, 0, 0);
			if ($form->get_request('image_edit') === 'on') {
				$response->image_edit = "step4";
			}
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
		$form = $response->get_form($this->actions_name, 'step3');
		$resource = new resource();
		$resource->get_instance_by_id($this->appliance->resources);
		// if not openQRM resource
		if ($resource->id != 0) {
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($resource->vtype);
		}
		
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');
		
		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
		
		// prepare image list according to the resource capabilities + vtype
		$image = new image();
		$list = $image->get_list();
		unset($list[0]);
		unset($list[1]);
		
		$images = array();
		// openQRM
		if ($resource->id == 0) {
			$images[] = array(0, 'Local openQRM Installation');
			
			// local-server integrated resource
		} else if (strstr($resource->capabilities, "TYPE=local-server")) {
			$local_image = new image();
			$local_image->get_instance_by_id($resource->imageid);
			$images[] = array($local_image->id, 'Local OS Installation');
			
			// local-deployment VMs
		} else if (strstr($virtualization->type, "-vm-local")) {
			
			$virtualization_plugin_name = $virtualization->get_plugin_name();
			$deployment = new deployment();
			$deployment_id_arr = $deployment->get_deployment_ids();
			$possible_deployment_types_arr = [];
			foreach ($deployment_id_arr as $deployment_id_db) {
				$deployment_id = $deployment_id_db['deployment_id'];
				$deployment->get_instance_by_id($deployment_id);
				if ($deployment->storagetype === $virtualization_plugin_name) {
					$possible_deployment_types_arr[] = $deployment->type;
				}
			}
			// filter image list with only the images from the VM deployment type
			foreach ($list as $value) {
				$image_id = $value['value'];
				$image->get_instance_by_id($image_id);
				// is image active ? then do not show it here
				if ($image->isactive == 1) {
					continue;
				}
				if (!in_array($image->type, $possible_deployment_types_arr)) {
					continue;
				}
				// filter local-server images
				$images[] = array($image_id, $image->id . ' / ' . $image->name . ' (' . $image->type . ')');
			}
			
			// network-deployment - show only network-boot images
		} else if (strstr($virtualization->type, "-vm-net")) {
			
			foreach ($list as $value) {
				$image_id = $value['value'];
				$image->get_instance_by_id($image_id);
				// is image active ? then do not show it here
				if ($image->isactive == 1) {
					continue;
				}
				// filter local-server images
				if (strstr($image->capabilities, "TYPE=local-server")) {
					continue;
				}
				$is_network_deployment = false;
				if ($image->is_network_deployment() === true) {
					$is_network_deployment = true;
				}
				
				if ($is_network_deployment) {
					$images[] = array($image_id, $image->id . ' / ' . $image->name . ' (' . $image->type . ')');
				}
			}
			
			// network deployment - physical systems - show only network-boot images
		} else {
			
			foreach ($list as $value) {
				$image_id = $value['value'];
				$image->get_instance_by_id($image_id);
				// is image active ? then do not show it here
				if ($image->isactive == 1) {
					continue;
				}
				// filter local-server images
				if (strstr($image->capabilities, "TYPE=local-server")) {
					continue;
				}
				$is_network_deployment = false;
				if ($image->is_network_deployment() === true) {
					$is_network_deployment = true;
				}
				
				if ($is_network_deployment) {
					$images[] = array($image_id, $image->id . ' / ' . $image->name . ' (' . $image->type . ')');
				}
			}
		}
		
		// handle appliance is new or edited
		$selected = $this->response->html->request()->get('image_id');
		if ($selected === '' && isset($this->appliance->imageid)) {
			$selected = $this->appliance->imageid;
		}
		
		$d['image']['label'] = $this->lang['form_image'];
		$d['image']['required'] = true;
		$d['image']['object']['type'] = 'htmlobject_select';
		$d['image']['object']['attrib']['index'] = array(0, 1);
		$d['image']['object']['attrib']['id'] = 'image';
		$d['image']['object']['attrib']['name'] = 'image';
		$d['image']['object']['attrib']['options'] = $images;
		$d['image']['object']['attrib']['selected'] = array($selected);
		
		$d['image_edit'] = [];
		if ($this->appliance->resources != 0) {
			$d['image_edit']['label'] = $this->lang['form_image_edit'];
			$d['image_edit']['object']['type'] = 'htmlobject_input';
			$d['image_edit']['object']['attrib']['type'] = 'checkbox';
			$d['image_edit']['object']['attrib']['id'] = 'image_edit';
			$d['image_edit']['object']['attrib']['name'] = 'image_edit';
			$d['image_edit']['object']['attrib']['checked'] = true;
		}
		
		$form->add($d);
		$response->form = $form;
		return $response;
	}
	
}

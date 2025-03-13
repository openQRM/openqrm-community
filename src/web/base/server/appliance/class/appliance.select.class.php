<?php
/**
 * Appliance Select
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

class appliance_select
{
	/**
	 * name of action buttons
	 * @access public
	 * @var string
	 */
	var $actions_name = 'appliance_action';
	/**
	 * identifier name
	 * @access public
	 * @var string
	 */
	var $identifier_name = 'appliance_identifier';
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
		$data = $this->select();
		
		$t = $this->response->html->template($this->tpldir . '/appliance-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['lang_filter'], 'lang_filter');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}
	
	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function select()
	{
		
		$d = array();
		$h = array();
		$h['appliance_state']['title'] = $this->lang['table_state'];
		$h['appliance_id']['title'] = $this->lang['table_id'];
		$h['appliance_id']['hidden'] = true;
		$h['appliance_name']['title'] = $this->lang['table_name'];
		$h['appliance_name']['hidden'] = true;
		$h['appliance_values']['title'] = '&#160;';
		$h['appliance_values']['sortable'] = false;
		$h['appliance_comment']['title'] = '&#160;';
		$h['appliance_comment']['sortable'] = false;
		$h['appliance_virtualization']['title'] = 'Type';
		$h['appliance_virtualization']['sortable'] = true;
		$h['appliance_virtualization']['hidden'] = true;
		$h['appliance_edit']['sortable'] = false;
		
		$appliance = new appliance();
		$params = $this->response->get_array($this->actions_name, 'select');
		$b = array();
		
		// unset unnecessary params
		unset($params['resource_type_filter']);
		unset($params['resource_filter']);
		unset($params['appliance[sort]']);
		unset($params['appliance[order]']);
		unset($params['appliance[limit]']);
		unset($params['appliance[offset]']);
		
		$table = $this->response->html->tablebuilder('appliance', $params);
		$table->offset = 0;
		$table->sort = 'appliance_id';
		$table->limit = 20;
		$table->order = 'ASC';
		$table->max = $appliance->get_count();
		$table->init();
		
		// handle table params
		#$tps = $table->get_params();
		$tp = '';
		#foreach($tps['appliance'] as $k => $v) {
		#	$tp .= '&appliance['.$k.']='.$v;
		#}
		
		$resource_filter = null;
		if ($this->response->html->request()->get('resource_filter') !== '') {
			$resource = $this->openqrm->resource();
			$resource_filter = array();
			$ar = $resource->find_resource($this->response->html->request()->get('resource_filter'));
			if (count($ar) > 0) {
				foreach ($ar as $k => $v) {
					$resource_filter[] = $v['resource_id'];
				}
			}
		}
		
		$disabled = array();
		$appliances = $appliance->display_overview(0, 10000, $table->sort, $table->order);
		foreach ($appliances as $index => $appliance_db) {
			
			$appliance = new appliance();
			$appliance->get_instance_by_id($appliance_db["appliance_id"]);
			$resource = new resource();
			$resource->get_instance_by_id($appliance->resources);
			$appliance_resources = $appliance_db["appliance_resources"];
			$kernel = new kernel();
			$kernel->get_instance_by_id($appliance_db["appliance_kernelid"]);
			$image = new image();
			$image->get_instance_by_id($appliance_db["appliance_imageid"]);
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($appliance_db["appliance_virtualization"]);
			$appliance_virtualization_name = $virtualization->name;
			$virtualization_plugin_name = $virtualization->get_plugin_name();
			$resource_is_local_server = false;
			$edit_resource_ip = '';
			
			if ($this->response->html->request()->get('resource_type_filter') === '' || ($this->response->html->request()->get('resource_type_filter') == $resource->vtype)) {
				
				// Skip all resources not in $resource_filter
				if (isset($resource_filter)) {
					if (!in_array($resource->id, $resource_filter)) {
						continue;
					}
				}
				
				if ($appliance_resources >= 0) {
					// an appliance with a pre-selected resource
					$resource->get_instance_by_id($appliance_resources);
					$resource_state_icon = '<span class="pill ' . $resource->state . '">' . $resource->state . '</span>';
					// idle ?
					if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
						$resource_state_icon = '<span class="pill idle">idle</span>';
					}
					// link to resource list
					$virtualization_vm_action_name = $virtualization->name;
					if (strstr($resource->capabilities, "TYPE=local-server")) {
						$resource_is_local_server = true;
					}
					$appliance_resources_str = '';
					if (strpos($virtualization->type, "-vm")) {
						$host_resource = new resource();
						if (isset($resource->vhostid) && $resource->vhostid !== '') {
							$host_resource->get_instance_by_id($resource->vhostid);
							$host_virtualization = new virtualization();
							$host_virtualization_name = $virtualization->get_plugin_name();
							$host_virtualization->get_instance_by_type($host_virtualization_name);
							$host_appliance = new appliance();
							$host_appliance->get_instance_by_virtualization_and_resource($host_virtualization->id, $resource->vhostid);
							$link = '?base=appliance&appliance_action=load_select';
							$link .= '&aplugin=' . $virtualization_plugin_name;
							$link .= '&amp;acontroller=' . $virtualization_plugin_name . '-vm';
							$link .= '&amp;' . $virtualization_plugin_name . '_vm_action=update';
							$link .= '&amp;appliance_id=' . $host_appliance->id;
							$link .= '&amp;vm=' . $resource->hostname;
							$appliance_resources_str = '<a href="' . $this->response->html->thisfile . $link . '" onclick="wait();">' . $resource->hostname . '</a> ' . $resource_state_icon;
						}
					} else {
						$appliance_resources_str = $resource->hostname . ' ' . $resource_state_icon;
					}
					// check for local VMs without an IP 
					$pos = strpos($virtualization->type, 'vm-local');
					if ($pos !== false) {
						$a = $this->response->html->a();
						$a->title = $this->lang['action_edit'] . ' IP';
						$a->label = $resource->ip;
						$a->css = 'edit';
						$a->href = '?base=resource&resource_filter=&resource_type_filter=&resource_action=edit&resource_id=' . $resource->id;
						$edit_resource_ip = $a->get_string();
					}
					
				} else {
					// an appliance with resource auto-select enabled
					$appliance_resources_str = "auto-select";
				}
				
				// active or inactive
				$resource_icon_default = "/openqrm/base/img/appliance.png";
				$active_state_icon = '<span class="pill active">active</span>';
				$inactive_state_icon = '<span class="pill inactive">inactive</span>';
				
				if ($appliance->stoptime == 0 || $appliance_resources == 0) {
					$state_icon = $active_state_icon;
				} else {
					$state_icon = $inactive_state_icon;
				}
				// no resource ip yet ?
				if ($resource->ip == '0.0.0.0') {
					$state_icon = '<span class="pill transition">transition</span>';
				}
				
				// link to image edit
				if ($image->id > 0) {
					$link = '?base=image';
					$link .= '&amp;image_action=edit';
					$link .= '&amp;image_id=' . $image->id;
					$image_edit_link = '<a href="' . $this->response->html->thisfile . $link . '" onclick="wait();">' . substr($image->name, 0, 80) . '</a>';
				} else {
					$image_edit_link = $image->name;
				}
				
				// release resource
				$release_resource = '';
				if ($appliance->stoptime == 0 || $appliance_resources == 0) {
					$release_resource = '';
				} else {
					if ($appliance->resources != -1) {
						$a = $this->response->html->a();
						$a->label = $this->lang['action_release'];
						$a->title = $this->lang['resource_release'];
						$a->css = 'enable';
						$a->href = $this->response->get_url($this->actions_name, 'release') . '&appliance_id=' . $appliance->id . '' . $tp;
						$release_resource = $a->get_string();
					}
				}
				
				$str = '<strong>' . $this->lang['table_id'] . ':</strong> ' . $appliance_db["appliance_id"] . '<br>
						<strong>' . $this->lang['table_name'] . ':</strong> ' . $appliance_db["appliance_name"] . '<br>
						<strong>Type:</strong> ' . $appliance_virtualization_name . '<br>
						<strong>Kernel:</strong> ' . $kernel->name . '<br>
						<strong>Image:</strong> ' . $image_edit_link . '<br>
						<strong>Resource:</strong> ' . $appliance_resources_str . '<br>
						<strong>IP:</strong> ' . $resource->ip;
				
				if (strpos($virtualization->type, "-vm") && isset($resource->vhostid) && ($resource->vhostid != '')) {
					$happliance = new appliance();
					$hresource = $happliance->get_ids_per_resource($resource->vhostid);
					if (isset($hresource[0]['appliance_id'])) {
						$happliance->get_instance_by_id($hresource[0]['appliance_id']);
						$link = '?base=appliance';
						$link .= '&amp;appliance_action=edit';
						$link .= '&amp;appliance_id=' . $happliance->id;
						$href = '<a href="' . $this->response->html->thisfile . $link . '" onclick="wait();">' . $happliance->name . '</a>';
						$str .= '<br><strong>Host:</strong> ' . $href;
					}
				}
				
				// appliance edit
				$a = $this->response->html->a();
				$a->title = $this->lang['action_edit'];
				$a->label = $this->lang['action_edit'];
				$a->handler = 'onclick="wait();"';
				$a->css = 'edit';
				$a->href = $this->response->get_url($this->actions_name, 'edit') . '&appliance_id=' . $appliance->id . '' . $tp;
				$strEdit = $a->get_string();
				
				// appliance start
				$strStart = '';
				if ($appliance_resources !== '0') {
					$a = $this->response->html->a();
					$a->handler = 'onclick="wait();"';
					if ($appliance->stoptime == 0) {
						$a->title = $this->lang['action_stop'];
						$a->label = $this->lang['action_stop'];
						$a->css = 'disable';
						$a->href = $this->response->get_url($this->actions_name, 'stop') . '&' . $this->identifier_name . '[]=' . $appliance->id . '' . $tp;
					} else {
						$a->title = $this->lang['action_start'];
						$a->label = $this->lang['action_start'];
						$a->css = 'enable';
						$a->href = $this->response->get_url($this->actions_name, 'start') . '&' . $this->identifier_name . '[]=' . $appliance->id . '' . $tp;
					}
					$strStart = $a->get_string();
				}
				
				// build the plugin link section
				$appliance_link_section = '';
				// add link to continue if appliance has unfinished wizard
				$disabled = array();
				if (isset($appliance->wizard) && strpos($appliance->wizard, 'wizard') !== false) {
					$params = explode(',', $appliance->wizard);
					$wizard_step = explode('=', $params[0]);
					$wizard_user = explode('=', $params[1]);
					if ($wizard_user[1] === $this->user->name) {
						// continue button
						$a = $this->response->html->a();
						$a->title = $this->lang['action_continue'];
						$a->label = $this->lang['action_continue'];
						$a->handler = 'onclick="wait();"';
						$a->css = 'badge continue';
						$a->href = $this->response->get_url($this->actions_name, $wizard_step[1]) . '&appliance_wizard_id=' . $appliance->id . '' . $tp;
						$appliance_comment = $a->get_string();
					} else {
						$appliance_comment = sprintf($this->lang['appliance_create_in_progress'], $wizard_user[1]);
					}
					// disable all buttons
					$disabled[] = $appliance->id;
					$strEdit = '';
					$strStart = '';
					$strStop = '';
					$release_resource = '';
				} else {
					$plugin = new plugin();
					$enabled_plugins = $plugin->enabled();
					foreach ($enabled_plugins as $index => $plugin_name) {
						$plugin_appliance_link_section_hook = $this->openqrm->get('webdir') . "/plugins/" . $plugin_name . "/openqrm-" . $plugin_name . "-appliance-link-hook.php";
						if (file_exists($plugin_appliance_link_section_hook)) {
							require_once "$plugin_appliance_link_section_hook";
							$appliance_get_link_function = str_replace("-", "_", "get_" . "$plugin_name" . "_appliance_link");
							if (function_exists($appliance_get_link_function)) {
								$p = $plugin->get_config($plugin_name);
								$alink = $appliance_get_link_function($appliance->id);
								if (is_object($alink)) {
									//	$alink->handler = $alink->handler.' onclick="wait();"';
									$alink->css = 'enable';
									$alink->title = preg_replace('~(.*?)<a.*>(.*?)</a>(.*?)~i', '$1$2$3', $p['description']);
									$alink = $alink->get_string();
								}
								$appliance_link_section .= $alink;
							}
						}
					}
					$appliance_link_section = $edit_resource_ip . ' ' . $appliance_link_section;
					if ($appliance_db["appliance_comment"] !== '') {
						$appliance_comment = $appliance_db["appliance_comment"];
						$appliance_comment .= "<hr>";
						$appliance_comment .= $appliance_link_section;
					} else {
						$appliance_comment = $appliance_link_section;
					}
				}
				
				$b[] = array(
					'appliance_state' => $state_icon,
					'appliance_id' => $appliance_db["appliance_id"],
					'appliance_name' => $appliance_db["appliance_name"],
					'appliance_values' => $str,
					'appliance_comment' => $appliance_comment,
					'appliance_virtualization' => $appliance_db["appliance_virtualization"],
					'appliance_edit' => $strEdit . '' . $strStart . '' . $release_resource,
				);
			}
			
		}
		
		// Filter
		$virtulization_types = new virtualization();
		$list = $virtulization_types->get_list();
		$filter = array();
		$filter[] = array('', '');
		foreach ($list as $l) {
			$filter[] = array($l['label'], $l['value']);
		}
		asort($filter);
		$select = $this->response->html->select();
		$select->add($filter, array(1, 0));
		$select->name = 'resource_type_filter';
		$select->handler = 'onchange="wait();this.form.submit();return false;"';
		$select->selected = array($this->response->html->request()->get('resource_type_filter'));
		$box1 = $this->response->html->box();
		$box1->add($select);
		$box1->id = 'resource_type_filter';
		$box1->css = 'htmlobject_box';
		$box1->label = $this->lang['lang_type_filter'];
		
		// Resource Filter
		$input = $this->response->html->input();
		$input->name = 'resource_filter';
		$input->value = $this->response->html->request()->get('resource_filter');
		$input->title = $this->lang['lang_filter_title'];
		$box2 = $this->response->html->box();
		$box2->add($input);
		$box2->id = 'resource_filter';
		$box2->css = 'htmlobject_box';
		$box2->label = $this->lang['lang_filter'];
		
		$add = $this->response->html->a();
		$add->title = $this->lang['action_add'];
		$add->label = $this->lang['action_add'];
		$add->handler = 'onclick="wait();"';
		$add->css = 'add';
		$add->href = $this->response->get_url($this->actions_name, "step1") . '' . $tp;
		
		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->autosort = true;
		$table->sort_link = false;
		$table->max = count($b);
		$table->head = $h;
		$table->body = $b;
		#$table->form_action = $this->response->html->thisfile;
		$table->actions_name = $this->actions_name;
		$table->actions = array(
			array('start' => $this->lang['action_start']),
			array('stop' => $this->lang['action_stop']),
			array('remove' => $this->lang['action_remove'])
		);
		$table->identifier = 'appliance_id';
		$table->identifier_name = $this->identifier_name;
		$table->identifier_disabled = $disabled;
		#$table->limit_select = array(
		#	array("value" => 10, "text" => 10),
		#	array("value" => 20, "text" => 20),
		#	array("value" => 30, "text" => 30),
		#	array("value" => 50, "text" => 50),
		#	array("value" => 100, "text" => 100),
		#);
		
		$d['form'] = $this->response->get_form($this->actions_name, 'select', false)->get_elements();
		$d['add'] = $add->get_string();
		$d['table'] = $table;
		$d['resource_type_filter'] = $box1->get_string();
		$d['resource_filter'] = $box2->get_string();
		return $d;
	}
	
}

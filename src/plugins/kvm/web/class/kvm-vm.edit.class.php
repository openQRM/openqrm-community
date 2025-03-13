<?php
/**
 * kvm-vm Edit VM
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_vm_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kvm_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'kvm_vm_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kvm_vm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kvm_vm_identifier';
/**
* identifier name
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
		$this->file                     = $openqrm->file();
		$this->openqrm                  = $openqrm;
		$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
	}

	//--------------------------------------------
	/**
	 * Init
	 *
	 * @access public
	 */
	//--------------------------------------------
	function init() {
		$id = $this->response->html->request()->get('appliance_id');
		if($id === '') {
			return false;
		}
		$appliance  = new appliance();
		$resource   = new resource();
		$appliance->get_instance_by_id($id);
		$resource->get_instance_by_id($appliance->resources);
		$this->resource  = $resource;
		$this->appliance = $appliance;
		$this->statfile  = $this->openqrm->get('basedir').'/plugins/kvm/web/kvm-stat/'.$resource->id.'.vm_list';
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
		$this->init();
		$data = $this->edit();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/kvm-vm-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->lang['lang_password_show'], 'lang_password_show');
			$t->add($this->lang['lang_password_hide'], 'lang_password_hide');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			$t->add($this->openqrm->get('baseurl'), 'baseurl');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_host'], $this->response->html->request()->get('appliance_id'));
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
			);
		}
	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function edit() {
		$resource_icon_default = "/img/resource.png";
		$storage_icon = "/plugins/kvm/img/plugin.png";
		//$state_icon = $this->openqrm->get('baseurl')."/img/".$this->resource->state.".png";
		if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
			$resource_icon_default = $storage_icon;
		}
		$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;
		// check if we have a plugin implementing the remote console
		$remote_console = false;
		$plugin = new plugin();
		$enabled_plugins = $plugin->enabled();
		foreach ($enabled_plugins as $index => $plugin_name) {
			//$plugin_remote_console_running = $this->openqrm->get('webdir')."/plugins/".$plugin_name."/.running";
			$plugin_remote_console_hook = $this->openqrm->get('webdir')."/plugins/".$plugin_name."/openqrm-".$plugin_name."-remote-console-hook.php";
			if ($this->file->exists($plugin_remote_console_hook)) {
				require_once "$plugin_remote_console_hook";
				$link_function = str_replace("-", "_", "openqrm_"."$plugin_name"."_remote_console");
				if(function_exists($link_function)) {
					$remote_functions[] = $link_function;
					$remote_console = true;
				}
			}
		}
		// prepare list of all Host resource id for the migration select
		// we need a select with the ids/ips from all resources which
		// are used by appliances with kvm capabilities
		$kvm_hosts = array();
		$appliance_list = new appliance();
		$appliance_list_array = $appliance_list->get_list();
		foreach ($appliance_list_array as $index => $app) {
			$appliance_kvm_host_check = new appliance();
			$appliance_kvm_host_check->get_instance_by_id($app["value"]);
			// only active appliances
			if ((!strcmp($appliance_kvm_host_check->state, "active")) || ($appliance_kvm_host_check->resources == 0)) {
				$virtualization = new virtualization();
				$virtualization->get_instance_by_id($appliance_kvm_host_check->virtualization);
				if ((!strcmp($virtualization->type, "kvm")) && (!strstr($virtualization->type, "kvm-vm"))) {
					$kvm_host_resource = new resource();
					$kvm_host_resource->get_instance_by_id($appliance_kvm_host_check->resources);
					// exclude source host
					#if ($kvm_host_resource->id == $this->resource->id) {
					#	continue;
					#}
					// only active appliances
					if (!strcmp($kvm_host_resource->state, "active")) {
						$migration_select_label = "Res. ".$kvm_host_resource->id."/".$kvm_host_resource->ip;
						$kvm_hosts[] = array("value"=>$kvm_host_resource->id, "label"=> $migration_select_label,);
					}
				}
			}
		}

		$d['state'] = '<span class="pill '.$this->resource->state.'">'.$this->resource->state.'</span>';
		$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
		$d['name'] = $this->appliance->name;
		$d['id'] = $this->appliance->id;

		$a = $this->response->html->a();
		$a->label   = $this->lang['action_add_local_vm'];
		$a->css     = 'add';
		$a->handler = 'onclick="wait();"';
		$a->href    = $this->response->get_url($this->actions_name, "add").'&vmtype=kvm-vm-local';
		$d['add_local_vm']   = $a->get_string();

		// only show network deployment VMs if dhcpd is enabled
		$plugin = $this->openqrm->plugin();
		$enabled_plugins = $plugin->enabled();
		if (in_array("dhcpd", $enabled_plugins)) {
			$a = $this->response->html->a();
			$a->label   = $this->lang['action_add_network_vm'];
			$a->css     = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href    = $this->response->get_url($this->actions_name, "add").'&vmtype=kvm-vm-net';
			$d['add_network_vm']   = $a->get_string();
		} else {
			$d['add_network_vm']   = '';
		}

		$body = array();
		$identifier_disabled = array();
		$file = $this->statfile;
		if($this->file->exists($file)) {
			$lines = explode("\n", $this->file->get_contents($file));
			if(count($lines) >= 1) {
				$i = 0;
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						
						$state = $line[0];
						$name = $line[1];
						$mac = $line[2];

						$resource = new resource();
						$resource->get_instance_by_mac($mac);
						if ($resource->vhostid != $this->resource->id) {
							continue;
						}
						$res_virtualization = new virtualization();
						$res_virtualization->get_instance_by_id($resource->vtype);

						$update = '';
						$a = $this->response->html->a();
						$a->title   = $this->lang['action_update'];
						$a->label   = $this->lang['action_update'];
						$a->handler = 'onclick="wait();"';
						$a->css     = 'edit';
						$a->href    = $this->response->get_url($this->actions_name, "update").'&vm='.$name.'&vmtype='.$res_virtualization->type;
						$update_link = $a->get_string();

						$clone = '';
						$a = $this->response->html->a();
						$a->title   = $this->lang['action_clone'];
						$a->label   = $this->lang['action_clone'];
						$a->handler = 'onclick="wait();"';
						$a->css     = 'clone';
						$a->href    = $this->response->get_url($this->actions_name, "clone").'&vm='.$name.'&mac='.$mac;
						$clone_link	= $a->get_string();
						
						if ($res_virtualization->type == 'kvm-vm-local') {
							if(($state === '0') && ($resource->image === 'idle')) {
								$update = $update_link;
								$clone = $clone_link;
							}
						}
						if ($res_virtualization->type == 'kvm-vm-net') {
							if(($state !== '2') && ($resource->image === 'idle')) {
								$update = $update_link;
								$clone = $clone_link;
							}
						}
						
						$migrate = '';
						$a = $this->response->html->a();
						$a->title   = $this->lang['action_migrate'];
						$a->label   = $this->lang['action_migrate'];
						$a->handler = 'onclick="wait();"';
						$a->css     = 'migrate';
						$a->href    = $this->response->get_url($this->actions_name, "migrate").'&vm='.$name.'&mac='.$mac;
						if(count($kvm_hosts) >= 1 && $state === '1') {
							$migrate    = $a->get_string();
						}

						$vnc_password_input = "<input type='password' id='vm_vncpasswd_".$resource->id."' name='vm_vncpasswd' class='vm_vncpasswd' size='10' maxlength='20' value='".$line[6]."'>";
						$vnc_password_input .= "&nbsp<input type='button' id='passtoggle_".$resource->id."' name='vm_vnctoggle' class='vm_vnctoggle' onclick='passgen.toggle(".$resource->id."); return false;' class='password-button' value='".$this->lang['lang_password_show']."'>";
						
						
						$data  = '<b>'.$this->lang['table_id'].'</b>: '.$resource->id.'<br>';
						$data .= '<b>'.$this->lang['table_name'].'</b>: '.$name.'<br>';
						$data .= '<b>'.$this->lang['table_type'].'</b>: '.$res_virtualization->name.'<br>';
						$data .= '<b>'.$this->lang['table_ip'].'</b>: '.$resource->ip.'<br>';
						$data .= '<b>'.$this->lang['table_mac'].'</b>: '.$mac.'<br>';
						$data .= '<b>'.$this->lang['table_cpu'].'</b>: '.$line[3].'<br>';
						$data .= '<b>'.$this->lang['table_ram'].'</b>: '.$line[4].'<br>';
						$data .= '<b>'.$this->lang['table_nics'].'</b>: '.$resource->nics;

						$appliance = new appliance();
						$appliance->get_instance_by_virtualization_and_resource($resource->vtype, $resource->id);
						$server = array();
						$server[0] = '';
						$server[1] = '1000000000000000000000000';
						if($appliance->id !== '') {
							$kernel = new kernel();
							$kernel->get_instance_by_id($appliance->kernelid);
							$image = new image();
							$image->get_instance_by_id($appliance->imageid);
							$storage = new storage();
							$storage->get_instance_by_id($image->storageid);

							$s  = '<b>'.$this->lang['table_appliance'].'</b>: '.$appliance->id.'<br>';
							$s .= '<b>'.$this->lang['table_name'].'</b>: '.$appliance->name.'<br>';
							$s .= '<b>'.$this->lang['table_kernel'].'</b>: '.$kernel->name.'<br>';
							$s .= '<b>'.$this->lang['table_image'].'</b>: '.$image->name.'<br>';
							$s .= '<b>'.$this->lang['table_storage'].'</b>: '.$storage->name.'<br><br>';

							$s .= '<b>'.$this->lang['table_vnc'].'</b>: '.$line[5].'<br>';
							$s .= '<b>'.$this->lang['table_vncpassword'].'</b>: '.$vnc_password_input.'<br>';
							
							
							$server[0] = $s;
							$server[1] = $appliance->id;
						}

						$console = '';
						if($state === '2') {
							$t = $this->response->html->template($this->openqrm->get('webdir').'/js/openqrm-progressbar.js');
							$identifier_disabled[] = $name;
							// progressbar
							$t->add(uniqid('b', true), 'id');
							$t->add($this->openqrm->get('baseurl').'/api.php?action=plugin&plugin=kvm&controller=kvm-vm&kvm_vm_action=progress&name='.$name.'.vm_migration_progress', 'url');
							$t->add($this->lang['action_migrate_in_progress'], 'lang_in_progress');
							$t->add($this->lang['action_migrate_finished'], 'lang_finished');
							$console = $t->get_string();
						} else {
							if($remote_console === true && $resource->imageid !== 1 && $state === '1') {
								foreach($remote_functions as $function) {
									$a = $function($resource->id);
									if(is_object($a)) {
										$console .= $a->get_string();
									}
								}
							}
						}

						$state = array();
						$state[0] = '<span class="pill idle">idle</span>';
						$state[1] = 'i';
						if(($line[0] === '1') && ($resource->image !== 'idle')) {
							$state[0] = '<span class="pill active">active</span>';
							$state[1] = 'a';
						}
						if (($res_virtualization->type == 'kvm-vm-net') && ($line[0] === '0')) {
							$state[0] = '<span class="pill off">off</span>';
							$state[1] = 'o';
						}
						
						
						$action = '';
						if(!in_array($name, $identifier_disabled)) {
							$action = $update.$clone.$migrate;
						}

						$body[$i] = array(
							'state' => $state[0],
							'state_s' => $state[1],
							'name' => $name,
							'id' => $resource->id,
							'mac' => $mac,
							'cpu' => $line[3],
							'ram' => $line[4],
							'ip' => $resource->ip,
							'vnc' => $line[5],
							'data' => $data,
							'appliance' => $server[0],
							'appliance_s' => $server[1],
							'plugins' => $console,
							'action' => $action,
						);
					}
					$i++;
				}
			}
		}

		$h['state']['title'] = $this->lang['table_state'];
		$h['state']['sortable'] = false;
		$h['state_s']['title'] = $this->lang['table_state'];
		$h['state_s']['sortable'] = true;
		$h['state_s']['hidden'] = true;
		$h['id']['title'] = $this->lang['table_id'];
		$h['id']['hidden'] = true;
		$h['name']['title'] = $this->lang['table_name'];
		$h['name']['hidden'] = true;
		$h['ip']['title'] = $this->lang['table_ip'];
		$h['ip']['hidden'] = true;
		$h['mac']['title'] = $this->lang['table_mac'];
		$h['mac']['hidden'] = true;
		$h['vnc']['title'] = $this->lang['table_vnc'];
		$h['vnc']['hidden'] = true;
		$h['cpu']['title'] = $this->lang['table_cpu'];
		$h['cpu']['hidden'] = true;
		$h['ram']['title'] = $this->lang['table_ram'];
		$h['ram']['hidden'] = true;
		$h['nics']['title'] = $this->lang['table_nics'];
		$h['nics']['hidden'] = true;
		$h['data']['title'] =  '&#160;';
		$h['data']['sortable'] = false;
		$h['appliance']['title'] =  '&#160;';
		$h['appliance']['sortable'] = false;
		$h['appliance_s']['title'] =  $this->lang['table_appliance'];
		$h['appliance_s']['sortable'] = true;
		$h['appliance_s']['hidden'] = true;
		$h['plugins']['title'] = '&#160;';
		$h['plugins']['sortable'] = false;
		$h['action']['title'] = '&#160;';
		$h['action']['sortable'] = false;
	
		$table = $this->response->html->tablebuilder('kvm_vm_edit', $this->response->get_array($this->actions_name, 'edit'));
		$table->sort            = 'name';
		$table->limit           = 10;
		$table->offset          = 0;
		$table->order           = 'ASC';
		$table->max             = count($body);
		$table->autosort        = true;
		$table->sort_link       = false;
		$table->id              = 'Tabelle';
		$table->css             = 'htmlobject_table';
		$table->border          = 1;
		$table->cellspacing     = 0;
		$table->cellpadding     = 3;
		$table->form_action     = $this->response->html->thisfile;
		$table->head            = $h;
		$table->body            = $body;
		$table->identifier      = 'name';
		$table->identifier_name = $this->identifier_name;
		$table->identifier_disabled = $identifier_disabled;
		$table->actions_name    = $this->actions_name;
		$table->actions         = array(
				array('start' => $this->lang['action_start']),
				array('stop' => $this->lang['action_stop']),
				array('reboot' => $this->lang['action_reboot']),
				array('remove' => $this->lang['action_remove'])
			);

		$d['table'] = $table->get_string();
		return $d;
	}

}

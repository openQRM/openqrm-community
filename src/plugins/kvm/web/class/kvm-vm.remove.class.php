<?php
/**
 * kvm-vm Remove VM(s)
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_vm_remove
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
		$this->file                     = $openqrm->file();
		$this->openqrm                  = $openqrm;
		$this->user			= $openqrm->user();

		require_once $this->openqrm->get('basedir').'/plugins/ip-mgmt/web/class/ip-mgmt.class.php';
		$ip_mgmt = new ip_mgmt();
		$this->ip_mgmt = $ip_mgmt;
		
		$this->plugin = new plugin();
		$this->plugins_enabled = $this->plugin->enabled();
		
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
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/kvm-vm-remove.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($response->form);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
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
		$event   = new event();
		$response = $this->get_response();
		$vms  = $response->html->request()->get($this->identifier_name);
		$form     = $response->form;
		$openqrm_resource = new resource();
		$openqrm_resource->get_instance_by_id(0);
		
		if( $vms !== '' ) {

			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			foreach($vms as $ex) {
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
				$appliance_id = $this->response->html->request()->get('appliance_id');
				$appliance    = new appliance();
				$resource     = new resource();
				$errors       = array();
				$message      = array();
				foreach($vms as $key => $vm) {
					$appliance->get_instance_by_id($appliance_id);
					$resource->get_instance_by_id($appliance->resources);
					$file = $this->openqrm->get('basedir').'/plugins/kvm/web/kvm-stat/'.$resource->id.'.vm_list';
					if($this->file->exists($file)) {					
						$lines = explode("\n", $this->file->get_contents($file));
						if(count($lines) >= 1) {
							foreach($lines as $line) {
								if($line !== '') {
									$line = explode('@', $line);
									if($vm === $line[1]) {
										$kvm = new resource();
										$kvm->get_instance_by_mac($line[2]);
										
										// check if it is still in use
										$appliances_using_resource = $appliance->get_ids_per_resource($kvm->id);
										if (count($appliances_using_resource) > 0) {
											$appliances_using_resource_str = implode(",", $appliances_using_resource[0]);
											$errors[] = sprintf($this->lang['msg_vm_resource_still_in_use'], $vm, $kvm->id, $appliances_using_resource_str);
										} else {
										    
											// remove all nics by mac from dhcpd
											if($line[7] !== '') {
												$openqrm_resource = new resource();
												$openqrm_resource->get_instance_by_id(0);
												$all_mac_array = explode(',', $line[7]);
												foreach ($all_mac_array as $addional_mac) {
													if ($addional_mac == '') {
													    continue;
													}
													$dhcpd_remove_command = $this->openqrm->get('basedir').'/plugins/dhcpd/bin/openqrm-dhcpd-manager remove_by_mac -m '.$addional_mac;
													$dhcpd_remove_command .= ' --openqrm-cmd-mode background';
													$event->log("console", $_SERVER['REQUEST_TIME'], 5, "kvm-storge-vm.remove.class.php", 'Removing '.$addional_mac.' from dhcpd server.', "", "", 0, 0, $kvm->id);
													$openqrm_resource->send_command($openqrm_resource->ip, $dhcpd_remove_command);
												}
											}
											
											
											// remove all ips from dns
											$kvm_ip_id_array = $this->ip_mgmt->get_ids_per_resource($kvm->id);
											foreach($kvm_ip_id_array as $kvm_ip_id) {

												$ovs_ip_array = $this->ip_mgmt->get_instance('id', $kvm_ip_id);
												$ovs_ip_mgmt_address = trim($ovs_ip_array['ip_mgmt_address']);
												$ovs_ip_mgmt_domain = trim($ovs_ip_array['ip_mgmt_domain']);

												$dns_command  = $this->openqrm->get('basedir').'/plugins/dns/bin/openqrm-dns-domain-manager remove_host';
												$dns_command .= ' -n '.$ovs_ip_mgmt_domain;
												$dns_command .= ' -i '.$ovs_ip_mgmt_address;
												$dns_command .= ' -q '.$kvm->vname;
												$openqrm_resource->send_command($openqrm_resource->ip, $dns_command);

												$ovs_update_array['ip_mgmt_resource_id'] = NULL;
												$this->ip_mgmt->update_ip($kvm_ip_id, $ovs_update_array);

											}

									    
											$kvm->remove($kvm->id, $line[2]);
											$command  = $this->openqrm->get('basedir').'/plugins/kvm/bin/openqrm-kvm-vm delete -n '.$vm;
											$command    .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
											$command .= ' --openqrm-ui-user '.$this->user->name;
											$command .= ' --openqrm-cmd-mode background';
											$resource->send_command($resource->ip, $command);
											$form->remove($this->identifier_name.'['.$key.']');
											$message[] = sprintf($this->lang['msg_removed'], $vm);

											// stop remote console
											$tmp    = explode(':',$line[5]);
											$server = $tmp[0];
											$port   = $tmp[1];
											$mac    = $line[2];
											$rid    = $kvm->id;
											$plugin  = new plugin();
											$enabled = $plugin->enabled();
											foreach ($enabled as $index => $name) {
												$running = $this->openqrm->get('webdir').'/plugins/'.$name.'/.running';
												$hook = $this->openqrm->get('webdir').'/plugins/'.$name.'/openqrm-'.$name.'-remote-console-hook.php';
												if (file_exists($hook)) {
													if (file_exists($running)) {
														$event->log("console", $_SERVER['REQUEST_TIME'], 5, "kvm-storge-vm.remove.class.php", 'Stopping '.$name.' remote console.', "", "", 0, 0, $kvm->id);
														require_once($hook);
														$console_function = 'openqrm_'.$name.'_disable_remote_console';
														$console_function = str_replace("-", "_", $console_function);
														$console_function($server, $port, $rid, $mac, $vm);
													}
												}
											}
										}
									}
								}
							}
						}
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
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'remove');
		$response->form = $form;
		return $response;
	}

}

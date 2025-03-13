<?php
/**
 * KVM-vm migrate VM
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_vm_migrate
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
		$this->user						= $openqrm->user();
		$id = $this->response->html->request()->get('appliance_id');
		if($id === '') {
			return false;
		}
		$vm = $this->response->html->request()->get('vm');
		if($vm === '') {
			return false;
		}
		$this->vm = $vm;
		$this->response->params['vm'] = $this->vm;
		$mac = $this->response->html->request()->get('mac');
		if($mac === '') {
			return false;
		}
		$this->mac = $mac;
		$this->response->params['mac'] = $this->mac;
		$appliance = new appliance();
		$resource  = new resource();
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
		$response = $this->migrate();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/kvm-vm-migrate.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->vm), 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * clone
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function migrate() {
		$response = $this->get_response();
		if(isset($response->msg)) {
			return $response;
		}
		$form = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$target      = $form->get_request('target');
			$vm_resource = new resource();
			$vm_resource->get_instance_by_mac($this->mac);
			$dest_host_resource = new resource();
			$dest_host_resource->get_instance_by_id($target);
			$source_host_resource = new resource();
			$source_host_resource->get_instance_by_id($vm_resource->vhostid);

			// first transfer the VM config from source to destination
			$tstatfile=$this->openqrm->get('basedir').'/plugins/kvm/web/kvm-stat/'.$this->vm.'.transfer_status';
			if ($this->file->exists($tstatfile)) {
				$this->file->remove($tstatfile);
			}
			$t_command     = $this->openqrm->get('basedir').'/plugins/kvm/bin/openqrm-kvm-vm transfer_vm_config';
			$t_command    .= ' -n '.$this->vm;
			$t_command    .= ' -k '.$dest_host_resource->ip;
			$t_command    .= ' -k1 '.$source_host_resource->ip;
			$t_command    .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
			$t_command .= ' --openqrm-ui-user '.$this->user->name;
			$t_command .= ' --openqrm-cmd-mode background';

			$openqrm = new openqrm_server();
			$openqrm->send_command($t_command, NULL, true);

			while (!$this->file->exists($tstatfile)) {
			  usleep(10000); // sleep 10ms to unload the CPU
			  clearstatcache();
			}
			$msg = trim($this->file->get_contents($tstatfile));
			if($msg !== "ok") {
				$response->error = $msg;
				return $response;
			}

			// calcuate the migration port
			list($o1, $o2, $o3, $o4) = explode(".", $vm_resource->ip, 4);
			$kvm_vm_migration_port = $o4 + 6000;


			// start as incoming on destination
			$s_command     = $this->openqrm->get('basedir').'/plugins/kvm/bin/openqrm-kvm-vm start_as_incoming';
			$s_command    .= ' -n '.$this->vm;
			$s_command    .= ' -j '.$kvm_vm_migration_port;
			$s_command    .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
			$s_command .= ' --openqrm-ui-user '.$this->user->name;
			$s_command .= ' --openqrm-cmd-mode background';

			$statfile=$this->openqrm->get('basedir').'/plugins/kvm/web/kvm-stat/'.$this->vm.'.vm_migrated_successfully';
			if ($this->file->exists($statfile)) {
				$this->file->remove($statfile);
			}

			$dest_host_resource->send_command($dest_host_resource->ip, $s_command);
			sleep(5);

			$m_command     = $this->openqrm->get('basedir').'/plugins/kvm/bin/openqrm-kvm-vm migrate';
			$m_command    .= ' -n '.$this->vm;
			$m_command    .= ' -k '.$dest_host_resource->ip;
			$m_command    .= ' -j '.$kvm_vm_migration_port;
			$m_command    .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
			$m_command .= ' --openqrm-ui-user '.$this->user->name;
			$m_command .= ' --openqrm-cmd-mode background';
			$this->resource->send_command($this->resource->ip, $m_command);

			$fields=array();
			$fields["resource_vhostid"] = $dest_host_resource->id;
			$vm_resource->update_info($vm_resource->id, $fields);

			$response->msg = sprintf($this->lang['msg_migrated'], $this->vm, $dest_host_resource->id.' / '.$dest_host_resource->ip);
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
		$form = $response->get_form($this->actions_name, 'migrate');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$targets = array();
		$list = array();
		if(isset($this->appliance)) {
			$list = $this->appliance->get_list();
		}
		foreach ($list as $key => $app) {
			$appliance = new appliance();
			$appliance->get_instance_by_id($app["value"]);
			// only active appliances
			if ((!strcmp($appliance->state, "active")) || ($appliance->resources == 0)) {
				$virtualization = new virtualization();
				$virtualization->get_instance_by_id($appliance->virtualization);
				if ((!strcmp($virtualization->type, "kvm")) && (!strstr($virtualization->type, "kvm-vm"))) {
					$resource = new resource();
					$resource->get_instance_by_id($appliance->resources);
					// exclude source host
					if ($resource->id === $this->resource->id) {
						continue;
					}
					// only active appliances
					if (!strcmp($resource->state, "active")) {
						$label = $resource->id." / ".$resource->ip;
						$targets[] = array($resource->id, $label);
					}
				}
			}
		}

		if(count($targets) >= 1 ) {	
			$d['target']['label']                       = $this->lang['form_target'];
			$d['target']['required']                    = true;
			$d['target']['object']['type']              = 'htmlobject_select';
			$d['target']['object']['attrib']['name']    = 'target';
			$d['target']['object']['attrib']['index']   = array(0,1);
			$d['target']['object']['attrib']['options'] = $targets;
			$form->add($d);
			$response->form = $form;
		} else {
			$response->msg = $this->lang['error_no_hosts'];
		}
		return $response;
	}

}





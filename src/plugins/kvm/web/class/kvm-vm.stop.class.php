<?php
/**
 * kvm-vm Stop VM(s)
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_vm_stop
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
		$response = $this->stop();
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response)
		);
	}

	//--------------------------------------------
	/**
	 * Stop
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function stop() {
		$response = '';
		$vms = $this->response->html->request()->get($this->identifier_name);
		if( $vms !== '' ) {
			$appliance_id = $this->response->html->request()->get('appliance_id');
			$appliance    = new appliance();
			$resource     = new resource();
			$errors       = array();
			$message      = array();
			foreach($vms as $key => $vm) {
				$appliance->get_instance_by_id($appliance_id);
				$resource->get_instance_by_id($appliance->resources);
				$file = $this->openqrm->get('basedir').'/plugins/kvm/web/kvm-stat/'.$resource->id.'.vm_list';
				$command  = $this->openqrm->get('basedir').'/plugins/kvm/bin/openqrm-kvm-vm stop -n '.$vm;
				$command    .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
				$command .= ' --openqrm-ui-user '.$this->user->name;
				$command .= ' --openqrm-cmd-mode background';
				$resource->send_command($resource->ip, $command);
				$message[] = sprintf($this->lang['msg_stoped'], $vm);

				$file  = $this->openqrm->get('basedir').'/plugins/kvm/web/kvm-stat/'.$resource->id.'.vm_list';
				if($this->file->exists($file)) {
					$lines   = explode("\n", $this->file->get_contents($file));
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							if($line[1] === $vm) {
								$tmp    = explode(':',$line[5]);
								$server = $tmp[0];
								$port   = $tmp[1];
								$mac    = $line[2];
								$resource->get_instance_by_mac($mac);
								$rid    = $resource->id;
							}
						}
						$event   = new event();
						$plugin  = new plugin();
						$enabled = $plugin->enabled();
						foreach ($enabled as $index => $name) {
							$running = $this->openqrm->get('webdir').'/plugins/'.$name.'/.running';
							$hook = $this->openqrm->get('webdir').'/plugins/'.$name.'/openqrm-'.$name.'-remote-console-hook.php';
							if (file_exists($hook)) {
								if (file_exists($running)) {
									$event->log("console", $_SERVER['REQUEST_TIME'], 5, "kvm-vm.console.class.php", 'Found plugin '.$name.' providing a remote console.', "", "", 0, 0, $resource->id);
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
			if(count($errors) === 0) {
				$response = join('<br>', $message);
			} else {
				$msg = array_merge($errors, $message);
				$response = join('<br>', $msg);
			}
		} else {
			$response = '';
		}
		return $response;
	}

}

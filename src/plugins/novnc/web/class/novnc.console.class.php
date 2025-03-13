<?php
/**
 * novnc Console
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */



class novnc_console
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'novnc_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'novnc_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'novnc_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'novnc_identifier';
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
/**
* url for images
* @access public
* @var string
*/
var $imgurl = '/openqrm/base/plugins/novnc/img/';
/**
* url for js
* @access public
* @var string
*/
var $jsurl = '/openqrm/base/plugins/novnc/novncjs/';

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
		$this->user                     = $openqrm->user();
		if($this->response->html->request()->get('appliance_id') !== '') {
			$this->appliance_id = $this->response->html->request()->get('appliance_id');
		}
		else if($this->response->html->request()->get('resource_id') !== '') {
			$this->resource_id = $this->response->html->request()->get('resource_id');
		}
		if($this->response->html->request()->get('vncport') !== '') {
			$this->vncport = $this->response->html->request()->get('vncport');
		}
		if($this->response->html->request()->get('vnchost') !== '') {
			$this->vnchost = $this->response->html->request()->get('vnchost');
		}
		$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
		$this->response->add('resource_id', $this->response->html->request()->get('resource_id'));
		$this->response->add('vncport', $this->response->html->request()->get('vncport'));
		$this->response->add('vnchost', $this->response->html->request()->get('vnchost'));

		$this->event = $this->openqrm->event();
		$this->openqrm_server = $this->openqrm->server();
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
		$response = $this->console();
		return $response;
	}

	//--------------------------------------------
	/**
	 * Console
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function console() {
		$response = '';
		if(isset($this->appliance_id)) {
			$appliance = $this->openqrm->appliance();
			$appliance->get_instance_by_id($this->appliance_id);
			$resource = $this->openqrm->resource();
			$resource->get_instance_by_id($appliance->resources);
		} 
		else if(isset($this->resource_id)) {
			$resource = $this->openqrm->resource();
			$resource->get_instance_by_id($this->resource_id);
		}

		// handle missing resource
		if($resource->id !== '') {
			$virtualization = $this->openqrm->virtualization();
			$virtualization->get_instance_by_id($resource->vtype);
			$vtype = $virtualization->get_plugin_name();
			if ($resource->vname == '') {
				$vm_name = $resource->hostname;
			} else {
				$vm_name = $resource->vname;
			}
			// vnc port set via request?
			if(isset($this->vncport)) {
				$vncport = $this->vncport;
			} else {
				$vncport = $this->__vmlist($resource->id, $vm_name, $vtype);
			}
			// host as parameter ?
			if(isset($this->vnchost)) {
				$vncserverip = $this->vnchost;
			} else {
				$vncserver = $this->openqrm->resource();
				$vncserver->get_instance_by_id($resource->vhostid);
				$vncserverip = $vncserver->ip;
			}
			// check if vncport is an array, if yes it contains server:port infos
			if (is_array($vncport)) {
				$vncserverip = $vncport['vncserverip'];
				$vncport = $vncport['vncport'];
			}
			// check if vncport already contains server:port infos
			$colon = strpos($vncport, ':');
			if ($colon !== false) {
				$vnc_info_arr = explode(":", $vncport);
				$vncserverip = $vnc_info_arr[0];
				$vncport = $vnc_info_arr[1];
			}
			if(($vncport !== '') && ($vncserverip != '')) {
				// get array position of $resource->id as webproxy port
				$list = $resource->get_list();
				$proxyport = ''; 
				foreach($list as $key => $value) {
					if(isset($value['resource_id']) && $value['resource_id'] == $resource->id) {
						// set the port, avoid zero
						$proxyport = $key+1; 
						break;
					}
				}
				$vnc = $this->__proxy($vncserverip, $vncport, $proxyport, $resource->mac, $vm_name);
				$tpl = $this->openqrm->get('basedir').'/plugins/novnc/web/tpl/novnc-console.tpl.php';
				$t = $this->response->html->template($tpl);
				if(isset($this->appliance_id)) {
					$t->add('api.php?action=plugin&plugin=novnc&controller=novnc&novnc_action=console&appliance_id='.$this->appliance_id , 'url');
				}
				else if(isset($this->resource_id)) {
					$t->add('api.php?action=plugin&plugin=novnc&controller=novnc&novnc_action=console&resource_id='.$this->resource_id , 'url');
				}
				$t->add($this->imgurl,'imgurl');
				$t->add($this->jsurl,'jsurl');
				$t->add($this->lang['lang_detach'],'lang_detach');
				$t->add($vnc['host'],'host');
				$t->add($vnc['port'],'port');
				$resourceinfo = $resource->hostname.' / '.$resource->ip;
				if(isset($this->vncport)) {
					$vp = $this->vncport + 5900;
					$resourceinfo .= ':'.$vp;
				}
				$t->add('Resource: '.$resourceinfo,'resource');
				$t->add($this->lang);
			} else {
				$t = $this->response->html->div();
				$t->style = 'margin: 25px 0 0 15px;';
				$t->add(sprintf($this->lang['error_no_port'], $resource->hostname));
			}
		} else {
			$t = $this->response->html->box();
			$t->style = 'margin: 25px 0 0 15px;';
			$t->label = sprintf($this->lang['error_no_port'], '');
			$t->add('');
		}
		return $t;
	}

	//--------------------------------------------
	/**
	 * Get VM list 
	 *
	 * @access private
	 * @param string $id host id
	 * @param string $vm vm name
	 * @param string $vtype
	 * @return string
	 */
	//--------------------------------------------
	function __vmlist($id, $vm, $vtype) {
		$port     = '';
		$basedir  = $this->openqrm->get('basedir');
		$vm_resource = $this->openqrm->resource();
		$vm_resource->get_instance_by_id($id);
		$resource = $this->openqrm->resource();
		$resource->get_instance_by_id($vm_resource->vhostid);

		switch ($vtype) {

			case 'kvm':
				if ($vm_resource->vnc != '') {
					return $vm_resource->vnc;
				}
				$file = $basedir.'/plugins/'.$vtype.'/web/'.$vtype.'-stat/'.$resource->id.'.vm_list';
				if($this->file->exists($file)) {
					$this->file->remove($file);
				}
				$command  = $basedir.'/plugins/'.$vtype.'/bin/openqrm-'.$vtype.'-vm post_vm_list';
				$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
				$command .= ' --openqrm-ui-user '.$this->user->name;
				$command .= ' --openqrm-cmd-mode regular';
				$resource->send_command($resource->ip, $command);
				while (!$this->file->exists($file)) // check if the data file has been modified
				{
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
				if($this->file->exists($file)) {
					$lines   = explode("\n", $this->file->get_contents($file));
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							if($line[1] === $vm) {
								$tmp  = explode(':',$line[5]);
								$port = $tmp[1];
								// update vnc in resource
								if ($vm_resource->vnc == '') {
									$resource_fields["resource_vnc"] = $port;
									$resource_fields["resource_vname"] = $vm;
									$vm_resource->update_info($id, $resource_fields);
								}
								break;
							}
						}
					}
				}
				return $port;
				break;

			case 'vmware-esx':

				// make sure vnc is enabled in the Hosts firewall
				$command  = $this->openqrm->get('basedir')."/plugins/vmware-esx/bin/openqrm-vmware-esx-vm enable_vnc -i ".$resource->ip;
				$command .= ' --openqrm-ui-user '.$this->user->name;
				$command .= ' --openqrm-cmd-mode background';
				$this->openqrm_server->send_command($command, NULL, true);
				sleep(5);

				if ($vm_resource->vnc != '') {
					return $vm_resource->vnc;
				}
				$file = $basedir.'/plugins/'.$vtype.'/web/'.$vtype.'-stat/'.$resource->ip.'.'.$vm.'.vm_config';
				if($this->file->exists($file)) {
					$this->file->remove($file);
				}
				$command  = $basedir.'/plugins/'.$vtype.'/bin/openqrm-'.$vtype.'-vm post_vm_config';
				$command .= ' -i '.$resource->ip.' -n '.$vm;
				$command .= ' --openqrm-ui-user '.$this->user->name;
				$command .= ' --openqrm-cmd-mode regular';
				$this->openqrm_server->send_command($command, NULL, true);
				while (!$this->file->exists($file)) // check if the data file has been modified
				{
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
				if($this->file->exists($file)) {
					$data = openqrm_parse_conf($file);
					if (!isset($data['OPENQRM_VMWARE_ESX_VM_VNC_PORT'])) {
						return;
					}
					$tport = $data['OPENQRM_VMWARE_ESX_VM_VNC_PORT'];
					if ($tport > 0) {
						$tport = $tport - 5900;
						// $this->event->log("console", $_SERVER['REQUEST_TIME'], 2, "novnc.console.class.php", "NoVNC FILE $file EXISTS port $tport !", "", "", 0, 0, 0);
						// update vnc in resource
						if ($vm_resource->vnc == '') {
							$resource_fields["resource_vnc"] = $tport;
							$resource_fields["resource_vname"] = $vm;
							$vm_resource->update_info($id, $resource_fields);
						}
						return $tport;
					} else {
						return;
					}
				}
				return;
				break;

				
			case 'xen':
				if ($vm_resource->vnc != '') {
					return $vm_resource->vnc;
				}
				$file = $basedir.'/plugins/'.$vtype.'/web/'.$vtype.'-stat/'.$resource->id.'.'.$vm.'.vm_config';
				if($this->file->exists($file)) {
					$this->file->remove($file);
				}
				
				$command  = $basedir.'/plugins/'.$vtype.'/bin/openqrm-'.$vtype.'-vm post_vm_config';
				$command .= ' -n '.$vm;
				$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
				$command .= ' --openqrm-ui-user '.$this->user->name;
				$command .= ' --openqrm-cmd-mode regular';
				$resource->send_command($resource->ip, $command);
				while (!$this->file->exists($file)) // check if the data file has been modified
				{
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
				if($this->file->exists($file)) {
					$data = openqrm_parse_conf($file);
					if (!isset($data['OPENQRM_XEN_VM_VNC'])) {
						return;
					}
					$tport = $data['OPENQRM_XEN_VM_VNC'];
					if ($tport > 0) {
						// $this->event->log("console", $_SERVER['REQUEST_TIME'], 2, "novnc.console.class.php", "NoVNC FILE $file EXISTS port $tport !", "", "", 0, 0, 0);
						// update vnc in resource
						if ($vm_resource->vnc == '') {
							$resource_fields["resource_vnc"] = $tport;
							$resource_fields["resource_vname"] = $vm;
							$vm_resource->update_info($id, $resource_fields);
						}
						return $tport;
					} else {
						return;
					}
				}
				break;
				
			case 'vmware-vsphere':
				if ($vm_resource->vnc != '') {
					return $vm_resource->vnc;
				}
				
				$file = $basedir.'/plugins/'.$vtype.'/web/'.$vtype.'-stat/'.$resource->ip.'.'.$vm.'.vm_config';
				if($this->file->exists($file)) {
					$this->file->remove($file);
				}
				$command  = $basedir.'/plugins/'.$vtype.'/bin/openqrm-'.$vtype.'-vm post_vm_config';
				$command .= ' -i '.$resource->ip.' -n '.$vm;
				$command .= ' --openqrm-ui-user '.$this->user->name;
				$command .= ' --openqrm-cmd-mode regular';
				$this->openqrm_server->send_command($command, NULL, true);
				while (!$this->file->exists($file)) // check if the data file has been modified
				{
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
				$vm_configuration = array();
				if($this->file->exists($file)) {
					$lines = explode("\n", file_get_contents($file));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$vm_configuration = $this->openqrm->string_to_array($line, '|', '=');
								$vnc_arr = array();
								$vncport = $vm_configuration['vncport'] - 5900;
								$vnc_arr['vncport'] = $vncport;
								$vnc_arr['vncserverip'] = $vm_configuration['hostip'];
								// update vnc in resource
								if ($vm_resource->vnc == '') {
									$resource_fields["resource_vnc"] = $vm_configuration['hostip'].":".$vncport;
									$vm_resource->update_info($id, $resource_fields);
								}
								return $vnc_arr;
							}
						}
					}
				}
				return;
				break;

			case 'amazon':
				if ($vm_resource->vnc != '') {
					return $vm_resource->vnc;
				} else {
					$resource_fields["resource_vnc"] = $vm_resource->ip.":5900";
					$vm_resource->update_info($id, $resource_fields);
					return $vm_resource->ip.":5900";
				}
				break;
				
				
			default:
				$this->event->log("console", $_SERVER['REQUEST_TIME'], 2, "novnc.console.class.php", "NoVNC console for VM type ".$vtype." is not yet supported!", "", "", 0, 0, 0);
				break;

		}

	}

	//--------------------------------------------
	/**
	 * Start the proxy
	 *
	 * @access private
	 * @param string $vnchostip
	 * @param string $vncport
	 * @param string $proxy_port
	 * @param string $vm_mac
	 * @param string $resource_name
	 * @return array
	 */
	//--------------------------------------------
	function __proxy($vnchostip, $vncport, $proxy_port, $vm_mac, $resource_name) {
		//$OPENQRM_SERVER_IP_ADDRESS = $this->openqrm_server->get_ip_address();
		$OPENQRM_SERVER_IP_ADDRESS = $_SERVER["SERVER_NAME"];
		$novnc_web_port_range_start = 6000;
		$novnc_proxy_port_range_start = 6800;
		// start the novnc proxy
		$command  = $this->openqrm->get('basedir')."/plugins/novnc/bin/openqrm-novnc-manager remoteconsole";
		$command .= " -n ".$resource_name;
		$command .= " -d ".$proxy_port;
		$command .= " -m ".$vm_mac;
		$command .= " -i ".$vnchostip;
		$command .= " -v ".$vncport;
		$command .= ' --openqrm-ui-user '.$this->user->name;
		$command .= ' --openqrm-cmd-mode regular';

		$this->openqrm_server->send_command($command, NULL, true);
		// calcualte the web + proxy port
		$novnc_web_port = $novnc_web_port_range_start + $proxy_port;
		$novnc_proxy_port = $novnc_proxy_port_range_start + $proxy_port;
		return array('port' => $novnc_web_port, 'host' => $OPENQRM_SERVER_IP_ADDRESS);
	}

}

<?php
/**
 * Network Manager Add Bridge
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class network_manager_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'network_manager_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "network_manager_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'network_manager_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'network_manager_identifier';
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
	function __construct($openqrm, $response, $controller) {
		$this->controller = $controller;
		$this->response   = $response;
		$this->openqrm = $openqrm;
		$this->file = $this->openqrm->file();
		$this->user = $openqrm->user();

		$id = $this->response->html->request()->get('appliance_id');
		$this->response->add('appliance_id', $id);

		$appliance = new appliance();
		$this->appliance = $appliance->get_instance_by_id($id);

		$resource = new resource();
		$this->resource = $resource->get_instance_by_id($this->appliance->resources);

		$this->statfile = $this->openqrm->get('basedir').'/plugins/network-manager/web/storage/'.$this->resource->id.'.network_config';
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
		$response = $this->add();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/network-manager-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");

		$t->add($this->lang['legend_bridge'], 'legend_bridge');
		$t->add($this->lang['legend_ip'], 'legend_ip');
		$t->add($this->lang['legend_vlan'], 'legend_vlan');
		$t->add($this->lang['legend_dnsmasq'], 'legend_dnsmasq');
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
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
	function add() {

		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {

			$device = $form->get_request('device');
			$vlan = $form->get_request('vlan');
			$ip = $form->get_request('ip');
			$gateway = $form->get_request('gateway');
			$mask = $form->get_request('subnet');
			$bridge_mac = $form->get_request('bridge_mac');
			$first_ip = $form->get_request('first_ip');
			$last_ip = $form->get_request('last_ip');

			if((isset($ip) && $ip !== '') && (!isset($mask) || $mask === '')) {
				$form->set_error("subnet", $this->lang['error_empty']);
			}
			if((isset($mask) && $mask !== '') && (!isset($ip) || $ip === '')) {
				$form->set_error("ip", $this->lang['error_empty']);
			}
			if(
				(isset($gateway) && $gateway !== '') && 
				(!isset($mask) || $mask === '') && 
				(!isset($ip) || $ip === '')
			) {
				$form->set_error("subnet", $this->lang['error_empty']);
				$form->set_error("ip", $this->lang['error_empty']);
			}
			if(!$form->get_errors()) {
				// check ip is valid
				$check = array('ip', 'gateway', 'first_ip', 'last_ip');
				foreach($check as $ipp) {
					if(isset($ipp) && $ipp !== '') {
						$ch = explode('.', $ipp);
						if(count($ch) === 4) {
							foreach($ch as $k => $v) {
								$v = intval($v);
								if($v > 255) {
									$form->set_error($ip, $this->lang['error_ip'].'>255');
									break;
								}
								if($k === 3) {
									if($v === 0) {
										$form->set_error($ip, $this->lang['error_ip'].'<1');
										break;
									}
								}
							}
						} else {
							$form->set_error($ip, $this->lang['error_ip'].'<4');
						}
					}
				}
				if(isset($mask) && $mask !== '') {
					// check subnet
					// possible values for subnetmask
					// 0, 128, 192, 224, 240, 248, 252, 254, 255
					// first octet not 0
					$subnet_values = array(0,128,192,224,240,248,252,254,255);
					$subnet = explode('.', $mask);
					if(count($subnet) === 4) {
						foreach($subnet as $k => $v) {
							$v = intval($v);
							if($k === 0) {
								if(!in_array($v, $subnet_values) || $v === 0) {
									$form->set_error("subnet", $this->lang['error_subnet']);
								}
							}
							if($k === 1) {
								if(!in_array($v, $subnet_values) || (intval($subnet[0]) !== 255 && $v !== 0)) {
									$form->set_error("subnet", $this->lang['error_subnet']);
									break;
								}
							}
							if($k === 2) {
								if(!in_array($v, $subnet_values) || (intval($subnet[1]) !== 255 && $v !== 0)) {
									$form->set_error("subnet", $this->lang['error_subnet']);
									break;
								}
							}
							if($k === 3) {
								if(!in_array($v, $subnet_values) || (intval($subnet[2]) !== 255 && $v !== 0)) {
									$form->set_error("subnet", $this->lang['error_subnet']);
								}
							}
						}
					} else {
						$form->set_error("subnet", $this->lang['error_subnet']);
					}
				}
				if(isset($bridge_mac) && $bridge_mac === '1' && (!isset($device) || $device === '')) {
						$form->set_error("device",  $this->lang['error_empty']);
				}
				if(isset($vlan) && $vlan !== '' && (!isset($device) || $device === '')) {
						$form->set_error("device",  $this->lang['error_empty']);
				}
				if(isset($first_ip) && $first_ip !== '') {
					if(!isset($last_ip) || $last_ip === '') {
						$form->set_error("last_ip",  $this->lang['error_empty']);
					}
					if(!isset($ip) || $ip === '') {
						$form->set_error("ip",  $this->lang['error_empty']);
					}
					if(!isset($subnet) || $subnet === '') {
						$form->set_error("subnet",  $this->lang['error_empty']);
					}
					if(isset($last_ip) && bindec($this->__ip2bin($last_ip)) < bindec($this->__ip2bin($first_ip))) {
						$form->set_error("last_ip",  $this->lang['error_ip']);
					}
				}
				if(isset($last_ip) && $last_ip !== '') {
					if(!isset($first_ip) || $first_ip === '') {
						$form->set_error("first_ip",  $this->lang['error_empty']);
					}
				}
			}

			if(!$form->get_errors()) {
				$command  = $this->openqrm->get('basedir').'/plugins/network-manager/bin/openqrm-network-manager add_br';
				$command .= ' -u '.$this->openqrm->admin()->name;
				$command .= ' -p '.$this->openqrm->admin()->password;
				$command .= ' -b '.$form->get_request('name');
				$command .= ' -f '.$form->get_request('bridge_fd');
				$command .= ' -h '.$form->get_request('bridge_hello');
				$command .= ' -a '.$form->get_request('bridge_maxage');
				$command .= ' -t '.$form->get_request('bridge_stp');
				$command .= ' -m '.$bridge_mac;
				$command .= ' --openqrm-ui-user '.$this->user->name;
				$command .= ' --openqrm-cmd-mode regular';
				if(isset($device) && $device !== '') {
					$command .= ' -n '.$device;
				}
				if(isset($ip) && $ip !== '') {
					$command .= ' -i '.$ip;
				}
				if(isset($mask) && $mask !== '') {
					$command .= ' -s '.$mask;
				}
				if(isset($vlan) && $vlan !== '') {
					$command .= ' -v '.$vlan;
				}
				if(isset($gateway) && $gateway !== '') {
					$command .= ' -g '.$gateway;
				}
				if(isset($first_ip) && $first_ip !== '') {
					$command .= ' -df '.$first_ip;
				}
				if(isset($last_ip) && $last_ip !== '') {
					$command .= ' -dl '.$last_ip;
				}
				$this->controller->__reload( $this->statfile, $this->resource );
				if ($this->file->exists($this->statfile)) {
					$lines = explode("\n", $this->file->get_contents($this->statfile));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								$check = $line[1];
								if($form->get_request('name') === $check) {
									$error = sprintf($this->lang['error_exists'], $form->get_request('name'));
								}
							}
						}
					}
				}
				if(isset($error)) {
					$response->error = $error;
				} else {
					$this->resource->send_command($this->resource->ip, $command);
					$file = $this->openqrm->get('basedir').'/plugins/network-manager/web/storage/'.$this->resource->id.'.network_stat';
					while (!$this->file->exists($file)) {
						usleep(10000); // sleep 10ms to unload the CPU
						clearstatcache();
					}
					$result = trim($this->file->get_contents($file));
					if($result === 'ok') {
						$response->msg = sprintf($this->lang['msg_added'], $form->get_request('name'));
					}
					else {
						$response->error = $result;
					}
				}
			}
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
		$form = $response->get_form($this->actions_name, 'add');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	
		$d['name']['label']                         = $this->lang['form_name'];
		$d['name']['required']                      = true;
		$d['name']['validate']['regex']             = '/^[a-z0-9.]+$/i';
		$d['name']['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9.');
		$d['name']['object']['type']                = 'htmlobject_input';
		$d['name']['object']['attrib']['title']     = $this->lang['title_name'];
		$d['name']['object']['attrib']['id']        = 'name';
		$d['name']['object']['attrib']['name']      = 'name';
		$d['name']['object']['attrib']['type']      = 'text';
		$d['name']['object']['attrib']['value']     = '';
		$d['name']['object']['attrib']['maxlength'] = 8;

		$d['ip']['label']                         = $this->lang['form_ip'];
		$d['ip']['required']                      = true;
		$d['ip']['validate']['regex']             = '/^[0-9.]+$/i';
		$d['ip']['validate']['errormsg']          = $this->lang['error_ip'];
		$d['ip']['object']['type']                = 'htmlobject_input';
		$d['ip']['object']['attrib']['title']     = $this->lang['title_ip'];
		$d['ip']['object']['attrib']['id']        = 'ip';
		$d['ip']['object']['attrib']['name']      = 'ip';
		$d['ip']['object']['attrib']['type']      = 'text';
		$d['ip']['object']['attrib']['value']     = '';
		$d['ip']['object']['attrib']['maxlength'] = 15;

		$d['subnet']['label']                         = $this->lang['form_subnet'];
		$d['subnet']['required']                      = true;
		$d['subnet']['validate']['regex']             = '/^[0-9.]+$/i';
		$d['subnet']['validate']['errormsg']          = $this->lang['error_subnet'];
		$d['subnet']['object']['type']                = 'htmlobject_input';
		$d['subnet']['object']['attrib']['title']     = $this->lang['title_subnet'];
		$d['subnet']['object']['attrib']['id']        = 'subnet';
		$d['subnet']['object']['attrib']['name']      = 'subnet';
		$d['subnet']['object']['attrib']['type']      = 'text';
		$d['subnet']['object']['attrib']['value']     = '';
		$d['subnet']['object']['attrib']['maxlength'] = 15;

		$d['gateway']['label']                         = $this->lang['form_gateway'];
		$d['gateway']['validate']['regex']             = '/^[0-9.]+$/i';
		$d['gateway']['validate']['errormsg']          = $this->lang['error_ip'];
		$d['gateway']['object']['type']                = 'htmlobject_input';
		$d['gateway']['object']['attrib']['title']     = $this->lang['title_gateway'];
		$d['gateway']['object']['attrib']['id']        = 'gateway';
		$d['gateway']['object']['attrib']['name']      = 'gateway';
		$d['gateway']['object']['attrib']['type']      = 'text';
		$d['gateway']['object']['attrib']['value']     = '';
		$d['gateway']['object']['attrib']['maxlength'] = 15;

		$vlan[] = array('');
		for($i=0; $i<=100; $i++) {
			$vlan[] = array($i);
		}
		$d['vlan']['label']                         = $this->lang['form_vlan'];
		$d['vlan']['validate']['regex']             = '/^[0-9]+$/i';
		$d['vlan']['validate']['errormsg']          = sprintf($this->lang['error_vlan'], '0-9');
		$d['vlan']['object']['type']                = 'htmlobject_select';
		$d['vlan']['object']['attrib']['title']     = $this->lang['title_vlan'];
		$d['vlan']['object']['attrib']['id']        = 'vlan';
		$d['vlan']['object']['attrib']['name']      = 'vlan';
		$d['vlan']['object']['attrib']['index']     = array(0,0);
		$d['vlan']['object']['attrib']['options']   = $vlan;
		$d['vlan']['object']['attrib']['maxlength'] = 4;

		for($i=0; $i<=20; $i++) {
			$range[] = array($i);
		}
		$d['bridge_fd']['label']                         = $this->lang['form_bridge_fd'];
		$d['bridge_fd']['validate']['regex']             = '/^[0-9]+$/i';
		$d['bridge_fd']['validate']['errormsg']          = sprintf($this->lang['error_vlan'], '0-9');
		$d['bridge_fd']['object']['type']                = 'htmlobject_select';
		$d['bridge_fd']['object']['attrib']['title']     = $this->lang['title_bridge_fd'];
		$d['bridge_fd']['object']['attrib']['id']        = 'bridge_fd';
		$d['bridge_fd']['object']['attrib']['name']      = 'bridge_fd';
		$d['bridge_fd']['object']['attrib']['index']     = array(0,0);
		$d['bridge_fd']['object']['attrib']['options']   = $range;
		$d['bridge_fd']['object']['attrib']['selected']  = array(0);
		$d['bridge_fd']['object']['attrib']['maxlength'] = 4;

		$d['bridge_hello']['label']                         = $this->lang['form_bridge_hello'];
		$d['bridge_hello']['validate']['regex']             = '/^[0-9]+$/i';
		$d['bridge_hello']['validate']['errormsg']          = sprintf($this->lang['error_vlan'], '0-9');
		$d['bridge_hello']['object']['type']                = 'htmlobject_select';
		$d['bridge_hello']['object']['attrib']['title']     = $this->lang['title_bridge_hello'];
		$d['bridge_hello']['object']['attrib']['id']        = 'bridge_hello';
		$d['bridge_hello']['object']['attrib']['name']      = 'bridge_hello';
		$d['bridge_hello']['object']['attrib']['index']     = array(0,0);
		$d['bridge_hello']['object']['attrib']['options']   = $range;
		$d['bridge_hello']['object']['attrib']['selected']  = array(2);
		$d['bridge_hello']['object']['attrib']['maxlength'] = 4;

		$d['bridge_maxage']['label']                         = $this->lang['form_bridge_maxage'];
		$d['bridge_maxage']['validate']['regex']             = '/^[0-9]+$/i';
		$d['bridge_maxage']['validate']['errormsg']          = sprintf($this->lang['error_vlan'], '0-9');
		$d['bridge_maxage']['object']['attrib']['title']     = $this->lang['title_bridge_maxage'];
		$d['bridge_maxage']['object']['type']                = 'htmlobject_select';
		$d['bridge_maxage']['object']['attrib']['id']        = 'bridge_maxage';
		$d['bridge_maxage']['object']['attrib']['name']      = 'bridge_maxage';
		$d['bridge_maxage']['object']['attrib']['index']     = array(0,0);
		$d['bridge_maxage']['object']['attrib']['options']   = $range;
		$d['bridge_maxage']['object']['attrib']['selected']  = array(12);
		$d['bridge_maxage']['object']['attrib']['maxlength'] = 4;


		$bool = array(array(1,'on'),array(0,'off'));
		$d['bridge_stp']['label']                         = $this->lang['form_bridge_stp'];
		$d['bridge_stp']['validate']['regex']             = '/^[0-9]+$/i';
		$d['bridge_stp']['validate']['errormsg']          = sprintf($this->lang['error_vlan'], '0-9');
		$d['bridge_stp']['object']['type']                = 'htmlobject_select';
		$d['bridge_stp']['object']['attrib']['title']     = $this->lang['title_bridge_stp'];
		$d['bridge_stp']['object']['attrib']['id']        = 'bridge_stp';
		$d['bridge_stp']['object']['attrib']['name']      = 'bridge_stp';
		$d['bridge_stp']['object']['attrib']['index']     = array(0,1);
		$d['bridge_stp']['object']['attrib']['options']   = $bool;
		$d['bridge_stp']['object']['attrib']['selected']  = array(0);
		$d['bridge_stp']['object']['attrib']['maxlength'] = 4;

		$d['bridge_mac']['label']                         = $this->lang['form_bridge_mac'];
		$d['bridge_mac']['validate']['regex']             = '/^[0-9]+$/i';
		$d['bridge_mac']['validate']['errormsg']          = sprintf($this->lang['error_vlan'], '0-9');
		$d['bridge_mac']['object']['type']                = 'htmlobject_select';
		$d['bridge_mac']['object']['attrib']['title']     = $this->lang['title_bridge_mac'];
		$d['bridge_mac']['object']['attrib']['id']        = 'bridge_mac';
		$d['bridge_mac']['object']['attrib']['name']      = 'bridge_mac';
		$d['bridge_mac']['object']['attrib']['index']     = array(0,1);
		$d['bridge_mac']['object']['attrib']['options']   = $bool;
		$d['bridge_mac']['object']['attrib']['selected']  = array(0);
		$d['bridge_mac']['object']['attrib']['maxlength'] = 4;

		$d['first_ip']['label']                         = $this->lang['form_first_ip'];
		$d['first_ip']['validate']['regex']             = '/^[0-9.]+$/i';
		$d['first_ip']['validate']['errormsg']          = $this->lang['error_ip'];
		$d['first_ip']['object']['type']                = 'htmlobject_input';
		$d['first_ip']['object']['attrib']['title']     = $this->lang['title_first_ip'];
		$d['first_ip']['object']['attrib']['id']        = 'first_ip';
		$d['first_ip']['object']['attrib']['name']      = 'first_ip';
		$d['first_ip']['object']['attrib']['type']      = 'text';
		$d['first_ip']['object']['attrib']['value']     = '';
		$d['first_ip']['object']['attrib']['maxlength'] = 15;

		$d['last_ip']['label']                         = $this->lang['form_last_ip'];
		$d['last_ip']['validate']['regex']             = '/^[0-9.]+$/i';
		$d['last_ip']['validate']['errormsg']          = $this->lang['error_ip'];
		$d['last_ip']['object']['type']                = 'htmlobject_input';
		$d['last_ip']['object']['attrib']['title']     = $this->lang['title_last_ip'];
		$d['last_ip']['object']['attrib']['id']        = 'last_ip';
		$d['last_ip']['object']['attrib']['name']      = 'last_ip';
		$d['last_ip']['object']['attrib']['type']      = 'text';
		$d['last_ip']['object']['attrib']['value']     = '';
		$d['last_ip']['object']['attrib']['maxlength'] = 15;

		$this->controller->__reload( $this->statfile, $this->resource );
		$nics[] = array('', '');
		if ($this->file->exists($this->statfile)) {
			$lines = explode("\n", $this->file->get_contents($this->statfile));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						if($line[0] === 'n') {
							$nics[$line[1]] = array($line[1], $line[1]);
						}
						elseif($line[0] === 'b') {
							if(isset($line[4]) && $line[4] !== '') {
								$up[] = $line[4];
							}
						}
					}
				}
			}
		}
		// remove nics in use
		if(isset($up) && is_array($up)) {
			foreach($up as $u) {
				unset($nics[$u]);
			}
		}
		$d['device']['label']                         = $this->lang['form_device'];
		$d['device']['required']                      = true;
		$d['device']['object']['type']                = 'htmlobject_select';
		$d['device']['object']['attrib']['title']     = $this->lang['title_device'];
		$d['device']['object']['attrib']['id']        = 'device';
		$d['device']['object']['attrib']['name']      = 'device';
		$d['device']['object']['attrib']['index']     = array(0,1);
		$d['device']['object']['attrib']['options']   = $nics;
		$d['device']['object']['attrib']['maxlength'] = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}



	//--------------------------------------------------
	/**
	* Ip Adress to binary
	* @access public
	* @param string $ip
	* @return string
	*/
	//--------------------------------------------------
	function __ip2bin($ip)
	{
		$return = '';
		if(!preg_match("/^\d+\.\d+\.\d+\.\d+$/", $ip)) return -1;
		$ar = explode(".", $ip);
		foreach($ar as $a)
		{
			$return .= str_pad(decbin($a), 8, 0, STR_PAD_LEFT);
		}
		return $return;
	}

}

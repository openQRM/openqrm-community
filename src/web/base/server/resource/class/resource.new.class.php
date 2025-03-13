<?php
/**
 * Resource New
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class resource_new
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'resource_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "resource_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'resource_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'resource_identifier';
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
		$this->user	  = $openqrm->user();
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
		$response = $this->resource_new();
		if(isset($response->msg)) {
			// wizard
			if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 2) {
				sleep(4);
				$this->response->redirect(
					$this->response->html->thisfile.'?base=appliance&appliance_action=step'.$this->user->wizard_step.'&appliance_id='.$this->user->wizard_id.'&resource_id='.$response->resource_id
				);
			} else {
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
				);
			}
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/resource-new.tpl.php');
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['form_docu'], 'form_docu');
		$t->add($this->lang['form_auto_resource'], 'form_auto_resource');
		$t->add($this->lang['form_add_resource'], 'form_add_resource');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * New
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function resource_new() {

		$response = $this->get_response();
		$form     = $response->form;
		$name	= $form->get_request('name');
		$ip		= $form->get_request('ip');
		$mac	= $form->get_request('mac');

		if (strlen($name)) {
			if(!$form->get_errors() && $this->response->submit()) {

				// check that mac adress + ip does not yet exists
				$resource = new resource();
				$resource->get_instance_by_mac($mac);
				if (strlen($resource->id)) {
					$response->error = sprintf($this->lang['msg_mac_in_use']);
					return $response;
				}
				$resource->get_instance_by_ip($ip);
				if (strlen($resource->id)) {
					$response->error = sprintf($this->lang['msg_ip_in_use']);
					return $response;
				}

				$tables = $this->openqrm->get('table');
				$id = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
				$mac = strtolower($mac);
				// send command to the openQRM-server
				$openqrm = new openqrm_server();
				$openqrm->send_command('openqrm_server_add_resource '.$id.' '.$mac.' '.$ip);
				// add to openQRM database
				$fields["resource_id"] = $id;
				$fields["resource_hostname"] = $name;
				$fields["resource_ip"] = $ip;
				$fields["resource_mac"] = $mac;
				$fields["resource_localboot"]=0;
				$fields["resource_vtype"]=1;
				$fields["resource_vhostid"]=$id;
				$fields["resource_state"]='unknown';
				$fields["resource_lastgood"]='-1';
				$resource->add($fields);
				// set lastgood to -1 to prevent automatic checking the state
				$resource->update_info($id, $fields);
				$response->msg = sprintf($this->lang['msg'], $name);
				$response->resource_id = $id;
			} else {
				$response->error = sprintf($this->lang['msg_add_failed']);
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
		$form = $response->get_form($this->actions_name, 'new');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d['name']['label']                         = $this->lang['form_name'];
		$d['name']['required']                      = true;
		$d['name']['validate']['regex']             = '/^[a-z0-9._]+$/i';
		$d['name']['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9._');
		$d['name']['object']['type']                = 'htmlobject_input';
		$d['name']['object']['attrib']['name']      = 'name';
		$d['name']['object']['attrib']['type']      = 'text';
		$d['name']['object']['attrib']['value']     = '';
		$d['name']['object']['attrib']['maxlength'] = 50;

		$d['ip']['label']                         = $this->lang['form_ip'];
		$d['ip']['required']                      = true;
		$d['ip']['validate']['regex']             = '/^[a-z0-9._]+$/i';
		$d['ip']['validate']['errormsg']          = sprintf($this->lang['error_ip'], 'a-z0-9._');
		$d['ip']['object']['type']                = 'htmlobject_input';
		$d['ip']['object']['attrib']['name']      = 'ip';
		$d['ip']['object']['attrib']['type']      = 'text';
		$d['ip']['object']['attrib']['value']     = '';
		$d['ip']['object']['attrib']['maxlength'] = 50;

		$d['mac']['label']                         = $this->lang['form_mac'];
		$d['mac']['required']                      = true;
		$d['mac']['validate']['regex']             = '/^[a-z0-9._:]+$/i';
		$d['mac']['validate']['errormsg']          = sprintf($this->lang['error_mac'], 'a-z0-9._');
		$d['mac']['object']['type']                = 'htmlobject_input';
		$d['mac']['object']['attrib']['name']      = 'mac';
		$d['mac']['object']['attrib']['type']      = 'text';
		$d['mac']['object']['attrib']['value']     = '';
		$d['mac']['object']['attrib']['maxlength'] = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}

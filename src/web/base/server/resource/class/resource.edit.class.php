<?php
/**
 * Resource Update
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class resource_edit
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
		$this->basedir = $this->openqrm->get('basedir');
		$this->response->params['resource_id'] = $this->response->html->request()->get('resource_id');

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
		$response = $this->edit();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/resource-edit.tpl.php');
		$t->add(sprintf($this->lang['label'], $this->response->html->request()->get('resource_id')), 'label');
		$t->add($this->lang['form_docu'], 'form_docu');
		$t->add($this->lang['form_edit_resource'], 'form_edit_resource');
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
	function edit() {
		$response = $this->get_response();
		$form	= $response->form;
		$id		= $this->response->html->request()->get('resource_id');
		$ip		= $form->get_request('ip');
		if (strlen($ip)) {
			if(!$form->get_errors() && $this->response->submit()) {
				$resource = new resource();
				// ip in use already ?
				$resource->get_instance_by_ip($ip);
				if (strlen($resource->id)) {
					$response->error = sprintf($this->lang['msg_ip_in_use']);
					return $response;
				}
				$resource->get_instance_by_id($id);

				// check for plugins which needs ip change
				$openqrm_server = new openqrm_server();
				$plugin = new plugin();
				$enabled_plugins = $plugin->enabled();
				if (in_array('dhcpd', $enabled_plugins)) {
					// adjust dhcpd ip if dhcpd plugin is enabled
					$openqrm_server->send_command($this->basedir."/plugins/dhcpd/bin/openqrm-dhcpd-manager remove -d ".$resource->id." -m ".$resource->mac." -i ".$resource->ip." -u ".$openqrm_admin_user->name." -p ".$openqrm_admin_user->password." --openqrm-cmd-mode background");
					sleep(4);
					$openqrm_server->send_command($this->basedir."/plugins/dhcpd/bin/openqrm-dhcpd-manager add -d ".$resource->id." -m ".$resource->mac." -i ".$ip." -u ".$openqrm_admin_user->name." -p ".$openqrm_admin_user->password." --openqrm-cmd-mode background");
				}
				if (in_array('dns', $enabled_plugins)) {
					// adjust dns ip if dns plugin is enabled
					$appliance = new appliance();
					$appliance_list = $appliance->get_ids_per_resource($resource->id);
					
					foreach ($appliance_list as $app) {
						$appliance->get_instance_by_id($app['appliance_id']);
						// adjust dns entry
						$openqrm_server->send_command($this->basedir."/plugins/dns/bin/openqrm-dns-manager stop ".$appliance->id." ".$appliance->name." ".$resource->ip." --openqrm-cmd-mode background");
						sleep(4);
						$openqrm_server->send_command($this->basedir."/plugins/dns/bin/openqrm-dns-manager start ".$appliance->id." ".$appliance->name." ".$ip." --openqrm-cmd-mode background");
					}
				}

				// update resource
				$fields["resource_ip"] = $ip;
				$resource->update_info($id, $fields);
				$response->msg = sprintf($this->lang['msg'], $id);
				$response->resource_id = $id;
                                
                                
			} else {
				$response->error = sprintf($this->lang['msg_edit_failed']);
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
		$form = $response->get_form($this->actions_name, 'edit');

		$resource = new resource();
		$resource->get_instance_by_id($this->response->html->request()->get('resource_id'));

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d['ip']['label']                         = $this->lang['form_ip'];
		$d['ip']['required']                      = true;
		$d['ip']['validate']['regex']             = '/^[a-z0-9._]+$/i';
		$d['ip']['validate']['errormsg']          = sprintf($this->lang['error_ip'], 'a-z0-9._');
		$d['ip']['object']['type']                = 'htmlobject_input';
		$d['ip']['object']['attrib']['name']      = 'ip';
		$d['ip']['object']['attrib']['type']      = 'text';
		$d['ip']['object']['attrib']['value']     = $resource->ip;
		$d['ip']['object']['attrib']['maxlength'] = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}

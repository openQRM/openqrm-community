<?php
/**
 * local-server-about Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class local_server_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'local_server_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'local_server_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'local_server_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'local_server_about_identifier';
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
var $lang = array(
	'documentation' => array (
		'tab' => 'About Local-Server',
		'label' => 'About Local-Server',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The local-server-plugin provides an integration for already existing, local-installed systems in openQRM.
			After integrating an existing, local-installed server it can be used "grab" the systems root-fs and transform
			it to an openQRM server-image. It also allows to dynamically deploy network-booted server images while
			still being able to restore/restart the existing server-system located on the local-harddisk.
			No manual configuration is needed.',
		'requirements_title' => 'Requirements',
		'requirements_list' => '<li>none</li>',
		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.',
		'provides_title' => 'Provides',
		'provides_list' => '<li>Integrates existing, local installed Systems into openQRM</li>',
		'type_title' => 'Plugin Type',
		'type_content' => 'Misc',
		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
	),
	'usage' => array (
		'tab' => 'About Local-Server',
		'label' => 'Local-Server Use-Cases',
		'usage_integrate_title' => 'Local-Server Integrate',
		'usage_integrate_content' => '<ul><li>Copy (scp) the "openqrm-local-server" utility to an existing, local-installed server in your network<br><br>
			<i><b>scp %s/plugins/local-server/bin/openqrm-local-server [ip-address-of-existing-server]:/tmp/</b></i>
			<br><br>
			</li><li>
			Execute the "openqrm-local-server" utility on the remote system via ssh e.g. :
			<br><br>
			<i><b>ssh [ip-address-of-existing-server] /tmp/openqrm-local-server integrate -u openqrm -p openqrm -q %s -i eth0 [-s http/https]</b></i>
			<br><br>
			</li><li>
			The system now appears in the openQRM-server as new resource
			<br><br>
			It should be now set to "network-boot" in its BIOS to allow dynamic assign- and deployment.
			<br>
			The resource can now be used to e.g. create a new "storage-server" within openQRM.
			</li><li>
			After setting the system to "network-boot" in its BIOS it also can be used to deploy server-images from diffrent types.
			</li></ul>',
		'usage_remove_title' => 'Local-Server Remove',
		'usage_remove_content' => '<ul><li>To remove a system from openQRM integrated via the local-server plugin run the "openqrm-local-server" utility again. e.g. :<br><br>
			<i><b>ssh [ip-address-of-existing-server] /tmp/openqrm-local-server remove -u openqrm -p openqrm -q %s [-s http/https]</b></i>
			<br><br>
			</li></ul>',

	),
	'localvm' => array (
		'tab' => 'About Local VMs',
		'label' => 'Local-Server for Local Virtual Machines',
		'usage_localvm_title' => 'How to use Local-Server for Local Virtual Machines',
		'usage_localvm' => 'For local-installed Virtual Machines (e.g. kvm-storage, xen-storage, lxc-storage, openvz-storage)
			which have access to the openQRM network there is an "openqrm-local-vm-client" available.
			This "openqrm-local-vm-client" just starts and stops the plugin-boot-services to allow further management functionality.
			Monitoring and openQRM actions are still running on behalf of the VM Host.',
		'usage_integrate_localvm' => '<ul><li>Download/Copy the <a href="/openqrm/base/plugins/local-server/local-vm/openqrm-local-vm-client" target="_BLANK">"openqrm-local-vm-client"</a> to a local installed VM<br><br>
			<i><b>scp openqrm-local-vm-client [ip-address-of-existing-server]:/tmp/</b></i>
			<br><br>
			</li><li>
			Execute the "openqrm-local-vm-client" on the VM
			<br><br>
			<i><b>openqrm-local-vm-client</b></i>
			<br><br>
			</li><li>
			The "openqrm-local-vm-client" fully automatically configures itself.
			</li></ul>',
	),

);

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
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/local-server/lang", 'local-server-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/local-server/tpl';
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		}
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = "documentation";
		}
		$content = array();
		switch( $this->action ) {
			case '':
			case 'documentation':
				$content[] = $this->documentation(true);
			break;
			case 'localvm':
				$content[] = $this->localvm(true);
			break;
			case 'usage':
				$content[] = $this->usage(true);
			break;
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}


	//--------------------------------------------
	/**
	 * About Local-Server
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/local-server/class/local-server-about.documentation.class.php');
			$controller = new local_server_about_documentation($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['documentation'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['documentation']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'documentation' );
		$content['onclick'] = false;
		if($this->action === 'documentation'){
			$content['active']  = true;
		}
		return $content;
	}





	//--------------------------------------------
	/**
	 * About Local-Server VM management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function localvm( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/local-server/class/local-server-about.localvm.class.php');
			$controller = new local_server_about_localvm($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['localvm'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['localvm']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'localvm' );
		$content['onclick'] = false;
		if($this->action === 'localvm'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * About Local-Server Use-Cases
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function usage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/local-server/class/local-server-about.usage.class.php');
			$controller = new local_server_about_usage($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['usage'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['usage']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'usage' );
		$content['onclick'] = false;
		if($this->action === 'usage'){
			$content['active']  = true;
		}
		return $content;
	}

}

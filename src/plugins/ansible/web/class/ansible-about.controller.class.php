<?php
/**
 * Ansible Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */


class ansible_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'ansible_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'ansible_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'ansible_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'ansible_about_identifier';
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
		'tab' => 'About Ansible',
		'label' => 'About Ansible',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The Ansible plugin provides automated configuration-management for Server in openQRM.
			It seamlessly integrates <a href="http://www.ansibleworks.com" target="_BLANK">Ansible</a> within the openQRM GUI and assists to apply pre-made or custom Ansible Playbooks to specific Server.
			By enabling the plugin the Ansible-environment is pre-configured and initialyzed automatically according to best-practice experiences e.g. by keeping the ansible-configuration within a svn-repsitory.
			Custom Ansible Playbookds can be added to the playbooks directory in the subversion repository.
			All Playbooks will be automatically displayed in the Ansible plugin server manager.
			The ansible-configuration repository is also available for external svn clients.
			To check out the ansible-repo please run<br><br>:',
		'introduction_title1' => 'Assigning Applications to Server',
		'introduction_content1' => '<ol><li>Go to the "Server" in the Ansible plugin menu</li>
			<li>Select a Server to be configured via Ansible</li>
			<li>Select the Ansible Playbookds to be applied to the server</li></ol>
			Within short time the ansible-server will distribute the new configuration to the Appliance automatically.',
		'requirements_title' => 'Requirements',
		'requirements_list' => '<li>The Ansible Plugin depends on the Dhcpd Plugin! Please make sure to have the Dhcpd Plugin enabled and started before.</li>',
		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.',
		'provides_title' => 'Provides',
		'provides_list' => '<li>Automated configuration-management for Server in openQRM</li>',
		'type_title' => 'Plugin Type',
		'type_content' => 'Deployment',
		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/ansible/lang", 'ansible-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/ansible/tpl';
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
		$content = array();
		switch( $this->action ) {
			case '':
			case 'documentation':
				$content[] = $this->documentation(true);
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
	 * About Ansible
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/ansible/class/ansible-about.documentation.class.php');
			$controller = new ansible_about_documentation($this->openqrm, $this->response);
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



}

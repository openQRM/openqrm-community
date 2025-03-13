<?php
/**
 * Puppet Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */


class puppet_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'puppet_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'puppet_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'puppet_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'puppet_about_identifier';
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
		'tab' => 'About Puppet',
		'label' => 'About Puppet',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The Puppet plugin provides automated configuration-management for Appliances in openQRM.
			It seamlessly integrates <a href="http://projects.puppetlabs.com/projects/puppet" target="_BLANK">Puppet</a> within the openQRM GUI and assists to put specific Appliances into pre-made or custom Puppet-classes.
			By enabling the plugin the puppet-environment (server and client) is pre-configured and initialyzed automatically according to best-practice experiences e.g. by keeping the puppet-configuration within a svn-repsitory.
			The puppet-configuration is organized in "classes", "goups" and "appliances".
			Custom custom classes can be added to the class-directory.
			Classes should be combined to "groups" these will be automatically displayed in the puppet-plugin.
			The puppet-configuration repository is also available for external svn clients.
			To check out the puppet-repo please run<br><br>:',
		'introduction_title1' => 'Assigning Applications to Appliances',
		'introduction_content1' => '<ol><li>Go to the "Appliances" in the puppet-plugin menu</li>
			<li>Select an Appliance to be configured via puppet</li>
			<li>Select the puppet-groups the appliance should belong to</li></ol>
			Within short time the puppet-server will distribute the new configuration to the Appliance automatically.',
		'requirements_title' => 'Requirements',
		'requirements_list' => '<li>The Puppet Plugin depends on the DNS Plugin! Please make sure to have the DNS Plugin enabled and started before.</li>',
		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.',
		'provides_title' => 'Provides',
		'provides_list' => '<li>Automated configuration-management for Appliances in openQRM</li>',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/puppet/lang", 'puppet-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/puppet/tpl';
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
	 * About Puppet
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/puppet/class/puppet-about.documentation.class.php');
			$controller = new puppet_about_documentation($this->openqrm, $this->response);
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

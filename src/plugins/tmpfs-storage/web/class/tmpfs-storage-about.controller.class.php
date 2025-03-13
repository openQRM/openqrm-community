<?php
/**
 * tmpfs-about Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */


class tmpfs_storage_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'tmpfs_storage_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'tmpfs_storage_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'tmpfs_storage_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'tmpfs_storage_about_identifier';
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
		'tab' => 'About TmpFs-Storage',
		'label' => 'About TmpFs-Storage',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The "TmpFs-Storage" plugin provisions server in memeory (tmpfs).',
		'requirements_title' => 'Requirements',
		'requirements_list' => 'A resource for the TmpFs-Storage Storage (this can be a remote system integrated into openQRM e.g. via the "local-server" plugin or the openQRM server itself)',

		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.',

		'provides_title' => 'Provides',
		'provides_list' => '<ul><li>Storage type: "TmpFs Storage"</li><li>Deployment types: "Tmpfs-root deployment"</li></ul>',
		'type_title' => 'Plugin Type',
		'type_content' => 'Storage',

		'deployment_title' => 'Deployment Type',
		'deployment_content' => 'Network-Deployment',

		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
		'network_deploymet' => 'Network-Deployment',
		'doc1' => '',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/tmpfs-storage/lang", 'tmpfs-storage-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/tmpfs-storage/tpl';
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
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}


	//--------------------------------------------
	/**
	 * About TmpFs-Storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/tmpfs-storage/class/tmpfs-storage-about.documentation.class.php');
			$controller = new tmpfs_storage_about_documentation($this->openqrm, $this->response);
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

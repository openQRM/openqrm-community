<?php
/**
 * novnc-about Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class novnc_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'novnc_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'novnc_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'novnc_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'novnc_about_identifier';
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
		'tab' => 'About NoVNC',
		'label' => 'About NoVNC',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The NoVNC Plugin integrates <a href="http://kanaka.github.com/noVNC/" target="_BLANK">NoVNC</a> into openQRM.
			It provides a remote console login to the openQRM Server and the managed Systems through the Web Interface.
			No manual configuration is needed.',

		'requirements_title' => 'Requirements',
		'requirements_list' => '<li>none</li>',
		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.',
		'provides_title' => 'Provides',
		'provides_list' => '<li>NoVNC Login through the openQRM Web Interface</li>',
		'type_title' => 'Plugin Type',
		'type_content' => 'Management',
		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
	),
	'usage' => array (
		'tab' => 'About NoVNC',
		'label' => 'NoVNC Use-Cases',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/novnc/lang", 'novnc-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/novnc/tpl';
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
			case 'bootservice':
				$content[] = $this->bootservice(true);
			break;
			case 'vms':
				$content[] = $this->vms(true);
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
	 * About NoVNC
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/novnc/class/novnc-about.documentation.class.php');
			$controller = new novnc_about_documentation($this->openqrm, $this->response);
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
	 * Boot-Service
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function bootservice( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/novnc/class/novnc-about.bootservice.class.php');
			$controller = new novnc_about_bootservice($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['bootservice'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['bootservice']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'bootservice' );
		$content['onclick'] = false;
		if($this->action === 'bootservice'){
			$content['active']  = true;
		}
		return $content;
	}




	//--------------------------------------------
	/**
	 * About NoVNC VM management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vms( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/novnc/class/novnc-about.vms.class.php');
			$controller = new novnc_about_vms($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['vms'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['vms']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'vms' );
		$content['onclick'] = false;
		if($this->action === 'vms'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * About NoVNC Use-Cases
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function usage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/novnc/class/novnc-about.usage.class.php');
			$controller = new novnc_about_usage($this->openqrm, $this->response);
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

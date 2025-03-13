<?php
/**
 * aa_plugins Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class aa_plugins_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'aa_plugins_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "aa_plugins_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'aa_plugins_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'aa_plugins_identifier';
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
	'select' => array (
		'tab' => 'Plugin Manager',
		'label' => 'Plugin Manager',
		'action_start' => 'start',
		'action_stop' => 'stop',
		'action_enable' => 'install',
		'action_disable' => 'uninstall',
		'action_configure' => 'configure',
		'title_start' => 'click to start plugin %s',
		'title_stop' => 'click to stop plugin %s',
		'title_enable' => 'click to install plugin %s',
		'title_disable' => 'click to uninstall plugin %s',
		'title_configure' => 'click to configure plugin %s',
		'table_name' => 'Plugin',
		'table_type' => 'Type',
		'table_description' => 'Description',
		'table_enabled' => 'installed',
		'table_started' => 'running',
		'lang_filter' => 'Filter by type',
		'please_wait' => 'Loading. Please wait ..',
	), 
	'start' => array (
		'label' => 'Start plugin',
		'msg' => 'Started plugin %s',
		'error_timeout' => 'Timeout while trying to start plugin %s',
		'error_start' => 'Could not start plugin %s',
	),
	'stop' => array (
		'label' => 'Stop plugin',
		'msg' => 'Stopped plugin %s',
		'error_timeout' => 'Timeout while trying to stop plugin %s',
		'error_stop' => 'Could not stop plugin %s',
	),
	'enable' => array (
		'label' => 'Install plugin',
		'msg' => 'Install plugin %s',
		'error_timeout' => 'Timeout while trying to install plugin %s',
		'error_enable' => 'Could not install plugin %s',
		'error_enabled' => 'Plugin %s already installed',
		'error_dependencies' => 'Dependencies for %s failed. Please install %s first.',
	),
	'disable' => array (
		'label' => 'Uninstall plugin',
		'msg' => 'Uninstall plugin %s',
		'error_timeout' => 'Timeout while trying to uninstall plugin %s',
		'error_in_use' => 'Plugin %s is still in use by openQRM',
		'error_disable' => 'Could not uninstall plugin %s',
		'error_dependencies' => 'Dependencies for %s failed. Please uninstall %s first.',
	),
	'configure' => array (
		'tab' => 'Configure Plugin',
		'label' => 'Configure Plugin %s',
		'msg' => 'Configured plugin %s',
		'no_data' => 'No values found to configure',
		'error_value' => 'Please do not use blank spaces',
		'canceled' => 'Operation canceled. Please wait ..',
		'please_wait' => 'Loading. Please wait ..',
	),
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param openqrm $openqrm
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		$this->tpldir   = $this->rootdir.'plugins/aa_plugins/tpl';
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/aa_plugins/lang", 'aa_plugins.ini');
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
			if(is_array($ar)) {
				$this->action = key($ar);
			} else {
				$this->action = $ar;
			}
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = "select";
		}

		// handle response
		$this->response->params['plugin_filter'] = $this->response->html->request()->get('plugin_filter');
		$vars = $this->response->html->request()->get('plugins');
		if($vars !== '') {
			if(!isset($vars['action'])) {
				foreach($vars as $k => $v) {
					$this->response->add('plugins['.$k.']', $v);
				}
			} else {
				foreach($vars as $k => $v) {
					unset($this->response->params['plugins['.$k.']']);
				}
			}
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'start':
				$content[] = $this->start(true);
			break;
			case 'stop':
				$content[] = $this->stop(true);
			break;
			case 'enable':
				$content[] = $this->enable(true);
			break;
			case 'disable':
				$content[] = $this->disable(true);
			break;
			case 'configure':
				$content[] = $this->select(false);
				$content[] = $this->configure(true);
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
	 * API
	 *
	 * @access public
	 */
	//--------------------------------------------
	function api() {
		require_once($this->rootdir.'/plugins/aa_plugins/class/aa_plugins.api.class.php');
		$controller = new aa_plugins_api($this);
		$controller->action();
	}

	
	//--------------------------------------------
	/**
	 * Select Plugins
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/aa_plugins/class/aa_plugins.select.class.php');
			$controller = new aa_plugins_select($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['select'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['select']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
	}	
	
	//--------------------------------------------
	/**
	 * Start Plugin
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function start( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/aa_plugins/class/aa_plugins.start.class.php');
			$controller                  = new aa_plugins_start($this->response, $this->file);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['start'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Start';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'start' );
		$content['onclick'] = false;
		if($this->action === 'start'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Stop Plugin
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function stop( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/aa_plugins/class/aa_plugins.stop.class.php');
			$controller                  = new aa_plugins_stop($this->response, $this->file);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['stop'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Stop';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'stop' );
		$content['onclick'] = false;
		if($this->action === 'stop'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Enable Plugin
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function enable( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/aa_plugins/class/aa_plugins.enable.class.php');
			$controller                  = new aa_plugins_enable($this->response, $this->file);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['enable'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Enable';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'enable' );
		$content['onclick'] = false;
		if($this->action === 'enable'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Disable Plugin
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function disable( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/aa_plugins/class/aa_plugins.disable.class.php');
			$controller                  = new aa_plugins_disable($this->response, $this->file);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['disable'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'disable';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'disable' );
		$content['onclick'] = false;
		if($this->action === 'disable'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Configure Plugin
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function configure( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/aa_plugins/class/aa_plugins.configure.class.php');
			$controller                  = new aa_plugins_configure($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['configure'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['configure']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'configure' );
		$content['onclick'] = false;
		if($this->action === 'configure'){
			$content['active']  = true;
		}
		return $content;
	}

}

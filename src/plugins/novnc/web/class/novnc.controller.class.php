<?php
/**
 * noVNC Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class novnc_controller
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
var $message_param = "novnc_msg";
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
	'login' => array (
		'tab' => 'NoVNC Login',
		'label' => 'NoVNC Login to %s',
		'login_msg' => 'Login to appliance %s',
		'form_port' => 'VNC Port',
		'info' => 'openQRM could not determine the VNC port to set up the proxy. Please type in the 2 last bits of the port e.g. 01, 10 or 45 and submit the form.',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'console' => array (
		'lang_Ctrl-Alt-Del' => 'Send Ctrl-Alt-Del',
		'lang_clipboard' => 'Clipboard',
		'lang_settings' => 'Settings',
		'lang_connect' => 'Connect',
		'lang_disconnect' => 'Disconnect',
		'lang_host' => 'Host',
		'lang_port' => 'Port',
		'lang_password' => 'Password',
		'lang_encrypt' => 'Encrypt',
		'lang_true_color' => 'True Color',
		'lang_local_cursor' => 'Local Cursor',
		'lang_clip' => 'Clip to Window',
		'lang_shared_mode' => 'Shared Mode',
		'lang_view_only' => 'View Only',
		'lang_timeout' => 'Connect Timeout (s)',
		'lang_apply' => 'Apply',
		'lang_clear' => 'Clear',
		'lang_error_js_disabled' => 'Error: Javascript is disabled',
		'lang_ssl_check' => 'Checking SSL certificate ..',
		'lang_detach' => 'Detach window',
		'error_no_port' => 'Error: Could not determain vnc port for Resource %s',
		'please_wait' => 'Loading'
	),

);
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
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->basedir  = $this->openqrm->get('basedir');
		$this->tpldir   = $this->rootdir.'/plugins/novnc/tpl';
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/novnc/lang", 'novnc.ini');

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
			$this->action = "login";
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'login':
				$content[] = $this->login(true);
			break;
			case 'console':
				$content[] = $this->console(true);
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
		require_once($this->basedir.'/plugins/novnc/web/class/novnc.api.class.php');
		$controller = new novnc_api($this);
		$controller->action();
	}

	//--------------------------------------------
	/**
	 * Login appliance (resource)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function login( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->basedir.'/plugins/novnc/web/class/novnc.login.class.php');
			$controller = new novnc_login($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['login'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['login']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'login' );
		$content['onclick'] = false;
		if($this->action === 'login'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Open Console
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function console( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->basedir.'/plugins/novnc/web/class/novnc.console.class.php');
			$controller = new novnc_console($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->imgurl          = $this->imgurl;
			$controller->jsurl           = $this->jsurl;
			$controller->lang            = $this->lang['console'];
			$data = $controller->action();
		}
		$content['label']   = 'noVNC';
		$content['value']   = $data;
		if($data instanceof htmlobject_template || $data instanceof htmlobject_box) {
			$content['hidden']  = true;
		}
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'console' );
		$content['onclick'] = false;
		if($this->action === 'console'){
			$content['active']  = true;
		}
		return $content;
	}

}

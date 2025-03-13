<?php
/**
 * development Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

function myErrorHandler($code, $txt, $file, $line, $context)
{
	$text = $file." [".$line."] ".$txt ."\n";
	switch ($code) {
	case E_PARSE:
		echo '<b>E_PARSE:</b> '.$text;
		break;
	case E_ERROR:
		echo '<b>E_ERROR:</b> '.$text;
		break;
	case E_DEPRECATED:
		$GLOBALS['error_php'][] = '<div><b>E_DEPRECATED:</b> '.$text.'</div>';
		break;
	case E_STRICT:
		$GLOBALS['error_php'][] = '<div><b>E_STRICT:</b> '.$text.'</div>';
		break;
	case E_WARNING:
		$GLOBALS['error_php'][] = '<div><b>E_WARNING:</b> '.$text.'</div>';
		break;
	case E_NOTICE:
		$GLOBALS['error_php'][] = '<div><b>E_NOTICE:</b> '.$text.'</div>';
		break;
	default:
		$GLOBALS['error_php'][] = '<div><b>'.$code.':</b> '.$text.'</div>';
		break;
	}
	/* Don't execute PHP internal error handler */
	return true;
}



class development_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'development_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "development_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'development_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'development_identifier';
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
	'select' => array(
		'tab' => 'Development',
		'label' => 'Development',
		'id' => 'Name',
		'action_docblock' => 'docblock',
		'action_rest' => 'rest',
		'action_lang' => 'lang',
		'action_template' => 'templates',
		'action_api' => 'api',
		'action_hooks' => 'hooks',
		'action_js' => 'js',
		'action_css' => 'css',
		'please_wait' => 'Loading. Please wait ..',
	),
	'rest' => array(
		'tab' => 'Rest',
		'label_plugin' => 'View Plugin %s Rest',
		'label_base' => 'View %s Rest',
		'param_must_be_set_within_action' => 'must be set within action',
		'param_optional' => 'optional',
		'param_form_field' => 'form field',
		'param_required' => 'required form field',
		'param_validator' => 'validator',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
	),
	'api' => array(
		'tab' => 'Api',
		'label_plugin' => 'View Plugin %s Api',
		'label_base' => 'View %s Api',
		'param_must_be_set_within_action' => 'must be set within action',
		'param_optional' => 'optional',
		'param_form_field' => 'form field',
		'param_required' => 'required form field',
		'param_validator' => 'validator',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
	),
	'template' => array(
		'tab' => 'Template',
		'label_plugin' => 'View Plugin %s Templates',
		'label_base' => 'View %s Templates',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
	),
	'lang' => array(
		'tab' => 'Translation',
		'label_plugin' => 'View Plugin %s Translation',
		'label_base' => 'View %s Translation',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
	),
	'docblock' => array(
		'tab' => 'Docblock',
		'label_plugin' => 'View Plugin %s Docblock',
		'label_base' => 'View %s Docblock',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
	),
	'hooks' => array(
		'tab' => 'Hooks',
		'label_plugin' => 'View Plugin %s Hooks',
		'label_base' => 'View %s Hooks',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
		'appliance-hook' => 'The appliance-hook allows plugins to run custom action during start, stop and update of a server.',
		'appliance-edit-hook' => 'The appliance-edit hook provides plugins the capability to add an action item in the server edit section.',
		'appliance-link-hook' => 'The appliance-edit hook provides plugins the capability to add an action item in the server overview section.',
		'billing-hook' => 'The billing-hook allows to plugin custom external billing systems for the Cloud deployments.',
		'cloud-hook' => 'The cloud-hook let the virtualization plugins define the way how specific VMs are created and removed.',
		'dashboard-quicklink-hook' => 'The dashboard-quicklink-hook provides an option for plugins to add a icon and URL to the Quicklink setion on the openQRM dashboard.',
		'deployment-auth-hook/auth-hook' => 'The deployment-auth-hook provides the storage plugins which is responsible for the specific image deployment types of the server image to run custom action on the storage object to authenticate/de-authenticate the image volume.',
		'deployment-cloud-hook' => 'The deployment-cloud-hook let the storage plugins define a the way how the image volumes are created/removed/resized/privatized for the automated Cloud deployment.',
		'event-hook' => 'The event-hook is triggered when a specific event arrives in openQRM and allows plugins to run custom actions.',
		'external-dns-hook' => 'The external-dns-hook provides a way to interact with remote DNS services.',
		'ha-cmd-hook' => 'The ha-cmd-hook let the virtualization plugins define the way how specific VMs are created and fenced in case of a highavailability fail-over.',
		'monitor-hook' => 'The monitor-hook provides an option for plugins to run frequent repeated commands e.g. for status updates.',
		'remote-console-hook' => 'The remote-console-hook allows plugins to embed a remote console for specific Virtualization technologies.',
		'resource-hook' => 'The resource-hook allows plugins to run custom action during start, stop and restart of a server.',
		'resource-fence-hook' => 'The resource-fence-hook defines how resources are fenced (STONITH) during highavailability fail-over.',
		'resource-virtual-command-hook' => 'The resource-virtual-command-hook let the virtualization plugins define the way how specific VMs and Hosts are started and stopped through the Virtualization Host API.',
		'role-hook' => 'The role-hook provides plugins the capability to define custom role-based permissions for every action in openQRM.',
	),
	'js' => array(
		'tab' => 'JS',
		'label_plugin' => 'View Plugin %s JS',
		'label_base' => 'View %s JS',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
	),
	'css' => array(
		'tab' => 'CSS',
		'label_plugin' => 'View Plugin %s CSS',
		'label_base' => 'View %s CSS',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/development/lang", 'development.ini');
		$this->tpldir   = $this->rootdir.'/plugins/development/tpl';
		if($this->response->html->request()->get('debug') !== '' ) {
			$this->response->add('debug', $this->response->html->request()->get('debug'));
		}
		$alter_error_handler = set_error_handler("myErrorHandler");
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
			$this->action = 'select';
		}

		$content = array();
		switch( $this->action ) {
			case '':
			default:
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'rest':
				$content[] = $this->select(false);
				$content[] = $this->rest(true);
			break;
			case 'api':
				$content[] = $this->select(false);
				$content[] = $this->api(true);
			break;
			case 'docblock':
				$content[] = $this->select(false);
				$content[] = $this->docblock(true);
			break;
			case 'lang':
				$content[] = $this->select(false);
				$content[] = $this->lang(true);
			break;
			case 'template':
				$content[] = $this->select(false);
				$content[] = $this->template(true);
			break;
			case 'hooks':
				$content[] = $this->select(false);
				$content[] = $this->hooks(true);
			break;
			case 'js':
				$content[] = $this->select(false);
				$content[] = $this->js(true);
			break;
			case 'css':
				$content[] = $this->select(false);
				$content[] = $this->css(true);
			break;
			case 'download':
				$this->__download();
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
	 * Select
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/development/class/development.select.class.php');
			$controller = new development_select($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['select'];
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
	 * Rest
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function rest( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/development/class/development.rest.class.php');
			$controller = new development_rest($this->openqrm, $this->response, $this);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['rest'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['rest']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'rest' );
		$content['onclick'] = false;
		if($this->action === 'rest'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * API
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function api( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/development/class/development.api.class.php');
			$controller = new development_api($this->openqrm, $this->response, $this);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['api'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['api']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'api' );
		$content['onclick'] = false;
		if($this->action === 'api'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Template
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function template( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/development/class/development.template.class.php');
			$controller = new development_template($this->openqrm, $this->response, $this);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['template'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['template']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'template' );
		$content['onclick'] = false;
		if($this->action === 'template'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Lang
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function lang( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/development/class/development.lang.class.php');
			$controller = new development_lang($this->openqrm, $this->response, $this);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['lang'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['lang']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'lang' );
		$content['onclick'] = false;
		if($this->action === 'lang'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Docblock
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function docblock( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/development/class/development.docblock.class.php');
			$controller = new development_docblock($this->openqrm, $this->response, $this);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['docblock'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['docblock']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'docblock' );
		$content['onclick'] = false;
		if($this->action === 'docblock'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Hooks
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function hooks( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/development/class/development.hooks.class.php');
			$controller = new development_hooks($this->openqrm, $this->response, $this);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['hooks'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['hooks']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'hooks' );
		$content['onclick'] = false;
		if($this->action === 'hooks'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * JS
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function js( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/development/class/development.js.class.php');
			$controller = new development_js($this->openqrm, $this->response, $this);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['js'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['js']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'js' );
		$content['onclick'] = false;
		if($this->action === 'js'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * CSS
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function css( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/development/class/development.css.class.php');
			$controller = new development_css($this->openqrm, $this->response, $this);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['css'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['css']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'css' );
		$content['onclick'] = false;
		if($this->action === 'css'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Download File
	 *
	 * @access public
	 * @param string $target
	 * @param string $mime
	 */
	//--------------------------------------------
	function __download() {
		$file = $this->response->html->request()->get('file');
		$file = str_replace('../', '', $file);

		if($this->response->html->request()->get('plugin_name') !== '') {
			$dir  = $this->response->html->request()->get('plugin_name');
			$path = $this->openqrm->get('basedir').'/plugins/'.$dir.'/web/'.$file;
			$mime = $this->response->html->request()->get('mime');
		}
		else if($this->response->html->request()->get('base_name') !== '') {
			if($this->response->html->request()->get('base_name') !== 'plugins') {
				$dir  = $this->response->html->request()->get('base_name');
				$path = $this->openqrm->get('basedir').'/web/base/server/'.$dir.'/'.$file;
				$mime = $this->response->html->request()->get('mime');
			}
			elseif ($this->response->html->request()->get('base_name') === 'plugins') {
				$dir  = 'aa_plugins';
				$path = $this->openqrm->get('basedir').'/web/base/plugins/aa_plugins/'.$file;
				$mime = $this->response->html->request()->get('mime');
			}
		}

		if (!headers_sent()) {
			$filename = str_replace(' ', '_', $file);
			ini_set('zlib.output_compression','Off');
			header("Pragma: public");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: must-revalidate");
			header("Content-type: ".$mime."");
			header("Content-Length: ".filesize($path));
			header("Content-disposition: inline; filename=$filename");
			header("Accept-Ranges: ".filesize($path)); 
			readfile($path);
			exit();
		} else {
			echo $path;
		}
	}

	//--------------------------------------------
	/**
	 * Previous and next
	 *
	 * @access private
	 * @param object $template
	 * @param string $mode
	 * @return object
	 */
	//--------------------------------------------
	function __get_navi($template, $mode) {
		$response = $this->response;
		if($this->response->html->request()->get('plugin_name') !== '') {
			$plugin = new plugin();
			$plugins = $plugin->available();
			$key = array_keys($plugins, $this->response->html->request()->get('plugin_name'));
			$key = $key[0];
			$param = 'plugin_name';
		}
		if($this->response->html->request()->get('base_name') !== '') {
			$plugins = array('aa_server', 'appliance', 'event', 'image','kernel', 'plugins', 'resource','storage');
			$key = array_keys($plugins, $this->response->html->request()->get('base_name'));
			$key = $key[0];
			$param = 'base_name';
		}
		$prev = '&#160';
		$next = '&#160';
		if($key === 0) {
			$next = $response->html->a();
			$next->label = $plugins[1].' &gt;&gt;';
			$next->href = $this->response->get_url($this->actions_name, $mode ).'&'.$param.'='.$plugins[1];
			$next->handler = 'onclick="wait();"';
		}		
		else if($key !== 0 && $key < count($plugins)-1) {
			$prev = $response->html->a();
			$prev->label = '&lt;&lt; '.$plugins[$key-1];
			$prev->href = $this->response->get_url($this->actions_name, $mode ).'&'.$param.'='.$plugins[$key-1];
			$prev->handler = 'onclick="wait();"';
			$next = $response->html->a();
			$next->label = $plugins[$key+1].' &gt;&gt;';
			$next->href = $this->response->get_url($this->actions_name, $mode ).'&'.$param.'='.$plugins[$key+1];
			$next->handler = 'onclick="wait();"';
		}
		else if($key === count($plugins)-1) {
			$prev = $response->html->a();
			$prev->label =  '&lt;&lt; '.$plugins[$key-1];
			$prev->href = $this->response->get_url($this->actions_name, $mode ).'&'.$param.'='.$plugins[$key-1];
			$prev->style = "text-decoration: none;";
			$prev->handler = 'onclick="wait();"';
		}
		$switch = '';
		if($mode !== 'docblock') {
			$a = $response->html->a();
			$a->label = 'docblock';
			$a->href = $this->response->get_url($this->actions_name, 'docblock' );
			$a->handler = 'onclick="wait();"';
			$switch .= $a->get_string();
		}
		if($mode !== 'lang') {
			$a = $response->html->a();
			$a->label = 'lang';
			$a->href = $this->response->get_url($this->actions_name, 'lang' );
			$a->handler = 'onclick="wait();"';
			$switch .= $a->get_string();
		}	
		if($mode !== 'rest') {
			$a = $response->html->a();
			$a->label = 'rest';
			$a->href = $this->response->get_url($this->actions_name, 'rest' );
			$a->handler = 'onclick="wait();"';
			$switch .= $a->get_string();
		}
		if($mode !== 'api') {
			$a = $response->html->a();
			$a->label = 'api';
			$a->href = $this->response->get_url($this->actions_name, 'api' );
			$a->handler = 'onclick="wait();"';
			$switch .= $a->get_string();
		}
		if($mode !== 'template') {
			$a = $response->html->a();
			$a->label = 'templates';
			$a->href = $this->response->get_url($this->actions_name, 'template' );
			$a->handler = 'onclick="wait();"';
			$switch .= $a->get_string();
		}
		if($mode !== 'hooks') {
			$a = $response->html->a();
			$a->label = 'hooks';
			$a->href = $this->response->get_url($this->actions_name, 'hooks' );
			$a->handler = 'onclick="wait();"';
			$switch .= $a->get_string();
		}
		if($mode !== 'js') {
			$a = $response->html->a();
			$a->label = 'js';
			$a->href = $this->response->get_url($this->actions_name, 'js' );
			$a->handler = 'onclick="wait();"';
			$switch .= $a->get_string();
		}
		if($mode !== 'css') {
			$a = $response->html->a();
			$a->label = 'css';
			$a->href = $this->response->get_url($this->actions_name, 'css' );
			$a->handler = 'onclick="wait();"';
			$switch .= $a->get_string();
		}

		$template->add($switch, 'switch');
		$template->add($prev, 'previous');
		$template->add($next, 'next');
		return $template;
	}

}

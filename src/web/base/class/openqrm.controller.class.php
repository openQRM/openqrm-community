<?php
/**
 * Openqrm Controller
 *
 * openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.
 *
 * All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.
 *
 * This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
 * The latest version of this license can be found here: src/doc/LICENSE.txt
 *
 * By using this software, you acknowledge having read this license and agree to be bound thereby.
 *
 * http://openqrm-enterprise.com
 *
 * Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class openqrm_controller
{
	/**
	 * translation
	 * @access public
	 * @var array
	 */
	var $lang = array(
		'content' => array(
			'tab' => 'License',
			'ltimeout' => 'Your openQRM license has expired!',
			'lclients' => 'Your openQRM client license has expired!',
		),
		'top' => array(
			'account' => 'Account',
			'support' => 'Support',
			'info' => 'Info',
			'documentation' => 'Documentation',
			'language' => 'Language',
		),
		'upload' => array(
			'tab' => 'Upload',
			'label' => 'Upload License File(s)',
			'public_key' => 'Public Key',
			'server_license' => 'Server License',
			'client_license' => 'Client Licenses (optional)',
			'welcome' => 'Welcome to your newly installed openQRM Enterprise Edition',
			'explanation' => 'Please activate it by uploading the license key files you received by email.',
			'msg' => 'Uploaded License File %s',
		),
	);
	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 */
	//--------------------------------------------
	function __construct()
	{
		
		// handle timezone needed since php 5.3
		if (function_exists('ini_get')) {
			if (ini_get('date.timezone') === '') {
				date_default_timezone_set('Europe/Berlin');
			}
		}
		
		$this->rootdir = $_SERVER["DOCUMENT_ROOT"] . '/openqrm/base';
		$this->tpldir = $this->rootdir . '/tpl';
		
		require_once($this->rootdir . '/class/file.handler.class.php');
		$file = new file_handler();
		
		require_once($this->rootdir . '/class/htmlobjects/htmlobject.class.php');
		require_once($this->rootdir . '/class/openqrm.htmlobjects.class.php');
		$html = new openqrm_htmlobject();
		
		// if openQRM is unconfigured, set openqrm empty
		if ($file->exists($this->rootdir . '/unconfigured')) {
			$this->openqrm = '';
			$this->webdir = $this->rootdir;
			$this->baseurl = $html->thisurl;
		} else {
			require_once($this->rootdir . '/class/user.class.php');
			$user = new user($_SERVER['PHP_AUTH_USER']);
			$user->set_user();
			
			require_once($this->rootdir . '/class/openqrm.class.php');
			$this->openqrm = new openqrm($file, $user, $html->response());
			$this->webdir = $this->openqrm->get('webdir');
			$this->baseurl = $this->openqrm->get('baseurl');
			
			// only translate if openqrm is not empty (configure mode)
			if ($this->openqrm !== '') {
				$html->lang = $user->translate($html->lang, $this->rootdir . "/lang", 'htmlobjects.ini');
				$file->lang = $user->translate($file->lang, $this->rootdir . "/lang", 'file.handler.ini');
				$this->lang = $user->translate($this->lang, $this->rootdir . "/lang", 'openqrm.controller.ini');
			}
			
		}
		
		$request = $html->request();
		
		require_once $this->rootdir . '/include/requestfilter.inc.php';
		$request->filter = $requestfilter;
		
		$this->response = $html->response();
		$this->request = $this->response->html->request();
		$this->file = $file;
	}
	
	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action()
	{
		
		$ti = microtime(true);
		
		// get js translation file;
		// EN file serves master key for language labels
		$file = $this->rootdir . '/lang/en.javascript.ini'; // 'en.' prefix is needed and must not be renamed
		$jstranslation = '';
		if ($this->file->exists($file)) {
			$lang = $this->file->get_ini($file);
			// only translate if openqrm is not empty (configure mode)
			if ($this->openqrm !== '') {
				$lang = $this->openqrm->user()->translate($lang, $this->rootdir . "/lang", 'javascript.ini');
			}
			$jstranslation = '<script type="text/javascript">' . "\n";
			$jstranslation .= '//<![CDATA[' . "\n";
			$jstranslation .= 'var jstranslation = {' . "\n";
			$i = 0;
			foreach ($lang as $key => $value) {
				$jstranslation .= $key . ': "' . $value . "\"";    // build js array
				if ($i < count($lang) - 1) {
					$jstranslation .= ",\n";
				}
				$i++;
			}
			$jstranslation .= "\n" . '};' . "\n";
			$jstranslation .= '//]]>' . "\n";
			$jstranslation .= '</script>' . "\n";
		}
		
		// handle scripts and stylesheets
		$style = '';
		$script = '';
		$basetarget = '<base target="MainFrame"></base>';
		
		if ($this->request->get('plugin') !== '') {
			$plugin = $this->request->get('plugin');
			$style = $this->__renderAssetInclude('/plugins/' . $plugin . '/css/', 'css');
			$script = $this->__renderAssetInclude('/plugins/' . $plugin . '/js/', 'js');
			$basetarget = '';
			$this->response->add('plugin', $plugin);
			if ($this->request->get('controller') !== '') {
				$this->response->add('controller', $this->request->get('controller'));
			}
		} else if ($this->request->get('base') !== '') {
			$plugin = $this->request->get('base');
			$style = $this->__renderAssetInclude('/server/' . $plugin . '/css/', 'css');
			$script = $this->__renderAssetInclude('/server/' . $plugin . '/js/', 'js');
			$basetarget = '';
			$this->response->params['base'] = $plugin;
			if ($this->request->get('controller') !== '') {
				$this->response->params['controller'] = $this->request->get('controller');
			}
		} else {
			$plugin = 'aa_server';
			$style = $this->__renderAssetInclude('/server/' . $plugin . '/css/', 'css');
			$script = $this->__renderAssetInclude('/server/' . $plugin . '/js/', 'js');
			$basetarget = '';
		}
		
		$t = $this->response->html->template($this->tpldir . '/index.tpl.php');
		
		// Configure switch
		if ($this->file->exists($this->webdir . '/unconfigured')) {
			$t->add('', "lang");
			$t->add('<h1 id="setup_h1">Setup</h1>', "menu");
			$content = $this->configure();
		} else if ($this->response->html->request()->get('upload') !== '') {
			$t->add($this->openqrm->user()->lang, "lang");
			$t->add($this->menu(), "menu");
			$content = $this->upload();
		} else {
			$t->add($this->openqrm->user()->lang, "lang");
			$t->add($this->menu(), "menu");
			$content = $this->content();
			// handle scripts and styles of sub loaded objects (tab in tab)
			if ($content->__elements['content'] instanceof htmlobject_tabmenu) {
				$tabs = $this->__renderTabs($content->__elements['content']);
				isset($tabs['script']) ? $script .= $tabs['script'] : null;
				isset($tabs['style']) ? $style .= $tabs['style'] : null;
			}
		}
		
		$t->add($content, "content");
		$t->add($this->baseurl, "baseurl");
		$t->add($basetarget, "basetarget");
		$t->add($jstranslation, "jstranslation");
		$t->add($style, "style");
		$t->add($script, "script");
		$t->add(date('Y'), "currentyear");
		$t->add($this->top(), "top");
		
		$memory = '';
		if (function_exists('memory_get_peak_usage')) {
			$memory = memory_get_peak_usage(false);
		}
		$t->add('Memory: ' . $memory . ' bytes', 'memory');
		$ti = (microtime(true) - $ti);
		$t->add('Time: ' . $ti . ' sec', 'time');
		
		return $t;
	}
	
	//--------------------------------------------
	/**
	 * Api
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function api()
	{
		if ($this->file->exists($this->rootdir . '/unconfigured')) {
			return '';
		} else {
			require_once($this->rootdir . '/class/openqrm.api.class.php');
			$controller = new openqrm_api($this);
			$controller->action();
		}
	}
	
	//--------------------------------------------
	/**
	 * CLI
	 *
	 * @access public
	 * @param string $argv console parameters
	 */
	//--------------------------------------------
	function cli($argv)
	{
		if ($this->file->exists($this->rootdir . '/unconfigured')) {
			return '';
		} else {
			require_once($this->rootdir . '/class/openqrm.cli.class.php');
			$controller = new openqrm_cli($this, $argv);
			$controller->action();
		}
	}
	
	//--------------------------------------------
	/**
	 * Build Top of page
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function top()
	{
		require_once($this->rootdir . '/class/openqrm.top.class.php');
		$controller = new openqrm_top($this->response, $this->file, $this->openqrm);
		$controller->tpldir = $this->tpldir;
		$controller->lang = $this->lang['top'];
		return $controller->action();
	}
	
	//--------------------------------------------
	/**
	 * Build menu
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function menu()
	{
		require_once($this->rootdir . '/class/openqrm.menu.class.php');
		$controller = new openqrm_menu($this->response, $this->file, $this->openqrm->user());
		$controller->tpldir = $this->tpldir;
		return $controller->action();
	}
	
	//--------------------------------------------
	/**
	 * Handle content
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function content()
	{
		require_once($this->rootdir . '/class/openqrm.content.class.php');
		$controller = new openqrm_content($this->response, $this->file, $this->openqrm->user(), $this->openqrm);
		$controller->tpldir = $this->tpldir;
		$controller->rootdir = $this->rootdir;
		$controller->lang = $this->lang['content'];
		$data = $controller->action();
		return $data;
	}
	
	//--------------------------------------------
	/**
	 * Configure openQRM
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function configure()
	{
		require_once($this->rootdir . '/class/openqrm.configure.class.php');
		$controller = new openqrm_configure($this->response, $this->file);
		$controller->tpldir = $this->tpldir;
		return $controller->action();
	}
	
	//--------------------------------------------
	/**
	 * Upload
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function upload()
	{
		require_once($this->rootdir . '/class/openqrm.upload.class.php');
		$controller = new openqrm_upload($this->response, $this->openqrm);
		$controller->tpldir = $this->tpldir;
		$controller->lang = $this->lang['upload'];
		return $controller->action();
	}
	
	//--------------------------------------------
	/**
	 * Render js/css include strings
	 *
	 * @access protected
	 * @param $path path to css/js dir
	 * @param $mode enum [css|js]
	 * @return string
	 */
	//--------------------------------------------
	protected function __renderAssetInclude($path, $mode)
	{
		$str = '';
		$files = $this->file->get_files($this->webdir . $path, '', '*.' . $mode);
		foreach ($files as $file) {
			if ($mode === 'css') {
				$str .= '<link rel="stylesheet" href="' . $this->baseurl . $path . $file['name'] . '" type="text/css">' . "\n";
			} else if ($mode === 'js') {
				$str .= '<script src="' . $this->baseurl . $path . $file['name'] . '" type="text/javascript"></script>' . "\n";
			}
		}
		return $str;
	}
	
	//--------------------------------------------
	/**
	 * Read pluginroot attrib (tab in tab)
	 *
	 * @access protected
	 * @param htmloject_tabmenu $obj
	 * @return array
	 */
	//--------------------------------------------
	protected function __renderTabs($obj)
	{
		$return['script'] = '';
		$return['style'] = '';
		foreach ($obj->__data as $a) {
			if (
				isset($a['active']) &&
				$a['active'] === true &&
				isset($a['value']) &&
				$a['value'] instanceof htmlobject_tabmenu
			) {
				if (isset($a['value']->pluginroot)) {
					$return['style'] .= $this->__renderAssetInclude($a['value']->pluginroot . '/css/', 'css');
					$return['script'] .= $this->__renderAssetInclude($a['value']->pluginroot . '/js/', 'js');
				}
				$tabs = $this->__renderTabs($a['value']);
				isset($tabs['script']) ? $return['script'] .= $tabs['script'] : null;
				isset($tabs['style']) ? $return['style'] .= $tabs['style'] : null;
				break;
			}
		}
		return $return;
	}
	
	
}

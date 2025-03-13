<?php
/**
 * Openqrm Content
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

class openqrm_content
{
	
	var $pluginkey;
	/**
	 * absolute path to template dir
	 * @access public
	 * @var string
	 */
	var $tpldir;
	/**
	 * absolute path to webroot
	 * @access public
	 * @var string
	 */
	var $rootdir;
	/**
	 * translation
	 * @access public
	 * @var array
	 */
	var $lang;
	
	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param file $file
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($response, $file, $user, $openqrm)
	{
		$this->response = $response;
		$this->openqrm = $openqrm;
		$this->file = $file;
		$this->user = $user;
		$this->request = $this->response->html->request();
	}
	
	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action()
	{
		$t = $this->response->html->template($this->tpldir . '/index_content.tpl.php');
		$t->add($this->content(), 'content');
		$t->add($this->pluginkey, 'contentclass'); // needs to run after $this->content()
		return $t;
	}
	
	//--------------------------------------------
	/**
	 * Build content
	 *
	 * @access public
	 * @return htmlobject_tabmenu | string
	 */
	//--------------------------------------------
	function content()
	{
		
		if (isset($lc)) {
			return $lc;
		}
		if ($this->request->get('iframe') !== '') {
			$this->pluginkey = 'iframe';
			$iframe = parse_url($this->request->get('iframe'), PHP_URL_PATH);
			$str = '<iframe name="MainFrame" id="MainFrame" src="' . $iframe . '" scrolling="auto" height="1" frameborder="0"></iframe>';
			$name = $this->request->get('name');
			if ($name === '') {
				$name = 'Iframe';
			}
			// assign params to response
			$this->response->add('iframe', $this->request->get('iframe'));
			$this->response->add('name', $this->request->get('name'));
			
			// tabs
			$content[] = array(
				'label' => $name,
				'value' => $str,
				'target' => $this->response->html->thisfile,
				'request' => $this->response->get_array(),
				'onclick' => false,
				'hidden' => false,
			);
			$tab = $this->response->html->tabmenu('iframe_tab');
			$tab->message_param = 'noop';
			$tab->auto_tab = false;
			$tab->css = 'htmlobject_tabs';
			$tab->add($content);
			return $tab;
		} else if ($this->request->get('base') !== '') {
			$plugin = $this->request->get('base');
			$this->pluginkey = $plugin;
			$name = $plugin;
			$class = $plugin;
			if ($this->request->get('controller') !== '') {
				$class = $this->request->get('controller');
				$name = $class;
			}
			$class = str_replace('-', '_', $class) . '_controller';
			$path = $this->rootdir . '/server/' . $plugin . '/class/' . $name . '.controller.class.php';
			$role = $this->openqrm->role($this->response);
			$data = $role->get_plugin($class, $path);
			return $data;
		} else if ($this->request->get('plugin') !== '') {
			$plugin = $this->request->get('plugin');
			$this->pluginkey = $plugin;
			$name = $plugin;
			$class = $plugin;
			if ($this->request->get('controller') !== '') {
				$class = $this->request->get('controller');
				$name = $class;
			}
			$class = str_replace('-', '_', $class) . '_controller';
			$path = $this->rootdir . '/plugins/' . $plugin . '/class/' . $name . '.controller.class.php';
			if ($this->file->exists($path)) {
				$role = $this->openqrm->role($this->response);
				$data = $role->get_plugin($class, $path);
				return $data;
			} else {
				// handle plugins not oop
				$path = $this->rootdir . '/plugins/' . $plugin . '/' . $name . '-manager.php';
				if ($this->file->exists($path)) {
					$params = '';
					foreach ($_REQUEST as $k => $v) {
						if (is_string($v)) {
							$params .= '&' . $k . '=' . $v;
						}
						if (is_array($v)) {
							foreach ($v as $key => $value) {
								$params .= '&' . $k . '[' . $key . ']' . '=' . $value;
							}
						}
					}
					$str = '<iframe name="MainFrame" id="MainFrame" src="plugins/' . $plugin . '/' . $name . '-manager.php?' . $params . '" scrolling="auto" height="1" frameborder="0"></iframe>';
					return $str;
				} else {
					$role = $this->openqrm->role($this->response);
					$data = $role->get_plugin($class, $path);
					return $data;
				}
			}
		} else {

			#exit quickly when queries hit an unconfigured openQRM
			require_once($this->rootdir . '/class/file.handler.class.php');
			$file = new file_handler();
			
			if ($file->exists($this->rootdir . '/unconfigured')) {
				return '';
			}
			else{
				// default page - datacenter overview
				$this->pluginkey = 'aa_server';
				$path = $this->rootdir . '/server/' . $this->pluginkey . '/class/datacenter.controller.class.php';
				$this->openqrm->init();
				require_once($path);
				$controller = new datacenter_controller($this->openqrm, $this->response);
				$data = $controller->action();
				return $data;
			}
			
		}
	}
	
	//--------------------------------------------
	/**
	 * lcc
	 *
	 * @access public
	 * @return null
	 */
	//--------------------------------------------
}
?>

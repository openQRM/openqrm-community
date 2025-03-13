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

class openqrm_cli
{
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
	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param openqrm_controller $controller
	 * @param string $argv console parameters
	 */
	//--------------------------------------------
	function __construct($controller, $argv)
	{
		$this->controller = $controller;
		$this->response = $this->controller->response;
		$this->openqrm = $this->controller->openqrm;
		$this->file = $this->controller->openqrm->file();
		$this->user = $this->controller->openqrm->user();
		
		$this->openqrm->init();
		
		// set request params
		foreach ($argv as $k => $v) {
			if ($k !== 0) {
				$tmp = explode('=', $v);
				if (isset($tmp[0]) && isset($tmp[1])) {
					if (strpos($tmp[0], '[]') !== false) {
						$tmp[0] = str_replace('[]', '', $tmp[0]);
						$_REQUEST[$tmp[0]][] = $tmp[1];
					} else {
						$_REQUEST[$tmp[0]] = $tmp[1];
					}
				}
			}
		}
		#print_r($_REQUEST);
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
		$action = $this->response->html->request()->get('action');
		switch ($action) {
			case 'plugin':
				$this->plugin();
				break;
			case 'base':
				$this->base();
				break;
		}
	}
	
	//--------------------------------------------
	/**
	 * Load plugins
	 *
	 * @access public
	 */
	//--------------------------------------------
	function plugin()
	{
		$plugin = $this->response->html->request()->get('plugin');
		$name = $plugin;
		$class = $plugin;
		if ($this->response->html->request()->get('controller') !== '') {
			$class = $this->response->html->request()->get('controller');
			$name = $class;
		}
		$class = str_replace('-', '_', $class) . '_controller';
		$path = $this->controller->rootdir . '/plugins/' . $plugin . '/class/' . $name . '.controller.class.php';
		if ($this->file->exists($path)) {
			require_once($path);
			$controller = new $class($this->openqrm, $this->response);
			if (method_exists($controller, 'cli')) {
				$controller->cli();
			}
		}
	}
	
	//--------------------------------------------
	/**
	 * Load Base
	 *
	 * @access public
	 */
	//--------------------------------------------
	function base()
	{
		$plugin = $this->response->html->request()->get('base');
		$name = $plugin;
		$class = $plugin;
		if ($this->response->html->request()->get('controller') !== '') {
			$class = $this->response->html->request()->get('controller');
			$name = $class;
		}
		$class = str_replace('-', '_', $class) . '_controller';
		$path = $this->controller->rootdir . '/server/' . $plugin . '/class/' . $name . '.controller.class.php';
		if ($this->file->exists($path)) {
			require_once($path);
			$controller = new $class($this->openqrm, $this->response);
			if (method_exists($controller, 'cli')) {
				$controller->cli();
			}
		}
	}
	
}

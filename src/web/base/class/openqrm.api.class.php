<?php
/**
 * Openqrm Content
 *
 * openQRM Community developed by OPENQRM AUSTRALIA PTY LTD.
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

class openqrm_api
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
	 * @param openqrm_controller
	 */
	//--------------------------------------------
	function __construct($controller)
	{
		$this->controller = $controller;
		$this->response = $this->controller->response;
		$this->openqrm = $this->controller->openqrm;
		$this->file = $this->controller->openqrm->file();
		$this->user = $this->controller->openqrm->user();
		
		$this->openqrm->init();
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
			case 'get_event_status':
				$this->get_event_status();
				break;
			case 'get_queue_status':
				$this->get_queue_status();
				break;
			case 'get_info_box':
				$this->get_info_box();
				break;
			case 'set_language':
				$this->set_language();
				break;
			case 'plugin':
				$this->plugin();
				break;
			case 'base':
				$this->base();
				break;
			case 'lock':
				$this->lock();
				break;
		}
	}
	
	//--------------------------------------------
	/**
	 * Get values for top status
	 *
	 * @access public
	 */
	//--------------------------------------------
	function get_event_status()
	{
		$appliance = new appliance();
		$appliance_all = $appliance->get_count();
		$appliance_active = $appliance->get_count_active();
		
		$resource = new resource();
		$resource_all = $resource->get_count("all");
		$resource_active = $resource->get_count("online");
		$resource_inactive = $resource->get_count("offline");
		$resource_error = $resource->get_count("error");
		
		$event = new event();
		$event_error_count = $event->get_count('error');
		$event_active_count = $event->get_count('active');
		echo $appliance_all . "@" . $appliance_active . "@" . $resource_all . "@" . $resource_active . "@" . $resource_inactive . "@" . $resource_error . "@" . $event_error_count . "@" . $event_active_count;
	}
	
	//--------------------------------------------
	/**
	 * Get values from queue
	 *
	 * @access public
	 */
	//--------------------------------------------
	function get_queue_status()
	{
		$running = array();
		$content = '';
		$path = $this->openqrm->get('basedir') . '/var/spool/';
		$files = $this->file->get_files($path);
		$waiting = count($files);
		if ($waiting > 0) {
			$i = 0;
			foreach ($files as $file) {
				if (stripos($file['name'], 'ouput') !== false) {
					$running[$i]['name'] = str_replace('ouput.', '', $file['name']);
					$running[$i]['start'] = filemtime($file['path']);
					$waiting = $waiting - 2;
					$i++;
				}
			}
			if (count($running) > 0) {
				foreach ($running as $key) {
					$time = time() - $key['start'];
					$content .= 'Running since <b>' . $time . '</b> seconds<br>' . $this->file->get_contents($path . '/openqrm-queue.' . $key['name']) . '<br>';
				}
			}
		}
		// unset data if user is not admin
		if (!$this->openqrm->user()->isAdmin()) {
			$content = '';
			$running = array();
		}
		echo $waiting . ";;" . count($running) . ';;' . $content;
	}
	
	//--------------------------------------------
	/**
	 * Set language
	 *
	 * @access public
	 */
	//--------------------------------------------
	function set_language()
	{
		$name = $this->response->html->request()->get('user');
		$lang = $this->response->html->request()->get('lang');
		$user = new user($name);
		$user->set_user_language($name, $lang);
	}
	
	
	//--------------------------------------------
	/**
	 * Get values for info box
	 *
	 * @access public
	 */
	//--------------------------------------------
	function get_info_box()
	{
		$now = $_SERVER['REQUEST_TIME'];
		if ($this->openqrm->l[3] == 0) {
			$valid = 'unlimited';
		} else {
			$valid = date("F j, Y", $this->openqrm->l[3]);
		}
		$bd = $this->openqrm->get('baseurl');
		$green = '<img src="' . $bd . '/img/active_small.png" alt="Active" title="Active">';
		$red = '<img src="' . $bd . '/img/error_small.png" alt="Expired" title="Expired">';
		$yellow = '<img src="' . $bd . '/img/transition_small.png" alt="Expiring soon" title="Expiring soon">';
		$icon = $red;
		$ex = '';
		echo '<p class="justify">';
		echo "openQRM Community developed by OPENQRM AUSTRALIA PTY LTD.<br>
			All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise. This source code is released under the GNU General Public License version 2	unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
			The latest version of this license can be found at <a href='src/doc/LICENSE.txt' TARGET='_BLANK'>src/doc/LICENSE.txt</a>. By using this software, you acknowledge having read this license and agree to be bound thereby.<br>";
		echo "</p>";
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
			if (method_exists($controller, 'api')) {
				$controller->api();
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
			if (method_exists($controller, 'api')) {
				$controller->api();
			}
		}
	}
	
	
	
	//--------------------------------------------
	/**
	 * global lock
	 *
	 * @access public
	 */
	//--------------------------------------------
	function lock()
	{
		require_once($this->controller->rootdir . '/class/lock.class.php');
		require_once($this->controller->rootdir . '/class/event.class.php');
		$event = new event();
		
		$lock_cmd = $this->response->html->request()->get('lock');
		$resource_id = $this->response->html->request()->get('resource_id');
		$section = $this->response->html->request()->get('section');
		if ((!strlen($lock_cmd)) || (!strlen($resource_id)) || (!strlen($section))) {
			$event->log("lock", $_SERVER['REQUEST_TIME'], 2, "openqrm.api.class.php", "Got empty paramater for lock, section or resource_id!", "", "", 0, 0, 0);
			return;
		}
		$lock = new lock();
		switch ($lock_cmd) {
			case 'aquire':
				$description = $this->response->html->request()->get('description');
				$token = $this->response->html->request()->get('token');
				$lock_fields['lock_resource_id'] = $resource_id;
				$lock_fields['lock_section'] = $section;
				$lock_fields['lock_description'] = $description;
				$lock_fields['lock_token'] = $token;
				$lock_id = $lock->add($lock_fields);
				if (strlen($lock_id)) {
					echo $lock_id;
					$event->log("lock", $_SERVER['REQUEST_TIME'], 5, "openqrm.api.class.php", "Section " . $section . " is now locked by " . $resource_id . "!", "", "", 0, 0, 0);
				} else {
					$event->log("lock", $_SERVER['REQUEST_TIME'], 2, "openqrm.api.class.php", "Section " . $section . " is still locked!", "", "", 0, 0, 0);
				}
				break;
			
			case 'release':
				$lock->get_instance_by_section($section);
				if (!strlen($lock->id)) {
					$event->log("lock", $_SERVER['REQUEST_TIME'], 2, "openqrm.api.class.php", "Resource " . $resource_id . " trying to remove lock but no lock active for section " . $section, "", "", 0, 0, 0);
					return;
				}
				if ($resource_id == $lock->resource_id) {
					$event->log("lock", $_SERVER['REQUEST_TIME'], 5, "openqrm.api.class.php", "Resource " . $resource_id . " released lock for section " . $section, "", "", 0, 0, 0);
					echo $lock->id;
					$lock->remove_by_section($section);
				} else {
					$event->log("lock", $_SERVER['REQUEST_TIME'], 2, "openqrm.api.class.php", "Resource " . $resource_id . " trying to remove lock from " . $lock->resource_id . " for section " . $section, "", "", 0, 0, 0);
				}
				
				break;
		}
	}
	
	
}

?>

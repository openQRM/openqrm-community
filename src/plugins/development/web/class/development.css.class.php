<?php
/**
 * Development CSS
 *
	openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

	All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

	This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
	The latest version of this license can be found here: src/doc/LICENSE.txt

	By using this software, you acknowledge having read this license and agree to be bound thereby.

				http://openqrm-enterprise.com

	Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */


class development_css
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
var $lang;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param openqrm $openqrm
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($openqrm, $response, $controller) {

		$this->openqrm  = $openqrm;
		$this->controller = $controller;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('basedir');
		$this->tpldir   = $this->rootdir.'/plugins/development/tpl';
		$this->response = $response;
		$this->file     = $this->openqrm->file();

		$plugin = $this->response->html->request()->get('plugin_name');
		$this->response->add('plugin_name', $plugin);
		$base = $this->response->html->request()->get('base_name');
		$this->response->add('base_name', $base);
		$this->methods = array('api', 'action');
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
		if($this->response->html->request()->get('plugin_name') !== '') {
			$name = $this->response->html->request()->get('plugin_name');
			$data['label'] = sprintf($this->lang['label_plugin'], $name);
		}
		else if ($this->response->html->request()->get('base_name') !== '') {
			$name = $this->response->html->request()->get('base_name');
			$data['label'] = sprintf($this->lang['label_base'], $name);
		}
		$vars = array_merge(
			$data, 
			array(
				'thisfile' => $this->response->html->thisfile,
		));
		$t = $this->response->html->template($this->tpldir.'/development-css.tpl.php');
		$t->add($vars);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');

		return $this->edit($t);

	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function edit($template) {
		if($this->response->html->request()->get('plugin_name') !== '') {
			$path = $this->rootdir.'/plugins/'.$this->response->html->request()->get('plugin_name').'/web/css/';
		}
		if($this->response->html->request()->get('base_name') !== '') {
			if($this->response->html->request()->get('base_name') !== 'plugins') {
				$path = $this->rootdir.'/web/base/server/'.$this->response->html->request()->get('base_name').'/css/';
			}
			elseif ($this->response->html->request()->get('base_name') === 'plugins') {
				$path = $this->rootdir.'/web/base/plugins/aa_plugins/css/';
			}
		}

		$files = $this->file->get_files($path, '', '*.css');
		$names = array();
		if(is_array($files)) {
			foreach($files as $file) {
				if(strripos($file['name'], '.css') !== false) {
					$names[] = $file['path'];
				}
			}
			$links = '';
			foreach($names as $path) {
				$links .= '<h3>'.basename($path).'</h3>';
				$links .= '<pre>'.$this->file->get_contents($path).'</pre>';
			}
			if($links === '') {
				$links = '<div>Plugin has no css</div>';
			}
			$template->add($links, 'links');
		} 
		$template = $this->controller->__get_navi($template, 'css');
		return $template;
	}


}

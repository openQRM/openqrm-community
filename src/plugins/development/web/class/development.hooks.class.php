<?php
/**
 * Development Hooks
 *
	openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

	All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

	This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
	The latest version of this license can be found here: src/doc/LICENSE.txt

	By using this software, you acknowledge having read this license and agree to be bound thereby.

				http://openqrm-enterprise.com

	Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */


class development_hooks
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
		$t = $this->response->html->template($this->tpldir.'/development-hooks.tpl.php');
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
			$plugin_name = $this->response->html->request()->get('plugin_name');
			$path = $this->rootdir.'/plugins/'.$plugin_name.'/web/';
		}
		if($this->response->html->request()->get('base_name') !== '') {
			$plugin_name = $this->response->html->request()->get('base_name');
			if($plugin_name !== 'plugins') {
				$path = $this->rootdir.'/web/base/server/'.$plugin_name;
			}
			elseif ($plugin_name === 'plugins') {
				$path = $this->rootdir.'/web/base/plugins/aa_plugins/';
			}
		}

		$files = $this->file->get_files($path);
		$names = array();
		if(is_array($files)) {
			foreach($files as $file) {
				if(strripos($file['name'], 'hook') !== false) {
					$names[] = $file['path'];
				}
			}
			$i = 0;
			$available = array();
			if(count($names) > 0) {
				$table = $this->response->html->table();
				$table->css = 'hooktable';
				foreach($names as $path) {
					$a = $this->response->html->a();
					$a->label = 'download';
					$a->href = $this->response->get_url($this->actions_name, 'download').'&file='.basename($path).'&mime=text/plain';
					$a->style = "text-decoration: none;";
					$a->target = '_blank';

					$tr = $this->response->html->tr();

					$plugin_name = str_replace('-storage', '', $plugin_name);
					$name = basename($path);
					$name = preg_replace('~(openqrm-'.$plugin_name.'-)(.*?)(.php)~is', '$1<b>$2</b>$3', $name);
					$available[] = preg_replace('~(openqrm-'.$plugin_name.'-<b>)(.*?)(</b>.php)~is', '$2', $name);
					$td = $this->response->html->td();
					$td->add($name);
					$tr->add($td);

					$td = $this->response->html->td();
					$td->add($a);
					$tr->add($td);

					$table->add($tr);
					$i++;
				}
			}
			if(!isset($table)) {
				$table = '<div>Plugin has no hooks</div>';
			}
			$template->add($table, 'table');
			// Legend
			$hidden = array('tab','label_plugin','label_base','please_wait','canceled');
			asort($this->lang);
			$legend = '';
			foreach($this->lang as $k => $v) {
				if(!in_array($k, $hidden)) {
					$css = '';
					if(in_array($k, $available)) { $css = 'class="available"'; }
					$legend .= '<div '.$css.'>';
					$legend .= '<h4 '.$css.'>'.$k.'</h4>';
					$legend .= '<div class="hookexplain">'.$v.'</div>';
					$legend .= '</div>';
				}
			}
			$template->add($legend, 'legend');

		} 
		$template = $this->controller->__get_navi($template, 'hooks');
		return $template;
	}


}

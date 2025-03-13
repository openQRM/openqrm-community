<?php
/**
 * Development Docblock
 *
	openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

	All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

	This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
	The latest version of this license can be found here: src/doc/LICENSE.txt

	By using this software, you acknowledge having read this license and agree to be bound thereby.

				http://openqrm-enterprise.com

	Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */


class development_docblock
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
		$t = $this->response->html->template($this->tpldir.'/development-docblock.tpl.php');
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
			$path = $this->rootdir.'/plugins/'.$this->response->html->request()->get('plugin_name').'/web/class/';
		}
		if($this->response->html->request()->get('base_name') !== '') {
			if($this->response->html->request()->get('base_name') !== 'plugins') {
				$path = $this->rootdir.'/web/base/server/'.$this->response->html->request()->get('base_name').'/class/';
			}
			elseif ($this->response->html->request()->get('base_name') === 'plugins') {
				$path = $this->rootdir.'/web/base/plugins/aa_plugins/class/';
			}
		}

		$files = $this->file->get_files($path);
		$names = array();
		if(is_array($files)) {
			foreach($files as $file) {
				if(strripos($file['name'], 'controller') !== false && strripos($file['name'], 'about') === false) {
					$names[] = $file['path'];
				}
			}
			$string = '';
			$links  = '<a name="topofpage" style="line-height:0px;font-size: 0px;">&#160;</a>';
			$i = 0;
			foreach($names as $path) {
				$class = basename($path);
				$class = str_replace('.class.php', '', $class);
				$class = str_replace('.', '_', $class);
				$class = str_replace('-', '_', $class);

				$a = $this->response->html->a();
				$a->label = $class;
				$a->href = "#".$class;
				$a->style = "text-decoration: none;";
				$links .= '<li>'.$a->get_string().'</li>';

				if($i !== 0) {
					$a = $this->response->html->a();
					$a->label = 'top';
					$a->href = "#topofpage";
					$a->css = "toplink";
					$string .= $a->get_string();
				}

				$t = $this->__template($path);
				$string .= $t->get_string();

				$i++;
			}
			$template->add($links, 'links');
			$template->add($string, 'controllers');
		} 
		$template = $this->controller->__get_navi($template, 'docblock');
		return $template;
	}


	//--------------------------------------------
	/**
	 * Get Classreader Template
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function __template($path) {

		require_once($this->rootdir.'/plugins/development/web/class/docblock.class.php');
		$reader = new docblock($path);

		$template = $this->response->html->template($this->rootdir.'/plugins/development/web/tpl/docblock.tpl.php');

		$ar = $reader->get();
		$out = array();
		$out['class'] = $ar['classname'];
		foreach($ar as $key => $value) {
		
			if($value !== '') {
				$out[$key] = '<b>'.$key.':</b> '.$value;
			} else {
				$out[$key] = '';
			}
			if($key === 'docblock') {
				$out['docblock'] = implode('<br>', $ar['docblock']);
			}
			if($key === 'attribs') {
				if(is_array($ar['attribs'])) {
					$out['attribs']    = '';
					$out['attribs-ul'] = '<ul>';
					foreach($ar['attribs'] as $k => $attrib) {
						$out['attribs'] .= '<a name="'.$ar['classname'].'-attrib-'.$k.'" href="#'.$ar['classname'].'">top</a>';
						$out['attribs'] .= '<div><b>attribute:</b> '. $k;
						$out['attribs'] .= '<br>';
						$out['attribs'] .= '<b>access:</b> '. $attrib['access'].'<br>';
						$out['attribs'] .= '<b>default:</b><div class="indent"><pre>'. $attrib['default'].'</pre></div><br>';
						$out['attribs'] .= '<pre>'.implode("\n", $attrib['docblock']).'</pre></div>';

						$out['attribs-ul'] .= '<li><a href="#'.$ar['classname'].'-attrib-'.trim($k).'">'.$k.'</a></li>';
					}
					$out['attribs-ul'] .= '</ul>';
				} else {
					$out['attribs']    = '';
					$out['attribs-ul'] = '';
				}
			}
			if($key === 'methods') {
				if(is_array($ar['methods'])) {
					$out['methods'] = '';
					$out['methods-ul'] = '<ul>';
					foreach($ar['methods'] as $k => $attrib) {
						$out['methods'] .= '<a name="'.$ar['classname'].'-method-'.$k.'" href="#'.$ar['classname'].'">top</a>';
						$out['methods'] .= '<div><b>function:</b> '. $k;
						$out['methods'] .= '<br>';
						if($attrib['params'] !== '') {
							$out['methods'] .= '<b>params:</b><div class="indent"><pre>';
							$out['methods'] .= str_replace(array(', ',','), "\n", $attrib['params']);
							$out['methods'] .= '</pre></div><br>';
						} else {
							$out['methods'] .= '<br>';
						}
						$out['methods'] .= '<pre>'.implode("\n", $attrib['docblock']).'</pre></div>';

						$out['methods-ul'] .= '<li><a href="#'.$ar['classname'].'-method-'.trim($k).'">'.$k.'</a></li>';
					}
					$out['methods-ul'] .= '</ul>';
				} else {
					$out['methods']    = '';
					$out['methods-ul'] = '';
				}
			}	

		}
		$template->add($out);
		return $template;
	}

}

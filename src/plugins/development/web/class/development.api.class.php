<?php
/**
 * Development Api
 *
	openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

	All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

	This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
	The latest version of this license can be found here: src/doc/LICENSE.txt

	By using this software, you acknowledge having read this license and agree to be bound thereby.

				http://openqrm-enterprise.com

	Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */


class development_api
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

		$this->openqrm    = $openqrm;
		$this->controller = $controller;
		$this->user       = $this->openqrm->user();
		$this->rootdir    = $this->openqrm->get('basedir');
		$this->tpldir     = $this->rootdir.'/plugins/development/tpl';
		$this->response   = $response;
		$this->file       = $this->openqrm->file();

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
		$data['canceled'] = $this->lang['canceled'];
		$data['wait'] = $this->lang['please_wait'];
		$data['prefix_tab'] = $this->prefix_tab;
		$vars = array_merge(
			$data, 
			array(
				'thisfile' => $this->response->html->thisfile,
		));
		$t = $this->response->html->template($this->tpldir.'/development-rest.tpl.php');
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

		// cache output
		ob_start();

		require_once($this->openqrm->get('webdir').'/class/htmlobjects/htmlobject.response.class.php');
		$response = new htmlobject_response($this->response->html, $this->response->id);
		$response->html->debug();

		require_once($this->openqrm->get('basedir').'/plugins/development/web/class/dummyuser.class.php');
		$dummy = new dummyuser('xxx');

		require_once($this->openqrm->get('basedir').'/web/base/class/openqrm.class.php');
		$oq = new openqrm($this->file, $dummy, $response);



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
		$name = array();
		$objs = array();
		if(is_array($files)) {
			foreach($files as $file) {
				if(strripos($file['name'], 'controller') !== false && strripos($file['name'], 'about') === false) {
					require_once($file['path']);
					$class = str_replace('.class.php', '', $file['name']);
					$class = str_replace('.', '_', $class);
					$class = str_replace('-', '_', $class);
					$class = new $class($oq, $response->response());
					$objs[] = $class;
					$name[] = str_replace('.controller.class.php','',  $file['name']);
				}
			}
		}

		$content = array();
		$i = 0;
		foreach($objs as $obj) {
				$content[$i]['name'] = $name[$i];
				$content[$i]['object'] = $obj;
				$content[$i]['class'] = get_class($obj);
				if(get_class_methods($obj)) {
						$content[$i]['methods'] = get_class_methods($obj);
				} else {
						$content[$i]['methods'] = '';
				}
				$content[$i]['vars'] = get_class_vars(get_class($obj));
				$i++;
		}

		$legend = '';
		$doc = '';
		if($this->response->html->request()->get('plugin_name') !== '') {
			$plugin = 'plugin='.$this->response->html->request()->get('plugin_name');
			$action = 'plugin';
		}
		else if($this->response->html->request()->get('base_name') !== '') {
			$plugin = 'base='.$this->response->html->request()->get('base_name');
			$action = 'base';
		}
		if(count($content) > 0) {
			foreach($content as $con) {
				foreach($con as $k => $v) {
					if($k === 'name') {
						$controller = 'controller='.$v;
					}
					if($k === 'methods') {
						if(is_array($v)) {
							$i = 0;
							foreach($v as $value) {
								$css = '';
								if(stripos($value, 'api') !== false && $controller !== 'controller=development') {
									$file = $con['name'].'.'.str_replace('_', '-', $value).'.class.php';
									if($this->file->exists($path.$file)) {
										$css = 'action';
										if($i === 0) { $i++; $css = 'action first'; }
										require_once($path.$file);
										$class = str_replace('.class.php', '', $file);
										$class = str_replace('.', '_', $class);
										$class = str_replace('-', '_', $class);
										$class = new $class($con['object']);
										// check lang
										if(isset($con['object']->lang[$value])) {
											$class->lang = $con['object']->lang[$value];
										} else {
											$class->lang = $con['object']->lang;
										}
										
										$t = $this->__template($path.$file);
										$doc .= '<div class="link"><b>Link:</b> api.php?action='.$action.'&amp;'.$plugin.'&amp;'.$controller.'&amp;'.$con['vars']['actions_name'].'=...</div>';
										$doc .= $t->get_string();
									} else {
										$GLOBALS['error_action'][] = '<div><b class="error">ACTION_ERROR:</b> missing file <b>'.$file.'</b> for action '.$value.'</div>';
									}
								}
								
							}
						}
					}
				}
			}
		} else {
			$oop = false;
			foreach($files as $file) {
				if(strripos($file['name'], 'controller') !== false && strripos($file['name'], 'about') !== false) {
					$oop = true;
					$doc = '<div>Plugin '.$this->response->html->request()->get('plugin_name').' is about controller only</div>';
					break;
				}
			}
			if($oop === false) {
				$doc = '<div>Plugin '.$this->response->html->request()->get('plugin_name').' is not OOP</div>';
			}
		}

		if($doc === '') {
			$doc = '<div>Plugin has no API</div>';
		}

		$template->add($doc, 'controllers');
		$template->add($legend, 'legend');

		// handle errors
		$phpinfo = '';
		if($this->response->html->request()->get('debug') !== '' ) {
			if(isset($GLOBALS['error_controller']) && count($GLOBALS['error_controller']) > 0) {
				$phpinfo .= implode('', $GLOBALS['error_controller']);
			}
			if(isset($GLOBALS['error_action']) && count($GLOBALS['error_action']) > 0) {
				$phpinfo .= implode('', $GLOBALS['error_action']);
			}
			if(isset($GLOBALS['error_php']) && count($GLOBALS['error_php']) > 0) {
				$phpinfo .= implode('', $GLOBALS['error_php']);
			}
			$messages = trim(ob_get_contents());
			if($messages !== '') {
				$phpinfo .= '<b>HTMLOBJECTS DEBUG:</b><br>'.$messages;
			}
		}

		// end cache
		ob_end_clean();

		if($phpinfo !== '') {
			$phpinfo = '<div class="phpinfobox">'.$phpinfo.'</div>';
		} else {
			$phpinfo = '&#160;';
		}
		$template->add($phpinfo, 'phpinfo');
		$template = $this->controller->__get_navi($template, 'api');


		// event cleanup, some methods creating events if constructed without parameters
		$event = new event();
		$event->remove_by_description('Could not create instance of');
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

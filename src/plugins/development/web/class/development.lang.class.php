<?php
/**
 * Development Lang
 *
	openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

	All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

	This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
	The latest version of this license can be found here: src/doc/LICENSE.txt

	By using this software, you acknowledge having read this license and agree to be bound thereby.

				http://openqrm-enterprise.com

	Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */


class development_lang
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
		$this->user  = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('basedir');
		$this->tpldir   = $this->rootdir.'/plugins/development/tpl';
		$this->response = $response;
		$this->file  = $this->openqrm->file();

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
		$t = $this->response->html->template($this->tpldir.'/development-lang.tpl.php');
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

		require_once($this->openqrm->get('webdir').'/class/htmlobjects/htmlobject.response.class.php');
		$response = new htmlobject_response($this->response->html, $this->response->id);
		$response->html->debug();

		require_once($this->openqrm->get('basedir').'/plugins/development/web/class/dummyuser.class.php');
		$dummy = new dummyuser('xxx');

		require_once($this->openqrm->get('basedir').'/web/base/class/openqrm.class.php');
		$oq = new openqrm($this->file, $dummy, $response);

		if($this->response->html->request()->get('plugin_name') !== '') {
			$path = $this->rootdir.'/plugins/'.$this->response->html->request()->get('plugin_name').'/web';
		}

		if($this->response->html->request()->get('base_name') !== '') {
			if($this->response->html->request()->get('base_name') !== 'plugins') {
				$path = $this->rootdir.'/web/base/server/'.$this->response->html->request()->get('base_name');
			}
			elseif ($this->response->html->request()->get('base_name') === 'plugins') {
				$path = $this->rootdir.'/web/base/plugins/aa_plugins';
			}
		}

		$files = $this->file->get_files($path.'/class/');
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

		$controller = '';
		if(count($content) > 0) {
			$files = $this->file->get_files($path.'/lang');
			foreach($content as $con) {
				if(isset($con['object']->lang)) {
					$controller .= '<div class="name"><b>Controller:</b> '.$con['class'].'</div>';
					$lfiles = array();
					foreach($files as $file) {
						if(strpos($file['name'], '.'.$con['name'].'.') !== false) {
							$lang = substr($file['name'], 0, strpos($file['name'], '.'));
							$lfiles[$lang] = $file['path'];
						}
					}
					$msg = array();
					foreach($lfiles as $lang => $file) {
						$error = array();
						$ini = $this->file->get_ini($file);
						foreach($con['object']->lang as $k => $v) {
							if(is_array($v)) {
								foreach($v as $key => $value) {
									if(isset($ini[$k]) && !array_key_exists($key, $ini[$k])) {
										$error['file'] = basename($file);
										$error['missing'][$k][$key] = '';
									} 
									else if(!isset($ini[$k])) {
										$error['file'] = basename($file);
										$error['missing'][$k][$key] = '';
									} else {
										unset($ini[$k][$key]);
									}
								}
							} else {
								if(!array_key_exists($k, $ini)) {
									$error['file'] = basename($file);
									$error['missing'][$k] = '';
								} else {
									unset($ini[$k]);
								}
							}
							if(isset($ini[$k]) && count($ini[$k]) < 1) {
								unset($ini[$k]);
							}
						}
						if(count($ini) < 1) {
							unset($ini);
						} else {
							foreach($ini as $i => $x) {
								$error['file'] = basename($file);
								if(!is_array($x)) {
									$error['supernumerous'][$i] = '';
								} else {
									if(count($x) > 0) {
										foreach($x as $xx => $vv) {
											$error['supernumerous'][$i][$xx] = '';
										}
									} else {
										$error['supernumerous'][$i] = '';
									}
								}
							}
						}

						$download = $this->response->html->a();
						$download->label  = 'download';
						$download->href   = $this->response->get_url($this->actions_name, 'download').'&file=lang/'.basename($file).'&mime=text/plain';
						$download->target = '_blank';
						$download->style  = 'vertical-align:top;text-decoration:none;';

						// check errors
						if(count($error) < 1) {
							$msg[] = '<div><b>'.$lang.'</b> <span style="color:green;vertical-align:top;">passed</span> '.$download->get_string().'</div>';
						} else {
							// cache output
							ob_start();
							$response->html->help($error);
							$messages = trim(ob_get_contents());
							// end cache
							ob_end_clean();
							if($messages !== '') {
								$txt  = '<div style="margin: 0 0 5px 0;"><b>'.$lang.'</b> <span style="color:red;vertical-align:top;">failed</span> '.$download->get_string().'</div>';
								$txt .= '<div style="height: 200px; border: 1px solid #91A7B4; overflow: auto; margin: 0 10px 10px 0;"><pre>';
								$txt .= $messages;
								$txt .= '</pre></div>';
								$msg[] = $txt;
							}
						}
					}

					// handle message
					if(count($msg) > 0) {
						$controller .= '<div style="margin: 0 0 0 30px;">';
						$controller .= implode('', $msg);
						$controller .= '</div>';
					}
				}
			}
		} else {
			$oop = false;
			foreach($files as $file) {
				if(strripos($file['name'], 'controller') !== false && strripos($file['name'], 'about') !== false) {
					$oop = true;
					$controller = '<div>Plugin '.$this->response->html->request()->get('plugin_name').' is about controller only</div>';
					break;
				}
			}
			if($oop === false) {
				$controller = '<div>Plugin '.$this->response->html->request()->get('plugin_name').' is not OOP</div>';
			}
		}

		$template->add($controller, 'controllers');

		// handle php errors
		$phpinfo = '';
		if($this->response->html->request()->get('debug') !== '' ) {
			if(isset($GLOBALS['error_php']) && count($GLOBALS['error_php']) > 0) {
				$phpinfo .= implode('', $GLOBALS['error_php']);
			}
		}
		if($phpinfo !== '') {
			$phpinfo = '<div class="phpinfobox">'.$phpinfo.'</div>';
		} else {
			$phpinfo = '&#160;';
		}
		$template->add($phpinfo, 'phpinfo');
		$template = $this->controller->__get_navi($template, 'lang');
		return $template;
	}


	//--------------------------------------------
	/**
	 * Translate
	 *
	 * @access public
	 * @param array $text_array array to translate
	 * @param string $dir dir of translation files
	 * @param string $file translation file
	 * @return array
	 */
	//--------------------------------------------
	function translate( $text_array, $dir, $file ) {
		$user_language = $this->lang;
		$path = $dir.'/'.$user_language.'.'.$file;
		if(file_exists($path)) {
			$tmp = parse_ini_file( $path, true );
			foreach($tmp as $k => $v) {
				if(is_array($v)) {
					foreach($v as $k2 => $v2) {
						$text_array[$k][$k2] = $v2;
					}
				} else {
					$text_array[$k] = $v;
				}
			}
		}
		return $text_array;
	}
}

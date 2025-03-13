<?php
/**
 * Development Rest
 *
	openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

	All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

	This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
	The latest version of this license can be found here: src/doc/LICENSE.txt

	By using this software, you acknowledge having read this license and agree to be bound thereby.

				http://openqrm-enterprise.com

	Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */


class development_rest
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
		if($this->response->html->request()->get('plugin_name') !== '') {
			$controllers = '<div class="plugin"><div class="name">plugin='.$this->response->html->request()->get('plugin_name').'</div>';
		}
		else if($this->response->html->request()->get('base_name') !== '') {
			$controllers = '<div class="plugin"><div class="name">base='.$this->response->html->request()->get('base_name').'</div>';
		}
		if(count($content) > 0) {

			$legend  = '<div class="legend">';
			$legend .= '<div><div class="plugin" style="display: inline;padding: 5px;line-height:0px;margin:0px;font-size:0px;">&#160;</div> = Plugin</div>';
			$legend .= '<div><div class="controller" style="display: inline;padding: 5px;line-height:0px;margin:0px;font-size:0px;">&#160;</div> = Controller</div>';
			$legend .= '<div><div class="action" style="display: inline;padding: 5px;line-height:0px;margin:0px;font-size:0px;">&#160;</div> = Action</div>';
			$legend .= '<fieldset style="margin: 5px 0 5px 0;">';
			$legend .= '<legend>Params</legend>';
			$legend .= '<div><strong>param</strong> = '.$this->lang['param_must_be_set_within_action'].'</div>';
			$legend .= '<div><i>param</i> = '.$this->lang['param_optional'].'</div>';
			$legend .= '<div>param = '.$this->lang['param_form_field'].'</div>';
			$legend .= '<div>param * = '.$this->lang['param_required'].'</div>';
			$legend .= '<div>param (...) = '.$this->lang['param_validator'].'</div>';
			$legend .= '</fieldset>';
			$legend .= '</div>';

			foreach($content as $con) {
				$controllers .= '<div class="controller">';
				foreach($con as $k => $v) {
					if($k === 'name') {
						$controllers .= '<div class="name">controller='.$v.'</div>';
						// check vars
						$this->__check_controller_vars($con);
					}
					if($k === 'methods') {
						if(is_array($v)) {
							$controllers .= '<div class="methods">';
							$i = 0;
							foreach($v as $value) {
								$css = '';
								if(!in_array($value, $this->methods) && strpos($value, '__') === false && stripos($value, 'reload') === false) {
								// handle duplicate
									if($value === 'duplicate') { $value = 'clone'; }
									$file = $con['name'].'.'.str_replace('_', '-', $value).'.class.php';
									if($this->file->exists($path.$file)) {
$url = array();
										$css = 'action';
										if($i === 0) { $i++; $css = 'action first'; }
										$controllers .= '<div class="'.$css.'"><span>'.$con['vars']['actions_name'].'='. $value.'</span>';

$url[] = $con['vars']['actions_name'].'='. $value;

										require_once($path.$file);
										$class = str_replace('.class.php', '', $file);
										$class = str_replace('.', '_', $class);
										$class = str_replace('-', '_', $class);
										$class = new $class($oq, $con['object']->response->response(), $con['object']);
										// check vars
										$this->__check_action_vars($class, $con, $value);
										// check lang
										if(isset($con['object']->lang[$value])) {
											$class->lang = $con['object']->lang[$value];
										} else {
											$class->lang = $con['object']->lang;
										}
										$x = new ReflectionObject($class);
										if($x->hasMethod('get_response')) {
											$params = $x->getMethod('get_response')->getParameters();
											$res = $class->get_response();
											if(isset($res->form)) {
												$str = '';
												$elem = $res->form->get_elements();
												if(is_array($elem) && count($elem) > 0) {
													foreach($elem as $key => $e) {
														if($e instanceof htmlobject_box) {
															// check elements name
															if(isset($e->__elements[0]->name) && $e->__elements[0]->name !== '') {
																$required = '';
																if(isset($res->form->__data[$key]['required'])) {
																	$required = ' *';
																}
																$regex = '';
																if(isset($res->form->__data[$key]['validate']['regex'])) {
																	$regex = ' ('.$res->form->__data[$key]['validate']['regex'].')';
																}
																$str .= '<span>'.$e->__elements[0]->name.$required.$regex.'</span>';
																$url[] = $e->__elements[0]->name.'=..';
															}
														} else {
															if(isset($e->name) && $e->name !== $con['vars']['actions_name']) {
																if($key !== 'submit' && $key !== 'cancel') {
																	$str .= '<span><strong>'.$e->name.'</strong></span>';
																	$url[] = $e->name.'=..';
																} else {
																	$str .= '<span><i>'.$e->name.'</i></span>';
																	if($key !== 'cancel') {
																		$url[] = $e->name.'=submit';
																	}
																}
															}
														}
													}
													if($str !== '') {
														$controllers .= '<div class="params"><b>Params</b>';
														$controllers .= $str;
														$controllers .= '</div>';
														if($this->response->html->request()->get('plugin_name') !== '') {
															$p = $this->response->html->request()->get('plugin_name');
															$tag = 'plugin';
														}
														else if($this->response->html->request()->get('base_name') !== '') {
															$p = $this->response->html->request()->get('base_name');
															$tag = 'base';
														}
														$controllers .= '<div class="link"><b>Link:</b> ?'.$tag.'='.$p.'&amp;controller='.$con['name'].'&amp'.implode('&amp;', $url).'</div>';
													}
												}
											}
										} else {
											$elem = '';
											if(isset($class->response->params)) {
												$elem = $class->response->params;
											}
											if(is_array($elem) && count($elem) > 0) {
												$str = '';
												foreach($elem as $key => $e) {
													$str .= '<span><strong>'.$key.'</strong></span>';
												}
												if($str !== '') {
													$controllers .= '<div class="params"><b>Params</b>';
													$controllers .= $str;
													$controllers .= '</div>';
												}
											}
										}
										$controllers .= '</div>';
									} else {
										$GLOBALS['error_action'][] = '<div><b class="error">ACTION_ERROR:</b> missing file <b>'.$file.'</b> for action '.$value.'</div>';
									}
								}
								
							}
							$controllers .= '</div>';
						}
					}
				}
				$controllers .= '</div>';
			}
			$controllers .= '</div>';
		} else {
			$oop = false;
			foreach($files as $file) {
				if(strripos($file['name'], 'controller') !== false && strripos($file['name'], 'about') !== false) {
					$oop = true;
					$controllers = '<div>Plugin '.$this->response->html->request()->get('plugin_name').' is about controller only</div>';
					break;
				}
			}
			if($oop === false) {
				$controllers = '<div>Plugin '.$this->response->html->request()->get('plugin_name').' is not OOP</div>';
			}
		}


		$template->add($controllers, 'controllers');
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
		$template = $this->controller->__get_navi($template, 'rest');


		// event cleanup, some methods creating events if constructed without parameters
		$event = new event();
		$event->remove_by_description('Could not create instance of');
		return $template;
	}

	//--------------------------------------------
	/**
	 * Check vars for controller
	 *
	 * @access private
	 * @param object $con (controller)
	 */
	//--------------------------------------------
	function __check_controller_vars($con) {
		$text = '<b class="error">CONTROLLER_ERROR:</b> missing property ';
		// actions_name
		if(!array_key_exists('actions_name', $con['vars'])) {
			$GLOBALS['error_controller'][] = '<div>'.$text.'<b>actions_name</b> for '.$con['class'].'</div>';
		}
		// message_param
		if(!array_key_exists('message_param', $con['vars'])) {
			$GLOBALS['error_controller'][] = '<div>'.$text.'<b>message_param</b> for '.$con['class'].'</div>';
		}
		// prefix_tab
		if(!array_key_exists('prefix_tab', $con['vars'])) {
			$GLOBALS['error_controller'][] = '<div>'.$text.'<b>prefix_tab</b> for '.$con['class'].'</div>';
		}
		// identifier_name
		if(!array_key_exists('identifier_name', $con['vars'])) {
			$GLOBALS['error_controller'][] = '<div>'.$text.' <b>identifier_name</b> for '.$con['class'].'</div>';
		}
		// lang
		if(!array_key_exists('lang', $con['vars'])) {
			$GLOBALS['error_controller'][] = '<div>'.$text.'<b>lang</b> for '.$con['class'].'</div>';
		}
	}

	//--------------------------------------------
	/**
	 * Check vars for action
	 *
	 * @access private
	 * @param object $class (current class)
	 * @param object $con (controller)
	 * @param string $value (action)
	 */
	//--------------------------------------------
	function __check_action_vars($class, $con, $value) {
		// actions_name
		if(!property_exists($class, 'actions_name')) {
			$GLOBALS['error_action'][] = '<div><b class="error">ACTION_ERROR:</b> missing property <b>actions_name</b> for action '.$value.' in class '.get_class($class).'</div>';
		} else {
			if(isset($class->actions_name) && $class->actions_name !== $con['object']->actions_name) {
				$GLOBALS['error_action'][] = '<div><b class="error">ACTION_ERROR:</b> missmatching property <b>actions_name</b> for action '.$value.' (action:'.$class->actions_name.' -> controller:'.$con['object']->actions_name.')</div>';
			}
		}
		// message_param
		if(!property_exists($class, 'message_param')) {
			$GLOBALS['error_action'][] = '<div><b class="error">ACTION_ERROR:</b> missing property <b>message_param</b> for action '.$value.' in class '.get_class($class).'</div>';
		} else {
			if(isset($class->message_param) && $class->message_param !== $con['object']->message_param) {
				$GLOBALS['error_action'][] = '<div><b class="error">ACTION_ERROR:</b> missmatching property <b>message_param</b> for action '.$value.' (action:'.$class->message_param.' -> controller:'.$con['object']->message_param.')</div>';
			}
		}
		// prefix_tab
		if(!property_exists($class, 'prefix_tab')) {
			$GLOBALS['error_action'][] = '<div><b class="error">ACTION_ERROR:</b> missing property <b>prefix_tab</b> for action '.$value.' in class '.get_class($class).'</div>';
		} else {
			if(isset($class->prefix_tab) && $class->prefix_tab !== $con['object']->prefix_tab) {
				$GLOBALS['error_action'][] = '<div><b class="error">ACTION_ERROR:</b> missmatching property <b>prefix_tab</b> for action '.$value.' (action:'.$class->prefix_tab.' -> controller:'.$con['object']->prefix_tab.')</div>';
			}
		}
		// identifier_name
		if(!property_exists($class, 'identifier_name')) {
			$GLOBALS['error_action'][] = '<div><b class="error">ACTION_ERROR:</b> missing property <b>identifier_name</b> for action '.$value.' in class '.get_class($class).'</div>';
		} else {
			if(isset($class->identifier_name) && $class->identifier_name !== $con['object']->identifier_name) {
				$GLOBALS['error_action'][] = '<div><b class="error">ACTION_ERROR:</b> missmatching property <b>identifier_name</b> for action '.$value.' (action:'.$class->identifier_name.' -> controller:'.$con['object']->identifier_name.')</div>';
			}
		}
	}

}

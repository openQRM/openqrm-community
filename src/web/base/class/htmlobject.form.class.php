<?php
/**
 * @package htmlobject
 *
 */

/*
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
  */

class htmlobject_formbuilder extends http
{
	/**
	 * string to mark value as required
	 * @access public
	 * @var string
	 */
	var $str_required = '*';
	/**
	 * store data
	 * @access protected
	 * @var string
	 */
	var $data = array();
	/**
	 * store request
	 * @access protected
	 * @var array
	 */
	var $request = null;
	/**
	 * store errors
	 * @access protected
	 * @var array|null
	 */
	var $check_request = null;
	
	
	//---------------------------------------
	/**
	 * Constructor
	 *
	 * Good to know:
	 * If form is cleared (inputs are empty) and then submitted,
	 * form will load preset values and will not appear empty.
	 * No Errors will be displayed. Form will appear like opening
	 * it for the first time.
	 *
	 * <code>
	 *
	 * $data  = array();
	 * $data['name']                              = array ();
	 * $data['name']['label']                     = 'Name';
	 * $data['name']['required']                  = true;
	 * // validation
	 * $data['name']['validate']                  = array ();
	 * $data['name']['validate']['regex']         = '/^[a-z0-9~._-]+$/i';
	 * $data['name']['validate']['errormsg']      = 'string must be a-z0-9~._-';
	 * // build object
	 * $data['name']['object']                    = array ();
	 * $data['name']['object']['type']            = 'htmlobject_input';
	 * $data['name']['object']['attrib']          = array();
	 * $data['name']['object']['attrib']['type']  = 'text';
	 * $data['name']['object']['attrib']['name']  = 'name';
	 * $data['name']['object']['attrib']['value'] = 'somevalue';
	 *
	 * $formbuilder = new htmlobject_formbuilder( $data );
	 *
	 * // Actions
	 * // no errors, do something
	 * if(!$formbuilder->get_errors()) {
	 *        $values = $formbuilder->get_request_as_array();
	 *        print_r($values);
	 * }
	 *
	 * $template = new Template_PHPLIB();
	 * $template->debug = 0;
	 * $template->setFile('t', 'html/template.html');
	 * $template->setVar($formbuilder->get_template_array());
	 *
	 * echo $template->parse('out', 't');
	 *
	 * </code>
	 * @acess public
	 * @param array $data
	 */
	//---------------------------------------
	function __construct($data = null)
	{
		
		if (debug::active()) {
			foreach ($data as $key => $value) {
				if (!isset($value['object'])) {
					debug::add('["' . $key . '"]["object"] not set', 'ERROR');
				}
				if (!isset($value['object']['type'])) {
					debug::add('["' . $key . '"]["object"]["type"] not set', 'ERROR');
				}
				if (!isset($value['object']['attrib'])) {
					debug::add('["' . $key . '"]["object"]["attrib"] not set', 'ERROR');
				}
				if (!isset($value['object']['attrib']['name'])) {
					debug::add('["' . $key . '"]["object"]["attrib"]["name"] not set', 'ERROR');
				} elseif ($value['object']['attrib']['name'] == '') {
					debug::add('["' . $key . '"]["object"]["attrib"]["name"] is empty', 'ERROR');
				}
				if (isset($value['validate']) &&
					!isset($value['validate']['errormsg'])
				) {
					debug::add('["' . $key . '"]["validate"]["errormsg"] not set', 'ERROR');
				} elseif (isset($value['validate']) &&
					$value['validate']['errormsg'] == ''
				) {
					debug::add('["' . $key . '"]["validate"]["errormsg"] is empty', 'NOTICE');
				}
			}
		}
		
		$this->data = $data;
		
	}
	//---------------------------------------------------------------- Public Section
	//---------------------------------------
	/**
	 * init
	 *
	 * @acess public
	 * @return array
	 */
	//---------------------------------------
	function init()
	{
		
		// only init if request not set
		// set_request() will set array
		if (!isset($this->request)) {
			// filter quots (")
			$this->add_request_filter(array(
				array('pattern' => '~\r\n~', 'replace' => '\n'),
				array('pattern' => '~&lt;~', 'replace' => '<'),
				array('pattern' => '~&quot;~', 'replace' => '"'),
				array('pattern' => '~\"~', 'replace' => '\"'),
			));
			$this->set_request();
			$this->set_check_request();
		}
		
	}
	//---------------------------------------
	/**
	 * get request values as array
	 *
	 * @acess public
	 * @return array
	 */
	//---------------------------------------
	function get_request_array($arg = null)
	{
		
		$this->init();
		if (count($this->request) > 0) {
			return $this->request;
		}
		
	}
	
	//---------------------------------------
	/**
	 * get request values as url params
	 *
	 * @acess public
	 * @return string
	 */
	//---------------------------------------
//	function get_request_string() {
	function get_request_string($firstchar = '?', $excludes = array())
	{
		$this->init();
		
		$strReturn = '';
		foreach ($this->data as $data) {
			if (isset($data['object']['attrib']['name'])) {
				$name = $this->unindex_array($data['object']['attrib']['name']);
				$request = '$this->request' . $this->string_to_index($name);
				if (eval("return isset($request);")) {
					if (is_array(eval("return $request;"))) {
						foreach (eval("return $request;") as $key => $value) {
							$strReturn .= '&' . $name . '[' . $key . ']=' . $value;
						}
					} else {
						$strReturn .= '&' . $name . '=' . eval("return $request;");
					}
				}
			}
		}
		return $strReturn;
		
	}
	
	//---------------------------------------
	/**
	 * get array for html template
	 *
	 * will return
	 * array[$key] = html element as string
	 * $key is
	 *
	 * @acess public
	 * @return array of strings
	 */
	//---------------------------------------
	function get_template_array()
	{
		$this->init();
		
		$ar = array();
		foreach ($this->data as $key => $data) {
			$box = new htmlobject_box();
			$box->label = $this->get_label($data);
			$box->content = $this->get_htmlobject_object($data);
			$box->css = 'htmlobject_box';
			$ar = array_merge($ar, array($key => $box->get_string()));
		}
		return $ar;
		
	}
	
	//---------------------------------------
	/**
	 * get errors
	 *
	 * will return array('name' => 'errormsg', ...)
	 * or null if no error occured
	 *
	 * @acess public
	 * @return array|null
	 */
	//---------------------------------------
	function get_errors()
	{
		
		$this->init();
		return $this->check_request;
		
	}
	
	//---------------------------------------------------------------- Protected Section
	//---------------------------------------
	/**
	 * set values from http request as array
	 *
	 * @acess protected
	 */
	//---------------------------------------
	function set_request()
	{
		
		$arReturn = array();
		foreach ($this->data as $data) {
			if (isset($data['object']['attrib']['name'])) {
				// set vars
				$name = $this->unindex_array($data['object']['attrib']['name']);
				if (debug::active()) {
					debug::add($name);
				}
				$request = $this->get_request($name);
				if ($request) {
					$ar = '$arReturn' . $this->string_to_index($name);
					eval("return $ar = \"$request\";");
					if (is_array($request)) {
						eval("return $ar = array();");
						foreach ($request as $key => $value) {
							$tar = $ar . '["' . $key . '"]';
							eval("return $tar = \"$value\";");
						}
					} else {
						eval("return $ar = \"$request\";");
					}
				}
			}
		}
		
		// set request even when Array is
		// empty - array is needed for
		// init() request not set test
		$this->request = $arReturn;
		
	}
	
	//---------------------------------------
	/**
	 * Check $this->data request
	 *
	 * Returns array of errors if
	 * request does not match given regex.
	 * Empty if no missmatch occured.
	 *
	 * @acess protected
	 * @return array('name'=> 'msg')
	 */
	//---------------------------------------
	function set_check_request()
	{
		
		$arReturn = array();
		foreach ($this->data as $data) {
			if (
				isset($data['validate']) &&
				isset($data['validate']['regex']) &&
				isset($data['validate']['errormsg']) &&
				isset($data['object']['attrib']['name']) &&
				count($this->request) > 0
			) {
				
				// set vars
				$regex = $data['validate']['regex'];
				$name = $data['object']['attrib']['name'];
				
				$request = '$this->request' . $this->string_to_index($name);
				if (eval("return isset($request);") && isset($regex) && $regex != '') {
					$matches = regex::match($regex, eval("return $request;"));
					if (!$matches) {
						$this->check_request[$name] = $data['validate']['errormsg'];
					}
				} elseif (eval("return !isset($request);") && isset($data['required']) && $data['required'] == true) {
					$this->check_request[$name] = 'null';
				}
			}
		}
		
	}
	
	//---------------------------------------
	/**
	 * get html objects
	 *
	 * @acess protected
	 * @param array $data
	 * @return object|null
	 */
	//---------------------------------------
	function get_htmlobject_object($data)
	{
		
		if (
			isset($data['object']) &&
			isset($data['object']['type']) &&
			isset($data['object']['attrib']) &&
			isset($data['object']['attrib']['name'])
		) {
			
			// set vars
			$object = strtolower($data['object']['type']);
			$attribs = $data['object']['attrib'];
			$name = $data['object']['attrib']['name'];
			
			// build object
			switch ($object) {
				case 'htmlobject_input':
				case 'htmlobject_select':
				case 'htmlobject_textarea':
				case 'htmlobject_button':
					$html = $this->make_htmlobject($object, $attribs);
					break;
				default:
					if (debug::active()) {
						debug::add($object . ' is not supported', 'ERROR');
					}
					break;
			}
			
			// set request
			if (isset($this->request) && count($this->request) > 0) {
				$request = '$this->request' . $this->string_to_index($name);
			}
			
			// build return
			if (
				isset($request) &&
				$request != '' &&
				isset($html)
			) {
				// add request to object
				switch ($object) {
					case 'htmlobject_input':
						$html = $this->handle_htmlobject_input($html, $request);
						break;
					case 'htmlobject_select':
						$html = $this->handle_htmlobject_select($html, $request);
						break;
					case  'htmlobject_textarea':
						$html = $this->handle_htmlobject_textarea($html, $request);
						break;
				}
				return $html;
			} elseif (isset($html)) {
				return $html;
			} else {
				return '';
			}
		}
		
	}
	
	//---------------------------------------
	/**
	 * make html objects
	 *
	 * @acess protected
	 * @param string $object
	 * @param array $attrib
	 * @return object
	 */
	//---------------------------------------
	function make_htmlobject($object, $attrib)
	{
		
		// build htmlobject
		$html = new $object();
		foreach ($attrib as $key => $param) {
			$html->$key = $param;
		}
		// make sure id is set
		$html->set_id();
		return $html;
		
	}
	
	//------- Object Section
	
	//---------------------------------------
	/**
	 * handle htmlobject_input
	 *
	 * @acess protected
	 * @param object $html
	 * @param array $attrib
	 * @param string $request
	 * @return object
	 */
	//---------------------------------------
	function handle_htmlobject_input($html, $request)
	{
		
		$html->type = strtolower($html->type);
		switch ($html->type) {
			case 'submit':
			case 'reset':
			case 'file':
			case 'image':
			case 'button':
				// do nothing
				break;
			case 'radio':
				if (
					eval("return isset($request);") &&
					eval("return $request;") == $html->value
				) {
					$html->checked = true;
				} else {
					$html->checked = false;
				}
				break;
			case 'checkbox':
				if (
					eval("return isset($request);") &&
					eval("return $request;") != ''
				) {
					$html->checked = true;
				} else {
					$html->checked = false;
				}
				break;
			case 'text':
			case 'hidden':
			case 'password':
				if (eval("return isset($request);")) {
					$html->value = str_replace('"', '&quot;', eval("return $request;"));
				} else {
					$html->value = '';
				}
				break;
		}
		return $html;
		
	}
	
	//---------------------------------------
	/**
	 * handle htmlobject_select
	 *
	 * @acess protected
	 * @param object $html
	 * @param string $request
	 * @return object
	 */
	//---------------------------------------
	function handle_htmlobject_select($html, $request)
	{
		
		if (eval("return isset($request);")) {
			if (is_array(eval("return $request;"))) {
				$html->selected = eval("return $request;");
			} else {
				$html->selected = array(eval("return $request;"));
			}
		}
		return $html;
		
	}
	
	//---------------------------------------
	/**
	 * handle htmlobject_textarea
	 *
	 * @acess protected
	 * @param object $html
	 * @param string $request
	 * @return object
	 */
	//---------------------------------------
	function handle_htmlobject_textarea($html, $request)
	{
		
		if (eval("return isset($request);")) {
			$html->text = str_replace('<', '&lt;', eval("return $request;"));
		} else {
			$html->text = '';
		}
		return $html;
		
	}
	
	//---------------------------------------
	/**
	 * handle label
	 *
	 * @acess protected
	 * @param array $data
	 * @return string
	 */
	//---------------------------------------
	function get_label($data)
	{
		
		$label = '';
		if (
			isset($data['label']) && $data['label'] != '' &&
			isset($data['object']['attrib']['name'])
		) {
			$label = $data['label'];
			$name = $data['object']['attrib']['name'];
			// mark error
			if ($this->check_request) {
				if (array_key_exists($name, $this->check_request)) {
					$label = '<span class="error">' . $label . '</span>';
				}
			}
			// mark required
			if (isset($data['required']) && $data['required'] === true) {
				$label = $label . ' ' . $this->str_required;
			}
		}
		return $label;
		
	}
	//-------------------------- Helpers
	
	//---------------------------------------
	/**
	 * get tab_request as html inputs
	 *
	 * @acess protected
	 * @param array $arValues
	 * @return string
	 */
	//---------------------------------------
	function get_tab_request_as_input($arValues = array())
	{
		
		$strReturn = '';
		$arValues = array_merge($this->tab_request, $arValues);
		foreach ($arValues as $key => $value) {
			$strReturn .= $this->get_input($key, $value, 'hidden');
		}
		return $strReturn;
		
	}
	
	//---------------------------------------
	/**
	 * get htmlobject_input as string
	 *
	 * @acess protected
	 * @param string $name
	 * @param string $value
	 * @param enum $type
	 * @return string
	 */
	//---------------------------------------
	function get_input($name, $value, $type = 'hidden')
	{
		
		$value = str_replace('"', '&quot;', $value);
		$value = str_replace('<', '&lt;', $value);
		
		$html = new htmlobject_input();
		$html->name = $name;
		$html->value = $value;
		$html->type = $type;
		
		return $html->get_string();
	}
	
	//---------------------------------------
	/**
	 * get htmlobject_button as string
	 *
	 * @acess protected
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @return string
	 */
	//---------------------------------------
	function get_button($name, $value, $label)
	{
		
		$html = new htmlobject_button();
		$html->name = $name;
		$html->value = $value;
		$html->type = 'submit';
		$html->label = $label;
		
		return $html->get_string();
	}
	
	
} // end class

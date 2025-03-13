<?php
/**
 * openQRM htmlobjects extension
 *
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

class openqrm_htmlobject extends htmlobject
{
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param openqrm $openqrm
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct()
	{
		parent::__construct($_SERVER["DOCUMENT_ROOT"] . '/openqrm/base/class/htmlobjects');
	}
	
	
	//------------------------------------------------
	/**
	 * Request Object
	 *
	 * @access public
	 * @return htmlobject_request
	 */
	//------------------------------------------------
	function request()
	{
		if (isset($this->__request)) {
			$request = $this->__request;
		} else {
			$request = parent::request();
			$request->filter = array(
				array('pattern' => '/(&#*\w+)[\x00-\x20]+;/u', 'replace' => '$1;'),
				array('pattern' => '/(&#x*[0-9A-F]+);*/iu', 'replace' => '$1;'),
				// Remove any attribute starting with "on" or xmlns
				array('pattern' => '#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', 'replace' => '$1>'),
				// Remove javascript: and vbscript: protocols
				array('pattern' => '#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', 'replace' => '$1=$2nojavascript...'),
				array('pattern' => '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', 'replace' => '$1=$2novbscript...'),
				array('pattern' => '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', 'replace' => '$1=$2nomozbinding...'),
				// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
				array('pattern' => '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', 'replace' => '$1>'),
				array('pattern' => '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', 'replace' => '$1>'),
				array('pattern' => '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', 'replace' => '$1>'),
				// Remove namespaced elements (we do not need them)
				array('pattern' => '#</*\w+:\w[^>]*+>#i', 'replace' => ''),
				// Remove really unwanted tags
				array('pattern' => '#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml|a)[^>]*+>#i', 'replace' => '')
			);
			$this->__request = $request;
		}
		return $request;
	}
	
	//------------------------------------------------
	/**
	 * Tabmenu Object
	 *
	 * @access public
	 * @param string $id prefix for posted vars
	 * @param array $params array(key => value, ...);
	 * @return htmlobject_tablebuilder
	 */
	//------------------------------------------------
	function tabmenu($id = 'currenttab')
	{
		$obj = parent::tabmenu($id);
		#$obj->message_filter = $this->__request->filter;
		return $obj;
	}
	
	//------------------------------------------------
	/**
	 * Tablebuilder Object
	 *
	 * @access public
	 * @param string $id prefix for posted vars
	 * @param array $params array(key => value, ...);
	 * @return htmlobject_tablebuilder
	 */
	//------------------------------------------------
	
	function tablebuilder($id, $params = null)
	{
		// handle table params
		!isset($_SESSION) ? @session_start() : null;
		$vars = $this->request()->get($id);
		if ($vars !== '') {
			$_SESSION['tablebuilder'][$id]['sort'] = $vars['sort'];
			$_SESSION['tablebuilder'][$id]['order'] = $vars['order'];
			$_SESSION['tablebuilder'][$id]['limit'] = $vars['limit'];
			$_SESSION['tablebuilder'][$id]['offset'] = $vars['offset'];
		} else {
			if (isset($_SESSION['tablebuilder'][$id]['sort'])) {
				$_REQUEST[$id]['sort'] = $_SESSION['tablebuilder'][$id]['sort'];
				$_REQUEST[$id]['order'] = $_SESSION['tablebuilder'][$id]['order'];
				$_REQUEST[$id]['limit'] = $_SESSION['tablebuilder'][$id]['limit'];
				$_REQUEST[$id]['offset'] = $_SESSION['tablebuilder'][$id]['offset'];
			}
		}
		$obj = parent::tablebuilder($id, $params);
		return $obj;
	}
	
	//------------------------------------------------
	/**
	 * B Object
	 *
	 * @access public
	 * @return htmlobject_customtag
	 */
	//------------------------------------------------
	function b()
	{
		return $this->customtag('b');
	}
	
	//------------------------------------------------
	/**
	 * I Object
	 *
	 * @access public
	 * @return htmlobject_customtag
	 */
	//------------------------------------------------
	function i()
	{
		return $this->customtag('i');
	}
	
	//------------------------------------------------
	/**
	 * Label Object
	 *
	 * @access public
	 * @return htmlobject_customtag
	 */
	//------------------------------------------------
	function label()
	{
		return $this->customtag('label');
	}
	
	//------------------------------------------------
	/**
	 * Span Object
	 *
	 * @access public
	 * @return htmlobject_customtag
	 */
	//------------------------------------------------
	function span()
	{
		return $this->customtag('span');
	}
	
}

<?php
/**
 * @package htmlobjects
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

//----------------------------------------------------------------------------------------

class htmlobject_tabmenu extends htmlobject
{
	/**
	 * general prefix must be set via constructor
	 * @access private
	 * @var string
	 */
	var $prefix;
	/**
	 * prefix for $message_param value
	 * @access private
	 * @var string
	 */
	var $prefix_tab = 'tab';
	/**
	 * url to process request
	 * Form disabled if empty
	 * @access public
	 * @var string
	 */
	var $form_action = '';
	/**
	 * name of param to transport message to messagebox
	 * @access public
	 * @var string
	 */
	var $message_param = 'strMsg';
	/**
	 * regex pattern for messagebox (crosssitescripting)
	 * @access public
	 * @var array
	 */
	var $message_replace = array(
		array('pattern' => '~</?script.+~i', 'replace' => ''),
		array('pattern' => '~</?iframe.+~i', 'replace' => ''),
		array('pattern' => '~</?object.+~i', 'replace' => ''),
		array('pattern' => '~://~', 'replace' => ':&frasl;&frasl;'),
	);
	/**
	 * time to show messagebox in milliseconds
	 * @access public
	 * @var int
	 */
	var $message_time = 10000;
	/**
	 * css class for messagebox
	 * @access public
	 * @var int
	 */
	var $message_css = 'msgBox';
	/**
	 * css class to highlight active tab
	 * @access public
	 * @var string
	 */
	var $tabcss = 'current';
	/**
	 * tab values
	 * @access private
	 * @var array
	 */
	var $_tabs = array();
	
	//----------------------------------------------------------------------------------------
	/**
	 * constructor
	 * <code>
	 * $content = array();
	 * $content[0]['label'] = 'some title';
	 * $content[0]['value']= 'some content text';
	 * $content[0]['target']= 'somefile.php';
	 * $content[0]['request']= '&param1=value1&param2=value2';
	 * $tab = new htmlobject_tabmenu($content, 'some_prefix');
	 * </code>
	 * @access public
	 * @param array $arr
	 * @param string $prefix
	 */
	//----------------------------------------------------------------------------------------
	function __construct($arr, $prefix = 'currenttab')
	{
		$this->prefix = $prefix;
		$this->_set($arr);
	}
	
	//----------------------------------------------------------------------------------------
	/**
	 * set tabs data
	 * @access private
	 * @param array $arr
	 */
	//----------------------------------------------------------------------------------------
	function _set($arr)
	{
		$i = 0;
		foreach ($arr as $val) {
			
			$identifier = $this->prefix . $this->prefix_tab . $i;
			
			if (array_key_exists('value', $val)) {
				$html = new htmlobject_div();
				$html->id = $identifier;
				$html->css = 'htmlobject_tab_box';
				$html->text = $val['value'] . '<div style="clear:both;line-height:0;">&#160;</div>';
				$value = $html->get_string();
			} else {
				$value = '';
			}
			
			array_key_exists('label', $val) ? $label = $val['label'] : $label = '';
			array_key_exists('target', $val) ? $target = $val['target'] . '?' . $this->prefix . '=' . $this->prefix_tab . $i : $target = '?' . $this->prefix . '=' . $this->prefix_tab . $i;
			array_key_exists('request', $val) ? $request = $val['request'] : $request = array();
			
			$this->_tabs[] = array(
				'target' => $target,
				'value' => $value,
				'label' => $label,
				'id' => $identifier,
				'request' => $request,
			);
			$i++;
		}
	}
	
	//----------------------------------------------------------------------------------------
	/**
	 * create tabs
	 * @access private
	 * @param string $currenttab
	 * @return string
	 */
	//----------------------------------------------------------------------------------------
	function _get_tabs($currenttab)
	{
		$thisfile = basename($_SERVER['PHP_SELF']);
		$_strReturn = "\n<div $this->_init_htmlobject>\n";
		$_strReturn .= "<ul>\n";
		foreach ($this->_tabs as $tab) {
			$css = '';
			if ($tab['id'] == $this->prefix . $this->prefix_tab . $currenttab) {
				$css = ' class="' . $this->tabcss . '"';
			}
			foreach ($tab['request'] as $key => $arg) {
				$tab['target'] = $tab['target'] . '&' . $key . '=' . $arg;
			}
			$_strReturn .= '<li id="tab_' . $tab['id'] . '"' . $css . '>';
			$_strReturn .= "<span>";
			if (strstr($tab['target'], $thisfile)) {
				$_strReturn .= '<a href="' . $tab['target'] . '" onclick="' . $this->prefix . 'Toggle(\'' . $tab['id'] . '\'); this.blur(); return false;">';
			} else {
				$_strReturn .= '<a href="' . $tab['target'] . '" onclick="this.blur();">';
			}
			$_strReturn .= $tab['label'];
			$_strReturn .= "</a>";
			$_strReturn .= "</span>";
			$_strReturn .= "</li>\n";
		}
		$_strReturn .= "</ul>\n";
		$_strReturn .= "</div>\n";
		$_strReturn .= "<div style=\"line-height:0px;clear:both;\">&#160;</div>\n";
		
		return $_strReturn;
	}
	
	//----------------------------------------------------------------------------------------
	/**
	 * create JS toggle function
	 * @access private
	 * @return string
	 */
	//----------------------------------------------------------------------------------------
	function _get_js()
	{
		$_strReturn = '';
		$thisfile = basename($_SERVER['PHP_SELF']);
		$_strReturn .= "\n<script>\n";
		$_strReturn .= "function " . $this->prefix . "Toggle(id) {\n";
		foreach ($this->_tabs as $tab) {
			if (strstr($tab['target'], $thisfile)) {
				$_strReturn .= "document.getElementById('" . $tab['id'] . "').style.display = 'none';\n";
				$_strReturn .= "document.getElementById('tab_" . $tab['id'] . "').className = '';\n";
			}
		}
		$_strReturn .= "document.getElementById(id).style.display = 'block';\n";
		$_strReturn .= "document.getElementById('tab_'+id).className = '" . $this->tabcss . "';\n";
		$_strReturn .= "}\n";
		$_strReturn .= "</script>\n";
		
		return $_strReturn;
	}
	
	//----------------------------------------------------------------------------------------
	/**
	 * create css
	 * @access private
	 * @param string $currenttab
	 * @return string
	 */
	//----------------------------------------------------------------------------------------
	function _get_css($currenttab)
	{
		$_strReturn = '';
		
		$_strReturn .= "\n<style>\n";
		foreach ($this->_tabs as $tab) {
			if ($tab['id'] == $this->prefix . $this->prefix_tab . $currenttab) {
				$_strReturn .= "#" . $tab['id'] . " { display: block; }\n";
			} else {
				$_strReturn .= "#" . $tab['id'] . " { display: none; }\n";
			}
		}
		$_strReturn .= "</style>\n";
		
		return $_strReturn;
	}
	
	//----------------------------------------------------------------------------------------
	/**
	 * create messagebox
	 * @access private
	 * @return string
	 */
	//----------------------------------------------------------------------------------------
	function _get_messagebox()
	{
		$_strReturn = '';
		if ($this->get_request($this->message_param) != "") {
			
			$this->http_request_replace = array_merge($this->message_replace, $this->http_request_replace);
			$msg = $this->get_request($this->message_param);
			
			$_strReturn .= '';
			$_strReturn .= '<div class="' . $this->message_css . '" id="' . $this->prefix . 'msgBox">' . $msg . '</div>';
			$_strReturn .= '<script>';
			$_strReturn .= 'var ' . $this->prefix . 'aktiv = window.setInterval("' . $this->prefix . 'msgBox()", ' . $this->message_time . ');';
			$_strReturn .= 'function ' . $this->prefix . 'msgBox() {';
			$_strReturn .= '    document.getElementById(\'' . $this->prefix . 'msgBox\').style.display = \'none\';';
			$_strReturn .= '    window.clearInterval(' . $this->prefix . 'aktiv);';
			$_strReturn .= '}';
			$_strReturn .= '</script>';
			$_strReturn .= '';
		}
		return $_strReturn;
	}
	
	//----------------------------------------------------------------------------------------
	/**
	 * build tabs output
	 * @access public
	 * @param array $arr
	 * @return string
	 */
	//----------------------------------------------------------------------------------------
	function get_string()
	{
		$_strReturn = '';
		($this->form_action != '') ? $_strReturn .= '<form action="' . $this->form_action . '" method="POST">' : null;
		if (count($this->_tabs) > 0) {
			$this->init_htmlobject();
			if (isset($_REQUEST[$this->prefix]) && $_REQUEST[$this->prefix] != '') {
				$currenttab = str_replace($this->prefix_tab, '', $_REQUEST[$this->prefix]);
			} else {
				$currenttab = '0';
			}
			$_strReturn .= $this->_get_js();
			$_strReturn .= $this->_get_css($currenttab);
			$_strReturn .= $this->_get_tabs($currenttab);
			$_strReturn .= $this->_get_messagebox();
			foreach ($this->_tabs as $tab) {
				if ($tab['value'] != '') {
					$_strReturn .= $tab['value'];
				}
			}
		}
		($this->form_action != '') ? $_strReturn .= '</form>' : null;
		
		return $_strReturn;
	}
}

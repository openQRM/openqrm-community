<?php
/**
 * @package htmlobjects
 */

//----------------------------------------------------------------------------------------
/**
 * Tabmenubuilder
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
	 * css class to highlight active tab
	 * @access public
	 * @var string
	 */
	var $tabcss = 'current';
	/**
	 * add a custom string to tabs
	 * @access public
	 * @var string
	 */
	var $custom_tab = '';
//------------------------------------------------------------------------------------ Message Section
	/**
	 * name of param to transport message to messagebox
	 * @access public
	 * @var string
	 */
	var $message_param = 'strMsg';
	/**
	 * regex pattern for messagebox (crosssitescripting)
	 * replace pattern with replace
	 * @access public
	 * @var array(array('pattern'=>'','replace'=>''));
	 */
	var $message_replace = array(
		array('pattern' => '~</?script.+~i', 'replace' => ''),
		array('pattern' => '~</?iframe.+~i', 'replace' => ''),
		array('pattern' => '~</?object.+~i', 'replace' => ''),
		array('pattern' => '~on.+=~i', 'replace' => ''),
		array('pattern' => '~javascript~i', 'replace' => ''),
		array('pattern' => '~://~', 'replace' => ':&frasl;&frasl;'),
	);
	/**
	 * time to show messagebox in milliseconds (1/1000 sec.)
	 * @access public
	 * @var int
	 */
	var $message_time = 10000;
	/**
	 * css class for messagebox
	 * @access public
	 * @var string
	 */
	var $message_css = 'msgBox';

//------------------------------------------------------------------------------------Private Section
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
	 * $content[0]['label']   = 'some title';
	 * $content[0]['value']   = 'some content text';
	 * $content[0]['target']  = 'somefile.php';
	 * $content[0]['request'] = '&param1=value1&param2=value2';
	 * $content[0]['onclick'] = false;
	 * $tab = new htmlobject_tabmenu($content, 'some_prefix');
	 * </code>
	 * @access public
	 * @param array $arr
	 * @param string $prefix
	 */
	//----------------------------------------------------------------------------------------
	function __construct($arr, $prefix = 'currenttab', $prefix_tab = 'tab')
	{
		$this->prefix = $prefix;
		$this->prefix_tab = $prefix_tab;
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
				$html->text = $val['value'] . "<div style=\"line-height:0px;clear:both;\">&#160;</div>\n";
				$value = $html;
			} else {
				$value = '';
			}
			
			array_key_exists('label', $val) ? $label = $val['label'] : $label = '';
			array_key_exists('target', $val) ? $target = $val['target'] . '?' . $this->prefix . '=' . $this->prefix_tab . $i : $target = '?' . $this->prefix . '=' . $this->prefix_tab . $i;
			array_key_exists('request', $val) ? $request = $val['request'] : $request = array();
			array_key_exists('onclick', $val) ? $onclick = $val['onclick'] : $onclick = true;
			array_key_exists('active', $val) ? $active = $val['active'] : $active = false;
			
			if ($active === true) {
				$_REQUEST[$this->prefix . $this->prefix_tab] = $i;
			}
			
			$this->_tabs[] = array(
				'target' => $target,
				'value' => $value,
				'label' => $label,
				'id' => $identifier,
				'request' => $request,
				'onclick' => $onclick,
				'active' => $active,
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
		$_str = "\n<div " . $this->_init . ">\n";
		$_str .= "<ul>\n";
		foreach ($this->_tabs as $tab) {
			$css = '';
			if ($tab['id'] == $this->prefix . $this->prefix_tab . $currenttab) {
				$css = ' class="' . $this->tabcss . '"';
			}
			foreach ($tab['request'] as $key => $arg) {
				$tab['target'] = $tab['target'] . '&amp;' . $key . '=' . $arg;
			}
			$_str .= '<li id="tab_' . $tab['id'] . '"' . $css . '>';
			$_str .= "<span>";
			
			if (strstr($tab['target'], $thisfile) && $tab['onclick'] !== false) {
				$_str .= '<a href="' . $tab['target'] . '" onclick="' . $this->prefix . 'Toggle(\'' . $tab['id'] . '\'); this.blur(); return false;">';
			} else {
				$_str .= '<a href="' . $tab['target'] . '" onclick="this.blur();">';
			}
			$_str .= $tab['label'];
			$_str .= "</a>";
			$_str .= "</span>";
			$_str .= "</li>\n";
		}
		$_str .= "</ul>\n";
		if ($this->custom_tab != '') {
			$_str .= "<div class=\"custom_tab\">" . $this->custom_tab . "</div>\n";
		}
		$_str .= "</div>\n";
		$_str .= "<div style=\"line-height:0px;clear:both;\">&#160;</div>\n";
		
		return $_str;
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
		$_str = '';
		$thisfile = basename($_SERVER['PHP_SELF']);
		$_str .= "\n<script type=\"text/javascript\">\n";
		$_str .= "function " . $this->prefix . "Toggle(id) {\n";
		foreach ($this->_tabs as $tab) {
			if (strstr($tab['target'], $thisfile)) {
				$_str .= "document.getElementById('" . $tab['id'] . "').style.display = 'none';\n";
				$_str .= "document.getElementById('tab_" . $tab['id'] . "').className = '';\n";
			}
		}
		$_str .= "document.getElementById(id).style.display = 'block';\n";
		$_str .= "document.getElementById('tab_' + id).className = '" . $this->tabcss . "';\n";
		$_str .= "}\n";
		$_str .= "</script>\n";
		
		return $_str;
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
		$_str = '';
		$this->add_request_filter($this->message_replace);
		$msg = $this->get_request($this->message_param);
		if ($msg != "") {
			$_str .= '';
			$_str .= '<div class="' . $this->message_css . '" id="' . $this->prefix . 'msgBox">' . $msg . '</div>';
			$_str .= '<script type="text/javascript">';
			$_str .= 'var ' . $this->prefix . 'aktiv = window.setInterval("' . $this->prefix . 'msgBox()", ' . $this->message_time . ');';
			$_str .= 'function ' . $this->prefix . 'msgBox() {';
			$_str .= '    document.getElementById(\'' . $this->prefix . 'msgBox\').style.display = \'none\';';
			$_str .= '    window.clearInterval(' . $this->prefix . 'aktiv);';
			$_str .= '}';
			$_str .= '</script>';
			$_str .= '';
		}
		return $_str;
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
		$_str = '';
		($this->form_action != '') ? $_str .= '<form action="' . $this->form_action . '" method="POST">' : null;
		if (count($this->_tabs) > 0) {
			$this->init();
			if (isset($_REQUEST[$this->prefix]) && $_REQUEST[$this->prefix] != '') {
				$currenttab = str_replace($this->prefix_tab, '', $_REQUEST[$this->prefix]);
			} else {
				$currenttab = '0';
			}
			$_str .= $this->_get_js();
			$_str .= $this->_get_tabs($currenttab);
			foreach ($this->_tabs as $tab) {
				if ($tab['value'] != '') {
					$html = $tab['value'];
					if ($tab['id'] !== $this->prefix . $this->prefix_tab . $currenttab) {
						$html->style = 'display:none;';
					} else {
						$html->text = $this->_get_messagebox() . $html->text;
					}
					$_str .= $html->get_string();
				}
			}
		}
		($this->form_action != '') ? $_str .= '</form>' : null;
		
		return $_str;
	}
}

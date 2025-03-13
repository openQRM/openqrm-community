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

class htmlobject extends http
{
	/**
	 * Attribute class
	 * @access public
	 * @var string
	 */
	var $css = '';
	/**
	 * Attribute id
	 * @access public
	 * @var string
	 */
	var $id = '';
	/**
	 * Attribute style
	 * @access public
	 * @var string
	 */
	var $style = '';
	/**
	 * Attribute title
	 * @access public
	 * @var string
	 */
	var $title = '';
	
	/**
	 * adds an eventhandler to
	 * @access public
	 * @var string
	 */
	var $handler = '';
	
	/**
	 * string of attribs
	 * @acess protected
	 * @var string
	 */
	var $_init;
	
	/**
	 * init attribs
	 *
	 * @acess protected
	 */
	function init()
	{
		$this->_init = '';
		if ($this->css != '') {
			$this->_init .= ' class="' . $this->css . '"';
		}
		if ($this->style != '') {
			$this->_init .= ' style="' . $this->style . '"';
		}
		if ($this->title != '') {
			$this->_init .= ' title="' . $this->title . '"';
		}
		if ($this->handler != '') {
			$this->_init .= ' ' . $this->handler;
		}
		// set id
		if ($this->id == '') {
			$this->set_id();
		}
		if ($this->id != '') {
			$this->_init .= ' id="' . $this->id . '"';
		}
	}
	
	/**
	 * set html id
	 *
	 * @acess public
	 * @param string $id
	 */
	function set_id($id = '')
	{
		if ($id != '') {
			$this->id = $id;
		}
		// if no id is set
		if ($this->id == '') {
			if (isset($this->name)) {
				$this->id = $this->name;
			} else {
				$this->id = uniqid('p',true);
			}
		}
	}
	
}

/**
 * Select
 *
 * @package htmlobject
 * @author Alexander Kuballa akuballa@openqrm-enterprise.com
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
class htmlobject_select extends htmlobject
{
	/**
	 * disable select
	 * @access public
	 * @var bool
	 */
	var $disabled = false;
	/**
	 * allow multiple selection
	 * @access public
	 * @var bool
	 */
	var $multiple = false;
	/**
	 * Attribute name (should the the same as Attribute id)
	 * @access public
	 * @var string
	 */
	var $name = '';
	/**
	 * number of lines to be shown
	 * @access public
	 * @var int
	 */
	var $size = '';
	/**
	 * Attribute tabindex
	 * @access public
	 * @var int
	 */
	var $tabindex = '';
	/**
	 * content of option element (text)
	 * @access public
	 * @var array
	 */
	var $text = array();
	/**
	 * index of array text
	 * @access public
	 * @var array
	 */
	var $text_index = array(
		"value" => 'id',
		"text" => 'name'
	);
	/**
	 * content of option element (value)
	 * @access public
	 * @var array
	 */
	var $selected = array();
	/**
	 * selected  by text or value
	 *
	 * true = selected by text
	 * false  = selected by values
	 * @access public
	 * @var bool
	 */
	var $selected_by_text = false;
	
	/**
	 * init attribs
	 *
	 * @acess protected
	 */
	function init()
	{
		parent::init();
		if ($this->disabled === true) {
			$this->_init .= ' disabled';
		}
		if ($this->multiple === true) {
			$this->_init .= ' multiple';
		}
		if ($this->name != '') {
			$this->_init .= ' name="' . $this->name . '"';
		}
		if ($this->size != '') {
			$this->_init .= ' size="' . $this->size . '"';
		}
		if ($this->tabindex != '') {
			$this->_init .= ' tabindex="' . $this->tabindex . '"';
		}
	}
	
	/**
	 * Get html element as string
	 *
	 * @acess public
	 * @return string
	 */
	function get_string()
	{
		$_strReturn = '';
		$this->init();
		$_strReturn = "\n<select$this->_init>\n";
		$_strReturn .= $this->get_options();
		$_strReturn .= "</select>\n";
		return $_strReturn;
	}
	
	function get_options()
	{
		$_strReturn = '';
		if (count($this->text) > 0) {
			for ($i = 0; $c=count($this->text) > $i; $i++) {
				$sel = "";
				if ($this->selected_by_text === true) {
					if (in_array($this->text[$i][$this->text_index['text']], $this->selected)) {
						$sel = ' selected="selected"';
					}
				} else {
					if (in_array($this->text[$i][$this->text_index['value']], $this->selected)) {
						$sel = ' selected="selected"';
					}
				}
				$_strReturn .= "<option value=\"" . $this->text[$i][$this->text_index['value']] . "\"$sel>" . $this->text[$i][$this->text_index['text']] . "</option>\n";
			}
		} else {
			$_strReturn .= '';
		}
		return $_strReturn;
	}
}

/**
 * Input
 *
 * @package htmlobject
 * @author Alexander Kuballa akuballa@openqrm-enterprise.com
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
class htmlobject_input extends htmlobject
{
	/**
	 * @access public
	 * @var bool
	 */
	var $checked = false;
	/**
	 * disable select
	 * @access public
	 * @var bool
	 */
	var $disabled = false;
	/**
	 * maxlength
	 * @access public
	 * @var int
	 */
	var $maxlength;
	/**
	 * Attribute name
	 * @access public
	 * @var string
	 */
	var $name = '';
	/**
	 * number of lines to be shown
	 * @access public
	 * @var int
	 */
	var $size = '';
	/**
	 * Attribute tabindex
	 * @access public
	 * @var int
	 */
	var $tabindex = '';
	/**
	 * type of element
	 * @access public
	 * @var string
	 * @values text | password | checkbox | radio | submit | reset | file | hidden | image | button
	 */
	var $type = '';
	/**
	 * value of input
	 * @access public
	 * @var string
	 */
	var $value = '';
	
	/**
	 * init attribs
	 *
	 * @acess protected
	 */
	function init()
	{
		parent::init();
		if ($this->checked != '') {
			$this->_init .= ' checked="checked"';
		}
		if ($this->disabled === true) {
			$this->_init .= ' disabled="disabled"';
		}
		if ($this->maxlength != '') {
			$this->_init .= ' maxlength="' . $this->maxlength . '"';
		}
		if ($this->name != '') {
			$this->_init .= ' name="' . $this->name . '"';
		}
		if ($this->size != '') {
			$this->_init .= ' size="' . $this->size . '"';
		}
		if ($this->tabindex != '') {
			$this->_init .= ' tabindex="' . $this->tabindex . '"';
		}
		if ($this->value != '') {
			$this->_init .= ' value="' . $this->value . '"';
		}
		$this->type = strtolower($this->type);
		switch ($this->type) {
			case 'text':
			case 'password':
			case 'checkbox':
			case 'radio':
			case 'submit':
			case 'reset':
			case 'hidden':
			case 'image':
			case 'button':
				$this->_init .= ' type="' . $this->type . '"';
				break;
			default:
				$this->_init .= ' type="text"';
				if (debug::active()) {
					debug::add('type ' . $this->type . ' not supported - type set to text', 'ERROR');
				}
				break;
		}
	}
	
	/**
	 * Get html element as string
	 *
	 * @acess public
	 * @return string
	 */
	function get_string()
	{
		$_strReturn = '';
		$this->init();
		$_strReturn = "<input$this->_init>";
		return $_strReturn;
	}
}

/**
 * Button
 *
 * @package htmlobject
 * @author Alexander Kuballa akuballa@openqrm-enterprise.com
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
class htmlobject_button extends htmlobject
{
	/**
	 * disable select
	 * @access public
	 * @var bool
	 */
	var $disabled = false;
	/**
	 * Attribute name
	 * @access public
	 * @var string
	 */
	var $name = '';
	/**
	 * Attribute tabindex
	 * @access public
	 * @var int
	 */
	var $tabindex = '';
	/**
	 * type of element
	 * @access public
	 * @var string
	 * @values button | submit | reset
	 */
	var $type = '';
	/**
	 * value of input
	 * @access public
	 * @var string
	 */
	var $value = '';
	/**
	 * value of input
	 * @access public
	 * @var string
	 */
	var $label = '';
	
	/**
	 * init attribs
	 *
	 * @acess protected
	 */
	function init()
	{
		parent::init();
		if ($this->disabled === true) {
			$this->_init .= ' disabled="disabled"';
		}
		if ($this->name != '') {
			$this->_init .= ' name="' . $this->name . '"';
		}
		if ($this->tabindex != '') {
			$this->_init .= ' tabindex="' . $this->tabindex . '"';
		}
		if ($this->value != '') {
			$this->_init .= ' value="' . $this->value . '"';
		}
		$this->type = strtolower($this->type);
		switch ($this->type) {
			case 'submit':
			case 'reset':
			case 'button':
				$this->_init .= ' type="' . $this->type . '"';
				break;
			default:
				$this->_init .= ' type="button"';
				if (debug::active()) {
					debug::add('type ' . $this->type . ' not supported - type set to button', 'ERROR');
				}
				break;
		}
	}
	
	/**
	 * Get html element as string
	 *
	 * @acess public
	 * @return string
	 */
	function get_string()
	{
		$_strReturn = '';
		$this->init();
		$_strReturn = "\n<button$this->_init>$this->label</button>";
		return $_strReturn;
	}
}

/**
 * Textarea
 *
 * @package htmlobject
 * @author Alexander Kuballa akuballa@openqrm-enterprise.com
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
class htmlobject_textarea extends htmlobject
{
	/**
	 * Attribute cols
	 * @access public
	 * @var int
	 */
	var $cols = 50;
	/**
	 * disable textarea
	 * @access public
	 * @var bool
	 */
	var $disabled = false;
	/**
	 * Attribute name (should the the same as Attribute id)
	 * @access public
	 * @var string
	 */
	var $name = '';
	/**
	 * set textarea to readonly
	 * @access public
	 * @var bool
	 */
	var $readonly = false;
	/**
	 * number of rows
	 * @access public
	 * @var int
	 */
	var $rows = 5;
	/**
	 * Attribute tabindex
	 * @access public
	 * @var int
	 */
	var $tabindex = '';
	/**
	 * wrap type (physical,virtual,none)
	 * @access public
	 * @var string
	 */
	var $wrap = '';
	
	/**
	 * Content of textarea
	 * @access public
	 * @var string
	 */
	var $text = '';
	
	/**
	 * init attribs
	 *
	 * @acess protected
	 */
	function init()
	{
		parent::init();
		if ($this->cols != '') {
			$this->_init .= ' cols="' . $this->cols . '"';
		}
		if ($this->disabled === true) {
			$this->_init .= ' disabled';
		}
		if ($this->name != '') {
			$this->_init .= ' name="' . $this->name . '"';
		}
		if ($this->readonly === true) {
			$this->_init .= ' readonly';
		}
		if ($this->rows != '') {
			$this->_init .= ' rows="' . $this->rows . '"';
		}
		if ($this->tabindex != '') {
			$this->_init .= ' tabindex="' . $this->tabindex . '"';
		}
		if ($this->wrap != '') {
			$this->_init .= ' wrap="' . $this->wrap . '"';
		}
	}
	
	/**
	 * Get html element as string
	 *
	 * @acess public
	 * @return string
	 */
	function get_string()
	{
		$_strReturn = '';
		$this->init();
		$_strReturn = "\n<textarea$this->_init>";
		$_strReturn .= $this->text;
		$_strReturn .= "</textarea>\n";
		return $_strReturn;
	}
}

/**
 * Form
 *
 * @package htmlobject
 * @author Alexander Kuballa akuballa@openqrm-enterprise.com
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
class htmlobject_form extends htmlobject
{
	/**
	 * uri
	 * @access public
	 * @var string
	 */
	var $action = '';
	/**
	 * mime type
	 * @access public
	 * @var string
	 */
	var $enctype = '';
	/**
	 * Post/Get
	 * @access public
	 * @var string
	 */
	var $method = '';
	/**
	 * Attribute name
	 * @access public
	 * @var string
	 */
	var $name = '';
	/**
	 * target
	 * @access public
	 * @var string
	 */
	var $target = '';
	/**
	 * form elements
	 * @access public
	 * @var string
	 */
	var $fields = '';
	
	/**
	 * init attribs
	 *
	 * @acess protected
	 */
	function init()
	{
		parent::init();
		if ($this->action != '') {
			$this->_init .= ' action="' . $this->action . '"';
		}
		if ($this->enctype != '') {
			$this->_init .= ' enctype="' . $this->enctype . '"';
		}
		if ($this->method != '') {
			$this->_init .= ' method="' . $this->method . '"';
		}
		if ($this->name != '') {
			$this->_init .= ' name="' . $this->name . '"';
		}
		if ($this->target != '') {
			$this->_init .= ' target="' . $this->target . '"';
		}
	}
	
	/**
	 * Get html element as string
	 *
	 * @acess public
	 * @return string
	 */
	function get_string()
	{
		$_strReturn = '';
		$this->init();
		$_strReturn .= "\n<form$this->_init>\n";
		$_strReturn .= $this->fields;
		$_strReturn .= "\n</form>\n";
		return $_strReturn;
	}
}

/**
 * Div
 *
 * @package htmlobject
 * @author Alexander Kuballa akuballa@openqrm-enterprise.com
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
class htmlobject_div extends htmlobject
{
	/**
	 * text
	 * @access private
	 * @var string
	 */
	var $text = '';
	
	/**
	 * Get html element as string
	 *
	 * @acess public
	 * @return string
	 */
	function get_string()
	{
		$_strReturn = '';
		$this->init();
		$_strReturn = "\n<div$this->_init>$this->text</div>";
		return $_strReturn;
	}
}

/**
 * Box
 *
 * @package htmlobject
 * @author Alexander Kuballa akuballa@openqrm-enterprise.com
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
class htmlobject_box extends htmlobject
{
	
	/**
	 * Label (Title) of box
	 * @access public
	 * @var string
	 */
	var $label = '';
	/**
	 * Label for input
	 * @access public
	 * @var string
	 */
	var $label_for = '';
	/**
	 * content
	 * @access public
	 * @var object | string
	 */
	var $content = '';
	/**
	 * css class for left box
	 * @access public
	 * @var string
	 */
	var $css_left = 'left';
	/**
	 * css class for right box
	 * @access public
	 * @var string
	 */
	var $css_right = 'right';
	
	/**
	 * init attribs
	 *
	 * @acess protected
	 */
	function init()
	{
		parent::init();
		if ($this->content == '') {
			$this->content = '&#160;';
		}
		if ($this->css_left != '') {
			$this->css_left = ' class="' . $this->css_left . '"';
		}
		if ($this->css_right != '') {
			$this->css_right = ' class="' . $this->css_right . '"';
		}
	}
	
	/**
	 * Get html element as string
	 *
	 * @acess public
	 * @return string
	 */
	function get_string()
	{
		$_strReturn = '';
		$this->init();
		$_strReturn .= "\n<div" . $this->_init . ">";
		
		if ($this->label != '') {
			$_strReturn .= "\n<div" . $this->css_left . ">";
			if (is_object($this->content) && isset($this->content->id)) {
				$_strReturn .= '<label for="' . $this->content->id . '">' . $this->label . '</label>';
			}
			if (is_string($this->content)) {
				if ($this->label_for != '') {
					$_strReturn .= '<label for="' . $this->label_for . '">' . $this->label . '</label>';
				} else {
					$_strReturn .= $this->label;
				}
			}
			$_strReturn .= "</div>";
		}
		$_strReturn .= "\n<div" . $this->css_right . ">";
		
		if (is_object($this->content)) {
			$_strReturn .= $this->content->get_string();
		}
		if (is_string($this->content)) {
			$_strReturn .= $this->content;
		}
		
		$_strReturn .= "</div>";
		$_strReturn .= "\n<div style=\"line-height:0px;height:0px;clear:both;\" class=\"floatbreaker\">&#160;</div>";
		$_strReturn .= "\n</div>";
		return $_strReturn;
	}
}

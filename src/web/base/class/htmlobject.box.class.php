<?php
/**
 * @package Htmlobjects
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


/**
 * @package Htmlobjects
 * @author Alexander Kuballa akuballa@openqrm-enterprise.com
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
	 * Attribute name (should the the same as Attribute id)
	 * @access public
	 * @var object
	 */
	var $content = '';
	/**
	 * Attribute name (should the the same as Attribute id)
	 * @access public
	 * @var string
	 */
	var $css_left = 'left';
	/**
	 * Attribute name (should the the same as Attribute id)
	 * @access public
	 * @var string
	 */
	var $css_right = 'right';
	
	/**
	 * internal use only
	 */
	
	function init_htmlobject_box()
	{
		if ($this->label == '') {
			$this->label = '&#160;';
		}
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
	
	function get_string()
	{
		$_strReturn = '';
		$this->init_htmlobject();
		$this->init_htmlobject_box();
		$_strReturn = '
		<div' . $this->_init_htmlobject . '>
		<div' . $this->css_left . '><label for="' . $this->content->id . '">' . $this->label . '</label></div>
		<div' . $this->css_right . '>' . $this->content->get_string() . '</div>
		<div style="line-height:0px;height:0px;clear:both;" class="floatbreaker">&#160;</div>
		</div>
		';
		return $_strReturn;
	}
}


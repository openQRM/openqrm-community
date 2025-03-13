<?php
/*
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
*/

$ClassDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/class/';

require_once($ClassDir."htmlobject.http.class.php");
require_once($ClassDir."htmlobject.class.php");
require_once($ClassDir."htmlobject.head.class.php");
require_once($ClassDir."htmlobject.tabmenu.class.php");
require_once($ClassDir."htmlobject.table.class.php");
require_once($ClassDir."htmlobject.preloader.class.php");
require_once($ClassDir."htmlobject.form.class.php");
require_once($ClassDir."htmlobject.debug.class.php");
require_once($ClassDir."regex.class.php");
require_once($ClassDir."PHPLIB.php");

//---------------------------------------------------------------
/**
* builds html input box
* @access public
* @param  $name string
* @param  $value array(array(label=>, value=>))
* @param  $type enum(text,hidden,password,checkbox)
* @param  $maxlength int
* @return string
*/
function htmlobject_input($name, $value, $type = 'text', $arg = '') {

	$html = new htmlobject_input();
	$html->name = $name;
	$html->id = 'p'.uniqid('', true);
	$html->value = $value['value'];
	$html->title = $value['label'];
	$html->type = $type;

	switch($type) {
		case 'text':
		case 'password':
			$html->maxlength = $arg;
			return htmlobject_box_from_object($html, ' input');
			break;
		case 'checkbox':
			$html->value = '';
			$html->checked = $arg;
			return htmlobject_box_from_object($html, ' checkbox');
			break;
		case 'hidden':
			$html->title = '';
			$html->id = '';
			return $html->get_string();
			break;
		default:
			return $html->get_string();
			break;
	}
}
//---------------------------------------------------------------
/**
* builds html select box
* @access public
* @param  $name string
* @param  $value array(label=>, value=>)
* @param  $title string
* @param  $selected array()
* @return string
*/
function htmlobject_select($name, $value, $title = '', $selected = array()) {

	$html = new htmlobject_select();
	$html->id = 'p'.uniqid('', true);
	$html->name = $name;
	$html->title = $title;
	$html->selected = $selected;
	$html->text_index = array("value" => "value", "text" => "label");
	$html->text = $value;

	return htmlobject_box_from_object($html, ' select');
}
//---------------------------------------------------------------
/**
* builds html select
* @access public
* @param  $name string
* @param  $value array(label=>, value=>)
* @param  $title string
* @param  $selected array()
* @return string
*/
function htmlobject_select_simple($name, $value, $title = '', $selected = '') {
	$html = new htmlobject_select();
	$html->name = $name;
	$html->title = $title;
	$html->selected = $selected;
	$html->text_index = array("value" => "value", "text" => "label");
	$html->text = $value;
	return $html->get_string();
}
//---------------------------------------------------------------
/**
* builds html textarea box
* @access public
* @param  $name string
* @param  $value array(label=>, value=>)
* @return string
*/
function htmlobject_textarea($name, $value) {

	$html = new htmlobject_textarea();
	$html->id = 'p'.uniqid('', true);
	$html->name = $name;
	$html->title = $value['label'];
	$html->text = $value['value'];

	return htmlobject_box_from_object($html, ' textarea');
}
//---------------------------------------------------------------
/**
* builds tabmenu
* @access public
* @param  $value array(array((label=>, value=>))
* @return string
*/
function htmlobject_tabmenu($value) {

	$tabmenu = new htmlobject_tabmenu($value);
	$tabmenu->prefix_tab = 'tab';
	$tabmenu->css = 'htmlobject_tabs';

	/*
	$i = 0;
	foreach ($value as $val) {
		$html = new htmlobject_div();
		$html->id = 'tab'.$i;
		$html->css = 'htmlobject_tab_box';
		$html->title = $val['label'];
		$html->text = $val['value'];
		$tabmenu->add($html);
		$i++;
	}
	*/

	return $tabmenu->get_string();
}
//---------------------------------------------------------------
/**
* builds htmlbox from object
* object must be htmlobject
* @access public
* @param $html object
* @return string
*/
function htmlobject_box_from_object($html, $css='', $usetitle = true) {

	$box = new htmlobject_box();
	$box->id = 'htmlobject_box_'. $html->name;
	$box->css = 'htmlobject_box'.$css;
	$box->label = $html->title;
	$box->content = $html;

	if($usetitle === false) {
		$html->title = '';
	}

	return $box->get_string();
}
//---------------------------------------------------------------
/**
* builds html radio box
* @access public
* @param  $name string
* @param  $value array(label=>, value=>)
* @param  $checked string
* @return string
*/
function htmlobject_radio_list($name, $value, $title = '', $checked = '') {
	$_strReturn = '';

	$i = 0;
	foreach ($value as $val) {
		$html = new htmlobject_input();
		$html->type = 'radio';
		$html->id = 'p'.uniqid('', true);
		$html->name = $name;
		$html->title =  $val['label'];
		if($checked == $val['value']) {
			$html->checked = true;
		}
		$html->value = $val['value'];
		$_strReturn .= htmlobject_box_from_object($html, ' radio');

		$i++;
	}

	$html = new htmlobject_div();
	$html->name = '';
	$html->title = $title;
	$html->text = $_strReturn;

	$_strReturn = htmlobject_box_from_object($html, ' outerbox', false);

	return $_strReturn;
}
//---------------------------------------------------------------
/**
* builds html table
* @access public
* @param  $name string
* @param  $value array(array(label=>, value=>, ...)
* @param  $mode enum(object,string)
* @return string
*/
function htmlobject_table($id, $value, $mode = 'object') {
	$_strReturn = '';

	$table = new htmlobject_table();
	$table->id = $id;
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 0;

	$i = 0;
	if($mode == 'object') {
		foreach ($value as $val) {
		$tr = new htmlobject_tr();
			foreach($val as $key => $v) {
				$td = new htmlobject_td();
				$td->css = $key;
				$td->text = $v;
				$tr->add($td);
			}
		$table->add($tr);
		}
	}
	if($mode == 'string') {
		$tr = '';
			foreach ($value as $val) {
				$tr .= $val;
			}
		$table->add($tr);
	}

	return $table->get_string();
}
//---------------------------------------------------------------
/**
* builds head of Page
* @access public
* @param $title string
* @return string
*/
function htmlobject_head($title = '', $timer = '', $url = '') {

	$html = new htmlobject_head();
	$html->add_meta('content-language','en');
	$html->add_meta('content-type','text/html; charset=utf-8');
	$html->add_meta('expires','Sat, 01 Dec 2001 00:00:00 GMT');
	$html->add_meta('cache-control','no-cache');
	$html->add_meta('pragma','no-cache');
	if($timer != '' && $url != '') {
		$html->add_meta('refresh', $timer.'; URL='.$url);
	}
	$html->add_style('../../css/htmlobject.css');
	$html->title = $title;

	return $html->get_string();
}
//---------------------------------------------------------------
function htmlobject_request($arg) {
	if (isset($_REQUEST[$arg]))
		return $_REQUEST[$arg];
	else
		return '';
}

function error_redirect($strMsg = '') {
	global $thisfile;
	$args = '?strMsg=<strong>Error:</strong><br>'.$strMsg;
	foreach($_POST as $key => $value) {
	if($key != 'action') {

		if(is_array($value)) {
			foreach($value as $key1 => $value1) {
				$args .= '&'.$key.'[]='.$value1;
			}
		} else {
			$args .= '&'.$key.'='.$value;
		}
	}
	}
	foreach($_GET as $key => $value) {
	if($key != 'action') {

		if(is_array($value)) {
			foreach($value as $key1 => $value1) {
				$args .= '&'.$key.'[]='.$value1;
			}
		} else {
			$args .= '&'.$key.'='.$value;
		}
	}
	}
	return $thisfile.$args;
}





class htmlobject_db_table extends htmlobject_table_builder 
{
	function __construct($field = '', $order = '', $limit = '') {
		parent::htmlobject_table_builder($field, $order, $limit);
	}
}

class htmlobject_table_identifiers_checked extends htmlobject_table_builder 
{

	var $_identifiers = array();

	function get_indentifier($key, $ident) {
		if($this->identifier != '') {
			$html = new htmlobject_input();
			$html->id = $ident;
			$html->name = 'identifier[]';
			$html->value = $this->body[$key][$this->identifier];
			$html->type = 'hidden';

			$this->_identifiers[] = $html->get_string();
		}
	}

	function get_table_head() {
		$tr = '';
		if(count($this->head) > 0) {
			$tr = new htmlobject_tr();
			$tr->css = 'htmlobject_tr';
			$tr->id = 'tr_'. uniqid('', true);

			foreach($this->head as $key_2 => $value) {
				if($value['title'] == '') { $value['title'] = '&#160;'; }
				$td = new htmlobject_td();
				$td->type = 'th';
				$td->css = 'htmlobject_td '.$key_2;
				$td->text = $value['title'];
				$tr->add($td);
			}
		}
		return $tr;
	}

	function get_table_bottom () {
		$tr = '';
		if(isset($this->bottom[0])) {
			$tr = new htmlobject_tr();
			$tr->css = 'htmlobject_tr';
			$tr->id = 'tr_'. uniqid('', true);

			$td = new htmlobject_td();
			$td->colspan = $this->_num_cols;
			$td->type = 'td';
			$td->css = 'htmlobject_td bottom';
			$str = '';
			foreach($this->bottom as $key_2 => $v) {
				$html = new htmlobject_input();
				$html->name = 'action';
				$html->value = $v;
				$html->type = 'submit';
				$str .= $html->get_string();
			}
			$str .= join("", $this->_identifiers);
			$td->text = $str;
			$tr->add($td);
		}
	return $tr;
	}
}
class htmlobject_table_identifiers_radio extends htmlobject_table_builder 
{
	function __construct($field = '', $order = '', $limit = '') {
		parent::htmlobject_table_builder($field, $order, $limit);
	}
	
	//----------------------------------------------------------------------------------------
	/**
	* returns JS for tr hover and click function
	* @access public
	* @return string
	*/
	//----------------------------------------------------------------------------------------
	function  get_js() {
	$_strReturn = '';
		$_strReturn .= "\n";
		$_strReturn .= '<script>'."\n";
		$_strReturn .= 'function tr_hover(element) {'."\n";
		$_strReturn .= '	x = element.className.match(/tr_hover/g);'."\n";
		$_strReturn .= '	if(x == null) {	element.className = element.className + " tr_hover"; }'."\n";
		$_strReturn .= '	else { element.className = element.className.replace(/ tr_hover/g, "");	}'."\n";
		$_strReturn .= '}'."\n";
		$_strReturn .= 'function tr_click(element, arg) {'."\n";
		$_strReturn .= '	document.getElementById(arg).checked = true;'."\n";
		$_strReturn .= '}'."\n";
		$_strReturn .= '</script>'."\n";
	return $_strReturn;
	}
}




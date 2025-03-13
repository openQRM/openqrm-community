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

class htmlobject_table extends htmlobject
{
	/**
	 * align
	 * @access public
	 * @var enum (left | center | right)
	 */
	var $align = '';
	/**
	 * table border
	 * @access public
	 * @var int
	 */
	var $border = '';
	/**
	 * table backgroundcolor
	 * @access public
	 * @var HEX
	 */
	var $bgcolor = '';
	/**
	 * cellpadding
	 * @access public
	 * @var int
	 */
	var $cellpadding;
	/**
	 * cellspacing
	 * @access public
	 * @var int
	 */
	var $cellspacing;
	/**
	 * frame
	 * @access public
	 * @var enum (void | above | below | hsides | lhs | rhs | vsides | box | border)
	 */
	var $frame = '';
	/**
	 * rules
	 * @access public
	 * @var enum (none | groups | rows | cols | all)
	 */
	var $rules = '';
	/**
	 * summary
	 * @access public
	 * @var string
	 */
	var $summary = '';
	/**
	 * width
	 * @access public
	 * @var int
	 */
	var $width = '';
	
	/**
	 * Content of table
	 * @access public
	 * @var array
	 */
	var $arr_table = array();
	
	
	function init()
	{
		parent::init();
		if ($this->align != '') {
			$this->_init .= ' align="' . $this->align . '"';
		}
		if (isset($this->border) && $this->border !== '') {
			$this->_init .= ' border="' . $this->border . '"';
		}
		if ($this->bgcolor != '') {
			$this->_init .= ' bgcolor="' . $this->bgcolor . '"';
		}
		if (isset($this->cellpadding) && $this->cellpadding !== '') {
			$this->_init .= ' cellpadding="' . $this->cellpadding . '"';
		}
		if (isset($this->cellspacing) && $this->cellspacing !== '') {
			$this->_init .= ' cellspacing="' . $this->cellspacing . '"';
		}
		if ($this->frame != '') {
			$this->_init .= ' frame="' . $this->frame . '"';
		}
		if ($this->rules != '') {
			$this->_init .= ' rules="' . $this->rules . '"';
		}
		if ($this->summary != '') {
			$this->_init .= ' summary="' . $this->summary . '"';
		}
		if ($this->width != '') {
			$this->_init .= ' width="' . $this->width . '"';
		}
	}
	
	function get_string()
	{
		$_strReturn = '';
		$this->init();
		$_strReturn = "\n<table$this->_init>";
		foreach ($this->arr_table as $tr) {
			if (is_object($tr) == true && get_class($tr) == 'htmlobject_tr') {
				$_strReturn .= $tr->get_string();
			} elseif (is_string($tr) == true) {
				$_strReturn .= $tr;
			} else {
				$_strReturn .= 'tr type not defined';
			}
		}
		$_strReturn .= "</table>\n";
		return $_strReturn;
	}
	
	function add($tr)
	{
		$this->arr_table[] = $tr;
	}
	
}

//----------------------------------------------------------------------------------------
/**
 * Tr
 *
 * @package htmlobjects
 * @author Alexander Kuballa akuballa@openqrm-enterprise.com
 * @copyright Copyright (c) 2009, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
//----------------------------------------------------------------------------------------

class htmlobject_tr extends htmlobject_table
{
	/**
	 * Content of tr
	 * @access public
	 * @var array
	 */
	var $arr_tr = array();
	
	
	function get_string()
	{
		$_strReturn = '';
		$this->init();
		$_strReturn = "\n<tr$this->_init>";
		foreach ($this->arr_tr as $td) {
			if (is_object($td) == true && get_class($td) == 'htmlobject_td') {
				$_strReturn .= $td->get_string();
			} elseif (is_string($td) == true) {
				$_strReturn .= $td;
			} else {
				$_strReturn .= '';
			}
		}
		$_strReturn .= "</tr>\n";
		return $_strReturn;
	}
	
	function add($td)
	{
		$this->arr_tr[] = $td;
	}
	
}

//----------------------------------------------------------------------------------------
/**
 * Td
 *
 * @package htmlobjects
 * @author Alexander Kuballa akuballa@openqrm-enterprise.com
 * @copyright Copyright (c) 2009, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
//----------------------------------------------------------------------------------------

class htmlobject_td extends htmlobject_tr
{
	/**
	 * colspan
	 * @access public
	 * @var int
	 */
	var $colspan = '';
	/**
	 * td type
	 * @access public
	 * @var enum (td,th)
	 */
	var $type = 'td';
	/**
	 * Content of td
	 * @access public
	 * @var string
	 */
	var $text = '';
	
	
	function init()
	{
		parent::init();
		if ($this->colspan != '') {
			$this->_init .= ' colspan="' . $this->colspan . '"';
		}
	}
	
	function get_string()
	{
		$_strReturn = '';
		$this->init();
		$_strReturn = "\n<$this->type$this->_init>";
		$_strReturn .= $this->text;
		$_strReturn .= "</$this->type>";
		return $_strReturn;
	}
}

//----------------------------------------------------------------------------------------
/**
 * Tablebuilder
 *
 * @package htmlobjects
 * @author Alexander Kuballa akuballa@openqrm-enterprise.com
 * @copyright Copyright (c) 2009, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
//----------------------------------------------------------------------------------------

class htmlobject_table_builder extends htmlobject_table
{
	/**
	 * head row of table (th)
	 * <code>
	 * $head = array();
	 * $head['id'] = array();
	 * $head['id']['title'] = 'id';
	 * $head['date'] = array();
	 * $head['date']['title'] = 'Datum';
	 * $head['date']['sortable'] = false;
	 * $head['date']['hidden'] = true;
	 *
	 * $table = new htmlobject_table_builder();
	 * $table->head = $head;
	 * </code>
	 * @access public
	 * @var array
	 */
	var $head = array();
	/**
	 * table body
	 * <code>
	 * $body = array();
	 * $body[] = array('id' => 'value1', 'date' => 'value2', ...)
	 * $body[] = array('id' => 'value1', 'date' => 'value2', ...)
	 *
	 * $table = new htmlobject_table_builder();
	 * $table->body = $body;
	 * </code>
	 * @access public
	 * @var array
	 */
	var $body = array();
	/**
	 * bottom row of table
	 * <code>
	 * $bottom = array();
	 * $bottom[] = 'delete';
	 * $bottom[] = 'sort';
	 *
	 * $table = new htmlobject_table_builder();
	 * $table->bottom = $bottom;
	 * </code>
	 * @access public
	 * @var array
	 */
	var $bottom = array();
	/**
	 * name for submit buttons
	 * @access public
	 * @var string
	 */
	var $bottom_buttons_name = 'action';
	
	/**
	 *  ------------------------------------------------------------- Identifier Section
	 */
	/**
	 * field to add value to checkbox
	 * @access public
	 * @var string
	 */
	var $identifier = '';
	/**
	 * type of identifier input
	 * @access public
	 * @var enum $identifier_type possible values [checkbox, radio]
	 */
	var $identifier_name = 'identifier';
	/**
	 * type of identifier input
	 * @access public
	 * @var enum $identifier_type possible values [checkbox, radio]
	 */
	var $identifier_type = 'checkbox';
	/**
	 * array of identifiers to be checked
	 * @access public
	 * @var array()
	 */
	var $identifier_checked = array();
	/**
	 * array of identifiers to be disabled
	 * @access public
	 * @var array()
	 */
	var $identifier_disabled = array();
	
	/**
	 *  ------------------------------------------------------------- CSS Section
	 */
	/**
	 * global prefix for css classes
	 * @access public
	 * @var string
	 */
	var $css_prefix = 'htmlobject_';
	
	/**
	 *  ------------------------------------------------------------- Form Section
	 */
	/**
	 * url to process request
	 *  Form disabled if empty
	 * <code>
	 * $thisfile = basename($_SERVER['PHP_SELF']);
	 * $table = new htmlobject_table_builder();
	 * $table->form_action = $thisfile;
	 * </code>
	 * @access public
	 * @var string
	 */
	var $form_action = '';
	/**
	 * first limit
	 * <code>
	 * // to change initial value to 10
	 * $table = new htmlobject_table_builder('somefield','', 10);
	 * </code>
	 * @access private
	 * @var string
	 */
	var $limit = 20;
	/**
	 * select with limit values
	 * <code>
	 * $limit_select = array(
	 *    array("value" => 10, "text" => 10),
	 *    array("value" => 20, "text" => 20),
	 *    array("value" => 30, "text" => 30),
	 *    );
	 *
	 * $table = new htmlobject_table_builder();
	 * $table->limit_select = $limit_select;
	 * </code>
	 * @access public
	 * @var array
	 */
	var $limit_select = array();
	/**
	 * offset
	 * @access public
	 * @var int
	 */
	var $offset = 0;
	/**
	 * maximum
	 * @access public
	 * @var int
	 */
	var $max = 0;
	/**
	 * field to be sorted
	 * @access public
	 * @var int
	 */
	var $sort = '';
	/**
	 * extra params for table head sort function
	 * disabled if not set
	 * @access public
	 * @var string
	 */
	var $sort_params;
	/**
	 * sort order
	 * @access public
	 * @var enum $order possible values [ASC, DESC]
	 */
	var $order = '';
	var $dorder = 'ASC';
	
	/**
	 * use array_sort to sort output
	 * @access public
	 * @var bol
	 */
	var $autosort = false;
	
	/**
	 *  ------------------------------------------------------------- Lang Section
	 */
	/**
	 * capation of refresh button
	 * @access public
	 * @var string
	 */
	var $lang_button_refresh = 'refresh';
	/**
	 * label for sort select
	 * @access public
	 * @var string
	 */
	var $lang_label_sort = 'order by';
	/**
	 * label for offset input
	 * @access public
	 * @var string
	 */
	var $lang_label_offset = 'offset';
	/**
	 * label for limit select
	 * @access public
	 * @var string
	 */
	var $lang_label_limit = 'limit';
	/**
	 * text for nolimit option
	 * @access public
	 * @var string
	 */
	var $lang_option_nolimit = 'none';
	/**
	 * Title for identifier Select function
	 * @access public
	 * @var string
	 */
	var $lang_select_title = 'Select:';
	/**
	 * identifier Select function (all)
	 * @access public
	 * @var string
	 */
	var $lang_select_all = 'all';
	/**
	 * identifier Select function (none)
	 * @access public
	 * @var string
	 */
	var $lang_select_none = 'none';
	/**
	 * identifier Select function (none)
	 * @access public
	 * @var string
	 */
	var $lang_select_invert = 'inverted';
	
	/**
	 *  ------------------------------------------------------------- Private Section
	 */
	/**
	 * number of cols
	 * @access private
	 * @var int
	 */
	var $_num_cols;
	/**
	 * output body array
	 * @access private
	 * @var array
	 */
	var $_body = array();
	/**
	 * additional headrows
	 * @access private
	 * @var array
	 */
	var $_headrow = array();
	/**
	 * additional bottomrows
	 * @access private
	 * @var array
	 */
	var $_bottomrow = array();
	/**
	 * internal prefix for posted vars
	 * @access private
	 * @var array
	 */
	var $_var_prefix;
	
	
	//----------------------------------------------------------------------------------------
	/**
	 * init htmlobject_table_builder vars from Request
	 * @access public
	 * @param string $sort key for initial sort - sort functionality is disabled if empty
	 * @param enum $order initial sort order [ASC, DESC]
	 * @param int $limit initial limit
	 * @param int $offset initial offset
	 * @param string $var_prefix prefix for posted vars
	 */
	//----------------------------------------------------------------------------------------
	function __construct($sort = '', $orderr = '', $limit = '', $offset = '', $var_prefix = 'table_')
	{
		
		$this->_var_prefix = $var_prefix;
		
		// filter request - remove all none digits
		$replace_tmp = $this->request_filter;
		$this->request_filter = array(array('pattern' => '~[^0-9]~', 'replace' => ''));
		
		if ($this->get_request($this->_var_prefix . 'limit') != '') {
			$this->limit = $this->get_request($this->_var_prefix . 'limit');
		} else if ($limit != '') {
			$this->limit = $limit;
		}
		if ($this->get_request($this->_var_prefix . 'max') != '') {
			$this->max = $this->get_request($this->_var_prefix . 'max');
		}
		if ($this->get_request($this->_var_prefix . 'offset') != '') {
			$this->offset = $this->get_request($this->_var_prefix . 'offset');
		} else if ($offset != '') {
			$this->offset = $offset;
		}
		
		// reset request filter
		$this->request_filter = $replace_tmp;
		
		if ($this->get_request($this->_var_prefix . 'order') != '') {
			$this->order = $this->get_request($this->_var_prefix . 'order');
		} else if ($orderr != '') {
			$this->order = $orderr;
		} else {
			$this->order = $this->dorder;
		}
		if ($this->get_request($this->_var_prefix . 'sort') != '') {
			$this->sort = $this->get_request($this->_var_prefix . 'sort');
		} else if ($sort != '') {
			$this->sort = $sort;
		}
		
		//------------------------------------------------------------------- set new offset
		if ($this->get_request($this->_var_prefix . 'action') != '') {
			switch ($this->get_request($this->_var_prefix . 'action')) {
				case '<':
					$this->offset = $this->offset - $this->limit;
					break;
				case '<<':
					$this->offset = 0;
					break;
				case '>':
					$this->offset = $this->offset + $this->limit;
					break;
				case '>>':
					$this->offset = $this->max - $this->limit;
					break;
				case $this->lang_button_refresh:
					break;
			}
		}
		//------------------------------------------- check offset
		if ($this->offset >= $this->max) {
			$this->offset = $this->max - $this->limit;
		}
		if ($this->offset < 0) {
			$this->offset = 0;
		}
	}
	
	//----------------------------------------------------------------------------------------
	/**
	 * init basic values _body, _num_cols
	 * @access public
	 */
	//----------------------------------------------------------------------------------------
	function init_table_builder()
	{
		
		$minus = 0;
		// Execute head array special key values
		foreach ($this->head as $key => $value) {
			//  special key hidden
			if (@array_key_exists('hidden', $this->head[$key]) == true) {
				if ($this->head[$key]['hidden'] === true) {
					$minus = $minus + 1;
				}
			}
		}
		$this->_num_cols = count($this->head) - $minus;
		if ($this->identifier != '') {
			$this->_num_cols = $this->_num_cols + 1;
		}
		
		// Sortfunction eabled?
		if ($this->sort != '') {
			// use autosort ?
			if ($this->autosort == true) {
				$this->arr_sort();
			}
			// max still untouched?
			if ($this->max == 0) {
				$this->max = count($this->body);
			}
			// Input bigger than Output?
			if (count($this->body) > $this->limit && $this->limit != 0) {
				// max smaller than  limit + offset?
				if (($this->offset + $this->limit) < $this->max) {
					$max = $this->offset + $this->limit;
				} else {
					$max = $this->max;
				}
				// Transfer Input to Output
				for ($i = $this->offset; $i < $max; $i++) {
					$this->_body[$i] = $this->body[$i];
				}
			} else {
				$this->_body = $this->body;
			}
		} else {
			$this->_body = $this->body;
		}
	}
	//----------------------------------------------------------------------------------------
	/**
	 * sorts array [body] by key [sort]
	 * @access public
	 */
	//----------------------------------------------------------------------------------------
	function arr_sort()
	{
		if ($this->order != '') {
			if ($this->order == 'ASC') $sort_order = SORT_ASC;
			if ($this->order == 'DESC') $sort_order = SORT_DESC;
		} else {
			$sort_order = SORT_ASC;
		}
		$column = array();
		foreach ($this->body as $val) {
			$column[] = $val[$this->sort];
		}
		array_multisort($column, $sort_order, $this->body);
	}
	//----------------------------------------------------------------------------------------
	/**
	 * builds table head
	 * @access public
	 * @return object|string htmlobject_tr or empty string
	 */
	//----------------------------------------------------------------------------------------
	function get_table_head()
	{
		$tr = '';
		if (count($this->head) > 0) {
			$tr = new htmlobject_tr();
			$tr->css = $this->css_prefix . 'tr';
			$tr->id = 'tr_' . uniqid('', true);
			
			foreach ($this->head as $key_2 => $value) {
				
				$hidden = false;
				if (@array_key_exists('hidden', $this->head[$key_2]) == true) {
					if ($this->head[$key_2]['hidden'] === true) {
						$hidden = true;
					}
				}
				
				$sortable = true;
				if (@array_key_exists('sortable', $this->head[$key_2]) == true) {
					if ($this->head[$key_2]['sortable'] === false) {
						$sortable = false;
					}
				}
				
				if ($hidden === false) {
					if ($value['title'] == '') {
						$str = '&#160;';
					} else {
						if ($this->sort != '' && $sortable === true && isset($this->sort_params)) {
							$order_param = '';
							$linkclass = '';
							if ($this->sort == $key_2) {
								if ($this->order == 'ASC') {
									$order_param = '&' . $this->_var_prefix . 'order=DESC';
									$linkclass = ' class="desc"';
								} else {
									$linkclass = ' class="asc"';
								}
							}
							$str = '<a href="?' . $this->_var_prefix . 'sort=' . $key_2 . $this->sort_params . $order_param . '"' . $linkclass . '>' . $value['title'] . '</a>';
						} else {
							$str = $value['title'];
						}
					}
					$td = new htmlobject_td();
					$td->type = 'th';
					$td->css = $this->css_prefix . 'td ' . $key_2;
					$td->text = $str;
					$tr->add($td);
				}
			}
			if ($this->identifier != '') {
				$td = new htmlobject_td();
				$td->type = 'th';
				$td->css = $this->css_prefix . 'td ' . $this->identifier_name;
				$td->text = '&#160;';
				$tr->add($td);
			}
		}
		return $tr;
	}
	//----------------------------------------------------------------------------------------
	/**
	 * adds one row to table body
	 * @access public
	 * @param array $val
	 * @return object|string htmlobject_tr or empty string
	 */
	//----------------------------------------------------------------------------------------
	function get_table_body($key, $val, $i)
	{
		$ident = 'id' . uniqid('', true);
		
		$tr = new htmlobject_tr();
		$tr->css = $this->css_prefix . 'tr ' . $i;
		$tr->id = 'tr_' . uniqid('', true);
		$tr->handler = $this->get_js_tr($ident);
		
		foreach ($val as $key_2 => $v) {
			if ($v == '') {
				$v = '&#160;';
			}
			if (@array_key_exists('exec', $this->head[$key_2]) == true) {
				#echo $key_2;
			}
			$hidden = false;
			if (@array_key_exists('hidden', $this->head[$key_2]) == true) {
				if ($this->head[$key_2]['hidden'] === true) {
					$hidden = true;
				}
			}
			
			if ($hidden === false) {
				$td = new htmlobject_td();
				$td->type = 'td';
				$td->css = $this->css_prefix . 'td ' . $key_2;
				$td->text = $v;
				$tr->add($td);
			}
		}
		//--------------------------------------------------------------- identifier
		if ($this->identifier != '') {
			$tr->add($this->get_indentifier($key, $ident));
		}
		return $tr;
	}
	//----------------------------------------------------------------------------------------
	/**
	 * returns table bottom row
	 * @access public
	 * @return object|string htmlobject_tr or empty string
	 */
	//----------------------------------------------------------------------------------------
	function get_table_bottom()
	{
		$tr = '';
		if (isset($this->bottom[0]) && isset($this->body[0])) {
			$tr = new htmlobject_tr();
			$tr->css = $this->css_prefix . 'tr';
			$tr->id = 'tr_' . uniqid('', true);
			
			$td = new htmlobject_td();
			$td->colspan = $this->_num_cols;
			$td->type = 'td';
			$td->css = $this->css_prefix . 'td bottom';
			$str = '<div class="actiontable">';
			foreach ($this->bottom as $key_2 => $v) {
				$html = new htmlobject_input();
				$html->name = $this->bottom_buttons_name;
				$html->value = $v;
				$html->type = 'submit';
				$str .= $html->get_string();
			}
			$str .= '</div>';
			$td->text = $this->get_select() . '' . $str . '<div style="line-height:0px;clear:both;">&#160;</div>';
			$tr->add($td);
		}
		return $tr;
	}
	//----------------------------------------------------------------------------------------
	/**
	 * adds a row above the sort row
	 * @access public
	 * @param  $str string
	 */
	//----------------------------------------------------------------------------------------
	function add_headrow($str = '')
	{
		$tr = new htmlobject_tr();
		$tr->css = $this->css_prefix . 'tr';
		$tr->id = 'tr_' . uniqid('', true);
		
		$td = new htmlobject_td();
		$td->colspan = $this->_num_cols;
		$td->type = 'td';
		$td->css = $this->css_prefix . 'td head';
		$td->text = $str;
		$tr->add($td);
		
		$this->_headrow[] = $tr;
	}
	//----------------------------------------------------------------------------------------
	/**
	 * adds a row under the bottom row
	 * @access public
	 * @param  $str string
	 */
	//----------------------------------------------------------------------------------------
	function add_bottomrow($str = '')
	{
		$tr = new htmlobject_tr();
		$tr->css = $this->css_prefix . 'tr';
		$tr->id = 'tr_' . uniqid('', true);
		
		$td = new htmlobject_td();
		$td->colspan = $this->_num_cols;
		$td->type = 'td';
		$td->css = $this->css_prefix . 'td head';
		$td->text = $str;
		$tr->add($td);
		
		$this->_bottomrow[] = $tr;
	}
	//----------------------------------------------------------------------------------------
	/**
	 * adds sort functions to table
	 * @access public
	 * @return object|string
	 */
	//----------------------------------------------------------------------------------------
	function get_sort()
	{
		$strR = '';
		if ($this->sort != '') {
			foreach ($this->head as $key_2 => $v) {
				if (isset($v['sortable']) == false) {
					$v['sortable'] = true;
				}
				if ($v['sortable'] == true) {
					$value[] = array("value" => $key_2, "label" => $v['title']);
				}
			}
			$sort = new htmlobject_select();
			$sort->id = 'p' . uniqid('', true);
			$sort->name = $this->_var_prefix . 'sort';
			$sort->text_index = array("value" => "value", "text" => "label");
			$sort->text = $value;
			$sort->selected = array($this->sort);
			$str_sort = '<label for="' . $sort->id . '">' . $this->lang_label_sort . $sort->get_string() . '</label>';
			
			$order = new htmlobject_select();
			$order->id = 'p' . uniqid('', true);
			$order->name = $this->_var_prefix . 'order';
			$order->text_index = array("value" => "value", "text" => "text");
			$order->text = array(array("value" => "ASC", "text" => "ASC"), array("value" => "DESC", "text" => "DESC"));
			$order->selected = array($this->order);
			$str_order = $order->get_string();
			
			if (count($this->limit_select) <= 0) {
				$this->limit_select = array(
					array("value" => 0, "text" => $this->lang_option_nolimit),
					array("value" => 10, "text" => 10),
					array("value" => 20, "text" => 20),
					array("value" => 30, "text" => 30),
					array("value" => 40, "text" => 40),
					array("value" => 50, "text" => 50),
				);
			}
			$limit_input = new htmlobject_select();
			$limit_input->id = 'p' . uniqid('', true);
			$limit_input->name = $this->_var_prefix . 'limit';
			$limit_input->text_index = array("value" => "value", "text" => "text");
			$limit_input->text = $this->limit_select;
			$limit_input->selected = array($this->limit);
			$str_limit = '<label for="' . $limit_input->id . '">' . $this->lang_label_limit . $limit_input->get_string() . '</label>';
			
			$offset_input = new htmlobject_input();
			$offset_input->id = 'p' . uniqid('', true);
			$offset_input->name = $this->_var_prefix . 'offset';
			$offset_input->value = "$this->offset";
			$offset_input->type = 'text';
			$offset_input->size = 3;
			$str_offset = '<label for="' . $offset_input->id . '">' . $this->lang_label_offset . $offset_input->get_string() . '</label>';
			
			$max_input = new htmlobject_input();
			$max_input->name = $this->_var_prefix . 'max';
			$max_input->value = $this->max;
			$max_input->type = 'hidden';
			
			$action = new htmlobject_input();
			$action->name = $this->_var_prefix . 'action';
			$action->value = $this->lang_button_refresh;
			$action->type = 'submit';
			
			$strR = '<div class="sort_box">';
			$strR .= $max_input->get_string() .
				$str_sort .
				$str_order .
				$str_offset .
				$str_limit .
				$action->get_string();
			$strR .= '<div style="line-height:0px;clear:both;">&#160;</div>';
			$strR .= '</div>';
		}
		return $strR;
	}
	//----------------------------------------------------------------------------------------
	/**
	 * returns page turn functions
	 * @access public
	 * @return string
	 */
	//----------------------------------------------------------------------------------------
	function get_pageturn()
	{
		$strR = '';
		if ($this->sort != '') {
			#$this->init_table_builder();
			
			$first = new htmlobject_input();
			$first->name = $this->_var_prefix . 'action';
			$first->value = '<<';
			$first->type = 'submit';
			
			$prev = new htmlobject_input();
			$prev->name = $this->_var_prefix . 'action';
			$prev->value = '<';
			$prev->type = 'submit';
			
			$next = new htmlobject_input();
			$next->name = $this->_var_prefix . 'action';
			$next->value = '>';
			$next->type = 'submit';
			
			$last = new htmlobject_input();
			$last->name = $this->_var_prefix . 'action';
			$last->value = '>>';
			$last->type = 'submit';
			
			if ($this->limit == 0) {
				$this->limit = $this->max;
			}
			
			if (($this->offset + $this->limit) >= $this->max) {
				$next->style = 'visibility:hidden;';
				$last->style = 'visibility:hidden;';
			}
			if ($this->offset <= 0) {
				$first->style = 'visibility:hidden;';
				$prev->style = 'visibility:hidden;';
			}
			
			if (($this->offset + $this->limit) < $this->max) {
				$max = $this->offset + $this->limit;
			} else {
				$max = $this->max;
			}
			
			$strR .= '<div class="pageturn_box">';
			$strR .= '<table class="pageturn_table" cellpadding="0" cellspacing="0"></tr>';
			$strR .= '  <td class="pageturn_left">' . $first->get_string() . $prev->get_string() . '</td>';
			$strR .= '  <td class="pageturn_middle">';
			$strR .= '    <span>' . ($this->offset + 1) . '</span> - ';
			$strR .= '    <span>' . $max . '</span> / ';
			$strR .= '    <span>' . $this->max . '</span>';
			$strR .= '  </td>';
			$strR .= '  <td class="pageturn_right">' . $next->get_string() . $last->get_string() . '</td>';
			$strR .= '</tr></table>';
			$strR .= '</div>';
		}
		return $strR;
	}
	//----------------------------------------------------------------------------------------
	/**
	 * returns identifier multi select functions
	 * @access public
	 * @return string
	 */
	//----------------------------------------------------------------------------------------
	function get_select()
	{
		$strR = '';
		if ($this->identifier_type == 'checkbox' && $this->identifier != '') {
			$strR .= '<div class="selecttable" id="' . $this->_var_prefix . 'SelectTable" style="display:none;">';
			$strR .= $this->lang_select_title;
			$strR .= ' <a href="javascript:selectident(\'all\');">' . $this->lang_select_all . '</a>' . "\n";
			$strR .= ' <a href="javascript:selectident(\'none\');">' . $this->lang_select_none . '</a>' . "\n";
			$strR .= ' <a href="javascript:selectident(\'invert\');">' . $this->lang_select_invert . '</a>' . "\n";
			$strR .= '<script>' . "\n";
			$strR .= 'document.getElementById("' . $this->_var_prefix . 'SelectTable").style.display = "inline"' . "\n";
			$strR .= 'function selectident(arg) {' . "\n";
			$strR .= '  if(arg == "all") {' . "\n";
			$strR .= '    for(i = 0; i < document.getElementsByName("' . $this->identifier_name . '[]").length; i++)  {' . "\n";
			$strR .= '      document.getElementsByName("' . $this->identifier_name . '[]")[i].checked = true;' . "\n";
			$strR .= '    }' . "\n";
			$strR .= '  }' . "\n";
			$strR .= '  if(arg == "none") {' . "\n";
			$strR .= '    for(i = 0; i < document.getElementsByName("' . $this->identifier_name . '[]").length; i++)  {' . "\n";
			$strR .= '      document.getElementsByName("' . $this->identifier_name . '[]")[i].checked = false;' . "\n";
			$strR .= '    }' . "\n";
			$strR .= '  }' . "\n";
			$strR .= '  if(arg == "invert") {' . "\n";
			$strR .= '    for(i = 0; i < document.getElementsByName("' . $this->identifier_name . '[]").length; i++)  {' . "\n";
			$strR .= '      if(document.getElementsByName("' . $this->identifier_name . '[]")[i].checked == false) {' . "\n";
			$strR .= '        document.getElementsByName("' . $this->identifier_name . '[]")[i].checked = true;' . "\n";
			$strR .= '      } else {' . "\n";
			$strR .= '        document.getElementsByName("' . $this->identifier_name . '[]")[i].checked = false;' . "\n";
			$strR .= '      }' . "\n";
			$strR .= '    }' . "\n";
			$strR .= '  }' . "\n";
			$strR .= '}' . "\n";
			$strR .= '</script>' . "\n";
			$strR .= '</div>' . "\n";
		}
		return $strR;
	}
	//----------------------------------------------------------------------------------------
	/**
	 * adds identifier td to body row
	 * @access public
	 * @param  $ident string
	 * @return object|string
	 */
	//----------------------------------------------------------------------------------------
	function get_indentifier($key, $ident)
	{
		$td = '';
		if ($this->identifier != '') {
			$html = new htmlobject_input();
			$html->id = $ident;
			$html->name = $this->identifier_name . '[]';
			$html->value = $this->body[$key][$this->identifier];
			$html->type = $this->identifier_type;
			if (in_array($this->body[$key][$this->identifier], $this->identifier_checked)) {
				$html->checked = true;
			}
			if (in_array($this->body[$key][$this->identifier], $this->identifier_disabled)) {
				$html = new htmlobject_div();
				$html->text = '&#160;';
			}
			
			$td = new htmlobject_td();
			$td->type = 'td';
			$td->css = $this->css_prefix . 'td ' . $this->identifier_name;
			$td->text = $html->get_string();
		}
		return $td;
	}
	//----------------------------------------------------------------------------------------
	/**
	 * returns JS for tr hover and click function
	 * @access public
	 * @return string
	 */
	//----------------------------------------------------------------------------------------
	function get_js()
	{
		$_strReturn = '';
		$id_1 = '';
		$id_2 = '';
		if ($this->identifier != '') {
			$id_1 = 'try { document.getElementById(arg).checked = true; } catch(e) {}';
			$id_2 = 'try { document.getElementById(arg).checked = false; } catch(e) {}';
		}
		$_strReturn .= "\n";
		$_strReturn .= '<script>' . "\n";
		$_strReturn .= 'function tr_hover(element) {' . "\n";
		$_strReturn .= '	x = element.className.match(/tr_hover/g);' . "\n";
		$_strReturn .= '	if(x == null) {	element.className = element.className + " tr_hover"; }' . "\n";
		$_strReturn .= '	else { element.className = element.className.replace(/ tr_hover/g, "");	}' . "\n";
		$_strReturn .= '}' . "\n";
		$_strReturn .= 'function tr_click(element, arg) {' . "\n";
		$_strReturn .= '	x = element.className.match(/tr_click/g);' . "\n";
		$_strReturn .= '	if(x == null) {	element.className = element.className + " tr_click";';
		$_strReturn .= '	' . $id_1 . ' }' . "\n";
		$_strReturn .= '	else { element.className = element.className.replace(/ tr_click/g, "");';
		$_strReturn .= '	' . $id_2 . ' }' . "\n";
		$_strReturn .= '}' . "\n";
		$_strReturn .= '</script>' . "\n";
		return $_strReturn;
	}
	//----------------------------------------------------------------------------------------
	/**
	 * returns JS for tr
	 * @access public
	 * @return string
	 */
	//----------------------------------------------------------------------------------------
	function get_js_tr($ident)
	{
		$script = 'onmouseover="tr_hover(this);" onmouseout="tr_hover(this);"';
		#if($this->identifier != '') {
		$script .= ' onclick="tr_click(this, \'' . $ident . '\')"';
		#}
		return $script;
	}
	//----------------------------------------------------------------------------------------
	/**
	 * builds html table
	 * @access public
	 * @param  $name string
	 * @param  $value array(array(label=>, value=>, ...)
	 * @return string
	 */
	//----------------------------------------------------------------------------------------
	function get_string()
	{
		$_strReturn = '';
		
		// build table
		$this->init_table_builder();
		
		// build additional table head
		foreach ($this->_headrow as $row) {
			$row->arr_tr[0]->colspan = $this->_num_cols;
			htmlobject_table::add($row);
		}
		// build sort functions
		if ($this->sort != '') {
			$td = new htmlobject_td();
			$td->colspan = $this->_num_cols;
			$td->type = 'td';
			$td->css = $this->css_prefix . 'td pageturn_head';
			
			$tr = new htmlobject_tr();
			$tr->css = $this->css_prefix . 'tr pageturn_head';
			$tr->id = 'tr_' . uniqid('', true);
			$td->text = $this->get_sort() . $this->get_pageturn();
			$tr->add($td);
			htmlobject_table::add($tr);
		}
		// build table head
		htmlobject_table::add($this->get_table_head());
		
		// build table body
		$i = 'odd';
		foreach ($this->_body as $key => $value) {
			htmlobject_table::add($this->get_table_body($key, $value, $i));
			if ($i == 'odd') $i = 'even';
			else  $i = 'odd';
		}
		// build table bottom
		htmlobject_table::add($this->get_table_bottom());
		
		// insert bottom pageturn
		if (count($this->_body) > 9 && $this->limit < $this->max && $this->sort != '') {
			$td = new htmlobject_td();
			$td->colspan = $this->_num_cols;
			$td->type = 'td';
			$td->css = $this->css_prefix . 'td pageturn_bottom';
			
			$tr = new htmlobject_tr();
			$tr->css = $this->css_prefix . 'tr pageturn_bottom';
			$tr->id = 'tr_' . uniqid('', true);
			$td->text = $this->get_pageturn();
			$tr->add($td);
			htmlobject_table::add($tr);
		}
		
		foreach ($this->_bottomrow as $row) {
			$row->arr_tr[0]->colspan = $this->_num_cols;
			htmlobject_table::add($row);
		}
		// build form
		$_strReturn = $this->get_js();
		($this->form_action != '') ? $_strReturn .= '<form action="' . $this->form_action . '" method="GET">' : null;
		$_strReturn .= htmlobject_table::get_string();
		($this->form_action != '') ? $_strReturn .= '</form>' : null;
		return $_strReturn;
	}
	
}//-- end class

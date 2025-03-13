<?php
/**
 * Openqrm Top
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

class openqrm_top
{
	/**
	 * absolute path to template dir
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
	 * @param htmlobject_response $response
	 * @param file $file
	 * @param openqrm $openqrm
	 */
	//--------------------------------------------
	function __construct($response, $file, $openqrm)
	{
		$this->response = $response;
		$this->file = $file;
		$this->openqrm = $openqrm;
	}
	
	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action()
	{
		$t = $this->response->html->template($this->tpldir . '/index_top.tpl.php');
		// only translate if user is not empty (configure mode)
		if ($this->openqrm !== '') {
			$t->add($this->openqrm->user()->name, 'username');
			$t->add($this->openqrm->user()->lang, 'userlang');
			$t->add($this->lang['account'], 'account');
			$t->add($this->lang['documentation'], 'documentation');
			$t->add($this->lang['info'], 'info');
			$t->add($this->lang['support'], 'support');
			$t->add($this->response->html->thisfile, 'thisfile');
			$t->add($this->openqrm->user()->lang, 'userlang');
			$openqrm_config = $this->openqrm->get('config');
			$openqrm_version = $openqrm_config['SERVER_VERSION'] . "." . $openqrm_config['MINOR_RELEASE_VERSION'];
			$t->add($openqrm_version, 'version');
			
			$a = $this->response->html->a();
			$a->href = '../';
			$a->label = '&#160;';
			$a->title = 'Logout';
			$a->css = 'logout';
			$a->id = 'logoutbutton';
			$a->style = 'display:none;';
			$a->handler = 'onclick="Logout(this);return false;"';
			$logout = $a->get_string();
			$logout .= '<script type="text/javascript">';
			$logout .= 'document.getElementById(\'logoutbutton\').style.display = "block";';
			$logout .= '</script>';
			
			$select = $this->response->html->select();
			$select->css = 'htmlobject_select';
			$select->id = 'Language_select';
			$select->name = 'language';
			$select->handler = 'onchange="wait(); set_language();"';
			
			$languages = array();
			$files = $this->file->get_files($this->openqrm->get('basedir') . '/web/base/lang/', '', '*.htmlobjects.ini');
			foreach ($files as $v) {
				$tmp = explode('.', $v['name']);
				$languages[] = $tmp[0];
			}
			
			foreach ($languages as $lang) {
				$o = $this->response->html->option();
				$o->label = '&nbsp;' . $lang;
				$o->css = 'lang-' . $lang;
				$o->value = $lang;
				$o->style = 'background-image: url(img/' . $lang . '.gif) no-repeat';
				if ($lang === $this->openqrm->user()->lang) {
					$o->selected = true;
				}
				$select->add($o);
			}
			$box = $this->response->html->box();
			$box->id = 'Language_box';
			$box->label = $this->lang['language'];
			$box->add($select);
			$box->add($logout);
			
			$t->add($box, 'language_select');
		} else {
			$t->add('&#160;', 'language_select');
			$t->add('&#160;', 'account');
			$t->add('&#160;', 'documentation');
			$t->add('&#160;', 'info');
			$t->add('&#160;', 'support');
		}
		return $t;
	}
	
}

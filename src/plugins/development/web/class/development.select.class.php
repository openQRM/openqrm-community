<?php
/**
 * Development select
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class development_select
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
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param openqrm $openqrm
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->tpldir   = $this->rootdir.'/plugins/development/tpl';

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
		$response = $this->select();
		$t = $this->response->html->template($this->tpldir.'/development-select.tpl.php');
		$t->add($response->table_plugins, 'table_plugins');
		$t->add($response->table_base, 'table_base');
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		return $t;

	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
		$response = $this->response;

		$h['plugin']['title']    = $this->lang['id'];
		$h['plugin']['sortable'] = true;

		$h['docblock']['title']    = '&#160;';
		$h['docblock']['sortable'] = false;

		$h['lang']['title']    = '&#160;';
		$h['lang']['sortable'] = false;

		$h['edit']['title']    = '&#160;';
		$h['edit']['sortable'] = false;

		$h['api']['title']    = '&#160;';
		$h['api']['sortable'] = false;

		$h['hooks']['title']    = '&#160;';
		$h['hooks']['sortable'] = false;

		$h['template']['title']    = '&#160;';
		$h['template']['sortable'] = false;

		$h['css']['title']    = '&#160;';
		$h['css']['sortable'] = false;

		$h['js']['title']    = '&#160;';
		$h['js']['sortable'] = false;

		// plugins
		$plugin = new plugin();
		$result = $plugin->available();
		$b = array();
		if(is_array($result)) {
			foreach($result as $v) {
				$tmp['plugin'] = $v;
				$tmp['docblock'] = '&#160;';
				$tmp['edit'] = '&#160;';
				$tmp['api'] = '&#160;';
				$tmp['lang'] = '&#160;';
				$tmp['template'] = '&#160;';
				$tmp['hooks'] = '&#160;';
				$path = $this->openqrm->get('basedir').'/plugins/'.$v.'/web/class/';
				$files = $this->file->get_files($path);
				$oop = false;
				foreach($files as $file) {
					if(strripos($file['name'], 'controller') !== false && strripos($file['name'], 'about') === false) {
						$oop = true;
						break;
					}
				}
				if($oop === true) {
					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'docblock' ).'&plugin_name='.$v;
					$a->label    = $this->lang['action_docblock'];
					$a->title    = $this->lang['action_docblock'];
					$a->handler  = 'onclick="wait();"';
					$tmp['docblock'] = $a->get_string();

					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'lang' ).'&plugin_name='.$v;
					$a->label    = $this->lang['action_lang'];
					$a->title    = $this->lang['action_lang'];
					$a->handler  = 'onclick="wait();"';
					$tmp['lang'] = $a->get_string();

					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'rest' ).'&plugin_name='.$v;
					$a->label    = $this->lang['action_rest'];
					$a->title    = $this->lang['action_rest'];
					$a->handler  = 'onclick="wait();"';
					$tmp['edit'] = $a->get_string();

					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'api' ).'&plugin_name='.$v;
					$a->label    = $this->lang['action_api'];
					$a->title    = $this->lang['action_api'];
					$a->handler  = 'onclick="wait();"';
					$tmp['api'] = $a->get_string();

					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'template' ).'&plugin_name='.$v;
					$a->label    = $this->lang['action_template'];
					$a->title    = $this->lang['action_template'];
					$a->handler  = 'onclick="wait();"';
					$tmp['template'] = $a->get_string();

					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'hooks' ).'&plugin_name='.$v;
					$a->label    = $this->lang['action_hooks'];
					$a->title    = $this->lang['action_hooks'];
					$a->handler  = 'onclick="wait();"';
					$tmp['hooks'] = $a->get_string();

					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'js' ).'&plugin_name='.$v;
					$a->label    = $this->lang['action_js'];
					$a->title    = $this->lang['action_js'];
					$a->handler  = 'onclick="wait();"';
					$tmp['js'] = $a->get_string();

					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'css' ).'&plugin_name='.$v;
					$a->label    = $this->lang['action_css'];
					$a->title    = $this->lang['action_css'];
					$a->handler  = 'onclick="wait();"';
					$tmp['css'] = $a->get_string();
				}
				$b[] = $tmp;
			}
		}


		$table = $this->response->html->tablebuilder('plugins', $this->response->get_array($this->actions_name, 'select'));
		$table->offset       = 0;
		$table->sort         = 'plugin';
		$table->limit        = 0;
		$table->order        = 'ASC';
		$table->max          = count($b);
		$table->css          = 'htmlobject_table';
		$table->border       = 0;
		$table->id           = 'Tabelle_1';
		$table->form_action  = $this->response->html->thisfile;
		$table->head         = $h;
		$table->body         = $b;
		$table->sort_params  = $response->get_string( $this->actions_name, 'select' );
		$table->sort_form    = false;
		$table->sort_link    = false;
		$table->autosort     = true;

		$response->table_plugins = $table;

		// base
		$result = array('aa_server','appliance', 'event', 'image','kernel','resource','storage', 'plugins');
		$b = array();
		if(is_array($result)) {
			foreach($result as $v) {
				$tmp['plugin'] = $v;
				$tmp['docblock'] = '&#160;';
				$tmp['edit'] = '&#160;';
				$tmp['api'] = '&#160;';
				$tmp['lang'] = '&#160;';
				$tmp['template'] = '&#160;';
				$tmp['hooks'] = '&#160;';
				if($v !== 'plugins') {
					$path = $this->openqrm->get('basedir').'/web/base/server/'.$v.'/class/';
				}
				elseif ($v === 'plugins') {
					$path = $this->openqrm->get('basedir').'/web/base/plugins/aa_plugins/class/';
				}
				$files = $this->file->get_files($path);
				$oop = false;
				foreach($files as $file) {
					if(strripos($file['name'], 'controller') !== false && strripos($file['name'], 'about') === false) {
						$oop = true;
						break;
					}
				}
				if($oop === true) {
					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'docblock' ).'&base_name='.$v;
					$a->label    = $this->lang['action_docblock'];
					$a->title    = $this->lang['action_docblock'];
					$a->handler  = 'onclick="wait();"';
					$tmp['docblock'] = $a->get_string();

					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'lang' ).'&base_name='.$v;
					$a->label    = $this->lang['action_lang'];
					$a->title    = $this->lang['action_lang'];
					$a->handler  = 'onclick="wait();"';
					$tmp['lang'] = $a->get_string();

					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'rest' ).'&base_name='.$v;
					$a->label    = $this->lang['action_rest'];
					$a->title    = $this->lang['action_rest'];
					$a->handler  = 'onclick="wait();"';
					$tmp['edit'] = $a->get_string();

					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'api' ).'&base_name='.$v;
					$a->label    = $this->lang['action_api'];
					$a->title    = $this->lang['action_api'];
					$a->handler  = 'onclick="wait();"';
					$tmp['api'] = $a->get_string();

					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'template' ).'&base_name='.$v;
					$a->label    = $this->lang['action_template'];
					$a->title    = $this->lang['action_template'];
					$a->handler  = 'onclick="wait();"';
					$tmp['template'] = $a->get_string();

					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'hooks' ).'&base_name='.$v;
					$a->label    = $this->lang['action_hooks'];
					$a->title    = $this->lang['action_hooks'];
					$a->handler  = 'onclick="wait();"';
					$tmp['hooks'] = $a->get_string();

					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'js' ).'&base_name='.$v;
					$a->label    = $this->lang['action_js'];
					$a->title    = $this->lang['action_js'];
					$a->handler  = 'onclick="wait();"';
					$tmp['js'] = $a->get_string();

					$a           = $response->html->a();
					$a->href     = $response->get_url($this->actions_name, 'css' ).'&base_name='.$v;
					$a->label    = $this->lang['action_css'];
					$a->title    = $this->lang['action_css'];
					$a->handler  = 'onclick="wait();"';
					$tmp['css'] = $a->get_string();
				}
				$b[] = $tmp;
			}
		}

		$table = $this->response->html->tablebuilder('base', $this->response->get_array($this->actions_name, 'select'));
		$table->offset       = 0;
		$table->sort         = 'plugin';
		$table->limit        = 0;
		$table->order        = 'ASC';
		$table->max          = count($b);
		$table->css          = 'htmlobject_table';
		$table->border       = 0;
		$table->id           = 'Tabelle_2';
		$table->form_action  = $this->response->html->thisfile;
		$table->head         = $h;
		$table->body         = $b;
		$table->sort_params  = $response->get_string( $this->actions_name, 'select' );
		$table->sort_form    = false;
		$table->sort_link    = false;
		$table->autosort     = true;

		$response->table_base = $table;


		return $response;
	}


}

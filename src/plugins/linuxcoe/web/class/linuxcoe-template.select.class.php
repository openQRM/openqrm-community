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



class linuxcoe_template_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'linuxcoe_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'linuxcoe_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'linuxcoe_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'linuxcoe_identifier';
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
var $lang;


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
		$this->response = $response;
		$this->openqrm = $openqrm;
		$this->file = $this->openqrm->file();
	}

	//--------------------------------------------
	/**
	 * Action New
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		$table = $this->select();
		$template = $this->response->html->template($this->tpldir."/linuxcoe-template-select.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($this->lang['label'], "linuxcoe_title");
		$template->add($table, 'table');
		$template->add($this->openqrm->get('baseurl'), 'baseurl');
		return $template;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
//		$this->__response->html->debug();

		$arHead = array();

		$arHead['lcoe_profile_icon'] = array();
		$arHead['lcoe_profile_icon']['title'] =' ';
		$arHead['lcoe_profile_icon']['sortable'] = false;

		$arHead['lcoe_profile_id'] = array();
		$arHead['lcoe_profile_id']['title'] = $this->lang['table_id'];

		$arHead['lcoe_profile_name'] = array();
		$arHead['lcoe_profile_name']['title'] = $this->lang['table_name'];

		$arHead['lcoe_profile_comment'] = array();
		$arHead['lcoe_profile_comment']['title'] = $this->lang['table_comment'];

		$arHead['lcoe_profile_edit'] = array();
		$arHead['lcoe_profile_edit']['title'] =' ';
		$arHead['lcoe_profile_edit']['sortable'] = false;

		$table = $this->response->html->tablebuilder( 'table_linuxcoe_template', $this->response->get_array($this->actions_name, 'select'));
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->limit           = 10;
		$table->id              = 'Tabelle';
		$table->head            = $arHead;
		$table->sort            = 'lcoe_profile_id';
		$table->autosort        = true;
		$table->form_action	= $this->response->html->thisfile;

		$lcoe_profile_count=1;
		$plugin_icon=$this->openqrm->get('baseurl')."/plugins/linuxcoe/img/plugin.png";
		$lcoe_profile_array = array();
		if (is_dir($this->webdir."/plugins/linuxcoe/profiles/")) {
			$this->folder->getFolders($this->webdir."/plugins/linuxcoe/profiles/");
			foreach ($this->folder->folders as $lcoe_profile) {
					array_push($lcoe_profile_array, $lcoe_profile);
			}
		}
		if(count($lcoe_profile_array) > 0) {
			foreach ($lcoe_profile_array as $lcoe) {
				// check if a comment exists
				if (file_exists($this->webdir."/plugins/linuxcoe/profiles/".$lcoe."/openqrm.info")) {
					$lcoe_profile_comment_str = file_get_contents($this->webdir."/plugins/linuxcoe/profiles/".$lcoe."/openqrm.info");
				}
				// edit button
				$a = $this->response->html->a();
				$a->title   = $this->lang['action_edit'];
				$a->label   = $this->lang['action_edit'];
				$a->handler = 'onclick="wait();"';
				$a->css     = 'edit';
				$a->href    = $this->response->get_url($this->actions_name, "edit").'&linuxcoe_template='.$lcoe;
				$edit_action = $a->get_string();
				
				
				$arBody[] = array(
					'lcoe_profile_icon' => '<img width="24" height="24" src="'.$plugin_icon.'" alt="LinuxCOE Installation Profile">',
					'lcoe_profile_id' => $lcoe_profile_count,
					'lcoe_profile_name' => $lcoe,
					'lcoe_profile_comment' => $lcoe_profile_comment_str,
					'lcoe_profile_edit' => $edit_action,
				);
				$lcoe_profile_count++;
			}
			$table->body = $arBody;
		}
		$table->max = $lcoe_profile_count-1;
		$table->actions_name = $this->actions_name;
		$table->actions = array(array('remove' => $this->lang['action_remove']));
		$table->sort_link = false;
		$table->identifier = 'lcoe_profile_name';
		$table->identifier_name = $this->identifier_name;
		return $table;
	}



}


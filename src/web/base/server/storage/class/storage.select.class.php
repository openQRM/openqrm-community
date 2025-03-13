<?php
/**
 * Storage Select
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class storage_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'storage_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'storage_identifier';
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
		$this->response = $response;
		$this->file     = $openqrm->file();
		$this->openqrm  = $openqrm;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {

		$data = $this->select();
		$t = $this->response->html->template($this->tpldir.'/storage-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->response->get_array());
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['lang_filter'], 'lang_filter');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function select() {

		$d = array();


		$h = array();
		$h['storage_state']['title'] = $this->lang['table_state'];
		$h['storage_state']['sortable'] = false;

		$h['storage_id']['title'] = $this->lang['table_id'];
		$h['storage_id']['hidden'] = true;

		$h['storage_name']['title'] = $this->lang['table_name'];
		$h['storage_name']['hidden'] = true;

		$h['storage_resource_id']['title'] = $this->lang['table_resource'];
		$h['storage_resource_id']['hidden'] = true;

		$h['storage_type']['title'] = $this->lang['table_type'];
		$h['storage_type']['hidden'] = true;

		$h['data']['title'] = '&#160;';
		$h['data']['sortable'] = false;

		$h['storage_comment']['title'] = '&#160;';
		$h['storage_comment']['sortable'] = false;

		$h['storage_edit']['title'] ='&#160;';
		$h['storage_edit']['sortable'] = false;

		$storage = new storage();
		$params  = $this->response->get_array($this->actions_name, 'select');
		$b       = array();

		unset($params['storage_filter']);

		$table = $this->response->html->tablebuilder('storage', $params);
		$table->offset = 0;
		$table->sort = 'storage_id';
		$table->limit = 20;
		$table->order = 'ASC';
		$table->max = $storage->get_count();
		$table->init();

		$storages = $storage->display_overview(0, 10000, $table->sort, $table->order);

		// handle table params
		$tps = $table->get_params();
		$tp = '';
		foreach($tps['storage'] as $k => $v) {
			$tp .= '&storage['.$k.']='.$v;
		}

		$i = 0;
		$deployment = new deployment();
		foreach ($storages as $key => $value) {
			$storage = new storage();
			$storage->get_instance_by_id($value["storage_id"]);
			
			$resource = new resource();
			$resource->get_instance_by_id($storage->resource_id);
			
			$deployment = new deployment();
			$deployment->get_instance_by_id($storage->type);
			
			$resource_icon_default = "/img/resource.png";
			$storage_icon = '/plugins/'.$deployment->storagetype.'/img/plugin.png';
			$state_icon = '<span class="pill '.$resource->state.'">'.$resource->state.'</span>';
			
			if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
				$resource_icon_default = $storage_icon;
			}
			$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;

			$data  = '<b>'.$this->lang['table_id'].':</b> '.$value["storage_id"].'<br>';
			$data .= '<b>'.$this->lang['table_name'].':</b> '.$value["storage_name"].'<br>';
			$data .= '<b>'.$this->lang['table_resource'].':</b> '.$resource->id.' / '.$resource->ip.'<br>';
			$data .= '<b>'.$this->lang['table_type'].':</b> '.$deployment->storagetype.'<br>';
			$data .= '<b>'.$this->lang['table_deployment'].':</b> '.$deployment->storagedescription;

			$a = $this->response->html->a();
			$a->title   = $this->lang['action_edit'];
			$a->label   = $this->lang['action_edit'];
			$a->handler = 'onclick="wait();"';
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($this->actions_name, "edit").'&storage_id='.$storage->id.''.$tp;
			$edit       = $a->get_string();

			#$url  = $this->openqrm->get('baseurl').'/index.php?plugin='.$deployment->storagetype.'&'.str_replace('-', '_',$deployment->storagetype).'_action=edit&storage_id='.$value["storage_id"];

			$url = $this->response->get_url($this->actions_name, 'load').'&splugin='.$deployment->storagetype.'&'.str_replace('-', '_',$deployment->storagetype).'_action=edit&storage_id='.$value["storage_id"];
			if( $deployment->storagetype === 'equallogic-storage' ||
				$deployment->storagetype === 'netapp-storage' ||
				$deployment->storagetype === 'vbox'
			) {
				$url = $this->openqrm->get('baseurl').'/index.php?plugin='.$deployment->storagetype.'&currenttab=tab0&action=select&identifier[]='.$storage->id;
			}
			$a = $this->response->html->a();
			$a->title   = $this->lang['action_mgmt'];
			$a->label   = $this->lang['action_mgmt'];
			$a->handler = 'onclick="wait();"';
			$a->css     = 'manage';
			$a->href    = $url;
			// no mgmt for local-server resources
			if (strstr($storage->capabilities, "TYPE=local-server")) {
				$mgmt = '&#160;';
			} else {
				$mgmt = $a->get_string();
			}
			if (
				$this->response->html->request()->get('storage_filter') === '' ||
				strstr($this->response->html->request()->get('storage_filter'), $deployment->storagetype )
			) {
				$b[] = array(
					'storage_state' => $state_icon,
					'storage_id' => $value["storage_id"],
					'storage_name' => $value["storage_name"],
					'storage_type' => '',
					'storage_resource_id' => "",
					'data' => $data,
					'storage_comment' => $value["storage_comment"],
					'storage_edit' => $edit.$mgmt,
				);
			}
			$i++;
		}

		
		$list = $deployment->get_storagetype_list();
		$filter = array();
		$filter[] = array('', '');
		foreach( $list as $l) {
			$filter[] = array( $l['value'], ucfirst($l['label']));

		}
		asort($filter);

		$select = $this->response->html->select();
		$select->add($filter, array(0,1));
		$select->name = 'storage_filter';
		$select->handler = 'onchange="wait();this.form.submit();return false;"';
		$select->selected = array($this->response->html->request()->get('storage_filter'));
		$box = $this->response->html->box();
		$box->add($select);
		$box->id = 'storages_filter';
		$box->css = 'htmlobject_box';
		$box->label = $this->lang['lang_filter'];

		$add = $this->response->html->a();
		$add->title   = $this->lang['action_add'];
		$add->label   = $this->lang['action_add'];
	//	$add->handler = 'onclick="wait();"';
		$add->css     = 'add';
		$add->href    = $this->response->get_url($this->actions_name, "add").''.$tp;
		
		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->autosort = false;
		$table->sort_link = false;
		$table->head = $h;
		$table->body = $b;
		$table->max = count($b);
		$table->actions_name = $this->actions_name;
		$table->actions = array(array('remove' => $this->lang['action_remove']));
		$table->identifier = 'storage_id';
		$table->identifier_name = $this->identifier_name;
		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 50, "text" => 50),
			array("value" => 100, "text" => 100),
		);

		$d['form']   = $this->response->get_form($this->actions_name, 'select', false)->get_elements();
		$d['add']    = $add->get_string();
		$d['filter'] = $box->get_string();
		$d['table']  = $table;
		return $d;
	}

}

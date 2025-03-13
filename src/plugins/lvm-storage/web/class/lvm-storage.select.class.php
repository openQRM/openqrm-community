<?php
/**
 * lvm-Storage Select Storage
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class lvm_storage_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'lvm_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "lvm_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'lvm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'lvm_identifier';
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
		$this->openqrm = $openqrm;
		$this->file = $this->openqrm->file();
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
		$table = $this->select();
		$t = $this->response->html->template($this->tpldir.'/lvm-storage-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($table, 'table');
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_tablebulider | htmlobject_div
	 */
	//--------------------------------------------
	function select() {
		// set ENV
		$deployment = new deployment();
		$storage    = new storage();
		$resource   = new resource();

		$type = $this->response->html->request()->get('storage_type');
		if($type === '') {
			$type = 'lvm-lvm-deployment';
		}
		$deployment->get_instance_by_type($type);
		$storages = $storage->display_overview(0, $storage->get_count(), 'storage_id', 'ASC');
		if(count($storages) >= 1) {
			foreach($storages as $k => $v) {
				$storage->get_instance_by_id($v["storage_id"]);
				$resource->get_instance_by_id($storage->resource_id);
				$deployment->get_instance_by_id($storage->type);
				if($deployment->storagetype === 'lvm-storage') {
					$resource_icon_default="/img/resource.png";
					$storage_icon="/plugins/lvm-storage/img/plugin.png";
					$state_icon = '<span class="pill">'.$resource->state.'</span>';
					if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
						$resource_icon_default=$storage_icon;
					}
					$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;
					$a = $this->response->html->a();
					$a->title   = $this->lang['action_edit'];
					$a->label   = $this->lang['action_edit'];
					$a->handler = 'onclick="wait();"';
					$a->css     = 'edit';
					if(($deployment->name === 'custom-iscsi-deployment') || ($deployment->name === 'custom-nfs-deployment')) {
						$a->href    = $this->response->get_url($this->actions_name, "custom").'&storage_id='.$storage->id;
					} else {
						$a->href    = $this->response->get_url($this->actions_name, "edit").'&storage_id='.$storage->id;
					}

					$data  = '<strong>'.$this->lang['table_recource'].':</strong> '.$resource->id.' / '.$resource->ip.'<br>';
					$data .= '<strong>'.$this->lang['table_type'].':</strong> <span class="pill">'.$deployment->storagetype.'</span><br>';
					$data .= '<strong>'.$this->lang['table_deployment'].':</strong> '.$deployment->storagedescription.'<br>';

					$b[] = array(
						'state' => $state_icon,
					//	'icon' => '<img width="24" height="24" src="'.$resource_icon_default.'" alt="Icon">',
						'storage_id' => $storage->id,
						'name' => $storage->name,
						'storage_resource_id' => $storage->resource_id,
						'deployment' => $deployment->storagedescription,
						'storage_data' => $data,
						'storage_comment' => '',
						'edit' => $a->get_string(),
					);
				}
			}

			if(isset($b) && is_array($b) && count($b) >= 1) {
				$h = array();
				$h['state'] = array();
				$h['state']['title'] ='&#160;';
				$h['state']['sortable'] = false;
/*
				$h['icon'] = array();
				$h['icon']['title'] ='&#160;';
				$h['icon']['sortable'] = false;
*/
				$h['storage_id'] = array();
				$h['storage_id']['title'] = $this->lang['table_id'];
				$h['name'] = array();
				$h['name']['title'] = $this->lang['table_name'];
				$h['storage_resource_id'] = array();
				$h['storage_resource_id']['title'] = $this->lang['table_recource'];
				$h['storage_resource_id']['hidden'] = true;
				$h['storage_data'] = array();
				$h['storage_data']['title'] = '&#160;';
				$h['storage_data']['sortable'] = false;
				$h['deployment'] = array();
				$h['deployment']['title'] = $this->lang['table_deployment'];
				$h['deployment']['hidden'] = true;
				$h['storage_comment'] = array();
				$h['storage_comment']['title'] ='&#160;';
				$h['storage_comment']['sortable'] = false;
				$h['edit'] = array();
				$h['edit']['title'] = '&#160;';
				$h['edit']['sortable'] = false;

				$table = $this->response->html->tablebuilder('lvm', $this->response->get_array($this->actions_name, 'select'));
				$table->sort      = 'storage_id';
				$table->limit     = 10;
				$table->order     = 'ASC';
				$table->max       = count($b);
				$table->autosort  = false;
				$table->sort_link = false;
				$table->autosort  = true;
				$table->id = 'Tabelle';
				$table->css = 'htmlobject_table';
				$table->border = 1;
				$table->cellspacing = 0;
				$table->cellpadding = 3;
				$table->form_action = $this->response->html->thisfile;
				$table->head = $h;
				$table->body = $b;
				return $table->get_string();
			} else {
				$a = $this->response->html->a();
				$a->title   = $this->lang['new_storage'];
				$a->label   = $this->lang['new_storage'];
				$a->handler = 'onclick="wait();"';
				$a->css     = 'add';
				$a->href    = $this->response->html->thisfile.'?base=storage&storage_action=add';

				$box = $this->response->html->div();
				$box->id = 'Tabelle';
				$box->css = 'htmlobject_box';
				$content  = $this->lang['error_no_storage'].'<br><br>';
				$content .= $a->get_string();
				$box->add($content);
				return $box->get_string();
			}
		} else {
			$a = $this->response->html->a();
			$a->title   = $this->lang['new_storage'];
			$a->label   = $this->lang['new_storage'];
			$a->handler = 'onclick="wait();"';
			$a->css     = 'add';
			$a->href    = $this->response->html->thisfile.'?base=storage&storage_action=add';

			$box = $this->response->html->div();
			$box->id = 'Tabelle';
			$box->css = 'htmlobject_box';
			$content  = $this->lang['error_no_storage'].'<br><br>';
			$content .= $a->get_string();
			$box->add($content);
			return $box->get_string();
		}
	}

}

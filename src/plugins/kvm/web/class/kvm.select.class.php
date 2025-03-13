<?php
/**
 * KVM Select Storage
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kvm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'kvm_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kvm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kvm_identifier';
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
		$table = $this->select();
		$t = $this->response->html->template($this->tpldir.'/kvm-select.tpl.php');
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
		$type       = $this->response->html->request()->get('storage_type');
		if($type === '') {
			$type = 'kvm';
		}
		$deployment->get_instance_by_type($type);
		$storages = $storage->display_overview(0, $storage->get_count(), 'storage_id', 'ASC');
		$b = array();
		if(count($storages) >= 1) {
			foreach($storages as $k => $v) {
				$storage->get_instance_by_id($v["storage_id"]);
				$resource->get_instance_by_id($storage->resource_id);
				$deployment->get_instance_by_id($storage->type);
				if($deployment->storagetype === 'kvm') {
					$resource_icon_default = "/img/resource.png";
					$storage_icon = "/plugins/kvm/img/plugin.png";
					$state_icon = $this->openqrm->get('baseurl')."/img/".$resource->state.".png";
					if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
						$resource_icon_default = $storage_icon;
					}
					$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;

					$a = $this->response->html->a();
					$a->title   = $this->lang['action_edit'];
					$a->label   = $this->lang['action_edit'];
					$a->handler = 'onclick="wait();"';
					$a->css     = 'edit';
					$a->href    = $this->response->get_url($this->actions_name, "edit").'&storage_id='.$storage->id;

					$data  = '<b>'.$this->lang['table_recource'].':</b> '.$resource->id.' / '.$resource->ip.'<br>';
					$data .= '<b>'.$this->lang['table_type'].':</b> '.$deployment->storagetype.'<br>';
					$data .= '<b>'.$this->lang['table_deployment'].':</b> '.$deployment->storagedescription.'<br>';

					$b[] = array(
						'state' => '<img width="24" height="24" src="'.$state_icon.'" alt="State">',
						'icon' => '<img width="24" height="24" src="'.$resource_icon_default.'" alt="Icon">',
						'storage_id' => $storage->id,
						'name' => $storage->name,
						'storage_resource_id' => $storage->resource_id,
						'deployment' => $deployment->storagedescription,
						'data' => $data,
						'comment' => '',
						'edit' => $a->get_string(),
					);
				}
			}

			$h = array();
			$h['state'] = array();
			$h['state']['title'] ='&#160;';
			$h['state']['sortable'] = false;
			$h['icon'] = array();
			$h['icon']['title'] ='&#160;';
			$h['icon']['sortable'] = false;
			$h['storage_id'] = array();
			$h['storage_id']['title'] = $this->lang['table_id'];
			$h['name'] = array();
			$h['name']['title'] = $this->lang['table_name'];
			$h['storage_resource_id'] = array();
			$h['storage_resource_id']['title'] = $this->lang['table_recource'];
			$h['storage_resource_id']['hidden'] = true;
			$h['data'] = array();
			$h['data']['title'] = '&#160;';
			$h['data']['sortable'] = false;
			$h['deployment'] = array();
			$h['deployment']['title'] = $this->lang['table_deployment'];
			$h['deployment']['hidden'] = true;
			$h['comment'] = array();
			$h['comment']['title'] ='&#160;';
			$h['comment']['sortable'] = false;
			$h['edit'] = array();
			$h['edit']['title'] = '&#160;';
			$h['edit']['sortable'] = false;

			$table = $this->response->html->tablebuilder('kvm', $this->response->get_array($this->actions_name, 'select'));
			$table->sort      = 'storage_id';
			$table->limit     = 10;
			$table->offset    = 0;
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
	}

}

<?php
/**
 * lvm-Storage Edit Storage
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class lvm_storage_edit
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
* identifier name
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
	 * Init
	 *
	 * @access public
	 */
	//--------------------------------------------
	function init() {
		$storage_id = $this->response->html->request()->get('storage_id');
		if($storage_id === '') {
			return false;
		}
		// set ENV
		$deployment = new deployment();
		$storage    = new storage();
		$resource   = new resource();

		$storage->get_instance_by_id($storage_id);
		$resource->get_instance_by_id($storage->resource_id);
		$deployment->get_instance_by_id($storage->type);

		$this->resource   = $resource;
		$this->storage    = $storage;
		$this->deployment = $deployment;

		$this->statfile = $this->openqrm->get('basedir').'/plugins/lvm-storage/web/storage/'.$resource->id.'.vg.stat';
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
		$this->init();
		$data = $this->edit();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/lvm-storage-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_deployment'], 'lang_deployment');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			$t->add($this->openqrm->get('baseurl'), 'baseurl');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_lvm'], $this->response->html->request()->get('storage_id'));
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
			);
		}
	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function edit() {
		if(strpos($this->deployment->type, 'lvm') !== false) {
			// check device-manager
			$devicemgm = false;
			if($this->file->exists($this->openqrm->get('webdir').'/plugins/device-manager/class/device-manager.addvg.class.php')) {
				$devicemgm = true;
			}
			$resource_icon_default="/img/resource.png";
			$storage_icon="/plugins/lvm-storage/img/plugin.png";
			$state_icon = '<span class="pill">'.$this->resource->state.'</span>';
			if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
				$resource_icon_default=$storage_icon;
			}
			$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;

			$d['state'] = $state_icon;
		//	$d['icon'] = '<img width="24" height="24" src="'.$resource_icon_default.'">';
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['deployment'] = $this->deployment->type;
			$d['name'] = $this->storage->name;
			$d['id'] = $this->storage->id;

			$body = array();
			$file = $this->statfile;
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					$i = 0;
					foreach($lines as $line) {
						if($line !== '') {
							$line  = explode('@', $line);
							$name  = substr($line[0], strripos($line[0], '/'));
							$vsize = number_format(substr($line[5], 0, strpos($line[5], '.')), 0, '', '').' MB';
							$vfree = str_replace('m', '', $line[6]);
							if($vfree !== '0') {
								$vfree = number_format(substr($line[6], 0, strpos($line[6], '.')), 0, '', '');
							}
							$a = $this->response->html->a();
							$a->title   = $this->lang['action_edit'];
							$a->label   = $this->lang['action_edit'];
							$a->handler = 'onclick="wait();"';
							$a->css     = 'edit';
							$a->href    = $this->response->get_url($this->actions_name, "volgroup").'&volgroup='.$name;
							$body[$i] = array(
							//	'icon' => $d['icon'],
								'name'   => $name,
								'pv' => $line[1],
								'lv' => $line[2],
								'sn' => $line[3],
								'attr' => $line[4],
								'vsize' => $vsize,
								'vfree' => $vfree.' MB',
								'edit' => $a->get_string(),
							);
							if($devicemgm === true) {
								if($line[2] === '0' && $line[3] === '0') {
									$a = $this->response->html->a();
									$a->title   = $this->lang['action_remove'];
									$a->label   = $this->lang['action_remove'];
									$a->handler = 'onclick="wait();"';
									$a->css     = 'remove';
									$a->href    = $this->response->get_url($this->actions_name, "removevg").'&volgroup='.$name;
									$body[$i]['remove'] = $a->get_string();
								} else {
									$body[$i]['remove'] = '&#160;';
								}
							}
							$i++;
						}
					}
				}
			}

/*
			$h['icon']['title'] = '&#160;';
			$h['icon']['sortable'] = false;
*/
			$h['name']['title'] = $this->lang['table_name'];
			$h['pv']['title'] = $this->lang['table_pv'];
			$h['lv']['title'] = $this->lang['table_lv'];
			$h['sn']['title'] = $this->lang['table_sn'];
			$h['attr']['title'] = $this->lang['table_attr'];
			$h['vsize']['title'] = $this->lang['table_vsize'];
			$h['vfree']['title'] = $this->lang['table_vfree'];
			$h['edit']['title'] = '&#160;';
			$h['edit']['sortable'] = false;

			if($devicemgm === true) {
				$h['remove']['title'] = '&#160;';
				$h['remove']['sortable'] = false;
			}

			$table = $this->response->html->tablebuilder('lvm_edit', $this->response->get_array($this->actions_name, 'edit'));
			$table->sort            = 'name';
			$table->limit           = 10;
			$table->offset          = 0;
			$table->order           = 'ASC';
			$table->max             = count($body);
			$table->autosort        = true;
			$table->sort_link       = false;
			$table->id              = 'Tabelle';
			$table->css             = 'htmlobject_table';
			$table->border          = 1;
			$table->cellspacing     = 0;
			$table->cellpadding     = 3;
			$table->form_action     = $this->response->html->thisfile;
			$table->head            = $h;
			$table->body            = $body;

			$d['add'] = '';
			if($devicemgm === true) {
				$a = $this->response->html->a();
				$a->title   = $this->lang['action_add'];
				$a->label   = $this->lang['action_add'];
				$a->handler = 'onclick="wait();"';
				$a->css     = 'add';
				$a->href    = $this->response->get_url($this->actions_name, "addvg");
				$d['add'] = $a->get_string();
			}

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}

}

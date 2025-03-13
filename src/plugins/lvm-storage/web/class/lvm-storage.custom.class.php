<?php
/**
 * lvm-Storage Custom Storage
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class lvm_storage_custom
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
		$data = $this->custom();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/lvm-storage-custom.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_deployment'], 'lang_deployment');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->lang['label'], 'label');
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
	 * custom
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function custom() {
		if(strpos($this->deployment->type, 'custom-') !== false) {
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

			$i = 0;
			$body = array();
			$image = new image();
			$images = $image->display_overview(0, $image->get_count(), 'image_id', 'ASC');
			if(count($images) >= 1) {
				foreach($images as $k => $v) {
					$image->get_instance_by_id($v["image_id"]);
					if ($image->storageid == $this->storage->id) {
						$name = $image->name;
						$initiator = $image->get_deployment_parameter('INITIATOR');
						$username = $image->get_deployment_parameter('USER');
						if ($this->deployment->name == 'custom-iscsi-deployment') {
							$root_device_arr = explode("/", $image->rootdevice);
							$target = $root_device_arr[2];
							$lun = $root_device_arr[3];
						}
						if ($this->deployment->name == 'custom-nfs-deployment') {
							$target = $image->rootdevice;
							$lun = 'nfs';
						}

						$body[$i] = array(
						//	'icon' => $d['icon'],
							'name'   => $name,
							'target'   => $target,
							'lun'   => $lun,
							'initiator' => $initiator,
							'username' => $username,
						);
						$i++;
					}
				}
			}

			$h['name']['title'] = $this->lang['table_name'];
			$h['target']['title'] = $this->lang['table_target']." / ".$this->lang['table_export'];
			$h['target']['sortable'] = true;
			$h['lun']['title'] = $this->lang['table_lun'];
			$h['lun']['sortable'] = true;
			$h['initiator']['title'] = $this->lang['table_initiator'];
			$h['initiator']['sortable'] = true;
			$h['username']['title'] = $this->lang['table_username'];
			$h['username']['sortable'] = true;

			$table = $this->response->html->tablebuilder('lvm_custom', $this->response->get_array($this->actions_name, 'custom'));
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

			$table->identifier          = 'name';
			$table->identifier_name     = $this->identifier_name;
			$table->actions_name        = $this->actions_name;
			$table->actions             = array(array('customremove' => $this->lang['action_remove']));
			
			
			$a = $this->response->html->a();
			$a->title   = $this->lang['action_add'];
			$a->label   = $this->lang['action_add'];
			$a->handler = 'onclick="wait();"';
			$a->css     = 'add';
			$a->href    = $this->response->get_url($this->actions_name, "customadd");
			$d['add'] = $a->get_string();

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}

}

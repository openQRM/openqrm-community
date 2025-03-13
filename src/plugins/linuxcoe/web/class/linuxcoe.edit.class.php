<?php
/**
 * Local-Storage Edit Storage
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class linuxcoe_edit
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
		require_once($this->openqrm->get('basedir').'/plugins/linuxcoe/web/class/linuxcoe-volume.class.php');
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
			$t = $this->response->html->template($this->tpldir.'/linuxcoe-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->lang['lang_vfree'], 'lang_vfree');
			$t->add($this->lang['lang_vsize'], 'lang_vsize');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			$t->add($this->openqrm->get('baseurl'), 'baseurl');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_local'], $this->response->html->request()->get('storage_id'));
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
		if($this->deployment->type === 'linuxcoe-deployment') {
			$resource_icon_default="/img/resource.png";
			$storage_icon="/plugins/linuxcoe/img/plugin.png";
			$state_icon = $this->openqrm->get('baseurl')."/img/".$this->resource->state.".png";
			if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
				$resource_icon_default=$storage_icon;
			}
			$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;

			$d['state'] = '<img width="24" height="24" src="'.$state_icon.'">';
			$d['icon'] = '<img width="24" height="24" src="'.$resource_icon_default.'">';
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->storage->name;
			$d['id'] = $this->storage->id;

			$a = $this->response->html->a();
			$a->label = $this->lang['action_add'];
			$a->css   = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "add");
			$d['add'] = $a->get_string();

			$body = array();
			$identifier_disabled = array();

			$table = $this->response->html->tablebuilder('linuxcoe_edit', $this->response->get_array($this->actions_name, 'edit'));
			$table->sort            = 'linuxcoe_volume_id';
			$table->limit           = 10;
			$table->offset          = 0;
			$table->order           = 'ASC';
			$table->autosort        = true;
			$table->sort_link       = false;
			$table->init();

			$linuxcoe_volume = new linuxcoe_volume();
			$linuxcoe_volume_arr = $linuxcoe_volume->display_overview(0, 10000, $table->sort, $table->order);
			if(count($linuxcoe_volume_arr) >= 1) {
				foreach($linuxcoe_volume_arr as $k => $v) {

					$c = $this->response->html->a();
					$c->title   = $this->lang['action_clone'];
					$c->label   = $this->lang['action_clone'];
					$c->handler = 'onclick="wait();"';
					$c->css     = 'clone';
					$c->href    = $this->response->get_url($this->actions_name, "clone").'&volume='.$v['linuxcoe_volume_name'];

					// edit image
					$local_image = new image();
					$local_image->get_instance_by_name($v['linuxcoe_volume_name']);
					$e = $this->response->html->a();
					$e->title   = $this->lang['action_edit'];
					$e->label   = $this->lang['action_edit'];
					$e->handler = 'onclick="wait();"';
					$e->css     = 'edit';
					$e->href    = '/openqrm/base/index.php?base=image&image_action=edit&image_id='.$local_image->id;

					$body[] = array(
						'icon' => $d['icon'],
						'linuxcoe_volume_id' => $v['linuxcoe_volume_id'],
						'linuxcoe_volume_name'   => $v['linuxcoe_volume_name'],
						'linuxcoe_volume_root'   => $v['linuxcoe_volume_root'],
						'description' => $v['linuxcoe_volume_description'],
						'clone' => $c,
						'edit' => $e,
					);
				}
			}

			$h['icon']['title'] = '&#160;';
			$h['icon']['sortable'] = false;
			$h['linuxcoe_volume_id']['title'] = $this->lang['table_id'];
			$h['linuxcoe_volume_name']['title'] = $this->lang['table_name'];
			$h['linuxcoe_volume_root']['title'] = $this->lang['table_root'];
			$h['description']['title'] = $this->lang['table_description'];
			$h['description']['sortable'] = false;
			$h['clone']['title'] = '&#160;';
			$h['clone']['sortable'] = false;
			$h['edit']['title'] = '&#160;';
			$h['edit']['sortable'] = false;

			$table->id              = 'Tabelle';
			$table->css             = 'htmlobject_table';
			$table->border          = 1;
			$table->cellspacing     = 0;
			$table->cellpadding     = 3;
			$table->form_action	    = $this->response->html->thisfile;
			$table->max             = count($linuxcoe_volume_arr);
			$table->head            = $h;
			$table->body            = $body;
			$table->identifier      = 'linuxcoe_volume_name';
			$table->identifier_name = $this->identifier_name;
			$table->identifier_disabled = $identifier_disabled;
			$table->actions_name    = $this->actions_name;
			$table->actions         = array(array('remove' => $this->lang['action_remove']));

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}

}

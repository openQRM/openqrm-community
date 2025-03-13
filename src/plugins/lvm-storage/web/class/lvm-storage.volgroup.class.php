<?php
/**
 * LVM-Storage Edit Storage
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class lvm_storage_volgroup
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
		$this->volgroup = $this->response->html->request()->get('volgroup');
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
		$this->response->add('storage_id', $storage_id);
		$this->response->add('volgroup', $this->volgroup);

		$this->statfile = $this->openqrm->get('basedir').'/plugins/lvm-storage/web/storage/'.$resource->id.'.'.$this->volgroup.'.lv.stat';
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
			$t = $this->response->html->template($this->tpldir.'/lvm-storage-volgroup.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_deployment'], 'lang_deployment');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->lang['lang_attr'], 'lang_attr');
			$t->add($this->lang['lang_pv'], 'lang_pv');
			$t->add($this->lang['lang_size'], 'lang_size');
			$t->add(sprintf($this->lang['label'], $this->volgroup, $data['name']), 'label');
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
			$resource_icon_default="/img/resource.png";
			$storage_icon="/plugins/lvm-storage/img/plugin.png";
			$state_icon = '<span class="pill">'.$this->resource->state.'</span>';
			if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
				$resource_icon_default=$storage_icon;
			}
			$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;

			// Storage info
			$d['state'] = $state_icon;
		//	$d['icon'] = "<img width=24 height=24 src=$resource_icon_default>";
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->storage->name;
			$d['deployment'] = $this->deployment->type;
			$d['id'] = $this->storage->id;

			// Volgroup info
			$lines = explode("\n", file_get_contents($this->openqrm->get('basedir').'/plugins/lvm-storage/web/storage/'.$this->resource->id.'.vg.stat'));
			foreach($lines as $line) {
				$line = explode("@", $line);
				if(isset($line[0]) && $line[0] === $this->volgroup) {
					$vsize = number_format(substr($line[5], 0, strpos($line[5], '.')), 0, '', '').' MB';
					$vfree = str_replace('m', '', $line[6]);
					if($vfree !== '0') {
						$vfree = substr($line[6], 0, strpos($line[6], '.'));
					}
					$d['volgroup_name'] = $line[0];
					$d['volgroup_pv'] = $line[1];
					$d['volgroup_lv'] = $line[2];
					$d['volgroup_sn'] = $line[3];
					$d['volgroup_attr'] = $line[4];
					$d['volgroup_vsize'] = $vsize;
					$d['volgroup_vfree'] = number_format($vfree, 0, '', '').' MB';
				}
			}

			$a = '&#160';
			if($d['volgroup_vfree'] !== '0 MB') {
				$a = $this->response->html->a();
				$a->label = $this->lang['action_add'];
				$a->css   = 'add';
				$a->href  = $this->response->get_url($this->actions_name, "add");
			}
			$d['add'] = $a;

			$body = array();

			$file = $this->statfile;
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					$disabled = array();
					$t = $this->response->html->template($this->openqrm->get('webdir').'/js/openqrm-progressbar.js');
					foreach($lines as $line) {
						if($line !== '') {
							$s    = '&#160;';
							$c    = '&#160;';
							$r    = '&#160;';
							$src  = '&#160;';
							$line = explode('@', $line);
							$name = $line[1];
							$mode = substr($line[3], 0, 1);
							if ($line[4] == "clone_in_progress") {
								// add to disabled identifier
								$disabled[] = $name;
								// progressbar
								$t->add(uniqid('b', true), 'id');
								$t->add($this->openqrm->get('baseurl').'/api.php?action=plugin&plugin=lvm-storage&lvm_storage_action=progress&name='.$this->resource->id.'.lvm.'.$name.'.sync_progress', 'url');
								$t->add($this->lang['action_clone_in_progress'], 'lang_in_progress');
								$t->add($this->lang['action_clone_finished'], 'lang_finished');
								$volume_size = $t->get_string();
							} else if ($line[4] == "sync_in_progress") {
								$volume_size = $this->lang['action_sync_in_progress'];
								$disabled[] = $name;
							} else {
								$volume_size = number_format(substr($line[4], 0, strpos($line[4], '.')), 0, '', '').' MB';
								$image_add_remove = '';
								$deployment_type = '';
								$image = new image();
								$image->get_instance_by_name($name);
								if (strlen($image->id)) {
									if( $image->type == $this->deployment->type ) {
										if( $line[0] === $this->deployment->type ) {
											if($d['volgroup_vfree'] !== '0 MB' ) {
												if($mode !== 's') {
													$s = $this->response->html->a();
													$s->title   = $this->lang['action_snap'];
													$s->label   = $this->lang['action_snap'];
													$s->handler = 'onclick="wait();"';
													$s->css     = 'snap';
													$s->href    = $this->response->get_url($this->actions_name, "snap").'&lvol='.$line[1];
												} else {
													$disabled[] = $line[5];
													$src = $line[5];
												}
												if($vfree >= (int)substr($line[4], 0, strpos($line[4], '.'))) {
													$c = $this->response->html->a();
													$c->title   = $this->lang['action_clone'];
													$c->label   = $this->lang['action_clone'];
													$c->handler = 'onclick="wait();"';
													$c->css     = 'clone';
													$c->href    = $this->response->get_url($this->actions_name, "clone").'&lvol='.$line[1];
												}
											}
											$r = $this->response->html->a();
											$r->title   = $this->lang['action_resize'];
											$r->label   = $this->lang['action_resize'];
											$r->handler = 'onclick="wait();"';
											$r->css     = 'resize';
											$r->href    = $this->response->get_url($this->actions_name, "resize").'&lvol='.$line[1];
											$deployment_type = $this->deployment->type;
										} else {
											$disabled[] = $name;
										}
									}
								}
								// create/remove image object, check if image exists
								if (strlen($image->id)) {
									if( $image->type != $this->deployment->type ) {
										$deployment_type = $image->type;
										$disabled[] = $name;
									} else {
										$i = $this->response->html->a();
										$i->title   = $this->lang['action_remove_image'];
										$i->label   = $this->lang['action_remove_image'];
										$i->handler = 'onclick="wait();"';
										$i->css     = 'remove';
										$i->href    = $this->response->get_url($this->actions_name, "image").'&image_id='.$image->id.'&image_command=remove';
										$image_add_remove = $i;
									}
								} else {
									$i = $this->response->html->a();
									$i->title   = $this->lang['action_add_image'];
									$i->label   = $this->lang['action_add_image'];
									$i->handler = 'onclick="wait();"';
									$i->css     = 'add';
									if($this->deployment->type === 'lvm-iscsi-deployment') {
										$image_root_device = $this->volgroup.':/dev/'.$name.'/1';
										$i->href    = $this->response->get_url($this->actions_name, "image").'&root_device='.$image_root_device.'&image_name='.$name.'&image_command=add';
									} else if($d['deployment'] === 'lvm-aoe-deployment') {
										$i->href    = $this->response->get_url($this->actions_name, "image").'&root_device='.$name.'&image_name='.$name.'&image_command=add';
									} else if($d['deployment'] === 'lvm-nfs-deployment') {
										$image_root_device = '/'.$this->volgroup.'/'.$name;
										$i->href    = $this->response->get_url($this->actions_name, "image").'&root_device='.$image_root_device.'&image_name='.$name.'&image_command=add';
									}
									$disabled[] = $name;
									$image_add_remove = $i;
								}
							}

							$body[] = array(
								//'icon'   => $d['icon'],
								'deploy' => $deployment_type,
								'name'   => $name,
								'attr'   => $line[3],
								'source' => $src,
								'size'   => $volume_size,
								'image'   => $image_add_remove,
								'snap'   => $s,
								'clone'  => $c,
								'resize' => $r,
							);
						}
					}
				}
			}

/*
			$h['icon']['title']      = '&#160;';
			$h['icon']['sortable']   = false;
*/
			$h['name']['title']      = $this->lang['table_name'];
			$h['deploy']['title']    = $this->lang['table_deployment'];
			$h['attr']['title']      = $this->lang['table_attr'];
			$h['source']['title']    = $this->lang['table_source'];
			$h['size']['title']      = $this->lang['table_size'];
			$h['image']['title']      = '&#160;';
			$h['image']['sortable']   = false;
			$h['snap']['title']      = '&#160;';
			$h['snap']['sortable']   = false;
			$h['clone']['title']     = '&#160;';
			$h['clone']['sortable']  = false;
			$h['resize']['title']    = '&#160;';
			$h['resize']['sortable'] = false;

			$table = $this->response->html->tablebuilder('lvm_lvols', $this->response->get_array($this->actions_name, 'volgroup'));
			$table->sort                = 'name';
			$table->limit               = 10;
			$table->offset              = 0;
			$table->order               = 'ASC';
			$table->max                 = count($body);
			$table->autosort            = true;
			$table->sort_link           = false;
			$table->id                  = 'Tabelle';
			$table->css                 = 'htmlobject_table';
			$table->border              = 1;
			$table->cellspacing         = 0;
			$table->cellpadding         = 3;
			$table->form_action         = $this->response->html->thisfile;
			$table->head                = $h;
			$table->body                = $body;
			$table->identifier          = 'name';
			$table->identifier_name     = $this->identifier_name;
			$table->identifier_disabled = $disabled;
			$table->actions_name        = $this->actions_name;
			$table->actions             = array(array('remove' => $this->lang['action_remove']));

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}

}

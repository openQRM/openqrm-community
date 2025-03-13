<?php
/**
 * NFS-Storage Edit Storage
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class nfs_storage_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'nfs_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "nfs_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'nfs_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'nfs_identifier';
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

		if (!$this->file->exists($this->openqrm->get('basedir').'/plugins/nfs-storage/web/storage/'.$resource->id.'.nfs.stat.manual')) {
			$this->statfile   = $this->openqrm->get('basedir').'/plugins/nfs-storage/web/storage/'.$resource->id.'.nfs.stat';
		} else {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'manual', $this->message_param, $this->lang['manual_configured'])
			);
		}
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
			$t = $this->response->html->template($this->tpldir.'/nfs-storage-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->lang['lang_vfree'], 'lang_vfree');
			$t->add($this->lang['lang_vsize'], 'lang_vsize');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_nfs'], $this->response->html->request()->get('storage_id'));
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

		if($this->deployment->type === 'nfs-deployment') {
			$resource_icon_default="/img/resource.png";
			$storage_icon="/plugins/nfs-storage/img/plugin.png";
			if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
				$resource_icon_default=$storage_icon;
			}
			$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;

			$d['state'] = '<span class="pill">'.$this->resource->state.'</span>';
			$d['icon'] = '<img width="24" height="24" src="'.$resource_icon_default.'">';
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->storage->name;
			$d['id'] = $this->storage->id;

			$a = $this->response->html->a();
			$a->label = $this->lang['action_manual'];
			$a->css   = 'add';
			$a->href  = $this->response->get_url($this->actions_name, "manual");
			$d['manual'] = $a->get_string();

			$a = $this->response->html->a();
			$a->label = $this->lang['action_add'];
			$a->css   = 'add';
			$a->href  = $this->response->get_url($this->actions_name, "add");
			$d['add'] = $a->get_string();

			$body = array();
			$identifier_disabled = array();
			$file = $this->statfile;
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					$i = 0;
					$t = $this->response->html->template($this->openqrm->get('webdir').'/js/openqrm-progressbar.js');
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							if($i === 0) {
								$d['vsize'] = number_format($line[0], 0, '', '').' MB';
								$d['vfree'] = number_format($line[1], 0, '', '').' MB';
							} else {
								$name = basename($line[1]);
								$export = $line[1];
								$authenticated = @$line[2];
								$clone_status = @$line[3];
								$auth_link = '&#160;';
								$clone_link = '&#160;';
								$image_add_remove = '';
								$deployment_type = '';
								$image = new image($this->deployment->type);
								$image->get_instance_by_name($name);
								if (strlen($image->id)) {
									if( $image->type == $this->deployment->type ) {
										if( $line[0] === $this->deployment->type ) {
											if($d['vfree'] !== '0 MB' ) {
												$a = $this->response->html->a();
												$a->title   = $this->lang['action_clone'];
												$a->label   = $this->lang['action_clone'];
												$a->handler = 'onclick="wait();"';
												$a->css     = 'clone';
												$a->href    = $this->response->get_url($this->actions_name, "clone").'&volume='.$name;
												$clone_link = $a->get_string();
											}
											$a = $this->response->html->a();
											$a->title   = $this->lang['action_auth'];
											$a->label   = $this->lang['action_auth'];
											$a->handler = 'onclick="wait();"';
											$a->css     = 'edit';
											$a->href    = $this->response->get_url($this->actions_name, "auth").'&volume='.$name;
											$auth_link = $a->get_string();
										} else {
											$identifier_disabled[] = $name;
										}
									}
								}
								// create/remove image object, check if image exists
								$deployment_type = $this->deployment->type;
								$image = new image($this->deployment->type);
								$image->get_instance_by_name($name);
								if (strlen($image->id)) {
									if( $image->type != $this->deployment->type ) {
										$deployment_type = $image->type;
										$identifier_disabled[] = $name;
										error_log("nfs storage type: disabled: ".$image->type." != ".$this->deployment->type);
									} else {
										$ia = $this->response->html->a();
										$ia->title   = $this->lang['action_remove_image'];
										$ia->label   = $this->lang['action_remove_image'];
										$ia->handler = 'onclick="wait();"';
										$ia->css     = 'edit';
										$ia->href    = $this->response->get_url($this->actions_name, "image").'&image_id='.$image->id.'&image_command=remove';
										$image_add_remove = $ia;
									}
								} else {
									$ia = $this->response->html->a();
									$ia->title   = $this->lang['action_add_image'];
									$ia->label   = $this->lang['action_add_image'];
									$ia->handler = 'onclick="wait();"';
									$ia->css     = 'edit';
									if($this->deployment->type === 'nfs-deployment') {
										$ia->href    = $this->response->get_url($this->actions_name, "image").'&root_device='.$export.'&image_name='.$name.'&image_command=add';
									}
									$identifier_disabled[] = $name;
									$image_add_remove = $ia;
								}

								$export = '';
								if ($authenticated != '') {
									$export = $this->lang['lang_authenticated_to']." ".$authenticated;
								}

								if ($clone_status == "clone_in_progress") {
									// add to disabled identifier
									$identifier_disabled[] = $name;
									$auth_link = "&#160;";
									$clone_link = "&#160;";
									$image_add_remove = '&#160;';
									// progressbar
									$t->add(uniqid('b', true), 'id');
									$t->add($this->openqrm->get('baseurl').'/api.php?action=plugin&plugin=nfs-storage&nfs_storage_action=progress&name='.$this->resource->id.'.nfs.'.$name.'.sync_progress', 'url');
									$t->add($this->lang['action_clone_in_progress'], 'lang_in_progress');
									$t->add($this->lang['action_clone_finished'], 'lang_finished');
									$export = $t->get_string();
								}

								$body[] = array(
									'icon' => $d['icon'],
									'name'   => $name,
									'export'   => $export,
									'image' => $image_add_remove,
									'auth' => $auth_link,
									'clone' => $clone_link,
								);
							}
						}
						$i++;
					}
				}
			}

			$h['icon'] = array();
			$h['icon']['title'] = '&#160;';
			$h['icon']['sortable'] = false;
			$h['name'] = array();
			$h['name']['title'] = $this->lang['table_name'];
			$h['export'] = array();
			$h['export']['title'] = $this->lang['table_export'];
			$h['image'] = array();
			$h['image']['title'] = '&#160;';
			$h['image']['sortable'] = false;
			$h['auth']['title'] = '&#160;';
			$h['auth']['sortable'] = false;
			$h['clone']['title'] = '&#160;';
			$h['clone']['sortable'] = false;

			$table = $this->response->html->tablebuilder('nfs_edit', $this->response->get_array($this->actions_name, 'edit'));
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
			$table->form_action	    = $this->response->html->thisfile;
			$table->head            = $h;
			$table->body            = $body;
			$table->identifier      = 'name';
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

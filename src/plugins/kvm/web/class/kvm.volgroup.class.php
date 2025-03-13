<?php
/**
 * KVM Edit Storage
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_volgroup
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
		$this->file     = $openqrm->file();
		$this->openqrm  = $openqrm;

		$this->response->add('storage_id', $this->response->html->request()->get('storage_id'));
		$this->response->add('volgroup', $this->response->html->request()->get('volgroup'));

		$this->volgroup = $this->response->params['volgroup'];
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

		$this->statfile = $this->openqrm->get('basedir').'/plugins/kvm/web/storage/'.$resource->id.'.'.$this->volgroup.'.lv.stat';
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
			if($this->deployment->type === 'kvm-ceph-deployment') {
				$t = $this->response->html->template($this->tpldir.'/kvm-volgroup-ceph.tpl.php');
			} else {
				$t = $this->response->html->template($this->tpldir.'/kvm-volgroup.tpl.php');
			}
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_free'], 'lang_free');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_deployment'], 'lang_deployment');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->lang['lang_attr'], 'lang_attr');
			$t->add($this->lang['lang_pv'], 'lang_pv');
			$t->add($this->lang['lang_size'], 'lang_size');
			$t->add($this->openqrm->get('baseurl'), 'baseurl');
			$t->add(sprintf($this->lang['label'], $this->volgroup, $data['name']), 'label');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_kvm'], $this->response->html->request()->get('storage_id'));
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
		if(strpos($this->deployment->type, 'kvm') !== false) {
			$resource_icon_default = "/img/resource.png";
			$storage_icon = "/plugins/kvm/img/plugin.png";
			if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
				$resource_icon_default = $storage_icon;
			}
			$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;

			// Storage info
			$d['state'] = '<span class="pill '.$this->resource->state.'">'.$this->resource->state.'</span>';
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->storage->name;
			$d['deployment'] = $this->deployment->type;
			$d['id'] = $this->storage->id;

			// Volgroup info
			$lines = explode("\n", $this->file->get_contents($this->openqrm->get('basedir').'/plugins/kvm/web/storage/'.$this->resource->id.'.vg.stat'));
			foreach($lines as $line) {
				$line = explode("@", $line);
				if(isset($line[0]) && $line[0] === $this->volgroup) {

					//handle format sent by df
					$line[5] = str_replace('MB', '.00', $line[5]);
					$line[6] = str_replace('MB', '.00', $line[6]);
				
					$vsize = number_format(substr($line[5], 0, strpos($line[5], '.')), 0, '', '').' MB';
					if($d['deployment'] === 'kvm-ceph-deployment') {
						$vfree = $line[6];
					} else {
						$vfree = str_replace('m', '', $line[6]);
						if($vfree !== '0') {
							$vfree = substr($line[6], 0, strpos($line[6], '.'));
						}
					}
					$d['volgroup_name'] = $line[0];
					if($d['deployment'] === 'kvm-lvm-deployment') {
						$d['volgroup_pv'] = $line[1];
						$d['volgroup_lv'] = $line[2];
						$d['volgroup_sn'] = $line[3];
						$d['volgroup_attr'] = $line[4];
						$d['volgroup_vsize'] = $vsize;
						$d['volgroup_vfree'] = number_format($vfree, 0, '', '').' MB';
					}
					if($d['deployment'] === 'kvm-bf-deployment') {
						$d['volgroup_pv'] = '-';
						$d['volgroup_lv'] = '-';
						$d['volgroup_sn'] = '-';
						$d['volgroup_attr'] = 'file';
						$d['volgroup_vsize'] = $vsize;
						$d['volgroup_vfree'] = number_format($vfree, 0, '', '').' MB';
					}
					if($d['deployment'] === 'kvm-gluster-deployment') {
						$d['volgroup_pv'] = '-';
						$d['volgroup_lv'] = '-';
						$d['volgroup_sn'] = '-';
						$d['volgroup_attr'] = 'gluster';
						$d['volgroup_vsize'] = $vsize;
						$d['volgroup_vfree'] = number_format($vfree, 0, '', '').' MB';
					}
					if($d['deployment'] === 'kvm-ceph-deployment') {
						$d['volgroup_attr'] = 'ceph';
						$d['volgroup_vfree'] = $line[6].' MB';
					}
				}
			}

			$a = '&#160';
			if($d['volgroup_vfree'] !== '0 MB') {
				$a = $this->response->html->a();
				$a->label   = $this->lang['action_add'];
				$a->css     = 'add';
				$a->handler = 'onclick="wait();"';
				$a->href    = $this->response->get_url($this->actions_name, "add");
			}
			$d['add'] = $a;

			$body = array();

			$file = $this->statfile;
			if($this->file->exists($file)) {
				$lines = explode("\n", $this->file->get_contents($file));
				if(count($lines) >= 1) {
					$disabled = array();
					$t = $this->response->html->template($this->openqrm->get('webdir').'/js/openqrm-progressbar.js');
					foreach($lines as $line) {
						if($line !== '') { 
							$image_add_remove = '';
							$deployment_type = ''; 
							$line = explode('@', $line);
							$name = $line[1];
							$mode = substr($line[3], 0, 1);
							$s = '';
							$c = '';
							$r = '';
							$src = '';
							$progress = '';
							if ($line[4] == "clone_in_progress") {
								// add to disabled identifier
								$disabled[] = $name;
								// progressbar
								$t->add(uniqid('b', true), 'id');
								$t->add($this->openqrm->get('baseurl').'/api.php?action=plugin&plugin=kvm&kvm_action=progress&name='.$this->resource->id.'.lvm.'.$name.'.sync_progress', 'url');
								$t->add($this->lang['action_clone_in_progress'], 'lang_in_progress');
								$t->add($this->lang['action_clone_finished'], 'lang_finished');
								$progress = $t->get_string();
							} else if ($line[4] == "sync_in_progress") {
								$progress = $this->lang['action_sync_in_progress'];
								$disabled[] = $name;
							} else {
								if($d['deployment'] === 'kvm-lvm-deployment') {
									$volume_size = number_format(substr($line[4], 0, strpos($line[4], '.')), 0, '', '');
									$volume_clone_size = (int)substr($line[4], 0, strpos($line[4], '.'));
								}
								if($d['deployment'] === 'kvm-bf-deployment') {
									$volume_size = number_format(($line[4]/(1000*1000)), 0, '', '');
									$volume_clone_size = (int)substr($line[4], 0, strpos($line[4], '.'));
								}
								if($d['deployment'] === 'kvm-gluster-deployment') {
									$volume_size = number_format(($line[4]/(1000*1000)), 0, '', '');
									$volume_clone_size = (int)substr($line[4], 0, strpos($line[4], '.'));
								}
								if($d['deployment'] === 'kvm-ceph-deployment') {
									$volume_size = $line[4];
									$name = str_replace('%', '@', $name);
									$volume_clone_size = $line[4];
								}
								
								$image_add_remove = '';
								$deployment_type = '';
								$image = $this->openqrm->image();
								$image->get_instance_by_name($name);
								if (strlen($image->id)) {
									if( $image->type == $this->deployment->type ) {
										if( $line[0] === $this->deployment->type && $line[4] !== "sync_in_progress" && $line[4] !== "clone_in_progress" ) {
											if($volume_size !== $image->size) {
												$r = $this->response->html->a();
												$r->title   = $this->lang['action_sync'];
												$r->label   = $this->lang['action_sync'];
												$r->handler = 'onclick="wait();"';
												$r->css     = 'enable';
												$r->href    = $this->response->get_url($this->actions_name, "image").'&image_command=sync&image_id='.$image->id.'&size='.$volume_size;
												$r = $r->get_string();
												$volume_size = '<span class="error" style="cursor:pointer;" title="Wrong size '.$image->size.' for image object">'.$volume_size.'</span>';
											}
											else if($d['volgroup_vfree'] !== '0 MB' ) {
												if($mode !== 's') {
													$s = $this->response->html->a();
													$s->title   = $this->lang['action_snap']; 
													$s->label   = $this->lang['action_snap'];
													$s->handler = 'onclick="wait();"';
													$s->css     = 'snap';
													$s->href    = $this->response->get_url($this->actions_name, "snap").'&lvol='.$line[1];
													$s = $s->get_string();
												} else {
													$disabled[] = $line[5];
													$src = $line[5];
												}
												if($vfree >= $volume_clone_size) {
													$c = $this->response->html->a();
													$c->title   = $this->lang['action_clone'];
													$c->label   = $this->lang['action_clone'];
													$c->handler = 'onclick="wait();"';
													$c->css     = 'clone';
													$c->href    = $this->response->get_url($this->actions_name, "clone").'&lvol='.$line[1];
													$c = $c->get_string();
												}
												$r = $this->response->html->a();
												$r->title   = $this->lang['action_resize'];
												$r->label   = $this->lang['action_resize'];
												$r->handler = 'onclick="wait();"';
												$r->css     = 'resize';
												$r->href    = $this->response->get_url($this->actions_name, "resize").'&lvol='.$line[1];
												$r = $r->get_string();
											}
											$deployment_type = $this->deployment->type;
										} else {
											$disabled[] = $name;
										}
									}
								}
								// create/remove image object, check if image exists
								if($d['deployment'] === 'kvm-gluster-deployment') {
									$path_glusters = "gluster://".$this->resource->ip."/".$this->volgroup."/".$name;
									$path  = '<b>'.$this->lang['table_path_physical'].'</b>: '.$line[2].'<br>';
									$path .= '<b>'.$this->lang['table_path_glusters'].'</b>: '.$path_glusters.'<br>';
								}
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
										$image_add_remove = $i->get_string();
									}
								} else {
									$i = $this->response->html->a();
									$i->title   = $this->lang['action_add_image'];
									$i->label   = $this->lang['action_add_image'];
									$i->handler = 'onclick="wait();"';
									$i->css     = 'add';
									if($d['deployment'] === 'kvm-lvm-deployment') {
										$i->href    = $this->response->get_url($this->actions_name, "image").'&root_device=/dev/'.$this->volgroup.'/'.$name.'&image_command=add&size='.$volume_size;
									} else if($d['deployment'] === 'kvm-bf-deployment') {
										$i->href    = $this->response->get_url($this->actions_name, "image").'&root_device='.$line[2].'&image_command=add&size='.$volume_size;
									} else if($d['deployment'] === 'kvm-gluster-deployment') {
										$i->href    = $this->response->get_url($this->actions_name, "image").'&root_device='.$path_glusters.'&image_name='.$name.'&image_command=add&size='.$volume_size;
									} else if($d['deployment'] === 'kvm-ceph-deployment') {
										$path_ceph = "rbd:".$this->volgroup."/".$name;
										$i->href    = $this->response->get_url($this->actions_name, "image").'&root_device='.$path_ceph.'&image_name='.$name.'&image_command=add&size='.$volume_size;
									}
									$disabled[] = $name;
									$image_add_remove = $i->get_string();
								}
							}

							$state = '<span class="pill inactive">unaligned</span>';
							if($d['deployment'] === 'kvm-lvm-deployment') {
								switch($image->isactive) {
									case '0':
										$state = '<span class="pill idle">idle</span>';
									break;
									case '1':
										$state = '<span class="pill active">active</span>';
									break;
								}

								$data  = '<b>'.$this->lang['table_name'].'</b>: '.$name.'<br>';
								$data .= '<b>'.$this->lang['table_deployment'].'</b>: '.$deployment_type.'<br>';
								$data .= '<b>'.$this->lang['table_attr'].'</b>: '.$line[3].'<br>';
								$data .= '<b>'.$this->lang['table_size'].'</b>: '.$volume_size.' MB<br>';
								$data .= '<b>'.'Root'.'</b>: '.str_replace('/'.$name, '', $image->rootdevice).'<br>';
								$data .= '<b>'.$this->lang['table_source'].'</b>: '.$src.'<br>';
								$data .= '<br><br>';
								$body[] = array(
									'state'    => $state,
									'deploy'   => $deployment_type,
									'name'     => $name,
									'attr'     => $line[3],
									'source'   => $src,
									'size'     => $volume_size,
									'data'     => $data,
									'progress' => $progress,
									'action'   => $image_add_remove.$c.$s.$r,
								);
							}
							if($d['deployment'] === 'kvm-bf-deployment') {
								switch($image->isactive) {
									case '0':
										$state = '<span class="pill idle">idle</span>';
									break;
									case '1':
										$state = '<span class="pill active">active</span>';
									break;
								}
								$root = str_replace('/'.$name, '', $image->rootdevice);
								$data  = '<b>'.$this->lang['table_name'].'</b>: '.$name.'<br>';
								$data .= '<b>'.$this->lang['table_deployment'].'</b>: '.$deployment_type.'<br>';
								$data .= '<b>'.$this->lang['table_attr'].'</b>: '.$line[3].'<br>';
								$data .= '<b>'.$this->lang['table_size'].'</b>: '.$volume_size.' MB<br>';
								$data .= '<b>'.'Root'.'</b>: '.$root.'<br>';
								$data .= '<b>'.$this->lang['table_source'].'</b>: '.str_replace($root.'/', '',$src).'<br>';
								$data .= '<br>';
								$body[] = array(
									'state'    => $state,
									'deploy'   => $deployment_type,
									'name'     => $name,
									'attr'     => $line[3],
									'source'   => $src,
									'size'     => $volume_size,
									'data'     => $data,
									'progress' => $progress,
									'action'   => $image_add_remove.$c.$s,
								);
							}
							if($d['deployment'] === 'kvm-gluster-deployment') {
								$body[] = array(
									'deploy' => $deployment_type,
									'name'   => $name,
									'path'   => $path,
									'source' => $src,
									'size'   => $volume_size,
									'image'   => $image_add_remove,
									'snap'   => $s,
									'clone'  => $c,
								);
							}
							if($d['deployment'] === 'kvm-ceph-deployment') {
								switch($image->isactive) {
									case '0':
										$state = '<span class="pill idle">idle</span>';
									break;
									case '1':
										$state = '<span class="pill active">active</span>';
									break;
								}

								$is_snapshot = false;
								$pos = strpos($name, '@');
								if ($pos === false) {
									$is_snapshot = false;
								} else {
									$is_snapshot = true;
								}

								$root = str_replace('/'.$name, '', $image->rootdevice);
								$data  = '<b>'.$this->lang['table_name'].'</b>: '.$name.'<br>';
								$data .= '<b>'.$this->lang['table_size'].'</b>: '.$volume_size.' MB<br>';
								if ($image->id != "") {
									$data .= '<b>'.'Root'.'</b>: '.$image->rootdevice.'<br>';
									if ($is_snapshot) {
										$data .= '<b>'.$this->lang['table_source'].'</b>: '.$line[5].'<br>';
									}
								}
								$data .= '<br>';
								
								if ($is_snapshot) {
									$image_action = $image_add_remove.$c.$r;
								} else {
									$image_action = $image_add_remove.$c.$r.$s;
								}
								
								$body[] = array(
									'state'    => $state,
									'deploy'   => $deployment_type,
									'name'     => $name,
									'source'   => $src,
									'size'     => $volume_size,
									'data'     => $data,
									'progress' => $progress,
									'action'   => $image_action,
								);
							}
						}
						
					}
				}
			}

			if($d['deployment'] === 'kvm-lvm-deployment') {
				$h['state']['title']       = $this->lang['table_state'];
				$h['name']['title']        = $this->lang['table_name'];
				$h['name']['hidden']       = true;
				$h['deploy']['title']      = $this->lang['table_deployment'];
				$h['deploy']['hidden']     = true;
				$h['attr']['title']        = $this->lang['table_attr'];
				$h['attr']['hidden']       = true;
				$h['size']['title']        = $this->lang['table_size'];
				$h['size']['hidden']       = true;
				$h['source']['title']      = $this->lang['table_source'];
				$h['source']['hidden']     = true;
				$h['data']['title']        = '&#160;';
				$h['data']['sortable']     = false;
				$h['progress']['title']    = '&#160;';
				$h['progress']['sortable'] = false;
				$h['action']['title']    = '&#160;';
				$h['action']['sortable'] = false;
			}
			if($d['deployment'] === 'kvm-bf-deployment') {
				$h['state']['title']       = $this->lang['table_state'];
				$h['name']['title']        = $this->lang['table_name'];
				$h['name']['hidden']       = true;
				$h['deploy']['title']      = $this->lang['table_deployment'];
				$h['deploy']['hidden']     = true;
				$h['attr']['title']        = $this->lang['table_attr'];
				$h['attr']['hidden']       = true;
				$h['size']['title']        = $this->lang['table_size'];
				$h['size']['hidden']       = true;
				$h['source']['title']      = $this->lang['table_source'];
				$h['source']['hidden']     = true;
				$h['data']['title']        = '&#160;';
				$h['data']['sortable']     = false;
				$h['progress']['title']    = '&#160;';
				$h['progress']['sortable'] = false;
				$h['action']['title']    = '&#160;';
				$h['action']['sortable'] = false;
			}
			if($d['deployment'] === 'kvm-gluster-deployment') {
				#$h['icon']['title']      = '&#160;';
				#$h['icon']['sortable']   = false;
				$h['name']['title']      = $this->lang['table_name'];
				$h['deploy']['title']    = $this->lang['table_deployment'];
				$h['path']['title']      = $this->lang['table_path'];
				$h['source']['title']    = $this->lang['table_source'];
				$h['size']['title']      = $this->lang['table_size'];
				$h['image']['title']      = '&#160;';
				$h['image']['sortable']   = false;
				$h['snap']['title']      = '&#160;';
				$h['snap']['sortable']   = false;
				$h['clone']['title']     = '&#160;';
				$h['clone']['sortable']  = false;
			}
			if($d['deployment'] === 'kvm-ceph-deployment') {
				$h['state']['title']       = $this->lang['table_state'];
				$h['name']['title']        = $this->lang['table_name'];
				$h['name']['hidden']       = true;
				$h['deploy']['title']      = $this->lang['table_deployment'];
				$h['deploy']['hidden']     = true;
				$h['size']['title']        = $this->lang['table_size'];
				$h['size']['hidden']       = true;
				$h['source']['title']      = $this->lang['table_source'];
				$h['source']['hidden']     = true;
				$h['data']['title']        = '&#160;';
				$h['data']['sortable']     = false;
				$h['progress']['title']    = '&#160;';
				$h['progress']['sortable'] = false;
				$h['action']['title']    = '&#160;';
				$h['action']['sortable'] = false;
			}

			$table = $this->response->html->tablebuilder('kvm_lvols', $this->response->get_array($this->actions_name, 'volgroup'));
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

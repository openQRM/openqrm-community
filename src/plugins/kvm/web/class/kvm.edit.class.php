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

class kvm_edit
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
		$this->statfile   = $this->openqrm->get('basedir').'/plugins/kvm/web/storage/'.$resource->id.'.vg.stat';
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
			$t = $this->response->html->template($this->tpldir.'/kvm-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_deployment'], 'lang_deployment');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->openqrm->get('baseurl'), 'baseurl');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
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

			// check device-manager
			$devicemgm = false;
			if($this->file->exists($this->openqrm->get('webdir').'/plugins/device-manager/class/device-manager.addvg.class.php')) {
				$devicemgm = true;
			}

			$d['state'] = '<span class="pill '.$this->resource->state.'">'.$this->resource->state.'</span>';
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['deployment'] = $this->deployment->type;
			$d['name'] = $this->storage->name;
			$d['id'] = $this->storage->id;

			$body = array();
			$file = $this->statfile;
			if($this->file->exists($file)) {
				$lines = explode("\n", $this->file->get_contents($file));
				if(count($lines) >= 1) {
					$i = 0;
					foreach($lines as $line) {
						if($line !== '') {
							$line  = explode('@', $line);
							$name  = substr($line[0], strripos($line[0], '/'));

							//handle format send by df
							$line[5] = str_replace('MB', '.00', $line[5]);
							$line[6] = str_replace('MB', '.00', $line[6]);

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

							if($d['deployment'] === 'kvm-lvm-deployment') {
								$body[$i] = array(
									'name' => $name,
									'pv' => $line[1],
									'lv' => $line[2],
									'sn' => $line[3],
									'attr' => $line[4],
									'vsize' => $vsize,
									'vfree' => $vfree.' MB',
									'action' => $a->get_string(),
								);
								if($devicemgm === true) {
									if($line[2] === '0' && $line[3] === '0') {
										$a = $this->response->html->a();
										$a->title   = $this->lang['action_remove'];
										$a->label   = $this->lang['action_remove'];
										$a->handler = 'onclick="wait();"';
										$a->css     = 'remove';
										$a->href    = $this->response->get_url($this->actions_name, "removevg").'&volgroup='.$name;
										$body[$i]['action'] = $body[$i]['action'].$a->get_string();
									}
								}
							}
							if($d['deployment'] === 'kvm-bf-deployment') {
								$body[$i] = array(
									'name'   => $name,
									'vsize' => $vsize,
									'vfree' => $vfree.' MB',
									'attr' => '&#160;',
									'action' => $a->get_string(),
								);
							}
							if($d['deployment'] === 'kvm-gluster-deployment') {
								$vg  = '<b>'.$this->lang['table_type'].'</b>: '.$line[1].'<br>';
								$vg .= '<b>'.$this->lang['table_status'].'</b>: '.$line[2].'<br>';
								$vg .= '<b>'.$this->lang['table_topology'].'</b>: '.$line[3].'<br>';
								$vg .= '<b>'.$this->lang['table_transport'].'</b>: '.$line[4];
								$brick_arr  = explode(',', $line[7]);
								$bricks  = '';
								foreach($brick_arr as $brick) {
									$bricks  .= '<b>'.$brick.'</b><br>';
								}
								$body[$i] = array(
									'name'   => $name,
									'vg' => $vg,
									'bricks' => $bricks,
									'vsize' => $vsize,
									'vfree' => $vfree.' MB',
									'action' => $a->get_string(),
								);
							}
							if($d['deployment'] === 'kvm-ceph-deployment') {
								$body[$i] = array(
									'name'   => $name,
									'poolid' => $line[1],
									'vusedpercent' => $line[3].' %',
									'vused' => $line[4].' MB',
									'available' => $line[6].' MB',
									'objects' => $line[2],
									'action' => $a->get_string(),
								);
							}

						}
						$i++;
					}
				}
			}

			if($d['deployment'] === 'kvm-lvm-deployment') {
				$h['name']['title'] = $this->lang['table_name'];
				$h['vsize']['title'] = $this->lang['table_vsize'];
				$h['vfree']['title'] = $this->lang['table_vfree'];
				$h['pv']['title'] = $this->lang['table_pv'];
				$h['lv']['title'] = $this->lang['table_lv'];
				$h['sn']['title'] = $this->lang['table_sn'];
				$h['attr']['title'] = $this->lang['table_attr'];
				$h['action']['title'] = '&#160;';
				$h['action']['sortable'] = false;
			}
			if($d['deployment'] === 'kvm-bf-deployment') {
				$h['name']['title'] = $this->lang['table_name'];
				$h['vsize']['title'] = $this->lang['table_vsize'];
				$h['vfree']['title'] = $this->lang['table_vfree'];
				$h['attr']['title'] =  '&#160;';
				$h['attr']['sortable'] = false;
				$h['action']['title'] = '&#160;';
				$h['action']['sortable'] = false;
			}
			if($d['deployment'] === 'kvm-gluster-deployment') {
				$h['name']['title'] = $this->lang['table_name'];
				$h['vg']['title'] = $this->lang['table_vg'];
				$h['bricks']['title'] = $this->lang['table_bricks'];
				$h['vsize']['title'] = $this->lang['table_vsize'];
				$h['vfree']['title'] = $this->lang['table_vfree'];
				$h['edit']['title'] = '&#160;';
				$h['edit']['sortable'] = false;
			}
			if($d['deployment'] === 'kvm-ceph-deployment') {
				$h['name']['title'] = $this->lang['table_name'];
				$h['poolid']['title'] = $this->lang['lang_id'];
				$h['vusedpercent']['title'] = $this->lang['table_vusedpercent'];
				$h['vusedpercent']['title'] = $this->lang['table_vusedpercent'];
				$h['vused']['title'] = $this->lang['table_vused'];
				$h['vused']['title'] = $this->lang['table_vused'];
				$h['available']['title'] = $this->lang['table_available'];
				$h['available']['title'] = $this->lang['table_available'];
				$h['objects']['title'] = $this->lang['table_objects'];
				$h['objects']['title'] = $this->lang['table_objects'];
				$h['action']['title'] = '&#160;';
				$h['action']['sortable'] = false;
			}

			$table = $this->response->html->tablebuilder('kvm_edit', $this->response->get_array($this->actions_name, 'edit'));
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
			if($devicemgm === true && $d['deployment'] === 'kvm-lvm-deployment') {
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

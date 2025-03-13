<?php
/**
 * kvm-vm Select Storage
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_vm_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kvm_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'kvm_vm_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kvm_vm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kvm_vm_identifier';
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
		$this->file                     = $openqrm->file();
		$this->openqrm                  = $openqrm;
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
		$t = $this->response->html->template($this->tpldir.'/kvm-vm-select.tpl.php');
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
		$resource   = $this->openqrm->resource();
		$virtualization = $this->openqrm->virtualization();
		$virtualization->get_instance_by_type("kvm");
		$appliance = $this->openqrm->appliance();
		$storage = $this->openqrm->storage();
		$deployment = $this->openqrm->deployment();

		$table = $this->response->html->tablebuilder('kvm_vm', $this->response->get_array($this->actions_name, 'select'));
		$table->sort      = 'appliance_id';
		$table->limit     = 10;
		$table->offset    = 0;
		$table->order     = 'ASC';
		$table->max       = $appliance->get_count_per_virtualization($virtualization->id);
		$table->autosort  = false;
		$table->sort_link = false;
		$table->init();

		// handle tab in tab
		if($this->response->html->request()->get('iplugin') !== '') {
			$strControler = 'icontroller';
		}
		else if($this->response->html->request()->get('rplugin') !== '') {
			$strControler = 'rcontroller';
		}
		else if($this->response->html->request()->get('aplugin') !== '') {
			$strControler = 'acontroller';
		} else {
			$strControler = 'controller';
		}

		// storages
		$deployment->get_instance_by_type('kvm');
		$storages = $storage->display_overview(0, 10000, 'storage_id', 'ASC');
		$s = array();
		if(count($storages) >= 1) {
			foreach($storages as $k => $v) {
				$storage->get_instance_by_id($v["storage_id"]);
				$resource->get_instance_by_id($storage->resource_id);
				$deployment->get_instance_by_id($storage->type);
				if($deployment->storagetype === 'kvm') {
					// replace id by string 
					$v['storage_type'] = $deployment->type;
					$s[] = $v;
				}
			}
		}

		$servers = $appliance->display_overview_per_virtualization($virtualization->id, $table->offset, $table->limit, $table->sort, $table->order);

		if(count($servers) >= 1) {
			foreach($servers as $k => $v) {
				$resource->get_instance_by_id($v["appliance_resources"]);
				$state_icon = '<span class="pill '.$resource->state.'">'.$resource->state.'</span>';

				$a = $this->response->html->a();
				$a->title   = $this->lang['title_vms'];
				$a->label   = 'VMS';
				$a->handler = 'onclick="wait();"';
				$a->css     = 'edit';
				$a->href    = $this->response->get_url($strControler, "kvm-vm").'&kvm_vm_action=edit&appliance_id='.$v['appliance_id'];
				$links = $a->get_string();

				// handle storages
				$slinks = '';
				foreach($s as $storage) {
					if($storage['storage_resource_id'] === $resource->id) {
						if($storage['storage_type'] === 'kvm-lvm-deployment') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['title_lvm'];
							$a->label   = 'LVM';
							$a->handler = 'onclick="wait();"';
							$a->css     = 'edit';
							$a->href    = $this->response->get_url($strControler, "kvm").'&kvm_action=edit&storage_id='.$storage['storage_id'];
							$slinks .= $a->get_string();
						}
						else if($storage['storage_type'] === 'kvm-bf-deployment') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['title_bf'];
							$a->label   = 'Blockfiles';
							$a->handler = 'onclick="wait();"';
							$a->css     = 'edit';
							$a->href    = $this->response->get_url($strControler, "kvm").'&kvm_action=edit&storage_id='.$storage['storage_id'];
							$slinks .= $a->get_string();
						}
						else if($storage['storage_type'] === 'kvm-gluster-deployment') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['title_glusterfs'];
							$a->label   = 'GlusterFS';
							$a->handler = 'onclick="wait();"';
							$a->css     = 'edit';
							$a->href    = $this->response->get_url($strControler, "kvm").'&kvm_action=edit&storage_id='.$storage['storage_id'];
							$slinks .= $a->get_string();
						}
						else if($storage['storage_type'] === 'kvm-ceph-deployment') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['title_ceph'];
							$a->label   = 'Ceph';
							$a->handler = 'onclick="wait();"';
							$a->css     = 'edit';
							$a->href    = $this->response->get_url($strControler, "kvm").'&kvm_action=edit&storage_id='.$storage['storage_id'];
							$slinks .= $a->get_string();
						}
					}
				}
				// handle missing storage
				if($slinks === '') {
					$a = $this->response->html->a();
					$a->title   = $this->lang['new_storage'];
					$a->label   = $this->lang['new_storage'];
					$a->handler = 'onclick="wait();"';
					$a->css     = 'add';
					$a->href    = $this->response->html->thisfile.'?base=storage&storage_action=add';
					$links .= $a->get_string();
				} else {
					$links .= $slinks;
				}

				// Network Manager
				$n = $this->response->html->a();
				$n->label   = $this->lang['network_manager'];
				$n->css     = 'enable';
				$n->handler = 'onclick="wait();"';
				$n->href    = $this->response->html->thisfile.'?plugin=network-manager&appliance_id='.$v['appliance_id'];
				$plugins = $n->get_string();
				// Sysinfo
				$n = $this->response->html->a();
				$n->label   = 'Sysinfo';
				$n->css     = 'enable';
				$n->handler = 'onclick="wait();"';
				$n->href    = $this->response->html->thisfile.'?plugin=kvm&controller=kvm-vm&kvm_vm_action=sysinfo&appliance_id='.$v['appliance_id'];
				$plugins .= $n->get_string();

				$data  = '<b>'.$this->lang['table_id'].'</b>: '.$v['appliance_id'].'<br>';
				$data .= '<b>'.$this->lang['table_name'].'</b>: '.$v['appliance_name'].'<br>';
				$data .= '<b>'.$this->lang['table_recource'].'</b>: '.$resource->hostname.'<br>';
				$data .= '<b>IP</b>: '.$resource->ip;

				$b[] = array(
					'state' => $state_icon,
					'appliance_id' => $v['appliance_id'],
					'name' => $v['appliance_name'],
					'appliance_resources' => $resource->id,
					'data' => $data,
					'comment' => $v['appliance_comment'].'<hr>'.$plugins,
					'action' => $links,
				);
			}

			$h['state']['title'] ='&#160;';
			$h['state']['sortable'] = false;
			$h['appliance_id']['title'] = $this->lang['table_id'];
			$h['appliance_id']['hidden'] = true;
			$h['name']['title'] = $this->lang['table_name'];
			$h['name']['hidden'] = true;
			$h['appliance_resources']['title'] = $this->lang['table_recource'];
			$h['appliance_resources']['hidden'] = true;
			$h['data']['title'] = '&#160;';
			$h['data']['sortable'] = false;
			$h['comment']['title'] ='&#160;';
			$h['comment']['sortable'] = false;
			$h['action']['title'] = '&#160;';
			$h['action']['sortable'] = false;

			$table->id = 'Tabelle';
			$table->css = 'htmlobject_table';
			$table->border = 1;
			$table->cellspacing = 0;
			$table->cellpadding = 3;
			$table->form_action = $this->response->html->thisfile;
			$table->head = $h;
			$table->body = $b;
			$table->limit_select = array(
				array("value" => 10, "text" => 10),
				array("value" => 20, "text" => 20),
				array("value" => 30, "text" => 30),
				array("value" => 40, "text" => 40),
				array("value" => 50, "text" => 50),
			);
			return $table->get_string();
		} else {
			$box = $this->response->html->div();
			$box->id = 'htmlobject_box_add';
			$box->css = 'htmlobject_box';
			$box_content  = $this->lang['error_no_host'].'<br><br>';
			$box_content .= '<a href="'.$this->response->html->thisfile.'?base=appliance&appliance_action=step1">'.$this->lang['new'].'</a>';
			$box->add($box_content);
			return $box->get_string();
		}
	}

}

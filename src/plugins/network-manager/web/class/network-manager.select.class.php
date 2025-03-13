<?php
/**
 * network-manager List Networkconfig
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class network_manager_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name;
/**
* message param
* @access public
* @var string
*/
var $message_param;
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab;
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name;
/**
* path to tpldir
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param openqrm $openqrm
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($openqrm, $response, $controller) {
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->response = $response;
		$this->controller = $controller;
		$this->file     = $this->openqrm->file();
		$this->tpldir   = $this->rootdir.'/plugins/network-manager/tpl';

		$id = $this->response->html->request()->get('appliance_id');
		$this->response->add('appliance_id', $id);

		$appliance = $this->openqrm->appliance();
		$this->appliance = $appliance->get_instance_by_id($id);

		$resource = $this->openqrm->resource();
		$this->resource = $resource->get_instance_by_id($this->appliance->resources);
		
		$this->statfile = $this->openqrm->get('basedir').'/plugins/network-manager/web/storage/'.$this->resource->id.'.network_config';
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action() {
		$response = $this->select();

		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		$data['add'] = $response->add;
		$data['table'] = $response->table;
		$data['label'] = sprintf($this->lang['label'], $this->appliance->name);
		$data['baseurl'] = $this->openqrm->get('baseurl');
		$t = $response->html->template($this->tpldir.'/network-manager-select.tpl.php');
		$t->add($data);
		return $t;
	}

	//--------------------------------------------
	/**
	 * Ad Volume Group
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
		$response = $this->get_response();

		$b = array();
		$identifier_disabled = array();
		$this->controller->__reload( $this->statfile, $this->resource );
		if ($this->file->exists($this->statfile)) {
			$result = trim($this->file->get_contents($this->statfile));
			$result = explode("\n", $result);
			if(is_array($result)) {
				$res = array();
				foreach($result as $v) {
					$res[] = explode('@', $v);
				}
				foreach ($res as $line) {
					$up = '';
					if($line[0] === 'n') {
						$identifier_disabled[] = $line[1];
						$type = 'Physical';
					}
					elseif($line[0] === 'b') {
						$type = 'Bridge';
						if(isset($line[4]) && $line[4] !== '') {
							$up = $line[4];
						}
					}

					$ip = '';
					if($line[3] !== '') {
						$tmp = explode('/', $line[3]);
						$ip = $tmp[0];
					}

					$b[] = array(
						'device' => $line[1],
						'type' => $type,
						'mac' => $line[2],
						'ip' => $ip,
						'up' => $up,
					);
				}
			}
		}

		$h['type']['title'] ='Type';
		$h['type']['sortable'] = true;
		$h['device']['title'] ='Device';
		$h['device']['sortable'] = true;
		$h['mac']['title'] ='Mac';
		$h['mac']['sortable'] = true;
		$h['ip']['title'] ='IP';
		$h['ip']['sortable'] = true;
		$h['up']['title'] ='Interface';
		$h['up']['sortable'] = true;

		$table = $this->response->html->tablebuilder('network', $this->response->get_array($this->actions_name, 'select'));
		$table->sort      = 'type';
		$table->limit     = 10;
		$table->offset    = 0;
		$table->order     = 'ASC';
		$table->max       = count($b);
		$table->autosort  = true;
		$table->sort_link = false;
		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->form_action	= $this->response->html->thisfile;
		$table->head = $h;
		$table->body = $b;
		$table->identifier = 'device';
		$table->identifier_name = $this->identifier_name;
		$table->identifier_disabled = $identifier_disabled;
		$table->actions_name = $this->actions_name;
		$table->actions = array(array('remove' => $this->lang['action_remove']));

		$response->table = $table;
		$response->add = '';
		if(count($b) > 0) {
			$a = $response->html->a();
			$a->label = $this->lang['action_add'];
			$a->css = 'add';
			$a->href = $response->get_url($this->actions_name, 'add') ;
			$a->handler = 'onclick="wait();"';
			$response->add = $a;
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param enum $mode [confirm]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response($mode = '') {
		$response = $this->response;
		#$form     = $response->get_form($this->actions_name, 'select');
		#$response->form = $form;
		return $response;
	}

}

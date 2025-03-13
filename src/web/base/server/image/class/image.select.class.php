<?php
/**
 * Image Select
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2014, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2014, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class image_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'image_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "image_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'image_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'image_identifier';
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
		$this->response->add('image_filter', $this->response->html->request()->get('image_filter'));
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
		$data = $this->select();
		$t = $this->response->html->template($this->tpldir.'/image-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function select() {

		$d = array();

		$h = array();
		$h['image_isactive']['title'] = $this->lang['table_isactive'];
		
		$h['image_id']['title'] = $this->lang['table_id'];
		$h['image_id']['hidden'] = true;
		
		$h['image_name']['title'] = $this->lang['table_name'];
		$h['image_name']['hidden'] = true;

		$h['image_type']['title'] = $this->lang['table_deployment'];
		$h['image_type']['hidden'] = true;

		$h['image_rootdevice']['title'] = $this->lang['table_root'];
		$h['image_rootdevice']['hidden'] = true;

		$h['image_version']['title'] = $this->lang['table_version'];
		$h['image_version']['hidden'] = true;

		$h['image_capabilities']['title'] = $this->lang['table_capabilities'];
		$h['image_capabilities']['hidden'] = true;

		$h['image_data']['title'] = '&#160;';
		$h['image_data']['sortable'] = false;

		$h['image_comment']['title'] = '&#160;';
		$h['image_comment']['sortable'] = false;
		
		$h['image_edit']['title'] = '&#160;';
		$h['image_edit']['sortable'] = false;

		$image = new image();
		$params  = $this->response->get_array($this->actions_name, 'select');
		$b       = array();

		// unset unnecessary params
		unset($params['image[sort]']);
		unset($params['image[order]']);
		unset($params['image[limit]']);
		unset($params['image[offset]']);
		unset($params['image_filter']);

		$table = $this->response->html->tablebuilder('image', $params);
		$table->offset = 0;
		$table->sort = 'image_id';
		$table->limit = 20;
		$table->order = 'ASC';
		$table->max = $image->get_count();

		$table->init();

		// handle table params
		$tps = $table->get_params();
		$tp = '';
		foreach($tps['image'] as $k => $v) {
			$tp .= '&image['.$k.']='.$v;
		}


		$image_arr = $image->display_overview(0, 10000, $table->sort, $table->order);
		$image_icon = "/openqrm/base/img/image.png";
		foreach ($image_arr as $index => $image_db) {

			if ($this->response->html->request()->get('image_filter') === '' || ($this->response->html->request()->get('image_filter') == $image_db["image_type"] )) {

				// prepare the values for the array
				$image = new image();
				$image->get_instance_by_id($image_db["image_id"]);
				// edit
				$a = $this->response->html->a();
				$a->title   = $this->lang['action_edit'];
				$a->label = $this->lang['action_edit'];
			//	$a->handler = 'onclick="wait();"';
				$a->css     = 'edit';
				$a->href    = $this->response->get_url($this->actions_name, 'edit').'&image_id='.$image->id.''.$tp;
				$image_edit = $a->get_string();

				// force remove
				$a = $this->response->html->a();
				$a->title   = $this->lang['action_remove'];
				$a->label = $this->lang['action_remove'];
				$a->css     = 'remove';
				$a->href    = $this->response->get_url($this->actions_name, 'remove').'&image_identifier='.$image->id.''.$tp;
				$image_remove = $a->get_string();
				$image_actions = $image_edit;

				// set the active icon
				$isactive_icon = '<span class="pill inactive">'.$this->lang['lang_inactive'].'</span>';
				if ($image_db["image_isactive"] == 1) {
					$isactive_icon = '<span class="pill active">'.$this->lang['lang_active'].'</span>';
				} else {
					$image_actions .= $image_remove;
				}

				// infos
				$storage = new storage();
				$storage->get_instance_by_id($image_db['image_storageid']);
				$deployment = new deployment();
				$deployment->get_instance_by_id($storage->type);
				$link = $storage->name;
				if($deployment->storagetype !== 'local-server') {
					$a = $this->response->html->a();
					$a->label   = $storage->name;
					$a->handler = 'onclick="wait();"';
					$a->href    = $this->response->html->thisfile.'?plugin='.$deployment->storagetype.'&'.str_replace('-', '_',$deployment->storagetype).'_action=edit&storage_id='.$storage->id;
					$link = $a->get_string();
				}

				$data  = '<div class="data">';
				$data .= '<b>'.$this->lang['table_id'].'</b>: '.$image_db["image_id"].'<br>';
				$data .= '<b>'.$this->lang['table_name'].'</b>: '.$image_db["image_name"].'<br>';
				$data .= '<b>'.$this->lang['table_deployment'].'</b>: '.$image_db["image_type"].'<br>';
				$data .= '<b>'.$this->lang['table_storage'].'</b>: '.$link.'<br>';
				if(isset($image_db["image_rootdevice"])) {
					$root = $image_db["image_rootdevice"];
					if ($image_db["image_type"] == "esx-deployment") {
						// vmware
						$root = str_replace($image_db["image_name"], '',$image_db["image_rootdevice"]);
						$root = str_replace(':/.vmdk', '', $root);
					}
					$data .= '<b>'.$this->lang['table_root'].'</b>: '.$root.'<br>';
				}
				if(isset($image_db["image_version"])) {
					$data .= '<b>'.$this->lang['table_version'].'</b>: '.$image_db["image_version"].'<br>';
				}
				if(isset($image_db["image_capabilities"])) {
					$data .= '<b>'.$this->lang['table_capabilities'].'</b>: '.$image_db["image_capabilities"];
				}
				$data .= '</div>';

				$b[] = array(
					'image_isactive' => $isactive_icon,
					'image_id' => $image_db["image_id"],
					'image_name' => $image_db["image_name"],
					'image_rootdevice' => $image_db["image_rootdevice"],
					'image_version' => $image_db["image_version"],
					'image_capabilities' => $image_db["image_capabilities"],
					'image_data' => $data,
					'image_comment' => $image_db["image_comment"],
					'image_edit' => $image_actions,
				);
			}
		}

		// Filter
		$types = $this->openqrm->deployment();
		$list = $types->get_list();
		$filter = array();
		foreach( $list as $l) {
			$filter[] = array( $l['label']);
		}
		asort($filter);
		array_unshift($filter, array('',''));
		$select = $this->response->html->select();
		$select->add($filter, array(0,0));
		$select->name = 'image_filter';
		$select->handler = 'onchange="wait();this.form.submit();return false;"';
		$select->selected = array($this->response->html->request()->get('image_filter'));
		$box = $this->response->html->box();
		$box->add($select);
		$box->id = 'image_filter_box';
		$box->css = 'htmlobject_box';
		$box->label = $this->lang['lang_filter'];

		$add = $this->response->html->a();
		$add->title   = $this->lang['action_add'];
		$add->label   = $this->lang['action_add'];
		$add->handler = 'onclick="wait();"';
		$add->css     = 'add';
		$add->href    = $this->response->get_url($this->actions_name, "add").''.$tp;

		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->autosort = false;
		$table->sort_link = false;
		if ($this->response->html->request()->get('image_filter') !== '') {
			$table->max = count($b);
		} else {
			$table->max = $image->get_count()-2;
		}
		$table->head = $h;
		$table->body = $b;
		$table->form_action = $this->response->html->thisfile;

		$d['form']   = $this->response->get_form($this->actions_name, 'select', false)->get_elements();
		$d['add']    = $add->get_string();
		$d['table']  = $table;
		$d['filter'] = $box->get_string();
		return $d;
	}

}
?>

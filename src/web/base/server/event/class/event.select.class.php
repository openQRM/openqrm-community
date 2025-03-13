<?php
/**
 * Event Select
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class event_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'event_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "event_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'event_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'event_identifier';
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
	 * @param htmlobject_response $response
	 * @param file $file
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->response = $response;
		$this->file     = $openqrm->file();
		$this->openqrm  = $openqrm;
		$this->rootdir  = $this->openqrm->get('webdir');
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
		$t = $this->response->html->template($this->tpldir.'/event-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($table, 'table');
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['lang_filter'], 'lang_filter');
		$t->add($this->lang['please_wait'], 'please_wait');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_tablebulider
	 */
	//--------------------------------------------
	function select() {

		$h = array();
		$h['event_id']['title'] = $this->lang['table_id'];
		$h['event_id']['hidden'] = true;
		$h['event_id']['sortable'] = false;
		$h['event_priority']['title'] = $this->lang['table_state'];
		$h['event_time']['title'] = $this->lang['table_date'];
		$h['event_source']['title'] = $this->lang['table_source'];
		$h['event_description']['title'] = $this->lang['table_description'];
		$h['event_description']['sortable'] = false;

		$event = new event();
        $events = [];

		$b     = array();

		$table = $this->response->html->tablebuilder('events', $this->response->get_array($this->actions_name, 'select'));
		$table->offset = 0;
		$table->limit = 100;
		$table->sort = 'event_time';
		$table->order = 'DESC';

		switch ($this->response->html->request()->get('event_filter')) {
			case '':
			case 'all':
				$table->max = $event->get_count();
				break;
			case 'active':
				$table->max = $event->get_count('active');
				break;
			case 'error':
				$table->max = $event->get_count('error');
				break;
			case 'acknowledge':
				$table->max = $event->get_count('acknowledge');
				break;
			case 'warning':
				$table->max = $event->get_count('warning');
				break;
		}

		$table->init();
		switch ($this->response->html->request()->get('event_filter')) {
			case '':
			case 'all':
				$events = $event->display_overview($table->offset, $table->limit, $table->sort, $table->order);
				break;
			case 'active':
				$events = $event->display_overview($table->offset, $table->limit, $table->sort, $table->order, 'active');
				break;
			case 'error':
				$events = $event->display_overview($table->offset, $table->limit, $table->sort, $table->order, 'error');
				break;
			case 'acknowledge':
				$events = $event->display_overview($table->offset, $table->limit, $table->sort, $table->order, 'acknowledge');
				break;
			case 'warning':
				$events = $event->display_overview($table->offset, $table->limit, $table->sort, $table->order, 'warning');
				break;
		}
		
		$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		$i = 0;
		foreach ($events as $key => $value) {
			$icon="transition.png";
			switch ($value['event_priority']) {
					case 0:
						$icon = '<span class="pill off">off</span>'; break;
					case 1:
					case 2:
					case 3:
						$icon = '<span class="pill red">error</span>'; break; // error event
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
						$icon = '<span class="pill green">notice</span>'; break; // undefined event
					case 9:	
						$icon = '<span class="pill yellow">running</span>'; break; // active event
					case 10:
						$icon = '<span class="pill green">ok</span>'; break; // notice event
			}
			if ($value['event_status'] === '1') {
					$icon = '<span class="pill green">ack</span>'; // acknowledged event
			}
			$description = '';
			if (strstr($value['event_description'], "ERROR running token")) {
				$error_token = str_replace("ERROR running token ", "", $value['event_description']);
				$cmd_file = $this->rootdir."/server/event/errors/".$error_token.".cmd";
				$error_file = $this->rootdir."/server/event/errors/".$error_token.".out";

				// get command and error strings
				if (($this->file->exists($cmd_file)) && ($this->file->exists($error_file))) {
					$oq_cmd = $this->file->get_contents($cmd_file);
					$oq_cmd = str_replace('"','', $oq_cmd);
					$oq_cmd_error = $this->file->get_contents($error_file);
					$oq_cmd_error = str_replace('"','', $oq_cmd_error);
					// set the event to error in any way
					$event_fields = array();
					$event_fields["event_priority"] = 1;
					$event->update($value['event_id'], $event_fields);
					$event->get_instance_by_id($value['event_id']);
					$icon = '<span class="pill red">error</span>';
					// set the description
					$description  = "<a href=\"/openqrm/base/server/event/errors/".$error_token.".out\" title=\"".$oq_cmd_error."\" target=\"_BLANK\">Error</a> running openQRM <a href=\"/openqrm/base/server/event/errors/".$error_token.".cmd\" title=\"".$oq_cmd."\"target=\"_BLANK\">command</a>";
					
					$a = $this->response->html->a();
					$a->title   = $this->lang['action_rerun'];
					$a->label   = $this->lang['action_rerun'];
					$a->handler = 'onclick="wait();"';
					$a->css     = 'start pull-right';
					$a->href    = $this->response->get_url($this->actions_name, 'rerun').'&token='.$error_token.'&event_id='.$event->id;
					$rerun = $a->get_string();
					$description .= $rerun;
				} else {
					// we are currently re-running the token, do not show the links
					$description = "Error running openQRM command<br><strong>Currently re-running token $error_token</strong>";
				}
			} else {
				$description = $value['event_description'];
			}
			$b[$i]['event_id'] = $value['event_id'];
			$b[$i]['event_priority'] = $icon;
			$b[$i]['event_time'] = date('Y/m/d H:i:s', $value['event_time']);
			$b[$i]['event_source'] = $value['event_source'];
			$b[$i]['event_description'] = $description;
			$i++;
		}

		$filter = array();
		$filter[] = array('', 'all');
		$filter[] = array($this->lang['filter_active'], 'active');
		$filter[] = array($this->lang['filter_warning'], 'warning');
		$filter[] = array($this->lang['filter_error'], 'error');
		$filter[] = array($this->lang['filter_acknowledge'], 'acknowledge');
		asort($filter);

		$select = $this->response->html->select();
		$select->add($filter, array(1,0));
		$select->name = 'event_filter';
		$select->handler = 'onchange="wait();this.form.submit();return false;"';
		$select->selected = array($this->response->html->request()->get('event_filter'));
		$box = $this->response->html->box();
		$box->add($select);
		$box->id = 'events_filter';
		$box->css = 'htmlobject_box';
		$box->label = $this->lang['lang_filter'];

		$table->add_headrow($box->get_string());

		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->form_action = $this->response->html->thisfile;
		$table->cellpadding = 3;
		$table->autosort = false;
		$table->sort_link = false;
		$table->head = $h;
		$table->body = $b;
		$table->actions_name = $this->actions_name;
		$table->actions = array(
							array('remove' => $this->lang['action_remove']),
							array('acknowledge' => $this->lang['action_acknowledge'])
						);
		$table->identifier = 'event_id';
		$table->identifier_name = $this->identifier_name;
		$table->limit_select = array(
			array("value" => 100, "text" => 100),
			array("value" => 200, "text" => 200),
			array("value" => 400, "text" => 400),
			array("value" => 500, "text" => 500)
		);
		return $table;
	}

}

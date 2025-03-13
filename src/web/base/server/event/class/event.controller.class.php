<?php
/**
 * Event Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class event_controller
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
var $lang = array(
	'select' => array (
		'tab' => 'Events',
		'label' => 'Events',
		'action_remove' => 'remove',
		'action_rerun' => 're-run command',
		'action_acknowledge' => 'acknowledge',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_date' => 'Date',
		'table_source' => 'Source',
		'table_description' => 'Description',
		'lang_filter' => 'Filter by state',
		'filter_error' => 'Error',
		'filter_active' => 'Active',
		'filter_warning' => 'Warning',
		'filter_acknowledge' => 'Acknowledged',
		'please_wait' => 'Loading. Please wait ..',
	), 
	'remove' => array (
		'label' => 'Remove Events(s)',
		'msg' => 'Removed event %s',
		'error_timeout' => 'Timeout while trying to remove event %s',
		'error_start' => 'Could not remove event %s',
	),
	'acknowledge' => array (
		'label' => 'Acknowledge Events(s)',
		'msg' => 'Acknowledged Event %s',
	),
	'rerun' => array (
		'label' => 'Rerun Events(s)',
		'msg' => 'Rerun Event %s',
	)
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->tpldir   = $this->rootdir.'/server/event/tpl';
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/server/event/lang", 'event.ini');
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
	function action($action = null) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			if(is_array($ar)) {
				$this->action = key($ar);
			} else {
				$this->action = $ar;
			}
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = "select";
		}
		$this->response->params['event_filter'] = $this->response->html->request()->get('event_filter');

		// handle table params
		$vars = $this->response->html->request()->get('events');
		if($vars !== '') {
			if(!isset($vars['action'])) {
				foreach($vars as $k => $v) {
					$this->response->add('events['.$k.']', $v);
				}
			} else {
				foreach($vars as $k => $v) {
					unset($this->response->params['events['.$k.']']);
				}
			}
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'remove':
				$content[] = $this->remove(true);
			break;
			case 'acknowledge':
				$content[] = $this->acknowledge(true);
			break;
			case 'rerun':
				$content[] = $this->rerun(true);
			break;
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * API
	 *
	 * @access public
	 */
	//--------------------------------------------
	function api() {
		require_once($this->rootdir.'/server/event/class/event.api.class.php');
		$controller = new event_api($this);
		$controller->action();
	}

	
	//--------------------------------------------
	/**
	 * Select event
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/event/class/event.select.class.php');
			$controller = new event_select($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['select'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['select']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
	}	
	
	//--------------------------------------------
	/**
	 * Remove event
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/event/class/event.remove.class.php');
			$controller                  = new event_remove($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['remove'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Remove';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove' );
		$content['onclick'] = false;
		if($this->action === 'remove'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Acknowledge Event
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function acknowledge( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/event/class/event.acknowledge.class.php');
			$controller                  = new event_acknowledge($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['acknowledge'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Acknowledge';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'acknowledge' );
		$content['onclick'] = false;
		if($this->action === 'acknowledge'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Rerun Event
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function rerun( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/event/class/event.rerun.class.php');
			$controller                  = new event_rerun($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['rerun'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Rerun';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'rerun' );
		$content['onclick'] = false;
		if($this->action === 'rerun'){
			$content['active']  = true;
		}
		return $content;
	}

}

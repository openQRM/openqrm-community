<?php
/**
 * Collectd System statistics Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class collectd_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'collectd_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "collectd_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'collectd_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'collectd_identifier';
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
	'statistics' => array(
		'tab' => 'Collectd',
		'label' => 'Statistics on resource %s',
		'action_download' => 'download',
		'no_data' => 'No statistics found',
		'form_interval' => 'Interval',
		'please_wait' => 'Loading Collectd Data. Please wait ..',
	),
);
/**
* path to image
* @access public
* @var string
*/
var $image_path = 'api.php?action=plugin&plugin=collectd&collectd_action=image';
/**
* image_width
* @access public
* @var integer
*/
var $image_width = 800;
/**
* image_height
* @access public
* @var integer
*/
var $image_height = 243;

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
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/collectd/lang", 'collectd.ini');
		$this->tpldir   = $this->rootdir.'/plugins/collectd/tpl';
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
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = 'statistics';
		}

		$content = array();
		switch( $this->action ) {
			case 'statistics':
				$content[] = $this->statistics(true);
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
		require_once($this->rootdir.'/plugins/collectd/class/collectd.api.class.php');
		$controller = new collectd_api($this);
		$controller->action();
	}

	//--------------------------------------------
	/**
	 * View statistics
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function statistics( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/collectd/class/collectd.statistics.class.php');
			$controller = new collectd_statistics($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['statistics'];
			$controller->image_path    = $this->image_path;
			$controller->image_width   = $this->image_width;
			$controller->image_height  = $this->image_height;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['statistics']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'statistics' );
		$content['onclick'] = false;
		if($this->action === 'statistics'){
			$content['active']  = true;
		}
		return $content;
	}


}

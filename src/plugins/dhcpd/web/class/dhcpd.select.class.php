<?php
/**
 * DHCP select
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class dhcpd_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'dhcpd_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "dhcpd_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'dhcpd_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'dhcpd_identifier';
/**
* path to dhcps
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
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->tpldir   = $this->rootdir.'/plugins/dhcpd/tpl';

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
		$response = $this->select();
		$t = $this->response->html->template($this->tpldir.'/dhcpd-select.tpl.php');
		$t->add($this->lang['label'], 'label');
		$t->add($response, 'content');
		return $t;

	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {

		$resource = $this->openqrm->resource();

		$file = $this->openqrm->file();
		$content = $file->get_contents($this->openqrm->get('basedir').'/plugins/dhcpd/etc/dhcpd.conf');

		$list = $resource->display_overview(0, 10000, 'resource_id', 'ASC');
		foreach($list as $v) {
			if(isset($v['resource_id']) && $v['resource_id'] !== '0') {
				$content = str_replace('resource'.$v['resource_id'], 'resource<a href="?base=resource&resource_filter='.$v['resource_mac'].'">'.$v['resource_id'].'</a>', $content);
			}
		}
		$content = str_replace("\t", '<span style="padding: 0 10px;">&#160;</span>', $content);
		$content = str_replace("\n", '<br>', $content);
		return $content;	

	}

}

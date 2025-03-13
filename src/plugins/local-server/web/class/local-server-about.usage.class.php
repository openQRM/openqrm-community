<?php
/**
 * local-server-about Documentation
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class local_server_about_usage
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'local_server_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "local_server_about_msg";
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
		$this->openqrm    = $openqrm;

		$this->basedir    = $this->openqrm->get('basedir');
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
		$resource = new resource();
		$resource->get_instance_by_id(0);
		$t = $this->response->html->template($this->tpldir.'/local-server-about-usage.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['usage_integrate_title'], 'usage_integrate_title');
		$t->add($this->lang['usage_remove_title'], 'usage_remove_title');
		$t->add(sprintf($this->lang['usage_integrate_content'], $this->basedir, $resource->ip), 'usage_integrate_content');
		$t->add(sprintf($this->lang['usage_remove_content'], $resource->ip), 'usage_remove_content');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		return $t;
	}


}

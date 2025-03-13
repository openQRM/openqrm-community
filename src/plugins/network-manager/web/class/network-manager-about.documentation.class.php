<?php
/**
 * network-manager-about Documentation
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class network_manager_about_documentation
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'network_manager_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'network_manager_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'network_manager_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'network_manager_about_identifier';
/**
* path to network-managers
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
	 * @return htmlobject_network-manager
	 */
	//--------------------------------------------
	function action() {
		$t = $this->response->html->template($this->tpldir.'/network-manager-about-documentation.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['type_title'], 'type_title');
		$t->add($this->lang['type_content'], 'type_content');
		$t->add($this->lang['tested_title'], 'tested_title');
		$t->add($this->lang['tested_content'], 'tested_content');
		$t->add($this->lang['introduction_title'], 'introduction_title');
		$t->add($this->lang['introduction_content'], 'introduction_content');
		$t->add($this->lang['requirements_title'], 'requirements_title');
		$t->add($this->lang['requirements_list'], 'requirements_list');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		return $t;
	}

}

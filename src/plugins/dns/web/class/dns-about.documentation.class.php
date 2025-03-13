<?php
/**
 * dns-about Documentation
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class dns_about_documentation
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'dns_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "dns_about_msg";
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
		$t = $this->response->html->template($this->tpldir.'/dns-about-documentation.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['type_title'], 'type_title');
		$t->add($this->lang['type_content'], 'type_content');
		$t->add($this->lang['tested_title'], 'tested_title');
		$t->add($this->lang['tested_content'], 'tested_content');
		$t->add($this->lang['provides_title'], 'provides_title');
		$t->add($this->lang['provides_list'], 'provides_list');
		$t->add($this->lang['introduction_title'], 'introduction_title');
		$t->add($this->lang['introduction_content'], 'introduction_content');
		$t->add($this->lang['requirements_title'], 'requirements_title');
		$t->add($this->lang['requirements_list'], 'requirements_list');
		$t->add($this->basedir."/plugins/dns/etc/openqrm-plugin-dns.conf", 'config_file');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		return $t;
	}


}

<?php
/**
 * Documentation Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class documentation_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'documentation_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "documentation_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'documentation_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'documentation_identifier';
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
	'main' => array (
		'tab' => 'Documentation',
		'label' => 'Documentation',
		'title' => 'openQRM Documentation',
		'technical' => 'Technical Documentation',
		'technical_description' => 'Please find the Technical Documentation of openQRM at:',
		'technical_url' => 'http://www.openqrm-enterprise.com/news/details/article/in-depth-documentation-of-openqrm-available.html',
		'howtos' => 'openQRM Howtos & Use Cases',
		'howto1_title' => 'Setup your own openQRM Cloud with KVM on Ubuntu',
		'howto1_url' => 'http://www.openqrm-enterprise.com/news/details/article/howto-setup-your-own-openqrm-cloud-with-kvm-on-ubuntu-lucid-lynx.html',
		'howto2_title' => 'Integrate Ubuntu Enterprise Cloud, Amazon EC2 and Eucalyptus with openQRM',
		'howto2_url' => 'http://www.openqrm-enterprise.com/news/details/article/integrate-ubuntu-enterprise-cloud-amazon-ec2-and-eucalyptus-with-openqrm.html',
		'howto3_title' => 'Setup openQRM Cloud deploying physical Windows Systems on CentOS',
		'howto3_url' => 'http://www.openqrm-enterprise.com/news/details/article/howto-setup-openqrm-cloud-deploying-physical-windows-systems-on-centos-55.html',
		'api' => 'openQRM Cloud SOAP API Documentation',
		'api_description' => 'Please find the API of openQRM at:',
		'api_url' => 'https://support.openqrm-enterprise.com/documentation/openQRM-SOAP-API/',
		'please_wait' => 'Loading. Please wait ..',

	    
	),
);

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
		$this->tpldir   = $this->rootdir.'/server/zz_documentation/tpl';
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/server/zz_documentation/lang", 'documentation.ini');
//		$response->html->debug();

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
			$this->action = "main";
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'main':
				$content[] = $this->main(true);
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
		require_once($this->rootdir.'/server/documentation/class/documentation.api.class.php');
		$controller = new documentation_api($this);
		$controller->action();
	}

	
	//--------------------------------------------
	/**
	 * Documentation Main
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function main( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/zz_documentation/class/documentation.main.class.php');
			$controller = new documentation_main($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['main'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['main']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'main' );
		$content['onclick'] = false;
		if($this->action === 'main'){
			$content['active']  = true;
		}
		return $content;
	}

}

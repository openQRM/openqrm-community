<?php
/**
 * lvm-about Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */


class lvm_storage_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'lvm_storage_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'lvm_storage_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'lvm_storage_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'lvm_storage_about_identifier';
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
	'documentation' => array (
		'tab' => 'About LVM-Storage',
		'label' => 'About LVM-Storage',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The "LVM-Storage" plugin integrate standard Linux Systems as LVM Storage server - NFS/iSCSI/AOE.
					The "LVM-Storage" plugin transforms a standard Linux-box into a rapid-fast-cloning storage-server
					supporting snap-shotting for NFS-, Aoe-, and Iscsi-filesystem-images.
					The snapshots (clones from a "golden server image") are immediatly available for deployment and
					saving space on the storage-subsystem because just the delta of the server image is being stored.
					   ',
		'requirements_title' => 'Requirements',
		'requirements_list' => '<ul><li>A resource for the LVM-Storage Storage (this can be a remote system integrated into openQRM e.g. via the "local-server" plugin or the openQRM server itself)</li>
					<li>One (or more) lvm volume group(s) with free space dedicated for the LVM-Storage Volumes</li>
				   <li>The following packages must be installed: nfs-common, nfs-kernel-server, screen, rsync, vblade, aoetools, iscsitarget, open-iscsi</li></ul>',

		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.',

		'provides_title' => 'Provides',
		'provides_list' => '<ul><li>Storage type: "Lvm Storage Server (Aoe/Nfs/Iscsi)"</li>
					<li>Deployment types: "Lvm Aoe/Nfs/Iscsi-root deployment"</li></ul>',
		'type_title' => 'Plugin Type',
		'type_content' => 'Storage',

		'deployment_title' => 'Deployment Type',
		'deployment_content' => 'Network-Deployment',

		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
		'network_deploymet' => 'Network-Deployment',
		'doc1' => '',
	),
	'bootservice' => array (
		'tab' => 'Boot-Service',
		'label' => 'LVM-Storage Boot-Service',
		'boot_service_title' => 'LVM-Storage Host Boot-Service',
		'boot_service_content' => 'The LVM-Storage Plugin provides an openQRM Boot-Service.
			This "LVM-Storage Boot-Service" is automatically downloaded and executed by the openQRM-Client on all integrated Systems.
			The Boot-Service is located at:<br>
			<br>
				<i><b>/usr/share/openqrm/plugins/lvm-storage/web/boot-service-lvm-storage.tgz</b></i>
			<br>
			<br>
			The "LVM-Storage Boot-Service contains the Client files of the LVM-Storage Plugin.<br>
			Also a configuration file for the LVM-Storage server is included in this Boot-Service.<br>
			<br>
			The Boot-Service configuration can be viewed and administrated by the "openqrm" utility.<br>
			To view the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n lvm-storage -a default</b></i>
			<br>
			<br>
			To view a Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n lvm-storage -a [appliance-name]</b></i>
			<br>
			<br>
			To adapt a parameter in the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n lvm-storage -a default -k [key] -v [value]</b></i>
			<br>
			<br>
			To adapt a paramter in the Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n lvm-storage -a [appliance-name] -k [key] -v [value]</b></i>
			<br>
			<br>
			In case the openmQRM Server itself is used as the LVM-Storage Storage please edit:<br>
			<br>
				<i><b>/usr/share/openqrm/plugins/lvm-storage/etc/openqrm-plugin-lvm-storage.conf</b></i>
			<br>
			<br>
			and set the configuration keys.<br>
			<br>
			',
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
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/lvm-storage/lang", 'lvm-storage-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/lvm-storage/tpl';
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
			$this->action = "documentation";
		}
		$content = array();
		switch( $this->action ) {
			case '':
			case 'documentation':
				$content[] = $this->documentation(true);
			break;
			case 'bootservice':
				$content[] = $this->bootservice(true);
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
	 * About LVM-Storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage-about.documentation.class.php');
			$controller = new lvm_storage_about_documentation($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['documentation'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['documentation']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'documentation' );
		$content['onclick'] = false;
		if($this->action === 'documentation'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Boot-Service
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function bootservice( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lvm-storage/class/lvm-storage-about.bootservice.class.php');
			$controller = new lvm_storage_about_bootservice($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['bootservice'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['bootservice']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'bootservice' );
		$content['onclick'] = false;
		if($this->action === 'bootservice'){
			$content['active']  = true;
		}
		return $content;
	}




}

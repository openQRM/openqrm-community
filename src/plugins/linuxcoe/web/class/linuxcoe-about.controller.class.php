<?php
/**
 * linuxcoe-about Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */


class linuxcoe_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'linuxcoe_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'linuxcoe_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'linuxcoe_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'linuxcoe_about_identifier';
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
		'tab' => 'About LinuxCOE',
		'label' => 'About LinuxCOE',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The "LinuxCOE" plugin integrates <a href="http://linuxcoe-project.org/" target="_BLANK">LinuxCOE</a> Install Server for automatic Linux deployments.
			LinuxCOE comes with a user-friendly UI to create automatic Linux installations for different distributions (Debian/Ubuntu/CentOS etc.) resulting in ISO files/images which can be used to
			fully automatically install physical Server and also Virtual Machines.<br>The integration of LinuxCOE in openQRM provides two different modes:<br><br>

				<strong>Automatic Installations from ISO</strong><br>
				After creating an Installation Template the resulting ISO image can be burned on an CD to automatically install a physical Server (the initial goal of the LinuxCOE Project).<br><br>
				In openQRM the LinuxCOE ISO Images are also automatically available on Virtualization Host from the type "local-deployment VMs" (e.g. "kvm-storage" and "xen-storage") in the /linuxcoe-iso directory.
				Simply configure a Virtual Machine to boot from such a LinuxCOE ISO image for an fully automatic VM installation.<br><br>
				Please notice that after a successfull installation the VM will most likely try to boot from the ISO image again after the automatic install procedure finished!
				Please stop the VMs Appliance after the initial automatic installation, then re-configure the Virtual Machine to boot from "local" and start the Appliance again.<br><br>

				<strong>Automatic Network-Installations</strong><br>
				The LinuxCOE integration in openQRM provides the capability to use the pre-configured automatic installations templates also for "network-deployment".
				For "network-deployment" physical Server and Virtual Machines from the type "local-deployment for VMs" (e.g. "kvm-storage" and "xen-storage") are supported.<br>
				-> physical Server and VMs for "network-deployment" must be set to network-boot (PXE) either in the BIOS or VM configuration!<br><br>
				The deployment of the LinuxCOE templates works via openQRMs "install-from-template" mechanism which allows to attach those automatic installation templates to Images.
				For Virtual Machines the templates can be directly attached to the Virtual Machines Image, for physical Server please create an Image pointing to a local disk with the LinuxCOE "Image Manager".<br><br>
				Starting an Appliance with an LinuxCOE automatic installation template attached to its Image automatically applies the specified configuration and automatically installs the systems.<br><br>
				Network-deploying LinuxCOE templates automatically installs the openQRM-Client and integrates the system into openQRM.
				After the automatic installation the resource (physical Server or VM) is automatically set to local-boot.<br><br>

				<strong>Cloud deployment</strong><br>
				Images with an attached LinuxCOE <strong>Automatic Network-Installation</strong> template are fully supported for Cloud-deployment!<br><br>',

		'requirements_title' => 'Requirements',
		'requirements_list' => '<ul><li>A LinuxCOE Install Server Storage using the openQRM as the resource</li>
				   <li>The following packages must be installed: screen, make, autoconf, automake, genisoimage, sudo, nfs-kernel-server, nfs-common</li></ul>',

		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.',

		'provides_title' => 'Provides',
		'provides_list' => '<ul><li>Storage type: "linuxcoe-deployment"</li>
					<li>Deployment types: "Automatic Linux Installation (LinuxCOE)"</li></ul>',

		'howto_title' => 'How to use',
		'howto_list' => '<ul><li>Create a new Storage from type "linuxcoe-deployment" using the openQRM Server as the resource</li>
					<li>Create one or more LinuxCOE Installation Templates</li>
					<li>Use the Template Manager to add a description to your Installation Templates</li>
					<li>Choose either <strong>Automatic Installations from ISO</strong> or <strong>Automatic Network-Installations</strong> to deploy the Templates</li></ul>',

		'type_title' => 'Plugin Type',
		'type_content' => 'Deployment',

		'deployment_title' => 'Deployment Type',
		'deployment_content' => 'Local-Deployment',

		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
		'network_deploymet' => 'Network-Deployment',
		'doc1' => '',
	),
	'bootservice' => array (
		'tab' => 'Boot-Service',
		'label' => 'LinuxCOE Boot-Service',
		'boot_service_title' => 'LinuxCOE Boot-Service',
		'boot_service_content' => 'The LinuxCOE Plugin provides an openQRM Boot-Service.
			This "LinuxCOE Boot-Service" is automatically downloaded and executed by the openQRM-Client on all integrated Systems.
			The Boot-Service is located at:<br>
			<br>
				<i><b>/usr/share/openqrm/plugins/linuxcoe/web/boot-service-linuxcoe.tgz</b></i>
			<br>
			<br>
			The "LinuxCOE Boot-Service contains the Client files of the LinuxCOE Plugin.<br>
			Also a configuration file for the LinuxCOE server is included in this Boot-Service.<br>
			<br>
			The Boot-Service configuration can be viewed and administrated by the "openqrm" utility.<br>
			To view the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n linuxcoe -a default</b></i>
			<br>
			<br>
			To view a Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n linuxcoe -a [appliance-name]</b></i>
			<br>
			<br>
			To adapt a parameter in the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n linuxcoe -a default -k [key] -v [value]</b></i>
			<br>
			<br>
			To adapt a paramter in the Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n linuxcoe -a [appliance-name] -k [key] -v [value]</b></i>
			<br>
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/linuxcoe/lang", 'linuxcoe-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/linuxcoe/tpl';
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
	 * About LinuxCOE
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/linuxcoe/class/linuxcoe-about.documentation.class.php');
			$controller = new linuxcoe_about_documentation($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/linuxcoe/class/linuxcoe-about.bootservice.class.php');
			$controller = new linuxcoe_about_bootservice($this->openqrm, $this->response);
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

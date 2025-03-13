<?php
/**
 * KVM Adds/Removes an Image from a Volume
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_vm_sysinfo
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kvm_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'kvm_vm_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kvm_vm_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kvm_vm_identifier';
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
		$this->response   = $response;
		$this->openqrm    = $openqrm;
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
		$response = $this->sysinfo();
		return $response;
	}

	//--------------------------------------------
	/**
	 * Sysinfo
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function sysinfo() {

		$appliance = $this->openqrm->appliance();
		$appliance->get_instance_by_id($this->response->html->request()->get('appliance_id'));
		$resource = $this->openqrm->resource();
		$resource->get_instance_by_id($appliance->resources);

		$filename = $resource->id.'.sysinfo';
		$file = $this->openqrm->get('basedir').'/plugins/kvm/web/kvm-stat/'.$filename;

		$command  = $this->openqrm->get('basedir').'/plugins/kvm/bin/openqrm-kvm-sysinfo';
		$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
		$command .= ' --file-name '.$filename;
		#$command .= ' --openqrm-cmd-mode background';
		
		if($this->openqrm->file()->exists($file)) {
			$this->openqrm->file()->remove($file);
		}
		$resource->send_command($resource->ip, $command);
		while (!$this->openqrm->file()->exists($file)) // check if the data file has been modified
		{
			usleep(10000); // sleep 10ms to unload the CPU
			clearstatcache();
		}

		$content = $this->openqrm->file()->get_contents($file);

		$image = $this->openqrm->image();
		$list  = $image->display_overview(0,10000,'image_id','ASC');
		foreach($list as $v) {
			if($v['image_rootdevice'] !== '') {
				$content = preg_replace('~[^=]('.$v['image_rootdevice'].')([^a-zA-Z0-9])~', '<span style="font-weight:bold">$1</span>$2', $content);
			}
		}

		$resource = $this->openqrm->resource();
		$list     = $resource->display_overview(0,10000,'resource_id','ASC');
		foreach($list as $v) {
			if($v['resource_hostname'] !== '') {
				$content = preg_replace('~([0-9] )('.$v['resource_hostname'].')\n~', '$1<span style="font-weight:bold">$2</span>'."\n", $content);
			}
		}

		$d = $this->response->html->div();
		$d->id = "kvm-sysinfo";
		$d->add($content);

		$this->openqrm->file()->remove($file);

		return $d;

	}

}

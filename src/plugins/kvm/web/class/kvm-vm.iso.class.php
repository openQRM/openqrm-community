<?php
/**
 * kvm-vm ISO
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_vm_iso
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
* openqrm rootdir
* @access public
* @var string
*/
var $rootdir;
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
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
		$this->user	= $openqrm->user();
		$id = $this->response->html->request()->get('appliance_id');
		if($id === '') {
			return false;
		}
		$appliance = new appliance();
		$resource  = new resource();
		$appliance->get_instance_by_id($id);
		$resource->get_instance_by_id($appliance->resources);
		$this->resource  = $resource;
		$this->appliance = $appliance;
		$this->statfile  = $this->openqrm->get('basedir').'/plugins/kvm/web/kvm-stat/'.$resource->id.'.pick_iso_config';

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
//		$response = $this->iso();

		#$this->response->redirect(
		#	$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response)
		#);
//		echo $response;

		$this->iso();
	}

	//--------------------------------------------
	/**
	 * ISO
	 *
	 * @access public
	 * @return htmlobject_response
	 *
	 *
	 * for testing
	 * http://cloud/openqrm/base/plugins/kvm/index-vm.php?appliance_id=&kvm_vm_action=iso&appliance_id=2&path=/tmp
	 *
	 */
	//--------------------------------------------
	function iso() {
		$response = $this->response;

		$iso_path = $response->html->request()->get('path');

		// TODO better validation
		if ($iso_path !== '') {

		    $command  = $this->openqrm->get('basedir')."/plugins/kvm/bin/openqrm-kvm-vm iso";
		    $command .= ' -q '.$iso_path;
		    $command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
			$command .= ' --openqrm-ui-user '.$this->user->name;
			$command .= ' --openqrm-cmd-mode background';

		    $file = $this->openqrm->get('basedir').'/plugins/kvm/web/kvm-stat/'.$this->resource->id.'.pick_iso_config';
		    if($this->file->exists($file)) {
			    $this->file->remove($file);
		    }
		    $this->resource->send_command($this->resource->ip, $command);
		    while (!$this->file->exists($file)) // check if the data file has been modified
		    {
		      usleep(10000); // sleep 10ms to unload the CPU
		      clearstatcache();
		    }
			$lines = $this->file->get_contents($file);
			$lines = explode("\n", $lines);

			if(is_array($lines) && count($lines) > 1) {
				$i = 0;
				foreach($lines as $c) {
					$tmp = explode('@', $c);
					if($tmp[0] === 'P') {
						$base = $tmp[1];
					} else {
						if($tmp[1] !== '.') {
							$a  = $response->html->a();
							$a->label = $tmp[1];
							if($tmp['1'] === '..') {
								if($base !== '/') {
									$path = substr($base, 0, strrpos($base,'/'));
									if($path === '') {
										$path = '/';
									}
									$a->href = $response->get_url($this->actions_name, 'iso' ).'&path='.$path;
									$a->css  = 'folder';
									$body[$i]['file'] = $c;
									$body[$i]['name'] = $a->get_string();
									$i++;
								}
							} else {
								if($tmp[0] === 'F') {
									$a->handler = 'onclick="alert(\''.$base.'/'.$tmp[1].'\');"';
									$a->css  = 'file';
								}
								if($tmp[0] === 'D') {
									$a->href = $response->get_url($this->actions_name, 'iso' ).'&path='.$base.'/'.$tmp['1'];
									$a->css  = 'folder';
								}
								$body[$i]['file'] = $c;
								$body[$i]['name'] = $a->get_string();
								$i++;
							}
						}
					}
				}
			}


			$table = $response->html->tablebuilder('kvm_vm_iso', $response);

			$head['file']['hidden']   = true;
			$head['file']['sortable'] = false;
			$head['name']['title'] = 'Name';
			$head['name']['map'] = 'file';

			$table->max                       = count($body);
			$table->limit                       = count($body);
			$table->offset                     = 0;
			$table->order                     = 'ASC';
			$table->form_action           = $html->thisfile;
			$table->form_method         = 'GET';
			$table->css                        = 'htmlobject_table';
			$table->border                   = 1;
			$table->id                          = 'Table';
			$table->head                     = $head;
			$table->body                     = $body;
			$table->sort                       = 'file';
			$table->sort_form              = false;
			$table->sort_link                = false;
			$table->autosort                = true;

			$table = $table->get_object();
			unset($table->__elements['pageturn_head']);
			unset($table->__elements[0]);


			echo $table->get_string();


		} else {
		    echo 'no';
		}		
	}

}

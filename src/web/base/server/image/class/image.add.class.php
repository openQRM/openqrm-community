<?php
/**
 * Image Add
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2014, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2014, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class image_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'image_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "image_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'image_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'image_identifier';
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
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
		$id = $this->response->html->request()->get('appliance_id');
		if($id !== '') {
			$this->response->add('appliance_id', $id);
			$this->appliance = new appliance();
			$this->appliance->get_instance_by_id($id);
		}
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

		$deployment = new deployment();
		$deployment_id_array = $deployment->get_deployment_ids();
		$info = '<div class="infotext">'.$this->lang['info'].'</div>';

		$storage_link_section = '';
		if(isset($this->appliance)) {
			$resource = new resource();
			$resource->get_instance_by_id($this->appliance->resources);
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($resource->vtype);
			#$resourceinfo  = '<b>Server:</b> '.$this->appliance->id.' / '.$this->appliance->name.'<br>';
			$info = '<b>Resource:</b> '.$resource->id.' / '.$resource->ip.' '.$resource->hostname.' - '.$virtualization->name.'<br><br>'.$info;
			foreach($deployment_id_array as $id) {
				$new_image_link = '';
				$deployment->get_instance_by_id($id['deployment_id']);
				if (($deployment->storagetype != 'none') && ($deployment->storagetype != 'local-server')) {
					if (strstr($resource->capabilities, "TYPE=local-server")) {
						// disable - local-server already has an image and cannot be re-deployed with a differenet one
						continue;
					}
					else if (strstr($virtualization->type, "-vm-local")) {
						// get virt plugin name, check if deployment->storagetype == virt plugin name
						if ($deployment->storagetype === $virtualization->get_plugin_name()) {
							$new_image_link = $this->response->get_url($this->actions_name, 'load').'&iplugin='.$deployment->storagetype;

						}
					}
					else if (strstr($virtualization->type, "-vm-net")) {
						// find with image-deployment type hook if deployment is network-deployment
						$is_network_deployment = false;
						$rootdevice_identifier_hook = $this->openqrm->get('basedir')."/web/boot-service/image.".$deployment->type.".php";
						if (file_exists($rootdevice_identifier_hook)) {
							require_once "$rootdevice_identifier_hook";
							$image_is_network_deployment_function="get_".$deployment->type."_is_network_deployment";
							$image_is_network_deployment_function=str_replace("-", "_", $image_is_network_deployment_function);
							if($image_is_network_deployment_function()) {
								$new_image_link = $this->response->get_url($this->actions_name, 'load').'&iplugin='.$deployment->storagetype;
							}
						}
					} else {
						// $new_image_link = "/openqrm/base/index.php?plugin=".$deployment->storagetype;
						// same as vm-net
						// all network deployment types
					}
					if($new_image_link !== '') {
						$storage_link_section .= "<a href='".$new_image_link."' style='text-decoration: none'><img title='".sprintf($this->lang['create_image'], $deployment->description)."' alt='".sprintf($this->lang['create_image'], $deployment->description)."' src='/openqrm/base/plugins/".$deployment->storagetype."/img/plugin.png' border=0> ".$deployment->description."</a><br>";
					}
				}
			}
		} else {
			foreach ($deployment_id_array as $deployment_id) {
				$deployment->get_instance_by_id($deployment_id['deployment_id']);
				if (($deployment->storagetype != 'none') && ($deployment->storagetype != 'local-server')) {
					#$new_image_link = "/openqrm/base/index.php?plugin=".$deployment->storagetype;
					$new_image_link = $this->response->get_url($this->actions_name, 'load').'&iplugin='.$deployment->storagetype;
					switch ($deployment->storagetype) {
						case 'coraid-storage':
						case 'equallogic-storage':
						case 'netapp-storage':
							$new_image_link = "/openqrm/base/index.php?iframe=/openqrm/base/plugins/".$deployment->storagetype."/".$deployment->storagetype."-manager.php";
							break;
					}
					$storage_link_section .= "<a href='".$new_image_link."' style='text-decoration: none'><img title='".sprintf($this->lang['create_image'], $deployment->description)."' alt='".sprintf($this->lang['create_image'], $deployment->description)."' src='/openqrm/base/plugins/".$deployment->storagetype."/img/plugin.png' border=0> ".$deployment->description."</a><br>";
				}
			}
		}

		if (!strlen($storage_link_section)) {
			$storage_link_section = $this->lang['start_storage_plugin'];
		}

		$t = $this->response->html->template($this->tpldir.'/image-add.tpl.php');
		$t->add($storage_link_section, 'image_new');
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['title'], 'title');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($info, 'info');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}


}
?>

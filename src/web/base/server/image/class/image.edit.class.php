<?php
/*
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
*/

class image_edit
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
		$this->user       = $openqrm->user();
		$this->response->params['image_id'] = $this->response->html->request()->get('image_id');
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
		$response = $this->edit();
		if(isset($response->msg)) {
			if ($response->msg === 'install-from-template') {
				$this->response->redirect(
					$this->response->html->thisfile.'?base=image&image_action=install1&image_id='.$response->image_id.'&install_from_template='.$response->install_from_template
				);
			} else {
				// check if we are in the appliance wizard
				if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 3) {
					// only if this is the image of the appliance in the wizard
					$wizard_appliance = new appliance();
					$wizard_appliance->get_instance_by_id($this->user->wizard_id);
					if ($wizard_appliance->imageid == $response->image_id) {
						// this is our image redirect to step 4
						$this->response->redirect(
							$this->response->html->thisfile.'?base=appliance&appliance_action=step4&appliance_id='.$this->user->wizard_id.'&image_id='.$response->image_id
						);
					} else {
						// regular select redir
						$this->response->redirect(
							$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
						);
					}

				} else {
					$this->response->redirect(
						$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
					);
				}
			}
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}

		$t = $this->response->html->template($this->tpldir.'/image-edit.tpl.php');
		$t->add(sprintf($this->lang['label'], $response->name), 'label');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['lang_password_generate'], 'lang_password_generate');
		$t->add($this->lang['lang_password_show'], 'lang_password_show');
		$t->add($this->lang['lang_password_hide'], 'lang_password_hide');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function edit() {
		$response  = $this->get_response();
		$form      = $response->form;
		$id        = $this->response->html->request()->get('image_id');

		if($id !== '') {

			// check password
			$pass = $form->get_request('image_password');
			if($pass !== '' && $pass !== $form->get_request('image_password_2')) {
				$form->set_error('image_password_2', $this->lang['error_password']);
			}

			$image = new image();
			$image->get_instance_by_id($id);
			if(!$form->get_errors() && $this->response->submit()) {
				$image_password			= $this->response->html->request()->get('image_password');
				$install_from_local		= $this->response->html->request()->get('install_from_local');
				$transfer_to_local		= $this->response->html->request()->get('transfer_to_local');
				$install_from_nfs		= $this->response->html->request()->get('install_from_nfs');
				$transfer_to_nfs		= $this->response->html->request()->get('transfer_to_nfs');
				$install_from_template	= $this->response->html->request()->get('install_from_template');
				$image_comment			= $this->response->html->request()->get('image_comment');
				$image_version			= $this->response->html->request()->get('image_version');
				// update image
				$fields['image_comment'] = $image_comment;
				$fields['image_version'] = $image_version;
				$image->update($id, $fields);
				$image->get_instance_by_id($id);
				# set password if given
				if(strlen($image_password)) {
					$image->set_root_password($id, $image_password);
				} else {
					$CMD="rm -f ".$this->openqrm->get('basedir')."/web/action/image-auth/iauth.".$id.".php";
					exec($CMD);
				}
				// install-from-nfs
				if(strlen($install_from_nfs)) {
					$install_from_nfs_image = new image();
					$install_from_nfs_image->get_instance_by_id($install_from_nfs);
					$install_from_nfs_storage = new storage();
					$install_from_nfs_storage->get_instance_by_id($install_from_nfs_image->storageid);
					$install_from_nfs_storage_resource = new resource();
					$install_from_nfs_storage_resource->get_instance_by_id($install_from_nfs_storage->resource_id);
					$install_from_nfs_storage_ip=$install_from_nfs_storage_resource->ip;
					$install_from_nfs_storage_path=$install_from_nfs_image->rootdevice;
					$install_from_nfs_path = $install_from_nfs_image->storageid.":".$install_from_nfs_storage_ip.":".$install_from_nfs_storage_path;
					$image->set_deployment_parameters("IMAGE_INSTALL_FROM_NFS", $install_from_nfs_path);
				} else {
					$image->set_deployment_parameters("IMAGE_INSTALL_FROM_NFS", "");
				}
				// transfer-to-nfs, we have to refresh the image object here
				$image->get_instance_by_id($id);
				if(strlen($transfer_to_nfs)) {
					$transfer_to_nfs_image = new image();
					$transfer_to_nfs_image->get_instance_by_id($transfer_to_nfs);
					$transfer_to_nfs_storage = new storage();
					$transfer_to_nfs_storage->get_instance_by_id($transfer_to_nfs_image->storageid);
					$transfer_to_nfs_storage_resource = new resource();
					$transfer_to_nfs_storage_resource->get_instance_by_id($transfer_to_nfs_storage->resource_id);
					$transfer_to_nfs_storage_ip=$transfer_to_nfs_storage_resource->ip;
					$transfer_to_nfs_storage_path=$transfer_to_nfs_image->rootdevice;
					$transfer_to_nfs_path = $transfer_to_nfs_image->storageid.":".$transfer_to_nfs_storage_ip.":".$transfer_to_nfs_storage_path;
					$image->set_deployment_parameters("IMAGE_TRANSFER_TO_NFS", $transfer_to_nfs_path);
				} else {
					$image->set_deployment_parameters("IMAGE_TRANSFER_TO_NFS", "");
				}
				// install-from-local, we have to refresh the image object again
				$image->get_instance_by_id($id);
				if(strlen($install_from_local)) {
					$image->set_deployment_parameters("IMAGE_INSTALL_FROM_LOCAL", $install_from_local);
				} else {
					$image->set_deployment_parameters("IMAGE_INSTALL_FROM_LOCAL", "");
				}
				// transfer-to-local, we have to refresh the image object again
				$image->get_instance_by_id($id);
				if(strlen($transfer_to_local)) {
					$image->set_deployment_parameters("IMAGE_TRANSFER_TO_LOCAL", $transfer_to_local);
				} else {
					$image->set_deployment_parameters("IMAGE_TRANSFER_TO_LOCAL", "");
				}
				// reset deployment parameter INSTALL_CONFIG
				$image->set_deployment_parameters("INSTALL_CONFIG", "");
				if (strlen($install_from_template)) {
					// redirect to install-from-template step1
					$response->msg = 'install-from-template';
					$response->install_from_template = $install_from_template;

				} else {
					$response->msg = sprintf($this->lang['msg'], $image->name);
				}

			
				$response->image_id = $id;
			}
			$response->name = $image->name;
		} else {
			$response->msg = '';
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'edit');
		$id = $this->response->html->request()->get('image_id');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$image  = $this->openqrm->image();
		$image->get_instance_by_id($id);
		$storage = $this->openqrm->storage();
		$storage->get_instance_by_id($image->storageid);
		$deployment = $this->openqrm->deployment();
		$deployment->get_instance_by_id($storage->type);
		$storage_resource = $this->openqrm->resource();
		$storage_resource->get_instance_by_id($storage->resource_id);

		// making the deployment parameters plugg-able
		$rootdevice_identifier_hook = $this->openqrm->get('basedir')."/web/boot-service/image.".$deployment->type.".php";
		if (file_exists($rootdevice_identifier_hook)) {
			require_once "$rootdevice_identifier_hook";
			// run function returning rootdevice array
			$get_rootfs_transfer_methods_function = "get_".$deployment->type."_rootfs_transfer_methods";
			$get_rootfs_transfer_methods_function = str_replace("-", "_", $get_rootfs_transfer_methods_function);
			$rootfs_transfer_methods = $get_rootfs_transfer_methods_function();

			$get_rootfs_set_password_method_function = "get_".$deployment->type."_rootfs_set_password_method";
			$get_rootfs_set_password_method_function = str_replace("-", "_", $get_rootfs_set_password_method_function);
			$rootfs_set_password_method = $get_rootfs_set_password_method_function();

			$get_rootfs_local_deployment_enabled_function = "get_".$deployment->type."_local_deployment_enabled";
			$get_rootfs_local_deployment_enabled_function = str_replace("-", "_", $get_rootfs_local_deployment_enabled_function);
			$rootfs_local_deployment_enabled = $get_rootfs_local_deployment_enabled_function();
		} else {
			$rootfs_transfer_methods = false;
			$rootfs_set_password_method = false;
			$rootfs_local_deployment_enabled = false;
		}

		// making the local deployment parameter plugg-able
		$local_deployment_methods_input = "";
		if ($rootfs_local_deployment_enabled) {
			$local_deployment_methods_arr[] = array("value" => "", "label" => "");
			$local_deployment = new deployment();
			$deployment_id_arr = $local_deployment->get_deployment_ids();
			foreach($deployment_id_arr as $deployment_id) {
				$local_deployment->get_instance_by_id($deployment_id['deployment_id']);
				$local_deployment_templates_identifier_hook = $this->openqrm->get('basedir')."/web/boot-service/template.".$local_deployment->type.".php";
				if (file_exists($local_deployment_templates_identifier_hook)) {
					require_once "$local_deployment_templates_identifier_hook";
					$deployment_function="get_"."$local_deployment->type"."_methods";
					$deployment_function=str_replace("-", "_", $deployment_function);
					$local_deployment_methods_arr[] = $deployment_function();
				}
			}
		}

		// in case the deployment method provides the rootfs-transfer options
		$nfs_image_identifier_array = array();
		if ($rootfs_transfer_methods) {
			// prepare the install-from and transfer-to selects
			$nfs_image_identifier_array[] = array("value" => "", "label" => "");
			$nfs_image = new image();
			$image_arr = $nfs_image->get_ids();
			foreach ($image_arr as $id) {
				$i_id = $id['image_id'];
				$timage = new image();
				$timage->get_instance_by_id($i_id);
				if (strstr($timage->type, "nfs")) {
					$timage_name = $timage->name;
					$nfs_image_identifier_array[] = array("value" => "$i_id", "label" => "$timage_name");
				}
			}
		}


		$html = new htmlobject_div();
		$html->text = '<a href="../../plugins/'.$deployment->storagetype.'/'.$deployment->storagetype.'-about.php" target="_blank" class="doculink">'.$deployment->description.'</a>';
		$html->id = 'htmlobject_image_type';

		$storage_deploy_box = new htmlobject_box();
		$storage_deploy_box->id = 'htmlobject_box_image_deploy';
		$storage_deploy_box->css = 'htmlobject_box';
		$storage_deploy_box->label = 'Deployment';
		$storage_deploy_box->content = $html;

		$html = new htmlobject_div();
		$html->text = $deployment->storagedescription;
		$html->id = 'htmlobject_storage_type';

		$storage_type_box = new htmlobject_box();
		$storage_type_box->id = 'htmlobject_box_storage_type';
		$storage_type_box->css = 'htmlobject_box';
		$storage_type_box->label = 'Storage';
		$storage_type_box->content = $html;

		#$storage_resource->id /
		$html = new htmlobject_div();
		$html->text = "$storage_resource->ip";
		$html->id = 'htmlobject_storage_resource';

		$storage_resource_box = new htmlobject_box();
		$storage_resource_box->id = 'htmlobject_box_storage_resource';
		$storage_resource_box->css = 'htmlobject_box';
		$storage_resource_box->label = 'Resource';
		$storage_resource_box->content = $html;



		// in case the deployment type allows to set the password in the image
		if ($rootfs_set_password_method) {
			$d['image_password']['label']                    = $this->lang['form_image_password'];
			$d['image_password']['required']                 = false;
			$d['image_password']['object']['type']           = 'htmlobject_input';
			$d['image_password']['object']['attrib']['id']   = 'pass_1';
			$d['image_password']['object']['attrib']['type'] = 'password';
			$d['image_password']['object']['attrib']['name'] = 'image_password';

			$d['image_password_2']['label']                    = $this->lang['form_image_password_repeat'];
			$d['image_password_2']['required']                 = false;
			$d['image_password_2']['object']['type']           = 'htmlobject_input';
			$d['image_password_2']['object']['attrib']['id']   = 'pass_2';
			$d['image_password_2']['object']['attrib']['type'] = 'password';
			$d['image_password_2']['object']['attrib']['name'] = 'image_password_2';
		} else {
			$d['image_password']   = '';
			$d['image_password_2'] = '';
		}


		if ($rootfs_transfer_methods) {

			$d['install_from_local']['label']                          = $this->lang['form_install_from_local'];
			$d['install_from_local']['required']                       = false;
			$d['install_from_local']['object']['type']                 = 'htmlobject_input';
			$d['install_from_local']['object']['attrib']['id']         = 'install_from_local';
			$d['install_from_local']['object']['attrib']['name']       = 'install_from_local';

			$d['transfer_to_local']['label']                          = $this->lang['form_transfer_to_local'];
			$d['transfer_to_local']['required']                       = false;
			$d['transfer_to_local']['object']['type']                 = 'htmlobject_input';
			$d['transfer_to_local']['object']['attrib']['id']         = 'transfer_to_local';
			$d['transfer_to_local']['object']['attrib']['name']       = 'transfer_to_local';


			$d['install_from_nfs']['label']                          = $this->lang['form_install_from_nfs'];
			$d['install_from_nfs']['required']                       = false;
			$d['install_from_nfs']['object']['type']                 = 'htmlobject_select';
			$d['install_from_nfs']['object']['attrib']['index']      = array('value', 'label');
			$d['install_from_nfs']['object']['attrib']['id']         = 'install_from_nfs';
			$d['install_from_nfs']['object']['attrib']['name']       = 'install_from_nfs';
			$d['install_from_nfs']['object']['attrib']['options']    = $nfs_image_identifier_array;

			$d['transfer_to_nfs']['label']                          = $this->lang['form_transfer_to_nfs'];
			$d['transfer_to_nfs']['required']                       = false;
			$d['transfer_to_nfs']['object']['type']                 = 'htmlobject_select';
			$d['transfer_to_nfs']['object']['attrib']['index']      = array('value', 'label');
			$d['transfer_to_nfs']['object']['attrib']['id']         = 'transfer_to_nfs';
			$d['transfer_to_nfs']['object']['attrib']['name']       = 'transfer_to_nfs';
			$d['transfer_to_nfs']['object']['attrib']['options']    = $nfs_image_identifier_array;

		} else {

			$d['install_from_local'] = '';
			$d['transfer_to_local'] = '';
			$d['install_from_nfs'] = '';
			$d['transfer_to_nfs'] = '';
		}


		if ($rootfs_local_deployment_enabled) {

			$d['install_from_template']['label']                          = $this->lang['form_install_from_template'];
			$d['install_from_template']['required']                       = false;
			$d['install_from_template']['object']['type']                 = 'htmlobject_select';
			$d['install_from_template']['object']['attrib']['index']      = array('value', 'label');
			$d['install_from_template']['object']['attrib']['id']         = 'install_from_template';
			$d['install_from_template']['object']['attrib']['name']       = 'install_from_template';
			$d['install_from_template']['object']['attrib']['options']    = $local_deployment_methods_arr;

		} else {
			$d['install_from_template'] = '';
		}
		$image_version_arr = $image->get_os_version();
		$d['image_version']['label']                          = $this->lang['form_image_version'];
		$d['image_version']['required']                       = false;
		$d['image_version']['object']['type']                 = 'htmlobject_select';
		$d['image_version']['object']['attrib']['index']      = array('value', 'label');
		$d['image_version']['object']['attrib']['id']         = 'image_version';
		$d['image_version']['object']['attrib']['name']       = 'image_version';
		$d['image_version']['object']['attrib']['options']    = $image_version_arr;
		$d['image_version']['object']['attrib']['selected']   = array($image->version);
		
		$d['image_comment']['label']                     = $this->lang['form_comment'];
		$d['image_comment']['object']['type']            = 'htmlobject_textarea';
		$d['image_comment']['object']['attrib']['id']    = 'comment';
		$d['image_comment']['object']['attrib']['name']  = 'image_comment';
		$d['image_comment']['object']['attrib']['value'] = $image->comment;

                $d['image_dp']['label']                     = 'Deployment parameter';
                $d['image_dp']['object']['type']            = 'htmlobject_textarea';
                $d['image_dp']['object']['attrib']['id']    = 'deployment_parameter';
                $d['image_dp']['object']['attrib']['name']  = 'deployment_parameter';
                $d['image_dp']['object']['attrib']['value'] = $image->deployment_parameter;
                $d['image_dp']['object']['attrib']['readonly'] = true;		


		$form->add($d);
		$response->form = $form;
		return $response;
	}

}

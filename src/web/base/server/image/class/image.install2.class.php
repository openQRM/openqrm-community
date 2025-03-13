<?php
/**
 * Image install-from-template step2
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2014, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2014, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class image_install2
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
		$this->response->add('image_id', $this->response->html->request()->get('image_id'));
		$this->response->add('install_server', $this->response->html->request()->get('install_server'));

		$image = new image();
		$image->get_instance_by_id($this->response->html->request()->get('image_id'));
		$this->image = $image;
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
		$response = $this->install2();
		if(isset($response->local_deployment_templates_select)) {

			// check if we are in the appliance wizard
			if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 4) {
				// only if this is the image of the appliance in the wizard
				$wizard_appliance = new appliance();
				$wizard_appliance->get_instance_by_id($this->user->wizard_id);
				if ($wizard_appliance->imageid == $response->image_id) {
					// this is our image
					$this->response->redirect(
						$this->response->html->thisfile.'?base=appliance&appliance_action=step'.$this->user->wizard_step.'&appliance_id='.$this->user->wizard_id.'&image_id='.$response->image_id
					);
				} else {
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
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}

		$t = $this->response->html->template($this->tpldir.'/image-install2.tpl.php');
		$t->add(sprintf($this->lang['label'], $this->image->name, $this->image->storageid), 'label');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
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
	function install2() {
		$response  = $this->get_response();
		$form      = $response->form;
		$install_server			= $this->response->html->request()->get('install_server');
		$id						= $this->response->html->request()->get('image_id');
		$template		= $this->response->html->request()->get('template');
		$persistent		= $this->response->html->request()->get('persistent');
		$parameter1		= $this->response->html->request()->get('parameter1');
		$parameter2		= $this->response->html->request()->get('parameter2');
		$parameter3		= $this->response->html->request()->get('parameter3');
		$parameter4		= $this->response->html->request()->get('parameter4');
		if(!$form->get_errors() && $this->response->submit()) {
			$image = $this->image;
			$template_arr = explode(':', $template);
			$response->msg = sprintf($this->lang['msg'], $template_arr[2], $image->name);
			$response->image_id = $id;
			$response->install_server = $install_server;
			$response->name = $image->name;
			$response->local_deployment_templates_select = 'jo';

			// plugable addtional parameters
			if (strlen($parameter1)) {
				$template = $template.":".$parameter1;
			}
			if (strlen($parameter2)) {
				$template = $template.":".$parameter2;
			}
			if (strlen($parameter3)) {
				$template = $template.":".$parameter3;
			}
			if (strlen($parameter4)) {
				$template = $template.":".$parameter4;
			}
			// add mode at the beginning
			$local_deployment_template = $persistent.":".$template;
			$image->set_deployment_parameters("INSTALL_CONFIG", $local_deployment_template);

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
		$form = $response->get_form($this->actions_name, 'install2');
		$id = $this->response->html->request()->get('image_id');
		$install_server = $this->response->html->request()->get('install_server');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$image  = new image();
		$image->get_instance_by_id($id);

		$storage = new storage();
		$storage->get_instance_by_id($install_server);

		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->type);

		// require template-deployment file
		$local_deployment_templates_identifier_hook = $this->openqrm->get('basedir')."/web/boot-service/template.".$deployment->type.".php";
		if($this->file->exists($local_deployment_templates_identifier_hook)) {
			require_once "$local_deployment_templates_identifier_hook";
			$get_deployment_templates_function="get_"."$deployment->type"."_templates";
			$get_deployment_templates_function=str_replace("-", "_", $get_deployment_templates_function);
			$local_deployment_templates_arr = $get_deployment_templates_function($install_server);

			// get additional optional local-deployment parameters from the template hook
			$get_additional_parameters_function="get_"."$deployment->type"."_additional_parameters";
			$get_additional_parameters_function=str_replace("-", "_", $get_additional_parameters_function);
			$additional_local_deployment_parameter = $get_additional_parameters_function();
		}

		// persistent deployment ?
		$local_deployment_persistent_arr = array();
		$local_deployment_persistent_arr[] = array("value" => "0", "label" => "First boot");
		$local_deployment_persistent_arr[] = array("value" => "1", "label" => "Persistent");


		$d['template']['label']                          = $this->lang['form_install_template'];
		$d['template']['required']                       = false;
		$d['template']['object']['type']                 = 'htmlobject_select';
		$d['template']['object']['attrib']['index']      = array('value', 'label');
		$d['template']['object']['attrib']['id']         = 'template';
		$d['template']['object']['attrib']['name']       = 'template';
		$d['template']['object']['attrib']['options']    = $local_deployment_templates_arr;

		$d['persistent']['label']                          = $this->lang['form_install_persistent'];
		$d['persistent']['required']                       = false;
		$d['persistent']['object']['type']                 = 'htmlobject_select';
		$d['persistent']['object']['attrib']['index']      = array('value', 'label');
		$d['persistent']['object']['attrib']['id']         = 'persistent';
		$d['persistent']['object']['attrib']['name']       = 'persistent';
		$d['persistent']['object']['attrib']['options']    = $local_deployment_persistent_arr;

		$n = 1;
		foreach ($additional_local_deployment_parameter as $paramters) {
			if (!isset($paramters['label'])) {
				continue;
			}
			$d['parameter'.$n]['label']                          = $paramters['label'];
			$d['parameter'.$n]['required']                       = false;
			$d['parameter'.$n]['object']['type']                 = 'htmlobject_input';
			$d['parameter'.$n]['object']['attrib']['id']         = 'parameter'.$n;
			$d['parameter'.$n]['object']['attrib']['name']       = 'parameter'.$n;
			$n++;
		}
		for ($j=$n; $j<=4; $j++) {
			$d['parameter'.$j] = '';
		}

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>

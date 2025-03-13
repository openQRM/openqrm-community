<?php
/**
 * Image install-from-template step1
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2014, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2014, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class image_install1
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
		$this->response->add('install_from_template', $this->response->html->request()->get('install_from_template'));

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



		$response = $this->install1();
		if(isset($response->install_server)) {

			// redirect to install-from-template step2
//			$this->response->redirect(
//				$this->response->get_url($this->actions_name, 'install2', $this->message_param, $response->msg)
//			);

			$this->response->redirect(
				$this->response->html->thisfile.'?base=image&image_action=install2&image_id='.$response->image_id.'&install_server='.$response->install_server
			);


		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}

		$t = $this->response->html->template($this->tpldir.'/image-install1.tpl.php');
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
	function install1() {
		$response  = $this->get_response();
		$form      = $response->form;
		$install_server			= $this->response->html->request()->get('install_server');
		$id						= $this->response->html->request()->get('image_id');
		if($install_server !== '') {
			if(!$form->get_errors() && $this->response->submit()) {

				$image = $this->image;
				$response->msg = sprintf($this->lang['msg'], $image->name);
				$response->image_id = $id;
				$response->install_server = $install_server;

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
		$form = $response->get_form($this->actions_name, 'install1');
		$id = $this->response->html->request()->get('image_id');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$image  = new image();
		$image->get_instance_by_id($id);
		$storage = new storage();
		$deployment = new deployment();

		$install_template_type = $this->response->html->request()->get('install_from_template');
		if (strlen($install_template_type)) {
			$deployment->get_instance_by_type($install_template_type);
			$storage_select_arr = $storage->get_ids_by_storage_type($deployment->id);
		} else {
			$storage_select_arr = array();
		}
		$storage_select = array();
		foreach($storage_select_arr as $storage_db) {
			$storage_id = $storage_db['storage_id'];
			$storage->get_instance_by_id($storage_id);
			$storage_select[] = array("value" => $storage_id, "label" => $storage_id." - ".$storage->name);
		}

		$d['install_server']['label']                          = $this->lang['form_install_server'];
		$d['install_server']['required']                       = true;
		$d['install_server']['object']['type']                 = 'htmlobject_select';
		$d['install_server']['object']['attrib']['index']      = array('value', 'label');
		$d['install_server']['object']['attrib']['id']         = 'install_server';
		$d['install_server']['object']['attrib']['name']       = 'install_server';
		$d['install_server']['object']['attrib']['options']    = $storage_select;


		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>

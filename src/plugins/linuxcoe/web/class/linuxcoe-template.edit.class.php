<?php
/**
 * Edit Storage
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */


class linuxcoe_template_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'linuxcoe_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'linuxcoe_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'linuxcoe_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'linuxcoe_identifier';
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
var $lang;

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
		$this->openqrm = $openqrm;
		$this->file = $this->openqrm->file();
		$this->template_name = $response->html->request()->get('linuxcoe_template');
		$this->response->add('linuxcoe_template', $this->template_name);
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
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
	
		$t = $this->response->html->template($this->tpldir."/linuxcoe-template-edit.tpl.php");
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add(sprintf($this->lang['label'], $this->template_name), 'label');
		$t->add($response->form);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
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

		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors() && $response->submit()) {
			$template_comment		= $this->response->html->request()->get('template_comment');
			$template_post			= $this->response->html->request()->get('template_post');
			file_put_contents($this->webdir."/plugins/linuxcoe/profiles/".$this->template_name."/openqrm.info", $template_comment);
			if (strlen($template_post)) {
				file_put_contents($this->webdir."/plugins/linuxcoe/profiles/".$this->template_name."/custom-post.info", $template_post."\n");
			}
			$response->msg = sprintf($this->lang['msg_edit'], $this->template_name);
		}
		return $response;

	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'edit');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
		
		$lcoe_profile_comment_str = '';
		if (file_exists($this->webdir."/plugins/linuxcoe/profiles/".$this->template_name."/openqrm.info")) {
			$lcoe_profile_comment_str = trim(file_get_contents($this->webdir."/plugins/linuxcoe/profiles/".$this->template_name."/openqrm.info"));
		}
		
		$lcoe_profile_post_str = '';
		if (file_exists($this->webdir."/plugins/linuxcoe/profiles/".$this->template_name."/custom-post.info")) {
			$lcoe_profile_post_str = trim(file_get_contents($this->webdir."/plugins/linuxcoe/profiles/".$this->template_name."/custom-post.info"));
		}
		
		$d['template_comment']['label']                     = $this->lang['form_comment'];
		$d['template_comment']['validate']['regex']         = '/^[a-z0-9._ -]+$/i';
		$d['template_comment']['validate']['errormsg']      = sprintf($this->lang['error_comment'], 'a-z0-9._ -');
		$d['template_comment']['object']['type']            = 'htmlobject_textarea';
		$d['template_comment']['object']['attrib']['name']  = 'template_comment';
		$d['template_comment']['object']['attrib']['value'] = $lcoe_profile_comment_str;

		$d['template_post']['label']                     = $this->lang['form_post'];
		$d['template_post']['validate']['regex']         = '';
		$d['template_post']['object']['type']            = 'htmlobject_textarea';
		$d['template_post']['object']['attrib']['name']  = 'template_post';
		$d['template_post']['object']['attrib']['value'] = $lcoe_profile_post_str;
		
		$form->add($d);
		$response->form = $form;
		return $response;
	}


}

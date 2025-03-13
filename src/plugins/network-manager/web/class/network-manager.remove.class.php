<?php
/**
 * Network Manager Remove Bridge(s)
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class network_manager_remove
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'network_manager_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "network_manager_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'network_manager_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'network_manager_identifier';
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
		$this->openqrm  = $openqrm;
		$this->file     = $openqrm->file();
		$this->user = $openqrm->user();

		$id = $this->response->html->request()->get('appliance_id');
		$this->response->add('appliance_id', $id);
		$this->response->add($this->identifier_name.'[]', '');

		$appliance = new appliance();
		$this->appliance = $appliance->get_instance_by_id($id);

		$resource = new resource();
		$this->resource = $resource->get_instance_by_id($this->appliance->resources);
		
		$this->statfile = $this->openqrm->get('basedir').'/plugins/network-manager/web/storage/'.$this->resource->id.'.network_stat';
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
		$response = $this->remove();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/network-manager-remove.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
		$t->add($response->form);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Remove
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function remove() {
		$response = $this->get_response();
		$bridges  = $response->html->request()->get($this->identifier_name);
		$form     = $response->form;
		if( $bridges !== '' ) {

			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			foreach($bridges as $ex) {
				$d['param_f'.$i]['label']                       = $ex;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $ex;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;		
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$errors  = array();
				$message = array();
				foreach($bridges as $key => $bridge) {
					$command  = $this->openqrm->get('basedir').'/plugins/network-manager/bin/openqrm-network-manager remove_br';
					$command .= ' -b '.$bridge;
					$command .= ' -u '.$this->openqrm->admin()->name;
					$command .= ' -p '.$this->openqrm->admin()->password;
					$command .= ' --openqrm-ui-user '.$this->user->name;
					$command .= ' --openqrm-cmd-mode regular';

#echo $command.'<br>';

					$file = $this->statfile;
					if($this->file->exists($file)) {
						$this->file->remove($file);
					}
					$this->resource->send_command($this->resource->ip, $command);
					while (!$this->file->exists($file)) {
						usleep(10000); // sleep 10ms to unload the CPU
						clearstatcache();
					}
					$result = trim($this->file->get_contents($file));
					if($result === 'ok') {
						$form->remove($this->identifier_name.'['.$key.']');
						$message[] = sprintf($this->lang['msg_removed'], $bridge);
					}
					else {
						$errors[] = $result;
					}
				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
				}
			}
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
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'remove');
		$response->form = $form;
		return $response;
	}

}

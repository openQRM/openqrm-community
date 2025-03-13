<?php
/**
 * kvm api
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class kvm_api
{
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $controller
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller = $controller;
		$this->user       = $this->controller->user;
		$this->html       = $this->controller->response->html;
		$this->response   = $this->html->response();
		$this->file       = $this->controller->file;
		#$this->admin      = $this->controller->openqrm->admin();

		#$id = $this->response->html->request()->get('appliance_id');
		#if($id === '') {
		#	return false;
		#}
		#// set ENV
		#$this->response->params['appliance_id'] = $id;
		#$appliance = new appliance();
		#$resource  = new resource();

		#$appliance->get_instance_by_id($id);
		#$resource->get_instance_by_id($appliance->resources);

		#$this->resource  = $resource;
		#$this->appliance = $appliance;
		#$this->statfile  = 'kvm-stat/'.$resource->id.'.pick_iso_config';
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {
		$action = $this->html->request()->get($this->controller->actions_name);
		switch( $action ) {
			case 'progress':
				$this->progress();
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Get progress
	 *
	 * @access public
	 */
	//--------------------------------------------
	function progress() {
		$name = basename($this->response->html->request()->get('name'));
		$file = $this->controller->openqrm->get('basedir').'/plugins/kvm/web/storage/'.$name;
		if($this->file->exists($file)) {
			echo $this->file->get_contents($file);
		} else {
			header("HTTP/1.0 404 Not Found");
		}
	}

}

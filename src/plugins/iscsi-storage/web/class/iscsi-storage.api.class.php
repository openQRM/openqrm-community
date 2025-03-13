<?php
/**
 * iSCSI-Storage Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class iscsi_storage_api
{
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $iscsi_storage_controller
	 */
	//--------------------------------------------
	function __construct($iscsi_storage_controller) {
		$this->controller = $iscsi_storage_controller;
		$this->user       = $this->controller->user;
		$this->html       = $this->controller->response->html;
		$this->response   = $this->html->response();
		$this->file       = $this->controller->file;
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
			case 'monitor':
				$this->monitor();
			break;
			case 'progress':
				$this->progress();
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Monitor
	 *
	 * @access public
	 */
	//--------------------------------------------
	function monitor() {
		$filename     = '/etc/ietd.conf';
		$lastmodif    = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;
		$currentmodif = filemtime($filename);
		while ($currentmodif <= $lastmodif) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		  $currentmodif = filemtime($filename);
		}
		echo 'changed';

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
		$file = $this->controller->openqrm->get('basedir').'/plugins/iscsi-storage/web/storage/'.$name;
		if($this->file->exists($file)) {
			echo $this->file->get_contents($file);
		} else {
			header("HTTP/1.0 404 Not Found");
		}
	}





}

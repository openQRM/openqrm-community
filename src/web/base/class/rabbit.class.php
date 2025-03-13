<?php

// This class represents a resource in openQRM (physical hardware or virtual machine)
/*
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
*/

require_once  $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/php-amqplib/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/user.class.php";
$event = new event();
global $event;
global $OPENQRM_SERVER_BASE_DIR;


class rabbit {

var $ip = '';
var $cmd = '';
var $read_only_commands = array("post_vm_list", "post_vg post_lv", "post_vm_config", "post_bridge_config", "post_identifier");


	//--------------------------------------------
	/**
	 * Constructor
	 */
	//--------------------------------------------
	function __construct() {
		$OPENQRM_ADMIN = new user('openqrm');
		$OPENQRM_ADMIN->set_user();
		$connection = new AMQPConnection('localhost', 5672, 'openqrm', $OPENQRM_ADMIN->password);
		$channel = $connection->channel();
		$this->connection = $connection;
		$this->channel = $channel;
		global $event;
		$this->event = new event();
		global $OPENQRM_SERVER_BASE_DIR;
		$this->openqrm_server_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}


	//--------------------------------------------
	/**
	 * Add to RabbitMQ queue
	 *
	 * @param string $queue
	 * @param string $cmd
	 * @access public
	 */
	//--------------------------------------------
	function queue($queue, $cmd) {
		$cmd_arr = explode(' ', $cmd);
		if (isset($cmd_arr[1])) {
			if (in_array($cmd_arr[1], $this->read_only_commands)) {
				$queue = $queue.'.ui';
			}
		}
		$this->event->log("send_command", $_SERVER['REQUEST_TIME'], 5, "rabbit.class.php", "Adding command to RabbitMQ queue ".$queue, "", "", 0, 0, 0);
		$this->channel->queue_declare($queue, false, false, false, false);
		$msg = new AMQPMessage($this->openqrm_server_base_dir."/openqrm/bin/openqrm-cmd ".$cmd, array('delivery_mode' => 2));
		$this->channel->basic_publish($msg, '', $queue);
		$this->channel->close();
		$this->connection->close();
	}



}

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


// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/htmlobjects/htmlobject.class.php";
global $OPENQRM_SERVER_BASE_DIR;
$event = new event();
global $event;



function get_collectd_appliance_link($appliance_id) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;

	$appliance_name='';

	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);
	$resource = new resource();
	$resource->get_instance_by_id($appliance->resources);
	$hostname = $appliance->name;
	if ($resource->id == 0) {
		$hostname = 'openqrm';
	}

	$a = '';
	if(file_exists('/usr/share/openqrm/plugins/collectd/data/'.$hostname)) {
		$html = new htmlobject($OPENQRM_SERVER_BASE_DIR.'/openqrm/web/base/class/htmlobjects');
		$a = $html->a();
		$a->label = 'collectd';
		$a->css = 'badge';
		$a->handler = 'onclick="wait();"';
		$a->href = '/openqrm/base/index.php?plugin=collectd&controller=collectd&collectd_action=statistics&appliance_id='.$appliance->id;
	}
	return $a;
}

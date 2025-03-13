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
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;

function get_novnc_appliance_link($appliance_id) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;

	$p_appliance = new appliance();
	$p_appliance->get_instance_by_id($appliance_id);
	$p_resource = new resource();
	$p_resource->get_instance_by_id($p_appliance->resources);
	$virtualization = new virtualization();
	$virtualization->get_instance_by_id($p_appliance->virtualization);

	$html = new htmlobject($OPENQRM_SERVER_BASE_DIR.'/openqrm/web/base/class/htmlobjects');
	$a = $html->a();
	$a->label = 'noVNC';
	$a->css = 'badge';
	$a->handler = 'onclick="wait();"';

	$plugin_link = '';
	if (strstr($p_appliance->state, "active")) {
		if(strstr($virtualization->type, '-vm-')) {
			$a->href = '/openqrm/base/index.php?plugin=novnc&controller=novnc&novnc_action=console&appliance_id='.$p_appliance->id;
		} else {
			$a->href = '/openqrm/base/index.php?plugin=novnc&controller=novnc&novnc_action=login&appliance_id='.$p_appliance->id;
		}
		$plugin_link = $a;
	}
	else if ($p_resource->id === '0') {
		$a->href = '/openqrm/base/index.php?plugin=novnc&controller=novnc&novnc_action=login&appliance_id='.$p_appliance->id;
		$plugin_link = $a;
	}
	return $plugin_link;
}

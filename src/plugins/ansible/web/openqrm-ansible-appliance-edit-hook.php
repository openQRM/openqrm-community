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

function get_ansible_appliance_edit($appliance_id, $openqrm, $response) {
	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);
	$plugin_title = "Configure Application on Appliance ".$appliance->name;

	$a = $response->html->a();
	$a->label = '<image height="24" width="24" alt="'.$plugin_title.'" title="'.$plugin_title.'" src="'.$openqrm->get('baseurl').'/plugins/ansible/img/plugin.png">';
	$a->href = $openqrm->get('baseurl').'/index.php?base=appliance&appliance_action=load_edit&aplugin=ansible&appliance_id='.$appliance_id.'&ansible_action=edit';
	$a->handler = '';

	return $a;
}


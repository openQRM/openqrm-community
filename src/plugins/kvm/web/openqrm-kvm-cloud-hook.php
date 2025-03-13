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


// This file implements the virtual machine abstraction in the cloud of openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

// for ovs we need to get the request for the ips
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";


$event = new event();
global $event;

global $OPENQRM_SERVER_BASE_DIR;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $RESOURCE_INFO_TABLE;

// ---------------------------------------------------------------------------------
// general kvm cloudvm methods
// ---------------------------------------------------------------------------------


// creates a vm 
function create_kvm_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $vm_type, $vncpassword, $source_image_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	global $RootDir;
	$event->log("create_kvm_vm_local", $_SERVER['REQUEST_TIME'], 5, "kvm-cloud-hook", "Creating KVM VM $name on Host resource $host_resource_id", "", "", 0, 0, 0);
	// start the vm on the host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	// we need to have an openQRM server object too since some of the
	// virtualization commands are sent from openQRM directly
	$openqrm = new openqrm_server();
	$openqrm_admin_user = new user("openqrm");
	$openqrm_admin_user->set_user();
	
	$vncpassword_parameter = "";
	if ($vncpassword != '') {
		$vncpassword_parameter = " -v ".$vncpassword;
	}
	
	
	// fill mac array from additional_network_str
	$vm_ovs_additonal_nic_str = '';
	$vm_resource_mac_array[] = $mac;
	$vm_resource_mac_tmp_array = explode(' ', trim($additional_nic_str));
	foreach($vm_resource_mac_tmp_array as $mac_param) {
	    if(strpos($mac_param, '-m') === false) {
		$vm_resource_mac_array[] = $mac_param;
	    }

	}

	// if ovs enabled remove last mac address from addtional_nic_str
	// and pop from array
	// for ovs we send the bridges from ip_mgmt
	$plugin = new plugin();
	if (in_array("openvswitch-manager", $plugin->enabled())) {

	    array_pop($vm_resource_mac_array);
	    // rebuild add_nic_str
	    $vm_ovs_additonal_nic_array = $vm_resource_mac_array;
	    array_shift($vm_ovs_additonal_nic_array);
	    $add_nic_loop = 1;
	    foreach ($vm_ovs_additonal_nic_array as $vm_ovs_additonal_nic_mac) {
		$vm_ovs_additonal_nic_str .= " -m".$add_nic_loop." ".$vm_ovs_additonal_nic_mac." ";
		$add_nic_loop++;
	    }

	    $additional_nic_str = $vm_ovs_additonal_nic_str;

	    // if ovs enabled  - get cr and add ip-mgmt ips to dhcpd
	    $vm_resource = new resource();
	    $vm_resource->get_instance_by_mac($mac);

	    // the resource name is set as the appliance_name in the cr we are looking for
	    $crl = new cloudrequest();
	    $cr_list = $crl->get_all_new_and_approved_ids();
	    $vm_cr_id = 0;
	    foreach($cr_list as $list) {
		    $cr_id = $list['cr_id'];
		    $cr = new cloudrequest();
		    $cr->get_instance_by_id($cr_id);
		    if ($cr->appliance_hostname == $name) {
			    $vm_cr_id = $cr->id;
			    $event->log("create_kvm_vm_local", $_SERVER['REQUEST_TIME'], 5, "kvm-cloud-hook", "OVS: Found cloud request id $vm_cr_id", "", "", 0, 0, 0);
		    }
	    }
	    if ($vm_cr_id == 0) {
		    $event->log("create_kvm_vm_local", $_SERVER['REQUEST_TIME'], 2, "kvm-cloud-hook", "OVS: Could not find out cloud request id!", "", "", 0, 0, 0);
		    return;
	    }

	    // here we have found the cr for this vm
	    $cr = new cloudrequest();
	    $cr->get_instance_by_id($vm_cr_id);

	    $cu = new clouduser();
	    $cu->get_instance_by_id($cr->cu_id);

	    $ovs_network_parameter = '';

	    // check ip-mgmt
	    $cc_conf = new cloudconfig();
	    $show_ip_mgmt = $cc_conf->get_value(26);	// ip-mgmt enabled ?
	    if (!strcmp($show_ip_mgmt, "true")) {
		    if (file_exists("$RootDir/plugins/ip-mgmt/.running")) {
			    require_once "$RootDir/plugins/ip-mgmt/class/ip-mgmt.class.php";
			    $ip_mgmt_array = explode(",", $cr->ip_mgmt);
			    $ip_mgmt_assign_loop = 0;
			    foreach($ip_mgmt_array as $ip_mgmt_config_str) {

				    $collon_pos = strpos($ip_mgmt_config_str, ":");
				    $nic_id = substr($ip_mgmt_config_str, 0, $collon_pos);
				    $ip_mgmt_id = substr($ip_mgmt_config_str, $collon_pos+1);
				    if (!strlen($ip_mgmt_id)) {
					    continue;
				    }
				    $orginal_ip_mgmt_id = $ip_mgmt_id;
				    $ip_mgmt_assign = new ip_mgmt();
				    $ip_mgmt_id_final = $ip_mgmt_id;
				    // we need to check if the ip is still free
				    $ip_mgmt_object_arr = $ip_mgmt_assign->get_instance('id', $ip_mgmt_id);
				    $ip_app_id = $ip_mgmt_object_arr['ip_mgmt_appliance_id'];
				    if ($ip_app_id > 0) {
					    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "WARNING: ip-mgmt id ".$ip_mgmt_id." is already in use. Trying to find the next free ip..", "", "", 0, 0, 0);
					    $ip_mgmt_id = -2;
				    } else {
					    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "SUCCESS: ip-mgmt id ".$ip_mgmt_id." is free.", "", "", 0, 0, 0);
				    }

				    // if ip_mgmt_id == auto (-2) search the next free ip for the user
				    if ($ip_mgmt_id == -2) {
					    $ip_mgmt_list_per_user = $ip_mgmt_assign->get_list_by_user($cu->cg_id);
					    $next_free_ip_mgmt_id = 0;
					    foreach($ip_mgmt_list_per_user as $list) {
						    $possible_next_ip_mgmt_id = $list['ip_mgmt_id'];
						    $possible_next_ip_mgmt_object_arr = $ip_mgmt_assign->get_instance('id', $possible_next_ip_mgmt_id);
						    if ($possible_next_ip_mgmt_object_arr['ip_mgmt_appliance_id'] == NULL) {
							    // we have found the next free ip-mgmt id
							    $next_free_ip_mgmt_id = $possible_next_ip_mgmt_id;
							    $ip_mgmt_id_final = $possible_next_ip_mgmt_id;
							    break;
						    }
					    }
					    if ($next_free_ip_mgmt_id == 0) {
						    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "WARNING: Could not find the next free ip-mgmt id for appliance ".$appliance_id.".", "", "", 0, 0, 0);
						    continue;
					    } else {
						    $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "cloud-monitor", "SUCCESS: Found the next free ip-mgmt id ".$next_free_ip_mgmt_id." for appliance ".$appliance_id.".", "", "", 0, 0, 0);
						    $ip_mgmt_id = $next_free_ip_mgmt_id;
						    // here we have to update the cr with the new ip-mgmt-id
						    $new_cr_ip_mgmt_str = str_replace($nic_id.":".$orginal_ip_mgmt_id, $nic_id.":".$ip_mgmt_id, $cr->ip_mgmt);
						    $new_cr_ip_mgmt_fields=array();
						    $new_cr_ip_mgmt_fields["cr_ip_mgmt"]=$new_cr_ip_mgmt_str;
						    $cr->update($cr->id, $new_cr_ip_mgmt_fields);
						    $cr->get_instance_by_id($cr->id);
					    }
				    }

				    // here we have a valid ip-mgmt opbject to update



				    $ip_mgmt_fields=array();

				    // get full network config
				    $ovs_ip_array = $ip_mgmt_assign->get_instance('id', $ip_mgmt_id);
				    $ovs_ip_mgmt_name = trim($ovs_ip_array['ip_mgmt_name']);
				    $ovs_ip_mgmt_address = trim($ovs_ip_array['ip_mgmt_address']);
				    $ovs_ip_mgmt_network = trim($ovs_ip_array['ip_mgmt_network']);
				    $ovs_ip_mgmt_subnet = trim($ovs_ip_array['ip_mgmt_subnet']);
				    $ovs_ip_mgmt_broadcast = trim($ovs_ip_array['ip_mgmt_broadcast']);
				    $ovs_ip_mgmt_dns1 = trim($ovs_ip_array['ip_mgmt_dns1']);
				    $ovs_ip_mgmt_dns2 = trim($ovs_ip_array['ip_mgmt_dns2']);
				    $ovs_ip_mgmt_domain = trim($ovs_ip_array['ip_mgmt_domain']);
				    $ovs_ip_mgmt_vlan_id = trim($ovs_ip_array['ip_mgmt_vlan_id']);
				    $ovs_ip_mgmt_bridge_name = trim($ovs_ip_array['ip_mgmt_bridge_name']);
				    $ovs_ip_mgmt_comment = trim($ovs_ip_array['ip_mgmt_comment']);

				    // update ip-mgmt object with resource_id
				    $ip_mgmt_fields["ip_mgmt_resource_id"]=$vm_resource->id;
				    $ip_mgmt_fields["ip_mgmt_nic_id"]=$nic_id;
				    $ip_mgmt_assign->update_ip($ip_mgmt_id, $ip_mgmt_fields);

				    // set resource_external_ip
				    if ($ip_mgmt_assign_loop == 0) {
					    $ip_mgmt_assign_arr = $ip_mgmt_assign->get_config_by_id($ip_mgmt_id_final);
					    $resource_external_ip = $ip_mgmt_assign_arr[0]['ip_mgmt_address'];

					    $command  = $OPENQRM_SERVER_BASE_DIR.'/openqrm/plugins/dhcpd/bin/openqrm-dhcpd-manager remove_by_mac';
					    $command .= ' -m '.$vm_resource_mac_array[$ip_mgmt_assign_loop];

					    $openqrm->send_command($command);
					    sleep(1);
					    // update vm resource objects main ip
					    $resource_fields['resource_ip'] = $ovs_ip_mgmt_address;
					    $resource_fields['resource_subnet'] = $ovs_ip_mgmt_subnet;
					    $resource_fields['resource_network'] = $ovs_ip_mgmt_network;
					    $resource_fields['resource_broadcast'] = $ovs_ip_mgmt_broadcast;
					    $vm_resource->update_info($vm_resource->id, $resource_fields);

					    // build bridge param - first nic
					    $ovs_network_parameter .= " -z ".$ovs_ip_mgmt_bridge_name;

				    } else {
					    // build bridge param - additional nics
					    $ovs_network_parameter .= " -z".$ip_mgmt_assign_loop." ".$ovs_ip_mgmt_bridge_name;


				    }
				    // assign to dhcpd
				    $command  = $OPENQRM_SERVER_BASE_DIR.'/openqrm/plugins/dhcpd/bin/openqrm-dhcpd-manager add';
				    $command .= ' -d '.$vm_resource->id.$ip_mgmt_assign_loop;
				    $command .= ' -i '.$ovs_ip_mgmt_address;
				    $command .= ' -s '.$ovs_ip_mgmt_subnet;
				    $command .= ' -m '.$vm_resource_mac_array[$ip_mgmt_assign_loop];
				    $openqrm->send_command($command);

				    // assign to dns
				    $command  = $OPENQRM_SERVER_BASE_DIR.'/openqrm/plugins/dns/bin/openqrm-dns-domain-manager add_host';
				    $command .= ' -n '.$ovs_ip_mgmt_domain;
				    $command .= ' -i '.$ovs_ip_mgmt_address;
				    $command .= ' -q '.$name;
				    $openqrm->send_command($command);

				    sleep(1);
				    $ip_mgmt_assign_loop++;
			    }
		    }
	    }

	}
	// send command to create vm / regular bridges
	$vm_create_cmd = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/kvm/bin/openqrm-kvm-vm create";
	$vm_create_cmd .= " -n ".$name;
	$vm_create_cmd .= " -y ".$vm_type;
	$vm_create_cmd .= " -m ".$mac;
	$vm_create_cmd .= " -r ".$memory;
	$vm_create_cmd .= " -c ".$cpu;
	$vm_create_cmd .= " -b local";
	$vm_create_cmd .= " ".$additional_nic_str;
	$vm_create_cmd .= " ".$vncpassword_parameter;
	$vm_create_cmd .= " -u ".$openqrm_admin_user->name." -p ".$openqrm_admin_user->password;
	// for ovs we send the bridges from ip_mgmt
	if (in_array("openvswitch-manager", $plugin->enabled())) {
		$vm_create_cmd .= " ".$ovs_network_parameter;
	}

	$event->log("create_kvm_vm_local", $_SERVER['REQUEST_TIME'], 5, "kvm-cloud-hook", "Running $vm_create_cmd", "", "", 0, 0, 0);
	$host_resource->send_command($host_resource->ip, $vm_create_cmd);


	
	
	
}



// removes a vm
function remove_kvm_vm($host_resource_id, $name, $mac) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	global $RootDir;

	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	// we need to have an openQRM server object too since some of the
	// virtualization commands are sent from openQRM directly
	$openqrm = new openqrm_server();
	$openqrm_admin_user = new user("openqrm");
	$openqrm_admin_user->set_user();

	
	// be sure to remove first $mac from dhcpd
	$dhcpd_remove_command = $OPENQRM_SERVER_BASE_DIR.'/openqrm/plugins/dhcpd/bin/openqrm-dhcpd-manager remove_by_mac -m '.$mac;
	$dhcpd_remove_command .= ' --openqrm-cmd-mode background';
	$openqrm->send_command($dhcpd_remove_command);
	sleep(1);

	$plugin = new plugin();
	if (in_array("openvswitch-manager", $plugin->enabled())) {
	
	    // OVS: check to remove all mac addresses from dhcpd
	    $command  = $OPENQRM_SERVER_BASE_DIR.'/openqrm/plugins/kvm/bin/openqrm-kvm-vm post_vm_list';
	    $command .= ' -u '.$openqrm_admin_user->name.' -p '.$openqrm_admin_user->password;
	    $command .= ' --openqrm-ui-user '.$openqrm_admin_user->name;
	    $command .= ' --openqrm-cmd-mode background';

	    $file = $OPENQRM_SERVER_BASE_DIR.'/openqrm/plugins/kvm/web/kvm-stat/'.$host_resource->id.'.vm_list';
	    if(file_exists($file)) {
		    unlink($file);
	    }
	    $host_resource->send_command($host_resource->ip, $command);
	    while (!file_exists($file)) // check if the data file has been modified
	    {
		    usleep(10000); // sleep 10ms to unload the CPU
		    clearstatcache();
	    }
	    $lines = explode("\n", file_get_contents($file));
	    if(count($lines) >= 1) {
		    foreach($lines as $line) {
			    if($line !== '') {
				    $line = explode('@', $line);
				    if($name === $line[1]) {
					    // remove all nics by mac from dhcpd
					    if($line[7] !== '') {
						$mac_str = $line[7];
						$all_mac_array = explode(',', $line[7]);
						    foreach ($all_mac_array as $addional_mac) {
							    if ($addional_mac == '') {
								continue;
							    }
							    $dhcpd_remove_command = $OPENQRM_SERVER_BASE_DIR.'/openqrm/plugins/dhcpd/bin/openqrm-dhcpd-manager remove_by_mac -m '.$addional_mac;
							    $dhcpd_remove_command .= ' --openqrm-cmd-mode background';
							    $event->log("console", $_SERVER['REQUEST_TIME'], 5, "kvm-cloud-hook.php", 'Removing '.$addional_mac.' from dhcpd server.', "", "", 0, 0, 0);
							    $openqrm->send_command($dhcpd_remove_command);
							    sleep(1);
						    }
					    }

				    }
			    }
		    }
	    }


	    // check ip-mgmt
	    $cc_conf = new cloudconfig();
	    $show_ip_mgmt = $cc_conf->get_value(26);	// ip-mgmt enabled ?
	    if (!strcmp($show_ip_mgmt, "true")) {
		    if (file_exists("$RootDir/plugins/ip-mgmt/.running")) {
			    require_once "$RootDir/plugins/ip-mgmt/class/ip-mgmt.class.php";

			    // remove all ips from dns
			    $ip_mgmt_deassign = new ip_mgmt();
			    $vm_resource = new resource();
			    $vm_resource->get_instance_by_mac($mac);
			    $kvm_ip_id_array = $ip_mgmt_deassign->get_ids_per_resource($vm_resource->id);
			    foreach($kvm_ip_id_array as $kvm_ip_id) {

				    $ovs_ip_array = $ip_mgmt_deassign->get_instance('id', $kvm_ip_id);
				    $ovs_ip_mgmt_address = trim($ovs_ip_array['ip_mgmt_address']);
				    $ovs_ip_mgmt_domain = trim($ovs_ip_array['ip_mgmt_domain']);

				    $dns_command  = $OPENQRM_SERVER_BASE_DIR.'/openqrm/plugins/dns/bin/openqrm-dns-domain-manager remove_host';
				    $dns_command .= ' -n '.$ovs_ip_mgmt_domain;
				    $dns_command .= ' -i '.$ovs_ip_mgmt_address;
				    $dns_command .= ' -q '.$vm_resource->vname;
				    $openqrm->send_command($dns_command);

				    $ovs_update_array['ip_mgmt_resource_id'] = NULL;
				    $ip_mgmt_deassign->update_ip($kvm_ip_id, $ovs_update_array);

			    }
		    }
	    }
	    sleep(1);
	}	
	
	// remove the vm from host
	$event->log("remove_kvm_vm_local", $_SERVER['REQUEST_TIME'], 5, "kvm-cloud-hook", "Removing KVM VM $name/$mac from Host resource $host_resource_id", "", "", 0, 0, 0);
	// we need to have an openQRM server object too since some of the
	// virtualization commands are sent from openQRM directly
	$openqrm = new openqrm_server();
	// send command to create the vm on the host
	$vm_remove_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm-vm delete -n ".$name." --openqrm-cmd-mode background";
	$event->log("remove_kvm_vm_local", $_SERVER['REQUEST_TIME'], 5, "kvm-cloud-hook", "Running $vm_remove_cmd", "", "", 0, 0, 0);
	$host_resource->send_command($host_resource->ip, $vm_remove_cmd);
}


// Cloud hook methods

function create_kvm_vm_local($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $vncpassword, $source_image_id, $size, $cu_id) {
	global $event;
	create_kvm_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, "kvm-vm-local", $vncpassword, $source_image_id);
}

function create_kvm_vm_net($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $vncpassword, $source_image_id, $size, $cu_id) {
	global $event;
	create_kvm_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, "kvm-vm-net", $vncpassword, $source_image_id);
}

function remove_kvm_vm_local($host_resource_id, $name, $mac, $cu_id) {
	global $event;
	remove_kvm_vm($host_resource_id, $name, $mac);
}

function remove_kvm_vm_net($host_resource_id, $name, $mac, $cu_id) {
	global $event;
	remove_kvm_vm($host_resource_id, $name, $mac);
}




// ---------------------------------------------------------------------------------

/*
$host_resource_id = 0;
$name = "kvmovscloudtest";
$mac="2c:76:8a:e5:a5:10";
$memory=512;
$cpu=1;
$swap=0;
$additional_nic_str = " -m1 2c:76:8a:e5:a5:11 -m2 2c:76:8a:e5:a5:12 ";
$vm_type = "kvm-vm-local";
$vncpassword = "openqrm";
$source_image_id = "14998967150366";

echo "running create_kvm_vm <br>";

create_kvm_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str, $vm_type, $vncpassword, $source_image_id);
*/



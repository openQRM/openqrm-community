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



$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/authblocker.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
// filter inputs
require_once $RootDir.'/class/htmlobjects/htmlobject.class.php';
require_once $RootDir.'/include/requestfilter.inc.php';
$html = new htmlobject($RootDir.'/class/htmlobjects/');
$request = $html->request();
$request->filter = $requestfilter;

global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;

$cloud_product_hook = $RootDir.'/plugins/kvm/openqrm-kvm-cloud-product-hook.php';
$cloud_selector_class = $RootDir.'/plugins/cloud/class/cloudselector.class.php';
$cloud_usergroup_class = $RootDir.'/plugins/cloud/class/cloudusergroup.class.php';

// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/kvm/storage';
// place for the kvm_server stat files
$KvmDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/kvm/kvm-stat';
// get params
$lvm_command = $request->get('lvm_command');
$kvm_server_command = $request->get('kvm_server_command');
$kvm_server_id = $request->get('kvm_server_id');
if (!strlen($lvm_command)) {
	$lvm_command = $kvm_server_command;
}
$kvm_image_name = $request->get('kvm_image_name');


// global event for logging
$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "lvm-action", "Un-Authorized access to lvm-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}


// $event->log("$lvm_command", $_SERVER['REQUEST_TIME'], 5, "kvm-action", "Processing kvm command $lvm_command", "", "", 0, 0, 0);
switch ($lvm_command) {
	// storage commands
	case 'get':
		if (!file_exists($StorageDir)) {
            if (!mkdir($StorageDir) && !is_dir($StorageDir)) {
                #throw new \RuntimeException(sprintf('Directory "%s" was not created', $StorageDir));
            }
		}
		$filename = $StorageDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	case 'get_ident':
		if (!file_exists($StorageDir)) {
            if (!mkdir($StorageDir) && !is_dir($StorageDir)) {
                #throw new \RuntimeException(sprintf('Directory "%s" was not created', $StorageDir));
            }
		}
		$filename = $StorageDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	case 'clone_finished':
		if (!file_exists($StorageDir)) {
            if (!mkdir($StorageDir) && !is_dir($StorageDir)) {
                #throw new \RuntimeException(sprintf('Directory "%s" was not created', $StorageDir));
            }
		}
		$filename = $StorageDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	case 'get_sync_progress':
		if (!file_exists($StorageDir)) {
            if (!mkdir($StorageDir) && !is_dir($StorageDir)) {
                #throw new \RuntimeException(sprintf('Directory "%s" was not created', $StorageDir));
            }
		}
		$filename = $StorageDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	case 'get_sync_finished':
		if (!file_exists($StorageDir)) {
            if (!mkdir($StorageDir) && !is_dir($StorageDir)) {
                #throw new \RuntimeException(sprintf('Directory "%s" was not created', $StorageDir));
            }
		}
		$filename = $StorageDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		sleep(5);
		unlink($filename);
		break;

	case 'auth_finished':
		// remove storage-auth-blocker if existing
		$authblocker = new authblocker();
		$authblocker->get_instance_by_image_name($kvm_image_name);
		if (strlen($authblocker->id)) {
			$event->log('auth_finished', $_SERVER['REQUEST_TIME'], 5, "kvm-action", "Removing authblocker for image $kvm_image_name", "", "", 0, 0, 0);
			$authblocker->remove($authblocker->id);
		}
		break;


	// vm commands
	// get the incoming vm list
	case 'get_kvm_server':
		if (!file_exists($KvmDir)) {
            if (!mkdir($KvmDir) && !is_dir($KvmDir)) {
                #throw new \RuntimeException(sprintf('Directory "%s" was not created', $KvmDir));
            }
		}
		$filename = $KvmDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	// send command to send the vm list
	case 'refresh_vm_list':
		$kvm_appliance = new appliance();
		$kvm_appliance->get_instance_by_id($kvm_server_id);
		$kvm_server = new resource();
		$kvm_server->get_instance_by_id($kvm_appliance->resources);
		$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm-vm post_vm_list -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
		$kvm_server->send_command($kvm_server->ip, $resource_command);
		break;

	// get the incoming vm config
	case 'get_kvm_config':
		if (!file_exists($KvmDir)) {
            if (!mkdir($KvmDir) && !is_dir($KvmDir)) {
                #throw new \RuntimeException(sprintf('Directory "%s" was not created', $KvmDir));
            }
		}
		$filename = $KvmDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	// send command to send the vm config
	case 'refresh_vm_config':
		$kvm_appliance = new appliance();
		$kvm_appliance->get_instance_by_id($kvm_server_id);
		$kvm_server = new resource();
		$kvm_server->get_instance_by_id($kvm_appliance->resources);
		$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm/bin/openqrm-kvm-vm post_vm_config -n $kvm_server_name -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
		$kvm_server->send_command($kvm_server->ip, $resource_command);
		break;

	// get the incoming bridge config
	case 'get_bridge_config':
		if (!file_exists($KvmDir)) {
            if (!mkdir($KvmDir) && !is_dir($KvmDir)) {
                #throw new \RuntimeException(sprintf('Directory "%s" was not created', $KvmDir));
            }
		}
		$filename = $KvmDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	// get VM migration status
	case 'get_vm_migration':
		if (!file_exists($KvmDir)) {
            if (!mkdir($KvmDir) && !is_dir($KvmDir)) {
                #throw new \RuntimeException(sprintf('Directory "%s" was not created', $KvmDir));
            }
		}
		$filename = $KvmDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	// get pick_iso config
	case 'get_pick_iso_config':
		if (!file_exists($KvmDir)) {
            if (!mkdir($KvmDir) && !is_dir($KvmDir)) {
                #throw new \RuntimeException(sprintf('Directory "%s" was not created', $KvmDir));
            }
		}
		$filename = $KvmDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;
		
	case 'init':
		// add cloud products
		if (file_exists($cloud_usergroup_class)) {
			require_once $cloud_usergroup_class;
			$cloud_project = new cloudusergroup();
			$cloud_project->get_instance_by_name('Admin');
			if (file_exists($cloud_selector_class)) {
				if (file_exists($cloud_product_hook)) {
					$cloud_hook_config = array();
					$cloud_hook_config['cloud_admin_procect'] = $cloud_project->id;
					require_once $cloud_product_hook;
					openqrm_kvm_cloud_product("add", $cloud_hook_config);
				}
			}
		}
		break;
		
	case 'uninstall':
		// remove cloud products
		if (file_exists($cloud_usergroup_class)) {
			require_once $cloud_usergroup_class;
			$cloud_project = new cloudusergroup();
			$cloud_project->get_instance_by_name('Admin');
			if (file_exists($cloud_selector_class)) {
				if (file_exists($cloud_product_hook)) {
					$cloud_hook_config = array();
					$cloud_hook_config['cloud_admin_procect'] = $cloud_project->id;
					require_once $cloud_product_hook;
					openqrm_kvm_cloud_product("remove", $cloud_hook_config);
				}
			}
		}
		break;

	case 'put_vnc':
		$cloud_vnc_dir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/action/cloud-conf/';
		if (!file_exists($cloud_vnc_dir)) {
            if (!mkdir($cloud_vnc_dir) && !is_dir($cloud_vnc_dir)) {
                #throw new \RuntimeException(sprintf('Directory "%s" was not created', $cloud_vnc_dir));
            }
		}
		$filename = $cloud_vnc_dir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	case 'put_stats':
		$cloud_vnc_dir = $KvmDir;
		if (!file_exists($KvmDir)) {
            if (!mkdir($KvmDir) && !is_dir($KvmDir)) {
                #throw new \RuntimeException(sprintf('Directory "%s" was not created', $KvmDir));
            }
		}
		$filename = $KvmDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	case 'set_image_size':
		$kvm_image_name = $request->get('kvm_image_name');
		$kvm_image_size = $request->get('kvm_image_size');
		if($kvm_image_name !== '' && $kvm_image_size !== '') {
			$kvm_image = new image();
			$kvm_image->get_instance_by_name($kvm_image_name);
			if($kvm_image->id !== '') {
				$kvm_image->update($kvm_image->id, array('image_size' => $kvm_image_size));
			}
		}
		break;

	default:
		$event->log("$lvm_command", $_SERVER['REQUEST_TIME'], 3, "kvm-action", "No such kvm command ($lvm_command)", "", "", 0, 0, 0);
		break;


}


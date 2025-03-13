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
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/authblocker.class.php";
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

$iscsi_storage_command = $request->get('iscsi_storage_command');
$iscsi_image_name = $request->get('iscsi_image_name');

// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/iscsi-storage/storage';
// global event for logging
$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "iscsi-action", "Un-Authorized access to iscsi-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}


$event->log("$iscsi_storage_command", $_SERVER['REQUEST_TIME'], 5, "iscsi-storage-action", "Processing iscsi-storage command $iscsi_storage_command", "", "", 0, 0, 0);
switch ($iscsi_storage_command) {
	case 'get_storage':
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
		$authblocker->get_instance_by_image_name($iscsi_image_name);
		if (strlen($authblocker->id)) {
			$event->log('auth_finished', $_SERVER['REQUEST_TIME'], 5, "iscsi-storage-action", "Removing authblocker for image $iscsi_image_name", "", "", 0, 0, 0);
			$authblocker->remove($authblocker->id);
		}
		break;

	default:
		$event->log("$iscsi_storage_command", $_SERVER['REQUEST_TIME'], 3, "iscsi-storage-action", "No such iscsi-storage command ($iscsi_storage_command)", "", "", 0, 0, 0);
		break;
}


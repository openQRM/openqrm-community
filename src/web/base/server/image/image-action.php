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
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/event.class.php";

// filter inputs
require_once $RootDir.'/class/htmlobjects/htmlobject.class.php';
$html = new htmlobject($RootDir.'/class/htmlobjects/');

$request = $html->request();

require_once $RootDir.'/include/requestfilter.inc.php';
$request->filter = $requestfilter;


global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;

$event = new event();

// user/role authentication
if (!strstr($OPENQRM_USER->role, "administrator")) {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "image-action", "Un-Authorized access to image-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$image_command = $request->get('image_command');
$image_id = $request->get('image_id');
$image_name = $request->get('image_name');
$image_type = $request->get('image_type');
$image_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "image_", 6) == 0) {
		$image_fields[$key] = $value;
	}
}
unset($image_fields["image_command"]);

$deployment_id = $request->get('deployment_id');
$deployment_name = $request->get('deployment_name');
$deployment_type = $request->get('deployment_type');
$deployment_description = $request->get('deployment_description');
$deployment_storagetype = $request->get('deployment_storagetype');
$deployment_storagedescription = $request->get('deployment_storagedescription');
$deployment_mapping = $request->get('deployment_mapping');
$deployment_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "deployment_", 11) == 0) {
		$deployment_fields[$key] = $value;
	}
}


// parse the identifier array to get the id
if($request->get('action') != '') {
	switch ($request->get('action')) {
		case 'add':
			foreach($_REQUEST['identifier'] as $id) {
				if(!strlen($image_fields["image_storageid"])) {
					$image_fields["image_storageid"]=$id;
				}
				continue;
			}
			break;
		case 'update':
			foreach($_REQUEST['identifier'] as $id) {
				if(!strlen($image_fields["image_storageid"])) {
					$image_fields["image_storageid"]=$id;
				}
				continue;
			}
			break;
	}
}



$event->log("$image_command", $_SERVER['REQUEST_TIME'], 5, "image-action", "Processing image $image_command on Image $image_name", "", "", 0, 0, 0);
switch ($image_command) {
	case 'new_image':
		$image = new image();
		$image_fields["image_id"]=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
		# switch deployment_id to deyployment_type
		$deployment_switch = new deployment();
		$deployment_switch->get_instance_by_id($image_type);
		$image_fields["image_type"] = $deployment_switch->type;
		// unquote
		$image_deployment_parameter = $image_fields["image_deployment_parameter"];
		$image_fields["image_deployment_parameter"] = stripslashes($image_deployment_parameter);
		$image->add($image_fields);
		break;

	case 'update_image':
		$image = new image();
		if(!strlen($image_fields["image_isshared"])) {
			$image_fields["image_isshared"]="0";
		}
		// unquote
		$image_deployment_parameter = $image_fields["image_deployment_parameter"];
		$image_fields["image_deployment_parameter"] = stripslashes($image_deployment_parameter);
		$image->update($image_id, $image_fields);
		break;

	case 'remove':
		$image = new image();
		$image->remove($image_id);
		break;

	case 'remove_by_name':
		$image = new image();
		$image->remove_by_name($image_name);
		break;

	case 'add_deployment_type':
		$deployment = new deployment();
		$deployment_fields["deployment_id"]=(int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
		$deployment->add($deployment_fields);
		break;

	case 'remove_deployment_type':
		$deployment = new deployment();
		$deployment->remove_by_type($deployment_name);
		break;

	default:
		$event->log("$image_command", $_SERVER['REQUEST_TIME'], 3, "image-action", "No such image command ($image_command)", "", "", 0, 0, 0);
		break;


}
?>


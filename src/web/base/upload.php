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
// check if configured already
if (file_exists("./unconfigured")) {
    header("Location: configure.php");
}
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once($RootDir.'class/htmlobjects/htmlobject.class.php');
require_once($RootDir.'class/openqrm.class.php');
require_once($RootDir.'class/resource.class.php');
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXECUTION_LAYER;
global $OPENQRM_WEB_PROTOCOL;


$lang = array(
	'label' => 'License upload',
	'upload' => 'Please select the openQRM Enterprise Server License File and Public Key from your local computer and submit',
	'msg' => 'Uploaded License file %s',
);


$html = new htmlobject($RootDir.'class/htmlobjects/');

$response = $html->response();
$form = $response->get_form();
$form->box_css = 'htmlobject_box';
$form->display_errors = true;

$d['upload']['label'] = $lang['upload'];
$d['upload']['object']['type']           = 'input';
$d['upload']['object']['attrib']['type'] = 'file';
$d['upload']['object']['attrib']['name'] = 'upload';
$d['upload']['object']['attrib']['size'] = 30;

$form->add($html->thisfile, 'thisfile');
$form->add($d);

if(!$form->get_errors() && $response->submit()) {
	require_once($RootDir.'class/file.handler.class.php');
	require_once($RootDir.'class/file.upload.class.php');
	$file = new file_handler();
	$upload = new file_upload($file);
	$error = $upload->upload('upload', $RootDir.'tmp');
	if($error !== '') {
		$form->set_error('upload', $error['msg']);
	} else {
		$resource_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/bin/openqrm license -l ".$OPENQRM_SERVER_BASE_DIR."/openqrm/web/base/tmp/".$_FILES['upload']['name']." --openqrm-cmd-mode background";
		$resource = new resource();
		$resource->get_instance_by_id(0);
		$resource->send_command($resource->ip, $resource_command);
		$response_msg = sprintf($lang['msg'], $_FILES['upload']['name']);
		sleep(4);
		$response->redirect('/openqrm/base/index.php?datacenter_msg='.$response_msg);
	}
}
$tpl = $html->template($RootDir.'tpl/upload.tpl.php');
$tpl->add($html->thisurl, 'baseurl');
$tpl->add($lang['label'], 'label');
$tpl->add($form->get_elements());
echo $tpl->get_string();

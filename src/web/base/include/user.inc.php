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
require_once( $RootDir.'/include/openqrm-database-functions.php');
global $USER_INFO_TABLE;

require_once( $RootDir.'/class/user.class.php');
require_once ($RootDir.'/class/event.class.php');

function set_env() {
	// auth user
	if (isset($_SERVER['PHP_AUTH_USER'])) {
		$OPENQRM_USER = new user($_SERVER['PHP_AUTH_USER']);
		if ($OPENQRM_USER->check_user_exists()) {
			$OPENQRM_USER->set_user();
			$GLOBALS['OPENQRM_USER'] = $OPENQRM_USER;
			define('OPENQRM_USER_NAME', $OPENQRM_USER->name);
			define('OPENQRM_USER_ROLE_NAME', $OPENQRM_USER->role);
		}
	}
	// admin user for running commands
	$OPENQRM_ADMIN = new user('openqrm');
	$OPENQRM_ADMIN->set_user();
	$GLOBALS['OPENQRM_ADMIN'] = $OPENQRM_ADMIN;
}

set_env();


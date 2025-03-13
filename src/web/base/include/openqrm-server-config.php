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

if ((file_exists("/etc/init.d/openqrm")) && (is_link("/etc/init.d/openqrm"))) {
	$OPENQRM_BASE_DIR=dirname(dirname(dirname(dirname(readlink("/etc/init.d/openqrm")))));
} else {
	$OPENQRM_BASE_DIR="/usr/share";
}
$OPENQRM_SERVER_CONFIG_FILE="$OPENQRM_BASE_DIR/openqrm/etc/openqrm-server.conf";


// function to get infos from the openqrm-server.conf
function openqrm_parse_conf ( $filepath ) {
	$ini = file( $filepath );
	if ( count( $ini ) == 0 ) { return array(); }
	$sections = array();
	$values = array();
	$globals = array();
	$i = 0;
	foreach( $ini as $line ){
		$line = trim( $line );
		// Comments
		if ( $line == '' || $line[0] != 'O' ) { continue; }
		// Key-value pair
		list( $key, $value ) = explode( '=', $line, 2 );
		$key = trim( $key );
		$value = trim( $value );
		$value = str_replace("\"", "", $value );
		$globals[ $key ] = $value;
	}
	return $globals;
}


$store = openqrm_parse_conf($OPENQRM_SERVER_CONFIG_FILE);
extract($store, EXTR_OVERWRITE);
global $OPENQRM_SERVER_CONFIG_FILE;


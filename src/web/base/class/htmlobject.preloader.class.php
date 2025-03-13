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

class htmlobject_preloader extends htmlobject
{
	function start($string = 'Loading ...')
	{
		echo '
		<div id="Loadbar" style="margin:40px 0 0 40px;display:none">
		<strong>' . $string . '</strong>
		</div>
		<script>
		document.getElementById("Loadbar").style.display = "block";
		</script>
		';
		flush();
	}
	
	function stop()
	{
		echo '
		<script>
			document.getElementById("Loadbar").style.display = "none";
		</script>
		';
	}
}

<!--
/*
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
*/
-->

<script type="text/javascript">

var passgen = {
	toggle : function(id) {
		vnc_e_id = 'vm_vncpasswd_' + id;
		pass_e_id = 'passtoggle_' + id;
		vnc = document.getElementById(vnc_e_id);
		but = document.getElementById(pass_e_id);
		if(vnc.type == 'password') {
			but.value = "{lang_password_hide}";
			np = vnc.cloneNode(true);
			np.type='text';
			vnc.parentNode.replaceChild(np,vnc);
		}
		if(vnc.type == 'text') {
			but.value = "{lang_password_show}";
			np = vnc.cloneNode(true);
			np.type='password';
			vnc.parentNode.replaceChild(np,vnc);
		}
	}
}

</script>


<h2>{label}</h2>

<div id="kvm_edit">
	<div class="row">
		<div class="span3">
			<div><b>{lang_id}</b>: {id}</div>
			<div><b>{lang_name}</b>: {name}</div>
			<div><b>{lang_resource}</b>: {resource}</div>
			<div><b>{lang_state}</b>: {state}</div>
		</div>
		<div id="addbuttons">
			{add_local_vm}<br>
			{add_network_vm}
		</div>
	</div>
	<div style="clear:both;" class="floatbreaker">&#160;</div>
	{table}
</div>

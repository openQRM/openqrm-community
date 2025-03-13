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
//-->
<h2>{label}</h2>
<div id="form">
	<form action="{thisfile}" method="GET">
	{form}

	<div style="float:left;">
	{image_password}
	{image_password_2}
	</div>
	<div style="float:left; margin: 0 0 0 20px;">
		<input type="button" id="passgenerate" onclick="passgen.generate(); return false;" class="password-button" value="{lang_password_generate}" style="display:none;"><br>
		<input type="button" id="passtoggle" onclick="passgen.toggle(); return false;" class="password-button" value="{lang_password_show}" style="display:none;">
	</div>
	<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>

	{install_from_local}
	{transfer_to_local}
	{install_from_nfs}
	{image_dp}
	{transfer_to_nfs}

	<br/>

	{install_from_template}
	{image_version}
	{image_comment}
	<div id="buttons">
	{submit}
	{cancel}
	</div>
    </form>
</div>

<script type="text/javascript">
var passgen = {
	generate : function() {
		pass = GeneratePassword();
		document.getElementById('pass_1').value = pass;
		document.getElementById('pass_2').value = pass;
	},
	toggle : function() {
		vnc = document.getElementById('pass_1');
		but = document.getElementById('passtoggle');
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
tmp = document.getElementById('pass_1');
if(tmp) {
	document.getElementById('passgenerate').style.display = 'inline';
	document.getElementById('passtoggle').style.display = 'inline';
}
</script>

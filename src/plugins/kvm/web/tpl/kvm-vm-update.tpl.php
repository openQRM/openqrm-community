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
<script type="text/javascript">
MousePosition.init();
function tr_hover() {}
function tr_click() {}
var filepicker = {
	init : function( element ) {
		this.element = element;
		mouse = MousePosition.get();
		document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
		document.getElementById('filepicker').style.left = (mouse.x + -210)+'px';
		document.getElementById('filepicker').style.top  = (mouse.y - 270)+'px';
		document.getElementById('filepicker').style.display = 'block';
		$.ajax({
			url: "{baseurl}/api.php?action=plugin&plugin=kvm&controller=kvm-vm&path=/&appliance_id={appliance_id}&{actions_name}=filepicker",
			dataType: "text",
			success: function(response) {
				document.getElementById('canvas').innerHTML = response;	
			}
		});
	},
	browse : function(target) {
		document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
		$.ajax({
			url: "{baseurl}/api.php?action=plugin&plugin=kvm&controller=kvm-vm&path="+target+"&appliance_id={appliance_id}&{actions_name}=filepicker",
			dataType: "text",
			success: function(response) {
				document.getElementById('canvas').innerHTML = response;	
			}
		});
	},
	insert : function(value) {
		document.getElementById(this.element).value = value;
		document.getElementById('filepicker').style.display = 'none';
	}
}

var passgen = {
	generate : function() {
		pass = GeneratePassword();
		document.getElementById('vnc').value = pass;
		document.getElementById('vnc_1').value = pass;
	},
	toggle : function() {
		vnc = document.getElementById('vnc');
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
function namegen() {
	var name = "";
	var name_characters = "0123456789";
	var one_random_char;
	for (j=0; j<6; j++) {
		one_random_char = name_characters.charAt(Math.floor(Math.random()*name_characters.length));
		name += one_random_char;
	}
	document.getElementById('name').value = 'kvm'+name;
}
</script>

<script>
	function nettoggle(element) {
		if(element.checked == false) {
			document.getElementById(element.name+'box').style.display = 'none';
		}
		else {
			document.getElementById(element.name+'box').style.display = 'block';
		}
	}
</script>

<h2>{label}</h2>

<div id="form">
	<form action="{thisfile}" method="GET">
	{form}

	<fieldset>
		<legend>{lang_basic}</legend>
			<div class="span8">
				{name}
			</div>
	</fieldset>

	<fieldset>
		<legend>{lang_hardware}</legend>
			<div class="span8">
				{cpus}
				{memory}
			</div>
	</fieldset>

	<fieldset>
		<legend>{lang_virtual_disk}</legend>
			<div class="span8">
				<div style="float:left;">
					{disk_interface}
				</div>
				<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
					{cdrom_iso_path}
					{cdrom_button}
				<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
			</div>
	</fieldset>

	<fieldset>
		<legend>{lang_net}</legend>
		<div class="span8">
			<fieldset>
				<div style="float:left;">{net0}</div>
				<div id="net0box" class="netbox">
					{mac}
					{bridge}
					{nic}
				</div>
			</fieldset>

			<fieldset>
				<div style="float:left;">{net1}</div>
				<div id="net1box" class="netbox">
					{mac1}
					{bridge1}
					{nic1}
				</div>
			</fieldset>
			<script>
				if(document.getElementById('net1').checked == false) {
					document.getElementById('net1box').style.display = 'none';
				}
			</script>

			<fieldset>
				<div style="float:left;">{net2}</div>
				<div id="net2box" class="netbox">
					{mac2}
					{bridge2}
					{nic2}
				</div>
			</fieldset>
			<script>
				if(document.getElementById('net2').checked == false) {
					document.getElementById('net2box').style.display = 'none';
				}
			</script>

			<fieldset>
				<div style="float:left;">{net3}</div>
				<div id="net3box" class="netbox">
					{mac3}
					{bridge3}
					{nic3}
				</div>
			</fieldset>
			<script>
				if(document.getElementById('net3').checked == false) {
					document.getElementById('net3box').style.display = 'none';
				}
			</script>

			<fieldset>
				<div style="float:left;">{net4}</div>
				<div id="net4box" class="netbox">
					{mac4}
					{bridge4}
					{nic4}
				</div>
			</fieldset>
			<script>
				if(document.getElementById('net4').checked == false) {
					document.getElementById('net4box').style.display = 'none';
				}
			</script>
		</div>
	</fieldset>

	<fieldset>
		<legend>{lang_boot}</legend>
		<div class="span8">
			{boot_cd}
			<div>
				{boot_iso}
				{boot_iso_path}
				{browse_button}
				<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
			</div>
			{boot_net}
			{boot_local}
		</div>
	</fieldset>

	<fieldset>
		<legend>{lang_vnc}</legend>
		<div class="span8">
			<div style="float:left;">
				{vnc}
				{vnc_1}
				{vnc_keymap}
			</div>
			<div style="float:left; width: 250px; margin: 3px 0 0 15px">
				<input type="button" id="passgenerate" onclick="passgen.generate(); return false;" class="password-button" value="{lang_password_generate}" style="display:none;"><br>
				<input type="button" id="passtoggle" onclick="passgen.toggle(); return false;" class="password-button" value="{lang_password_show}" style="display:none;">
			</div>
			<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
		</div>
	</fieldset>

	<div id="buttons">{submit}&#160;{cancel}</div>

	</form>
</div>

<div id="filepicker" style="display:none;position:absolute;top:15px;left:15px;"  class="function-box">
	<div class="functionbox-capation-box" 
			id="caption"
			onclick="MousePosition.init();"
			onmousedown="Drag.init(document.getElementById('filepicker'));"
			onmouseup="document.getElementById('filepicker').onmousedown = null;">
		<div class="functionbox-capation">
			{lang_browser}
			<input type="button" id ="close" class="functionbox-closebutton" value="X" onclick="document.getElementById('filepicker').style.display = 'none';">
		</div>
	</div>
	<div id="canvas"></div>
</div>



<script type="text/javascript">
if(document.getElementById('browsebutton')) {
	document.getElementById('browsebutton').style.display = 'inline';
}
if(document.getElementById('cdrom_button')) {
	document.getElementById('cdrom_button').style.display = 'inline';
}
document.getElementById('passgenerate').style.display = 'inline';
document.getElementById('passtoggle').style.display = 'inline';
</script>

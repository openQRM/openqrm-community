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

	<div class="row">
		<fieldset class="span4">
			<legend>{legend_bridge}</legend>
			{name}
			{bridge_fd}
			{bridge_hello}
			{bridge_maxage}
			{bridge_stp}
			{bridge_mac}
			{device}
		</fieldset>
		<fieldset class="span4">
			<legend>{legend_ip}</legend>
			{ip}
			{subnet}
			{gateway}
		</fieldset>
	</div>
	<div class="row">
		<fieldset class="span4">
			<legend>{legend_vlan}</legend>
			{vlan}
		</fieldset>
		
<!--		
		
		<fieldset class="span4">
			<legend>{legend_dnsmasq}</legend>
			{first_ip}
			{last_ip}
		</fieldset>

//-->

	</div>
	
	<div id="buttons" class="span2">{submit}&#160;{cancel}</div>
</div>

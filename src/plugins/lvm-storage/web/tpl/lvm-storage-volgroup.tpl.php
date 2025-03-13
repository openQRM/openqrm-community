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
	<div style="width:280px;float:left;">
		<div><b>{lang_id}</b>: {id}</div>
		<div><b>{lang_name}</b>: {name}</div>
		<div><b>{lang_resource}</b>: {resource}</div>
		<div><b>{lang_deployment}</b>: {deployment}</div>
		<div><b>{lang_state}</b>: {state}</div>
	</div>

	<div style="width:300px;float:left;">
		<div><b>{lang_name}</b>: {volgroup_name}</div>
		<div><b>{lang_attr}</b>: {volgroup_attr}</div>
		<div><b>{lang_pv}</b>: {volgroup_pv} / {volgroup_lv} / {volgroup_sn}</div>
		<div><b>{lang_size}</b>: {volgroup_vsize} / {volgroup_vfree}</div>
	</div>

	<div style="float:right; width:350px;">
		<div id="add">{add}</div>
	</div>
	<div style="clear:both; margin: 0 0 25px 0;" class="floatbreaker">&#160;</div>
	{table}
</div>

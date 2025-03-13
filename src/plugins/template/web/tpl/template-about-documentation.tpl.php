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
<h2>{label}</h2>							<!-- first headline should be h2 -->

<div class="row">							<!-- start content row: provides 10 grid columns-->
	<div class="span6">						<!-- first column: spans over 6 grid columns -->
		
		<h3>{introduction_title}</h3>		<!-- wrap title in html tag locally -->
		{introduction_content}				<!-- markup for text content need to be done in the language file -->
		{introduction_command}
		
		<h3>{introduction_title1}</h3>
		{introduction_content1}

		<h3>{provides_title}</h3>
		{provides_list}
	
		<h3>{requirements_title}</h3>
		{requirements_list}
	
	</div>

	<div class="span3 offset1">				<!-- first column: spans over 3 grid columns, 1 column offset -->
		<h3>{type_title}</h3>
		{type_content}
	
		<h3>{tested_title}</h3>
		{tested_content}
	</div>
</div>





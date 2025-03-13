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
//<![CDATA[
var lang_inventory_servers = "{lang_inventory_servers}";
var lang_inventory_storages = "{lang_inventory_storages}";
//]]>
</script>


<div class="row">
	<div class="span4">
		
		<!-- Start: Quicklink section -->
<!--
		{quicklinks_headline}
		{quicklinks}
//-->
		
		<!-- Start: Datacenter load current -->		
		<h2>
			{load_headline}
			<small>{load_current}</small>
			<!--
			<span class="pull-right">
				<a class="widget-action refresh-load-current" href="#">
					<span class="halflings-icon refresh"><i></i></span>
				</a>
			</span>
			-->
		</h2>
		<table class="table table-condensed">
			<tr>
				<td class="span2">{datacenter_load_overall}</td>
				<td>
					<div class="bar-01 chart-bar">
						<div class="bar">
							<label>0.43</label>
						</div>
						
					</div>
				</td>
			</tr>
			<tr>
				<td>{appliance_load_overall}</td>
				<td>
					<div class="bar-02 chart-bar">
						<div class="peak"></div>
						<div class="bar">
							<label>0.43</label>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>{storage_load_overall}</td>
				<td>
					<div class="bar-03 chart-bar">
						<div class="peak"></div>
						<div class="bar">
							<label>0.43</label>
						</div>
					</div>
				</td>
			</tr>
		</table>
				
		<!-- Start: Inventory overview -->
		<h2>{inventory_headline}</h2>
		<div class="row">
			<div class="span2" style="margin: 0 0 20px 60px;">
				
				<div id="chartdiv-inventory-server">
					<div class="no-data-available">
						<h3>{no_data_available}</h3>
						{link_server_management}
					</div>
				</div>	
				<div id="chartdiv-inventory-server-legend" class="donut-chart-legend"></div>
			</div>

			<div class="span2" style="margin: 0 0 20px 60px;">
				<div id="chartdiv-inventory-storage">
					<div class="no-data-available">
						<h3>{no_data_available}</h3>
						{link_storage_management}
					</div>
				</div>
				<div id="chartdiv-inventory-server-storage" class="donut-chart-legend"></div>
			</div>
		</div>
			
		
	</div>
	<div class="span6">
		<!-- Start: Datacenter load chart -->
		<h2>{load_headline}
			<small>{load_last_hour}</small>
			<!--
			<span class="pull-right">
				<a class="widget-action refresh-load-chart" href="#">
					<span class="halflings-icon refresh"><i></i></span>
				</a>
			</span>
			-->
		</h2>	
		<div id="chartdiv-load" style="height:220px; width:585px;"></div>

		<!-- Start: Event table -->
		<h2>{events_headline}
			<!--
			<span class="pull-right">
				<a class="widget-action refresh-events" href="#">
					<span class="halflings-icon refresh"><i></i></span>
				</a>&nbsp;
				<a class="widget-action linkto-events" href="#">
					<span class="halflings-icon log_in"><i></i></span>
				</a>
			</span>
			-->
		</h2>
		<table class="table table-condensed table-striped eventtable">
			<thead>
				<tr>
					<th>{events_date}</th>
					<th></th>
					<th>{events_source}</th>
					<th>{events_description}</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>


	</div>

</div>

/* handle IE missing map function */
if (!Array.prototype.map)
{
	Array.prototype.map = function(fun)
	{
		var len = this.length;
		if (typeof fun != "function")
			throw new TypeError();
		var res = new Array(len);
		var thisp = arguments[1];
		for (var i = 0; i < len; i++)
		{
			if (i in this)
			res[i] = fun.call(thisp, this[i], i, this);
		}
		return res;
	};
}


$(document).ready(function(){
	
	var seriesColors = [ "#82c782", "#f07109", "#769cd3", "#579575", "#839557", "#958c12", "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc"];
			
	// options for dummy donut charts
	var donutOptions = {
		height: 200,
		title: {
			text: '',
			textColor: '#353535'
		},
		seriesColors: seriesColors,
		
		seriesDefaults: {
			renderer:$.jqplot.DonutRenderer,
			rendererOptions:{
				sliceMargin: 5,
				shadow: false,
				startAngle: -90,
				showDataLabels: false
			}
		},
		legend: { 
			show: false
		},
		grid: {
			drawGridLines: false,
			background: '#ffffff',
			borderWidth: 0,
			shadow: false,
			renderer: $.jqplot.CanvasGridRenderer,
			rendererOptions: {}
		}
	};
	

	function renderDonutLegend(values) {
		var legend = $('<ul>');
		$.each(values, function(k,v) {
			legend.append(
				$('<li>').append(
					$('<div>').addClass('legend-tile').attr('style', 'background:' + seriesColors[k])
				).append( v[0] )
			);
		})
		return legend;
	}

	/**
	 * Build server donut chart. Does not use jqplots build-in 
	 * legend due to lack of positioning options
	 */
	function server_donut() {
		var server_list = openqrm.get_server_list();
		var server_values = [];
		var virtualization, virtualization_list = [];
		var hist = {};
		
		if(server_list != false && $('#chartdiv-inventory-server').length) {
			try{
				// remove "no data" message
				$('#chartdiv-inventory-server .no-data-available').remove();
			
				donutOptions.title.text = lang_inventory_servers;
			
				$.each(server_list, function(k,server){
					virtualization_list.push(server['appliance_virtualization']);
				});
				virtualization_list.map( function (a) { if (a in hist) hist[a] ++; else hist[a] = 1; } );
				$.each(hist, function(k,v){
					server_values.push([k + ' (' +v+ ')',v]);
				})
				$.jqplot('chartdiv-inventory-server', [server_values], donutOptions);
				var legend = renderDonutLegend(server_values);
				$('#chartdiv-inventory-server-legend').append(legend);
			} catch(e) { }
		}
	}

	/**
	 * Build storage donut chart. Does not use jqplots build-in 
	 * legend due to lack of positioning options
	 */
	function storage_donut() {
		var storage_list = openqrm.get_storage_list();
		var storage_values = [];
		var deploment, deployment_list = [];
		var hist = {};
		
		if(storage_list != false && $('#chartdiv-inventory-storage').length) {
			try{
				// remove "no data" message
				$('#chartdiv-inventory-storage .no-data-available').remove();

				donutOptions.title.text = lang_inventory_storages;
				donutOptions.seriesDefaults.rendererOptions.startAngle = 0;
					
				$.each(storage_list, function(k,storage){
					deployment_list.push(storage['storage_type']);
				});
				deployment_list.map( function (a) { if (a in hist) hist[a] ++; else hist[a] = 1; } );
				$.each(hist, function(k,v){
					storage_values.push([k + ' (' +v+ ')',v]);
				})
				$.jqplot('chartdiv-inventory-storage', [storage_values], donutOptions);
			
				var legend = renderDonutLegend(storage_values);
				$('#chartdiv-inventory-server-storage').append(legend);
			} catch(e) { }
		}
	}
	
	/**
	 *	Populate new laod data to load chart and redraw chart canvas
	 */
	function updateLoadChart() {
		var stats = openqrm.get_datacenter_load();
		var dc_load = [[],[],[]];	
		var xaxis_labels = []; 
		var idx;
		
		if(stats != null && $('#chartdiv-load').length) {
			
			$.each(stats, function(k,v) {
				idx = parseInt(k)+1;
				xaxis_labels.push(idx%5 > 0 ? (' ') : (parseInt(k)+1));
				
				dc_load[0].push( [idx, parseFloat(v['datacenter_load_overall'] )] );
				dc_load[1].push( [idx, parseFloat(v['datacenter_load_server'] )] );
				dc_load[2].push( [idx, parseFloat(v['datacenter_load_storage'] )] );
			});
	
	
			$('#chartdiv-load *').remove();
			var plot1 = $.jqplot('chartdiv-load', dc_load, 
				{
					seriesColors: seriesColors,
					showMarker:false,
					seriesDefaults: {
						linewidth: 1,
						showMarker: false
					},
					axesDefaults: {
						min: 0
					},
					axes:{
						xaxis:{
							ticks: xaxis_labels.reverse(),
							renderer: $.jqplot.CategoryAxisRenderer,
							tickOptions: {
								showGridline: false
							}
						}
					},
					grid: {
						drawGridLines: false,
						gridLineColor: '#dddddd',
						background: '#ffffff',
						borderWidth: 0,
						shadow: false,
						renderer: $.jqplot.CanvasGridRenderer,
						rendererOptions: {}
					}
				}
			);
		}
	}


	function updateEventSection() {
		var events = openqrm.get_event_list();
		
		if(events) {
			// delete tbody content 
			$('.eventtable tbody').html('');
			
			// add updated events
			$.each(events, function(k,event){
				
				var event_time = new Date((parseInt(event['event_time'])*1000));
				$('.eventtable tbody').append(
					$('<tr>')
						.append($('<td>').html(openqrm.formatDate(event_time, '%Y/%M/%d %H:%m:%s')))
						.append($('<td>').html(
							$('<span>').attr('class','pill ' + openqrm.getEventStatus(event['event_priority']))
						))
						.append($('<td>').html(event['event_source']))
						.append(
							$('<td>')
								.attr('title', event['event_description'])
								.html(
									openqrm.crop(event['event_description'], 50)
								)
						)
				);
			});
		}
	}

	function updateLoadSection() {
		var status = openqrm.get_datacenter_status();
		
		if(status != null) {
			$('.bar-01 .bar').attr('style','width:' + (status[0]*10) + '%');
			$('.bar-01 .bar label').html(status[0]);
			
			$('.bar-02 .bar').attr('style','width:' + (status[3]*10) + '%');
			$('.bar-02 .bar label').html(status[3]);
			$('.bar-02 .peak').attr({'style' : 'left: ' + (status[4]*10) + '%'});
	
			$('.bar-03 .bar').attr('style','width:' + (status[1]*10) + '%');
			$('.bar-03 .bar label').html(status[1]);
			$('.bar-03 .peak').attr({'style' : 'left: ' + (status[2]*10) + '%'});
		}
	}

	server_donut();
	storage_donut();

	updateLoadChart();
	updateLoadSection();
	updateEventSection();

	
	// Init refresh interval for datacenter load section and chart, 
	// event list section
	
	setInterval(function (){
		updateLoadChart();
		updateLoadSection();
		updateEventSection();
	}, 5000);
	
	
	// add refresh events to widget buttons
	$('.refresh-load-current').click( function() {
		updateLoadSection();
	});
	$('.refresh-load-chart').click( function() {
		updateLoadChart();
	});
	$('.refresh-events').click( function() {
		updateEventSection();
	});
	
	
});

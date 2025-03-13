<div id="menuSection_1"  class="menuSection first">
	{menu_1}
</div>

<div class="menuSection" id="menuSection_queue" style="margin-bottom:20px;">

	<div class="treemenudiv" id="menuSection_queue_1">
		<img class="imgs" alt="|-" src="/openqrm/base/img/menu/tree_split.png">
		<a class="phplmnormal" style="padding-left: 4px;" >Activities</a>
	</div>

	<div style="margin: 12px 0 0 30px;">
		<div style="float:left;padding:0 0 3px 0;">
			<span class="phplm">Events</span>
		</div>
		<div id="Event_messages">
			<a id="Event_box" href="index.php?base=event&amp;event_filter=error" style="visibility:hidden;" title="Error Events"><span class="pill error" id="events_critical">0</span></a>
			<a id="Event_active_box" href="index.php?base=event&amp;event_filter=active" style="visibility:hidden;" title="Active Events"><span class="pill orange" id="events_active">0</span></a>
		</div>
		<div class="floatbreaker">&#160;</div>
	</div>

	<div style="margin: 4px 0 12px 30px;">
		<div style="float:left;padding:0 0 3px 0;">
			<span class="phplm">Commands</span>
		</div>
		<div id="Queue_messages">
			<a title="Running Commands" style="visibility:hidden;cursor:pointer;" onclick="show_queue_commands();"><span class="pill active" id="queue_running">1</span></a>
			<a title="Queued Commands" style="visibility:hidden;"><span class="pill idle" id="queue_waiting" style="cursor:default;">&#160;</span></a>
		</div>
		<div class="floatbreaker">&#160;</div>
	</div>

</div>

<div id="queue_command" style="display:none;position:absolute;top:15px;left:15px;z-index:10; width:330px;" class="function-box">
	<div class="functionbox-capation-box" 
			id="caption"
			onclick="MousePosition.init();"
			onmousedown="Drag.init(document.getElementById('queue_command'));"
			onmouseup="document.getElementById('queue_command').onmousedown = null;">
		<div class="functionbox-capation" style="text-align:right;padding:3px;">
			<input type="button" id ="close" class="functionbox-closebutton" value="X" onclick="show_queue_commands();">
		</div>
	</div>
	<div id="queue_command_box"
		style="
			width:300px;
			background-color: #FFFFE0;
			padding: 14px;
			text-align:left;
			border-left: solid 1px #aaaaaa;
			border-right: solid 1px #aaaaaa;
			border-bottom: solid 1px #aaaaaa;"
		></div>
	<div id="queue_command_history" 
		style="
			display:none;
			width:300px;
			background-color: #FFFFE0;
			padding: 14px;
			text-align:left;
			border-left: solid 1px #aaaaaa;
			border-right: solid 1px #aaaaaa;
			border-bottom: solid 1px #aaaaaa;
			height:100px;
			overflow-y:auto;"
		></div>
</div>


<div id="menuSection_2" class="menuSection first">
{menu_2}
</div>

<script type="text/javascript">

function get_queue_status() {
	$.ajax({
		url: "api.php?action=get_queue_status",
		cache: false,
		async: false,
		dataType: "text",
		success: function(response) {
			if(response != '') {
				var status = response.split(";;");
				var queue_waiting = parseInt(status[0]);
				var queue_running = parseInt(status[1]);
				var queue_command = status[2];
				$("#queue_waiting").html(queue_waiting);
				if(queue_waiting > 0) {
					$("#queue_waiting").html(queue_waiting);
					document.getElementById('queue_waiting').style.visibility = 'visible';
				} else {
					document.getElementById('queue_waiting').style.visibility = 'hidden';
				}
				if(queue_running > 0) {

					$("#queue_running").html(queue_running);
					if($("#queue_command_box").html() != '') {
						tmp1 = $("#queue_command_box").html().split('<br>');
						tmp2 = queue_command.split('<br>');
						if(tmp1[1] != tmp2[1]) {
							if($("#queue_command_history").html() == '') {
								document.getElementById('queue_command_history').style.display = 'block';
								$("#queue_command_history").html($("#queue_command_box").html());
							} else {
								$("#queue_command_history").html($("#queue_command_box").html() + '<hr>' + $("#queue_command_history").html());
							}
						}
					}

					$("#queue_command_box").html(queue_command);
					document.getElementById('queue_running').style.visibility = 'visible';
				} else {
					document.getElementById('queue_running').style.visibility = 'hidden';
				}
			}
		}
	});
	setTimeout("get_queue_status()", 2000);
}

function show_queue_commands() {
	elem = document.getElementById('queue_command');
	mode = elem.style.display;
	if(mode == 'block') {
		elem.style.display = 'none';
		$("#queue_command_history").html('');
		$("#queue_command_history").style.display = 'none';
	} else {
		mouse = MousePosition.get();
		elem.style.left = (mouse.x + -210)+'px';
		elem.style.top  = (mouse.y - 270)+'px';
		elem.style.display = 'block';
	}
}

function get_event_status() {
	$.ajax({
		url: "api.php?action=get_event_status",
		cache: false,
		async: true,
		dataType: "text",
		success: function(response) {
			if(response != '') {
				var status_array = response.split("@");
				var event_error = parseInt(status_array[6]);
				var event_active = parseInt(status_array[7]);
				$("#events_critical").html(event_error);
				if(event_error > 0) {
					document.getElementById('Event_box').style.visibility = 'visible';
				} else {
					document.getElementById('Event_box').style.visibility = 'hidden';
				}

				if(event_active > 0) {
					$("#events_active").html(event_active);
					document.getElementById('Event_active_box').style.visibility = 'visible';
				} else {
					document.getElementById('Event_active_box').style.visibility = 'hidden';
				}
			}
		}
	});
	setTimeout("get_event_status()", 5000);
}

MousePosition.init();
get_event_status();
get_queue_status();
</script>

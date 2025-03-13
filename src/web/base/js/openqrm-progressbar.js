function trigger_progress() {
	$("#progressbar").progressbar({});
	$.ajax({
		url: "{url}",
		dataType: "text",
		error: function(response) {
			if($("#progressbar").html == '') {
				setTimeout("trigger_progress()", 1000);
			}
		},
		success: function(response) {
			var no = parseInt(response);
			if (no < 0) { no = 0; }
			$("#progressbar").progressbar("option", "value", no);
			if (no < 100) {
				$("#watcher").html("&nbsp;&nbsp;<small>" + response + "% - {lang_in_progress}</small>");
				setTimeout("trigger_progress()", 1000);
			} else {
				if(no == 100) {
					$("#watcher").html("&nbsp;<small>100 % - {lang_finished}</small>");
				} else {
					setTimeout("trigger_progress()", 1000);
				}
			}	
		}
	});
}
trigger_progress();
/*
<div class="progress_bar">
	<div id="progressbar"></div>
</div>
<div id="watcher" class="progress_watcher">{lang_in_progress}</div>
*/

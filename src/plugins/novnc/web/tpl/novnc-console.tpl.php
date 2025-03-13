<script>
var INCLUDE_URI = '{jsurl}';
function receiveMessage(event)
{
	window.setTimeout("dislayControls()", 100);
}
// IE postMessage callback
function postMessagePassthrough(s){
	window.setTimeout("dislayControls()", 100);
}
// delay the popup
function pausecomp(ms) {
	ms += new Date().getTime();
	while (new Date() < ms){}
}
pausecomp(2000);
// 
if(window.location.protocol == 'https:') {
	if(window.addEventListener) {
		window.addEventListener("message", receiveMessage, false);
	} 
	else if(window.attachEvent) {
		window.attachEvent('onmessage',receiveMessage);
	}
	var windowName = 'userConsole'; 
	var popUp = window.open('https://{host}:{port}/novncsslcheck.html?__' + new Date(), windowName, 'width=600, height=600, left=24, top=24, scrollbars, resizable');
	if (popUp == null || typeof(popUp)=='undefined') { 	
		alert('Please disable your pop-up blocker and reload page.'); 
	}
} else {
	window.onload = function() {
		dislayControls();
	}
}
function dislayControls()
{
	document.getElementById('sslcheck').style.display = 'none';
}
</script>

<div class="novncpage" style="position:relative;">


	<div id="noVNC-control-bar">
		<div id="noVNC-menu-bar" style="display:none;">
		</div>
		<!--noVNC Mobile Device only Buttons-->
		<div class="noVNC-buttons-left">
			<input type="image" src="{imgurl}drag.png"
				id="noVNC_view_drag_button" class="noVNC_status_button"
				title="Move/Drag Viewport" alt="drag">
			<div id="noVNC_mobile_buttons">
				<input type="image" src="{imgurl}mouse_none.png"
					id="noVNC_mouse_button0" class="noVNC_status_button" alt="mouse0">
				<input type="image" src="{imgurl}mouse_left.png"
					id="noVNC_mouse_button1" class="noVNC_status_button" alt="mouse1">
				<input type="image" src="{imgurl}mouse_middle.png"
					id="noVNC_mouse_button2" class="noVNC_status_button" alt="mouse2">
				<input type="image" src="{imgurl}mouse_right.png"
					id="noVNC_mouse_button4" class="noVNC_status_button" alt="keyboard">
				<input type="image" src="{imgurl}keyboard.png"
					id="showKeyboard" class="noVNC_status_button"
					title="Show Keyboard" alt="keyboard">
				<input type="text" autocapitalize="off" autocorrect="off"
					id="keyboardinput" class="">
				<div id="noVNC_extra_keys">
					<input type="image" src="{imgurl}showextrakeys.png"
						id="showExtraKeysButton"
						class="noVNC_status_button" alt="keys">
					<input type="image" src="{imgurl}ctrl.png"
						id="toggleCtrlButton"
						class="noVNC_status_button" alt="crtl">
					<input type="image" src="{imgurl}alt.png"
						id="toggleAltButton"
						class="noVNC_status_button" alt="alt">
					<input type="image" src="{imgurl}tab.png"
						id="sendTabButton"
						class="noVNC_status_button" alt="tab">
					<input type="image" src="{imgurl}esc.png"
						id="sendEscButton"
						class="noVNC_status_button" alt="esc">
				</div>
			</div>
		</div>

		<div id="resourceInfo">{resource}</div>
		<div id="noVNC_status">{please_wait}</div>

		<!--noVNC Buttons-->
		<div class="noVNC-buttons-right">
			<input type="image" src="{imgurl}glyphicons_349_fullscreen.png"
				 id="detachbutton" class="noVNC_status_button"
				title="{lang_detach}" onclick="popUp();" alt="{lang_detach}" />
			<input type="image" src="{imgurl}ctrlaltdel.png"
				 id="sendCtrlAltDelButton" class="noVNC_status_button"
				title="{lang_Ctrl-Alt-Del}" alt="{lang_Ctrl-Alt-Del}" />
			<input type="image" src="{imgurl}clipboard.png"
				id="clipboardButton" class="noVNC_status_button"
				title="{lang_clipboard}" alt="{lang_clipboard}" />
			<input type="image" src="{imgurl}settings.png"
				id="settingsButton" class="noVNC_status_button"
				title="{lang_settings}" alt="{lang_settings}" />
			<input type="image" src="{imgurl}connect.png"
				id="connectButton" class="noVNC_status_button"
				title="{lang_connect}" alt="{lang_connect}" />
			<input type="image" src="{imgurl}disconnect.png"
				id="disconnectButton" class="noVNC_status_button"
				title="{lang_disconnect}" alt="{lang_disconnect}" />
		</div>

		<!-- Description Panel -->
		<!-- Shown by default when hosted at for kanaka.github.com -->
		<div id="noVNC_description" style="display:none;" class="">
			noVNC is a browser based VNC client implemented using HTML5 Canvas
			and WebSockets. You will either need a VNC server with WebSockets
			support (such as <a href="http://libvncserver.sourceforge.net/">libvncserver</a>)
			or you will need to use
			<a href="https://github.com/kanaka/websockify">websockify</a>
			to bridge between your browser and VNC server. See the noVNC
			<a href="https://github.com/kanaka/noVNC">README</a>
			and <a href="http://kanaka.github.com/noVNC">website</a>
			for more information.
			<br />
			<input id="descriptionButton" type="button" value="Close">
		</div>

		<!-- Clipboard Panel -->
		<div id="noVNC_clipboard" class="triangle-right top">
			<textarea id="noVNC_clipboard_text" rows=5>
			</textarea>
			<br />
			<input id="noVNC_clipboard_clear_button" type="button"
				value="{lang_clear}">
		</div>

		<!-- Settings Panel -->
		<div id="noVNC_settings" class="triangle-right top">
			<div id="noVNC_settings_menu">
				<input id="noVNC_encrypt" type="hidden">
				<label><input id="noVNC_true_color" type="checkbox"> {lang_true_color}</label>
				<label><input id="noVNC_cursor" type="checkbox"> {lang_local_cursor}</label>
				<label><input id="noVNC_clip" type="checkbox"> {lang_clip}</label>
				<label><input id="noVNC_shared" type="checkbox"> {lang_shared_mode}</label>
				<label><input id="noVNC_view_only" type="checkbox"> {lang_view_only}</label>
				<label><input id="noVNC_connectTimeout" type="text"> {lang_timeout}</label>

				<input id="noVNC_path" type="text" value="websockify" style="display: none;">
				<input id="noVNC_repeaterID" type="text" value="" style="display: none;">
				<select id="noVNC_stylesheet" name="vncStyle" style="display: none;">
					<option value="default">default</option>
				</select>
				<select id="noVNC_logging" name="vncLogging" style="display: none;"></select>

				<input type="button" id="noVNC_apply" value="{lang_apply}">
			</div>
		</div>

		<!-- Connection Panel -->
		<div id="noVNC_controls" class="triangle-right top" style="visibility:visible;">
			<table>
				<tr><td><label for="noVNC_host"><strong>{lang_host} </strong></label></td><td><input id="noVNC_host"></td></tr>
				<tr><td><label for="noVNC_port"><strong>{lang_port} </strong></label></td><td><input id="noVNC_port"></td></tr>
				<tr><td><label for="noVNC_password"><strong>{lang_password} </strong></label></td><td><input id="noVNC_password" type="password"></td></tr>
				<tr><td>&#160;</td><td><input id="noVNC_connect_button" type="button" value="{lang_connect}"></td></tr>
			</table>
		</div>

	</div> <!-- End of noVNC-control-bar -->

	<noscript><div class="noscript">{lang_error_js_disabled}</div></noscript>

	<div id="sslcheck" style="display:none;padding: 45px 0 5px 0;text-align: center;">{lang_ssl_check}</div>
	<script>
		if(window.location.protocol == 'https:') {
			document.getElementById('sslcheck').style.display = 'block';
		}
	</script>

	<div id="noVNC_screen" style="visibility:visible;">
		<div id="noVNC_screen_pad"></div>
		<h1 id="noVNC_logo"><span>no</span><br />VNC</h1>
		<!-- HTML5 Canvas -->
		<div id="noVNC_container">
			<canvas id="noVNC_canvas" width="640" height="20">
						Canvas not supported.
			</canvas>
		</div>
	</div>

	<div id="noVNC_popup_status_panel" class=""></div>

	<script src="{jsurl}util.js"></script>
	<script src="{jsurl}ui.js"></script>
	<script>
		window.onload = function() {
			UI.updateSetting('host', '{host}');
			UI.updateSetting('port', '{port}');
			UI.updateSetting('connectTimeout', '20');
		}
	</script>


	<script>
		function popUp() {
			path = "{url}";
			noVncWindow = window.open(path, "noVnc_{port}", "titlebar=no, location=no, scrollbars=yes, width=800, height=500, left=50, top=50");
			noVncWindow.focus();
		}
	</script>

</div>

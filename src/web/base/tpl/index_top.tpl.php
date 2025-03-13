<div class="logo">
	<a href="{thisfile}"><img src="img/logo.png" alt="openQRM logo" width="110"></a>
	<div class="openqrmVersion"></div>
</div>


<div class="top">
	<form>
		<input id="username" type="hidden" value="{username}">
		<input id="userlang" type="hidden" value="{userlang}">
		{language_select}
		<a id="Login_box" href="index.php?base=user" onclick="wait();">{account}</a>
		<a id="Support_box" href="https://openqrm-enterprise.com?s=oqbe" target="_blank">{support}</a>
		<a id="Docu_box" href="https://wiki.openqrm-enterprise.com/view/Main_Page?s=oqbe" target="_blank">{documentation}</a>
		<a id="Info_box" href="javascript:openPopup()">{info}</a>
		<div class="floatbreaker">&#160;</div>
	</form>
</div>
<div class="floatbreaker">&#160;</div>

<div id="popupInfo">
	<a id="popupInfoClose">x</a>
	<h1><img src="img/logo.png" alt="openQRM logo"> openQRM Enterprise {version}</h1>
		<p id="infoArea"></p>
</div>
<div id="backgroundPopup"></div>


<div style="position: absolute;left: 310px;top: 15px;">
<script type='text/javascript' src='//storage.ko-fi.com/cdn/widget/Widget_2.js'></script><script type='text/javascript'>kofiwidget2.init('Buy us a Coffee', '#9e9e9e', 'A08116CR');kofiwidget2.draw();</script>
</div>


<script type="text/javascript">
$(document).ready(function(){
	$("#popupInfoClose").click(function(){
		disablePopup();
	});
	$("#backgroundPopup").click(function(){
		disablePopup();
	});
});

function set_language() {
	var username = $("#username").val();
	var selected_lang = $("#Language_select").val();

	$.ajax({
		url: "api.php?action=set_language&user=" + username + "&lang=" + selected_lang,
		cache: false,
		async: false,
		dataType: "text",
		success: function(response) {
			window.location.reload();
		}
	});

}

var popupStatus = 0;
function loadPopup(){
	if(popupStatus==0){
		$("#backgroundPopup").css({ "opacity": "0.3" });
		$("#backgroundPopup").fadeIn();
		$("#popupInfo").fadeIn();
		popupStatus = 1;
	}
}

function disablePopup(){
	if(popupStatus==1){
		$("#backgroundPopup").fadeOut();
		$("#popupInfo").fadeOut();
		popupStatus = 0;
	}
}

function centerPopup(){
	var windowWidth = document.documentElement.clientWidth;
	var windowHeight = document.documentElement.clientHeight;
	var popupHeight = $("#popupInfo").height();
	var popupWidth = $("#popupInfo").width();
	$("#popupInfo").css({
		"position": "absolute",
		"top": "120px",
		"left": "400px" 
	});
	$("#backgroundPopup").css({
		"height": windowHeight
	});
}


function openPopup() {
	centerPopup();
	loadPopup();
	get_info_box();
}

function get_info_box() {
	$.ajax({
		url: "api.php?action=get_info_box",
		cache: false,
		async: false,
		dataType: "text",
		success: function(response) {
			$("#infoArea").html(response);
		}
	});
}
</script>

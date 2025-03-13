/**
 * Get Browser Infos
 * 
 */
var BrowserInfo = {
	swfVersion : function() {
		var isIE    = (navigator.appVersion.indexOf("MSIE") != -1) ? true : false;
		var isWin   = (navigator.appVersion.toLowerCase().indexOf("win") != -1) ? true : false;
		var isOpera = (navigator.userAgent.indexOf("Opera") != -1) ? true : false;
		var version = "false";	
		if (navigator.plugins != null && navigator.plugins.length > 0) {
			if (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]) {
				var swVer2 = navigator.plugins["Shockwave Flash 2.0"] ? " 2.0" : "";
				var flashDescription = navigator.plugins["Shockwave Flash" + swVer2].description;
				var descArray = flashDescription.split(" ");
				var tempArrayMajor = descArray[2].split(".");			
				var vMa = tempArrayMajor[0];
				var vMin = tempArrayMajor[1];
				var vRev = descArray[3];
				if (vRev == "") {
					vRev = descArray[4];
				}
				if (vRev[0] == "d") {
					vRev = vRev.substring(1);
				} else if (vRev[0] == "r") {
					vRev = vRev.substring(1);
					if (vRev.indexOf("d") > 0) {
						vRev = vRev.substring(0, vRev.indexOf("d"));
					}
				}
				version = vMa + "," + vMin + "," + vRev;
			}
		}
		else if (navigator.userAgent.toLowerCase().indexOf("webtv/2.6") != -1) version = 4;
		else if (navigator.userAgent.toLowerCase().indexOf("webtv/2.5") != -1) version = 3;
		else if (navigator.userAgent.toLowerCase().indexOf("webtv") != -1) version = 2;
		else if (isIE && isWin && !isOpera) {
			try {
				axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
				version = axo.GetVariable("$version");
			} catch (e) {}
			if (!version)
			{
				try {
					axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");			
					version = "WIN 6,0,21,0";
					axo.AllowScriptAccess = "always";
					version = axo.GetVariable("$version");
				} catch (e) {}
			}
			if (!version)
			{
				try {
					axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.3");
					version = axo.GetVariable("$version");
				} catch (e) {}
			}
			if (!version)
			{
				try {
					axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.3");
					version = "WIN 3,0,18,0";
				} catch (e) {}
			}
			if (!version)
			{
				try {
					axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
					version = "WIN 2,0,0,11";
				} catch (e) { version = "false"; }
			}	
		}
		version = version.replace(/[a-z ]/gi, ''); 
		version = version.replace(/,/g, '.'); 
		return version;
	},
	info : function() {
		info = {
			CODENAME   : navigator.appCodeName,
			APPNAME    : navigator.appName,
			VERSION    : navigator.appVersion,
			COOKIES    : navigator.cookieEnabled,
			LANGUAGE   : navigator.language,
			PLATFORM   : navigator.platform,
			USERAGENT  : navigator.userAgent,
			WxH        : screen.width +" x "+ screen.height,
			COLORDEPTH : screen.colorDepth,
			FLASH      : this.swfVersion()
		}
		return info;
	}
};

/**
 * Drag functions
 * 
 */
var Drag = {

	obj : null,
	init : function(o, oRoot, minX, maxX, minY, maxY, bSwapHorzRef, bSwapVertRef, fXMapper, fYMapper)
	{
		o.onmousedown	= Drag.start;        
		o.hmode			= bSwapHorzRef ? false : true ;
		o.vmode			= bSwapVertRef ? false : true ;
		o.root = oRoot && oRoot != null ? oRoot : o ;

		if (o.hmode  && isNaN(parseInt(o.root.style.left  ))) o.root.style.left   = "0px";
		if (o.vmode  && isNaN(parseInt(o.root.style.top   ))) o.root.style.top    = "0px";
		if (!o.hmode && isNaN(parseInt(o.root.style.right ))) o.root.style.right  = "0px";
		if (!o.vmode && isNaN(parseInt(o.root.style.bottom))) o.root.style.bottom = "0px";

		o.minX	= typeof minX != 'undefined' ? minX : 0;
		o.minY	= typeof minY != 'undefined' ? minY : 0;
		o.maxX	= typeof maxX != 'undefined' ? maxX : null;
		o.maxY	= typeof maxY != 'undefined' ? maxY : null;

		o.xMapper = fXMapper ? fXMapper : null;
		o.yMapper = fYMapper ? fYMapper : null;

		o.root.onDragStart	= new Function();
		o.root.onDragEnd	= new Function();
		o.root.onDrag		= new Function();
	},

	start : function(e)
	{
		var o = Drag.obj = this;
		e = Drag.fixE(e);
		var y = parseInt(o.vmode ? o.root.style.top  : o.root.style.bottom);
		var x = parseInt(o.hmode ? o.root.style.left : o.root.style.right );
		o.root.onDragStart(x, y);

		o.lastMouseX	= e.clientX;
		o.lastMouseY	= e.clientY;

		if (o.hmode) {
			if (o.minX != null)	o.minMouseX	= e.clientX - x + o.minX;
			if (o.maxX != null)	o.maxMouseX	= o.minMouseX + o.maxX - o.minX;
		} else {
			if (o.minX != null) o.maxMouseX = -o.minX + e.clientX + x;
			if (o.maxX != null) o.minMouseX = -o.maxX + e.clientX + x;
		}

		if (o.vmode) {
			if (o.minY != null)	o.minMouseY	= e.clientY - y + o.minY;
			if (o.maxY != null)	o.maxMouseY	= o.minMouseY + o.maxY - o.minY;
		} else {
			if (o.minY != null) o.maxMouseY = -o.minY + e.clientY + y;
			if (o.maxY != null) o.minMouseY = -o.maxY + e.clientY + y;
		}

		document.onmousemove	= Drag.drag;
		document.onmouseup		= Drag.end;

		return false;
	},

	drag : function(e)
	{
		e = Drag.fixE(e);
		var o = Drag.obj;

		var ey	= e.clientY;
		var ex	= e.clientX;
		var y = parseInt(o.vmode ? o.root.style.top  : o.root.style.bottom);
		var x = parseInt(o.hmode ? o.root.style.left : o.root.style.right );
		var nx, ny;

		if (o.minX != null) ex = o.hmode ? Math.max(ex, o.minMouseX) : Math.min(ex, o.maxMouseX);
		if (o.maxX != null) ex = o.hmode ? Math.min(ex, o.maxMouseX) : Math.max(ex, o.minMouseX);
		if (o.minY != null) ey = o.vmode ? Math.max(ey, o.minMouseY) : Math.min(ey, o.maxMouseY);
		if (o.maxY != null) ey = o.vmode ? Math.min(ey, o.maxMouseY) : Math.max(ey, o.minMouseY);

		nx = x + ((ex - o.lastMouseX) * (o.hmode ? 1 : -1));
		ny = y + ((ey - o.lastMouseY) * (o.vmode ? 1 : -1));

		if (o.xMapper)		nx = o.xMapper(y)
		else if (o.yMapper)	ny = o.yMapper(x)

		Drag.obj.root.style[o.hmode ? "left" : "right"] = nx + "px";
		Drag.obj.root.style[o.vmode ? "top" : "bottom"] = ny + "px";
		Drag.obj.lastMouseX	= ex;
		Drag.obj.lastMouseY	= ey;

		Drag.obj.root.onDrag(nx, ny);
		return false;
	},

	end : function()
	{
		document.onmousemove = null;
		document.onmouseup   = null;
		Drag.obj.root.onDragEnd(	parseInt(Drag.obj.root.style[Drag.obj.hmode ? "left" : "right"]), 
									parseInt(Drag.obj.root.style[Drag.obj.vmode ? "top" : "bottom"]));
		Drag.obj = null;
	},

	fixE : function(e)
	{
		if (typeof e == 'undefined') e = window.event;
		if (typeof e.layerX == 'undefined') e.layerX = e.offsetX;
		if (typeof e.layerY == 'undefined') e.layerY = e.offsetY;
		return e;
	}
};

/**
 * Get current mouse position
 * 
 */
var MousePosition = {	
	init : function ()
	{
		this.x = '';
		this.y = '';
		this.MicrosoftModel = 0;
		if (document.all){
			this.MicrosoftModel  = 1;
			document.onmousemove = this.capture;
		}
		if (!(this.MicrosoftModel)){
			if (typeof(document.addEventListener) == "function"){
				document.addEventListener("mousemove", this.capture, true);
			} else if (document.runner){
		  		window.captureEvents(Event.MOUSEMOVE);
		 		window.onmousemove = this.capture;
			}
		}
	},
	capture : function( event ){
		if (!event){  event = window.event; }
		if (typeof(event)!="object") return;
		if (document.all){
			x = event.clientX;
			y = event.clientY + document.documentElement.scrollTop;
		} else {
			x = event.pageX;
			y = event.pageY;
		}
		MousePosition.x = x;
		MousePosition.y = y;
	},
	get : function (){
		return { x: this.x, y: this.y };
	}
};

/**
 * Get window size
 * 
 */
var WindowSize = function() {
	height = "";
	if(window.innerHeight != "undefined") {
		height = window.innerHeight;
	}
	if(document.body.clientHeight != "undefined") {
		height = document.body.clientHeight;
	}
	if(document.documentElement.clientHeight != "undefined") {
		height = document.documentElement.clientHeight;
	}

	width = "";
	if(window.innerWidth != "undefined") {
		width = window.innerWidth;
	}
	if(document.body.clientWidth != "undefined") {
		width = document.body.clientWidth;
	}
	if(document.documentElement.clientWidth != "undefined") {
		width = document.documentElement.clientWidth;
	}

	return { height: height, width: width };
};

/**
 * Get or Set Selection or Insert at Selection of an element
 * 
 */
var SelectionRange = {

	get : function(element){
		if(window.getSelection) {
			start = element.selectionStart;
			end   = element.selectionEnd;
		}
		else if( document.selection ){
			// current selection
			range = document.selection.createRange();
			// use this as a 'dummy'
			stored_range = range.duplicate();
			// select all text
			stored_range.moveToElementText( element );
			// move 'dummy' end point to end point of original range
			stored_range.setEndPoint( 'EndToEnd', range );
			// calculate start and end points
			start = parseInt(stored_range.text.length) - parseInt(range.text.length);
			end   = parseInt(start) + parseInt(range.text.length);
		}
		return {start: start, end: end};
	},

	set : function(element, start, end){
		if (element.setSelectionRange) {
			element.focus();
			element.setSelectionRange(start, end);
		}
		else if (element.createTextRange) {
			range = element.createTextRange();
			start = element.value.substring(0, start).replace(/\r/g,"");
			end   = element.value.substring(0, end).replace(/\r/g,"");
			range.collapse(true);
			range.moveEnd('character', end.length);
			range.moveStart('character', start.length);
			range.select();
		}
	},

	insert : function(element, content){
		st  = element.scrollTop;
		sl  = element.scrollLeft;
		sel = this.get(element);
		element.value = element.value.substring(0, sel.start) + content + element.value.substring(sel.end, element.value.length);
		this.set(element,  parseInt(sel.start) + parseInt(content.length), parseInt(sel.start) + parseInt(content.length));
		element.scrollTop  = st;
		element.scrollLeft = sl;
	}

};

/**
 * Parse object attributes
 *
 *
 */
function objects(obj) {
	val = new Array();
	i = 0;
	for (var attrib in obj) {
		val[i] = attrib + ' : ' + obj[attrib];
		i = i+1;
	}
	val.sort();
	alert(val.join(", "));
}

/**
 * Generate Password
 *
 *
 */
function GeneratePassword() {
	var password = "";
	var password_characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	var one_random_char;
	for (j=0; j<8; j++) {
		one_random_char = password_characters.charAt(Math.floor(Math.random()*password_characters.length));
		password += one_random_char;
	}
	return password;
}

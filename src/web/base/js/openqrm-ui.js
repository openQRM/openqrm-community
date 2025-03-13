/**
 * This file contains misc openqrm UI javascript methods
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */
 
 


$.extend({
    error: function( msg ) { throw msg; },
    parseJSON: function( data ) {
        if ( typeof data !== "string" || !data ) {
            return null;
        }    
        data = jQuery.trim( data );    
        if ( /^[\],:{}\s]*$/.test(data.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, "@")
            .replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, "]")
            .replace(/(?:^|:|,)(?:\s*\[)+/g, "")) ) {    
            return window.JSON && window.JSON.parse ?
                window.JSON.parse( data ) :
                (new Function("return " + data))();    
        } else {
            jQuery.error( "Invalid JSON: " + data );
        }
    }
});


/**
	List of global 'a' tag classes and their icon class
	(see glyphicons-halflings @ http://www.glyphicons.com)
	used for action-buttons.	
*/
var actionButtons = {
	'a.add'		: 'plus-sign',
	'a.edit'	: 'edit',
	'a.manage'	: 'cog',
	'a.resize'	: 'resize-full',
	'a.details'	: 'search',
	'a.remove'	: 'minus-sign',
	'a.console'	: 'dashboard',
	'a.snap'	: 'camera',
	'a.migrate'	: 'arrow-right',
	'a.clone'	: 'export',
	'a.graphs'	: 'signal',
	'a.stop'	: 'stop',
	'a.start'	: 'play',
	'a.enable'	: 'plus',
	'a.disable'	: 'minus'
};


$(document).ready(function(){

	// move error-event/active-event info from header to events menu item
	//$('#Event_box').addClass('pull-right').appendTo('#menu1_9');
	//$('#Event_active_box').addClass('pull-right').attr('style','display: none; margin-right:3px').appendTo('#menu1_9');

	// add icon to menu section headers
	$('#menu1_1').prepend( $('<span>').addClass('glyphicons global').prepend($('<i>')) );
	$('#menu2_1').prepend( $('<span>').addClass('glyphicons show_big_thumbnails').prepend($('<i>'))	);
	$('#menuSection_queue_1').prepend( $('<span>').addClass('glyphicons inbox_in').prepend($('<i>')) );
		
	// prepare actionButton links
	$.each(actionButtons, function(key, value) {
		$(key).prepend(
			$('<span>').addClass('halflings-icon  white ' + value).prepend($('<i>'))
		);
	});
	
	// remove attribute 'style' from .pageturn_head a .pageturn_bottom a
	$('.pageturn_head a[style="visibility:hidden;"]').addClass('disabled').removeAttr('style');
	$('.pageturn_bottom a[style="visibility:hidden;"]').addClass('disabled').removeAttr('style');
	
	// find .disabled elements and remove href and onlick attributes
	$('a.disabled').removeAttr('href').removeAttr('onclick').attr("disabled", "disabled");
	
	// add openqrm.wait event to listed elements
	// list of interface elements that should trigger the openqrm.wait() function
	var waitElements = [
		'input.submit',
		'a.edit',
		'a.manage',
		'.htmlobject_tabs li span a',
		'.pageturn_head a:not(.disabled)',
		'.pageturn_bottom a:not(.disabled)',
		'.actiontable input',
		'a.refresh'
	];
	
	// attach wait function to list elements
	$.each(waitElements, function(k, v) {
		$(v).attr('data-message', jstranslation['please_wait']);
		$(v).click(function() { openqrm.wait(v ,'') } );
	});
	
	// attach namegen button to element
	var namegen = $('<input>');
	$(namegen).attr('type', 'button');
	$(namegen).attr('class', 'namegenButton');
	$(namegen).attr('tabindex', '-1');
	$(namegen).attr('value', jstranslation['generate_name']);
	$(namegen).click(function() {
		openqrm.generateUniqueName($('input.namegen').attr('data-prefix'), $('input.namegen'), $('input.namegen').attr('data-length'));
		return false;
	});
	$('input.namegen').after(namegen);

	// htmlobjects_tablebuilder
	$('select.sort').attr('data-message', jstranslation['please_wait']);
	$('select.sort').change(function() { openqrm.wait(this ,''); this.form.submit(); } );
	$('select.order').attr('data-message',  jstranslation['please_wait']);
	$('select.order').change(function() { openqrm.wait(this ,''); this.form.submit(); } );
	$('select.limit').attr('data-message',  jstranslation['please_wait']);
	$('select.limit').change(function() { openqrm.wait(this ,''); this.form.submit(); } );

	// menu
	$.each($('a.phplmselected'), function(k, v) {
		t = $(v).attr('target');
		if(t != '_blank') {
			$(v).attr('data-message', jstranslation['please_wait']);
			$(v).click(function() { openqrm.wait(v ,'') } );
		}
	});
	$.each($('a.phplm'), function(k, v) {
		t = $(v).attr('target');
		if(t != '_blank') {
			$(v).attr('data-message', jstranslation['please_wait']);
			$(v).click(function() { openqrm.wait(v ,'') } );
		}
	});

});

/* global openqrm interface methods */
openqrm = {

	wait : function(element, msg) {
	
		// TODO: after updating jquery we should use the 'data' method
		if($(element).attr('data-message') != 'undefined') {
			msg = $(element).attr('data-message');
		}

		// create transparent background overlay
		$('body').prepend( 
			$('<div>').attr('class','modal-overlay')
		);
		
		// create content box with message
		$('body').prepend(
			$('<div>')
				.attr('class', 'modal-box lead')
				.append(
					$('<p>').html(msg)
				)
				/*
				.append(
					$('<img>').attr({'src':'/openqrm/base/img/ajax-loader.gif','class':'ajax-loader'})
				)
				*/
		);

		// center content box on screen
		$('.modal-box').css({
			left: (($(window).width() - $('.modal-box').outerWidth())/2),
			top: (($(window).height() - $('.modal-box').outerHeight())/2)
		});
	},
	
	/* Global generate names method
		prefix:         string prefix to generate unique string
		element:        input element that should be filled with generated string
		length:         length of unique string
	*/
	generateUniqueName : function (prefix, element, length) {
		if(!length) {
			length = 10;
		}
		var name = "";
		var name_characters = "123456789"; // don't use 0 because of openvz
		var one_random_char;
		for (j=0; j < length; j++) {
			one_random_char = name_characters.charAt( Math.floor( Math.random() * name_characters.length ) );
			name += one_random_char;
		}
		$(element).attr('value', prefix + name);
	},
	
	/* Format date helper.
		Usage:	openqrm.formatDate([timestamp], '%Y-%M-%d')
		date:	unix timestamp
		fmt:	format string
	*/
	formatDate : function(date, fmt) {
	    function pad(value) {
	        return (value.toString().length < 2) ? '0' + value : value;
	    }
	    return fmt.replace(/%([a-zA-Z])/g, function (_, fmtCode) {
	        switch (fmtCode) {
	        case 'Y':
	            return date.getFullYear();
	        case 'M':
	            return pad(date.getMonth() + 1);
	        case 'd':
	            return pad(date.getDate());
	        case 'H':
	            return pad(date.getHours());
	        case 'm':
	            return pad(date.getMinutes());
	        case 's':
	            return pad(date.getSeconds());
	        default:
	            throw new Error('Unsupported format code: ' + fmtCode);
	        }
	    });
	},
	
	/* Crop text helper. Appends '...' to returned text if it was cropped.
	   text:	the text that should be cropped
	   length:	number of characters
	*/
	crop : function(text, length) {
		return (text.length > length) ? text.substring(0, length) + '...' : text;
	},
	
	
	getEventStatus : function(event_priority) {
		var status;
		switch (parseInt(event_priority)) {
			/*
			case 0: $icon = "off.png"; 	break;
			case 1: $icon = "error.png"; break;
			case 2: $icon = "error.png"; break;
			case 3:	$icon = "error.png"; break;
			case 4:	$icon = "unknown.png"; break;
			case 5:	$icon = "active.png"; break;
			case 6:	$icon = "idle.png"; break;
			case 7:	$icon = "idle.png"; break;
			case 8:	$icon = "idle.png"; break;
			case 9:	$icon = "transition.png"; break;
			case 10:$icon = "active.png"; break;
			*/
			
			case 0: 	
				status = 'disabled';
				break;
			case 1: 
			case 2: 
			case 3:	
				status = 'error';		// error event
				break;
			case 4:	
			case 5:	
			case 6:	
			case 7:	
			case 8:	
				status = 'notice';		// undefined event
				break;
			case 9:	
				status = 'running';		// active event
				break;
			case 10:
				status = 'ok';			// notice event
				break;
			default:
				status = 'undefined';
				break;
			
		}

		return status;
	},

	get_datacenter_status : function() {

		var data = $.ajax({
			url : "api.php?action=base&base=aa_server&controller=datacenter&datacenter_action=dc_status",
			type: "POST",
			cache: false,
			async: false,
			dataType: "text",
			success : function () {}
		}).responseText;
		// TODO: Convert to json
		return data.split(',');
	},
	
	get_datacenter_load : function() {
	
		var data = $.ajax({
			url : "api.php?action=base&base=aa_server&controller=datacenter&datacenter_action=statistics",
			type: "POST",
			cache: false,
			async: false,
			dataType: "json",
			success : function () { }
		}).responseText;
		var response = $.parseJSON(data);
		return (response != null) ? response : false;
	},	

	get_event_list : function() {
		var data = $.ajax({
			url : "api.php?action=base&base=aa_server&controller=datacenter&datacenter_action=eventlist",
			type: "POST",
			cache: false,
			async: false,
			dataType: "json",
			success : function () { }
		}).responseText;
		var response = $.parseJSON(data);
		return (response != null) ? response : false;
	},
	
	get_storage_list : function() {
		var data = $.ajax({
			url : "api.php?action=base&base=aa_server&controller=datacenter&datacenter_action=storage_list",
			type: "POST",
			cache: false,
			async: false,
			dataType: "json",
			success : function () { }
		}).responseText;
		var response = $.parseJSON(data);
		return (response != null) ? response : false;
	},
	
	get_server_list : function() {
	
		var data = $.ajax({
			url : "api.php?action=base&base=aa_server&controller=datacenter&datacenter_action=server_list",
			type: "POST",
			cache: false,
			async: false,
			dataType: "json",
			success : function () { }
		}).responseText;
		var response = $.parseJSON(data);
		return (response != null) ? response : false;
	}

};



/**
	method for resizing iframes depending on their content height
*/
var resizeFrame = {
	set : function() {
		h = '';
		f = document.getElementById('MainFrame');
		if(f.contentDocument && f.contentDocument.body.offsetHeight != "undefined") {
			p = parseFloat(navigator.userAgent.substring(navigator.userAgent.indexOf("Firefox")).split("/")[1])>=0.1? 16 : 0
			h = f.contentDocument.body.offsetHeight+p;
		}
		if(f.Document && f.Document.body.offsetHeight != "undefined") {
			h = f.Document.body.offsetHeight;
		}
		f.height = h +20;
	},
	handlers : function() {
		f = document.getElementById('MainFrame');
		if (window.addEventListener) {
			f.addEventListener("load", resizeFrame.set, false);
		}
		else if (window.attachEvent) {
			f.attachEvent("onload", resizeFrame.set);
		}
		else {
			f.onload = resizeFrame.set;
		}
	}
};
if(document.getElementById('MainFrame')) {
	resizeFrame.handlers();
}



function Logout(element) {
	path = window.location.protocol+"//dummy:dummy@"+window.location.host+""+window.location.pathname+'?'+Math.random()*11;
	try{
		var agt=navigator.userAgent.toLowerCase();
		if (agt.indexOf("msie") != -1) {
			// IE clear HTTP Authentication
			document.execCommand("ClearAuthenticationCache");
		} else {
			var data = $.ajax({
				url : path,
				type: "POST",
				cache: false,
				async: false,
				dataType: "text",
				success : function () { }
			}).responseText;
		}
		window.location.href = element.href;
	} catch(e) { alert(e); }
}


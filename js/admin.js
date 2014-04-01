/*
jQuery(document).ready(function($) {
	$('.uci-curl #get-race-data').click(function(e) {
		e.preventDefault();
		
	});
});
*/
/* our "modal" -- loading animation */
$=jQuery.noConflict();
$body = $("body");

$(document).on({
    ajaxStart: function() { $body.addClass("loading");    },
     ajaxStop: function() { $body.removeClass("loading"); }    
});
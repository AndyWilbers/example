/*	_assets/js/script.js*/
	var debug	= "off"; // in case "on": debug messeing on browsers sonsole.
	var show	= "off"; // in case "on": local data is shown on browsers sonsole.
	var no_redirect = "on";
	var tan 	= 200;	// UI animation time, for example to use for slide.
	
	
	
//	Catch vertial scroll position:	
	$( document ).on( "scroll", function(e){ 
	    var pos = $('html').scrollTop();
	    $('[data-pos][href]').each (function(){
	       var href = $(this).attr('href')+'&pos='+pos;
	       $(this).attr('href',href);
	       return;
	    });
           return;   
	} );

	
//	Javascript to include:
	// @koala-prepend "_extentions.js"
	// @koala-prepend "_gen.js"
	// @koala-prepend "_dragg.js"
	// @koala-prepend "_requests.js"
	// @koala-prepend "_ce.js"
	// @koala-prepend "_sa.js"
        // @koala-prepend "_ui.js"
	// @koala-prepend "_observation.js"
	// @koala-prepend "_calculation.js"
	// @koala-prepend "_fe.js"
	// @koala-prepend "_user.js"
	// @koala-prepend "_geo.js"

	
//	Scroll to last postion:
	var pos = gen.getUrlParameter(window.location.href,'pos',0);
	window.scrollTo(0,pos);

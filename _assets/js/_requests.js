// js/_requests.js

$(document).ready(function() {
  
	    
 //	Bind for Anchors for AJAX calls:
	$( document ).on( "click", '[href*="ajax/"]', function(e){ 
	    e.preventDefault();
	    var pieces = $(this).attr('href').split('/');
	    var request = 'requests.'+pieces.pop();
	    gen.runFunction(request,$(this));
	} );
	
	$( document ).on( "click", '[data-sa*="ajax:"]', function(e){ 
	    e.preventDefault();
	    var data = sa.dataSA($(this).attr('data-sa'));
	    gen.runFunction(data.ajax,$(this));
	} );
	    	
	return;
		
});
	
	
var requests = {
	

//	Toggle image size:	
	imagesize: function($obj){
	    var ajax = new gen.ajax('');
	    var status = $obj.hasClass('lr')? 'lr' :'normal';
	    
	    ajax.post({ url: $obj.attr('href'), data:{status:status}, callback:'requests.imagesize_callback'});
	    return;
	},
	imagesize_callback: function(data){
	    if (data.status === "lr") {
		$('#btn-imagsize').addClass('lr');
	    } else {
		$('#btn-imagsize').removeClass('lr');
	    }
	    var title = $('#btn-imagsize').attr('title');
	    $('#btn-imagsize').attr('title',$('#btn-imagsize').attr('data-title'));
	    $('#btn-imagsize').attr('data-title',title);
	    return;
	},
	
//	Toggle device:	
	device: function($obj){
	    var ajax = new gen.ajax('');
	    var status = $obj.hasClass('mobile')? 'mobile' :'desktop';
	    
	    ajax.post({ url: $obj.attr('href'), data:{status:status}, callback:'requests.device_callback'});
	    return;
	},
	device_callback: function(data){
	    window.console.log( 'calback');
	    window.console.log(JSON.stringify(data));
	    if (data.status === 'mobile') {
		$('#btn-device').addClass('mobile');
	    } else {
		$('#btn-device').removeClass('mobile');
	    }
	    var title = $('#btn-device').attr('title');
	    $('#btn-device').attr('title',$('#btn-device').attr('data-title'));
	    $('#btn-device').attr('data-title',title);
	    $('[data-sa*="show:device;"]').toggleClass('hide');
	    return;
	},	
    
};
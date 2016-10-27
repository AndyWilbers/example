//	_assets/js/_ui.js

//	Handling script attribute
	$(document).ready(function() {
	    
	 
	 
    	   //	Bind show and hide menu: 
    	    	$( document ).on( "click", '#btn-showmenu', ui.show_menu );
    	    	
    	   //   Popup links:
    	        $( document ).on( "click", ' a[data-link-id*="reference"], a[data-link-id*="note"]', ui.show_popup );
    	    
    	    //	Load image in normal resolution:  
    	        $( document ).on( "click", 'img[data-image-id][src$=".png"]', ui.load_img_hr );
    	      
    	   //	Open/close active article:     
    	        $( document ).on( "click", '[data-sa*="action:ui.toggle_article;id:"]', ui.toggle_article );
    	        
    	   //	Show/ hide settings (on report, observation and calculation pages): 
    	       $( document ).on( "click", '#btn-settings', ui.toggle_settings);
    	    
		return;
	});


var ui = {
	
		
	show_menu: function(e){
	    e.preventDefault();
	    var title = $(this).attr('title');
	    $(this).attr('title', $(this).attr('data-title'));
	    $(this).attr('data-title', title );
	    $(this).toggleClass('close');
	    $('#menu-header').toggleClass("show");
	    return;
	},
	
	show_popup: function(e){
	    e.preventDefault();
	    alert($(this).attr('title'));
	    return;
	},
	
	load_img_hr: function(e){
	    window.console.log('img');
	    var src= $(this).attr('src');
	    window.console.log(src);
	    src= src.replace(".png", ".jpeg"); 
	    window.console.log('to:'+src);
	    $(this).attr('src', src);
	    $(this).removeAttr('title');
	    return;
	},
	
	toggle_article: function(e){
	    
	    var data = sa.data($(this).attr('data-sa') );
	    var id   = data.id;
	    
	    $(this).toggleClass('open');
	    
	    $('[data-sa="hide_on:'+id+';"]').toggleClass('hide');
	    return;
	   
	    
	},
	
	toggle_settings: function(e){
	    e.preventDefault();
	    $('[data-sa="settings"]').slideToggle(tan);
	    return;
	}

	
}

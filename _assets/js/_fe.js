//	_assets/js/_fe.js

//	Handling script attribute
	$(document).ready(function() {
	
	 
	 
    	   //	Bindings for favorite articles: 
    	    	$( document ).on( "click", '[data-fe*="add_to_favorite:"]', fe.add_to_favorite );
    	        $( document ).on( "click", '[data-fe="remove_all_favorites"]', fe.remove_all_favorites );
    	        $( document ).on( "click", '[data-fe*="remove_from_favorite:"]', fe.remove_from_favorite );
    	        $( document ).on( "click", '#save_favorites', fe.save_favorites );
    	        $( document ).on( "change", '#report_set', fe.change_favorites );
    	        
    	        $( document ).on( "click", '#save_oberservation_set', fe.save_observation_set );
	        $( document ).on( "change", '#observation_set', fe.change_observation_set );
	        
	        
    	    //	Datapicker 
	        if (  $( 'input.date').size()>0){
	            $( 'input.date').datepicker({dateFormat: "yy-mm-dd"});
	        }
		return;
	});


var fe = {
	
	add_to_favorite: function(e){
	    e.preventDefault();
	    e.stopPropagation();
	   
	    var data= {};
	    
	//  Get ID:
	    var data_fe = sa.data($(this).attr('data-fe'));
	    data.ID = data_fe.add_to_favorite;
	 
	    var ajax 	= new gen.ajax('');
	    var sa_html = sa.dataSA($('html').eq(0).attr('data-sa'));
	    var url =  sa_html['application'] == "VBNE"?  'ajax/favorite_add':  sa_html['application']+'/ajax/favorite_add';
	    ajax.post({ url:url, data:data, callback:'fe.favorite_added'});
	    return;
	  
	},
	
	favorite_added: function(data){
	 
	    if (debug=="on"){
		 window.console.info( 'fe.favorite_added');
		 window.console.log( JSON.stringify(data));
	    }
	    if (data.meta.error < 0) {
		if (debug=="on"){  window.console.log( JSON.stringify(data));}
		return;
	    }
	    
	    $('[data-counter="favorites"]').text(data.count).removeClass('hide');
	    return;
	},
	
	remove_all_favorites: function(e){
	    e.preventDefault();
	    e.stopPropagation();
	   
	    var data= {};
	    
	//  Get ID:
	    var data_fe = sa.data($(this).attr('data-fe'));
	    data.ID = data_fe.add_to_favorite;
	 
	    var ajax 	= new gen.ajax('');
	    var sa_html = sa.dataSA($('html').eq(0).attr('data-sa'));
	    var url =  sa_html['application'] == "VBNE"?  'ajax/favorite_remove_all':  sa_html['application']+'/ajax/favorite_remove_all';
	    ajax.post({ url:url, data:data, callback:'fe.favorite_all_removed'});
	    return;
	  
	},
	
	favorite_all_removed: function(data){
	 
	    if (debug=="on"){
		 window.console.info( 'fe.favorite_added');
		 window.console.log( JSON.stringify(data));
	    }
	    if (data.meta.error < 0) {
		if (debug=="on"){  window.console.log( JSON.stringify(data));}
		return;
	    }
	    
	    $('[data-counter="favorites"]').text(0).addClass('hide');
	    $('#container-favorites').remove();
	    return;
	},
	
	remove_from_favorite: function(e){
	    e.preventDefault();
	    e.stopPropagation();
	   
	    var data= {};
	    
	//  Get ID:
	    var data_fe = sa.data($(this).attr('data-fe'));
	    data.ID = data_fe.remove_from_favorite;
	 
	    var ajax 	= new gen.ajax('');
	    var sa_html = sa.dataSA($('html').eq(0).attr('data-sa'));
	    var url =  sa_html['application'] == "VBNE"?  'ajax/favorite_remove':  sa_html['application']+'/ajax/favorite_remove';
	    ajax.post({ url:url, data:data, callback:'fe.favorite_removed'});
	    return;
	  
	},
	
	favorite_removed: function(data){
		 
	    if (debug=="on"){
		 window.console.info( 'fe.favorite_removed');
		 window.console.log( JSON.stringify(data));
	    }
	    if (data.meta.error < 0) {
		if (debug=="on"){  window.console.log( JSON.stringify(data));}
		return;
	    }
	    
	    location.reload(); 
	  
	    return;
	},
	
	save_favorites: function(e){
	    e.preventDefault();
	    
	    window.console.info('save_favorites');
	    var sa_html = sa.dataSA($('html').eq(0).attr('data-sa'));
	    if ( sa_html['application'] == "VBNE") {return;}
	    
	 // Read ID and name of current set of favorites:
	    var $current_selection = $('[data-sa*="current_selection:"]');
	    
	    var data_sa = sa.data($current_selection.attr('data-sa'));
	    var ID 	= parseInt(data_sa.current_selection,10);
	    
	    var data     = {};
	        data.ID  = ID;
	        
	    var name =  ID == -1?  '': $current_selection.text();
	    
	 // Ask for a name of new name:
	    data.name = window.prompt('Geef een naam of voor deze set artikelen',name);
	    
	 // In case name is changed: make a new set, so ID= -1;
	    data.ID   = data.name != name? -1: data.ID
	    
	    var ajax = new gen.ajax('');
	    var url =  sa_html['application']+'/ajax/report_set_save';
	    ajax.post({ url:url, data:data, callback:'fe.favorites_saved'});
	    return;
	    
	},
	
	favorites_saved: function(data){
	  
	    if (data.meta.error < 0) {
		if (debug=="on"){  window.console.log( JSON.stringify(data));}
		alert(data.meta.msg);
		return;
	    }
	    
	    location.reload(); 
	    return;
	},
	
	change_favorites: function(e){
	    
	    window.console.info('change_favorites');
	    
	    var sa_html = sa.dataSA($('html').eq(0).attr('data-sa'));
	    if ( sa_html['application'] == "VBNE") {return;}
	    
         // Read ID and name of current set of favorites:
	    var $report_set = $('#report_set');
	    var ID 	= parseInt($report_set.val(),10);
	    var name 	= ID>0? $report_set.find('option[value="'+ID+'"]').text(): '';
	    
	    window.console.log('ID: '+ID);
	    window.console.log('name: '+name);
	    if (ID <1) { return;}
	    
	    var $current_selection = $('[data-sa*="current_selection:"]');
	    var data_sa = sa.data($current_selection.attr('data-sa'));
	    if (ID == parseInt(data_sa.current_selection,10) ){ return;}
	    
	    if (window.confirm('Bij een klik op "OK" wordt de huidige selectie overschreven.') ){
		
		var data = {};
		data.ID =ID;
		var ajax 	= new gen.ajax('');
		var url =  sa_html['application']+'/ajax/report_set_change';
		ajax.post({ url:url, data:data, callback:'fe.favorites_changed'});
		return;
	       
	   }
	     

	   return;
	    
	},
	
	favorites_changed: function(data){
	    if (data.meta.error < 0) {
		if (debug=="on"){  window.console.log( JSON.stringify(data));}
		return;
	    }
	
	  location.reload(); 
	    
	    return;
	},
	
	save_observation_set: function(e){
	    e.preventDefault();
	    window.console.info('save_observation_set');
	    var sa_html = sa.dataSA($('html').eq(0).attr('data-sa'));
	    if ( sa_html['application'] == "VBNE") {return;}
	    
	 // Read ID and name of current set of favorites:
	    var $current_selection = $('[data-sa*="current_selection:"]');
	    
	    var data_sa = sa.data($current_selection.attr('data-sa'));
	    var ID 	= parseInt(data_sa.current_selection,10);
	    
	    var data     = {};
	        data.ID  = ID;
	        
	    var name =  ID == -1?  '': $current_selection.text();
	    
	 // Ask for a name or new name:
	    data.name = window.prompt('Geef een naam of voor deze set observaties',name);
	    
	 // In case name is changed: make a new set, so ID= -1;
	    data.ID   = data.name != name? -1: data.ID;
	    
	    var ajax = new gen.ajax('');
	    var url =  sa_html['application']+'/ajax/observation_set_save';
	    ajax.post({ url:url, data:data, callback:'fe.obsevation_set_saved'});
	    return;
	},
	
	obsevation_set_saved: function(data){
	    window.console.info('obsevation_set_saved');
	    if (data.meta.error < 0) {
		if (debug=="on"){  window.console.log( JSON.stringify(data));}
		alert(data.meta.msg);
		return;
	    }
	
	    location.reload(); 
	  
	    return;
	},
	
	change_observation_set:function(e){
	    
	    window.console.info('change_observation_set');
	    
	    var sa_html = sa.dataSA($('html').eq(0).attr('data-sa'));
	    if ( sa_html['application'] == "VBNE") {return;}
	    
         // Read ID and name of current set of favorites:
	    var $set = $('#observation_set');
	    var ID 	= parseInt($set.val(),10);
	    var name 	= ID>0? $set.find('option[value="'+ID+'"]').text(): '';
	    
	    window.console.log('ID: '+ID);
	    window.console.log('name: '+name);
	    if (ID <1) { return;}
	    
	    var $current_selection = $('[data-sa*="current_selection:"]');
	    var data_sa = sa.data($current_selection.attr('data-sa'));
	    if (ID == parseInt(data_sa.current_selection,10) ){ return;}
	    
	    if (window.confirm('Bij een klik op "OK" wordt de huidige set observaties overschreven.') ){
		
		var data = {};
		data.ID =ID;
		var ajax 	= new gen.ajax('');
		var url =  sa_html['application']+'/ajax/observation_set_change';
		ajax.post({ url:url, data:data, callback:'fe.observation_set_changed'});
		return;
	       
	   }
	     
	   return;
	    
	},
	
	observation_set_changed: function(data){
	    if (data.meta.error < 0) {
		if (debug=="on"){  window.console.log( JSON.stringify(data));}
		
		return;
	    }
	   
	    location.reload(); 
	   
	    return; 
	},
	
	toggle_view:function(data){
	    window.console.info('fe.toggle_view');
	   
	    var $me = data.me;
	    $me.toggleClass('open').toggleClass('closed');
	    
	    var label = data.label;
	    window.console.info('label: '+label);
	    
	    window.console.info($('[data-sa="'+label+'"]').size());
	    
	    var $content =  $('[data-sa="'+label+'"]');
	    $content.each(function(){
		if ($(this).hasClass('hide')){
		    $(this).slideUp(0,function(){
			$(this).removeClass('hide');
			$(this).slideDown(tan);
			return;
		    });
		} else {
		    $(this).slideToggle(tan);
		}
		return;
	    });
	    return;
	}
	
};

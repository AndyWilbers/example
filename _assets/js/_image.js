//	_assets/js/image.js

//	Document ready script:
	$(document).ready(function() {
	    
	//  click on thumb (on images form:
	    $( document ).on( "click", '#images .thumb', function(e){
		
	     
		var $tumbs 	= $('#image_selector .thumb');
		var indx 	= $tumbs.index($(this));
		var data_shift 	= "#none";
		if (e.shiftKey) {
		 
		    if ($('#image_selector').attr('data-shift') !="#none") {
    		 	data_shift = parseInt( $('#image_selector').attr('data-shift'),10); 
    		 	var from   = indx < data_shift?  indx : data_shift;
    		 	var to     = indx >= data_shift? indx : data_shift;
    		 	    to++;
    		 	if (e.shiftKey) {	
    		 	    $tumbs.slice(from,to).addClass('selected');
    		 	}
    		 	$('#image_selector').attr('data-shift',indx) ;
    		 	return;
		    }
		    $(this).addClass('selected');
		    $('#image_selector').attr('data-shift',indx) ;
		    return;
		}
		$('#image_selector').attr('data-shift',"#none") ;
	     
		if (e.altKey) {
	      	     $(this).toggleClass('selected');
	      	     return;
		}
		      		
		$(this).toggleClass('selected').siblings().removeClass('selected');
		return;
		
	    });
		
	    $( document ).on( "click", '#toggle_view', function(e){
		
		
		
		 var data ={};
		 data.type = $('#image_selector_view').attr('data-type');
		 
	    	 var ajax = new gen.ajax('');
	    	 var htmlSA = sa.dataSA($('html').attr('data-sa'));
		 var url = htmlSA['application'] == "VBNE"?  'image/ajax/toggle_view': htmlSA['application']+'/image/ajax/toggle_view';
	    	 ajax.get({ url:url, data:  data , callback:'image.toggle_view' });
	    	 return;
		
	    });
	    
	    $( document ).on( "click", '#save_alt', function(e){
		
		
	    //	Get changes:
		var $changes = $('#image_selector_view').find('input[name^="ID"].changed');
		
		
	     //  Stop in case there are no changes:
		 if ($changes.size() == 0 )  {return;}
		
	      // Read changed values:
		 var records ={};
		 $changes.each( function(){
		     var name = $(this).attr('name');
		     var text = $(this).val();
		     records[name] = text;
		 });
		 
		
	      // Send POST: 
		 var data = {};
		     data.records = JSON.stringify(records);
	    	 var ajax = new gen.ajax('');
	    	 var htmlSA = sa.dataSA($('html').attr('data-sa'));
		 var url = htmlSA['application'] == "VBNE"?  'image/ajax/save_alt': htmlSA['application']+'/image/ajax/save_alt';
	    	 ajax.post({ url:url, data:  data , callback:'image.save_alt' });
	    	 return;
		
	    });
	    
	 
	    $( document ).on( "click", '#popup_dir_new .btn', function(e){
		
		
		var name = $('#popup_dir_new').find('input[name="name"]').val();
		
	
	      // POST: 
		 var data = {};
		     data.name = name;
	    	 var ajax = new gen.ajax('');
	    	 var htmlSA = sa.dataSA($('html').attr('data-sa'));
		 var url = htmlSA['application'] == "VBNE"?  'image/ajax/new_dir': htmlSA['application']+'/image/ajax/new_dir';
	    	 ajax.post({ url:url, data:  data , callback:'image.new_dir' });
	    	 return;
		
		
	    });
	    
	    $( document ).on( "click", '#popup_image_move .btn', function(e){
		
		var dir = $('#popup_image_move').find('option[selected="selected"]').attr('value');
	
		
		var $images = $('#image_selector_view').find('[data-id].thumb.selected');
		if ($images.size() <1) {
		    $('#msg').html('<p>Er zijn geen afbeeldingen geselecteerd.</p>');
		    $('#popup').addClass('ui-closed');
		    return;
		}
		
		var records = [];
		$images.each( function(){
		    records.push($(this).attr('data-id'));
		    $(this).addClass('hide');
		});
	
	      // POST: 
		 var data = {};
		     data.dir = dir;
		     data.records = JSON.stringify(records);
		    
	    	 var ajax = new gen.ajax('');
	    	 var htmlSA = sa.dataSA($('html').attr('data-sa'));
		 var url = htmlSA['application'] == "VBNE"?  'image/ajax/move': htmlSA['application']+'/image/ajax/move';
	    	 ajax.post({ url:url, data:  data , callback:'image.move' });
	    	 return;
		
		
	    });
	   
	   
		
	});
	



var image = {
	
	
	
	
	toggle_view: function(data){
	    
	    if (parseInt(data.meta.error,10) < 0) {
		    if (debug == "on") {	window.console.error(JSON.stringify(data));}
		    return;
	    }
	    
	    var type = data.type;
	    
	    $('#image_selector_view').html(data.html).attr('data-type', data.type).attr('data-shift', '#none');
	    
	    form.data.load_named_objects($('#images'));
	   
	    var text = $('#toggle_view').text();
	    var title = $('#toggle_view').attr('title');
	    $('#toggle_view').text($('#toggle_view').attr('data-text'));
	    $('#toggle_view').attr('title', $('#toggle_view').attr('data-title'));
	    $('#toggle_view').attr('data-text', text);
	    $('#toggle_view').attr('data-title', title);
	    if (type == 'thumbs' ) {
		$('#save_alt').addClass('hidden');
	    } else {
		$('#save_alt').removeClass('hidden');
	    }
	    return;
	    
	},
	
	save_alt: function(data){
	    
	    if (parseInt(data.meta.error,10) < 0) {
		 if (debug == "on") {	window.console.error(JSON.stringify(data));}
		 $('#msg').html(data.meta.msg).addClass('warning');
		 return;
	    }
	    
	 // Update fields and remove stored data:
	    $.each(data.records ,function(key,val){
		  $('#image_selector_view').find('input[name="'+key+'"]').val(val).removeClass('changed');
		  form.data.unset($('#images'),key);
	    });
	    $('#msg').html(data.meta.msg).removeClass('warning');
	    
	    return;
	},
	
	new_dir: function(data){
	    
	// Catch error:    
	   if (parseInt(data.meta.error,10) < 0) {
	      if (debug == "on") { window.console.error(JSON.stringify(data));}
	      $('#msg').html(data.meta.msg).addClass('warning');
	      return;
	   }
	   
	 // Re-load page to new directory:
	    document.location.reload(true);
	},
	
	move: function(data){
	    
	// Catch error:    
	   if (parseInt(data.meta.error,10) < 0) {
		      if (debug == "on") { window.console.error(JSON.stringify(data));}
		      $('#msg').html(data.meta.msg).addClass('warning');
		      return;
            }
	  
	// Re-load page:
           document.location.reload(true);
	   }


};

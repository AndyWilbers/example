//	_assets/js/_user.js
	$(document).ready(function() {
	    

	
	    $( document ).on( "click", '#new-account', user.new_account);
	    $( document ).on( "click", '#password-reset', user.password_reset);
	    
	    
	    $( document ).on( "click", '[data-ce*="name:showName;"]', user.toggle_show_name);
	    $( document ).on( "taphold", '[data-ce*="name:showName;"]', user.toggle_show_name);
	    $( document ).on( "click", '[data-ce*="name:shareObservations;"]', user.toggle_share_observations);
	    $( document ).on( "taphold", '[data-ce*="name:shareObservations;"]', user.toggle_share_observations);
	    
	    $( document ).on( "click", '#delete', user.delete_account);
	    
	    $( document ).on( "submit", '[action="save_my_observation"]', user.save_my_observation);
	    $( document ).on( "submit", '[action="save_my_report"]', user.save_my_report);
	    
	    $( document ).on( "click", '[data-sa*="remove"]', user.remove);
    
	});


var user = {
	
	my_account_save_callback: function(data){
	    
	     window.console.info('user.my_account_save_callback');
	     window.console.info(JSON.stringify(data));
	     
	    //  Get form object:
	    	var $form = $('#'+data.inputData.form_id);
	    	
	    	  window.console.info($form.size());
	        if (data.meta.error < 0) {
	            $form.find('.msg').addClass('warning');
	            if (data.hasOwnProperty('field_warning')){
		         $form.find('[name="'+data.field_warning+'"]').addClass('warning');
		    }
	        } else {
	            form.data.remove($form);
	            $form.find('.changed').removeClass('changed');
	        }
	        $form.find('.msg').text(data.meta.msg);
	        $form.find('.msg').slideDown(tan); 
	 	return;
	 	
	},
	
	new_account: function(e){
	    e.preventDefault();
	    window.console.info('new-account');
	    
	    $('#login').attr('action','registatie');
	    $('#login').trigger('submit');
	    return;
	    
	},
	
	delete_account: function(e){
	  
	    e.preventDefault();
	    window.console.info('delete');
	    var check = confirm('Verwijderen van het account kan niet ongedaan worden, door op OK te klikken vewijdert u uw account definitief.');
	    if (check == false) {
		return;
	    }
	    var action = $(this).attr('href');
	    $('#my-account-ar').attr('action', action);
	    $('#my-account-ar').trigger('submit');
	    return;
	    
	},

	password_reset: function(e){
	    e.preventDefault();
	    window.console.info('password-reset');
	    $('#login').attr('action','password_reset_request');
	    $('#login').trigger('submit');
	    return;
	},
	
	toggle_show_name: function(){
	    var val = $(this).hasClass('checked')? 1 : -1;
	    $('#showName').val(val);
	    return;
	},
	
	toggle_share_observations: function(){
	    var val = $(this).hasClass('checked')? 1 : -1;
	    $('#shareObservations').val(val);
	    return;
	},
	
	save_my_observation: function(e){
	    e.preventDefault();
	    var  data = {};
	    
	    data.name = $('#name').val();
	    window.console.info('data.name: '+data.name);
	    data.ID = $('#ID').val();
	    window.console.info('data.ID: '+data.ID);
	    data.date = $('#date').val();
	    window.console.info('data.date: '+data.date);
	    var viewers= new Array();
	    if ($('[data-ce*="name:user;"].checked').size()>0){
    	    	$('[data-ce*="name:user;"].checked').each(function(){
    	    	    var ce = sa.data($(this).attr('data-ce'));
    	    	    viewers.push(ce.ID);
    	    	});
    	    	data.viewers = JSON.stringify(viewers);
    	        window.console.info('data.viewers: '+data.viewers);
    	    	
	    }
	    
	    var ajax = new gen.ajax('');
	    var sa_html = sa.dataSA($('html').eq(0).attr('data-sa'));
	    if (sa_html['application'] == "VBNE") {
		alert('Deze functie kan niet worden uitgevoerd.');
		return;
	    }
	    var url =    sa_html['application']+'/ajax/observation_set_save_meta';
	    
	    ajax.post({ url: url, data:data, callback:'user.observation_set_save_meta_callback'});
	    return;
	    
	   
	    
	},
	
	observation_set_save_meta_callback: function(data){
	    
	 // Catch error:    
            if (parseInt(data.meta.error,10) < 0) {
		 if (debug == "on") { window.console.error(JSON.stringify(data));}
		 $('#msg').html(data.meta.msg).addClass('warning');
		 return;
	    }
         
         // Re-direct page:
	    var htmlSA = sa.dataSA($('html').attr('data-sa'));
	    url =  htmlSA['path']+htmlSA['application']+'/my_observations';
            location.assign(url);
	    return;
	},
	
	save_my_report: function(e){
	    e.preventDefault();
	    
	    var  data = {};
	    
	    data.name = $('#name').val();
	 
	    data.ID = $('#ID').val();
	   
	  
	    var viewers= new Array();
	    if ($('[data-ce*="name:user;"].checked').size()>0){
    	    	$('[data-ce*="name:user;"].checked').each(function(){
    	    	    var ce = sa.data($(this).attr('data-ce'));
    	    	    viewers.push(ce.ID);
    	    	});
    	    	data.viewers = JSON.stringify(viewers);
    	        window.console.info('data.viewers: '+data.viewers);
    	    	
	    }
	  
	    
	    var ajax = new gen.ajax('');
	    var sa_html = sa.dataSA($('html').eq(0).attr('data-sa'));
	    if (sa_html['application'] == "VBNE") {
		alert('Deze functie kan niet worden uitgevoerd.');
		return;
	    }
	    var url =    sa_html['application']+'/ajax/report_set_save_meta';
	    
	    ajax.post({ url: url, data:data, callback:'user.report_set_save_meta_callback'});
	 
	    return;
	    
	},
	
	report_set_save_meta_callback: function(data){
	    
        // Catch error:    
	   if (parseInt(data.meta.error,10) < 0) {
		if (debug == "on") { window.console.error(JSON.stringify(data));}
		$('#msg').html(data.meta.msg).addClass('warning');
		return;
	   }
	         
        // Re-direct page:
	   var htmlSA = sa.dataSA($('html').attr('data-sa'));
	       url =  htmlSA['path']+htmlSA['application']+'/my_reports';
	       location.assign(url);
	       return;
	   },

	remove: function(e){
	    e.preventDefault();
	    
	    var check = confirm('Verwijderen kan niet ongedaan worden, door op OK te klikken vewijdert u definitief.');
	    if (check == false) {
		return;
	    }
	    
	    var data = {};
	    var $sa  = sa.data($(this).attr('data-sa'));
	    
	    data.ID = $sa.id;
	    data.ext = $sa.remove;
	    
	    var ajax = new gen.ajax('');
	    var sa_html = sa.dataSA($('html').eq(0).attr('data-sa'));
	 
	  
	    var url =    sa_html['application']+'/ajax/remove_set';
	    ajax.post({ url: url, data:data, callback:'user.remove_callback'});
	},
	
	remove_callback:function(data){
	    
	// Catch error:    
           if (parseInt(data.meta.error,10) < 0) {
		if (debug == "on") { window.console.error(JSON.stringify(data));}
		$('#msg').html(data.meta.msg).addClass('warning');
		return;
	    }
           
       
		         
	 // Re-direct page:
	    var htmlSA = sa.dataSA($('html').attr('data-sa'));
		url =  htmlSA['path']+htmlSA['application']+'/'+data.page;
		location.assign(url);
		return;
           
	    
	}

};

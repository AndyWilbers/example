//	_assets/js/_admin.js

$(document).ready(function() {
	
//  UI bindings:
    $( document ).on( "click", '#btn-help', admin.toggle_help);
//    $( document ).on( "click", '[data-sa*="toggle_by_id:"]', admin.toggle_by_id);
    
    
    $( document ).on( "change", 'select[name="FID_ARTICLE_CAT"]', admin.reset_fid_article);
   
    $( document ).on( "click", '.btn.slide[data-sa]', function(e){
	e.preventDefault();
	var data_sa = $(this).attr('data-sa');
	$('.slide-container[data-sa="'+data_sa +'"]').slideToggle(tan);
	var title = $(this).attr('title');
	$(this).attr('title', $(this).attr('data-title'));
	$(this).attr('data-title', title);
	$(this).toggleClass('up');
	return;
    });
    
//  Open a popup 
    $( document ).on( "click", '[data-popup]', function(e){
	
	window.console.info($(this).attr('data-popup'));
	
    //	Get popup obejct to open:
	var $popup =  $('#'+$(this).attr('data-popup'));
	if ( parseInt($popup.size(),10) != 1 ) { return;}
	
    //  Open the popup:   
	$popup.removeClass('ui-closed').siblings('[id]').addClass('ui-closed');
	$('#popup').removeClass('ui-closed');
	return;
	    
    });
	
    
//  Close popup elelements   
    $( document ).on( "click", '#popup>.btn.close', function(e){
	e.preventDefault();
	$('#popup').addClass('ui-closed');
	return
    });
    
    $( document ).on( "change", '#popup select', function(e){
	var $option_selected = $(this).find('option[value="'+$(this).val()+'"]');
	$option_selected.attr('selected',"selected").siblings('option').removeAttr('selected');
	return;
    });	
    
//  Image selector:
    
//  select other category thums
    $( document ).on( "click", '#image_selector  a', function(e){
	 e.preventDefault();
	
	 var ajax = new gen.ajax(''); 
	 var data = {};
	 data['dir']  = $(this).attr('href');
	 var htmlSA = sa.dataSA($('html').attr('data-sa'));
	     data.image_path = htmlSA['path'];
	 var url = htmlSA['application'] == "VBNE"?  'image/ajax/get_selector': htmlSA['application']+'/image/ajax/get_selector';
	
	 ajax.get({ url: url, data:data, callback:'admin.update_image_selector'});
	
    });
    $( document ).on( "change", '#image_selector *', function(e){
	$('#msg').removeClass('warning').html('');
	return;
    });
    
    $( document ).on( "click", '#image_selector *', function(e){
	$('#msg').removeClass('warning').html('');
	return;
    });
    return;
});


var admin = {
	
	toggle_publish:function(data){
	    
	 
	    if (debug === "on"){
    	    	window.console.info('admin.toggle_publish data:');
    	    	window.console.info(JSON.stringify(data));
	    }
	    
	    
	   //	Toggle publish:
		var ajax = new gen.ajax('');
		ajax.post({ url:'ajax/toggle_publish', data: data, callback:'admin.toggle_publish_callback' });
		return;
	  
	},
	
	toggle_publish_callback: function(data)	{
	    
	    if (debug === "on"){
    	    	window.console.info('admin.toggle_publish_callback:');
    	    	window.console.info(JSON.stringify(data));
	    }
	    if (data.meta.error == 0) {
		
		var onClick 	= data.inputData.onClick;
		var name 	= data.inputData.name;
		var id 		= data.inputData.id;
		
		$('[data-sa*="onClick:'+onClick+';"][data-sa*="name:'+name+';"][data-sa*="id:'+id+';"]').toggleClass('not');
		
		
	    }
	    
	    return;
	},
	
	toggle_help:function(e){
	    e.preventDefault();
	    var $me =$(this);
	    $('#help').slideToggle(tan ,function(){
		 var current_title = $me.attr('title');
		 $me.attr('title',$me.attr('data-title'));
		 $me.attr('data-title',current_title);
	    });
	},	
	
	toggle_by_id:function(e){
	    e.preventDefault();
	    var $me =$(this);
	    var dataSA= sa.dataSA($me.attr('data-sa'));
	    $('#'+dataSA['toggle_by_id']).slideToggle(tan ,function(){
		 var current_title = $me.attr('title');
		 var current_label = $me.text();
		 $me.text($me.attr('data-label'));
		 $me.attr('data-label',current_label);
		 $me.attr('title',$me.attr('data-title'));
		 $me.attr('data-title',current_title);
	    });
	},
	
	reset_fid_article:function(e){
	
	   
	       var ajax = new gen.ajax('');
	    
	       var htmlSA = sa.dataSA($('html').attr('data-sa'));
	       var data = {};
	       data['FID_ARTICLE_CAT']  = $(this).val();
	       var url = htmlSA['application'] == "VBNE"?  'article/options': htmlSA['application']+'/article/options';
	       ajax.get({ url: url, data:data, callback:'admin.update_article_options'});
	   
	   return;
	},
	
	update_article_options: function(data) {
	    window.console.info('update_article_options');
	    
	    if (parseInt(data.meta.error,10) < 0) {
	    	if (debug == "on") {
	    		window.console.error(JSON.stringify(data));
	    	}
	    }else{
		
		
		$('[data-sa="FID_ARTICLE_CAT"]').html(data.select);
		$('select[name="FID_ARTICLE"]').val(-1).trigger('change');
		
		if (data.select == "") {
		    $('[data-sa="FID_ARTICLE_CAT"]').addClass('ui-closed');
		}else {
		    $('[data-sa="FID_ARTICLE_CAT"]').removeClass('ui-closed');
		}
	    }
	    return;
	},
	
	update_record_selector:function(data){
	    
	   if (! data.hasOwnProperty('name') ) {
	     if (debug == "on") {  window.console.error('update_record_selector: data-sa[name] is not defined.');}
	   }
	   
	   var $me = data.me;
	   var FID_CAT = $me.val();
	   window.console.log('update_record_selector:');
	
	   window.console.info('name: '+ data.name);
	  window.console.info('FID_CAT: '+ FID_CAT);
	  
	  var ajax = new gen.ajax('');
	  var htmlSA = sa.dataSA($('html').attr('data-sa'));
	  var request = {};
	      request.FID_CAT = FID_CAT;
	      request.exclude = $('#record').attr('data-exclude');
	      request.ID = $('input[name="ID"]').val();
	      request.title = $('#record>option').eq(0).text();
	
	  var url = htmlSA['application'] == "VBNE"?  'admin/ajax/'+data.name+'/get_record_selector' : htmlSA['application']+'/admin/ajax/'+data.name+'/get_record_selector';
	       ajax.get({ url: url, data:request, callback:'admin.get_record_selector_callback'});
	  
	   return; 
	},
	get_record_selector_callback: function(data){
		if (parseInt(data.meta.error,10) < 0) {
	    	if (debug == "on") {	window.console.error(JSON.stringify(data));}
	    	return;
	    }
		$('#record').html(data.html);
	    return;
	},
	
	update_image_selector: function(data){
		if (parseInt(data.meta.error,10) < 0) {
		    if (debug == "on") {	window.console.error(JSON.stringify(data));}
		    return;
		}
		$('#image_selector').html(data.html);
		form.data.load_named_objects($('#images'));
		
		$('#popup_image_move select').val(data.dir);
		$('#popup_image_move select option').removeAttr('selected');
		$('#popup_image_move select option[value="'+data.dir+'"]').attr('selected','selected');
		return;
	    
	}
	
};

//	_assets/js/admin.observation.js



var observation = {
	
	setup: function() {
	
	//  Setup for observation-ar form:
	    observation.setup_observation_ar_form();
	    return;
	
	},
	
	setup_observation_ar_form: function() {
	    
        if ($('#observation-ar').size() != 1) {return;}
       
	    
	//  Change tab based on `type` field:    
	    $( document ).on( "click", '[data-ce*="name:type;"]', function(e){ 
		   $(this).parents('nav.tab').addClass('active').siblings().removeClass('active');
		   var dataCE = ce.data($(this).attr('data-ce'));
		   var value = dataCE['value'];
		   $('.tab-container[data-sa*="name:type;"]').addClass('hide').filter('[data-sa*="'+value+':y;"]').removeClass('hide');
		   if (value == "radio") {
		       $('[data-ce*="name:is_default"]').removeClass("hidden");
		   } else {
		       $('[data-ce*="name:is_default"]').addClass("hidden"); 
		   }
	    } );
	   
	 // Reset options to remove
	    $(document).on( "click", 'form[id] [data-sa="reload"]', function(e){
	
		$('tr.remove').each( function(){
		    var $obj = $(this).find('[data-sa*="onClick:observation.option_remove;"]');
		    var label =  $obj.text();
		    var title = $obj.attr('title');
		    $obj.text($obj.attr('data-label'));
		    $obj.attr('title', $obj.attr('data-title') );
		    $obj.attr('data-label', label );
		    $obj.attr('data-title', title);
		    $(this).removeClass('remove');
		});
		$($('input[name="options_to_delete"]')).val('');
		
	    });
	    
	  // Option list: add new items using local data:
	     var  $data = form.data.get($('#observation-ar'));
	    
	     
    	     //	Collect data for new option rows:
    	     	var $request_data = {};
    	     	
    	     	$.each($data, function(index,value){
    	     	    var parts = index.split('_');
    	     	    if ( $('[name="'+index+'"]').size() == 0  && parts[0] === "opt" ) {
    	     		var pos  = parts[2];
    	     		if (! $request_data.hasOwnProperty(pos) ) {  $request_data[pos] = {}; }
    	     		var name = parts[1];
    	     		$request_data[pos][name] = value;
    	     	    }
    	     	});
    	     	
    
	    	var htmlSA = sa.dataSA($('html').eq(0).attr('data-sa'));
	    	var ajax = new gen.ajax('');
	    	ajax.get({ url:htmlSA['application']+'/observation/options_get_rows', data:$request_data , callback:'observation.option_add_new_rows' });
	    	
	    	
	    	return;
    	 
	   
	},
	
	option_add_new_rows: function(data){
	    
	   var  $data = form.data.get($('#observation-ar'));
	    
	   // 	Add rows:
	    	for (var i = 0; i < data.rows.length; i++) { 
	    	    var $tbody = $('#observation_opt').find('tbody');
    	    	    if ($tbody.find('tr').size() == 0 ) {
    	    		$tbody.html(data.rows[i]['html']);
    	    	    } else {
    	    		$tbody.find('tr:last').after(data.rows[i]['html'])	;
        	    }
	    	    $tbody.find('tr:last').addClass('new');
	    	    $tbody.find('tr:last input[type="text"]').trigger('change');
	    	}
	    	
	    //	Update "is_default" field:
	    	$.each( $('#observation_opt').find('[data-ce*="name:is_default;"]'), function() {
	    	   
		    var dataCE = ce.data($(this).attr('data-ce'));
		    if ( $data.hasOwnProperty( dataCE['name']) ) {
			
			if (dataCE['value'] == $data[dataCE['name']]) {
			    if (!$(this).hasClass("checked")){
			    	$(this).trigger("click");
			    }	    		
			}
		    }
		   
		});
	    
	    // 	Show or hide "is_default" field:  
	    	var dataCE = ce.data( $('[data-ce*="name:type;"].checked').attr('data-ce'));
	    	if (dataCE['value'] == "radio") {
	    	    $('[data-ce*="name:is_default"]').removeClass("hidden");
                } else {
    		    $('[data-ce*="name:is_default"]').addClass("hidden"); 
                }
	    	
	    //	Update options to remove:
	    	if ( $data.hasOwnProperty('options_to_delete') ) {
	    	  
	    	    var options_to_delete = $data['options_to_delete'].split(';');
	    	    for (var i=0; i < options_to_delete.length; i++) {
	    		
	    		var $row = $('#observation_opt').find('[name="opt_name_'+options_to_delete[i]+'"]').parents('tr');
	    		$row.addClass('remove');
	    		
	    		var $obj = $row.find('[data-sa*="onClick:observation.option_remove;"]');
			var label =  $obj.text();
			var title = $obj.attr('title');
			    $obj.text($obj.attr('data-label'));
			    $obj.attr('title', $obj.attr('data-title') );
			    $obj.attr('data-label', label );
			    $obj.attr('data-title', title);
	    	    }
	    	}
	    	return;
	},
	
	
	save_callback: function(data){
	   // window.console.info('observation:save_callback:');
	  //  window.console.info(JSON.stringify(data));
	    window.console.info(data.redirect);
	    return;
	    
	},
	
	option_new: function(data){
	    
	
	    //	Get parent table:
	    	var $table = $('#observation_opt');
	    	
	    	
	    //	Get highest postion:
	    	var max = null;
	    	$table.find('input[name*="opt_position"]').each(function(){
	    	    var pos = parseFloat($(this).val());
	    	    if (max == null) { max = pos;}
	    	    if (max < pos)   { max = pos; }
	    	});
	    	data.position_max = max;
	    	data.position_row = 100*(parseInt($table.find('input[name*="opt_position"]').size(),10)+1);
	    	
	    	
	    //	Get application name form html object for call url and do AJAX call:	
	    	var htmlSA = sa.dataSA($('html').eq(0).attr('data-sa'));
	    	var ajax = new gen.ajax('');
	    	ajax.get({ url:htmlSA['application']+'/observation/options_get_row', data:data , callback:'observation.option_add_new_row' });
	    	return;
	},
	
	option_add_new_row: function(data){
	    
	    
	    
	    // 	Add row:  
	    	var $tbody = $('#observation_opt').find('tbody');
	    	
	    	if ($tbody.find('tr').size() == 0 ) {
	    	    $tbody.html(data.html_row);
	    	    $tbody.find('tr:last').find('[data-ce*="is_default"]').trigger("click");
	    	} else {
	    	    $tbody.find('tr:last').after(data.html_row)	;
	    	}
	    	$tbody.find('tr:last').addClass('new');
	    	$tbody.find('tr:last input[type="text"]').trigger('change');
	    
	    // 	Show or hide "is_default" field:  
	    	var dataCE = ce.data( $('[data-ce*="name:type;"].checked').attr('data-ce'));
	    	if (dataCE['value'] == "radio") {
	    	    $('[data-ce*="name:is_default"]').removeClass("hidden");
                } else {
    		    $('[data-ce*="name:is_default"]').addClass("hidden"); 
                }
	        return;
	},
	
	option_remove: function(data){
	    
	    
	    //	Toggle class, label and title:
	    	data.me.parents('tr').toggleClass('remove');
	    	var label =  data.me.text();
	    	var title = data.me.attr('title');
	    	data.me.text(data.me.attr('data-label'));
	    	data.me.attr('title', data.me.attr('data-title') );
	    	data.me.attr('data-label', label );
	    	data.me.attr('data-title', title);
	    	
	    //  Update field "options_to_delete":
	    	var str_options_to_delete = '';
	    	var glue= '';
	    	$('input[name*="opt_name"]').each(function(){
	    	    if ($(this).parents('tr').hasClass('remove') ) {
	    		var name = $(this).attr('name');
	    		str_options_to_delete += glue+ name.replace('opt_name_','');
	    		glue = ';';
	    		
	    	    }
	    	});
	    	$('input[name="options_to_delete"]').val(str_options_to_delete);
	        $('input[name="options_to_delete"]').trigger('change');
	    	return; 
	}
	
	
};

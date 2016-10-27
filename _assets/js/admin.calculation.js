//	_assets/js/admin.calculation.js



var calculation = {
	
	setup: function() {
	
	//  Setup for observation-ar form:
	    calculation.setup_ar_form();
	    return;
	
	},
	
	setup_ar_form: function() {
	    if ($('#calculation-ar').size() != 1) {return;}
	    
	    
	//  submit save:
	    $(document).on( "submit", '#calculation-ar', function(e) {
		e.preventDefault();
        	 
            //	POST data:
        	var data = {};
        	
        	// Form ID:
        	   data['form_id'] = $(this).attr('id');
        	   
    
        	// Read form fields:
        	    var val = '';
        	    data.to_delete    = {};
    		    data.to_delete.inp = {};
    		    data.to_delete.act = {};
    		    data.to_delete.sco = {};
    		    data.to_delete.alg = {};
        	    $.each($(this).serializeArray(),function(name,field){
        		if ($('input[name="'+field.name+'"][data-default*="NULL"], input[name="'+field.name+'"][data-default*="null"]').size()>0) {
        		    val = field.value ==''? "NULL": field.value; 
        		} else {
        		   val  = field.value;
        		}
        		
      
        		var prefixes = field.name.split('_');
        		var prefix = prefixes.shift();
        		
        		
        		
        		
        		
        		switch (prefix) {
        		
        			case 'inp':     if (! data.hasOwnProperty('inp')) {data.inp ={};}
        			    		if (prefixes.join('_') == 'to_delete'){
        			    		   data.to_delete.inp = val.split(';');
         			        	   break;
         			                }
        			    		var indx  = prefixes.shift();
        			    		var name = prefixes.join('_');
        			    		if (! data.inp.hasOwnProperty(indx)) {
        			    		    data.inp[indx] = {};
        			    		    data.inp[indx]['act']  = {};
        			    		    data.to_delete.act[indx] = {};
        			    		}
        			    		data.inp[indx][name]=  val;
        			    	        break;
        			    		
        			case 'act':     if (! data.hasOwnProperty('inp')) {data.inp = {};}
        			                var input  = prefixes.shift();
        			                if (! data.inp.hasOwnProperty(input)) {
        			        	   data.inp[input] ={};
        			        	   data.inp[input]['act']  = {};
        			        	   data.to_delete.act[input] = {};
        			                }
        			                if (prefixes.join('_') == 'to_delete'){
        			                    data.to_delete.act[input] =  val.split(';');
        			        	   break;
        			                }
        				        var indx  = prefixes.shift();
        				        if (! data.inp[input]['act'] .hasOwnProperty(indx)) {data.inp[input]['act'][indx] = {};}
        				        var name = prefixes.join('_');
        				        data.inp[input]['act'][indx][name] = val;
        			    	        break;
        			    		
        			case 'alg':     if (! data.hasOwnProperty('alg')) {data.alg ={};}
		    				if (prefixes.join('_') == 'to_delete'){
		    				    data.to_delete.alg =  val.split(';');
		    				    break;
		    				}
		    				var indx  = prefixes.shift();
		    				var name = prefixes.join('_');
		    				if (! data.alg.hasOwnProperty(indx)) {
		    				    data.alg[indx] = {};
			    		   
		    				}
		    				data.alg[indx][name]=  val;
		    				break;
        			    		
        			case 'sco':     if (! data.hasOwnProperty('sco')) {data.sco ={};}
    						if (prefixes.join('_') == 'to_delete'){
    						    data.to_delete.sco = val.split(';');
    						    break;
    						}
    						var indx  = prefixes.shift();
    						var name = prefixes.join('_');
    						if (! data.sco.hasOwnProperty(indx)) {
    						    data.sco[indx] = {};
	    		   
    						}
    						data.sco[indx][name]=  val;
    						break;
        			    		
        			default:  	data[field.name] =  val;
        				  	break;
        		}
        		
        		
        	    });
        	    
        	    if ( data.hasOwnProperty('inp') ) {data.inp = JSON.stringify(data.inp );}
        	    if ( data.hasOwnProperty('alg') ) {data.alg = JSON.stringify(data.alg );}
        	    if ( data.hasOwnProperty('sco') ) {data.sco = JSON.stringify(data.sco );}
        	    data.to_delete = JSON.stringify(data.to_delete);
        	    
        	    
                //  Data custom radio buttons:
        	    $(this).find('[data-ce*="type:radio;"].checked').each( function(){
        		var dataCE = ce.data($(this).attr('data-ce'));
        	        data[dataCE['name']] = dataCE['value'];
        	    });
        	    
        	//  Publish field:
        	    var field_publish = $(this).find('.publish').size()===1 ? $(this).find('.publish'): null;
        	    if (field_publish !== null) {
        		 data['publish'] = field_publish.hasClass('not')? -1 : 1;
        	    }
        	    
        	    
                
        	    
             //  Do AJAX call:  
        	 if (debug == "on"){
        	    var url = window.location.href;
        	    url = url.toLowerCase();
        	    if (url.indexOf("?show")>0 || url.indexOf("&show")> 0  || show === "on") { 
        		//window.console.info("Form-data send to server:");
        		//$.each(data, function(name,value){
        		   // window.console.info(name+': '+value);
        		//});
        	    }
        	 }
        	 
        	
        	 var ajax = new gen.ajax('');
        	 window.console.info( $(this).attr('data-sa'));
        	 var data_sa = sa.data($(this).attr('data-sa') );
        	  window.console.info( data_sa.submit);
        	  window.console.info( data_sa.callback);
        	 ajax.post({ url: data_sa.submit, data:data, callback:data_sa.callback});
  
	    });
	
	//  tabs:
	    $( document ).on( "click", '.tab[data-sa*="group"][data-sa*="show"]', function(e){
		
	    	var data_sa = sa.data($(this).attr('data-sa') );
	    	var group = data_sa['group'];
	    	var show  = data_sa['show'];
	    	$('.tab[data-sa*="group:'+group+'"]').not('[data-sa*="show:'+show+'"]').removeClass('active');
	    	$(this).addClass('active');
		
	    	$('.tab-container[data-sa*="group:'+group+'"]').not('[data-sa*="name:'+show+'"]').addClass('hide');
	    	$('.tab-container[data-sa*="name:'+show+'"]').removeClass('hide');
	    	
	   	$('input[name="tab"]').val(data_sa.tab).trigger('change');
	   	
		
	    });
	       
	    
	//  Additional change events:
	    
	    //  calculation_inp, calulation_act:
	    	$ (document).on( "change", '[name^="inp_"],[name^="act_"]', function(e){
	    		calculation.inputs.propagate_change($(this));
	    		return;
	    	});
	   
	    //  calculation_alg:
	    	$ (document).on( "change", '[name^="alg_"]', function(e){
	    		calculation.algorithms.propagate_change($(this));
	    		return;
	    	});
	    	
	    //  calculation_sco:	
	    	$ (document).on( "change", '[name^="sco_"]', function(e){
	    		calculation.scores.propagate_change($(this));
	    		return;
	    	});
	    
	    //	calculation_act.description:
	    	$ (document).on( "change", '[name^="act_"][name*="_description"]', function(e){
	    		calculation.description.propagate_change($(this));
	    		return;
	    	});
	    	
	    //	calculation_inp.FID_OBSERVATION: 	
	    	$ (document).on( "change", '[name^="inp_"][name*="FID_OBSERVATION"]', function(e){
	    		calculation.fid_observation.propagate_change($(this));
	    		return;
	    	});
	    	
	    //	calculation_*.FID_ARTILCE: 	
	    	$ (document).on( "change", '[name*="FID_ARTICLE"]', function(e){
	    	        calculation.fid_article.propagate_change($(this));
	    		return;
	    	});
	    	
	    //	calculation_*.FID_CALCULATION_NEXT: 	
	    	$ (document).on( "change", '[name*="FID_CALCULATION_NEXT"]', function(e){
	    		calculation.fid_calculation_next.propagate_change($(this));
	    		return;
	    	});
	    
	  
	//  At open execute additional change events for fields with local_data:
	    var local_data = form.data.get($('#calculation-ar'));
	    
	    
	    //  Set input, algorithm, score tab:
	        if (local_data.hasOwnProperty('tab') ){
	            var tab =  local_data.tab;
	            $('.tab[data-sa*="group"][data-sa*="show"][data-sa*="'+tab+'"]').trigger('click');
	        }
	    
	    //  calculation_inp, calulation_act:
	    	$('[name^="inp_"],[name^="act_"]').each( function(){
	    	var name = $(this).attr('name');
	    	if (local_data.hasOwnProperty(name) ) {
	    	    calculation.inputs.propagate_change($(this));	
	    	}
	    	return;
	    	});
	    	 
	    //  calculation_alg:
	    	$('[name^="alg_"]').each( function(){
	    	var name = $(this).attr('name');
	    	if (local_data.hasOwnProperty(name) ) {
	    	    calculation.algorithms.propagate_change($(this));	
	    	}
	        return;
	    	});
	    	
	    //  calculation_sco:
	    	$('[name^="sco_"]').each( function(){
		var name = $(this).attr('name');
		if (local_data.hasOwnProperty(name) ) {
		    calculation.scores.propagate_change($(this));
		}
		return;
		});
		    
	    
	    //	calculation_act.description:
	    	$('[name^="act_"][name*="_description"]').each( function(){
	    	var name = $(this).attr('name');
	    	if (local_data.hasOwnProperty(name) ) {
	    	    calculation.description.propagate_change($(this));
	    	}
	    	return;
	    	});
	    	
            //	calculation_inp.FID_OBSERVATION:  
	    	$('[name^="inp_"][name*="FID_OBSERVATION"]').each( function(){  
		var name = $(this).attr('name');
		if (local_data.hasOwnProperty(name) ) { 
		    calculation.fid_observation.propagate_change($(this));
		}
	        return;
		});
	    	
	    //	calculation_*.FID_ARTICLE: 
	    	
	    	$('[name*="FID_ARTICLE"]').each( function(){
		var name = $(this).attr('name');
		
		if (local_data.hasOwnProperty(name) ) {
		    calculation.fid_article.propagate_change($(this));
		}
	        return;
		});
	  
	    //	calculation_*.FID_CALCULATION_NEXT: 
	    	$('[name*="FID_CALCULATION_NEXT"]').each( function(){
		var name = $(this).attr('name');
		if (local_data.hasOwnProperty(name) ) {
		    calculation.fid_calculation_next.propagate_change($(this));
		}
	        return;
		});
	    	
	   //	Add class "remove" to rows that are marked to be removed in local data:
	    	$.each(local_data, function(name,value){
	    	    if (name.indexOf('_to_delete') != -1) {
	    		
	    		var row = name.replace('_to_delete', '');
	    		var indx = value.split(';');
	    		
	    		var glue = ';"], tr[data-sa*="row:'+row+';"][data-sa*="indx:';
	    		var select =  'tr[data-sa*="row:'+row+';"][data-sa*="indx:'+indx.join(glue)+';"]';
	    		$(select).addClass('remove');
	    	    }
	    	});
	    	
	    //	Add new rows in case in local data:
	    	if ( local_data.hasOwnProperty('new_fields') ) {
	    	    
	    	    
	    	//  Read    new_fields in object "to_add": 
	    	    var new_fields = local_data.new_fields.split('|');
	    	    var to_add = {};
	    	    $.each(new_fields, function(name,value){
	    		
	    		var data_sa 	= sa.data(value);
	    		var row  	= data_sa.row;
	    		var indx 	= data_sa.indx;
	    		var inp  	= null;
	    		if (row.indexOf('_') !== -1) {
	    		    var parts = row.split('_');
	    		    row = parts[0];
	    		    inp = parts[1]; 
	    		}
	    		
	    		if ( !to_add.hasOwnProperty(row) ){ to_add[row] = inp == null? [] : {};}
	    		if (inp == null ) {
	    		    to_add[row].push(indx);
	    		} else {
	    		    if ( !to_add[row].hasOwnProperty(inp) ){ to_add[row][inp] = [];}
	    		    to_add[row][inp].push(indx);
	    		}
	    	    });
	  
	    	
	    	//  GET new rows
	    	    var htmlSA 		= sa.dataSA($('html').eq(0).attr('data-sa'));
	    	    var ajax 		= new gen.ajax('');
	    	    
	    	// Create searchable array from local_data
		    var nested_data = calculation.convert_to_nested(local_data);

	    	    
	    	//  Add rows
	    	    $.each(['inp','act','alg','sco'], function(k1,row){
	    		var D = nested_data.hasOwnProperty(row)? nested_data[row] : {};
	    			
	    		if ( to_add.hasOwnProperty(row) ) { $.each(to_add[row], function(k2){
	    		  
		    	    if ( Array.isArray(to_add[row][k2]) ) {
		    		$.each( to_add[row][k2], function(k3){
		    		    var i  = k2;
		    		    D = D.hasOwnProperty(i)? D[i] : {};
		    		    var ii = to_add[row][k2][k3];
		    		  
		    		    var request	     = D.hasOwnProperty(ii)? D[ii] : {};
		    		        request.indx = ii;
		    		        request.inp  = i;
		    		        request.pos  = request.hasOwnProperty('position')? request['position'] : 100;
		    		      
		    		        
		    		    //  Get FID_OBSERVATION: 
		    		        var lookup  = nested_data.hasOwnProperty('inp')? nested_data['inp'] : {};
		    		            lookup  = lookup.hasOwnProperty(i)? lookup[i] : {};
		    		       
		    		        var FID_OBSERVATION     =   lookup.hasOwnProperty('FID_OBSERVATION')? lookup['FID_OBSERVATION'] : -1;
		    	            	
		    			request.FID_OBSERVATION = parseInt( FID_OBSERVATION,10);
		    			
		    		        ajax.get({ url:htmlSA['application']+'/calculation/ajax/get_'+row+'_row', data:  request , callback:'calculation.callback_add_row' });
		    		});
		    		
		    	    } else {
		    		 var i = to_add[row][k2];
		    		 var request       = D.hasOwnProperty(to_add[row][k2])? D[to_add[row][k2]] : {};
		    		     request.indx  = i;
		    		     request.pos    = request.hasOwnProperty('position')? request['position'] : 100;
		    		     ajax.get({ url:htmlSA['application']+'/calculation/ajax/get_'+row+'_row', data:  request , callback:'calculation.callback_add_row' });
		    	    }
		    	   
	    		});}
	    			
	    	    });
	
	    	}
	    
	    return;
	},
	
	
	convert_to_nested: function(local_storage){
	    
	    var result = {};
	    
	    $.each(local_storage, function(str_key,value){

		var parts= str_key.split('_');
		
		if (parts.length>0 ) {
		    var keys = [];
		        keys.push( parts.shift() );
		        if ( parts.length>0 ) {
		             var next = parts.shift();
		             while ($.isNumeric(next) && parts.length>0 ){
		        	 keys.push( parseInt(next,10) );
		        	 next = parts.shift(); 
		             }
		             if (parts.length>0) {
		        	 next += '_'+parts.join('_');
		             }
		             keys.push( next);
		       }
		result = calculation.convert_to_nested_add(result, keys,value);
	    	}
		
	    });
	    
	    return result;
	    
	},
	
	convert_to_nested_add: function(result,keys,value){
	    

        //  Get next key:
	    var key = keys.shift();
	  
	//  Create sub-array:
	    if ( !result.hasOwnProperty(key) ){ result[key] = {};} 
	    
	//  to next level:
	    if ( keys.length > 0 ){ 
		result[key] = calculation.convert_to_nested_add(result[key], keys,value);
		
	    }  else {
		result[key] = value;
	   }
           return result;
	   
	},
	
	get_new_row:function(row_type, $obj) {
	    
	    
	      
	     var request= {};
		 request.pos  = 100;
		 request.indx = 1;
		 
		 
	    var $inp   = $obj.parents('tr[data-sa*="row:inp;"]');
	    var inp = '';
	        if ($inp.size() == 1) {
		     var sa_inp = sa.data($inp.attr('data-sa') ); 
		     request.inp = parseInt(sa_inp.indx,10);
		     inp =  '_'+request.inp;
	        }
		 
	    var row   = row_type;
		var $rows = $('tr[data-sa*="row:'+row+inp+';"]');
		if ( $rows.size() > 0) {
		     var $last_row = $rows.last();
		     var sa_last_row =sa.data($last_row.attr('data-sa') );
		     request.indx = sa_last_row.hasOwnProperty('indx')? parseInt(sa_last_row.indx) +1: 1;
		
		     var max = gen.find_max_val('tr[data-sa*="row:'+row+inp+';"] input[name$="_position"]');
		     if ( max !== null ){
			 request.pos  = parseInt(max,10) + 100;
		     }
		 }
		
	    //  FID_OBSERVATION for act only:
		if (row == 'act') {
		   var FID_OBSERVATION     =  $('[name="inp_'+request.inp+'_FID_OBSERVATION"]').val();
		   request.FID_OBSERVATION = parseInt( FID_OBSERVATION,10);
		}
		  
	    //  GET active record for this observation_id :   	 
		 var htmlSA = sa.dataSA($('html').eq(0).attr('data-sa'));
	    	 var ajax = new gen.ajax('');
	    	    
	    	 ajax.get({ url:htmlSA['application']+'/calculation/ajax/get_'+row+'_row', data:  request , callback:'calculation.callback_add_row' });
	    	 return;
	    
	},
	
	callback_add_row: function(data){
	      
	        
	       
	        var local_data = form.data.get($('#calculation-ar'));
	
		if (parseInt(data.meta.error,10) < 0) { 
		    if ( debug == "on" ) {
			window.console.error(JSON.stringify(data) );
		    }
		    return;
		}
		
		var pfx= data.row_type;
		
		if ($('#calculation_'+pfx+'>tbody>tr[data-sa*="row:'+pfx+';"]').size() == 0) {
		    $('#calculation_'+pfx+'>tbody').html(data.html);
		} else {
		    $('#calculation_'+pfx+'>tbody>tr[data-sa*="row:'+pfx+';"]:last').after(data.html);
		}
		
		
		
		var $new_row = $('#calculation_'+pfx+'>tbody>tr[data-sa*="row:'+pfx+';"]:last');
		
		
		if (local_data.hasOwnProperty(pfx+'_to_delete')) {
		    var  indxs = ';'+local_data[pfx+'_to_delete']+';';
		    
		    var new_row_sa = sa.data($new_row.attr('data-sa'));
		    var indx = new_row_sa.indx;
		   
		    if  ( indxs.indexOf(';'+indx+';') >= 0 ){
			
			 var $btn = $new_row.find('[data-sa*="onClick:"][data-sa*=".remove"]');
			
			 calculation.remove($btn);
		    }
		    
		}
		
		$new_row.find('[name]').trigger('change');
		
		
		 
		var new_fields = local_data.hasOwnProperty('new_fields')? local_data.new_fields: '';
		
		var new_row_sa = $new_row.attr('data-sa');
		
		if ( new_fields.indexOf(new_row_sa) == -1) {
		       var glue= new_fields.length >0? '|':'';
		      new_fields += glue+new_row_sa;
			     
		} 
		
		form.data.set($('#calculation-ar'),'new_fields',new_fields);
		
		return;
	},

	popup:{
	    
	    get: function(data) {
		
	    //   Check name property:
		 if (!data.hasOwnProperty("name")) { if (debug == "on") { window.console.error("name not defined."); return;}}
	
            //   Request parameters
		 var request 	  	= {};
		     request.name   	= data.name;
		     request.val  	= $('[name="'+data.name+'"]').val();
		     request.action_ok 	= "calculation.popup.update";
		     request.ID 	= $('input[name="ID"]').val();
		 
	     //  AJAX call:   	 
		 var htmlSA = sa.dataSA($('html').eq(0).attr('data-sa'));
	    	 var ajax = new gen.ajax('');
	    	 ajax.get({ url:htmlSA['application']+'/calculation/ajax/get_popup', data:  request , callback:'calculation.popup.callback' });
	    	 return;
	    },
	   
	    callback: function(data){
		if (parseInt(data.meta.error,10) < 0) {return; }
		$('#box-content').html(data.html);
		$('#popup').removeClass('ui-closed');
		return;
	    },
	    
	    update: function(){
	    
	    //  Get name and value from popup box:
		var val  = $('#record').val();
		var name = $('#record').attr('data-name');
		
		
	    //  Update named field:	
		$('[name="'+name+'"]').val(val);
			    
	     // Trigger change event on named field:   
		$('[name="'+name+'"]').trigger('change');
			  
	     // Close popup:  
		$('#popup').addClass('ui-closed');
		return;
	    },
	    
	},
		
	inputs: {
	    
	    propagate_change: function($obj){
	    //   Add change-class to slide button:
		 var name   = $obj.attr('name');   
		 var ids    = name.split('_');
		 var inp_id = ids[1];
		 $('.btn[data-sa="inp-'+inp_id+'"]').addClass("changed");
		 
            //   Add change-class to inputs-tab: 
	         $('.tab[data-sa*="show:inputs"] label').addClass("changed");
	         return;
	    },
	   
	    add: function(data){
		 calculation.get_new_row('inp', data.me);
		 return;
	    },
	    
	   
	    
	    remove: function(data){
		 calculation.remove(data.me);
		 return;
	    },
		
	    actions: {
		
		add: function(data){
		     calculation.get_new_row('act', data.me);
		     return;
		 },
		 
		
		    
		remove: function(data){
		        calculation.remove(data.me);
		        return;
		}
		
	    }
	},
	
	algorithms: {
	    
	    propagate_change: function($obj){
	        $('.tab[data-sa*="show:algorithm;"] label').addClass("changed");
	        return;
	    },
	    
	
	    add: function(data){
		 calculation.get_new_row('alg', data.me);
		 return;
	    },
	    
	    remove: function(data){
		 calculation.remove(data.me);
		 return;
	    }
	 
	},
	
	scores: {
	    
	    propagate_change: function($obj){
	        $('.tab[data-sa*="show:scores;"] label').addClass("changed");
	        return;
	    },
	    
	    add: function(data){
		 calculation.get_new_row('sco', data.me);
		 return;
	    },
	    
	    remove: function(data){
		 calculation.remove(data.me);
		 return;
	    }
	   
	},
	
	fid_observation: {
	    
	    propagate_change: function($obj){
		
		
	    //	GET name and ID of changed FID_OBSERVATION field:
		var request 			= {};
		    request.name 	        = $obj.attr('name'); 	
		    request.ID 	                = $obj.val();
		    
		  
		
	     //  GET active record for this observation_id :   	 
		 var htmlSA = sa.dataSA($('html').eq(0).attr('data-sa'));
	    	 var ajax = new gen.ajax('');
	    	 ajax.get({ url:htmlSA['application']+'/observation/ajax/get_record_by_id', data:  request , callback:'calculation.fid_observation.callback' });
	    	 return;
	  
	    },
	    
	    callback: function(data){
		
		
		if (parseInt(data.meta.error,10) < 0) {return; }
		
		var htmlSA = sa.dataSA($('html').eq(0).attr('data-sa'));
		var path   = htmlSA['path']+htmlSA['application']+'/admin/observations';
	
		var name = data.inputData.name;
		var $a   = $('[data-sa*="name:'+name+'"]').siblings('p').eq(0).find('a');
		
		var text 		= data.ar.hasOwnProperty('name')? data.ar.name 		: $a.attr('data-text');
		var href		= data.ar.hasOwnProperty('href')? path+'/'+data.ar.href : path;
		
		$a.addClass("changed").text(text).attr('href', href);
		$('[data-sa*="name:'+name+'"]').addClass("changed");
		
		
		var parts = name.split('_');
		var indx = parts[1];
		var $act_rules = $('#calculation_act_'+indx+' tbody tr select[name$="_rule"]');
		
		
		var request = {};
		    request.ID = data.inputData.ID;
		    request.selectors = {};
		    $act_rules.each(function(){
			request.selectors[$(this).attr('name')] = $(this).val();
		   
		    });
		  
		    var htmlSA = sa.dataSA($('html').eq(0).attr('data-sa'));
		    var ajax = new gen.ajax('');
		    ajax.get({ url:htmlSA['application']+'/observation/ajax/get_options_after_update', data:  request , callback:'calculation.fid_observation.callback_update_options' });
		return;
	    },
	    
	    callback_update_options: function(data) {
		
		 
		 $.each(data.selectors, function(name,d){
		     var $selector = $('[name="'+name+'"]');
		     var val 	   = $selector.val();
		     $selector.html(d.html);
		     
		     if (val != d.value ){
			 $selector.val(d.value ).trigger('change');
		     }
		    
		 });
	   
		
		 return;
	    }
		    
	    
	},
	
	description: {
	    
	   		
	     propagate_change: function($obj){
	     //  Update title of message button:		
		 var message 	= $obj.val();
		 var name 	= $obj.attr('name'); 	
		 var title = $('[data-sa*="name:'+name+'"]').attr('title');
		 var parts = title.split('"');
		 parts[1] = message;
		 title = parts.join('"');
		 
	     //  Add class "changed" on message buttuon:
		 $('[data-sa*="name:'+name+'"]').attr('title',title).addClass("changed");
		 
	     // Continue propagation for input:	    
		calculation.inputs.propagate_change($obj);
		return;
				
	     },
	
	},
		
	fid_article: {
	    
	    propagate_change: function($obj){
	
		
	    //	GET name and ID of changed FID_ARTICLE field:
		var request 			= {};
		    request.name 	        = $obj.attr('name'); 	
		    request.ID 	                = $obj.val();
		
		
	     //  GET active record for this article_id :   	 
		 var htmlSA = sa.dataSA($('html').eq(0).attr('data-sa'));
	    	 var ajax = new gen.ajax('');
	    	
	    	 ajax.get({ url:htmlSA['application']+'/article/ajax/get_record_by_id', data:  request , callback:'calculation.fid_article.callback' });
	    	 return;
         
	    },
	    
	    callback: function(data){
		
		var name	= data.inputData.name;
		
		
		if (parseInt(data.meta.error,10) < 0) {return; }
		
		var htmlSA = sa.dataSA($('html').eq(0).attr('data-sa'));
		var path   = htmlSA['path']+htmlSA['application']+'/admin/articles';
	
		
		var $a   	= $('[data-link*="'+name+'"]');
		
		var title 		= data.ar.hasOwnProperty('name')?	data.ar.name		: $a.attr('data-title');
		var id 	        	= data.ar.hasOwnProperty('ID')? 	data.ar.ID		: 'NULL';
		var href		= data.ar.hasOwnProperty('href')? 	path+'/'+data.ar.href 	: path;
		
	
		$('[data-sa*="name:'+name+'"]').addClass("changed");
		$a.text(id).addClass("changed").attr('title',title).attr('href', href);
		
		return;
	    }
	    
	},
	
	fid_calculation_next: {
	    
	    propagate_change: function($obj){
		
		
	    //	GET name and ID of changed FID_CALCULAION_NEXT field:
		var request 			= {};
		    request.name 	        = $obj.attr('name'); 	
		    request.ID 	                = $obj.val();
		
		
	     //  GET active record for this calculation_next_id :   	 
		 var htmlSA = sa.dataSA($('html').eq(0).attr('data-sa'));
	    	 var ajax = new gen.ajax('');
	    	
	    	 ajax.get({ url:htmlSA['application']+'/calculation/ajax/get_record_by_id', data:  request , callback:'calculation.fid_calculation_next.callback' });
	    	 return;
            },
	    
	    callback: function(data){
		
		
		if (parseInt(data.meta.error,10) < 0) {return; }
		
		var htmlSA = sa.dataSA($('html').eq(0).attr('data-sa'));
		var path   = htmlSA['path']+htmlSA['application']+'/admin/calculations';
	
		var name	= data.inputData.name;
		var $a   	= $('[data-link*="'+name+'"]');
		
		var title 	= data.ar.hasOwnProperty('name')?	data.ar.name		: $a.attr('data-title');
		var id 	        = data.ar.hasOwnProperty('ID')? 	data.ar.ID		: 'NULL';
		var href	= data.ar.hasOwnProperty('href')? 	path+'/'+data.ar.href 	: path;
		
	
		$('[data-sa*="name:'+name+'"]').addClass("changed");
		var $a   = $('[data-link*="'+name+'"]');
		$a.text(id).addClass("changed").attr('title',title).attr('href', href);
		return;
	    }
	    
	},
	
	remove: function($obj){
	    
	    
	    var data_sa =  sa.data($obj.attr('data-sa'));
	    if ( data_sa.hasOwnProperty('row') && data_sa.hasOwnProperty('indx') ){
		var indx  = data_sa.indx;
		var row   = data_sa.row; 
		var $row = $obj.parents('tr[data-sa*="row:'+row+';"][data-sa*="indx:'+indx+';"]');
		
		
	
		//  Toggle class, label and title:
		    $row.toggleClass('remove');
	            var label =  $obj.text();
	            var title = $obj.attr('title');
	            $obj.text($obj.attr('data-label'));
	            $obj.attr('title', $obj.attr('data-title') );
	            $obj.attr('data-label', label );
	            $obj.attr('data-title', title);
	
	       //  Update field "_to_delete":
		   var name = data_sa.row+'_to_delete';
		   var to_delete = '';
		   var glue= '';
		   $('tr[data-sa*="row:'+row+';"]').each(function(){
	    	      if ( $(this).hasClass('remove') ) {
	    		 var tr_data_sa =  sa.data($(this).attr('data-sa'));
	    		 if ( tr_data_sa.hasOwnProperty('indx')  ){
	    		     to_delete += glue+tr_data_sa.indx;
	    		     glue = ';';
	    		 }
	    	     }
	    	   });
	    	   $('input[name="'+name+'"]').val(to_delete);
	    	   $('input[name="'+name+'"]').trigger('change');
	    }
	    return; 
	}
	
};

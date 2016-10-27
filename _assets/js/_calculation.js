// js/_calculation.js

$(document).ready(function() {
    
    
//  Bind Listeners for actions
    var selector   = '.calculation [data_rule_show] .observation [data-ce*="type:"],  ';
        selector  += '.calculation [data_rule_msg]  .observation [data-ce*="type:"],  ';
        selector  += '.calculation [data_rule_info] .observation [data-ce*="type:"],  ';
        selector  += '.calculation [data_rule_calc] .observation [data-ce*="type:"],  ';
        selector  += '.calculation [data_rule_report] .observation [data-ce*="type:"] ';
        $( document ).on( "click",   selector, calculation.listener );
        $( document ).on( "taphold",   selector, calculation.listener ); 
        
    var selector   = '.calculation [data_rule_show] .observation [type="text"],  ';
        selector  += '.calculation [data_rule_msg]  .observation [type="text"],  ';
        selector  += '.calculation [data_rule_info] .observation [type="text"],  ';
        selector  += '.calculation [data_rule_calc] .observation [type="text"],  ';
        selector  += '.calculation [data_rule_report] .observation [type="text"] ';
        $( document ).on( "change",   selector, calculation.listener );    
 
 // Apply actions of first  input using pre-filled data:   
    var selector   = '.calculation [data_rule_show] .observation [data-ce*="type:"],   ';
        selector  += '.calculation [data_rule_msg]  .observation [data-ce*="type:"],   ';
        selector  += '.calculation [data_rule_info] .observation [data-ce*="type:"],   ';
        selector  += '.calculation [data_rule_calc] .observation [data-ce*="type:"],   ';
        selector  += '.calculation [data_rule_report] .observation [data-ce*="type:"], ';
        selector  += '.calculation [data_rule_show] .observation [type="text"],        ';
        selector  += '.calculation [data_rule_msg]  .observation [type="text"],        ';
        selector  += '.calculation [data_rule_info] .observation [type="text"],        ';
        selector  += '.calculation [data_rule_calc] .observation [type="text"],        ';
        selector  += '.calculation [data_rule_report] .observation [type="text"]       ';
    /*        
    var $first_input = $(selector).eq(0);
    	if ($first_input.size() == 1){
    	    var $first_observation = $first_input.parents('.observation');
    	    
            var show_open_report = calculation.take_actions($first_observation,false);
            calculation.toggle_btn_open_report(show_open_report);
    	}
    	*/
    	$(selector).each(function(){
    	    
    	 var $first_observation = $(this).parents('.observation');
 	    
         var show_open_report = calculation.take_actions($first_observation,false);
         calculation.toggle_btn_open_report(show_open_report);
    	    
    	});	
     
        
  //	Listener for update calulation result:  listener_update_result   
        var selector   = '.calculation .observation [data-ce*="type:"]';
        $( document ).on( "click",   selector, calculation.listener_update_result);
        $( document ).on( "taphold",   selector, calculation.listener_update_result );
        
        var selector   = '.calculation .observation [type="text"]';
        $( document ).on( "change",   selector, calculation.listener_update_result ); 
          
        return;	
});
	
	
var calculation = {
	
	
	
	
	 listener: function(){
	   
	     var $input = $(this).parents('.input');
	     
	 //  Create an interpreter instance for related observation:
	     var $observation =  $input.find('[data-observation-id]');
	     var show_open_report = calculation.take_actions( $observation,false);
	     calculation.toggle_btn_open_report(show_open_report);
	   
	     return;
		
	 },
	 
	
	 take_actions: function($obj, show_open_report){
	      
	     
	 //  Create an interpreter instance:
	     var interpreter = calculation.interpreter( $obj );
	     if (interpreter.is_available === false) { return; }
	 
	     var $calculation 	= interpreter.calculation;
	     var $input 	= interpreter.input;
	     
	 
	     
	//   Hide next inputs which are conditional    
	     var input_index = $(".input").index( $input);
	     $(".input").slice(input_index+1).filter('.conditional').addClass('ui-closed');
	
	//   Hide msg, info and calc containers: (will only be shown when rule is "true":
	     var msg  = false;
	     var $msg  = $input.find('.msg');
	     $msg.addClass('ui-closed');
	     $msg.find('[data-action]').addClass('ui-closed');
	   
	     var info = false;
	     var $info = $input.find('.info');
	     $info.addClass('ui-closed');
	     $info.find('[data-action]').addClass('ui-closed');
	     
	     var calc = false;
	     var $calc = $input.find('.calc');
	     $calc.addClass('ui-closed');
	     $calc.find('[data-action]').addClass('ui-closed'); 
	    
	         
	 //  Get actions:
	     var actions  = calculation.get_actions( $obj );
	     
         //  Take actions:
	     var $next_observations = [];
	    
	     for ( var action_type in actions ){
		
	    	 for ( var action in actions[action_type] ){
		   
	    		 var rule = actions[action_type][action]; 
	    		 var result = interpreter.execute(rule);
	    		 if (action_type === "show"){ 
	    		  //   window.console.info($obj.parents('.input').attr('id'));
	    		  //   window.console.log($obj.parents('.input').attr('class'));
	    		  //   window.console.log(action+': '+result);
	    		    
	    		// window.console.log($obj.parents('.input').hasClass('ui-closed'));
	    		 result = $obj.parents('.input').hasClass('ui-closed')? false : result;
	    		 }
	    		 if ( result == true ){
	  
	    			 if (action_type == 'msg')  { 
	    			      msg  = true;
	    			      $msg.find('[data-action="'+action+'"]').removeClass('ui-closed'); 
	    			 }
	    			 if (action_type == 'info') { 
	    			      info = true;
	    			      $info.find('[data-action="'+action+'"]').removeClass('ui-closed'); 
	    			 }
	    			 if (action_type == 'calc') { 
	    			      calc = true;
	    			      $calc.find('[data-action="'+action+'"]').removeClass('ui-closed'); 
	    			 }
	    			 if (action_type == 'show') { 
	    			      var $next_input =  $calculation.find('#input-'+action);
	    			      $next_input.removeClass('ui-closed');
	    			      $next_input.removeClass('conditional');
	    			      $next_observations.push( $next_input.find('[data-observation-id]') );
	    			 } 
	    			 if (action_type == 'report')  { 
	    			     show_open_report = true;
	    			 }
	    		 } else {
	    		     if (action_type == 'show') { 
	    			 var $next_input =  $calculation.find('#input-'+action);
	    			 $next_input.addClass('ui-closed');
	    			 $next_input.addClass('conditional');
	    			 $next_observations.push( $next_input.find('[data-observation-id]') );
	    		     }
	    		     
	    		 }
	    	 }
	     }
	     
	      
	     if ( msg ) {   $msg.removeClass('ui-closed');  }
	 
	//     if ( info ) {  $info.removeClass('ui-closed'); }
	//     if ( calc ) {  $calc.removeClass('ui-closed'); }
	    

	     for (var i= 0 ; i< $next_observations.length; i++){
		
		 show_open_report = calculation.take_actions( $next_observations[i], show_open_report); 
	     	
	     }
	    
	     return show_open_report; 
	     
	 },
	 
	 listener_update_result: function(){
	     $('html').attr('data-time-stamp',Date.now());
	     
	     $('#score, #stroke_top').css('opacity', '0.3');
	     $('#score, #stroke_top').find('span').css('background-color', '#FFF');
	  
	     var data= {};
	     
	     data.time_stamp = $('html').attr('data-time-stamp');
	     data.id =         $('[data-calculation-id]').attr('data-calculation-id');
		 
	     var ajax 	= new gen.ajax('');
	     var sa_html = sa.dataSA($('html').eq(0).attr('data-sa'));
	     var url =  sa_html['application'] == "VBNE"?  'ajax/update_result':  sa_html['application']+'/ajax/update_result';
	     ajax.get({ url:url, data:data, callback:'calculation.update_result'});
	     return;
	 },
	 
	 update_result: function(data){
	     if (debug=="on"){
		 window.console.info( 'fe.favorite_added');
		 window.console.log( JSON.stringify(data));
	    }
	    if (parseInt(data.meta.error,10)< 0) { return;}
	    
	    
	     var last_request  = parseInt($('html').attr('data-time-stamp'),10);
	     var time_stamp    = parseInt(data.time_stamp,10);
	   
	     window.console.info( time_stamp >= last_request);
	     if ( time_stamp >= last_request) {
		 window.console.info( 'UPADATE');
		 window.console.log(data.result);
		 
		 //  Update on-page-score:
		     $('#score').html(data.html);
		 
		 //  Update stroke_top score:
		     $('#stroke_top').html(data.html_stroke_top);
		     $('#score, #stroke_top').css('opacity', '1');
		 return;
	     } 
	    
	     return;
	 },
	 
	 toggle_btn_open_report: function(show_open_report) {
	     if ($('.calculation [data_rule_report]').size()> 0) {
	     	    if (show_open_report) {
	     		$('#open-report').removeClass('ui-closed'); 
	     	    } else {
	     		 $('#open-report').addClass('ui-closed'); 
	     	    }
	     }
	     return;
	 },
	 
	 get_actions: function($obj){
	     
	 //  Read data rules:
	     var data_rules = {};
	     if ( $obj.parents('[data_rule_show]').size() == 1 ) {
		data_rules.show = $obj.parents('[data_rule_show]').attr('data_rule_show');	
	     }
	     if ( $obj.parents('[data_rule_msg]').size() == 1 ) {
		 data_rules.msg = $obj.parents('[data_rule_msg]').attr('data_rule_msg');	
	     }
	     if ( $obj.parents('[data_rule_info]').size() == 1 ) {
		 data_rules.info = $obj.parents('[data_rule_info]').attr('data_rule_info');	
	     }
	     if ( $obj.parents('[data_rule_calc]').size() == 1 ) {
		data_rules.calc = $obj.parents('[data_rule_calc]').attr('data_rule_calc');	
	     }
	     if ( $obj.parents('[data_rule_report]').size() == 1 ) {
		data_rules.report = $obj.parents('[data_rule_report]').attr('data_rule_report');	
	     }
		     
         //  Read data rules for each rule type:: 
	     var actions = {};
		    
	     for (var type in data_rules) {
		if ( ! actions.hasOwnProperty(type) ){
		    actions[type] = {};
		}
		var rules = data_rules[type].trim().split('|');
		for (var i = 0; i<rules.length; i++ ) {
		     var p = rules[i].trim().split(':');
		     if (p.length == 2) {
			actions[type][ p[1] ] = p[0];
		     }
		} 
	     }
	   

	     return actions;     
         },
	
	
	    interpreter: function($obj){ 
	
         //  properties:
		this.is_available = false;
		this.valid_types  =  ['value','radio','check'];
		this.valid_rules  = {};
		this.valid_rules.value      = ['EQ','NE','LT','GT','LE','GE','BETWEEN','IS_NULL','IS_NOT_NULL','AND','OR'];
		this.valid_rules.radio      = ['IN','NOT','AND','OR','IS_NULL','IS_NOT_NULL'];
		this.valid_rules.check      = ['IN','ALL','NONE', 'CHECK', 'AND','OR'];
		this.rules_without_param    = ['IS_NULL','IS_NOT_NULL'];
		
		this.type 	  = null;
		this.val	  = '#NA';
		this.check_nb	  = null;
		this.input 	  = null;
		this.calculation  = null;
		
		
	    //  construct:
	        this.construct = function($obj){
	            
	          this.calculation              = $obj.parents('.calculation');
	          this.input 			= $obj.parents('.input');
		  var $observation 		= this.input.find('[data-observation-id]');
		  this.observation_object 	= observation.registrate($observation );
	        
	        
	        // Get "observation type": 
		   var type = this.observation_object.type;
		   var indx = this.valid_types.indexOf(type);
		   if (indx == -1) { window.console.error('calculation.interpreter: type incorrect'); return this;}
		   this.type = this.valid_types[indx];
		   
		// Get value: 
		   var val =  this.observation_object.val();
		   if (val == '#NA') { window.console.error('calculation.interpreter: incorrect value'); return this; }
		           
		   // For type "radio": convert single value into array: 
		      if (this.type == 'radio') {
		         val =  val!= null? parseInt(val,10) : null;
		         val = [val];
		       }
		        
		  // For type "radio" or "check" check and convert to integers: 
		     if (this.type == 'check' ) {
			 val = this.to_integer(val);
			 if (!val) { window.console.error('calculation.interpreter: incorrect value'); return this;}
			 this.check_nb = this.input.find('[data-ce*="type:check;"]').size();
			
		     }
		     
		  // For type "value" convert to floating point
		     if (this.type == 'value') {
			if (val != null) {
			    val = this.to_float(val);
			   if ( val == '#NA') { window.console.error('calculation.interpreter: incorrect value'); return this;}
			}
		     }
		   
		
		   
		// Construct is completed: interpreter is available:
		
		   this.val = val;
		   this.is_available = true;
		   return this; 
		};
		
	
	     
	     // execute: interpretation of a rule o nested rules.
	        this.execute = function(nested_rule){
	    
	        
	        // Get first rule
	           var indx = nested_rule.indexOf('(');
	           if (indx == -1){ return '#NA';} 
	          
	           var rule = nested_rule.substring(0,indx).trim().toUpperCase();
	           if (this.valid_rules[this.type].indexOf(rule) == -1 ) { return '#NA'; }
	       
	       //  Get parameters:
	           var params = null;
	           if (this.rules_without_param.indexOf(rule) == -1 ) {
	               
	              indx++;
	              var indx_end  = nested_rule.length - 1;
	              if (indx == indx_end) { return '#NA'; }
	           
	               var next_nested_rule = nested_rule.substring(indx,indx_end).trim();
	           
	               if (next_nested_rule.indexOf(');') != -1 ) {
	            
	                  next_nested_rule = next_nested_rule.replace(/\);/g, ')<split here/>');
	                  params = next_nested_rule.split('<split here/>');
	               } else {
	                  params = next_nested_rule.split(';');
	               }
	           }
	          
	           
	        // Get value:
	           var val = this.val;
	           if (val == '#NA') { return '#NA'; }
	           
	        // Apply rule:
	           var result          = '#NA';
	           
	           switch (rule) {
	           
	              case 'EQ':
	              var param_0_float   = this.to_float(params[0]);
	              if (param_0_float == '#NA')     {                 break;}
	              if ( val == null              ) { result = false; break;}
	              result = val ==  param_0_float;
	              break;
	               
	              case 'NE':
	              var param_0_float   = this.to_float(params[0]);
		      if (param_0_float == '#NA')     {                 break;}
		      if ( val == null              ) { result = true;  break;}
		      result = val !=  param_0_float;
	              break;   
	           
	              case 'GE':
	              var param_0_float   = this.to_float(params[0]);
		      if (param_0_float == '#NA')     {                 break;}
		      if ( val == null              ) { result = false; break;}
		      result = val >=  param_0_float;
	              break; 
	           
	              case 'LE':
	              var param_0_float   = this.to_float(params[0]);
		      if (param_0_float == '#NA')     {                 break;}
		      if ( val == null              ) { result = false; break;}
		      result = val <=  param_0_float;
	              break;   
	               
	              case 'GT':
	              var param_0_float   = this.to_float(params[0]);
		      if (param_0_float == '#NA')     {                 break;}
		      if ( val == null              ) { result = false; break;}
		      result = val >  param_0_float;
	              break;     
	           
	              case 'LT':
	              var param_0_float   = this.to_float(params[0]);
		      if (param_0_float == '#NA')     {                 break;}
		      if ( val == null              ) { result = false; break;}
		      result = val <  param_0_float;
	              break;
	               
	              case 'BETWEEN':
	              if (params.length < 4                                 ) {                 break;}
	              var G = params[0].trim().toUpperCase();
	              var L = params[2].trim().toUpperCase(); 
	              if (['GT', 'GE'].indexOf(G) == -1                     ) {                 break;}
	              if (['LT', 'LE'].indexOf(L) == -1                     ) {                 break;} 
	              
	              var param_1_float = this.to_float(params[1]);
	              var param_3_float = this.to_float(params[3]);
	              if ( param_1_float == '#NA' || param_3_float == '#NA' ) {                 break;}   
	              if ( val == null                                      ) { result = false; break;}
	              if (G == 'GT'){
	        	   result = val > param_1_float;
	              } else {
	        	   result = val >=  param_1_float;
	              }
	              if (L == 'LT'){
	        	   result = result && (val <  param_3_float );
	              } else {
	        	   result = result && (val <=  param_3_float );
	              }   
	              break;     
	            
	              case 'IS_NULL':
	              val = this.type =="radio" ? val[0]: val;
	              result = val == null;
	              break;
	           
	              case 'IS_NOT_NULL':
	              val = this.type =="radio" ? val[0]: val;
		      result = val != null;
		      break;
	               
	              case 'IN':   
	              params =  this.to_integer(params);
	              if ( params === false ) { break;}
	              var i = 0;
	              var len = val.length;
	              result = false;
	              while (i < len  && !result){
	                if ( params.indexOf(val[i]) > -1) {
	        	    result = true;
	                }
	                i++;
	              }
	              break;
	              
	              case 'NOT': 
	              params =  this.to_integer(params);
		      if ( params === false ) { break;}
		      var i = 0;
		      var len = params.length;
		      result = true;
		      do {
			 if ( val.indexOf(params[i]) == -1) {
			    result = false;
			}
			i++; 
		      } while (i < len  && result);
		      break;
		       
	              case 'ALL': 
		      params =  this.to_integer(params);
		      if ( params === false ) { break;}     
		      var i = 0;
		      var len = params.length;
		      result = true;
		      do {
			if ( val.indexOf(params[i]) == -1) {
			    result = false;
			}
			i++; 
		      } while (i < len  && result);
		      break;
		              
		      case 'NONE': 
		      params =  this.to_integer(params);
		      if ( params === false ) { break;}      
		      var i = 0;
	              var len = val.length;
		      result = true;
		      while (i < len  && result){
		        if ( params.indexOf(val[i]) > -1) {
			   result = false;
		        }
		        i++;
		      }
		      break;
		      
		      case 'CHECK':
		      var param_0   = params[0].trim();
		     
		      if ( param_0.length == 0 || param_0 == "*" || param_0.toUpperCase() == "ALL" || param_0 == 0 ) {
			     param_0 = param_0.length == 0 ||  param_0 == 0? 0 : this.check_nb;
		      } else {
			     param_0 = $.isNumeric(param_0)? parseInt( param_0,10) : '#NA';
		      }
		      if (param_0 == '#NA' ) {break; }
		      
		      var operator = 'EQ';
		      if (params.length > 1){
			  var param_1  = params[1].trim().toUpperCase(); 
			     operator  = ['NE','GT','GE', 'LT','LE'].indexOf(param_1) >-1? param_1: 'EQ';
		      }
		      var nb_checked = this.input.find('[data-ce*="type:check;"].checked').size();
		      
		    
		      switch (operator){
		      case 'NE': result = nb_checked != param_0;        break;
		      case 'GT': result = nb_checked >  param_0;        break;
		      case 'GE': result = nb_checked >= param_0;        break;
		      case 'LT': result = nb_checked <  param_0;        break;
		      case 'LE': result = nb_checked <= param_0;        break;
		      default:   result = nb_checked == param_0;        break;
		      break;
		      }
		      
		     
		      break;
			
		      case 'AND':
		      var i = 0;
	              var len = params.length;
		      result = true;
	              while (i < len && (result  !== '#NA')){
		        result = result && this.execute(params[i]);
		        i++;
	             }
	             break;
	               
		     case 'OR':
		     var i = 0;
		     var len = params.length;
		     result = false;
		     while (i < len &&  (result !== '#NA')) {
	               result = result|| this.execute(params[i]);
	               i++;
		     }
	             break;
	        }
	        return result;
	         
	   };
	       
	
	// to_integer
	   this.to_integer = function(values){
	       
		   var i= 0;
		   var check = true;
		   var len = values.length;
		   while ( i< len && $.isNumeric(values[i]) && check) {
		       var int = parseInt(values[i],10);
		       if (int == values[i]) {
			   values[i] = int;
			   i++; 
		       } else {
			   check = false; 
		       }
		   }
		   return i == len? values : false;
	       
	   };
	   
	   this.to_float = function(val){
	      
	       if ( $.isNumeric( val) )   { return parseFloat(val); }
	       if (val.indexOf(',') >-1 ) { return parseFloat(val.replace(/,/,'.')); }
	       return '#NA';      
	              
	   };
	       
	// Execute construct and return object:		
	   this.construct($obj);
	   return this;
			   
	}
	
    
};
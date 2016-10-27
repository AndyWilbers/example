// js/_observation.js v1.0

$(document).ready(function() {
    
       
	    
 //	Bindings for update observation and two-way data-binding for type ce-check and ce-radio:
	$( document ).on( "click", '[data-ce*="type:radio"], [data-ce*="type:check"]', function(e){
	    
	    var $parent = $(this).parents('[data-observation-id]');
	
	//  Create an observation object:
	    var observation_object =  observation.registrate($parent);
	    if (observation_object == false ) {  return; }
	    
	//  Two-way data-binding: 
	    var value = observation_object.val();
	    
	    var $same = $('[data-observation-id="'+observation_object.id+'"]').find('[data-ce="value:'+value+'"]');
	    
	    if ( $(this).hasClass('checked') ){
	       $same.addClass('checked');
	    } else {
	      $same.removeClass('checked');
	    }
	   	
	
       //  Update $_SESSION:
	   observation.update(observation_object);
	   return;
	   
	} );
	
//	Two-way data-binding on value  on keyUp: 
	
	$( document ).on( "keyup", 'input[data-observation-id][type="text"]', function(e){ 
	    
	//  Create an observation object:
	    var observation_object =  observation.registrate($(this));
	    if (observation_object == false ) {  return; }
	    
	    var value = $(this).val();
	    $('input[data-observation-id="'+observation_object.id +'"][type="text"]').not(this).val(value);
	    return;
	   
	} );
	
	
//	Bindings for update observation type value and two-way data-binding:	
	$( document ).on( "change", 'input[data-observation-id][type="text"]', function(e){ 
	    
	//  Create an observation object:
	    var observation_object =  observation.registrate($(this));
	    if (observation_object == false ) {  return; }
	    
	    var value = observation.val();
	    $('input[data-observation-id="'+observation_object.id +'"][type="text"]').not(this).val(value);
	   
	//  Update $_SESSION:
	    observation.update(observation_object);
	    return;
	   
	} );
	    	
	return;
		
});


var observation = {
    
    
	registrate: function($obj){ 
	     
	   this.obj = $obj;
	    
	// Object should be a valid observation type:    
	   if ( this.obj.filter('[data-type]').size() !== 1 ) {
	       window.console.error('observation.registrate failed: no data-type attribute.');
	       return false;
	   }
	   var valid_types = ['value','radio','check'];
	   this.type =  this.obj.attr('data-type').toLowerCase().trim();
	   if (valid_types.indexOf( this.type) === -1 ) {
	       window.console.error('observation.registrate failed: no valid data-type attribute.');
	       return false;
	   }
	   
        // Object should have a valid data-observation-id
	   this.id = parseInt(this.obj.attr('data-observation-id'),10);
	   if ( this.id <1 || isNaN( this.id ) )  { 
	       window.console.error('observation.registrate failed: no valid data-observation-id attribute.');
	       return false;
	   }   
	   
	// Default, min and max value:
	   if (this.type.toString() === 'value') {
	       this.default_value 	= this.obj.attr('data-default').toLowerCase() !='null'? parseFloat(this.obj.attr('data-default'))	: null ;
	       this.min 		= this.obj.attr('data-min').toLowerCase() !='null'? parseFloat(this.obj.attr('data-min'))		: null ;
	       this.max 		= this.obj.attr('data-max').toLowerCase() !='null'? parseFloat(this.obj.attr('data-max'))		: null ;
	   }
	
	
	// Method val():
	   this.val = function(){
	       
	       var val 			= '#NA';
	       var type 		= this.type.toString();
	       var observation_id 	= 'observation-'+this.id.toString();
	       
	       switch (type) {
	       
	         case 'radio':
	         val = ce.radio.val(observation_id);
	         break;
	       
	         case 'check':
	         val = ce.check.val(observation_id);
	         break;
	       
	         case 'value':
	         //  Get value:
	             val = this.obj.val() != 'null' ? parseFloat(this.obj.val()) : null;
	             val = isNaN(val)? null: val;
	             
	         //  Set to default when null:
	             if (this.default_value !== null && val == null){
	        	 val = this.default_value;
		         this.obj.val(val);
		     }
	             
	         //  Clip min:
	             if (this.min !== null && val !== null){
	                val = val < this.min? this.min: val;
	                this.obj.val(val);
	             }
	             
	         //  Clip max:
	             if (this.max !== null && val !== null){
	                val = val > this.max? this.max: val;
	                this.obj.val(val);
	             }
	         break;
	       
	       }
	       return val;
	   };
	   
	
	   return this;
	},
	       	

//	Reset:	
	reset: function(){
	    var ask =  window.confirm("Weet u zeker dat u alle waarnemingen wilt wissen?");
	    if (ask == false ) {return;}
	   
	    if ($('html[data-sa]').eq(0).size()  != 1) { window.console.error( 'no html data-sa'); return;}
	    
	    var sa_html = sa.dataSA($('html').eq(0).attr('data-sa'));
	    var url =  sa_html.application+'/observation/reset';
	    
	    var ajax 	= new gen.ajax('');
	    var href = window.location.href.split('#');
	    
	    ajax.post({ url: url, data:{'redirect':  href[0]}, callback:'observation.reset_callback'});
	    return;
	},
	reset_callback: function(data){
	    if (debug=="on"){
		 window.console.info( 'observation.reset_callback');
		// window.console.log(JSON.stringify(data));
		// var ask =  window.confirm("Wilt u de pagina herladen?");
		// if (ask == false ) {return;}
	    }
	    if ( data.redirect != "#NA" ) {
	    	window.location.href = data.redirect;
	    }
	    return;
	   
	},
	

	
//	Update:	
	update: function(observation_object){
	    
	  if ($('html[data-sa]').eq(0).size()  != 1) { window.console.error( 'no html data-sa'); return;}
	  
	
	  var val = observation_object.val() ;
	  if ( val == '#NA') { window.console.error( 'no value'); return;}
	  var id = observation_object.id; 
	
	  var sa_html = sa.dataSA($('html').eq(0).attr('data-sa'));
	  var url 	=  sa_html.application+'/observation/update'; 
	  var ajax 	= new gen.ajax('');
	  ajax.post({ url: url, data:{'ID':id, 'val':val } });
	  return;
	}
	
    
};
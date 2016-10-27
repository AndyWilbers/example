//	_assets/js/_ce.js

//	Handling custom elements
	$(document).ready(function() {
	    

	 
	    
	    //	Bind UI-events:
	    	ce.bind();
		return;
	});



var ce = {
	
	//	Generic functionality:
    		bind: 	function(){ // call all UI-events binders:
    		    	ce.radio.bind();
    		    	ce.check.bind();
    		    	return;
    		    
    		},
		data: function (strData){ // convert content of data-ce atribute in an associative data object:
			
			var dataCE= strData.split(";");
			var data = {};
			for (var index = 0; index < dataCE.length; ++index) {
			   var d = dataCE[index].split(":");
			   data[d[0]]=d[1];
			}
			return data;
		},

		

	//	ce-radio:		
	  	radio:	{ //	ce-radio:
	  
            	  	    	bind:	function(){ 
            	  	    	   
            	  	    	    	// onClick:
        	  	    		   $( document ).on( "click taphold", ".ce-radio", function(e){ 
        	  	    		      
        	  	    		       var data =ce.data($(this).attr('data-ce'));
        	  	    		       if ($(this).hasClass('no-default') && $(this).hasClass('checked') ){
        	  	    			   $(this).removeClass('checked');
        	  	    		       } else {
        	  	    			   $('[data-ce*="name:'+data["name"]+';"]').removeClass('checked');
        	  	    			   $(this).addClass('checked');
        	  	    		   	}
         	  	    		
        	  	    		    } );
        	  	    		
        	  	    		   return;
                    	  			
            	  	    	},
                	  	val:function(strName){
                	  	    
                	  	    
                	  	   //	Select checked radio-button: 
                	  	    	$radioChecked = $('[data-ce*="name:'+strName+';"].checked');
                	  	    	if ($radioChecked.size() == 0) {return null;}
                	  	    	
                	  	   //	Read data-ce and return value:	
                	  	    	var data = ce.data($radioChecked.attr('data-ce')); 
                	  	    	return data['value'];
                	  	},
	  	    
	  	},
	
	//	ce-check:		
	  	check:	{ //	ce-check:
	  
            	  	    	bind:	function(){ 
            	  	    	    
            	  	    	    	// onClick:
        	  	    		   $( document ).on( "click", ".ce-check", function(e){ 
        	  	    		      $(this).toggleClass('checked');
        	  	    		    } );
        	  	    		$( document ).on( "taphold", ".ce-check", function(e){ 
      	  	    		      		$(this).toggleClass('checked');
        	  	    			} );
        	  	    		
        	  	    		   return;
                    	  			
            	  	    	},
                	  	val:function(strName){
                	  	    
                	  	   //	Select checked check-items: 
                	  	    	$checked = $('[data-ce*="name:'+strName+';"].checked');
                	  	    	
                	  	    	var result =  new Array();
                	  	    	$($checked).each( function(){
                	  	    	    var data = ce.data($(this).attr('data-ce')); 
                	  	    	    if (result.indexOf(data.value ) === -1) {
                	  	    		 result.push(data.value );
                	  	    	    }
                	  	    	});
                	  	    	
            
                	  	    	return result;
                	  	},
	  	    
	  	}
	
		

		
	
		
	
		
	


	
};

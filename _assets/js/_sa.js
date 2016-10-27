//	_assets/js/_sa.js

//	Handling script attribute
	$(document).ready(function() {
	    
	   
	   
	    //	Stop submit-behaviour of forms where submit is turned off by data-sa.
    	    	$( document ).on( "submit", 'form[data-sa*="submit:off;"]', function(e){ 
    		     e.preventDefault();
    	    	} );
    	    	
    	   //	Bind onClick events: 
    	    	$( document ).on( "click", '[data-sa*="onClick:"]', function(e){ 
    	    	
		     e.preventDefault();
		     var data =  sa.dataSA($(this).attr('data-sa'));
		     data['me'] = $(this);
		     gen.runFunction(data.onClick,data);
		    
	    	} );  
    	    	
    	    //	Bind onChange events: 
    	    	$( document ).on( "change", '[data-sa*="onChange:"]', function(e){ 
		     var data =  sa.dataSA($(this).attr('data-sa'));
		     data['me'] = $(this);
		     gen.runFunction(data.onChange,data);
	    	} );  
	
		
		return;
	});


var sa = {
		

		
//		Convert content of data-sa atribute in an associative data object:
		data: function(strdataSA){ return sa.dataSA(strdataSA);},
		dataSA: function (strdataSA){
			
			var dataSA= strdataSA.split(";");
			var data = {};
			for (var index = 0; index < dataSA.length; ++index) {
			   var d = dataSA[index].split(":");
			   data[d[0]]=d[1];
			}
			return data;
		},

//		Get fields in key=>value pairs, indicated by attribute data-sa*="group:...":		
		getFieldsByGroup: function(group) {
			
			//	Create empty object to collect field data:
				var fields = {};
				
				var group = $('[data-sa*="group:'+group+'"]');
				
			//	Stop and return empty object in case group doesn't excist on page:	
				if (group.size() == 0){ return fields;}
				
			//	Read fields	
				$(group).each(function(){
					
					
					//	Read data-sa attribute in array:
						var dataSA = fe.dataSA($(this).attr('data-sa'));
						
					//	Get value from objects containing the data-field:
						if ( dataSA.hasOwnProperty("data-field") ) {
							fields[dataSA["data-field"]] = fe.getValue($(this));
						}
				
				});
				
			//	Return result:
				return fields;
			
		},
		
		getValue: function(dataField){
			
			
			//	Get value, depending on type of input field:
				var type = $(dataField).attr("type");
			
			
				
			//	Overwrite type in case of data-sa type is set:
				var dataSA = fe.dataSA( $(dataField).attr("data-sa") );
				if (dataSA.hasOwnProperty("type")){
					type = dataSA["type"];
				}
				
			//	Return value:
				switch(type) {
				
					case   'radio':
							var name = $(dataField).attr('name');
							return $('[name="'+name+'"]').filter('[checked="checked"]').val();
							break;
				
							
					case   'html':
							return $(dataField).html();
							break;
				    
					default:
				    		return $(dataField).val();
				    		break;
				} 
			
		},
		
		checkOnPattern: function(dataField){
			
			/*	Check field with it's pattern. 
			 *	Returns "true" or "false" and add "pass" or "fail" class to field.
			 *	In case field doesn't contain a pattern: set no action  and return true.	
			 */
			
				
			//	Check if field has pattern attribute:
				if ( $(dataField).filter('[pattern]').size() !== 1 ) {
					return true;
				}
			//	Check if value is empty:
				if ( $(dataField).val() == "") {
					return true;
				}
				
			//	Remove "pass" / "fail" class:
				$(dataField).removeClass('pass').removeClass('fail');	
				
				
			//	Check on pattern:	
				var pat = $(dataField).attr('pattern');
				var re = new RegExp(pat);
				if (!$(dataField).val().match(re)) {
					
					$(dataField).addClass("fail");
					return false;
						
				} else {
					
					$(dataField).addClass("pass");
					return true;
				}

		},
		
		checkAllRequired:function(group){
			
			
			//	Check if all required fields contain a value:
				var result= true;
				$('[data-sa*="group:'+group+'"]').filter('.required').each( function(){
					$(this).removeClass('pass');
					$(this).removeClass('fail');
					if ( $(this).val() == "" ){
						$(this).addClass('fail');
						result= false;
					}else {
						
						//	Check on pattern:
							if (fe.checkOnPattern(this) ) {
								
								//	Pass: set pass class:
									$(this).addClass('pass');
							}else {
								
								//	Fail: set fail class:
									$(this).addClass('fail');
									
								//	Total failed:	
									result= false;
							}
					}
				});
				
				
			//	Return total result:	
				return result;
		}


	
};

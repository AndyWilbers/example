//	js/_gen.js


	

//	Functions for general purpose:
	var gen = {
			
		
	//	Run a function by name  for dynamic function call:	
		runFunction: function(functionName /*, args */) {
		    
        		var  context = window;
        		
        	    	var args = Array.prototype.slice.call(arguments, 1);
        	    	var namespaces = functionName.split(".");
        	    	var func = namespaces.pop();
        	
        	    	for (var i = 0; i < namespaces.length; i++) {
        	        	context= context[namespaces[i]];
        	    	}
        	  
        	    	return context[func].apply(context, args);
		},
		
	//	Check if value is set;
		isSet: function(val ){
			   return typeof val !== 'undefined' ? true : false;
		},	
		
	//	Assign default value in case variable is empty
		setDefault: function(val, valDefault){
			   return typeof val !== 'undefined' ? val : valDefault;
		},	
		
	//	Check on browser support:
		sup: {
			historyState: function() {
				
				var msg ="Browser doesn't support history management.";
				
				if (history.pushState) {
					if (window.onpopstate != undefined) {
					    return true;
					} else {
						window.console.log(msg);
					    return false;
					}
				} else { 
					window.console.log(msg);
					return false;
				}
			}
		},
		
	//	Add or replace parameter to an url string:
		setUrlParameter: function (strUrl, name, value) {
			
			//	Read current URL:
				var strUrl = strUrl.trim();
				
			//	Find '?' and '#' positions:
				var posParStart =  strUrl.indexOf('?',0);
				var posHashStart =  strUrl.indexOf('#',0);
				
			//	No parameter, no hash-tag: just add:
				if (posParStart < 0 && posHashStart < 0) {
					return strUrl+"?"+name.trim()+'='+value.trim();
				}
				
			//	In case of a hash-tag: split URL
				var hashTag = '';
				if (posHashStart> 0) {
					var parts 	= strUrl.split('#');
						strUrl 	= parts[0];
						hashTag = '#'+parts[1];
				}
			
			//	No parameter: add and glue hastag.
				if (posParStart < 0 ) {
					return strUrl+"?"+name.trim()+'='+value.trim()+hashTag;
				}
				
			
			//	In case parameter is not is already in URL: add to location.search.
				var posStart = strUrl.replace('?', '&').indexOf('&'+name.trim()+'=',0);
				if ( posStart<0) {
					return strUrl+'&'+name.trim()+'='+value.trim()+hashTag;
				
				}
				
			//	Replace value of parameter:
				var posEnd = strUrl.indexOf('&',posStart+1);
				if ( posEnd<0) {
					return strUrl.substring(0,posStart+1)+name.trim()+'='+value.trim()+hashTag;
				} else {
					return strUrl.substring(0,posStart+1)+name.trim()+'='+value.trim()+strUrl.substring(posEnd,strUrl.length-1)+hashTag;
				}	
		},
		
		getUrlParameter: function(url, name, defaultValue){
			
			
		
			//	Normalize url:
				var strUrl = url.trim();
					strUrl = strUrl.replace('?', '&');
					strUrl = strUrl.replace('#', '&');	
				
			//	Find parameter start and end point:
				var parameter = '&'+name.trim()+'=';
				var pos = strUrl.indexOf(parameter,0);
				if (pos < 0 ) { 
					if (gen.isSet(defaultValue) ){
						return defaultValue;
					} else {
						return false;
					}
				}
				
				var start =  pos+parameter.length;	
				var last =  strUrl.indexOf('&',start);	
			
			//	Return parameter:	
				if (last <0) {
					return strUrl.substring(start).trim();
				} else {
					return strUrl.substring(start, last).trim();
				}
				
			
		},
		
		deleteUrlParameter: function (strUrl, name, value) {
			
			//	Read current URL:
				var strUrl = strUrl.trim();
			
			// 	Normalize URL to get position of paramter
				var normUrl = strUrl.replace('?', '&');
					normUrl = normUrl.replace('#', '&');	
					
			//	Find parameter start and end point:
				var parameter = '&'+name.trim()+'=';
				var pos = normUrl.indexOf(parameter,0);
					if (pos < 0 ) { 
						return strUrl;
					}
				
				var start =  pos+parameter.length;	
				var last =  normUrl.indexOf('&',pos+1);	
			
			//	Return parameter:
				var newUrl =  strUrl.substring(0,pos).trim();
				if (last >0) {
					
					newUrl = newUrl+strUrl.substring(last).trim();
				}
				return newUrl;
				
		},
						
		timestamp: function() {
			var dd = new Date();
			return dd.getHours()+":"+dd.getMinutes();
		},
		
		preventDefault: function(e){
			e.preventDefault();
		},
		
		dataLog: function(data){
			window.console.log(JSON.stringify(data));
		},
		dataAlert: function(data){
			alert(JSON.stringify(data));
		},
		
		find_max_val: function(selector){
		    var max = null;
		    if  ( $(selector).size() == 0 ) { return null;}
		    
		    $(selector).each(function(){
			 if (   max === null ) {
			     max = parseInt($(this).val(),10);
			 } else {
			     var  this_max = parseInt($(this).val(),10);
			     max =   max <  this_max?  this_max : max;
			 }
		     });
		     return max;
		},
		
		find_min_val: function(selector){
		    var min = null;
		    
		    if  ( $(selector).size() == 0 ) { return null;}
		    
		    $(selector).each(function(){
			 if (   min === null ) {
			     min = parseInt($(this).val(),10);
			 } else {
			     var  this_min = parseInt($(this).val(),10);
			     min =   min >  this_min?  this_min : min;
			 }
		     });
		     return min;
		},
		
		
//		Class for ajax request:	
		ajax : function (root){
			
			
			//	Set properties:
				this.p = {
					root: gen.setDefault(root.trim()+'/',"")	
				};
			
			//	Methods:
				
			//	get(): handle a GET request.
				this.get = function(params){
				    
				    
				    	//	Unset 'me'
				    		if (params.data.hasOwnProperty('me')) {delete params.data.me;}
				    
				    
				    	//	Add url to data:
			    			params.data['url'] = params.url;
					
					//	Set paramters:
						var p = {
							data: 		gen.setDefault(params.data,{}),
							callback:	gen.setDefault(params.callback ,"#DEFAULT#")
						};
						
					//	Do Ajax request:
						$.ajax({
							url:		'front.php',
							type:		'GET',
							data: 		p.data,
							dataType: 	'json',
							cache: 		false,
							success:	function(data){
							    
								
								//	Stop in case no callback is set to "#SKIP#"
									if ( p.callback === "#SKIP#" ) { return; }	
								
								//	In case no callback is definied: show message with data
									if ( p.callback === "#DEFAULT#" ) {
										window.console.log("Respons from:'"+params.url +"':");
										window.console.log("inputData: "+JSON.stringify(p.data));
										window.console.log("responsData: "+JSON.stringify(data));
										return;
									}
									
									
								//	Check on error in response (if is set by server):
									if (gen.isSet(data.meta.error)) {
										if (parseInt(data.meta.error,10) < 0 ) {
											window.console.error("Request to '"+params.url +"' returned an error, ");
											window.console.error("input :"+JSON.stringify(p.data));
											window.console.log("responsData: "+JSON.stringify(data));
										}
									}
								
								//	Run callBack(data):
									data.inputData = p.data;
									gen.runFunction (p.callback.trim(),data);
									return;
									
								
							},		
							error: function(data){ 
								window.console.error("Ajax request to '"+params.url +"' failed, input:");
								window.console.error(JSON.stringify(p.data));
								window.console.error("Debug info:");
								window.console.error(data.responseText);
								return;
							}
						});
				
				}; // END get()
				
				//	post(): handle a POST request.
					this.post = function(params){
					    
					   
					    
						//	Unset 'me'
				    			if (params.data.hasOwnProperty('me')) {delete params.data.me;}
					    
					    	// 	Add url to data:
					    		params.data['url'] = params.url;
						
						//	Set paramters:
							var p = {
								data: 		gen.setDefault(params.data,{}),
								callback:	gen.setDefault(params.callback ,"#NONE#")
							};
							
						//	Do Ajax request:
							// window.console.info('post');
							$.ajax({
								url:		'front.php',
								type:		'POST',
								data: 		p.data,
								dataType: 	'json',
								cache: 		false,
								success:	function(data){
									
									//	Run callback only in case is definied:
										if ( p.callback !== "#NONE#" ) {
										
											//	Check on error in response (if is set by server):
												if (gen.isSet(data.meta.error)) {
													if (parseInt(data.meta.error,10) < 0 ) {
														window.console.error("Request to '"+params.url +"' returned an error, ");
														window.console.error("input :"+JSON.stringify(p.data));
														window.console.log("responsData: "+JSON.stringify(data));
													}
												}
												
											//	Run callBack(data):
												data.inputData = p.data;
												gen.runFunction (p.callback.trim(),data);
										}
										return;
									
								},		
								error: function(data){ 
									window.console.error("Ajax request to '"+params.url +"' failed, input:");
									window.console.error(JSON.stringify(p.data));
									window.console.error("Debug info:");
									window.console.error(data.responseText);
									return;
								}
							});
					
					}; // END post()
				
			//	Return instance of ajax class:
				return this;
				
		} // END Class ajax
		
		
		
	}; // END gen{}
	
	





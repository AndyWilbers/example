//	js/_extentions.js

//	Extentions on vanila Javascript:

//	String: 
	String.prototype.count = function(search) {
		
		//	Returns number of occurances of a sequence of characters in a string. 
		
			var m = this.match(new RegExp(search.toString().replace(/(?=[.\\+*?[^\]$(){}\|])/g, "\\"), "g"));
			return m ? m.length:0;
	}
	
	String.prototype.repeat = function( N )
	{
		//	Repeats as asting N times: 
    		return new Array( parseInt(N) + 1 ).join( this );
	}
	
//	Fallback for browsers not supporting trim() on a string:
	if (!String.prototype.trim) {
	      
		String.prototype.trim = function() {
			
			//	Regular expression to find space, cr, lf etc. at begin and end:
	        	var rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;
	        	
	        //	Return modified string:
	            return this.replace(rtrim, '');
	    };
	    
	}
	
// 	Production steps of ECMA-262, Edition 5, 15.4.4.14
// 	Reference: http://es5.github.io/#x15.4.4.14
	if (!Array.prototype.indexOf) {
	  Array.prototype.indexOfq = function(searchElement, fromIndex) {

	    var k;

	    if (this == null) {
	      throw new TypeError('"this" is null or not defined');
	    }

	    var o = Object(this);
	   
	    var len = o.length >>> 0;
	   
	    if (len === 0) {
	      return -1;
	    }

	    var n = +fromIndex || 0;

	    if (Math.abs(n) === Infinity) {
	      n = 0;
	    }

	    
	    if (n >= len) {
	      return -1;
	    }

	   
	    k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);

	    while (k < len) {
	      
	      if (k in o && o[k] === searchElement) {
	        return k;
	      }
	      k++;
	    }
	    return -1;
	  };
	}
	
//	isArray
	if (!Array.isArray) {
	    Array.isArray = function(arg) {
	      return Object.prototype.toString.call(arg) === '[object Array]';
	    };
	 }

//	Number:
	Number.prototype.addZeros= function(size) {
		   var n= this+"";
		   while (n.length < size) s = "0" + n;
		   return s;
	};
	

	

//	window.console: empty functions to prevent browsers without console from crach due to debug info
	if(!window.console){ window.console = {log: function(){}}; } 
	if (!window.console.warn) { window.console.warn = window.console.log;}
	if (!window.console.error) { window.console.error = window.console.log;}
	if (!window.console.info) { window.console.info = window.console.log;}
	

//	History object:
	/*
	 *	Add a search-parameter to the  browsers URL by pushing a new state to the History object,
	 *  using the history.pushState(stateObj, title,locationSearch) method.
	 *  see https://developer.mozilla.org/en-US/docs/Web/API/History_API.
	 *  
	 *  The parameter is given by a name, value pair. The current URL is read using location.search
	 *  and to this value the paramter is added or updated. This new loaction.search string is pushed
	 *  to the browser's history state-stack. 
	 *  
	 */
	history.pushParameter =  function (stateObj, title, name, value) {
				
	//	Read current URL parameters:
		var currentParams = location.search.trim();
		
	//	In case no parameters are set: create new location.search.
		if (currentParams.length<1) { 
			history.pushState( stateObj,title.trim(), '?'+name.trim()+'='+value.trim());
			return;
		}
		
	//	In case parameter is not is already in URL: add to location.search.
		var posStart = currentParams.replace('?', '&').indexOf('&'+name.trim()+'=',0);
		if ( posStart<0) {
			history.pushState( stateObj,title.trim(), currentParams+'&'+name.trim()+'='+value.trim());
			return;
		}
		
	//	Replace value of parameter:
		var posEnd = currentParams.indexOf('&',posStart+1);
		if ( posEnd<0) {
			history.pushState( stateObj,title.trim(),currentParams.substring(0,posStart+1)+name.trim()+'='+value.trim());
			return;
		} else {
			history.pushState( stateObj,title.trim(),currentParams.substring(0,posStart+1)+name.trim()+'='+value.trim()+currentParams.substring(posEnd,currentParams.length-1));
			return;
			
		}	
	}
	

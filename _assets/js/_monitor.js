//	_assets/js/_monitor.js

//	Handling custom elements
	$(document).ready(function() {
	    
	  
		return;
	});



var monitor = {
	

	setDebuglevel:function(data){
	    
	   //	Change debug level:
		var ajax = new gen.ajax('');
		ajax.post({ url:'monitor/ajax/setdebuglevel', data: { level: ce.radio.val('debuglevel') }});
		return;
	    
	}
	
			
	
		
	


	
}

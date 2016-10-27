/*	_assets/js/admin.docready.js*/


//	Document ready script:
	$(document).ready(function() {
	    
	 // setup for the observation editor
	    if (window.hasOwnProperty('observation')) { observation.setup();}

	    
	 // setup from handling for all forms with the id-attribute set:  
	    form.setup(); 
	    
	// setup for calculation editor
	    if (window.hasOwnProperty('calculation')) { calculation.setup();}
		
	return;
		
	});
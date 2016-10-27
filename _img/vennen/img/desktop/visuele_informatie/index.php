<?php
	// 	Meta information: 
	
		define(REL_PATH, 		"");
		define(TITLE, 			"Vennensleutel");
		define(DESCRIPTION, 	"Webapplicatie over beheer van vennen uitgegeven door de VBNE, samenstellers: Emiel Brouwer en Hein van Kleef.");
		define(KEYWORDS, 		"Vennen, venbeheer, ecosyteem, VBNE, OBN, Bosschap");
	
	//	Related files:	
		require_once REL_PATH."_factory/config.php";
		require_once REL_PATH."_factory/common.php";
		
	//	Create a controller instance:	
		$Fe = new_controller('Main');
		
	//	Hanndle request for html
		$Fe->html_request('fe');
			
?>
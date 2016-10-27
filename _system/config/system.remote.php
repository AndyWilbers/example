<?php	//	_system/config.remote.php

    
//	Database constants:
	define  ('DB_HOST',			'localhost');														//	Datbase Host
	define  ('DB_USER',			'natuurkenn_db1');							 						//	Database Account name
	define  ('DB_PASSWORD',		'OG8ZFVPfVW');														//	Password.
	define  ('DB_NAME',			'natuurkenn_db1');													//	Name of Database.
	define  ('ADMIN_MAIL',      'sleutels@natuurkennis.nl'); 					                    //  Email for sending password reset and new account".
	define  ('MIN_PASSWORD_LEN',	7); 		   						                			//  Minimuum numbers of characters of a password.
	define  ('TIMESLOT',	      48); 		   						            					//  Timeslot for reset password, new account in hours.
	
	define ('LANGUAGE', 		'nl');																// Langauage setting:
	define ('SERVER_PATH', 		'/home/natuurkenn/domains/natuurkennis.nl/public_html/sleutels/'); 	// Path to application root on the server.
	define ('HTTP',				'http://www.natuurkennis.nl/sleutels/');							// http to home;
	define ('MAX_IDLE_TIME', 	7200);																// Maximum time no user-activity (seconds);
	define ('UPLOAD_FILES', 	'jpeg;png;gif;svg');												// Filetypes allowed to be uploaded.;
	define ('MAX_FILES_SIZE', 	1500);							                					// Maximum file size to be uploaded (KB).;
	define ('IMG_SIZE_SR',      1024);																// Standard width for images in pixels (or heigth for portret)
	define ('IMG_SIZE_LR',      100);																// Low resolution width for images in pixels (or heigth for portret)
	
//	Constants for reading /writing CSV files
	define ('CSV_DELIMITER' 	,";" );
	define ('CSV_ENCLOSURE' 	,'"' );
	define ('CSV_ESCAPE' 		,"\\");


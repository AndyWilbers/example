<?php	//	_system/config.local.php

    
//	Database constants:
	define  ('DB_HOST',			'localhost');									//	Datbase Host
	define  ('DB_USER',			'root');										//	Database Account name
	define  ('DB_PASSWORD',		'root');										//	Password.
	define  ('DB_NAME',			'vbne');										//	Name of Database.
	define  ('ADMIN_MAIL',      'sleutels@natuurkennis.nl'); 					//  Email for sending password reset and new account".
    define  ('MIN_PASSWORD_LEN',	7); 		   						        //  Minimuum numbers of characters of a password.
    define  ('TIMESLOT',	      48); 		   						            //  Timeslot for reset password, new account in hours.
	
	define ('LANGUAGE', 		'nl');											// Langauage setting:
	define ('SERVER_PATH', 		'/Applications/MAMP/htdocs/VBNEsleutels/'); 	// Path to application root on the server.
	define ('HTTP',				'http://localhost:8080/VBNEsleutels/');			// http to home;
	define ('MAX_IDLE_TIME', 	7200);											// Maximum time no user-activity (seconds);
	
//	Constants for reading /writing CSV files
	define ('CSV_DELIMITER' 	,";" );
	define ('CSV_ENCLOSURE' 	,'"' );
	define ('CSV_ESCAPE' 		,"\\");


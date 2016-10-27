<?php	//	_system/system/db.php	
			defined('BONJOUR') or die;

class	Db extends mysqli {
	
	
	
	
	
/*	
	Connection to 'sleutel' database and custum methods.
*/		
			
			
//	Constructor:
	public	function __construct() {
		
			return parent::__construct(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
			
			
			
	} 	//	END __construct(). 
			
		
	
		
} // END class Db
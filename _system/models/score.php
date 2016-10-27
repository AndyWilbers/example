<?php	//	_system/models/score.php
			defined('BONJOUR') or die;		
			
				
class	modelScore extends  model {
	
	
	private $structure  = null;
  
	
		
	public function __construct() {
		
			
		
			parent::__construct();
			
	
			
			
		  	//	set active table to "image":
				$this->active_table_set('score');
				
	
			
			
			//  Default settings for a new active record:
				$this->ar_new['ID'] 		= "new";
			
		
			
	} // END __construct(). 
	


	
}
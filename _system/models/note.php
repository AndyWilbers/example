<?php	//	_system/models/note.php
			defined('BONJOUR') or die;		
			
				
class	modelNote extends  model {
	
	public 		$ar_new = array();

		
	public function __construct() {
		
		   $this->root_path = 'notes';
		
			parent::__construct();
			
		  	//	set active table to "note":
				$this->active_table_set('note');
				
			//	set category table:
				$this->categories = new table('note_cat');
				
			
			//  Default settings for a new active record:
				$this->ar_new['ID'] 		 	= "new";
				$this->ar_new['name'] 		 	= null;
				$this->ar_new['description'] 	= null;
				$this->ar_new['FID_CAT'] 	 	= -1;
				$this->ar_new['positon'] 	 	=  100;
				$this->ar_new['publish'] 	 	= -1;
				$this->ar_new['path'] 	 	    = '';
			
			
	} // END __construct(). 
	

	
} // END class modelNote
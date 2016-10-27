<?php	//	_system/models/reference.php
			defined('BONJOUR') or die;		
			
				
class	modelReference extends  model {
	
	public 		$ar_new = array();

		
	public function __construct() {
		
		   $this->root_path = 'references';
		
			parent::__construct();
			
		  	//	set active table to "reference":
				$this->active_table_set('reference');
				
			//	set category table:
				$this->categories = new table('reference_cat');
				
			
			//  Default settings for a new active record:
				$this->ar_new['ID'] 		 	= "new";
				$this->ar_new['name'] 		 	= null;
				$this->ar_new['description'] 	= null;
				$this->ar_new['FID_CAT'] 	 	= -1;
				$this->ar_new['positon'] 	 	=  100;
				$this->ar_new['publish'] 	 	= -1;
				$this->ar_new['path'] 	 	    = '';
			
			
	} // END __construct(). 
	
	public function get_records_by_category_all_fields($FID_CAT = -1, $published_only = true){
			
	//	Sanatize $FID_CAT:
		$FID_CAT = (int)$FID_CAT;
		$FID_CAT = $FID_CAT>0? $FID_CAT: -1;
			
	//	Check is category is published:
		if ($published_only && $FID_CAT>0) {
			$category = $this->categories->ar($FID_CAT);
			if ((int)$category['publish'] != 1 ){
				return array();
			}
		}
	
	//	Get records
		$options 				= array();
		$options['where']		= $published_only? ' `publish`="1" AND `FID_CAT`="'.$FID_CAT.'" ' : ' `FID_CAT`="'.$FID_CAT.'" ';
		$options['orderby']		= ' `ID`, `author`, `YYYY` DESC,`name`,`pages` ';
		return $this->active_table->select_all($options);
	
	}
	
} // END class modelReference
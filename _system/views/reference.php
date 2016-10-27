<?php	// _system/views/reference.php

class viewReference extends view {
	
	public function __construct($template = 'form.reference.html'){
		
		$this->has_category_record_structure 	= true;
		$this->label							= TXT_VBNE_LBL_REFERENCE;
		$this->label_plu 						= TXT_VBNE_LBL_REFERENCES;
		$this->root_path 						= 'references/';
		$this->record_name						= "reference";
		
		parent::__construct($template);
		
		$this->set_inputfield_path();
		
	}
	

	
	
}

<?php	// _system/views/note.php

class viewNote extends view {
	
	public function __construct($template = 'form.note.html'){
		
		$this->has_category_record_structure 	= true;
		$this->label							= TXT_VBNE_LBL_NOTE;
		$this->label_plu 						= TXT_VBNE_LBL_NOTES;
		$this->root_path 						= 'notes/';
		$this->record_name						= "note";
		
		parent::__construct($template);
		
		$this->set_inputfield_path();
		
	}
	

	
	
}

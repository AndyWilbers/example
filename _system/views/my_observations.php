<?php	//	_system/views/my_observations.php
			require_once VIEWS.'my_records.php';

class viewMyObservations extends viewMyRecords {
	
	public function __construct($template = 'form.my_observations.html'){
		
		$this->ext = 'obs';
		parent::__construct($template);
		
		
	}
	

	
	
}

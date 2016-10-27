<?php	//	_system/views/my_reports.php
			require_once VIEWS.'my_records.php';
			
			
class viewMyReports extends viewMyRecords {
	
	public function __construct($template = 'form.my_reports.html'){
		
		
		$this->ext = "rep";
		parent::__construct($template);
		
		
	}
	

	
	
}

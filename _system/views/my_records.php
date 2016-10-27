<?php	// _system/views/my_records.php

class viewMyRecords extends view {
	
	protected 	$ext = null;
	private 	$model_user = null;
	
	public function __construct($template){
		
		require_once MODELS.'user.php';
		$this->model_user = new modelUser();
		
		
		$overview = $this->html_overview();
		
		$this->addReplacement('table', $overview);
		parent::__construct($template);
		
		
	}
	
	private function html_overview(){
		
		$records = $this->ext== "rep"? $this->model_user->get_reports() : $this->model_user->get_observations();
		$records = $records['owner'];
		
		$this->addReplacement('ext', $this->ext);
		
		$html = array();
		
		$href = $this->ext== "rep"? PATH_.'my_reports?id=' : PATH_.'my_observations?id=';
		foreach ($records as $key=>$record){
			$record['href'] = $href.$record['ID'];
			$this->addReplacement('record', $record);
			$html[] = $this->getHtml('my_records_row.html');
		}
		if  (count($html) == 0) {
			$text = $this->fieldReplace(TXT_VBNE_NO_ITEMS, array('application'=>APPLICATION));
			return $this->wrapTag('p',$text);
		}
	
		$tbody = $this->wrapTag('tbody',$html);
		return $this->wrapTag('table',$tbody);
		
	}
	
    public function html_form($ID){
    	
    	$template  = $this->ext=="rep"? "form.my_report.html" : "form.my_observation.html";
    	
    	$hide = array();
    	$hide['desktop']  = $_SESSION['device'] === 'desktop'?' class="hide"' : '';
    	$hide['mobile']   = $_SESSION['device'] === 'mobile'?'  class="hide"' : '';
    		
    	
    	
    	$this->addReplacement('hide', $hide);
    	$record   = $this->ext=="rep"? $this->model_user->get_my_report($ID) : $this->model_user->get_my_observation($ID);
    	$viewers   = $this->ext=="rep"? $this->model_user->get_report_viewers($ID) : $this->model_user->get_observation_viewers($ID);
    	$this->addReplacement('record', $record);
    	
    	$rows = array();
    	foreach ($viewers as $viewer){
  
    		$viewer['ce_check'] = $viewer['check']? 'ce-check checked': 'ce-check';
    		$viewer['check'] = $viewer['check']? 1: -1;
    		
    		$this->addReplacement('viewer', $viewer);
    		$rows[] = $this->getHtml('form.my_viewers.row.html');
    	}
    	$this->addReplacement('viewers', $this->wrapTag('tbody',$rows));
    	return $this->getHtml($template);
    	
    }
	
	
}

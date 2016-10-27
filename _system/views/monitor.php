<?php	// _system/views/viewMonitor.php

class viewMonitor extends view {
	
	public function __construct(){
		
		parent::__construct('monitor.html');
		
		if ( array_key_exists('reset', $_REQUEST) ){
			unset($_SESSION[$_REQUEST['reset']]);
			header('Location: '.HOME.URL);
			exit;
		}
		
	//	Set current, active class:
		$activeSession 		= str_starts('monitor/session', ROUTE)? 								'class=" current active" ' : '';
		$activeServer		= str_starts('monitor/server', ROUTE)? 									'class=" current active" ' : '';
		$activeConstants	= str_starts('monitor/constants', ROUTE)? 								'class=" current active" ' : '';
		$activeMonitor 		= $activeSession.$activeServer.$activeConstants === ''? 				'class=" current active" ' : '';
		$this->addReplacement('activeSession',$activeSession);
		$this->addReplacement('activeServer',$activeServer);
		$this->addReplacement('activeConstants',$activeConstants);
		$this->addReplacement('activeMonitor',$activeMonitor);
		
	//	Create options for level:
		$levels = array();
		$currentLevel = isset($_SESSION['DEBUG'])? (int)$_SESSION['DEBUG']:-1;
		$levels['off'] = $currentLevel <=0 ? true: false;
		$levels[10] = $currentLevel === 10? true: false;
		$levels[100] = $currentLevel === 100? true: false;
		$levels[1000] = $currentLevel=== 1000? true: false;
		
		$options='';
		$glue='';
		foreach ($levels as $level => $checked){
			$this->addReplacement('label',$level);
			$value = (int)$level>0? (int)$level:-1;
			$this->addReplacement('value',$value);
			$checked = $checked? ' checked':'';
			$this->addReplacement('checked', $checked);
			$options .= $glue.$this->getHtml('monitor.logsetting.html');
			$glue= PHP_EOL;
		}
		$this->addReplacement('options',$options);
	
	}// END __construct
	
}

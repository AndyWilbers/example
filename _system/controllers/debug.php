<?php	//	_system/controllers/debug.php
			defined('BONJOUR') or die;
	

class controllerDebug extends controller {
		
	public function __construct(){
		
			parent::__construct();
			
			$env = ENV === "PRODUCTION"? '': strtolower(ENV);
			
			
		//	No action in case the url doesn't contain enough information:
		//	Block in case of production:
			if (count($this->request) <2) { 
				header('Location: http://www.natuurkennis.nl/sleutels/'.$env);
				exit;}
			if (ENV === "PRODUCTION"){ 
				header('Location: http://www.natuurkennis.nl/sleutels/'.$env);
				exit;}
				
			switch (strtolower($this->request[1]))	{
				case "on":
					$level = count($this->request) < 3? 		1000	: (int)$this->request[2];
					$level = in_array($level,[10,100,1000])? 	$level	: 1000;
					$_SESSION['DEBUG'] = $level;
				break;
				case "off":
					unset($_SESSION['DEBUG']);
				break;
				case "numbers":
					$status = count($this->request) < 3? 		"#NA"	: strtolower(trim($this->request[2]));
					$status = in_array($status,["on","yes","ja","aan","j","y","true"])? 	"on"	: $status;
					$status = in_array($status,["off","no","nee","uit","n","false"])? 	    "off"	: $status;
					if ($status === "on") {
						$_SESSION['numbers'] = "on";
					}
					if ($status === "off") {
						unset($_SESSION['numbers']);
					}
				break;
				case "reset":
					if (count($this->request) < 3) { 
						header('Location: http://www.natuurkennis.nl/sleutels/'.$env);
						exit;
						break;
					}
					$key = trim($this->request[2]);
					if (array_key_exists($key, $_SESSION)){
						unset($_SESSION[$key]);
					}
				break;
			}
			
			header('Location: http://www.natuurkennis.nl/sleutels/'.$env);
			exit;
			
		
	}
}
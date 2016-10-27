<?php	//	_system/models/system_info.php
			defined('BONJOUR') or die;		
			
			

class	modelSystemInfo extends  model {
		
/*	
	The menu model.
*/	
//	Properties:	

			
//	Constructor:	
	public function __construct() {
		
			parent::__construct();
			
	} // END __construct(). 

		
// 	Methods:
	/**
	 * 
	 * @return array:content of  $_SERVER 
	 */
	public function constants_server(){
		
		//	Read server constants:
			$constants = array();
			$get = $_SERVER;
			ksort($get);
			foreach ($_SERVER as $name=>$val){
				if (is_array($val) === true) {
					$val = implode('; ',$val);
				}
				$contant = array();
				$contant['name'] = $name;
				$contant['value'] = $val;
				$constants[] = $contant;
			}
			return $constants;
	}
	
	public function constants_app(){
		
		//	Read application defined constants:
			$constants = array();
			$get = get_defined_constants (true);
			ksort($get['user']);
			foreach ($get['user'] as $name=>$val){
				
				$contant = array();
				$contant['name'] = $name;
				$contant['value'] = htmlspecialchars($val);
				$constants[] = $contant;
			}
			return $constants;		
	}
	
	public function constants_session(){
		
		//	Read application defined constants:
		$constants = array();
		$get = $_SESSION;
		ksort($get);
		foreach ($get as $name=>$val){
			
			if ($name !== "LOG"){
				if (is_array($val) === true) {
					$val = print_r($val,true);
				}
				$contant = array();
				$contant['name'] = $name;
				$contant['value'] = htmlspecialchars($val);
				
				$attr = array();
				$attr['class'] = 'btn';
				$attr['href'] = '?reset='.$name;
				$attr['target'] = '_self';
				
				
				$view = new view();
				$contant['reset'] = $view->wrapTag('a','remove', $attr);
				$constants[] = $contant;
			}
		}
		return $constants;
		
	}

	public function log(){
	
		//	Read logging:
			$constants = array();
			if ( isset($_SESSION['LOG'] ) === false) { return $constants; }
			foreach ($_SESSION['LOG'] as $val){
		
				$contant = array();
				$contant['log'] = htmlspecialchars($val);
				$constants[] = $contant;
			}
			return $constants;
	}
	
	public function clearLog(){
	
		//	Reset  logging:
			if ( isset($_SESSION['LOG'] ) === false) { return true; }
			
			unset($_SESSION['LOG']);
			return true;
	}
	
		
} // END class modSystemInfo
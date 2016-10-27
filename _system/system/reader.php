<?php	//	_system/system/reader.php
			defined('BONJOUR') or die;
			
		

class	reader extends  common {
	

	public  $msg 	= '';
	public 	$name	= null;
	public  $raw_files  = null;
    
    public function __construct() {
    	
    	$this->name = $this->my_name();
    	parent::__construct();
    	
    	
    	return;
    }
	
 	public function get_raw_files(){
 	
 			if ($this->raw_files === null ){
 					
 				$handle = dir(CSV_RAW.strtolower(APP));
 				while (false !== ($file_name = $handle->read())) {
 						
 					if (strtolower(substr($file_name, -4) ) == ".csv") {
 						$key = substr(strtolower($file_name), 0,strlen($file_name)-4 );
 						$this->raw_files[$key] = $key.'.csv';
 					}
 				}
 				$handle->close();
 					
 			}
 			return $this->raw_files;
 		
 	}
 	
 	public function get_raw_file($file_name){
 	
 	//	Check if reequested filename excists:
 		if (!file_exists(CSV_RAW.strtolower(APP).DS.$file_name) ) {
 			$this->msg = 'File  &quot;'.CSV_RAW.strtolower(APP).DS.$file_name.'&quot; does not excists.';
 			return false;
 		}
 	
 	//	[2] Load file into array:
 		$rows = array();
 		$handle = @fopen(CSV_RAW.strtolower(APP).DS.$file_name, "r");
 		if ($handle) {
 			while (($buffer = fgets($handle, 4096)) !== false) {
 				$rows[] = str_getcsv ( $buffer ,  CSV_DELIMITER , CSV_ENCLOSURE , CSV_ESCAPE  );
 			}
 			if (!feof($handle)) {
 				$this->msg = 'File  &quot;'.$file_name.'.csv&quot; can not be processed.[2]';
 				return false;
 			}
 			fclose($handle);
 		}
 		return  $rows;
 	
 	}
 	/**
 	 * This flow should be overwritten in the child
 	 * @return pretty_print html of all availalve raw files:
 	 */
 	public function flow(){
 		
 		$raw_files = array();
 
 		foreach ($this->raw_files as $name =>$file_name) {
 			$file = $this->get_raw_file($file_name);
 			if ($file === false) {
 				return false;
 			}
 			$raw_files[$name] = $file;
 		}
 		
 		
 		return array_pretty_print($raw_files);
 		
 	}
 	
}
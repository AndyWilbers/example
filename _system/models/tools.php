<?php	//	_system/models/tools.php
			defined('BONJOUR') or die;		
			
				
class	modelTools extends  model {
	

	private $csv_files  				= null;
	public $system_tables				= array();
	public $system_table_descriptions	= array();
	private $default_article_cat		= array();
	
	private $application_tables 		= array();
	private $dir_export                 = null;
	private $application_table_ids      = array();
	private $FID      					= null;
	
	public function __construct() {
		$this->root_path = 'tools';
		
	//	Define system tables:
		$this->system_tables['article'] 			= 1;
		$this->system_tables['article_cat'] 		= 1;
		$this->system_tables['observation'] 		= 1;
		$this->system_tables['observation_cat'] 	= 1;
		$this->system_tables['calculation'] 		= 1;
		$this->system_tables['calculation_cat'] 	= 1;
		$this->system_tables['reference'] 			= 1;
		$this->system_tables['reference_cat'] 		= 1;
		$this->system_tables['note'] 				= 1;
		$this->system_tables['note_cat'] 			= 1;
		$this->system_tables['image'] 				= 1;
		
	
	//	Define default article_cat:
		$row = array();
		
		$row['name'] 		= '"Inoud"';
		$row['path'] 		= '"inoud"';
		$row['PARENT_ID'] 	= -1;
		$row['position'] 	= 100;
		$row['top'] 		= 1;
		$row['publish'] 	= -1;
		$row['APP'] 		= '"'.APP.'"';
		$this->default_article_cat[]=$row;
		
		$row['name'] 		= '"Toelichting bij vragen"';
		$row['path'] 		= '"toelichting_bij_vragen"';
		$row['PARENT_ID'] 	= -1;
		$row['position'] 	= 200;
		$row['top'] 		= 'NULL';
		$row['publish'] 	= -1;
		$row['APP'] 		= '"'.APP.'"';
		$this->default_article_cat[]=$row;
		
		$row['name'] 		= '"Toelichting bij sleutels"';
		$row['path'] 		= '"toelichting_bij_sleutels"';
		$row['PARENT_ID'] 	= -1;
		$row['position'] 	= 300;
		$row['top'] 		= 'NULL';
		$row['publish'] 	= -1;
		$row['APP'] 		= '"'.APP.'"';
		$this->default_article_cat[]=$row;
		
		$row['name'] 		= '"Sleutel resultaten"';
		$row['path'] 		= '"sleutel_resultaten"';
		$row['PARENT_ID'] 	= -1;
		$row['position'] 	= 400;
		$row['top'] 		= 'NULL';
		$row['publish'] 	= -1;
		$row['APP'] 		= '"'.APP.'"';
		$this->default_article_cat[]=$row;
		
		
		parent::__construct();	
		
	//	Get next ID of the system tables:
		foreach ($this->system_tables as $table=>$next_id){
			$sql = 'ALTER TABLE `'.$table.'` AUTO_INCREMENT = 1';
			$this->db->query($sql);
			$sql = 'SELECT MAX(`ID`)+1 AS `NEXT_ID`  FROM `'.$table.'`	 LIMIT 1';
			$result = $this->select($sql);
			if ($result[0]['NEXT_ID'] !== null) {
				$this->system_tables[$table] = (int)$result[0]['NEXT_ID'];
			}
		}
		
	//	Definition of application_tables (for in- and export of an application:
		$this->application_tables['article']		= ['cat'];
		$this->application_tables['observation']	= ['cat', 'opt'];
		$this->application_tables['calculation']	= ['cat', 'inp', 'act','alg','sco'];
		$this->application_tables['route_disable']	= [];
		$this->application_tables['score']			= [];
		$this->application_tables['note']		    = ['cat'];
		$this->application_tables['reference']		= ['cat'];
		$this->application_tables['image']		    = [];
	}
 
/**
	*  Reads '.csv' filenames available in '_csv/import'.
	*  @return array with file_name=>file_name.csv
	*/
	public function csv_get_files(){
		if ($this->csv_files === null ){
			
			$handle = dir(CSV_IMPORT);
			while (false !== ($file_name = $handle->read())) {
					
				if (strtolower(substr($file_name, -4) ) == ".csv") {
					$key = substr(strtolower($file_name), 0,strlen($file_name)-4 );
					$this->csv_files[$key] = $key.'.csv';
				}
			}
			$handle->close();
			
		}
		return $this->csv_files;
	}
	
/**
 	* Reads the csv-file "file_name" into an array();
 	* @return array with content. | false incase the file doesn't excists.array
 	*/
	public function csv_get_file($file_name){
		
	//	Check if reequested filename excists:
		if (!file_exists(CSV_IMPORT.$file_name.'.csv') ) { 
			$this->msg = 'File  &quot;'.$file_name.'.csv&quot; does not excists.';
			return false; 
		}
		
		
	//	[1] Create model to insert:
		$parts = explode('_',$file_name);
		$model_file_name 	=  MODELS.$parts[0].'.php';
		$model_name 		= 'model'.ucfirst($parts[0]);
		if ( !file_exists($model_file_name) ) {
			$this->msg = 'File  &quot;'.$file_name.'.csv&quot; can not be processed.[1]';
			return false;
		}
		require_once $model_file_name;
		$model = new $model_name();
		
	//	[2] Load file into array:
		$rows = array();
		$handle = @fopen(CSV_IMPORT.$file_name.'.csv', "r");
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
		
		$result = array();
		$result['model'] = $model;
		$result['rows'] = $rows;
		return $result;
		
	}

	public function delete_application(){
	//@todo: conver to all application_tables
		
	//	Delete loop:
		foreach ($this->system_tables as $table =>$next_id) {
			$sql = 'DELETE FROM `'.$table.'` WHERE `APP`="'.APP.'"';
			$this->addLog($sql,100);
			$result = $this->db->query($sql);
			if ($result === false) {
				$this->addLog('DELETE '.$table.' records failed'. $this->db->error,1);
				$this->msg =$this->db->error;
				return false;
			}
			$sql = 'ALTER TABLE `'.$table.'` AUTO_INCREMENT = 1';
			$this->db->query($sql);
			$sql = 'SELECT MAX(`ID`)+1 AS `NEXT_ID`  FROM `'.$table.'`	 LIMIT 1';
			$result = $this->select($sql);
			if ($result[0]['NEXT_ID'] !== null) {
				$this->system_tables[$table] = (int)$result[0]['NEXT_ID'];
			}
		}
	
	//	Insert default article categrories:
		foreach ($this->default_article_cat as $row) {
			$arr_columns = array();
			$arr_values  = array();
			foreach ($row as $column=>$value){
				$arr_columns[] = $column;
				$arr_values[]  = $value;
			}
			$columns = implode('`, `', $arr_columns);
			$values  = implode(', ', $arr_values);
			$sql = 'INSERT INTO `article_cat` (`'.$columns.'`) VALUES ('.$values.')';
			$this->addLog($sql,100);
			$result = $this->db->query($sql);
		}
		$this->system_tables['article_cat'] = $this->db->insert_id;
		
		$this->msg = $this->fieldReplace(TXT_TOOLS_DELETE_SUCCESS,array('application'=> APPLICATION));
		return $this->system_tables;
	}
	
	public function tables(){
		
		$sql 		= 'SELECT `TABLE_NAME` FROM information_schema.tables WHERE table_schema = "'.DB_NAME.'"';
		
		$records    = $this->select($sql);
	    return $records === false? array(): $records;
		
	}
	
	
	public function csv_export_application_tables(){
		
	//	Create directory for export in case not alreade excist:
		$this->dir_export = CSV_EXPORT.DS.APP;
		if (!file_exists($this->dir_export)) { mkdir($this->dir_export, 0777, true);}
		
	//	$result: array name=>href
		$result = array();
	
		foreach ($this->application_tables as $name=>$post_fixes){
		//	Create export of base table:
			if ($this->csv_export_table_with_app_field($name) ){
				$result[$name] = CSV_EXPORT_HREF.APP.'/'.$name.'.csv';
			}
			foreach ($post_fixes as $pfx){
				$name_pfx = $name.'_'.$pfx;
				switch ($pfx){
					case 'cat':
						if ($this->csv_export_table_with_app_field($name_pfx, false) ){
							$result[$name_pfx] = CSV_EXPORT_HREF.APP.'/'.$name_pfx.'.csv';
						}
					break;
					
					default:
					if (count($this->application_table_ids)>0){
						if ($this->csv_export_table_with_ids($name_pfx) ){
							$result[$name_pfx] = CSV_EXPORT_HREF.APP.'/'.$name_pfx.'.csv';
						}
					}
					break;
				}
			}
		}
		return $result;
	}
	
	private  function csv_export_table_with_app_field($name, $base_table = true){
		
	//	Read fields form table:	
	    $name = strtolower(trim($name));
	   
	//	Get export file    
		$sql = 'SELECT * FROM `'.$name .'` WHERE APP="'.APP.'"';
		$RS = $this->select($sql);
		$header =  array();
		$fill_header = true;
		if ($base_table) {
				$this->application_table_ids= array();
				$this->FID = 'FID_'.strtoupper($name);
		}
		foreach ($RS as $key => $record) {
			foreach ($record as $field_name=>$field_value) {
				if ($fill_header) {
					$header[] =$field_name;
				}
				if ( in_array($field_name, $this->html_encoded_fields)) {
					$RS[$key][$field_name]  = html_entity_decode($field_value, ENT_HTML5);
				}
				if (is_null($field_value)){
					$RS[$key][$field_name]  = 'NULL';
				}
				
			}
			if ($base_table) {
				$this->application_table_ids[] =$record['ID'];
			}
			$fill_header = false;
		}
		
	//	Add fieldnames as first row:
		array_unshift($RS, $header);
		$name = $name.'.csv';
		
	//	Create csv file:
		$fp = fopen($this->dir_export.DS.$name, 'w');
		foreach ($RS as $fields) {
			fputcsv($fp, $fields,CSV_DELIMITER, CSV_ENCLOSURE, CSV_ESCAPE);
		}
		fclose($fp);
		return true;
	}
	
	private  function csv_export_table_with_ids($name){
	
	//	Read fields form table:
		$name = strtolower(trim($name));
		
	//	Build "IN":
	    $IN = '`'.$this->FID.'` IN('.implode(',' ,$this->application_table_ids).')';
	
	//	Get export file
		$sql = 'SELECT * FROM `'.$name .'` WHERE '.$IN;
		$RS = $this->select($sql);
		$header =  array();
		$fill_header = true;
		
		foreach ($RS as $key => $record) {
			foreach ($record as $field_name=>$field_value) {
				if ($fill_header) {
					$header[] =$field_name;
				}
				if ( in_array($field_name, $this->html_encoded_fields)) {
					$RS[$key][$field_name]  = html_entity_decode($field_value, ENT_HTML5);
				}
				if (is_null($field_value)){
					$RS[$key][$field_name]  = 'NULL';
				}
	
			}
			
			$fill_header = false;
		}
	
	//	Add fieldnames as first row:
		array_unshift($RS, $header);
		$name = $name.'.csv';
	
	//	Create csv file:
		$fp = fopen($this->dir_export.DS.$name, 'w');
		foreach ($RS as $fields) {
			fputcsv($fp, $fields,CSV_DELIMITER, CSV_ENCLOSURE, CSV_ESCAPE);
		}
		fclose($fp);
		return true;
	}
	
	
} // END class modelTools
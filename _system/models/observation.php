<?php	//	_system/models/observation.php
			defined('BONJOUR') or die;		
			
				
class	modelObservation extends  model {
	
	public 		$ar_new = array();

		
	public function __construct() {
		
		   $this->root_path = 'observations';
		
			parent::__construct();
			
		  	//	set active table to "observation":
				$this->active_table_set('observation');
				
			//	set category table:
				$this->categories = new table('observation_cat');
				
			
			//  Default settings for a new active record:
				$this->ar_new['ID'] 		 	= "new";
				$this->ar_new['name'] 		 	= "";
				$this->ar_new['description'] 	= null;
				$this->ar_new['type'] 		 	= 'value';
				$this->ar_new['min'] 		 	= null;
				$this->ar_new['max'] 		 	= null;
				$this->ar_new['default_value'] 	= null;
				$this->ar_new['FID_CAT'] 	 	= -1;
				$this->ar_new['positon'] 	 	=  100;
				$this->ar_new['publish'] 	 	= -1;
				$this->ar_new['href'] 	 	    = '';
			
			
	} // END __construct(). 
	
	public function get_ar($ID = null){
		
		
		//	Get records from base table:
			$ID = (int)$ID;
			if ($ID < 1 ) { return $ar; }
			$ar = parent::get_ar($ID);
			if (!array_key_exists('ID', $ar))  {return $ar;}
			if ($ar['ID']  != $ID) {return $ar;}
			
		//	Get related options:
			$options = $this->options_get($ID,true);
			if ( count($options) == 0 ) { return $ar;}
			$row = 100;
			foreach ($options as $opt) {
				foreach ($opt as $name => $value) {
					switch ($name){
						
						case "FID_OBSERVATION": 
							break;
						
						case "is_default":
							if ($value === "yes") { $ar[$name] = $row;}
							break;
							
						default:
							$ar['opt_'.$name.'_'.$row] = $value;
							break;
					}
				}
				$row+=100;
			}
			return $ar;
		
	}
	
	public function options_get($FID = null){
		
	//	Check FID:
		if ($FID === null) { return array();}
		$FID  = (int)$FID;
		if ( $FID <= 0 ) { return array();}
		
	//	Get records:
		$where =  'WHERE `FID_OBSERVATION`="'.$FID.'"';
		$sql = 	' SELECT *
				  FROM `observation_opt` '
				.$where.
				' ORDER BY `position`, `name`';
		
		return $this->select($sql);
		
	}
	
	public function save_ar($fields = array(),$ID = null ){
		
		if ( is_array($fields) === false ) { return false; }
		
	//	Get fields for options and unset in "fields" array since this are not fields in the main table:
		$options = array();
		if ( array_key_exists('options', $fields) ){
	    	$options = $fields['options'];
	    	unset($fields['options']);
		}
		
		$is_default_row = 100;
		if ( array_key_exists('is_default', $fields) ){
			$is_default_row = $fields['is_default'];
			unset($fields['is_default']);
		}
		
	//	Save active record in main table:	
		$check = parent::save_ar($fields, $ID);
		if ( $check === false ) {return false;}
		
		$record = $this->ar();
	
	//	Stop in case type ="value" since for this type options are not relevant.
		if ($record['type'] == "value") { return true;}
		
		
	//	Remove excisting options for this record;
		$sql = 'DELETE FROM `observation_opt` WHERE `FID_OBSERVATION` = "'.$record['ID'].'"';
		$this->addLog($sql,100);
		$result = $this->db->query($sql);
		if ($result === false) {
			$this->addLog('DELETE observation_opt records failed'. $this->db->error,1);
			$this->last_sql_error = $this->db->error;
			return false;
		}
		
	//	Stop in case no options are set:
		if ( is_array($options) === false ) { return false; }
		if (count( $options) == 0 ) { return true; }
		

	//	Sort options by 'position':
		$positions = array();
		foreach ($options as $row => $opt){
			$positions[$opt['position']] = $row;
		}
		ksort($positions,SORT_NUMERIC);
	
	//	INSERT new option records:
		$sql = 'INSERT INTO `observation_opt` (`FID_OBSERVATION`, `name`, `position`, `value`, `is_default`)
				VALUES ';
		$pos = 0;
		$glue='';
		foreach ($positions as $row) {
			$opt 	= $options[$row];
			$pos    = $pos+100;
			$name 	= array_key_exists('name', $opt)? htmlspecialchars(trim($opt['name']), ENT_HTML5, "UTF-8")  : 'Name'.$pos;
			if ( strlen($name) == 0) {$name = 'Name'.$pos;}
			$value  = (float)$opt['value'];
			$is_default = 'NULL';
			if ($row == $is_default_row) { 
				$is_default = '"yes"';
			}
			$sql .= $glue.' ("'.$record['ID'].'","'.$name.'","'.$pos .'","'.$value.'",'.$is_default.')';
			$glue = ',';
		}
		$this->addLog($sql,100);
		$result = $this->db->query($sql);
		if ($result === false) {
			$this->addLog('INSERT observation_opt records failed'. $this->db->error,1);
			$this->last_sql_error = $this->db->error;
			return false;
		}
		
		return true;
		
	}

	/**
	 * Returns an associative array with fields and fields of options for a set of observations
	 * @param array $rI with observation ID's
	 * @return array [FID_OBSERVATION] => 	{ 
	 * 											fields,
	 * 											[options] => {} 
	 * 										}
	 */
	public function get_by_ids($in){
	
	//	Result array:
		$rR = array();
		
	//	Check parameter $IN and build IN 
		if ( !is_array($in) 		) { return $rR;}
		if ( count($in) == 0	) { return $rR;}
		$IN = '"'.implode('", "',$in).'"';
		
	//	Get observations:
		$sql  = 	' SELECT DISTINCT *
				  	  FROM `observation`
				      WHERE `ID` IN('.$IN.')
				      ORDER BY `ID`, `position`, `name` ';
		$observations = $this->select($sql);
		if (count($observations) == 0) { return $rR; }
		
	//	Get options:
		$sql  = 	' SELECT DISTINCT *
				  	  FROM `observation_opt`
				      WHERE `FID_OBSERVATION` IN('.$IN.')
				      ORDER BY `FID_OBSERVATION`, `position`, `name` ';
		$result= $this->select($sql);
		$options = array();
		if ( count($result) > 0 ) {
			foreach ($result as $row) {
				if ( !array_key_exists($row['FID_OBSERVATION'], $options) ){
					$options[$row['FID_OBSERVATION']] = array();
				}
				$options[$row['FID_OBSERVATION']][$row['name']] = $row;
			}
		}
		
	//	Build $rR and return
		foreach ($observations as $row) {
		 	$rR[$row['ID']] = $row;
		 	$rR[$row['ID']]['options'] =  array_key_exists($row['ID'], $options)? $options[ $row['ID'] ] : array();
		}
		return $rR;
	}
	
} // END class modelArticle
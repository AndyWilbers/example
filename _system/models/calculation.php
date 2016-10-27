<?php	//	_system/models/calculation.php
			defined('BONJOUR') or die;		
			
				
class	modelCalculation extends  model {
	
	public 		$ar_new = array();
    private     $article = null;
    
    private     $error = array();
   
		
	public function __construct() {
		
		   $this->error_reset();	
		   
		   $this->return_path = HOME_.'sleutels/calculaties/';
		
		   $this->root_path = 'calculations';
		
			parent::__construct();
			
		  	//	set active table to "observation":
				$this->active_table_set('calculation');
				
			//	set category table:
				$this->categories = new table('calculation_cat');
				
			//	Article table:
				$this->article = new table('article');
				
			
			//  Default settings for a new active record:
				$this->ar_new['ID'] 		= "new";
				$this->ar_new['name'] 		= "";
				$this->ar_new['FID_CAT'] 	= -1;
				$this->ar_new['publish'] 	= -1;
				$this->ar_new['inputs'] 	= array();
				$this->ar_new['rules']   	= array();
				$this->ar_new['scores'] 	= array();
			
			
	} // END __construct(). 
	
	private function error_reset(){
		
		$this->error['message'] = TXT_ERR_UNKWOWN;
		$this->error['field']   = null;
		return $this->error;
		
	}
	
	public function  get_error(){
		return $this->error;
	}
	
	public function new_inp( $record = array() ){
		
		$aR = $record;
		
		$record = array();
		$record['position'] 			= array_key_exists('pos', $aR)? (int)$aR['pos'] 							: 100;
		$record['input'] 				= array_key_exists('input', $aR)? trim($aR['input']) 						: null;
		$record['description'] 			= array_key_exists('description', $aR)? trim($aR['description']) 			: null;
		
		$record['actions']  = array(); //@todo"handle actions send by client".
		
		$FID_OBSERVATION 				= array_key_exists('FID_OBSERVATION', $aR)? (int)($aR['FID_OBSERVATION'])	:-1;
		$record['FID_OBSERVATION'] 		= $FID_OBSERVATION> 0 ? $FID_OBSERVATION: null;
		
		require_once MODELS.'observation.php';
		$observation = new modelObservation();
		
		if ( !is_null($record['FID_OBSERVATION']) ){
			$record['observation'] = $observation->get_ar_with_path_to_record($record['FID_OBSERVATION']);
			
		} else {
			$record['observation'] =$observation->ar_new;
			$record['observation']['name'] 	= TXT_VBNE_LBL_NO_OBSERVATION;
			$record['observation']['href'] 	= '';
		}
		
		return $record;
	}
	
	public function new_act( $record = array()  ){
		
		$aR = $record;
	
		$record =array();
		$record['position'] 				= array_key_exists('pos', $aR)? (int)$aR['pos'] 									: 100;
		$record['type'] 					= array_key_exists('type', $aR)?  strtolower(trim($aR['type']))						: 'message';
		
		//	rule:
			$rule 	= array_key_exists('rule', $aR)? strtoupper(trim($aR['rule']))	    : null;
			$params	= array_key_exists('params', $aR)? strtoupper(trim($aR['params']))	: '';
			$input	= array_key_exists('input', $aR)? strtoupper(trim($aR['input']))	: null;
			if ( !is_null($rule) ) {
				$rule .= '('.$params.')';
				$rule .= is_null($input)? '' :    ':'.$input;
			}
			$record['rule'] = $rule;
		
		
		$record['description'] 				= array_key_exists('description', $aR)? (int)$aR['description'] 					: '';
		
		$FID_ARTICLE 						= array_key_exists('FID_ARTICLE', $aR)? (int)($aR['FID_ARTICLE'])	:-1;
		$record['FID_ARTICLE'] 				= $FID_ARTICLE> 0 ? $FID_ARTICLE: null;
		
		$FID_CALCULATION_NEXT 				= array_key_exists('FID_CALCULATION_NEXT', $aR)? (int)($aR['FID_CALCULATION_NEXT'])	:-1;
		$record['FID_CALCULATION_NEXT'] 	= $FID_CALCULATION_NEXT> 0 ? $FID_CALCULATION_NEXT: null;
	
	// 	Additional fields for FID_ARTICLE  and FID_CALCULATION_NEXT:
		if ( !is_null($record['FID_ARTICLE']) || !is_null($record['FID_CALCULATION_NEXT'] ) ){
			
			require_once VIEWS.'calculation.php';
			$view_calculation = new viewCalculation();
		
			if ( !is_null($record['FID_ARTICLE']) ){	
				require_once MODELS.'article.php';
				$model_article = new modelArticle();
				$record = $view_calculation->add_article_fields($record, $model_article);
					
			}
			
			if ( !is_null($record['FID_CALCULATION_NEXT']) ) {
				$record = $view_calculation->add_calculation_fields($record, $this );
			}
			
		}
		
		return $record;
	}
	
	public function new_alg( $record = array() ){
		
		$aR = $record;
	
		$record =array();
		$record['position'] 				= array_key_exists('pos', $aR)? (int)$aR['pos'] 									: 100;
		$record['rule'] 					= array_key_exists('rule', $aR)? trim($aR['rule'] )					                : null;
		$record['params'] 					= array_key_exists('params', $aR)?trim($aR['params']) 								: null;
		$record['calculation'] 				= array_key_exists('calculation', $aR)? strtoupper(trim($aR['calculation'])	)		: '#NA';
		
		$FID_ARTICLE 						= array_key_exists('FID_ARTICLE', $aR)? (int)($aR['FID_ARTICLE'])	:-1;
		$record['FID_ARTICLE'] 				= $FID_ARTICLE> 0 ? $FID_ARTICLE: null;
		
		$FID_CALCULATION_NEXT 				= array_key_exists('FID_CALCULATION_NEXT', $aR)? (int)($aR['FID_CALCULATION_NEXT'])	:-1;
		$record['FID_CALCULATION_NEXT'] 	= $FID_CALCULATION_NEXT> 0 ? $FID_CALCULATION_NEXT: null;
	
	// 	Additional fields for FID_ARTICLE  and FID_CALCULATION_NEXT:
		if ( !is_null($record['FID_ARTICLE']) || !is_null($record['FID_CALCULATION_NEXT']) ){
			
			require_once VIEWS.'calculation.php';
			$view_calculation = new viewCalculation();
		
			if ( !is_null($record['FID_ARTICLE']) ){	
				require_once MODELS.'article.php';
				$model_article = new modelArticle();
				$record = $view_calculation->add_article_fields($record, $model_article);
					
			}
			
			if ( !is_null($record['FID_CALCULATION_NEXT']) ) {
				$record = $view_calculation->add_calculation_fields($record, $this );
			}
			
		}
		return $record;
	}
	
	public function new_sco( $record = array() ){
		
		$aR = $record;
	
		$record =array();
	
		$record['GE'] 							= null;
		if (array_key_exists('GE', $aR)) {
			$GE = $aR['GE'];
			$record['GE'] = is_numeric($GE)? (float)$GE: null;
		}
		
		$record['LT'] 							= null;
		if (array_key_exists('LT', $aR)) {
			$GE = $aR['LT'];
			$record['LT'] = is_numeric($GE)? (float)$GE: null;
		}
		$FID_SCORE 						= array_key_exists('FID_SCORE', $aR)? (int)($aR['FID_SCORE'])	:-1;
		$record['FID_SCORE'] 			= $FID_SCORE >0 ? $FID_SCORE : '#NA';
		
		$FID_ARTICLE 					= array_key_exists('FID_ARTICLE', $aR)? (int)($aR['FID_ARTICLE'])	:-1;
		$record['FID_ARTICLE'] 			= $FID_ARTICLE> 0 ? $FID_ARTICLE: null;
	
	// 	Additional fields for FID_ARTICLE:
		if ( !is_null($record['FID_ARTICLE'])  ){
			
			require_once VIEWS.'calculation.php';
			$view_calculation = new viewCalculation();
			
			require_once MODELS.'article.php';
			$model_article = new modelArticle();
			$record = $view_calculation->add_article_fields($record, $model_article);
					
			
			
		}
		return $record;
	}
	
	
	
	public function get_ar($ID = null){
		
		
		//	Get records from base table:
			$ID = (int)$ID;
			if ($ID < 1 ) { return $this->ar_new; }
			$ar = parent::get_ar($ID);
			if (!array_key_exists('ID', $ar))  {return $ar;}
			if ($ar['ID']  != $ID) {return $ar;}
			$ar['inputs'] 		= $this->get_inputs($ID);
			$ar['rules'] 		= $this->get_rules($ID);
			$ar['scores'] 		= $this->get_scores($ID);
			return $ar;
		
	}
	
	public function save_ar($fields= array(), $ID = null ){
		
	//	Check FID_ARTICLE:
		if (array_key_exists('FID_ARTICLE',$fields) ) {
			$article = $this->article->ar($fields['FID_ARTICLE']);
			if (!array_key_exists('ID',$article) ){
				$fields['FID_ARTICLE'] =null;
			} else {
				if ((int)$article['ID'] !== (int)$fields['FID_ARTICLE']){
					$fields['FID_ARTICLE'] = null;
				}
			}
		}
		
	//	Save to  table calculation  and satellite tables
		try {
			
			//	Start transaction by setting auto-commit off:
				$this->db->autocommit(false);
				
			//	Save to table calculation:
				$calculation = array();
				foreach ( ['name', 'description','FID_ARTICLE','path','FID_CAT', 'position', 'publish'] as $name) {
					if ( array_key_exists($name, $fields) ) {	$calculation[$name] = $fields[$name];}
				}
				$save = parent::save_ar($calculation, $ID );
				if ($save === false ){ throw new Exception('failure');}
			
			//	Get active record:	
				$ar  = $this->active_table->ar();
				$FID_CALCULATION = $ar['ID'];
				
			//	Save calculation_inp:
				if ( array_key_exists('inp', $fields) ) {
					
				//  Delete related records from calculation_act
					$sql = 'DELETE FROM `calculation_act` WHERE `FID_CALCULATION` = "'.$FID_CALCULATION.'"';
					if ($this->db->query($sql) === false ) {throw new Exception('failure_act_delete');}
						
				//  Delete related records from calculation_inp
					$sql = 'DELETE FROM `calculation_inp` WHERE `FID_CALCULATION` = "'.$FID_CALCULATION.'"';
					if ($this->db->query($sql) === false ) {throw new Exception('failure_inp_delete');}
				
				//	Add calcultion_inp  and calcultion_act records:
					if ( is_array($fields['inp']) ){
						
						if ( count($fields['inp']) >0 ) {
						
						//	Sort by 'position' and collect action records
							$rows 		= array();
							$actions 	= array();
						
							foreach ($fields['inp'] as $inp=> $row ){
								$position = $row['position'];
								$rows[$position] = $row;
								if (array_key_exists('act', $row) ){
									$act = $row['act'];
									if ( is_array($act) ){
										
										if (count($act) > 0 ){
											$actions = array_merge($actions, $act);
										}
									}
								}
							}
							ksort($rows,SORT_NUMERIC);
					
						//	INSERT calculation_inp:
							$names = ['input', 'position','FID_OBSERVATION','description'];
							$sql = $this->sql_insert_satellite('inp', $FID_CALCULATION, $names, $rows);
							if ($this->db->query($sql) === false ) {throw new Exception('failure_inp_insert');}
						
							if ( count($actions) >0 ) {
							//	INSERT calculation_act:
								$names = ['input', 'ID_ACTION','type','rule', 'FID_ARTICLE', 'FID_CALCULATION_NEXT', 'description'];
								$sql = $this->sql_insert_satellite('act', $FID_CALCULATION, $names, $actions);
								if ($this->db->query($sql) === false ) {throw new Exception('failure_act_insert');}
							}
						}
					}
				}
				
			//	Save calculation_alg:
				if ( array_key_exists('alg', $fields) ) {
					
				//  Delete related records from calculation_alg
					$sql = 'DELETE FROM `calculation_alg` WHERE `FID_CALCULATION` = "'.$FID_CALCULATION.'"';
					if ($this->db->query($sql) === false ) {throw new Exception('failure_alg_delete');}	
					
					if ( is_array($fields['alg']) ){
				
						if ( count($fields['alg']) >0 ) {
						//	Sort by 'position' 
							$rows 	= array();
							foreach ($fields['alg'] as $row ){
								$position = $row['position'];
								$rows[$position] = $row;
							}
							ksort($rows,SORT_NUMERIC);
							
						
						//	INSERT calculation_alg:
							$names = ['rule', 'position','calculation', 'params', 'FID_ARTICLE', 'FID_CALCULATION_NEXT'];
							$sql = $this->sql_insert_satellite('alg', $FID_CALCULATION, $names, $rows);
							if ($this->db->query($sql) === false ) {throw new Exception('failure_alg_insert');}
						}
					}
				}
				
			//	Save calculation_sco:
				if ( array_key_exists('sco', $fields) ) {
						
				//  Delete related records from calculation_alg
					$sql = 'DELETE FROM `calculation_sco` WHERE `FID_CALCULATION` = "'.$FID_CALCULATION.'"';
					if ($this->db->query($sql) === false ) {throw new Exception('failure_sco_delete');}
						
					if ( is_array($fields['sco']) ){
				
						if ( count($fields['sco']) >0 ) {
					
						//	INSERT calculation_alg:
							$names = ['FID_SCORE', 'GE','LT', 'FID_ARTICLE'];
							$sql = $this->sql_insert_satellite('sco', $FID_CALCULATION, $names, $fields['sco']);
							if ($this->db->query($sql) === false ) {throw new Exception('failure_sco_insert');}
						}
				
					}
				}	
				
				
		} catch (Exception $e) {
			$this->error['message'] =	'Exception: '.$e->getMessage().'; DB:'.$this->db->error. '; SQL:'.$sql;
			$this->db->rollback();
			$this->db->autocommit(true);
	    	return false;
		}
		
	//  Commit 	
		$this->db->commit();
		$this->db->autocommit(true);
		return true;
	}

	 private function sql_insert_satellite($tag,$FID_CALCULATION, $names, $rows  ){
	 	
	 	$sql = 'INSERT INTO `calculation_'.$tag.'` (`FID_CALCULATION`, `'.implode('`, `',$names).'`)
				                    VALUES ';
	 	$pos = 0;
	 	$glue='';
	 	$pos = 100;
	 	foreach ($rows as  $row) {
	 	
	 		$sql .= $glue.' (';
	 		$sql .= '"'.$FID_CALCULATION.'"';
	 	
	 		foreach ( $names as $name) {
	 			if ($name == "position") {
	 				$sql .= ',"'.$pos.'"';
	 				
	 			} else {
		 			if ( $row[$name] == null  || strtoupper(trim($row[$name])) === 'NULL'  ) {
		 				$sql .= ', NULL';
		 			} else {
		 				$sql .= ', "'.$row[$name].'"';
		 			}
	 			}
	 		}
	 		$sql .= ')';
	 	
	 		$glue = ',';
	 	
	 		$pos += 100;
	 			
	 	}
	 	return $sql;
	 	
	 }
     
	
	

	/**
	 * Returns an associative array with input-fields, observations and actions for a calculation
	 * @param integer $FID_CALCULATION
	 * @return array [input] => { 
	 * 								fields,
	 * 								[actions] => array [ID_ACTION] => { fields } 
	 * 								[observations] 	=> array [FID_OBSERVATION] => { fields, [options] => {} }	
	 * 							}
	 */
	public  function get_inputs($FID_CALCULATION){
		
		$rR = array();
		
	//	Check $FID_CALCULATION
		$FID_CALCULATION = (int)$FID_CALCULATION;
		if ( $FID_CALCULATION <1 ) { return $rR;}
	
	//	Get records form calculation_inp:
		$sql  = 	' SELECT DISTINCT * 
				  	  FROM `calculation_inp`
				      WHERE `FID_CALCULATION` = "'.$FID_CALCULATION.'"
				      ORDER BY `position` ';
		$rows = $this->select($sql);
		if ( count($rows) == 0 								) { $this->addLog('calculation->get_inputs: no inputs for FID_CALCULATION="'.$FID_CALCULATION.'".',1); return $rR;}
	
	//	Get all observations for this calculation:
		$in = array();
		foreach ($rows as $row){
			if ( array_key_exists('FID_OBSERVATION', $row) ){
				$in[$row['FID_OBSERVATION']] = $row['FID_OBSERVATION'];
			}
		}
		if ( count($in) == 0	) { return $rR;}
		require_once MODELS.'observation.php';
		$modObservation = new modelObservation();
		$observations = $modObservation->get_by_ids($in);
		
	//	Get actions by input
		$actions = $this->get_actions($FID_CALCULATION);
		
	//	Conditional inputs:
		$conditional_inputs = $this->get_conditional_inputs($actions);
	
	//	Build $rR:
		foreach ($rows as $row){
			$row['conditional_input'] = in_array($row['input'],$conditional_inputs)? true: false;
			$rR[$row['input']] = $row;
			$rR[$row['input']]['actions'] = array_key_exists($row['input'], $actions)? 						$actions[$row['input']] 			: array();
			$rR[$row['input']]['observation'] = array_key_exists($row['FID_OBSERVATION'], $observations)? 	$observations[$row['FID_OBSERVATION']]	: array();
		}
		return $rR;
	}
	
	/**
	 * Returns records from calculation_act for $FID_CALCULATION in an associative array.
	 * @param int $FID_CALCULATION
	 * @return array ['input'] => 	{ [ID_ACTION 1] => {}, [ID_ACTION 2] => {},... [ID_ACTION N] => {} }
	 */								
	public function get_actions($FID_CALCULATION){
		
		$rR = array();
		
	//	Check $FID_CALCULATION:
		$FID_CALCULATION = (int)$FID_CALCULATION;
		if ($FID_CALCULATION < 1 ) { return $rR; }
		
	//	Get records form calculation_act:
		$sql  = 	' SELECT DISTINCT *
				  	  FROM `calculation_act`
				      WHERE `FID_CALCULATION` = "'.$FID_CALCULATION.'"
				      ORDER BY `input` ';
		$rows = $this->select($sql);
		if ( count($rows) == 0 	) {return $rR; }
		
	//	Build $rR:
		foreach ($rows as $row){
			if ( !array_key_exists($row['input'], $rR) ){
				$rR[$row['input']] = array();	
			}
			$rR[$row['input']][$row['ID_ACTION']] =$row;
		}
		return $rR;
		
	}
	/**
	 * Returns an array with inputs that are contitional shown by show rules on other inputs
	 * @param array $actions
	 * @return array {} with inputs
	 */
	public function get_conditional_inputs($all_actions) {
		
		if (! is_array($all_actions) ){ return array(); }
		
		$result = array();
		foreach ($all_actions as $actions){
			foreach ($actions as $input => $action){
				if ( $action['type'] == "show" ) {
					$rule= explode(':',$action['rule'] );
					if ( count($rule) >1 ) {
						$input =trim($rule[1]);
						if (  !in_array($input,$result) ) {
							$result[] = $input;
						}
					}
				}
			}
		}
		return $result;
		
	}
	
	
	/**
	 * Returns records from calculation_alg for $FID_CALCULATION in an associative array.
	 * @param int $FID_CALCULATION
	 * @return array [rule] => { fields }
	 */
	public function get_rules($FID_CALCULATION){
	
		$rR = array();
	
	//	Check $FID_CALCULATION:
		$FID_CALCULATION = (int)$FID_CALCULATION;
		if ($FID_CALCULATION < 1 ) { return $rR; }
	
	//	Get records form calculation_alg:
		$sql  = 	' SELECT DISTINCT *
				  	  FROM `calculation_alg`
				      WHERE `FID_CALCULATION` = "'.$FID_CALCULATION.'"
				      ORDER BY `position` ';
		$rows = $this->select($sql);
		if ( count($rows) == 0 	) {return $rR; }
	
	//	Build $rR:
		foreach ($rows as $row){
			$rR[ $row['rule'] ] =$row;
		}
		return $rR;
	
	}
	
	
	
	/**
	 * Returns records from calculation_sco for $FID_CALCULATION in an associative array.
	 * @param int $FID_CALCULATION
	 * @return array ['FID_SCORE'] => { fields}
	 */
	public function get_scores($FID_CALCULATION){
	
		$rR = array();
	
	//	Check $FID_CALCULATION:
		$FID_CALCULATION = (int)$FID_CALCULATION;
		if ($FID_CALCULATION < 1 ) { return $rR; }
	
	//	Get records form calculation_act:
		$sql  = 	' SELECT DISTINCT *
				  	  FROM `calculation_sco`
				      WHERE `FID_CALCULATION` = "'.$FID_CALCULATION.'"
				      ORDER BY `GE` ';
		$rows = $this->select($sql);
		if ( count($rows) == 0 	) {return $rR; }
	
	//	Build $rR:
		foreach ($rows as $row){
			$rR[ $row['FID_SCORE'] ] = $row;
		}
		return $rR;
	
	}
	
	public function score($FID_CALCULATION, $value) {
		
		
	//	Result in case not enought data for a score:	
		$result 			= array();
		$result['name'] 	= TXT_VBNE_LBL_NO_SCORE;
		$result['value'] 	= 0;
		$result['color'] 	= '#CCCCCC';
		
	//  No value: return no-result:
		if (!is_numeric($value) ) { return $result;}
		$value = (float)$value;
		
	//  Get scores:
		$scores = $this->get_scores($FID_CALCULATION);
		$i= 1;
		$nb = count($scores);
		$range = reset($scores);
		while ($value >= (float)$range['LT']  && $i < $nb) {
			$i++;
			$range = next($scores);
		}
		$score_id = $range['FID_SCORE'];
		
	//	Instance of score-table:
		$score = new table('score');
		$aR = $score->ar($score_id);
		if (array_key_exists('ID', $aR)) {
			$result = $aR;
			$result['FID_ARTICLE'] = $range['FID_ARTICLE'];
		}
		return $result;
	}
	
	
	
	public function valid_calculations(){
		
		$sql = 'DESCRIBE  `calculation_alg`';
		$rows = $this->select($sql);
		
		$type = null;
		$count = count($rows);
		$row = reset($rows);
		$i = 1;
		$test= array();
		while ($i < $count  && is_null($type) ){
			if ($row['Field'] == 'calculation') {
				$type = $row['Type'];
			}
			$row = next($rows);
			$i++;
		}
		

		
		if ( is_null($type) ) { return array();}
		$pos = strpos($type,'(',0);
		if ($pos === false ) { return array(); }
		$str_values = substr($type, $pos+1,-1);
		if ( strlen($str_values) == 0 ) { return array(); }
		$values_raw = explode(',',$str_values);
		$values = array();
		foreach ($values_raw as $value){
			$values[] = substr($value, 1,-1);
		}
		return $values;
		
	}
	
	public function valid_act_types(){
	
		$sql = 'DESCRIBE  `calculation_act`';
		$rows = $this->select($sql);
	
		$type = null;
		$count = count($rows);
		$row = reset($rows);
		$i = 1;
		$test= array();
		while ($i < $count  && is_null($type) ){
			if ($row['Field'] == 'type') {
				$type = $row['Type'];
			}
			$row = next($rows);
			$i++;
		}
	
	
	
		if ( is_null($type) ) { return array();}
		$pos = strpos($type,'(',0);
		if ($pos === false ) { return array(); }
		$str_values = substr($type, $pos+1,-1);
		if ( strlen($str_values) == 0 ) { return array(); }
		$values_raw = explode(',',$str_values);
		$values = array();
		foreach ($values_raw as $value){
			$values[] = substr($value, 1,-1);
		}
		return $values;
	
	}
	
	
	
	public function unique_name($name, $FID_CAT, $ID = null ){
		
		$WHERE = '`name`="'.trim($name).'" AND `FID_CAT`="'.(int)$FID_CAT.'"';
	    $WHERE .= !is_null($ID)?  ' AND `ID`<>"'.(int)$ID.'"': '';
		$nb = $this->active_table->count_all(array('where'=>$WHERE) );
		return (int)$nb === 0;
		
	}
	
	public function unique_path($ID,$path, $FID_CAT, $ID=null ){
	
		$WHERE = '`path`="'.trim($path).'" AND `FID_CAT`="'.(int)$FID_CAT.'"';
		$WHERE .= !is_null($ID)?  ' AND `ID`<>"'.(int)$ID.'"': '';
		$nb = $this->active_table->count_all(array('where'=>$WHERE) );
		return (int)$nb === 0;
	
	}
	
	
	public function pre_process_satellite_fields ($fields = array()){
		
		$this->error_reset();
		if ( !is_array($fields) ) { return false;}
		
	//	calultation_inp:
		$fields = $this-> pre_process_satellite_fields_inp( $fields);
		if ($fields === false) { return false;}	
		
	//	calultation_alg:
		$fields = $this-> pre_process_satellite_fields_alg( $fields);
	    if ($fields === false) { return false;}
	    
		
	//	calultation_sco:
		$fields = $this-> pre_process_satellite_fields_sco( $fields);
		if ($fields === false) { return false;}
		
		return $fields;
		
	}
	
	private function pre_process_satellite_fields_inp ($fields ){
		
		if (!array_key_exists('inp', $fields)) { return $fields;}
		if (!is_array($fields['inp']) ) 	   { return false;}
		
		$assigned_inputs = array();
		
		foreach ($fields['inp'] as $inp=>$field){
			
		//  Remove inp, if marked  to be deleted:
			if ( in_array($inp,$fields['to_delete']['inp']) ){
				unset($fields['inp'][$inp] );
				continue;
			}
		
		//	calculation_inp.input:
			$input = array_key_exists('input', $field)? trim($field['input']) : '';
			if (strlen($input) == 0) {
				$this->error['message']	= TXT_VBNE_ERR_NOT_FILLED_INP;
				$this->error['field'] = 'inp_'.$inp.'_input';
				return false;
			}
			
			//	Check on UNIQUE_INPUT
				if ( in_array($input,$assigned_inputs) ) {
					$this->error['message']	= TXT_VBNE_ERR_INPUT_UNIQUE;
					$this->error['field'] = 'inp_'.$inp.'_input';
					return false;
				}
			$assigned_inputs[] 	= $input;
			$field['input'] 	= $input;
		
		//	calculation_inp.position:
			$field['position'] 			= array_key_exists('position', $field)? (int)$field['position'] 				: 9999;
		
		//	calcualtion_inp.FID_OBSERVATION:
			$FID_OBSERVATION			= array_key_exists('FID_OBSERVATION', $field)? (int)$field['FID_OBSERVATION'] 	: -1;
			$field['FID_OBSERVATION']   = $FID_OBSERVATION >0?	$FID_OBSERVATION	: null;
				
		//	calculation_inp.description:
			$description 					= array_key_exists('description', $field)? trim($field['description'] )		: '';
			$field['description']			= strlen($description) >0? htmlspecialchars($description, ENT_HTML5, "UTF-8") : null;
		
				
		// Pre-process act fields:
			if (array_key_exists('act', $field) ){
				if (!is_array($field['act']) ) { return $fields; }
				foreach ($field['act'] as $i=> $f){
					
				//  Remove act, if marked  to be deleted
					if ( in_array($i,$fields['to_delete']['act'][$inp] ) ){
						unset($field['act'][$i]);
						continue;
					}
						
					
				//	Pre-process calculation_act fields
					$f =  $this->pre_process_satellite_fields_act ($field,$i,$f);
					if ($f === false) {
						$this->error['message']	= $this->fieldReplace(TXT_VBNE_ERR_NOT_FILLED_INP_NB, array('name'=>$field['input']));
						$this->error['field']   = 'act_'.$inp.'_'.$i.'_rule';
						return false;
					}
				
				//	Reload pre-processed field:
					$field['act'][$i] = $f;
				}
			}
			
		//	Reload pre-processed field:
			$fields['inp'][$inp] = $field;
		}
		
		return $fields;
	}
	
	private function pre_process_satellite_fields_act ($field,$i, $f){
		
		//  Get input for rule:
		    $input_for_rule =  array_key_exists('input', $f)? trim($f['input']) : '';
		
		//  calculation_act.input:
			$input =  trim($field['input']) ;
			if (strlen($input) == 0) { return false;}
			$f['input'] = $input;
		
		//  calculation_act.ID_ACTION:
			$f['ID_ACTION'] = (int)$i;
			
		//  calculation_act.type:
			$type = array_key_exists('type', $f)? trim($f['type']) : 'message';
			$valid_types = $this->valid_act_types();
			$f['type']  = in_array($type, $valid_types)? $type: 'message';
		
		//  calculation_act.rule:
			$rule = array_key_exists('rule', $f)? strtoupper( trim($f['rule']) ) : '';
			if (strlen($rule) == 0) { return false; }
			if ($rule == '#NA')     { return false; }
			
			$params = array_key_exists('params', $f)? trim($f['params']) : '';
			$rule .='('.$params.')';
			unset($f['params']);
			
			if (strlen($input_for_rule) > 0) {$rule .=':'.$input_for_rule;}
			$f['rule'] = $rule;
		
		//  calculation_act.FID_ACTICLE:
			$FID_ARTICLE			= array_key_exists('FID_ACTICLE', $f)? (int)$f['FID_ARTICLE'] 						: -1;
			$f['FID_ARTILCE']   = $FID_ARTICLE >0?	$FID_ARTICLE	: 'NULL';
	
		//  calculation_act.FID_CALCULATION_NEXT:
			$FID_CALCULATION_NEXT 		= array_key_exists('FID_CALCULATION_NEXT', $f)? (int)$f['FID_CALCULATION_NEXT'] 	: -1;
			$f['FID_CALCULATION_NEXT']  = $FID_CALCULATION_NEXT >0?	$FID_CALCULATION_NEXT	: 'NULL';
		
		//  calculation_act.description:
			$description				= array_key_exists('description', $f)? trim($f['description'] )						: '';
			$f['description']			= strlen($description) >0? htmlspecialchars($description, ENT_HTML5, "UTF-8")       : null;
			
			return $f;
	
	}
	
	private function pre_process_satellite_fields_alg ($fields ){
		
		if (!array_key_exists('alg', $fields)) { return $fields;}
		if (!is_array($fields['alg']) ) 	   { return false;}
		
		$assigned_rules = array();
		
		foreach ($fields['alg'] as $i=>$field){
			
			//  Remove alg, if marked  to be deleted:
				if ( in_array($i,$fields['to_delete']['alg']) ){
					unset($fields['alg'][$i] );
					continue;
				}
			
			
			//  calculation_alg.rule:
				$rule = array_key_exists('rule', $field)? trim($field['rule'])  : '';
				if ( !strlen($rule)>0 ) { 
					$this->error['message']	= TXT_VBNE_ERR_NOT_FILLED_ALG;
					$this->error['field']   = 'alg_'.$i.'_rule';
					return false;
				}
				//	Check on UNIQUE_RULE
					if ( in_array($rule,$assigned_rules) ) {
						$this->error['message']	= TXT_VBNE_ERR_ALGORITHM_UNIQUE;
						$this->error['field']   = 'alg_'.$i.'_rule';
						return false;
					}
				$assigned_rules[] = $rule;
				$field['rule'] = $rule;
			
			//  calculation_alg.position:
				$field['position'] 	= array_key_exists('position', $field)? (int)$field['position'] 				: 9999;
				
			//  calculation_alg.calculation:
				$calculation = array_key_exists('calculation', $field)? strtoupper( trim($field['calculation']) ) 			: '#NA';
				$valid_calculations =$this->valid_calculations();
				if ( !in_array($calculation, $valid_calculations) ) { 
					$this->error['message']	= TXT_VBNE_ERR_NOT_FILLED_ALG;
					$this->error['field']   = 'alg_'.$i.'_calculation';
					return false;
				}
				$field['calculation'] = $calculation;
				
			//  calculation_alg.params:
				$params = array_key_exists('params', $field)? trim($field['params']) 	: '';
				if ( !strlen($params)>0 ) {
					$this->error['message']	= TXT_VBNE_ERR_NOT_FILLED_ALG;
					$this->error['field']   = 'alg_'.$i.'_params';
					return false;
				}
				$field['params'] = $params;
				
			//  calculation_alg.FID_ARTICLE:
				$FID_ARTICLE			= array_key_exists('FID_ARTICLE', $field)? (int)$field['FID_ARTICLE'] 	:-1;
				$field['FID_ARTILCE']   = $FID_ARTICLE >0?	$FID_ARTICLE	:null;
				
			//  calculation_alg.FID_CALCULATION_NEXT:
				$FID_CALCULATION_NEXT 		= array_key_exists('FID_CALCULATION_NEXT', $field)? (int)$field['FID_CALCULATION_NEXT'] 	: -1;
				$field['FID_CALCULATION_NEXT']  = $FID_CALCULATION_NEXT >0?	$FID_CALCULATION_NEXT	: null;
		
		}
		
		return $fields;
	}
	
	private function pre_process_satellite_fields_sco ($fields ){
		
		if (!array_key_exists('sco', $fields)) { return $fields;}
		if (!is_array($fields['sco']) ) 	   { return false;}
		
		$assigned_scores = array();
		
		foreach ($fields['sco'] as $i=>$field){
			
			//  Remove sco, if marked  to be deleted:
				if ( in_array($i,$fields['to_delete']['sco']) ){
					unset($fields['sco'][$i] );
					continue;
				}
				
			
			//  calculation_sco.FID_SCORE:
				$FID_SCORE			= array_key_exists('FID_SCORE', $field)? (int)$field['FID_SCORE'] 	: -1;
				if ($FID_SCORE < 1) {
					$this->error['message']	= TXT_VBNE_ERR_NOT_FILLED;
					$this->error['field']   = 'sco_'.$i.'_FID_SCORE';
					return false;
				}
				//	Check on UNIQUE_SCORE
					if ( in_array($FID_SCORE,$assigned_scores) ) {
						$this->error['message']	= TXT_VBNE_ERR_SCORE_UNIQUE;
						$this->error['field']   = 'sco_'.$i.'_FID_SCORE';
						return false;
					}
				$assigned_scores[]    = $FID_SCORE;
				$field['FID_SCORE']   = $FID_SCORE;
				
			//  calculation_sco.GE:
				$GE			= array_key_exists('GE', $field)? $field['GE'] 	: null;
				if (!is_null($GE)) {
					$GE = str_replace(',', '.', $GE);
					$GE = is_numeric($GE)? (float)$GE : null;
				}
				$field['GE'] =$GE;
				
			//  calculation_sco.LT:
				$LT		= array_key_exists('LT', $field)? $field['LT'] 	: null;
				if (!is_null($LT)) {
					$LT = str_replace(',', '.', $LT);
					$LT = is_numeric($LT)? (float)$LT : null;
				}
				$field['LT'] =$LT;
			
			//  calculation_sco.FID_ARTICLE:
				$FID_ARTICLE			= array_key_exists('FID_ARTICLE', $field)? (int)$field['FID_ARTICLE'] 	:-1;
				if ($FID_ARTICLE < 1) {
					$this->error['message']	= TXT_VBNE_ERR_NOT_FILLED_SCO;
					$this->error['field']   = 'sco_'.$i.'_FID_ARTICLE';
					return false;
				}
				$field['FID_ARTILCE']   = 	$FID_ARTICLE;
		
		}
		 return $fields;
		
	}
	
  
	

	
} // END class modelCalculation
<?php	// _system/classes/calculator.php

class calculator {
	
	private $calculation   			=  array();

	public $rules    				= array();
	public $log_rules   		    = array();
	public $inputs					= array();
	public $stack     				= array();


	public  $fid_articles   		= array();
	public  $fid_calculations	    = array();
	public  $error          	    = null;
	public  $score          		= null;
	public  $log                    = array();
	
	private $i						= 0;
	private $params					= array();
	private $stack_name     		= null; 
	private $rgx		    		= '/^\[[0-9_a-zA-Z]*\]$/';
	
	private $fid_article			= null;
	private $fid_calculation  		= null;
	
	public function __construct($calculation){
		
	
		
	//	Calculation array:
	    $this->calculation = $calculation;
	
	//  Set inputs, rules and stack:
	    $inputs = array_key_exists('inputs', $calculation)? $calculation['inputs'] : array();
	    $this->inputs = $inputs;
	
		$rules = array_key_exists('rules', $calculation)? $calculation['rules'] : array();
		$this->rules = $rules;
		
		$stack = array_key_exists('stack', $calculation)? $calculation['stack'] : array();
		$this->stack = $stack;
		
		return;
			
	}
	
	public function run($all = true){
	
	
		
		if ($all === false ) { $this->all = false;}
		$this->score = null;
		$rule = reset($this->rules);
		$count = count($this->rules);
		$this->i = 0;
		while ( $this->i < $count && is_null($this->error)  && is_null($this->score) ){
			
			$formula = $rule['calculation'];
		
			
			$formula = $formula === 'IF'? 'formulaIF'   : $formula;
			$formula = $formula === 'AND'? 'formulaAND' : $formula;
			$formula = $formula === 'OR'? 'formulaOR'   : $formula;
			if ( !method_exists($this,$formula) ){
				$this->error = '"'.$formula.'" in '.$rule['rule'].'('.$this->i.') is not a valid function.';
				break;
			}
			$this->params = explode(";", $rule['params']);
			$FID_ARTICLE 			=  (int)$rule['FID_ARTICLE'];
			$this->fid_article		=  $FID_ARTICLE>0? $FID_ARTICLE: null;
			
			$FID_CALCULATION_NEXT 	=  (int)$rule['FID_CALCULATION_NEXT'];
			$this->fid_calculation  =  $FID_CALCULATION_NEXT>0? $FID_CALCULATION_NEXT: null;
			
			$this->stack_name = $rule['rule'];
			$this->log_rules[$this->stack_name] = $rule['rule'].': '. $rule['params'].'; FID_ARTICLE:'.$this->fid_article.'; FID_CALCULATION:'.$this->fid_calculation;
			
			$this->$formula();
			$rule = next($this->rules);
			$this->i++;
			
		}
		return  $this->score;
		
	}
	
	private function check_params($min=2) {
		if ( !is_array($this->params) ) { $this->error = 'LINE('.$this->i.')="'.$this->stack_name.'" has no parameters.'; return false;}
		if ( count($this->params) < $min) { $this->error = 'LINE('.$this->i.')="'.$this->stack_name.'" has to less parameters.';  return false;}
		return true;
	}
	
	private function get_val($param,$type='numeric') {
		
		$log = $param;
		

		
	
	//	When encapsulated by "[]": element from stack:
		if (preg_match($this->rgx, $param) ) {
		
			$param = str_replace("[", "", $param);
			$param = str_replace("]", "", $param);
			$param = array_key_exists($param, $this->stack) ? $this->stack[$param] : '#NA';
			if ($param === '#NA') { return '#NA';}
		}
		
		switch ($type){
			
			case 'boolean':
			$param = (boolean)$param;
			$param_txt =$param?'true' :'false';
			$this->log[] ='get_val('.$log .','.$type.'): (boolean)'.$param_txt;
			return  $param;
			break;
			
			case 'array':
			$this->log[] ='get_val('.$log .','.$type.'): (boolean)'.array_pretty_print($param);
			return  $param;
			break;
			
			default: //numeric
			if ($param === '#NA') { return '#NA';}
			$param = str_ireplace(',', '.', $param);
			if ( is_numeric($param) )	{
				if ( (int)$param == $param ) {
					$param =  (int)$param;
					$this->log[] ='get_val('.$log .','.$type.'): (int)'.$param;
					return $param;
				}
				$param =(float)$param;
				$this->log[] ='get_val('.$log .','.$type.'): (float)'.$param;
				return $param;
			}
			break;
		}
		
	}
	
	private function values_to_number() {
	// 	Check params:
		if ( !$this->check_params() ) { return  false;}
	
	// 	Get values:
		$values = array();
		foreach ($this->params as $param ){
			$val = $this->get_val($param);
			$this->log[] ='values_to_number()'.$param .': '.$val;
			if ($val === '#NA') {
	
				return false;
				break;
			}
			
			
			$values[] = $val;
		}
		return $values;
	}
	
	private function values_to_boolean() {
	// 	Check params:
		if ( !$this->check_params() ) { return  false;}
	
	// 	Get values:
		$values = array();
		foreach ($this->params as $param ){
			$val = $this->get_val($param,'boolean');
			if ($val === '#NA') {
				return false;
				break;
			}
			$values[] = $val;
		}
		return $values;
	}
	
	private function SUM() {
	
		$this->stack[$this->stack_name] = '#NA';
		
	// 	Check params:
		if ( !$this->check_params() ) { return  '#NA';}
		
	// 	Calculate:
		$this->stack[$this->stack_name] = 0;
		foreach ($this->params as $param ){
			$val = $this->get_val($param);
			if ($val === '#NA') {
				$this->stack[$this->stack_name] = '#NA';
				break;
			}
			$this->stack[$this->stack_name] += $val;
		}
		return $this->stack[$this->stack_name];
	}
	
	private function DIFF() {
		
		$this->stack[$this->stack_name] = '#NA';
		
	// 	Check params:
		if ( !$this->check_params() ) { return  '#NA';}
		
	// 	Calculate:
		$param0 = $this->get_val($this->params[0]);
		$param1 = $this->get_val($this->params[1]);
		if ($param0  === '#NA' || $param1  === '#NA'){
			$this->stack[$this->stack_name] = '#NA';
			return '#NA';
		}
		$this->stack[$this->stack_name] = $param0 - $param1;
		return $this->stack[$this->stack_name]; 
	}
	
	private function MUL() {
		
		$this->stack[$this->stack_name] = '#NA';
		
	// 	Check params:
		if ( !$this->check_params() ) { return  '#NA';}
	
	// 	Calculate:
		$this->stack[$this->stack_name] = 1;
		foreach ($this->params as $param ){
			$val = $this->get_val($param);
			if ($val === '#NA') {
				$this->stack[$this->stack_name] = '#NA';
				break;
			}
			$this->stack[$this->stack_name] *= $val;
		}
		return $this->stack[$this->stack_name];
	}
	
	private function DIV() {
		
		$this->stack[$this->stack_name] = '#NA';
		
	// 	Check params:
		if ( !$this->check_params() ) { return  '#NA';}
	
	// 	Calculate:
		$param0 = $this->get_val($this->params[0]);
		$param1 = $this->get_val($this->params[1]);
		if ($param0  === '#NA' || $param1  === '#NA'){
			$this->stack[$this->stack_name] = '#NA';
			return '#NA';
		}
		if ($param1 == 0 ){
			$this->stack[$this->stack_name] = '#NA';
			return '#NA';
		}
		$this->stack[$this->stack_name] = $param0 / $param1;
		return $this->stack[$this->stack_name];
	}
	
	private function MAX(){
		
		$this->stack[$this->stack_name] = '#NA';
		
		$values =  $this->values_to_number();
		if ( $values === false) { return '#NA';}
		$this->stack[$this->stack_name] = max($values);
		return $this->stack[$this->stack_name];
	}
	
	private function MIN(){
		
		$this->stack[$this->stack_name] = '#NA';
		
		$values =  $this->values_to_number();
		if ( $values === false) { return '#NA';}
		$this->stack[$this->stack_name] = min($values);
		return $this->stack[$this->stack_name];
	}
	
	private function LOOKUP(){
	
		$this->log[] ="LOOKUP: ".$this->stack_name.'('.count($this->params).')';
		$this->stack[$this->stack_name] = '#NA';
		
		
		
	// 	Check params:
	   if ( !$this->check_params(3) ) { return  '#NA';}
	   

	   $this->log[] ="LOOKUP par0: ".	$this->params[0] ;
		
	// 	Get value:
		$indx= $this->get_val($this->params[0]);
		 
		
		$this->log[] ="LOOKUP indx: ".	$indx ;
		$count = count($this->params);
		if ($indx <1 || $indx >= $count) { return  '#NA';}
		$this->stack[$this->stack_name] = $this->get_val($this->params[$indx]);
		
		return $this->stack[$this->stack_name];
	}
	
	private function RULE(){
		
		$this->stack[$this->stack_name] = '#NA';
		
	//	Process parameters: 
		/*
		 *  Rule can contain paramters seperarted by ';' istself.
		 *  Is:
		 *  $this->params[0]: 		name of the input to apply rule on.
		 *  $this->params[1..n]: 	pieces that form together the rule including paramters
		 *  
		 *  Soll:
		 *  $this->params[0]: 		name of the input to apply rule on.
		 *  $this->params[1]:       implode(';', $this->params[1..n]);
		 *  
		 */
		$params 			= $this->params;
		$this->params 		= array();
		$this->params[0] 	= array_shift($params);
		$this->params[1] 	= implode(';', $params);
		
	// 	Check params:
		if ( !$this->check_params() ) { return  '#NA';}
		
	//	Get input:
		$input_key = $this->params[0];
		if ( !preg_match($this->rgx, $input_key ) ){ return '#NA'; }
		$input_key = str_replace("[", "", $input_key);
		$input_key = str_replace("]", "", $input_key);
		$input = array_key_exists($input_key, $this->inputs)? $this->inputs[$input_key] : array();
		
	//	Check on check if this input is available for this calculation:
		if (!array_key_exists($input_key, $this->stack) ){
			return  '#NA';
		}
		
	//	Get observation:
		if ( !array_key_exists('observation', $input) ){ return '#NA'; }
		$observation_record = $input['observation'];
		$observation = new observation($observation_record );
		if ( !$observation->is_available ){ return '#NA'; }
		
	//	Apply rule:
		$rule = $this->params[1];
		$this->stack[$this->stack_name] = $observation->apply_rule($rule);
		$this->log[]="APPLY OBSERVATION RULE:";
		$this->log[]= array_pretty_print($observation->debug);
		return $this->stack[$this->stack_name];
	}
	
	private function EQ(){
		
		$this->stack[$this->stack_name] = '#NA';
		
	// 	Check params:
		if ( !$this->check_params() ) { return  '#NA';}
		$values =  $this->values_to_number();
		if ($values === false) { return  '#NA';}
		
	//	EQ:
		$this->stack[$this->stack_name]  = $values[0] == $values[1];
		return $this->stack[$this->stack_name];
		
	}
	
	private function NE(){
	
		$this->stack[$this->stack_name] = '#NA';
	
	// 	Check params:
		if ( !$this->check_params() ) { return  '#NA';}
		$values = $this->values_to_number();
		if ($values === false) { return  '#NA';}
	
	//	NE:
		$this->stack[$this->stack_name] = $values[0]!= $values[1];
		return $this->stack[$this->stack_name];
	
	}
	
	private function LT(){
	
		$this->stack[$this->stack_name] = '#NA';
	
	// 	Check params:
		if ( !$this->check_params() ) { return  '#NA';}
		$values = $this->values_to_number();
		if ($values === false) { return  '#NA';}
	
	//	LT:
		$this->stack[$this->stack_name]  = $values[0] < $values[1];
		return $this->stack[$this->stack_name];
	
	}
	
	private function LE(){
	
		$this->stack[$this->stack_name] = '#NA';
	
	// 	Check params:
		if ( !$this->check_params() ) { return  '#NA';}
		$values = $this->values_to_number();
		if ($values === false) { return  '#NA';}
	
	//	LE:
		$this->stack[$this->stack_name] = $values[0] <= $values[1];
		return $this->stack[$this->stack_name];
	
	}
	
	private function GT(){
	
		$this->stack[$this->stack_name] = '#NA';
	
	// 	Check params:
		if ( !$this->check_params() ) { return  '#NA';}
		$values = $this->values_to_number();
		if ($values == false) { return  '#NA';}
	
	//	GT:
		$this->stack[$this->stack_name]  = $values[0] > $values[1];
		return $this->stack[$this->stack_name];
	
	}
	
	private function GE(){
	
		$this->stack[$this->stack_name] = '#NA';
	
	// 	Check params:
		if ( !$this->check_params() ) { return  '#NA';}
		$values = $this->values_to_number();
		if ($values == false) { return  '#NA';}
	
	//	GE:
		$this->stack[$this->stack_name]  = $values[0] >= $values[1];
		return $this->stack[$this->stack_name];
	
	}
	
	private function BETWEEN(){
		
		$this->stack[$this->stack_name] = '#NA';
		
	// 	Check params:
		if ( !$this->check_params(4) ) { return  '#NA';}
		$O1 = strtoupper( trim( $this->params[1] ) );
		$O2 = strtoupper( trim( $this->params[3] ) );
		if ( !in_array($O1, ['LT', 'LE'] ) )    {                                     return  '#NA'; }
		if ( !in_array($O2, ['LT', 'LE'] ) )    {                                     return  '#NA'; }
		$param_0_float   = $this->get_val( $this->params[0] );
		$param_2_float   = $this->get_val( $this->params[2] );
		$param_4_float   = $this->get_val( $this->params[4] );
		
		if ( $param_0_float === '#NA' || $param_2_float === '#NA'|| $param_4_float ) {  return  '#NA'; }
		
	//	Compare:
		if ($O1 == 'LT') {
			$result = $param_0_float < $param_2_float;
		} else {
			$result = $param_0_float <= $param_2_float;
		}
		if ($O2 == 'LT') {
			$this->stack[$this->stack_name] = $result && ($param_2_float < $param_4_float);
		} else {
			$this->stack[$this->stack_name] = $result && ($param_2_float <= $param_4_float);
		}
		return $this->stack[$this->stack_name];
		
	}
	
	private function ISNA(){
		
	// 	Read and check params:
		if ( !$this->check_params(1) ) { return  '#NA';}
		
	//	Try to get value:
		$test = $this->get_val( $this->params[0] );
		$test = $test === '#NA';
		$this->stack[$this->stack_name]  = $test;
		return  $test === '#NA';
		
	}
	
	private function IFNA(){
	
	// 	Read and check params:
		if ( !$this->check_params(1) ) { return  '#NA';}
	
	//	Try to get value:
		$test = $this->get_val( $this->params[0] );
		$test = $test === '#NA';
			
	//	True and false value
		$true = true;
		if (count($this->params) >1 ) {
			$true = is_bool($this->params[1])? (boolean)$this->params[1]: $this->get_val( $this->params[1] );
		}
		
		$false = false;
		if (count($this->params) >2 ) {
			$false = is_bool($this->params[2])? (boolean)$this->params[2]: $this->get_val( $this->params[2] );
		}
		
		$result = $test? $true : $false;
		$this->stack[$this->stack_name]  = $result;
		return $result;
	
	}
	
	private function formulaIF(){
		
		$this->stack[$this->stack_name] = '#NA';
		
	// 	Read and check params:
		if ( !$this->check_params(1) ) { return  '#NA';}
		
		$test  = $this->get_val( $this->params[0], 'boolean' );
		if ( $test === '#NA') { return  '#NA';}
		$true = count($this->params) >1 ?  $this->get_val( $this->params[1] ): true;
		$false = count($this->params) >2 ?  $this->get_val( $this->params[2] ): false;
	
	//	Evaluate:
		if ($test) {
			$this->stack[$this->stack_name]  = $true;
		} else {
			$this->stack[$this->stack_name]  = $false;
		}
		
		return $this->stack[$this->stack_name];
		
	}
	
	private function NOT(){
	
		$this->stack[$this->stack_name] = '#NA';
	
	// 	Read and check params:
		if ( !$this->check_params(1) ) { return  '#NA';}
	
		$boo  = $this->get_val( $this->params[0], 'boolean' );
		if ( $boo === '#NA') { return  '#NA';}
	
	//	Invert:
		$this->stack[$this->stack_name]  = !$boo;
		
		return $this->stack[$this->stack_name];
	
	}
	
	private function formulaAND(){
	
		$this->stack[$this->stack_name] = '#NA';
	
	// 	Read and check params:
		if ( !$this->check_params() ) { return  '#NA';}
		
		$bools = $this->values_to_boolean();
		if ($bools === false ) { return  '#NA';}
	
	//	Evalaute:
		$result = true;
		$count = count($bools);
		$i = 0;
		$boo = reset($bools);
		while ($i < $count && $result){
			$result = $boo;
			$i++;
			$boo = next($bools);
		}
		$this->stack[$this->stack_name] = $result;
	
		return $this->stack[$this->stack_name];
	
	}
	
	private function formulaOR(){
	
		$this->stack[$this->stack_name] = '#NA';
	
	// 	Read and check params:
		if ( !$this->check_params() ) { return  '#NA';}
	
		$bools = $this->values_to_boolean();
		if ($bools === false ) { return  '#NA';}
	
	//	Evalaute:
		$result = false;
		$count = count($bools);
		$i = 0;
		$boo = reset($bools);
		while ($i < $count && !$result){
			$result = $boo;
			$i++;
			$boo = next($bools);	
		}
		$this->stack[$this->stack_name] = $result;
	
		return $this->stack[$this->stack_name];
	
	}
	
	
	
	private function STOP(){
		
	
	// 	Read and check params:
		if ( !$this->check_params() ) { 
			$this->score  = '#NA';
			return  false;
		}
		
		$test  = $this->get_val( $this->params[0], 'boolean' );
		if ( $test === '#NA') {
			return  false;
		}
		$val =  $this->get_val( $this->params[1] );
		$this->log[]='val:'.$val;
	//	Evaluate:
		if ($test) { $this->score  = $val;}
		return $test;
	}
	
	private function ARTICLE(){
	
	// 	Read and check params:
		if ( !$this->check_params(1) ) { return  false;}
	
		$test  = $this->get_val( $this->params[0], 'boolean' );
		if ( $test === '#NA') {return  false; }
		$fid_article =  $this->fid_article;
		if ( is_null($fid_article))          { return false;}
	
	//	Evaluate:
		if ($test) { $this->fid_articles[$fid_article] = $fid_article;}
		return true;
	}
	
	private function CALCULATION(){
	
	// 	Read and check params:
		if ( !$this->check_params(1) ) { return  false;}
	
		$test  = $this->get_val( $this->params[0], 'boolean' );
		if ( $test === '#NA') {return  false; }
		$fid_calculation =  $this->fid_calculation;
		if ( is_null($fid_calculation) )          { return false;}
	
	//	Evaluate:
		if ($test) { $this->fid_calculations[$fid_calculation] = $fid_calculation ;}
		return true;
	}
	
	private function END(){
	
	// 	Read and check params:
		if ( !$this->check_params(1) ) {
			$this->score  = '#NA';
			return  false;
		}
	
		$this->score =  $this->get_val( $this->params[0] );
		return true;
	}
	
	


}
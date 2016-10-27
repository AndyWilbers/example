<?php	// _system/classes/observation.php

class observation {

	public $type = null;	// observation type
	public $valid = array();			// valid rules.
	private $no_params  =  array(); 	// rules without a parameter.
	public $is_available = false;
	public $val = '#NA';				// default value '#NA'
	private $nb_checks = null;			// nb of checks will be set when $observation 
	public $debug = array();			// for dumping resutls at debug.
	
	public function __construct($observation){
		
		$this->valid['value'] =  ['EQ','NE','LT','GT','LE','GE','BETWEEN','IS_NULL','IS_NOT_NULL','AND','OR'];
		$this->valid['radio'] =  ['IN','NOT','AND','OR','IS_NULL','IS_NOT_NULL'];
		$this->valid['check'] =  ['IN','ALL','NONE', 'CHECK','NOT', 'AND','OR'];
		$this->no_params      =  [ 'IS_NULL','IS_NOT_NULL'];
	
	//	Check obeservation_type:
		$observation_type =  array_key_exists('type', $observation)? strtolower( trim($observation['type']) ): '#NA';
		if ( !in_array($observation_type, ['value', 'radio','check'] ) ){return;}
		$this->type = $observation_type;
		
	//	Count number of options:
		if ( $this->type == "check" ) {
			$this->nb_checks = count($observation['options']);
		}
		
	//	Get value from $_SESSION:
		$observation_id = array_key_exists('ID', $observation)? (int)$observation['ID'] : '#NA';
		$val = vbne_observation_get($observation_id);
		
	//	In case not found: use default in case definied:
		if ($val === false ) {
		
			switch ( $this->type ) {
					
				case "value":
				if ( !is_null($observation['default_value']) ) {
					$val = (float)$observation['default_value'];
				}
				break;
						
				case "radio":
				$val = null;
				$options = 	$observation['options'];
				$option = reset($options);
				$counter = 1;
				while ( is_null($option['is_default']) && $counter < count($options) ){
					$option = next($options);
					$counter++;
				}
				if ( !is_null($option['is_default']) ){
					$val = (int)$option['value'];
				}
				
				break;
					
				case "check":
				$val = array();
				break;
						
			}
		
		}
		if ($val !== false) {
		
		//	For radio: wrap value in array:
			if ($this->type == "radio") {
				$val =  !is_null($val)? (int)$val : null;
				$val = [$val];
			}
				
		
			$this->val = $val;
		}
	
		$this->is_available = true;

		return;
			
	}
	
	public function valid_rules($type="#NA"){
		
		$type = in_array($type,['value','radio','check'])? $type: $this->type;
		
		$result = array();
		foreach ($this->valid[$type] as $r ){
			$result[$r] =$r;
		}
		return $result;
	}
	
	public function apply_rule($rule){
		
		$this->debug[]="apply_rule()";
		
	//  In case observation doesn't have a value: return '#NA':
		if ($this->val == '#NA') { return '#NA'; }
		
	//	Read and validate rule:
		$rule = trim($rule);
		$parts = explode('(',$rule);
		if  ( count($parts) < 2 ) { return '#NA'; }
		$rule = array_shift($parts);
		$rule = strtoupper( trim($rule) );
		if ( !in_array($rule, $this->valid[$this->type] ) ) { return '#NA';}
		
	//  Get parameters:
		if ( !in_array($rule, $this->no_params )  ){
			$str_params = rtrim(implode('(',$parts), ')');
			if ( strpos($str_params, ');') !== false ) {
			//	parameters are values:
				$params = explode(');', $str_params);
				
			} else {
			//	parameters are other rules (in case of AND, OR):
				$params = explode(';', $str_params);
			}
		}
		$this->debug[]="apply_rule(): paramters ok";
		
		$result = '#NA';
		switch ($rule) {
		
			case 'EQ':
			$param_0_float   = $this->to_float($params[0]);
			if ($param_0_float == '#NA'			) {  					break;}
			if ( is_null($this->val)   			) {  $result = false; 	break;}
			$result = $this->val ==  $param_0_float;
			break;	
			
			case 'NE':
			$param_0_float   = $this->to_float($params[0]);
			if ($param_0_float == '#NA'			) {  					break;}
			if ( is_null($this->val)   			) {  $result = true; 	break;}
			$result = $this->val !=  $param_0_float;
			break;
			
			case 'LT':
			$param_0_float   = $this->to_float($params[0]);
			if ($param_0_float == '#NA'			) {  					break;}
			if ( is_null($this->val)   			) {  $result = false; 	break;}
			$result = $this->val <  $param_0_float;
			break;	
			
			case 'LE':
			$param_0_float   = $this->to_float($params[0]);
			if ($param_0_float == '#NA'			) {  					break;}
			if ( is_null($this->val)   			) {  $result = false; 	break;}
			$result = $this->val <=  $param_0_float;
			break;
			
			case 'GT':
			$param_0_float   = $this->to_float($params[0]);
			if ($param_0_float == '#NA'			) {  					break;}
			if ( is_null($this->val)   			) {  $result = false; 	break;}
			$result = $this->val >  $param_0_float;
			break;
			
			case 'GE':
			$param_0_float   = $this->to_float($params[0]);
			if ($param_0_float == '#NA'			) {  					break;}
			if ( is_null($this->val)   			) {  $result = false; 	break;}
			$result = $this->val >=  $param_0_float;
			break;
			
			case 'BETWEEN':
			if ( count($params) < 4 )              {                     break;}
			$G = strtoupper( trim( $params[0] ) );
			$L = strtoupper( trim( $params[2] ) );
			if ( !in_array($G, ['GT', 'GE'] ) )    {                     break;}
			if ( !in_array($L, ['LT', 'LE'] ) )    {                     break;}
			$param_1_float   = $this->to_float($params[1]);
			$param_3_float   = $this->to_float($params[3]);
			if ( $param_1_float == '#NA' || $param_3_float == '#NA' ) {  break;}
			if ( is_null($this->val)   			) {  $result = false; 	 break;}
			if ($G == 'GT') {
				$result = $this->val > $param_1_float;
			}else {
				$result = $this->val >= $param_1_float;
			}
			if ($L == 'LT') {
				$result = $result && ($this->val < $param_3_float);
			}else {
				$result = $result && ($this->val <= $param_3_float);
			}
			break;
			
			case 'IS_NULL':
			$val = $this->type == "radio"? $this->val[0]: $this->val;
			$result = $val == null;
			break;
			
			case 'IS_NOT_NULL':
			$val = $this->type == "radio"? $this->val[0]: $this->val;
			$result = $val != null;
			break;
			
			case 'IN':
			$params =  $this->to_integer($params);
			if ( $params === false ) { break;}
			$i = 0;
			$len = count($this->val);
			$result = false;
			while ($i < $len  && !$result){
				$result =in_array($this->val[$i],$params);
				$i++;
			}
			break;
			
			case 'NOT':
			$params =  $this->to_integer($params);
			if ( $params === false ) { break;}
			$i = 0;
			$len = count($this->val);
			
			$result = true;
			if ($len == 0) { break; }
			do{
				$result =in_array($this->val[$i],$params);
				$i++;
			} while($i < $len  && $result);
			break;
			
			case 'ALL':
			$params =  $this->to_integer($params);
			if ( $params === false ) { break;}
			$i = 0;
			$len = count($this->val);
			if ($len == 0) {$result = false; break; }
			$result = true;
			do{
				$result = in_array($this->val[$i],$params);
				$i++;
			} while($i < $len  && $result);
			break;
			
			case 'NONE':
			$params =  $this->to_integer($params);
			if ( $params === false ) { break;}
			$i = 0;
			$len = count($this->val);
			$result = true;
			while ($i < $len  && $result){
				$result =!in_array($this->val[$i],$params);
				$i++;
			}
			break;
			
			case 'CHECK':
			$param_0 = trim($params[0]);
			
			$this->debug[]="CHECK";
			$this->debug[]="nb all: ".$this->nb_checks;
			$this->debug[]="param_0: ".$param_0;
		
		 	if ( strlen($param_0) == 0 || $param_0 == "*" || strtoupper($param_0) == "ALL" || (int)$param_0 == 0 ) {
		 		$this->debug[]="O of *";
			    $param_0 =  strlen($param_0) == 0 ||  (int)$param_0 == 0 ? 0 : $this->nb_checks;
		    } else {
		    	$this->debug[]="geen getal";
			    $param_0 = is_numeric($param_0)? (int)$param_0: '#NA';
		    }
		    if ($param_0 === '#NA' ) { break; }
		    $this->debug[]="vergelijk met: ".$param_0;
		    
		    $operator = 'EQ';
		    if (strlen($params[1])> 1){
		    	$param_1  = strtoupper( trim($params[1]) );
		    	$operator  = in_array($param_1,['NE','GT','GE', 'LT','LE'])? param_1: 'EQ';
		    }
		    $nb_checked = count($this->val);
		    
		    $this->debug[]="operator: ".$operator;
		  
		    $this->debug[]="nb: ".$nb_checked;
		    
		    switch ($operator){
		    	case 'NE': $result = $nb_checked != $param_0;        break;
		    	case 'GT': $result = $nb_checked >  $param_0;        break;
		    	case 'GE': $result = $nb_checked >= $param_0;        break;
		    	case 'LT': $result = $nb_checked <  $param_0;        break;
		    	case 'LE': $result = $nb_checked <= $param_0;        break;
		    	default:   $result = $nb_checked == $param_0;        break;
		    }
		    break;
		    
		    case 'AND':
		    $i = 0;
		    $len = count($params);
		    $result = true;
		    while (i < $len && $result  !== '#NA'){
		    	$result = $result && $this->apply_rule($params[$i]);
		    	$i++;
		    }
		    break;
		    
		    case 'OR':
		    $i = 0;
		    $len = count($params);
		   	$result = false;
		    while (i < $len && ($result  !== '#NA' || $result == false) ){
	    		$result =  $this->apply_rule($params[$i]);
	    		$i++;
	    	}
	    	break;
		    
		}
		return $result;
	}
	



	private function to_float($val){
		
		$val = str_replace(',', '.', $val);
		if ( is_numeric($val)  === false ) { return '#NA'; }
		return (float)$val;
		
	}
	
	private function to_integer($values){
		if (!is_array($values) ) { return false;}
		$results = array();
		foreach ($values as $key=>$value){
			if (is_numeric($value)) {
				$results[$key] = (int)$value;
			}
		}
		if ( count($results) < 1 ) { return false;}
		return $results;
	}

	

}
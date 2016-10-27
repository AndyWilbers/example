<?php	// _system/views/observation.php

class viewObservation extends view {
	
	
	private $type_class 		= array();
	private $type_checked 		= array();
	private $show_valuefields 	= '';
	private $show_options 		= ' hide';
	private $option_tbody		= '';
	
	public function __construct($template = 'form.observation.html'){
		
		$this->has_category_record_structure 	= true;
		$this->label							= TXT_VBNE_LBL_OBSERVATION;
		$this->label_plu 						= TXT_VBNE_LBL_OBSERVATIONS;
		$this->root_path 						= 'observations/';
		$this->record_name						= "observation";
		
		parent::__construct($template);
		
	//	Default assignments (active record parameter is not set):	
		$this->assignments_by_ar();
		
	}
	
	/**
	 * Assigns replacements based on content of active record
	 * @param ar: array with active record
	 */
	public function assignments_by_ar($ar = array()){
		
	//	Default assignments	
		$this->default_assignments();
		
	//	Stop in case $ar is empty or incorrect:
		if ( is_array($ar) === false) 	{ return; }
		if ( count($ar) === 0 )			{ return; }
		
	//	Setup view depending on type-field:
		if ( array_key_exists('type', $ar) ){
			$type = $ar['type'];
			if ($type !== 'value'){
				
				$this->show_valuefields = ' hide';
				$this->show_options 	= '';
				
				$this->type_class['value']		= '';
				$this->type_checked['value']	= '';
				
				$this->type_class[$type]		= ' active';
				$this->type_checked[$type]	    = ' checked';
				
			}
		}
		$this->addReplacement('show_valuefields', $this->show_valuefields);
		$this->addReplacement('show_options', $this->show_options);
		$this->addReplacement('type_class', $this->type_class);
		$this->addReplacement('type_checked', $this->type_checked);
		
	//	Assign option tbody:
		require_once MODELS.'observation.php';
		$model = new modelObservation();
		$options = $model->options_get($ar['ID'],true);
		$show_is_default = $type == 'radio'? true: false;
		$this->option_tbody  = $this->html_option_tbody($options,$show_is_default);
		$this->addReplacement('option_tbody', $this->option_tbody);
		
		return;
		
	}
	
	private function default_assignments(){
		
		$this->show_valuefields = '';
		$this->show_options 	= ' hide';
		$this->option_rows 		= '';
		
		$this->addReplacement('show_valuefields', $this->show_valuefields);
		$this->addReplacement('show_options', $this->show_options);
		$this->addReplacement('option_tbody', $this->option_tbody);
			
		$this->type_class['value']		= ' active';
		$this->type_checked['value']	= ' checked';
		$this->type_class['check']		= '';
		$this->type_checked['check']	= '';
		$this->type_class['radio']		= '';
		$this->type_checked['radio']	= '';
		$this->addReplacement('type_class', $this->type_class);
		$this->addReplacement('type_checked', $this->type_checked);
		return;
		
	}
	
	public function html_option_tbody($options = array(),$show_is_default=false) {
		
	//	Empty html:
		$html = '<tbody>'.PHP_EOL."\t".'<!--no options -->'.PHP_EOL.'</tbody>';
		
	//	Check input array:	
		if ( !is_array($options) ) { return $html; }
		if ( count($options) <1  ) { return $html; }
	
		$indx = 1;
		$rows = array();
		foreach ($options as $opt){
			$opt['row'] 		= $indx*100; 
			$opt['position'] 	= $indx*100; //normalize position.
			$rows[] = $this->html_option_row($opt, $show_is_default);
			$indx++;
		}
		
		return $this->wrapTag('tbody',$rows);
		
	}
	/**
	 * Creates an 'tr' object for the observation option table.
	 * 
	 * @param array $opt: 
	 */
	public function html_option_row($opt = array() , $show_is_default = false ) {
		
	//	Stop in case $opt is not an array:
		if ( !is_array($opt) ) { $opt = array(); }
		
	//	Fill with defaults for missing parameters:
		$opt['position'] 	= array_key_exists('position', $opt)? 			$opt['position']		: '';
		$opt['name'] 		= array_key_exists('name', $opt)? 				$opt['name']			: '';
		$opt['value'] 		= array_key_exists('value', $opt)? 				$opt['value']			: '';
		$opt['is_default']  = array_key_exists('is_default', $opt)? 		$opt['is_default']		: null;
		
	//	Build row:	
		$tds = array();
			
	//	position:
		$attr = array();
		$attr['name'] = 'opt_position_'.$opt['row'];
		$attr['class'] = 'n3';
		$attr['value'] = $opt['position'];
		$attr['type'] = 'text';
		$html = $this->wrapTag('input','',$attr);
		$tds[]= $this->wrapTag('td',$html);
		
	//	is_default:
		$attr = array();
		$attr['data-ce'] = 'type:radio;name:is_default;value:'.$opt['row'].';';
		$attr['class'] = $opt['is_default'] == "yes"? "ce-radio no-default checked" : "ce-radio no-default";
		if (!$show_is_default) {
			$attr['class'].= " hidden";
		}
		$html = $this->wrapTag('span','',$attr);
		$tds[]= $this->wrapTag('td',$html);
		
	//	label:
		$attr = array();
		$attr['name'] = 'opt_name_'.$opt['row'];
		$attr['value'] = $opt['name'];
		$attr['type'] = 'text';
		$html= $this->wrapTag('input','',$attr);
		$tds[]= $this->wrapTag('td',$html);
		
	//	value:
		$attr = array();
		$attr['name'] = 'opt_value_'.$opt['row'];
		$attr['class'] = 'n20';
		$attr['value'] = $opt['value'];
		$attr['type'] = 'text';
		$html = $this->wrapTag('input','',$attr);
		$tds[]= $this->wrapTag('td',$html);
		
	//	remove:
		$attr = array();
		$attr['data-sa'] = 'onClick:observation.option_remove;';
		$attr['href'] = '#';
		$attr['title'] = TXT_TITLE_REMOVE;
		$attr['data-label'] = TXT_LBL_ROLLBACK;
		$attr['data-title'] = TXT_TITLE_ROLLBACK;
		$html = $this->wrapTag('a',TXT_LBL_REMOVE,$attr);
		$tds[]= $this->wrapTag('td',$html);
			
		return $this->wrapTag('tr',$tds);
		
	}
	
	
	
	public function fe_page($structure = array()){
		
	//	Check input-array:
		if ( !is_array($structure)) { return '';}
		if ( !array_key_exists('fields',  $structure) ) { return ''; }
		if ( !array_key_exists('records', $structure) ) { return ''; }
		
	//	Build page:
		$html= array();
		

		//	Header and introduction:
			$fields = array();
			$fields['name'] 	= array_key_exists('name',  $structure['fields'])? $structure['fields']['name']: '';
			
			$introduction =  $this->fe_introduction($structure['fields']);
			if ($introduction !== false){
				$fields['introduction'] = $introduction;
			}
			
			$html[] = parent::fe_page($fields);
			
		//	Observation-list:
			if (array_key_exists('publish', $structure['fields'])) {
				if ((int)$structure['fields']['publish'] != 1) { return $this->getHtml('404.html');}
			}
			
			
		//	$html[] = array_pretty_print($structure['records']);
			foreach ($structure['records'] as $record) {
				$html[] = $this->fe_observation($record);
			}
			
		//	Menu to next level:
			$html[] = $this->fe_menu_cat($structure['children']);
		
			return $this->wrapTag('div',$html);
		
	}
	
	public function fe_observation($record = array(), $attr = array()){
		
	//	Check record array:
		if ( !is_array($record) )  					{ return ''; }
		if ( !array_key_exists('type', $record) )	{ return ''; }
		if ( !array_key_exists('ID', $record) )		{ return ''; }
		
	//	Read ID:
		$ID  = (int)$record['ID'];
		if ($ID< 1){ return '';}
		
		$html = array();
		$name = array_key_exists('name', $record)? $record['name'] : '&nbsp;';
		$html[] = $this->wrapTag('h2',$name);
		$description = array_key_exists('description', $record)? htmlspecialchars_decode( $record['description'] ,ENT_HTML5): '&nbsp;';
		$html[] = $this->wrapTag('div',$description);
		
	//	Get value from session:
		$value = vbne_observation_get($ID);
		$attr['data-observation-id'] = $ID;
		
	//	Select by observation_type:
		$type = trim($record['type']);
		$attr['data-type'] = $type;
		switch ( strtolower( $type) ){
			case 'value': 	$html[] = $this->fe_observation_value($record, $attr, $value); break;
			case 'radio':	$html[] =  $this->fe_observation_radio($record, $attr, $value,  $ID); break;
			case 'check':	$html[] =  $this->fe_observation_check($record, $attr, $value, $ID ); break;
			default: 		break;
			
		}
		return  $this->wrapTag('div',$html, array('class'=>'observation'));
	}
	
	private function fe_observation_value($record, $attr, $value){
		
		$attr['type'] 			= 'text';
		$attr['data-min'] 		= !is_null($record['min'])? 			(float)$record['min'] 		    : 'null';
		$attr['data-max'] 		= !is_null($record['max'])? 			(float)$record['max'] 		    : 'null';
		$attr['data-default'] 	= !is_null($record['default_value'])? 	(float)$record['default_value'] : 'null';
		
		if ( $value  === false) {
			$attr['value'] = $record['default_value'] !== null? (float)$record['default_value'] : '';
		} else {
			$attr['value'] = (float)$value;
		}
		
		$placeholder = TXT_VBNE_PLACEHOLDER_NUMERIC;
		if ($attr['data-min'] != "null" || $attr['data-max'] != "null") {
			$placeholder  = $attr['data-min'] != "null"? $attr['data-min']. '&nbsp;&le;&nbsp;'.TXT_VBNE_PLACEHOLDER_NUMERIC.'&nbsp;': TXT_VBNE_PLACEHOLDER_NUMERIC.'&nbsp;';
			$placeholder .= $attr['data-max'] != "null"? '&nbsp;&le;&nbsp;'.$attr['data-max']: '';
		}
		
		$attr['placeholder'] = $placeholder;
		
		return $this->wrapTag('input','',$attr);
		
	}
	
	private function fe_observation_radio($record, $attr, $value, $ID){
		
		$options = array();
		
	//	Determine if observattion has a default value: 
		$has_default = false;
		foreach ($record['options'] as $opt) {
			if ($opt['is_default'] =='yes') {
				$has_default = true;
			}

		}
		
		foreach ($record['options'] as $opt) {
			
			$opt_attr = array();
			$opt_attr['data-ce'] ='type:radio;name:observation-'.$ID.';value:'. $opt['value'].';';
			
			$opt_attr['class'] = $has_default? 'h20px ce-radio':'h20px ce-radio no-default';
			if ( $value  === false) {
				$opt_attr['class'] .= $opt['is_default'] =='yes'?' checked' :'';
			
			} else {
				$opt_attr['class'] .= (int)$opt['value'] === (int)$value? ' checked' :'';
		    }
		    $option  	= $this->wrapTag('span','',$opt_attr);
		    $options[] 	= $this->wrapTag('label', $option.$opt['name']);
		}
	
		return $this->wrapTag('div', $options,$attr);
		
	}
	
	private function fe_observation_check($record, $attr, $value, $ID){
		
		$selected_values = is_array($value)? $value: array();
		
		$options = array();
		foreach ($record['options'] as $opt) {
			$opt_attr = array();
			$opt_attr['data-ce'] ='type:check;name:observation-'.$ID.';value:'. $opt['value'].';';
				
			$opt_attr['class'] = 'h20px ce-check';
			$opt_attr['class'] .= in_array($opt['value'], $selected_values)?  ' checked': '';
			$option  	= $this->wrapTag('span','',$opt_attr);
			$options[] 	= $this->wrapTag('label',$option.$opt['name']);
		}
		return $this->wrapTag('div', $options,$attr);
	
	}
	
	
		
}

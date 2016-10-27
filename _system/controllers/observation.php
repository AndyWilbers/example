<?php	//	_system/controllers/observation.php
			defined('BONJOUR') or die;
	

class controllerObservation extends controller {
	
		
	public function __construct(){
		
		$this->has_category_record_structure 	= true;
	
		parent::__construct();
		
	//	Model and view:
		require_once MODELS.'observation.php';
		$this->model = new modelObservation();
		
		require_once VIEWS.'observation.php';
		$this->view = new viewObservation();
		
	//	The observation category contains the field FID_ARTILCE_CAT: assign the selectbox to the category form:	
		$this->set_select_article_cat();
	    $this->set_select_article();
		

		
	//	Get and start respond_method:
		$respond_method = count($this->request) > 1? $this->request[1] : $this->respond_method;
		
		
		if ( method_exists($this, $respond_method) ) {  $this->respond_method = $respond_method;}
		
		return;
			
	}
	
	protected function crs_admin_record(){
		
		
		if ($this->ar === null) {
			if (isset( $_REQUEST['id'])) {
		
				$id = (int)$_REQUEST['id'];
				if ($id < 1) {  						return  $this->crs_admin_index(); }
				$ar = $this->model->ar($id);
				if (!array_key_exists('ID', $ar)) {  	return  $this->crs_admin_index(); }
				if ( (int)$ar['ID']  !== $id ) {  		return  $this->crs_admin_index(); }
				$this->set_active_record($ar);
			}
		}
		
		$this->view->assignments_by_ar($this->ar);
		
		return parent::crs_admin_record();
		
	}
	
	protected function save() {
		
		
		$this->meta_msg 	= TXT_ERR_UNKWOWN;
		
	//	Form id
		$this->data['form_id'] = isset($_POST['form_id'])? $_POST['form_id'] : 'unknown';
		
	//	Check ID:
		$this->meta_error 	= -1;
		if (array_key_exists('ID', $_POST) === false){ parent::respond_data(); return; }
		
	// 	Get model:
		$this->meta_error 	= -2;
		if (array_key_exists('recordname', $_POST) === false){ parent::respond_data(); return; }
		
		$this->meta_error 	= -21;
		$file = rtrim( strtolower(trim($_POST['recordname'])), '.php').'.php';
		if ( !file_exists(MODELS.$file)){ parent::respond_data(); return; }
		require_once MODELS.$file;
		
		$this->meta_error 	= -22;
		$class_name = 'model'.$this->file_to_classname($file);
		if ( !class_exists($class_name)) { parent::respond_data(); return; }
		$model =  new $class_name();
		
	//	Get ID:
		$this->meta_error 	= -3;
		if ($_POST['ID'] == "new") {
			$ID = null;
		} else {
			$ID = (int)$_POST['ID'];
			if ($ID <1 ) {  parent::respond_data(); return; }
		}

	//	Get fields from request
		$fields = array();
		
		if (array_key_exists('publish', $_POST) ){
			$fields['publish']  = (int)$_POST['publish'] === 1? 1: -1;
		}
		
		if (array_key_exists('name', $_POST) ){
			$fields['name']  = trim($_POST['name']);
		}
		
		if (array_key_exists('FID_CAT', $_POST) ){
			$fields['FID_CAT']  = (int)trim($_POST['FID_CAT']);
		}
		
		if (array_key_exists('position_id', $_POST)  && array_key_exists('FID_CAT', $_POST) ){
			$position = $model->ar_positions_get_position($_POST['position_id'], $_POST['FID_CAT']);
			if ($position !== false) {
				$fields['position'] = $position;
			}		
		}
		
		if (array_key_exists('description', $_POST) ){
			$fields['description']  = trim($_POST['description']);
		}
		
		if (array_key_exists('type', $_POST) ){
			$type = strtolower(trim($_POST['type']));
			if (in_array($type,["value","check","radio"])) {
				$fields['type'] = $type;
			}
		}
		
	//	Type related fields
		
		if ( array_key_exists('type', $fields) ) {
			
			switch ($fields['type']) {
				
				case "value":
					  if (array_key_exists('min', $_POST)) { $fields['min'] = strtoupper($_POST['min']) != "NULL"? (float)$_POST['min']: null;}
					  if (array_key_exists('max', $_POST)) { $fields['max'] = strtoupper($_POST['max']) != "NULL"?(float)$_POST['max']: null;}
					  if (array_key_exists('default_value', $_POST)) { $fields['default_value'] = strtoupper($_POST['default_value']) != "NULL"?(float)$_POST['default_value']: null;}
					  break;
					 
				case "check":
				case "radio":
					  //	Get options to delete:
					 	 	$options_to_delete = array();
					  		if (array_key_exists('options_to_delete', $_POST) ) {
					  			$options_to_delete = explode(";", $_POST['options_to_delete']);
					  		}
					  		
					  		
					  //	Get options:
					  		$options = array();
					  		foreach ($_POST as $name => $value) {
					  			if (str_starts('opt_', $name) ){
					  				$parts = explode("_",$name);
					  				if (count($parts) == 3 ){
					  					$field_name = $parts[1];
					  					$row		= $parts[2];
					  					if ( in_array($row, $options_to_delete) == false ){
					  						$options[$row] = array_key_exists($row, $options )? $options[$row] : array();
					  						$options[$row][$field_name] = $value;
					  					}
					  				}
					  			}
					  		}
					  		$fields['options'] = $options;
					  		
					  //	For type= "radio" "is_default", set by request, in cae not set or set to an option that will be deleted the first option is choosen.		
					  		if ($fields['type'] === "radio" && count($fields['options'])>0) {
					  			
					  			
					  			$is_default = array_key_exists('is_default', $_POST)? (int)$_POST['is_default'] : null;
					  			
					  			
					  			if (array_key_exists( $is_default, $options ) || $is_default === null) {
					  				$fields['is_default'] = $is_default;
					  			} else {
					  				$option_keys = array_keys($options);
					  				$fields['is_default'] = $option_keys[0];
					  			}
					  			
					  		}
					  break;	
			}
		}
		
	//	Update / insert record:
		$this->meta_error = -4;
		$ar = $this->model->save_ar($fields,$ID);
		if ($ar === false){
			$known_error = $this->known_error($model->last_sql_error());
			if ($known_error !== false){
				$this->meta_msg = $known_error['msg'];
				$this->data['field_warning'] = $known_error['field'];
			}
			parent::respond_data();
			return;
		}
		$ar = $this->model->ar();
		$this->data['redirect'] = rtrim(HOME_.'admin/'.$_POST['root_path'].$model->get_category_path_by_id($ar['FID_CAT'],true),'/').'?id='.$ar['ID'];
	
		$this->data['ID'] 	= $ID;
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_DB_SUCCESS;
		
		parent::respond_data();
		return;
		
		
	}

	protected function options_get_row(){
		$position_row= array_key_exists('position_row', $_GET)? $_GET['position_row']: 100;
	
		$position_max = array_key_exists('position_max', $_GET)? $_GET['position_max']: 999;
		$position = 100*ceil(1+(float)$position_max /100);
		
		$opt= array();
		$opt['position'] 	= (int)$position;
		$opt['row'] 		= (int)$position_row;
		
		$this->data['html_row']= $this->view->html_option_row($opt);
		$this->meta_error 	= 0;
		$this->meta_msg 	= "OK";
		
		parent::respond_data();
		return;
	}
	
	protected function options_get_rows(){
		
		$this->data['rows'] = array();
		foreach ($_GET as $pos =>$fields){
			if (is_array($fields)) {
				if (array_key_exists('position', $fields)) {
					
					$opt = array();
					$opt['row']			= $pos;
					$opt['position'] 	= $fields['position'];
					$opt['name'] 		= $fields['name'];
					$opt['value'] 		= $fields['value'];
					
					$row = array();
					$row['row']    			= $pos;
					$row['html']   			= $this->view->html_option_row($opt);
					$this->data['rows'][] 	= $row;
				}
			}
		}
		
		$this->meta_error 	= 0;
		$this->meta_msg 	= "options_get_rows";
		parent::respond_data();
		
	}


	public function fe_published_records( $records = array() ){
			
	//	Get basic fields from parent:		
		$published_records = parent::fe_published_records($records);
		if ($published_records === false ) 	  { return $published_records;}
		if (count($published_records)  == 0 ) { return $published_records;}
		
	//	Add options for relevant types:
		foreach( $published_records as $key=>$record) {
			if ( in_array($record['type'], array('radio','check') ) ){
				$record['options'] = $this->model->options_get((int)$record['ID']);
				$published_records[$key] = $record;
			}
		}
		return $published_records;

	}
	
	
	public function reset(){
		
		$this->data['redirect'] =  array_key_exists('redirect', $_POST)? $_POST['redirect'] :'#NA';
		vbne_observations_clear();
		$this->meta_msg = '';
		parent::respond_data();
		return;
	}
	
	public function update(){
		
	//	Get and check POST parameters:
		$ID 	= array_key_exists('ID', $_POST)? 	(int)$_POST['ID']	: false;
		$val 	= array_key_exists('val', $_POST)? 	$_POST['val'] 		: null;
		
		if ($ID === false) {
			$this->meta_error 	= -11;
			$this->meta_msg 	= TXT_ERR_UNKNOWN_INPUT;
			parent::respond_data();
		}
		
		if ( $ID <= 0 ){
			$this->meta_error 	= -12;
			$this->meta_msg 	= TXT_ERR_UNKNOWN_INPUT;
			parent::respond_data();
		}
		
	//	Check if ID is an excisting value:
		$observation = $this->model->ar($ID);
		if ( !array_key_exists('ID', $observation) ){
			$this->meta_error 	= -13;
			$this->meta_msg 	= TXT_ERR_UNKNOWN_INPUT;
			parent::respond_data();
		}
		
		
	//	Sanatize val:
		switch ($observation['type']){
			case 'value':
			if (is_numeric($val) ) {
				$val = (float)$val;
				if ($observation['min'] !== null){
					$val = $val < (float)$observation['min'] ? (float)$observation['min'] : $val;
				}
				if ($observation['max'] !== null){
					$val = $val > (float)$observation['max'] ? (float)$observation['max'] : $val;
				}
			} else {
				$val = null;
			}
			break;
			
			case 'radio':
			$val = (int)$val<1? null:(int)$val;
			break;
				
			case 'check':
			$val =  is_array($val)? $val : null;
			if ($val !== null) {
				$val 		= count($val)>0? $val : null;
				$val_input 	= $val;
				$val		= array();
				if ($val !== null) {
					foreach ($val_input as  $indx) {
						$indx = (int)$indx;
						if ( $indx >= 1) {
							$val[]=$indx;
						}
					}
				}
				$val = count($val)< 1? null: $val;
			}
			break;
		}
		
		
	//	Update opservation session:
		$update = vbne_observations_set($ID,$val);
		if ($update === false) {
			$this->meta_error 	= -20;
			$this->meta_msg 	= TXT_ERR_UNKNOWN;
			parent::respond_data();
			
		}
		$new_value = vbne_observation_get($ID);
		$this->data['new_value'] = $new_value !== false? $new_value : null;
		parent::respond_data();
		return;
	}
	
	
	public function get_options_after_update(){
	
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_OK;
		$ID 		= array_key_exists('ID', $_GET)? (int)$_GET['ID'] : -1;
		$selectors  = array_key_exists('selectors', $_GET)? $_GET['selectors'] : array(); 
		
		$record 	= $ID > 0 ? $this->model->get_ar($ID) : $this->model->ar_new;
		$record['options'] =$this->model->options_get($ID);
		
		$observation = new observation($record);
		$valid_rules = $observation->valid_rules();
		
		$this->data['selectors']  =array();
		if ( is_array($selectors) ){
			foreach ($selectors as $name=>$value){
				$this->data['selectors'][$name] = array();
				$value = array_key_exists($value, $valid_rules)? $value : '#NA';
				$this->data['selectors'][$name]['value'] = $value;
				$this->data['selectors'][$name]['html'] = $this->view->html_options($valid_rules,TXT_VBNE_LBL_ACT_RULE,$value);
			}
		}
		
		parent::respond_data();
		return;
	}
	
	
	
	
}
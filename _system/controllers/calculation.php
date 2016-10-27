<?php	//	_system/controllers/calculation.php
			defined('BONJOUR') or die;
	

class controllerCalculation extends controller {
	
	public $score = array();
	
		
	public function __construct(){
		
		
		$this->has_category_record_structure 	= true;
		
		parent::__construct();
		
	//	Model and view:
		require_once MODELS.'calculation.php';
		$this->model = new modelCalculation();
		
		require_once VIEWS.'calculation.php';
		$this->view = new viewCalculation();
		
	//	Calculate score:
		$this->score = $this->calculate_score();
		$this->view->score = $this->score;
		
	//	The calcultation category contains the field FID_ARTILCE_CAT: assign the select-tbox to the category form:	
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
	
	public function fe_published_records( $records = array() ){
			
	//	Get basic fields from parent:
		$published_records = parent::fe_published_records($records);
		if ($published_records === false ) 	  { return $published_records;}
		if (count($published_records)  == 0 ) { return $published_records;}
	
	//	Check id requested ID excists as a published calculation:
		$ID = array_key_exists('id', $_REQUEST)? (int)$_REQUEST['id']  	: -1;
		$ID = array_key_exists($ID, $published_records)? $ID 			: -1;
		if ( $ID > 0 ){
			$published_records[$ID]['calculation'] = $this->model->get_ar($ID);
		}
		return $published_records;
	
	}

	

	protected function save() {
		
	
	
	
		$this->meta_msg 	= TXT_ERR_UNKWOWN;
	
	//	Form id
		$this->data['form_id'] = isset($_POST['form_id'])? $_POST['form_id'] : 'unknown';
	
	//	Check ID:
		$this->meta_error 	= -1;
		if (array_key_exists('ID', $_POST) === false){ parent::respond_data(); return; }
		
	// 	Get model:
		require_once MODELS.'calculation.php';
		$model =  new modelCalculation();
	
	//	Get ID:
		$this->meta_error 	= -2;
		if ($_POST['ID'] == "new") {
			$ID = null;
		} else {
			$ID = (int)$_POST['ID'];
			if ($ID <1 ) {  parent::respond_data(); return; }
		}
		
	
	//	Get fields from request
		$fields = array();
		
	//	calculation.name:	
		$fields['name'] = array_key_exists('name', $_POST)? trim($_POST['name']): '';
		$this->meta_error 	= -3;
		if (strlen($fields['name'] )  == 0 ) {
			$this->meta_msg 	= TXT_VBNE_ERR_NOT_FILLED;
			$this->data['error_field'] = 'name';
			parent::respond_data();
			return;
		}
		$fields['publish'] = -1;
		if (array_key_exists('publish', $_POST) )		{ $fields['publish']  			= (int)$_POST['publish'] === 1? 1: -1; }
	
	//	calculation.path:
		$fields['path'] = array_key_exists('path', $_POST)? trim($_POST['path']): '';
		$this->meta_error 	= -4;
		if (strlen($fields['path'] )  == 0 ) {
			$this->meta_msg 	= TXT_VBNE_ERR_NOT_FILLED;
			$this->data['error_field'] = 'path';
			parent::respond_data();
			return;
		}
		
	//	calculation.FID_CAT:
		$fields['FID_CAT'] = array_key_exists('FID_CAT', $_POST)? (int)trim($_POST['FID_CAT']) : -1;
		if ( $fields['FID_CAT'] < 1 ) { $fields['FID_CAT']= -1;}
	
	//	calculation.FID_ARTICLE:
		$fields['FID_ARTICLE'] = array_key_exists('FID_ARTICLE', $_POST)? (int)trim($_POST['FID_ARTICLE']) : -1;
		if ( $fields['FID_ARTICLE'] < 1 ) { $fields['FID_ARTICLE']= -1;}
		
	//	calculation.description:
		$fields['description'] = array_key_exists('description', $_POST)? trim($_POST['description']) : '';
		if ( strlen($fields['description']) == 0 ) {
			$fields['description'] = null;
		}
	
	//	Check UNIQUE_NAME:
		$this->meta_error 	= -4;
		if (!$this->model->unique_name($fields['name'] , $fields['FID_CAT'], $ID) ) {
			$this->meta_msg 	= TXT_VBNE_ERR_NAME_UNIQUE;
			$this->data['error_field'] = 'name';
			parent::respond_data();
			return;
		}
		
	//	Check UNIQUE_PATH:
		$this->meta_error 	= -5;
		if (!$this->model->unique_path($fields['path'] , $fields['FID_CAT'], $ID) ) {
			$this->meta_msg 	= TXT_VBNE_ERR_PATH_UNIQUE;
			$this->data['error_field'] = 'path';
			parent::respond_data();
			return;
		}
		
	
	
		
	//	Add "associative arrays for "inp", "act", "alg" and "sco" from $_POST into $fields:
		$fields = $this->read_satellite_fields($fields);
		
		
	//	Pre-process fields of the satellite tables inp, act, alg and sco: 
	    $this->meta_error 	= -6;
	    $fields = $this->model->pre_process_satellite_fields($fields);
	    
	    
	    if ($fields === false) {
	    	$model_error 				= $this->model->get_error();
	    	$this->meta_msg 			= $model_error['message'];
	    	$this->data['error_field'] 	=  $model_error['field'];
	    	parent::respond_data();
	    	return;
	    }
	       
	
	//	Update / insert record:
		$this->meta_error = -10;
		$check = $this->model->save_ar($fields,$ID);
		if ($check == false){
			$error= $this->model->get_error();
			$known_error = $this->known_error($error['message']);
			$this->meta_msg = $known_error !== false? $known_error['msg'] : $error['message']; 
			
			$this->data['field_warning'] = $known_error['field'];
			parent::respond_data();
			return;
		}
		
	//  Set tab:
		if ( array_key_exists('tab', $_POST) ) {($_SESSION['tab'] = $_POST['tab']); }
		
	//	Respond to client:
		$ar = $this->model->ar();
		$this->data['redirect'] = rtrim(HOME_.'admin/'.$_POST['root_path'].$model->get_category_path_by_id($ar['FID_CAT'],true),'/').'?id='.$ar['ID'];
	
		$this->data['ID'] 	= $ID;
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_DB_SUCCESS;
		
		parent::respond_data();
		return;
	
	
	}
	
	/**
	 *  Add "associative arrays for "inp", "act", "alg" and "sco" from $_POST into $fields :
	 *  
	 *  $field['inp'] => { [input], [position], [FID_OBSERVATION], [description]
	 *                     [act] => { [input], [ID_ACTION], [type], [FID_ARTICLE], [FID_CALCULATION_NEXT], [description] }
	 *                    }
	 *  $field['alg'] => { [rule], [position], [calculation], [params], [FID_ARTICLE], [FID_CALCULATION_NEXT] }
	 *  $field['sco'] => { [FID_SCORE], [GE], [LT], [FID_ARTICLE] }
	 *  
	 *  $fields['to_delete']['inp'] => {ID1, ID2 ,,, IDn }
	 *  $fields['to_delete']['act'] => { [inp1] =>{ID1, ID2 ,,, IDn }, 
	 *                                   [inp2] =>{ID1, ID2 ,,, IDn },
	 *                                    ...
	 *                                   [inpn] =>{ID1, ID2 ,,, IDn }
	 *                                 }
	 *  $fields['to_delete']['alg'] => {ID1, ID2 ,,, IDn }
	 *  $fields['to_delete']['sco'] => {ID1, ID2 ,,, IDn }  
	 *  
	 *  @param  array fields: fields to be saved.
	 *  @return changed array fields to be saved.
	 */
	private function read_satellite_fields($fields){
		
		
		if (array_key_exists('inp', $_POST) ) {$fields['inp'] = json_decode($_POST['inp'], true); }
		if (array_key_exists('alg', $_POST) ) {$fields['alg'] = json_decode($_POST['alg'], true); }
		if (array_key_exists('sco', $_POST) ) {$fields['sco'] = json_decode($_POST['sco'], true); }
		if (array_key_exists('to_delete', $_POST) ) {$fields['to_delete'] = json_decode($_POST['to_delete'], true); }
		
		return $fields;
	}
	
	public function calculate_score(){
	
	//	GET ID;
		if ( !array_key_exists('id', $_REQUEST) )  { return array();}
		$ID = (int)$_REQUEST['id'];
		if ( $ID < 1 )  { return array(); }
		
		$aR = $this->model->get_ar($ID);
		if ( !array_key_exists('ID', $aR) )  { return array(); }
		if ( (int)$aR['ID'] !== $ID)         { return array(); }
		
	//	Calculate and get score:
		$report = $this->calculation($aR);
		if (!is_array($report)) {return array();}
		return array_key_exists('score', $report)? $report['score'] : array();
	}
	
	
	
	
	public function result(){
		
	//	GET ID;
		if ( !array_key_exists('id', $_REQUEST) )  { return $this->view->getHtml('404.html'); }
		$ID = (int)$_REQUEST['id'];
		if ( $ID < 1 )  { return $this->view->getHtml('404.html'); }
		
		$aR = $this->model->get_ar($ID);
		if ( !array_key_exists('ID', $aR) )  { return $this->view->getHtml('404.html'); }
		if ( (int)$aR['ID'] !== $ID)         { return $this->view->getHtml('404.html'); }
		
	//	Calculate and get report:
		$report = $this->calculation($aR);
		$html = $this->view->html_report($report);
		return $html;
	

		
	}
	
	public function calculation($aR) {
	
	//	Read availalbe inputs: 
		$aR['stack'] = $this->calculation_read_inputs($aR);

	//  Calculation:
		$calculator = new calculator($aR);
		$calculator->run();
		
	//  Compaire score with ranges:
		$value 							= $calculator->score;
	    $FID_CALCULATION 				= $aR['ID'];
	    $score 							= $this->model->score($FID_CALCULATION, $value);
	
	//  Data for building report
		$report 						= array();
		$report['FID_CALCULATION'] 		= $FID_CALCULATION;
		$report['score'] 				= $score;
		$report['fid_articles'] 		= $calculator->fid_articles;
		$report['fid_calculations'] 	= $calculator->fid_calculations;
		
		$report['stack'] 				= $calculator->stack;
		$report['log'] 					= $calculator->log;
		$report['log'][] 				= $calculator->score;
		$report['rules'] 				= $calculator->log_rules;
		$report['error'] 				= $calculator->error;
		return $report;
		
	}
	
	private function calculation_read_inputs($aR){
		
	
		
		$stack = array();
		
		$inputs = $aR['inputs'];
		$count  = count($inputs);
		$input  = reset($inputs);
		$i = 0;
		while ($i < $count ){
		// In case an input is always shown or previous inputs contains values that make this input visible: set value to stack:
		
		
			if ( $input['conditional_input'] == false) {
				
			//	Read value and set on stack:	
				$observation = new observation($input['observation']);
				if ( !$observation->is_available ) { break;}
				
				$stack[$input['input']] = $input['observation']['type']==='radio'? $observation->val[0]: $observation->val;
				
			//	Apply show actions to overrule the "conditional_input" parameters for next input when rule is true:
				foreach ($input['actions'] as $action){
					if ($action['type'] == "show"){
						$rule_input = explode(':',$action['rule']);
						
						if (count($rule_input) >=2 ) {
							$rule 			= $rule_input[0];
							$input_to_show 	= trim($rule_input[1]);
							
							if ($observation->apply_rule($rule) == true ){
								
								$inputs[$input_to_show]['conditional_input'] = false;
							}
						}
					}
				}
				
			}
			$i++;
			$input  = next($inputs);
			
		}
				
		
		
	
		
		return $stack;
	}

	
	public function get_popup(){
		
		$this->meta_error 	= -10;
		$this->meta_msg 	= TXT_ERR_UNKNOWN;
		
		
	//	Get name:	
		$name = array_key_exists('name', $_GET)?	$_GET['name']	: null;
		if (is_null($name)) {
			parent::respond_data();
			return;
		}
		
	//	Get val:
		$val  = array_key_exists('val', $_GET)?		$_GET['val']	: '';
		
	//	Get action_ok
		$action_ok  = array_key_exists('action_ok', $_GET)?	$_GET['action_ok']	: '';
		              $this->view->addReplacement('action_ok', $action_ok);
		
	//	Get type of popup:
		$type = null;
		if (strpos($name, 'description') !== false ) {	$type = 'description'; }
		if (strpos($name, 'ARTICLE') !== false ) {		$type = 'article';     }
		if (strpos($name, 'CALCULATION') !== false ) {	$type = 'calculation'; }
		if (strpos($name, 'OBSERVATION') !== false ) {	$type = 'observation'; }
		switch ($type) {
			
		case 'observation':
		require_once MODELS.'observation.php';
		$model = new modelObservation();
		$txt_label = TXT_VBNE_LBL_OBSERVATION;
		$this->data['html'] = $this->popup_html($model, $val,  $txt_label, $type );
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_OK;
		break;
			
		case 'description':
		$this->view->addReplacement('name', $name);
		$this->view->addReplacement('content', $val);
		$this->view->addReplacement('label', strtolower(TXT_VBNE_LBL_MESSAGE));
		$this->data['html'] = $this->view->getHtml('box.textarea.html');
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_OK;
		break;
			
		case 'article':
		require_once MODELS.'article.php';
		$model = new modelArticle();
		$txt_label = TXT_VBNE_LBL_ARTICLE;
		
		$this->data['html'] = $this->popup_html($model, $val, $txt_label, $type);
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_OK;
		break;
		
		case 'calculation':
		require_once MODELS.'calculation.php';
		$model = new modelCalculation();
		$txt_label = TXT_VBNE_LBL_CALCULATION;
		
		//	Exlcude ID of calculation from options to be FID_CALCULATION_NEXT.
			$ID  = array_key_exists('ID', $_GET)?		(int)$_GET['ID']	: -1;
			$exclude = $ID>-1? [$ID]: [];
		
		$this->data['html'] = $this->popup_html($model, $val, $txt_label, $type, $exclude);
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_OK;
		break;
		
		
		
		
		}
		
		
		
		parent::respond_data();
		return;
		
	}
	
	private function popup_html($model, $val, $txt_label, $type, $exclude = array()) {
	
	//	Get ID of record from val:
		$ID = (int)$val>0? (int)$val: -1;
		
	//	GET name:
		$name 			= trim($_GET['name']);
		         		  $this->view->addReplacement('name', $name);
		
	//	Get fill template 'box.select_record_in_category.html' and get html:
		$structure 		= $model->get_options($ID, false);
			
		
		$attr= array();
		$attr['data-sa'] = 'onChange:admin.update_record_selector;name:'.$type.';';
		$select_cat 	 = $this->view->html_select_category($structure['structure_cat'],array('value'=> $structure['FID_CAT'], 'attr'=>$attr) );
		                   $this->view->addReplacement('select_cat', $select_cat);
		
		$options    	 = $this->view->html_options($structure['records'], $txt_label,$ID, -1, $exclude);
		                   $this->view->addReplacement('options', $options);
		
		$popup_title 	 = $this->fieldReplace(TXT_LBL_SELECT, array('name'=>strtolower($txt_label) ) );
		                   $this->view->addReplacement('popup_title', $popup_title);
		                   
		$data_exclude    = count($exclude) > 0? 'yes': 'no';
						   $this->view->addReplacement('data_exclude', $data_exclude);
		
		return $this->view->getHtml('box.select_record_in_category.html');
		
	}
	
	public function get_inp_row(){
		
		$this->meta_error 	= -10;
		$this->meta_msg 	= TXT_ERR_UNKNOWN;
		
		
	//	Get parameters from request:
		$indx	= array_key_exists('indx', $_GET)?	(int)$_GET['indx'] 	: 1;
		$indx 	= $indx < 1? 						1 					: $indx;
		$FID_OBSERVATION	= array_key_exists('FID_OBSERVATION', $_GET)?	(int)$_GET['FID_OBSERVATION'] 	: -1;
		
	//	Create a new input record:	
		$record = $this->model->new_inp($_GET);
		
	//  Required models:
		require_once MODELS.'observation.php';
		$model_observation = new modelObservation();
		
		require_once MODELS.'article.php';
		$model_article 		= new modelArticle();
		
		$model_calculation = $this->model;
		
	//	Add observation
		if ($FID_OBSERVATION<1) {
			$record['observation'] 		      =  $model_observation->ar_new;
		}else {
			$record['observation']	          =  $model_observation->ar($FID_OBSERVATION);
			$record['observation']['options'] =  $model_observation->options_get($FID_OBSERVATION, false);
		}
		
		
	//	Create and return inp_row:
		$this->data['html'] = $this->view->html_inp_row($record , $indx, $model_observation, $model_article, $model_calculation);
		$this->data['row_type'] ='inp';
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_OK;
		parent::respond_data();
		return;
		
	}
	
	public function get_act_row(){
		
		
		$this->meta_error 	= -10;
		$this->meta_msg 	= TXT_ERR_UNKNOWN;
	
	//	Get parameters from request:
		$indx				= array_key_exists('indx', $_GET)?	(int)$_GET['indx'] 	: 1;
		$indx 				= $indx < 1? 1 : $indx;
		$inp 				= array_key_exists('inp', $_GET)? 	(int)$_GET['inp'] 	: -1;
		$FID_OBSERVATION	= array_key_exists('FID_OBSERVATION', $_GET)?	(int)$_GET['FID_OBSERVATION'] 	: -1;
		
		if ($inp < 1) {
			parent::respond_data();
			return;
		}
	
	//	Create a new act record:
		$record= $this->model->new_act($_GET);
		
	//  Required models:
		require_once MODELS.'observation.php';
		$model_observation = new modelObservation();
		
		require_once MODELS.'article.php';
		$model_article 		= new modelArticle();
		
		$model_calculation = $this->model;
		
	//	Get valid_rules for $FID_OBSERVATION
		if ($FID_OBSERVATION<1) {
			$record_obs		=  $model_observation->ar_new;
		}else {
			$record_obs	= $model_observation->ar($FID_OBSERVATION);
			$record_obs['options'] =  $model_observation->options_get($FID_OBSERVATION, false);
		}
		$observation 	= new observation($record_obs );
	    $valid_rules	= $observation->valid_rules();
	
	//	Create and return act_row:
		$this->data['html'] = $this->view->html_act_row($inp, $indx, $record, $valid_rules, $model_article, $model_calculation);
		$this->data['row_type'] ='act_'.$inp;
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_OK;
		parent::respond_data();
		return;
	
	}
	
	public function get_alg_row(){
		
		$this->meta_error 	= -10;
		$this->meta_msg 	= TXT_ERR_UNKNOWN;
		
	//	Get parameters from request:
		$indx	= array_key_exists('indx', $_GET)?	(int)$_GET['indx'] 	: 1;
		$indx 	= $indx < 1? 						1 					: $indx;
		
	//	Create a new act record:
		$record= $this->model->new_alg($_GET);
		
	//  Required models:
		require_once MODELS.'observation.php';
		$model_observation = new modelObservation();
		
		require_once MODELS.'article.php';
		$model_article 		= new modelArticle();
		
		$model_calculation = $this->model;
	
	//	Get valid calcultions:
		$formulas = array();
		foreach ($model_calculation->valid_calculations() as $formula ){
			$formulas[$formula ] = $formula;
		}
		
	//	Create and return alg_row:
		$this->data['html'] = $this->view->html_alg_row($indx, $formulas, $record, $model_article, $model_calculation);
		$this->data['row_type'] ='alg';
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_OK;
		parent::respond_data();
		return;
	}
	
	public function get_sco_row(){
		
		$this->meta_error 	= -10;
		$this->meta_msg 	= TXT_ERR_UNKNOWN;
	
	//	Get parameters from request:
		$indx	= array_key_exists('indx', $_GET)?	(int)$_GET['indx'] 	: 1;
		$indx 	= $indx < 1? 						1 					: $indx;
		
	//	Create a new sco record:
		$record= $this->model->new_sco($_GET);
		
	//  Required models:
		require_once MODELS.'article.php';
		$model_article 		= new modelArticle();
		
		
	//	Get available score options for this appplication:
		$tbl_score = new table('score');
		$available = array();
		foreach ( $tbl_score->select_all() as $row ){
			$available[$row['ID']] =$row['name'];
		}
		
	//	Create and return sco_row:
		$this->data['html'] = $this->view->html_sco_row($indx, $record, $available, $model_article);
		$this->data['row_type'] ='sco';
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_OK;
		parent::respond_data();
		return;
	
	}
	
	public function update_result(){
		
		$this->data['time_stamp'] = array_key_exists('time_stamp', $_GET)? (int)$_GET['time_stamp'] : -1;
		

	 // Get result;	
		$this->view->addReplacement('score', $this->score);
		$this->data['result'] =$this->score;
		$this->data['html'] = $this->view->getHtml('score.html');
		
		
		
		$score = $this->score;
		
		$score['name'] = strtolower($score['name']);
	
		$attr = array();
		$ID = (int)$_GET['id'];
		$attr['href'] = $attr['href'] = HOME_.'sleutels/calculaties/result?calculation='.$ID.'&id='.$ID; // parameter 'calculation is needed for re-direct and updata return stack.
		$attr['target'] = "_self";
		$attr['title'] = TXT_VBNE_TITLE_REPORT;
		$score['name'] = $this->view->wrapTag('a',$score['name'],$attr);
			
		$this->view->addReplacement('score',$score);
		$this->data['html_stroke_top'] = $this->view->getHtml('score.stroke_top.html');
		
		
		
		
		$this->meta_error 	= 0;
		$this->meta_msg 	= 'update_result()';
		parent::respond_data();
		return;
		
	}

}
<?php	// _system/views/calculation.php

class viewCalculation extends view {
	
	
	private $type_class 		= array();
	private $type_checked 		= array();
	private $show_valuefields 	= '';
	private $show_options 		= ' hide';
	private $option_tbody		= '';
	
	private $viewObservation    = null;
	private $modelCalculation   = null;
	private $tab        		= array();
	
	public $score 				= array();
	
	public function __construct($template = 'form.calculation.html'){
		
		$this->has_category_record_structure 	= true;
		$this->label							= TXT_VBNE_LBL_CALCULATION;
		$this->label_plu 						= TXT_VBNE_LBL_CALCULATIONS;
		$this->root_path 						= 'calculations/';
		$this->record_name						= "calculation";
		parent::__construct($template);
		
		require_once VIEWS.'observation.php';
		$this->viewObservation = new viewObservation();
	
	// Add path field to form new
		$this->set_inputfield_path();
	
	//	Set input, algorithm, score tab
		$val = 'inp';
		if (array_key_exists('tab', $_SESSION) ){
			$val =   strtolower( trim($_SESSION['tab']) ) ;
			unset($_SESSION['tab']);
		}
		$tabs =  ['inp','alg','sco'];
		$val  = in_array($val, $tabs)? $val : 'inp';
		foreach ( $tabs as $tab ) {
			$this->tab[$tab] = array();
			$this->tab[$tab]['active'] =     $tab == $val ?     ' active' 	: '';
			$this->tab[$tab]['container'] =  $tab == $val ? 	'' 			: ' hide';
		}
		$this->tab['val'] = $val;
		$this->addReplacement('tab', $this->tab);

		
	}
	
	private function model_calculation () {
	 	
	 	if ( $this->modelCalculation  === null ){
	 		require_once MODELS.'calculation.php';
	 		$this->modelCalculation = new modelCalculation();
	 	}
	 	return $this->modelCalculation;
	 }
	
	/**
	 * Assigns replacements based on content of active record
	 * @param ar: array with active record
	 */
	public function assignments_by_ar($ar = array()){
	
	//	Stop in case $ar is empty or incorrect:
	 	if ( is_array($ar) === false) 	{ return; }
	 	if ( count($ar) === 0 )			{ return; }
	 	
	//	Get models:
	 	$model_article 		= $this->model_article;
	 	$model_calculation 	= $this->model_calculation();
	 	require_once MODELS.'observation.php';
	 	$model_observation	= new modelObservation();
	 	
	//	Get full record, including inputs, rules and scores:
	 	$aR = $model_calculation->get_ar($ar['ID']);
	 
	//	Selection block for Introduction article(s):
	 	$this->add_replacements_intro_article_selectors($aR,$model_article);
	
	//	Inputs:
		$html = $this->html_inp($aR['inputs'], $model_observation, $model_article, $model_calculation);
		$this->addReplacement('inputs', $html);
		
	//	Algorithms: 
		$html = $this->html_alg($aR['rules'], $model_article, $model_calculation);
		$this->addReplacement('algorithm', $html);
		
	//	Scores
		$html = $this->html_sco($aR['scores'], $model_article);
		$this->addReplacement('scores', $html);
	   
	 	return;
	 
	 }

	/**
	 * Fills replacements 'select_article_category' and 'select_fid_article', 
	 * on admin_form to select FID_ARTICLE_CAT and FID_ARTICLE for the introduction.
	 * 
	 * @param array  $ar active record of calculation
	 * @param object $model_article instance of model article.
	 */
	private  function add_replacements_intro_article_selectors($ar,$model_article){
	 	
	//	Get FID_ARTICLE FID_ARTICLE_CAT:
	 	$FID_ARTICLE = $ar['FID_ARTICLE'];
	 	if (is_null($FID_ARTICLE)) {
	 		$FID_ARTICLE 		= -1;
	 		$FID_ARTICLE_CAT 	= -1;
	 	
	 	} else {
	 		$article 			= $model_article->ar($FID_ARTICLE);
	 		$FID_ARTICLE 		= array_key_exists('ID', $article)? $article['ID'] : -1;
	 		$FID_ARTICLE_CAT 	= array_key_exists('FID_CAT', $article)? $article['FID_CAT'] : -1;
	 	}
	 
	// Get and replace 'select_article_category':
	 	$options = array();
	 	$options['value'] =  $FID_ARTICLE_CAT;
	 	$options['name']  = 'FID_ARTICLE_CAT';
	 	$html_select = $this->html_select_acticle_category($options);
	 	$this->addReplacement('select_article_category', $html_select);
	 	
	//	Get and replace 'select_fid_article':
	 	$articles = $model_article->get_records_by_category($FID_ARTICLE_CAT, false); // get articles for FID_ARTICLE_CAT regardless of publish.
	 	
	 	$attr= array();
	 	if ( count($articles) == 0 ) {
	
	 	//	No articles: 'select_fid_article': empty and hidden.
	 		$attr['class'] = 'ui-closed';
	 		$attr['data-sa'] = 'FID_ARTICLE_CAT';
	 		$html_select = $this->wrapTag('div','<!--no options-->',$attr);
	 		$this->addReplacement('select_fid_article', $html_select);
	 		
	 	} else {
	 	
	 	//	Articles available: fill 'select_fid_article' with options and select FID_ARTICLE
	 		$options = array();
	 		foreach ($articles as $article){
	 			$attr= array();
	 			$attr['value'] =$article['ID'];
	 			if ( $article['ID'] == $FID_ARTICLE ) { $attr['selected'] =  "selected"; }
	 			$options[] = $this->wrapTag('option',$article['name'],$attr);
	 		}
	 		$attr= array();
	 		$attr['name']  = 'FID_ARTICLE';
	 		$attr['value'] = $FID_ARTICLE;
	 		$html_select = $this->wrapTag('select',$options,$attr);
	 		$attr= array();
	 		$attr['data-sa'] = 'FID_ARTICLE_CAT';
	 		$html_select = $this->wrapTag('div',$html_select,$attr);
	 		$this->addReplacement('select_fid_article', $html_select);
	 	}
	 	return; 
	}
	 

     
 //	METHODS for front-end views:________________________________________________________ 
     
	public function fe_page($structure = array()){	
		
	//	Check input-array:
		if ( !is_array($structure)) {return '';}
		if ( !array_key_exists('fields',  $structure) ) { return ''; }
		if ( !array_key_exists('records', $structure) ) { return ''; }
		
	//	When request paramter "id" is set: build calultation-page:
		$ID =  array_key_exists('id', $_REQUEST)? (int)$_REQUEST['id'] : -1;
		if ( $ID > 0 ) {
			if ( array_key_exists($ID, $structure['records']) ){
				return $this->fe_page_calculation($ID,$structure);
			} else {
				return $this->fe_page_category($structure);
			}
		} else {
			return $this->fe_page_category($structure);
	    }
	}
		
	private function fe_page_category($structure){	
	//	Build page:
		$html= array();
		if ( array_key_exists('publish', $structure['fields'])){
			if ( (int)$structure['fields']['publish'] !== 1 ) {
				return $this->getHtml('404.html');
			}
		}
	
	//	Header and introduction:
		$fields = array();
		$fields['name'] 	= array_key_exists('name',  $structure['fields'])? $structure['fields']['name']: '';
			
		$introduction =  $this->fe_introduction($structure['fields']);
		if ($introduction !== false){
			$fields['introduction'] = $introduction;
		}
		$html[] = parent::fe_page($fields);
				
			
	//	Calculation-list:
		$html[] = $this->fe_menu($structure['records']);
		
	//	Menu to other calculation categories:
		$html[] = $this->wrapTag('h3',TXT_VBNE_LBL_OTHER_CALCULATION_CAT, array('class'=>'margin-top-20') );
		$html[] = $this->fe_page_menu_other_categories();
			
	//	Menu to next level:
		$html[] = $this->fe_menu_cat($structure['children']);
		return $this->wrapTag('div',$html);
	
	}
		
	private function fe_page_calculation($ID,$structure){
		
		
	//	Build page:
		$html= array();

	//	Calculation:
		$category 	= $this->get_category_by_request_path(3);
		$calc 		= $structure['records'][$ID];
		if ((int)$calc['publish'] !== 1){return $this->getHtml('404.html');}
		
		$page_title = $category['name'].'&nbsp;-&nbsp;'.strtolower($calc['name']);
		$html[]= $this->wrapTag('h2',$page_title);
		$html[] = $this->html_calculation($calc);
		
		$html[] = $this->wrapTag('div','', array('class'=>'clearfix') );
	//	Menu to other calculations:	
		$html[] = $this->wrapTag('h3',TXT_VBNE_LBL_OTHER_CALCULATION, array('class'=>'margin-top-40') );
		$html[] = $this->fe_page_menu_other_calculations($ID,$structure);
		
	//	Menu to other calculation categories:	
		$html[] = $this->wrapTag('h3',TXT_VBNE_LBL_OTHER_CALCULATION_CAT, array('class'=>'margin-top-20') );
		$html[] = $this->fe_page_menu_other_categories();
		
		return $this->wrapTag('div',$html);
	}
	
	private function fe_page_menu_other_calculations($ID,$structure){	
		
	//	Menu to other calculations:
		$others = $structure['records'];
		unset($others[$ID]);
		return $this->fe_menu($others);
	}
	
	private function fe_page_menu_other_categories(){
	
	//	Step to top-category and build lead for href:
		$request = $this->request;
		$href  	 = PATH_;
		$path  	 =  array_shift($request);
		$href 	.= $path.'/';
		$path  	 =  array_shift($request);
			
		$href 	.= $path.'/';
		$path 	= array_shift($request);
	
	//	Load root-level of the ategory-structure:
		$structure_cat 	= array();
		$structure_root = $this->get_root_structure();
		foreach ($structure_root['children'] as $field) {
			$structure_cat[$field['path']] = $field;
		}
			
	//	Get category-menu items: alternative options of all parent categories
		$a 				= array();
		$attr 			= array();
		$attr['target']	= '_self';
			
		while ( $path !== null){
		//	Create anchors for menu:
			foreach ($structure_cat as $indx_path => $field) {
				if ($path != $indx_path && (int)$field['publish'] ==1) {
					$attr['href'] 	= $href.$field['path'];
					$attr['title'] 	= $this->fieldReplace(TXT_VBNE_TITLE_OPEN,array('name'=>strtolower($field['name']) ) );
					$a[] 			= $this->wrapTag('a',$field['name'],$attr);
				}
			}
	
		//	Next level:
			$structure_cat	 = array_key_exists($path, $structure_cat)? 		$structure_cat[$path] 		: array();
			$structure_cat	 = array_key_exists('children', $structure_cat)? 	$structure_cat['children'] 	: array();
	
			$href 		 	.= $path.'/';
			$path 			 = count($request)>0? array_shift($request): null;
	
		}
		return $this->wrapTag('nav',$a, array('class'=>'menu') );
	}
	
	private function html_calculation($calc){
		
	//	Build page:
		$html= array();
	
	//	Add return-button to previous calculation (comming from link):
		$html_return_button = $this->html_stack_return_button();
		if ($html_return_button != ''){
			$html[] =$html_return_button;
		}
		
	//	Introduction:
		$content = array();
		if ( is_null($calc['FID_ARTICLE']) === false) {
			$introduction = $this->fe_introduction( array('FID_ARTICLE'=>(int)$calc['FID_ARTICLE']) );
			if ($introduction !== false) {
				foreach($introduction as $row){
					$content[] = $this->wrapTag('h3',$row['name']);
					$content[] = $this->wrapTag('div',htmlspecialchars_decode($row['content'],ENT_HTML5));
				}
				$html[] = $this->wrapTag('section',$content);
			}
		}
		if ( is_null($calc['FID_ARTICLE']) && count($content) == 0) {
			if (!is_null($calc['description']) && $calc['description']!= ''){
				$html[] = $this->wrapTag('section',htmlspecialchars_decode($calc['description'],ENT_HTML5));
			}
		}
		
		
		if ( array_key_exists("raw", $_REQUEST) && array_key_exists("DEBUG", $_SESSION) ){
				$html[] = array_pretty_print( $calc);
				$attr 			= array();
				$attr['class'] 	= 'calculation';
				return $this->wrapTag('div',$html, $attr);
		}
		$calculation = $calc['calculation'];
		$ID = $calculation['ID'];
		$model_calculation = $this->model_calculation();
		$calc_fields= $model_calculation->get_ar_with_path_to_record($ID);
		$show_report_button = true;
		foreach ($calculation['inputs'] as $input ) {
			
			$html_input = array();
			
		//	Merge description of input and observation:
			$description_input = array_key_exists('description', $input)? htmlspecialchars_decode($input['description'], ENT_HTML5): '';
			$description_observation = array_key_exists('description', $input['observation'])? htmlspecialchars_decode($input['observation']['description'], ENT_HTML5): '';
			$description = $description_observation.$description_input;
			$description = $this->model_article->content_decode($description);
			$input['observation']['description'] = $description;
			$attr= array();
			$attr['data-sa'] ='input:'.$input['input'].';';
		
		//	Read data-rules
			$data_rule 			=  array();
			$data_rule['attr'] 	='';
			$data_rule['glue'] 	='';
			$data_rule_show 	= $data_rule;
			$data_rule_msg 		= $data_rule;
			$data_rule_info		= $data_rule;
			$data_rule_calc 	= $data_rule;
			$data_rule_report 	= $data_rule;
			$msg  = array();
			$info = array();
			$calc = array();
			$info[] = $this->wrapTag('h3',TXT_VBNE_LBL_ARTICLES_BACKGROUND);
			$calc[] = $this->wrapTag('h3',TXT_VBNE_LBL_CALCULATIONS_NEXT );
		
		//	Add input to name for debug only	
			$debug = array_key_exists('DEBUG', $_SESSION)? (int)$_SESSION['DEBUG'] :0;
			$debug = array_key_exists('numbers', $_SESSION)? 1 :$debug;
			if ($debug> 0){
				$input['observation']['name'] = $input['input'].'). '.$input['observation']['name'] ;
			}
			
			foreach ($input['actions'] as $action){
			
				$type 		= $action['type']; 
				$ID_ACTION	= $action['ID_ACTION'];
				$rule		= $action['rule'];
				
				switch ( $type  ) {
					
					case 'show':
					$data_rule_show['attr'].= $data_rule_show['glue'].$rule;
					$data_rule_show['glue'] = '|';
					break;
					
					
					case 'message': if ( trim($action['description']) != ""){
					$data_rule_msg['attr'].= $data_rule_msg['glue'].$rule.':'.$ID_ACTION;
					$data_rule_msg['glue'] = '|';
					
					$message = htmlspecialchars_decode($action['description'], ENT_HTML5);
					$attr = array();
					$attr['data-action'] = $ID_ACTION;
					$attr['class'] 		 = 'ui-closed';
					$msg[] = $this->wrapTag('section', $message, $attr);
					} break;
					
					
					case 'article': if ( !is_null($action['FID_ARTICLE']) ){
					$article = $this->model_article->get_ar_with_path_to_record($action['FID_ARTICLE']);
					if ( array_key_exists('ID', $article) ){
						
						$data_rule_info['attr'].= $data_rule_info['glue'].$rule.':'.$ID_ACTION;
						$data_rule_info['glue'] = '|';
						
						$attr = array();
						$attr['data-action'] 	= $ID_ACTION;
						$attr['class'] 			= 'ui-closed block';
						$attr['data-pos']       = "";
						$attr['target'] 		= '_self';
						$attr['title'] 			= $this->fieldReplace(TXT_VBNE_TITLE_OPEN, array('name'=>$article['name']) );
						$attr['href'] 		    = PATH_.'article/'.$article['href'].'&calculation='.$ID;
						$info[]  = $this->wrapTag('a', $article['name'], $attr);
						
					}}break;
					
					case 'calculation': if ( !is_null($action['FID_CALCULATION_NEXT']) ){
					$model = $this->model_calculation();
					$calculation_next = $model->get_ar_with_path_to_record($action['FID_CALCULATION_NEXT']);
					if ( array_key_exists('ID', $calculation_next) ){
						
						$data_rule_calc['attr'].= $data_rule_calc['glue'].$rule.':'.$ID_ACTION;
						$data_rule_calc['glue'] = '|';
						
						$attr = array();
						$attr['data-action'] 	= $ID_ACTION;
						$attr['data-pos']       = "";
						$attr['class'] 			= 'ui-closed block';
						$attr['target'] 		= '_self';
						$attr['title'] 			= $this->fieldReplace(TXT_VBNE_TITLE_OPEN, array('name'=>$calculation_next['name']) );
						$attr['href'] 		    = PATH_.'sleutels/calculaties/'.$calculation_next['href'].'&calculation='.$ID;
						$calc[] = $this->wrapTag('a', $calculation_next['name'], $attr);
						
					}} break;
					
					case 'report':	
					$show_report_button = false;
					$data_rule_report['attr'].= $data_rule_report['glue'].$rule.':'.$ID_ACTION;
					$data_rule_report['glue'] = '|';
					break;
					
				}
			}
			
		//	Observation:
			$html_input[] = $this->viewObservation->fe_observation($input['observation']);
		
		//	Merge content:
			$html_input[] = $this->wrapTag('div', $msg, array('class'=>'msg margin-top-40 ui-closed'));
			$html_input[] = $this->wrapTag('div', $info, array('class'=>'info  margin-top-40 ui-closed'));
			$html_input[] = $this->wrapTag('div', $calc, array('class'=>'calc  margin-top-40 ui-closed'));
			
			$attr = array();
			if ( $data_rule_show['attr'] 	!= '' 	){ $attr['data_rule_show'] 		= $data_rule_show['attr']	; }
			if ( $data_rule_msg['attr'] 	!= '' 	){ $attr['data_rule_msg'] 		= $data_rule_msg['attr']	; }
			if ( $data_rule_info['attr'] 	!= '' 	){ $attr['data_rule_info'] 		= $data_rule_info['attr']	; }
			if ( $data_rule_calc['attr'] 	!= '' 	){ $attr['data_rule_calc'] 		= $data_rule_calc['attr']	; }
			if ( $data_rule_report['attr'] 	!= '' 	){ $attr['data_rule_report'] 	= $data_rule_report['attr']	; }
			$attr['class'] =  $input['conditional_input']? 'input conditional ui-closed' : 'input';
		
			$attr['id'] ='input-'.$input['input'];
			$attr['name'] ='input-'.$input['input'];
			$html[] = $this->wrapTag('section', $html_input, $attr);
		}
	//  Result and Link to result page:
		$attr= array();
		$attr['id']= 'score';
		if ( array_key_exists('value', $this->score)){
			$this->addReplacement('score', $this->score);
			$score = $this->getHtml('score.html');
		}
		$attr= array();
		$attr['id']= 'score';
		$attr['class']= 'float-left';
		
		$html[] =$this->wrapTag('div',$score,$attr);
		
		$attr= array();
		$attr['href'] = $attr['href'] = PATH_.'sleutels/calculaties/result?calculation='.$ID.'&id='.$ID;
		$attr['target'] = "_self";
		$attr['id'] = "open-report";
		$attr['class'] = $show_report_button? 'button' :'button ui-closed';
		$attr['title'] = TXT_VBNE_TITLE_REPORT;
		$html[] = $this->wrapTag('a',strtolower(TXT_VBNE_LBL_REPORT),$attr);
		
		
	//	Return calculation div:	
		$attr 			= array();
		$attr['class'] 	= 'calculation';
		$attr['data-calculation-id'] = $ID;
		return $this->wrapTag('div',$html, $attr);
		
		
	}


//	METHODS for adding fields for a view:_______________________________________________	
	
	/**
	 * Adds field ['FID_ARTICLE_txt'] and ['article']={['title'], ['href']}
	 * @param array  $record: record with 'FID_ARTICLE' field
	 * @param object $model_article
	 * 
	 * @return record array with additional fields
	 */
	public function add_article_fields ($record, $model_article ){
		
	//	Check record:
		if (!array_key_exists('FID_ARTICLE', $record) ) { return $record;}
		 
	//  Create text field:
		$record['FID_ARTICLE_txt'] 			= is_null($record['FID_ARTICLE'])? 			'NULL' :$record['FID_ARTICLE'];
	
	//	Create ['article']['href'] and ['article']['title'] fields:
		$record['article']  				= array();
		$record['article']['title']			= TXT_VBNE_TITLE_ARTICLE_INDEX;
		$record['article']['data_title']	= TXT_VBNE_TITLE_ARTICLE_INDEX;
		$record['article']['href']  = '';
		if ( !is_null($record['FID_ARTICLE']) ){
			$article = $model_article->get_ar_with_path_to_record($record['FID_ARTICLE']);
			if (array_key_exists('name', $article)) {
				$record['article']['title']	= $this->fieldReplace(TXT_VBNE_TITLE_ARTICLE_VIEW, array('name'=>$article['name']) );
			}
			$record['article']['href']  = array_key_exists('href', $article)? $article['href']: '';
		}
		 
		return $record;
	}
	
	/**
	 * Adds field ['FID_CALCULATION_NEXT_txt'] and ['calculation_next']={['title'], ['href']}
	 * @param array  $record: record with 'FID_CALCULATION_NEXT' field.
	 * @param object $model_calculation
	 *
	 * @return record array with additional fields
	 */
	public function add_calculation_fields ($record, $model_calculation){
		
	//	Check record:
		if (!array_key_exists('FID_CALCULATION_NEXT', $record) ) { return $record;}
		 
	//  Create text field:
		$record['FID_CALCULATION_NEXT_txt'] 	= is_null($record['FID_CALCULATION_NEXT'])? 'NULL' : $record['FID_CALCULATION_NEXT'];
		 
	//	Create ['calculation_next']['href'] and ['article']['title'] fields:
		$record['calculation_next'] 				= array();
		$record['calculation_next']['title'] 		= TXT_VBNE_TITLE_CALCULATION_INDEX;
		$record['calculation_next']['data_title'] 	= TXT_VBNE_TITLE_CALCULATION_INDEX;
		$record['calculation_next']['href']			= '';
		if ( !is_null($record['FID_CALCULATION_NEXT']) ){
			$calculation = $model_calculation->get_ar_with_path_to_record($record['FID_CALCULATION_NEXT']);
			if (array_key_exists('name', $calculation) ) {
				$record['calculation_next']['title']	= $this->fieldReplace(TXT_VBNE_TITLE_CALCULATION_VIEW, array('name'=>$calculation['name']) );
			}
			$record['calculation_next']['href']  = array_key_exists('href', $calculation)? $calculation['href']: '';
		}
		 
		return $record;
		 
	}

	/**
	 * The field action['rule'] "RULE(par;par;par):input" is interpretated and split into:
	 * action['rule']   =  rule name.
	 * action['params]  = ";-seperated" parameters.
	 * action['input]   = 'name of input in case of an show action or null
	 * @param array action
	 * return the action array including the additional fields.
	 */
	public function decode_action_rule($action){
	
	//	Check in $actions
		if ( !is_array($action) ){return $action;}
		if ( !array_key_exists('rule', $action) ) {return $action;}
		$action_rule 		= $action['rule'];
	
	//	Default:
		$action['rule'] 	= '#NA';
		$action['params'] 	= '';
		$action['input']    = null;
	
	//	Get rule and parameters
		$delimiter_pos 		= strpos($action_rule, '(');
		if ($delimiter_pos === false ) {return $action;}
	
		$rule 				= substr($action_rule, 0,$delimiter_pos);
		$action['rule'] 	= strtoupper( trim($rule) );
		$len 				= strlen($action_rule);
		$delimiter_pos      = $delimiter_pos +1;
		$params 			= substr($action_rule, $delimiter_pos, $len-$delimiter_pos);
	
	//	In case $action['rule'] contained ':' pointing to an input: get input
		$delimiter_pos = strpos($params, ':');
		if ($delimiter_pos !== false ) {
			$len 				= strlen($params);
			$input 				= substr($params, $delimiter_pos+1, $len-$delimiter_pos);
			$action['input'] 	= trim($input);
			$params				= substr($params, 0,$delimiter_pos);
		}
	
	//	Sanatize params:
		$params = trim($params);
		$params = substr($params, 0, -1);
		$params = explode(";",$params);
		$params = array_map("trim",$params);
		$action['params']  = implode(";",$params);
	
		return $action;
	}



//	METHODS to fill in a child template:________________________________________________	
	
	/**
	 * Fills in template 'form.calculation_inp.html'
	 * @param array   $records: from table calculation_inp
	 * @param object  $model_observation
	 * @param object  $model_article
	 * @param object  $model_calculation
	 * @return string html
	 */
	public function html_inp($records, $model_observation, $model_article, $model_calculation){
	
	//	Build table:
		$rows= array();
		$i= 1;
		foreach ($records as $R){
			$rows[] = $this->html_inp_row($R, $i, $model_observation, $model_article, $model_calculation);
			$i++;
		}
		$tbody = $this->wrapTag('tbody',$rows);
		$this->addReplacement('tbody', $tbody);
		return $this->getHtml('form.calculation_inp.html');
	}
	
	/**
	 * Fills in template 'form.calculation_inp.row.html'
	 * @param array   $record: from table calculation_inp
	 * @param integer $i: row number
	 * @param unknown $model_observation
	 * @param unknown $model_article
	 * @param unknown $model_calculation
	 * @return string html
	 */
	public  function html_inp_row($record, $i, $model_observation, $model_article, $model_calculation){
		
		 
	//	Get valid_rules:
		$observation 	= new observation($record['observation']);
		$valid_rules    = $observation->is_available ?  $observation->valid_rules()  : $observation->valid_rules('value'); 
		
	//	Decode description field:
		$record['description'] = htmlspecialchars_decode($record['description'], ENT_HTML5);
		 
	//	Add field[href] : href to admin-page of observation to $record['observation']:
		$record['observation']['href'] 	= '';
		if ( array_key_exists('ID', $record['observation']) ){
			$observation 				= $model_observation->get_ar_with_path_to_record($record['observation']['ID']);
			$record['observation']['href'] 	=  array_key_exists('href', $observation)? $observation['href'] : '';
		}
		 
	//	Assign $record to 'input' $i to 'i':
		$this->addReplacement('input', $record);
		$this->addReplacement('i', $i);
		 
	//	Actions:
		$html= $this->html_act_tbody($i, $record['actions'], $valid_rules, $model_article, $model_calculation);
		$this->addReplacement('tbody', $html);
	
		return $this->getHtml('form.calculation_inp.row.html');
	}
	
	/**
	 * Creates a tbody object with "form.calculation_act.row.html" rows
	 * @param integer $i: index to input
	 * @param array   $records: from table calculation_act
	 * @param array   $valid_rules: RULES that are valid for input's observation type.
	 * @param object  $model_article
	 * @param object  $model_calculation
	 * @return string html
	 */
	public function html_act_tbody($i, $records, $valid_rules, $model_article, $model_calculation) {
	
		$rows = array();
		$ii= 1;
		foreach($records as $record){
			$rows[] = $this->html_act_row($i, $ii, $record, $valid_rules, $model_article, $model_calculation);
			$ii++;
		}
		return $this->wrapTag('tbody',$rows);
	}
	
	/**
	 * Fills  "form.calculation_act.row.html"
	 * @param integer $i:  index to input
	 * @param integer $ii: index to action
	 * @param array  $record: from table calculation_act
	 * @param array  $valid_rules: RULES that are valid for input's observation type.
	 * @param object $model_article
	 * @param object $model_calculation
	 * @return string html
	 */
	public function html_act_row($i, $ii, $record,$valid_rules, $model_article, $model_calculation) {

	/*	Convert $record['rule'] "RULE(par;par;par):input" into:
	 *  $record['rule']   : RULE
	 *  $record ['params] : ;-seperated parameters.
	 *  $record['input]   : name of input or null
	 */
		$record = $this->decode_action_rule($record);
		
	//	Set selected for type:
		$record['type_message']     			= '';
		$record['type_show']     				= '';
		$record['type_article']     			= '';
		$record['type_calculation'] 			= '';
		$record['type_report'] 					= '';
		$record['type_'.$record['type']] 		= ' selected="selected"';
		
	//	Options for act_rule selector:
		$options = $this->html_options($valid_rules,TXT_VBNE_LBL_ACT_RULE,$record['rule'] );
		
		$this->addReplacement('options', $options);
		
	//	FID_ARTICLE: text label and link to view.
		$record = $this->add_article_fields ($record, $model_article );
		 
	//	FID_CALCULATION_NEXT: text label and link to view.
		$record = $this->add_calculation_fields ($record, $model_calculation);
		
	//	Add record and return html for this row:
		$this->addReplacement('record', $record);
		$this->addReplacement('i', $i);
		$this->addReplacement('ii', $ii);
		return $this->getHtml('form.calculation_act.row.html');
	}
	
	/**
	 * Fills  "form.calculation_alg.html"
	 * @param  array $records: from table calculation_alg
	 * @param  object $model_article
	 * @param  object $model_calculation
	 * @return string html
	 */
	public function html_alg($records, $model_article, $model_calculation){
	
	// Get valid formulas  for selector:
		$formulas = array();
		foreach ($model_calculation->valid_calculations() as $formula ){
			$formulas[$formula ] = $formula;
		}
	
	//	Build tbody:
		$rows	= array();
		$i 		= 1;
		foreach ($records as $record) {
			$rows[] = $this->html_alg_row( $i, $formulas, $record, $model_article, $model_calculation);
			$i++;
		}
		$tbody = $this->wrapTag('tbody',$rows);
		$this->addReplacement('tbody', $tbody);
	
		return $this->getHtml('form.calculation_alg.html');
	
	}
	
	/**
	 * Fills  "form.calculation_alg.row.html"
	 *
 	 * @param integer $i:  index to algorithm rule
	 * @param  array $formulas: availalbe formulas for algorithm rules
	 * @param  array $record: from table calculation_alg
	 * @param  object $model_article
	 * @param  object $model_calculation
	 * @return string html
	 */
	public function html_alg_row( $i, $formulas, $record, $model_article, $model_calculation){
	
	//	Options for selector alg_calculation
		$options = $this->html_options($formulas,TXT_VBNE_LBL_ALG_RULE,$record['calculation'] );
		$this->addReplacement('options', $options);
		 
	//	FID_ARTICLE: text label and link to view.
		$record = $this->add_article_fields ($record, $model_article );
		 
	//	FID_CALCULATION_NEXT: text label and link to view.
		$record = $this->add_calculation_fields ($record, $model_calculation);
		 
		$this->addReplacement('record', $record);
		$this->addReplacement('i', $i);
		return $this->getHtml('form.calculation_alg.row.html');
		 
	}
	/**
	 * Fills  "form.calculation_sco.html" 
	 * 
	 * @param array $records: records from table "calculation_sco"
	 * @param object $model_article
	 * @return string html
	 */
	public function html_sco($records, $model_article ){
		
	//	Get available score options for this appplication:
		$tbl_score = new table('score');
		$available = array();
		foreach ( $tbl_score->select_all() as $row ){
			$available[$row['ID']] =$row['name'];
		}
		
	//	Build tbody	
		$rows = array();
		$i=1;
		foreach ($records as $record){
			$rows[]= $this->html_sco_row($i, $record, $available, $model_article);
			$i++;
		}
		$tbody = $this->wrapTag('tbody',$rows);
		$this->addReplacement('tbody', $tbody);
		
		return $this->getHtml('form.calculation_sco.html');
		 
	}
	/**
	 * Fills  "form.calculation_sco.row.html" 
	 * 
	 * @param inreger 	$i: index score-row
	 * @param array 	$record: from table "calculation_sco"
	 * @param array 	$available: availalbe scores from table "score"
	 * @param object 	$model_article
	 * 
	 * @return string html 
	 */
	public function html_sco_row($i,$record, $available, $model_article){
	
	//	Score selector:
		$options = $this->html_options($available, TXT_VBNE_LBL_SCO_SCORE,$record['FID_SCORE'] );
		$this->addReplacement('options', $options);
		
	//	FID_ARTICLE: text label and link to view.
		$record = $this->add_article_fields ($record, $model_article );
		
		$this->addReplacement('record', $record);
		$this->addReplacement('i', $i);
		
		return $this->getHtml('form.calculation_sco.row.html');
			
	}
	
    public function html_report($report) {
    //	Build page:
    	$html= array();
    	
   
    	
    	
    //	Get name of calculation:	
    	$model_calculation = $this->model_calculation();
    	$full_name =$model_calculation->get_name_with_path($report['FID_CALCULATION']);
    	
    //	Result article:
    	$FID_ARTICLE = array_key_exists('FID_ARTICLE', $report['score'])? (int)$report['score']['FID_ARTICLE'] :-1;
    	$pdf= '<a  class="btn pdf float-right" href="'.PATH_.'pdf?id='.$report['FID_CALCULATION'].'" target="_self" title="Exporteer naar pdf."></a>';
    	
    	if ($FID_ARTICLE>0){
    		$result_article = $this->model_article->ar($report['score']['FID_ARTICLE']);
    		
    		$html[] = $this->wrapTag('h2','<span class="circle-16" style="background-color:'.$report['score']['color'].'"></span>'.$result_article['name'].$pdf);
    		
    		//	Add return-button to previous calculation (comming from link):
	    		$html_return_button = $this->html_stack_return_button();
	    		if ($html_return_button != ''){
	    			$html[] =$html_return_button;
	    		}
    		
    		$html[] = $this->wrapTag('div',$result_article['content']);
    	} else {
    		$html[] = $this->wrapTag('h2','<span class="circle-16" style="background-color:'.$report['score']['color'].'"></span>'.$full_name.$pdf);
    		
    		//	Add return-button to previous calculation (comming from link):
	    		$html_return_button = $this->html_stack_return_button();
	    		if ($html_return_button != ''){
	    			$html[] =$html_return_button;
	    		}
    	}


    
    	
    	
   //	Add articles:
   		$flag_title = true;
   		foreach ($report['fid_articles'] as $ID){
   			$aR =$this->model_article->ar($ID);
   			if ( array_key_exists('name', $aR)){
   				if ($flag_title) {
   					$html[] =$this->wrapTag('h2',TXT_VBNE_LBL_ARTICLES_BACKGROUND);
   					$flag_title = false;
   				}
   				$html[] =$this->wrapTag('h3',$aR['name']);
   				$html[] =$this->wrapTag('div',htmlspecialchars_decode($aR['content'],ENT_HTML5));
   			}
   			
   		}
    
   	//	Add calculations:
   		$flag_title = true;
   		foreach  ($report['fid_calculations'] as $ID){
   			$aR =$model_calculation->ar($ID);
   			if ( array_key_exists('name', $aR)){
   				if ($flag_title) {
   					$html[] =$this->wrapTag('h2',TXT_VBNE_LBL_CALCULATIONS_NEXT);
   					$flag_title = false;
   				}
   				$attr= array();
   				$attr['target'] = "_self";
   				$attr['href']   = $model_calculation->get_ar_href($ID).'&result='.$report['FID_CALCULATION'];
   				$attr['title']  = $this->fieldReplace(TXT_VBNE_TITLE_OPEN, array('name'=>$aR['name']));
   				
   				$html[] =$this->wrapTag('h3',$this->wrapTag('a',$aR['name'],$attr));
   			}
   		} 
    
  
   
    //	Debug array:	
    	if ($_SESSION['DEBUG'] >100) {$html[] = array_pretty_print($report);}
    	
    	return $this->wrapTag('div',$html);
    	
    	
    	
    	
    }
}

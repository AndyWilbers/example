<?php	//	_system/controllers/article.php
			defined('BONJOUR') or die;
	

class controllerArticle extends controller {
	
		
	public function __construct(){
		
		
		$this->has_category_record_structure 	= true;
		
		
		parent::__construct();
		
	//	Model and view:
		require_once MODELS.'article.php';
		$this->model = new modelArticle();
		
		require_once VIEWS.'article.php';
		$this->view = new viewArticle();
		
	//	Get and start respond_method:
		$respond_method = count($this->request) > 1? $this->request[1] : $this->respond_method;
		if ( method_exists($this, $respond_method) ) {  $this->respond_method = $respond_method;}
		
		return;
			
	}
	
	public function save() {
		
		
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
	
		$fields = array();
		
		if (array_key_exists('publish', $_POST) ){
			$fields['publish']  = (int)$_POST['publish'] === 1? 1: -1;
		}
		
		if (array_key_exists('name', $_POST) ){
			$fields['name']  = trim($_POST['name']);
		}
		
		if (array_key_exists('path', $_POST) ){
			$path = trim($_POST['path']);
			if (!preg_match("/^[_a-zA-Z0-9]*$/",$path)) {
				$this->meta_error 	= -31;
				$this->meta_msg		= TXT_VBNE_ERR_PATH;
			}
			$fields['path']  = $path;
		}
		
		if (array_key_exists('content', $_POST) ){
			$content  = trim($_POST['content']);
			$content  = $this->model->content_encode($content);
			$fields['content']  = $content;
		}
		
		if (array_key_exists('FID_CAT', $_POST) ){
			$fields['FID_CAT']  = (int)($_POST['FID_CAT']);
		}
		
		if (array_key_exists('position_id', $_POST)  && array_key_exists('FID_CAT', $_POST) ){
			$position = $model->ar_positions_get_position($_POST['position_id'], $_POST['FID_CAT']);
			if ($position !== false) {
				$fields['position'] = $position;
			}		
		}
		
	//	Update / insert record:
		$this->meta_error = -4;
		if ($_POST['ID'] === "new") {
			$this->meta_error = -41;
			if (!array_key_exists('position', $fields) ) {
				$last = $model->ar_positions_last($fields['FID_CAT']);
				if ($last !== false) {
					$fields['position'] = (int)$last['position']+100;
				}
			}
			$check = $model->ar_insert($fields);
			if (!$check) {
				$known_error = $this->known_error($model->last_sql_error());
				if ($known_error !== false){
					$this->meta_msg = $known_error['msg'];
					$this->data['field_warning'] = $known_error['field'];
				}
				parent::respond_data(); return;
			}
			$record= $model->ar();
			$ID = $record['ID'];
			
			$this->data['redirect']= rtrim(HOME_.'admin/'.$_POST['root_path'].$model->get_category_path_by_id($record['FID_CAT'],true),'/').'?id='.$ID;
				
				
		} else {
			$ID = (int)$_POST['ID'];
			
			if ($ID < 1 ) { parent::respond_data(); return; }
			$check = $model->ar($ID);
			
			
			$this->meta_error 	= -421;
			if (!$check){ parent::respond_data(); return; }
			$this->meta_error 	= -422;
			$check = $model->ar_update($fields);
			if (!$check) {
				$known_error = $this->known_error($model->last_sql_error());
				if ($known_error !== false){
					$this->meta_msg = $known_error['msg'];
					$this->data['field_warning'] = $known_error['field'];
				}
				parent::respond_data(); return;
			}
			$record = $model->ar();
			$this->data['redirect']= rtrim(HOME_.'admin/'.$_POST['root_path'].$model->get_category_path_by_id($record['FID_CAT'],true),'/').'?id='.$record['ID'];
				
		}
		$model->ar_positions($record['FID_CAT']);
		
		$this->data['ID'] 	= $ID;
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_DB_SUCCESS;
		
		parent::respond_data();
		return;
		
		
	}
	
	public function options(){
		
		$this->meta_error 	= -99;
		$this->meta_msg 	= TXT_ERR_UNKNOWN;
		
	//	Get FID_ARTICLE_CAT:
		if ( !array_key_exists('FID_ARTICLE_CAT', $_GET) ) {
			$this->meta_error 	= -10;
			parent::respond_data();
			return;
		}
		$FID_ARTICLE_CAT = (int)$_GET['FID_ARTICLE_CAT'];
		$FID_ARTICLE_CAT = $FID_ARTICLE_CAT <1? -1 : $FID_ARTICLE_CAT;
		
		
		$articles = $this->model->get_records_by_category($FID_ARTICLE_CAT);
		if (count($articles) == 0 ) {
			$this->data['select'] = '';
			$this->meta_error 	= 0;
			$this->meta_msg 	= TXT_OK;
			parent::respond_data();
			return;
			
		}
		$options = array();
		$name_select = $this->fieldReplace(TXT_LBL_SELECT, array('name'=> TXT_VBNE_LBL_ARTICLE) );
		$attr = array();
		$attr['value']  = -1;
		$attr['selected']  = "selected";
		
		$options[] = $this->view->wrapTag('option',$name_select, $attr);
		foreach ($articles as $article){
			$attr = array();
			$attr['value']  = $article['ID'];
			$options[] =   $this->view->wrapTag('option',$article['name'], $attr);
		}
		$attr = array();
		$attr['value']  = -1;
		$attr['name']  = 'FID_ARTICLE';
		$this->data['select'] =$this->view->wrapTag('select',$options, $attr);
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_OK;
		
		parent::respond_data();
		return;
		
	}
	
	public function get_popup(){
		
	//	Get type of popup:	
		$popup = array_key_exists('popup', $_GET)? trim($_GET['popup']) : "#NOT_SET";
		
	//	Get val:
		$val  = array_key_exists('val', $_GET)?		$_GET['val']	: -1;
		
	//	Get action_ok
		$action_ok  = array_key_exists('action_ok', $_GET)?	$_GET['action_ok']	: '';
		$this->view->addReplacement('action_ok', $action_ok);
		
		$this->data['size'] = 'normal';
		switch ($popup){
			case "image":
			$this->data['size'] = 'large';
			require_once CONTROLLERS.'image.php';
		
			$controller_image = new controllerImage();
			
			$image_path =  array_key_exists('image_path', $_GET)? $_GET['image_path']: PATH;
			$controller_image->set_image_path($image_path);
			
			$controller_image->set_type('thumbs');
			$this->data['html'] = $controller_image->image_picker_html();
			break;
			
			case "note":
			require_once MODELS.'note.php';
		    $model = new modelNote();
		    $txt_label = TXT_VBNE_LBL_NOTE;
		    $this->data['html'] = $this->popup_html($model, $val, $txt_label, $popup);
			break;
			
			case "reference":
			require_once MODELS.'reference.php';
		    $model = new modelReference();
		    $txt_label = TXT_VBNE_LBL_REFERENCE;
		    $this->data['html'] = $this->popup_html($model, $val, $txt_label, $popup);
			break;
		
			case "article":
			require_once MODELS.'article.php';
			$model = new modelArticle();
			$txt_label = TXT_VBNE_LBL_ARTICLE;
			
		//	Exlcude ID the current article (no link to self).
			$ID  = array_key_exists('ID', $_GET)?		(int)$_GET['ID']	: -1;
			$exclude = array();
			$exclude[]=$ID;
			$this->data['html'] = $this->popup_html($model, $val, $txt_label, $popup, $exclude);
			break;
			
			case "calculation":
			require_once MODELS.'calculation.php';
		    $model = new modelCalculation();
		    $txt_label = TXT_VBNE_LBL_CALCULATION;
		    $this->data['html'] = $this->popup_html($model, $val, $txt_label, $popup);
			break;
			
			default:
			$this->meta_error 	= -1;
			$this->meta_msg 	= TXT_ERR_UNKNOWN;
			parent::respond_data();
			return;
			break;
			
			
		}
	
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_DB_SUCCESS;
		
		parent::respond_data();
		return;
		
	}
	
	/**
	 * Gives the html for a category, records selector
	 * @param $model: model object to get options from
	 *
	 * @return string html
	 */
	private function popup_html($model, $val, $txt_label, $type, $exclude = array()) {
	
	//	Get ID of record from val:
		$ID = (int)$val>0? (int)$val: -1;
	
	//	Name:
		switch($type){
			case 'article':
			$name = TXT_VBNE_LBL_ARTICLE;
			break;
			
			case 'calculation':
			$name = TXT_VBNE_LBL_CALCULATION;
			break;
			
			default:
			$name = TXT_LBL_RECORD;
			break;
		}
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
	
	public function get_link(){
		
	//	Get ID:
		$ID = array_key_exists('ID', $_GET)? (int)$_GET['ID'] : -1;
		if ($ID <1) {
			$this->meta_error 	= -1;
			$this->meta_msg 	= TXT_ERR_NO_SELECTION.
			parent::respond_data();
			return;
		}
		
	//	Get type:
		$type = array_key_exists('type', $_GET)? $_GET['type'] : null;
		if ( !in_array($type, ['article','calculation','note','reference']) ){
			$this->meta_error 	= -2;
			$this->meta_msg 	= TXT_ERR_UNKNOWN;
			parent::respond_data();
			return;
		
		}
		
	//	Get text:
		$text = array_key_exists('text', $_GET)? trim($_GET['text']) : '';
		$text = $text == ''? 'text...' : $text;
	
	//	Create html of link:
		$tag = '{link;'.$type.';'.$ID.';'.$text.'}';
		$this->data['html']   =  $this->model_article->content_decode_link($tag);
		$this->data['record'] = $this->model_article->link_ar;

		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_DB_SUCCESS;
		
		parent::respond_data();
		return;
		
	}
	
	public function favorite_add(){
		
	//	Read sanatized ID:
		$ID = $this->sanatize_POST_ID('ID',-1);
		
		if ( !array_key_exists(SES_FAVORITES, $_SESSION) ){
			$_SESSION[SES_FAVORITES] = array();
		}
		if ( !array_key_exists(APP, $_SESSION[SES_FAVORITES]) ){
			$_SESSION[SES_FAVORITES][APP] = array();
		}
		
		$_SESSION[SES_FAVORITES][APP][$ID] = $ID;
		
		$this->meta_error 		= 0;
		$this->meta_msg 		= TXT_MSG_FAVORITE_ADD;
		$this->data['count']	= count($_SESSION[SES_FAVORITES][APP]);
		
		parent::respond_data();
		return;
		
	}
	
	public function favorite_remove(){
		
	//	Read sanatized ID:
		$ID = $this->sanatize_POST_ID('ID',-1);
		$this->data['ID'] = $ID;
		
	//	If favorites don't excist: return succesfully without the need for action:	
		if ( !array_key_exists(SES_FAVORITES, $_SESSION) ){
		$this->meta_error 		= 0;
		$this->meta_msg 		= TXT_MSG_FAVORITE_REMOVE;
		$this->data['count']	= 0;
		parent::respond_data();
		return;	
		}
		
	//	If APP favorites don't excist: return succesfully without the need for action:
		if ( !array_key_exists(APP, $_SESSION[SES_FAVORITES]) ){
			$this->meta_error 		= 0;
			$this->meta_msg 		= TXT_MSG_FAVORITE_REMOVE;
			$this->data['count']	= 0;
			parent::respond_data();
			return;
		}
		
	//	If ID don't excist: return succesfully without the need for action:
		if ( !array_key_exists($ID, $_SESSION[SES_FAVORITES][APP]) ){
		$this->meta_error 		= 0;
		$this->meta_msg 		= TXT_MSG_FAVORITE_REMOVE;
		$this->data['count']	= count($_SESSION[SES_FAVORITES][APP]);
		parent::respond_data();
		return;	
		}
	
	//	Remove ID from favorites and remove favorites is case no 
		unset($_SESSION[SES_FAVORITES][APP][$ID]);
		if (count($_SESSION[SES_FAVORITES][APP]) == 0) {
			unset($_SESSION[SES_FAVORITES][APP]);
			$this->data['count'] = 0;
		} else {
			$this->data['count']	= count($_SESSION[SES_FAVORITES][APP]);
		}
		
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_MSG_FAVORITE_REMOVE;
		parent::respond_data();
		return;
	}	
	
	public function favorite_remove_all(){
		
	//	If favorites don't excist: return succesfully without the need for action:
		if ( !array_key_exists(SES_FAVORITES, $_SESSION) ){
			$this->meta_error 		= 0;
			$this->meta_msg 		= TXT_MSG_FAVORITE_REMOVE;
			$this->data['count']	= 0;
			parent::respond_data();
			return;
		}
		
	//	If favorites don't excist: return succesfully without the need for action:
		if ( !array_key_exists(APP, $_SESSION[SES_FAVORITES]) ){
			$this->meta_error 		= 0;
			$this->meta_msg 		= TXT_MSG_FAVORITE_REMOVE;
			$this->data['count']	= 0;
			parent::respond_data();
			return;
		}
		unset($_SESSION[SES_FAVORITES][APP]);
		$this->meta_error 		= 0;
		$this->meta_msg 		= TXT_MSG_FAVORITE_REMOVE;
		$this->data['count']	= 0;
		parent::respond_data();
		return;
	}
	

	public function favorite_set_id($ID = null){
		
		$ID = (int)$ID;
		if ($ID >0 ) {
			if (!array_key_exists(SES_FAVORITES.'_ID', $_SESSION)){
				$_SESSION[SES_FAVORITES.'_ID'] = array();
			}
			$_SESSION[SES_FAVORITES.'_ID'][APP] = $ID;
			$favorites = $this->favorite_get_record();
			
			if (array_key_exists('RECORDS', $favorites)){
				
				if ( !array_key_exists(SES_FAVORITES, $_SESSION)) {$_SESSION[SES_FAVORITES] = array();}
				$_SESSION[SES_FAVORITES][APP]=$favorites['RECORDS'];
				
			}

			
		}
	}
	
	public function favorite_reset_id(){
		if ( !array_key_exists(SES_FAVORITES.'_ID', $_SESSION) ){ return true;}
		
		if ( !array_key_exists(APP, $_SESSION[SES_FAVORITES.'_ID']) ){ 
			if (count($_SESSION[SES_FAVORITES.'_ID']) == 0 ){ unset($_SESSION[SES_FAVORITES.'_ID']);} 
			return true;
		}

		unset($_SESSION[SES_FAVORITES.'_ID'][APP]);
		if (count($_SESSION[SES_FAVORITES.'_ID']) == 0 ){ unset($_SESSION[SES_FAVORITES.'_ID']);} 
		return true;
		
	}

	public function favorite_get_record(){
		
		if (!array_key_exists(SES_FAVORITES.'_ID', $_SESSION)       ){ return array(); }
		if (!array_key_exists(APP, $_SESSION[SES_FAVORITES.'_ID'])  ){ return array(); }
		
		$ID = (int)$_SESSION[SES_FAVORITES.'_ID'][APP];
		
		$table = new table('user_rep');
		$aR =  $table->ar($ID);
		
		if (array_key_exists('RECORDS', $aR) ){
			$aR['RECORDS'] = json_decode($aR['RECORDS'], true);
		}
		
		return $aR;
	
	}
	
	

}
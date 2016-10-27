<?php	//	_system/controllers/admin.php
			defined('BONJOUR') or die;
	

class controllerAdmin extends controller {
		
		
	public function __construct(){
		
		
			parent::__construct();
			
		//	Initialization
			require_once VIEWS.'admin.php';
		
		//	Create view-instances:
			$this->view = new viewAdmin();
		
		//	Add menu:	
			$menu = $this->view->html_menu_admin();
			$this->view->addReplacement('menu',$menu);
				
		//	Dummy content:
			$this->view->addReplacement('content', $this->view->getHtml('dummy.html'));

		//	Get and start respond_method:
			$respond_method =  ROUTE === "admin"? 'dashboard' : $this->request[1];
			if ( method_exists($this, $respond_method) ) {$this->respond_method = $respond_method;}
			return;
			
		
	}
	
	
	protected function dashboard(){
		
		
		//	Dasboard view:
			$html = $this->view->getHtml('admin.dashboard.html');
			
		//	Respond page to client:	
			$this->html = $this->view->html_page($html);
			$this->respond();
			exit;
		
	}
	
	protected function routes(){
		
			$strcuture = $this->model_route->get_menu_structure();
			$scope =  strtoupper($this->request[2]);
			switch ($scope){
				case   'ROOT':
				case   'APP':
						$html = array_pretty_print($strcuture[$scope]);
						break;
						
				default:
					$html = array_pretty_print($strcuture);
			}
		
		
			
		//	Respond page to client:	
			$this->html = $this->view->html_page_2_8_2($html);
			$this->respond();
			exit;
		
	}
	
	protected function users(){
		
	
		//	Users view:
			$html = $this->view->getHtml('dummy.html');
			
		//	Respond page to client:	
			$this->html = $this->view->html_page($html);
			$this->respond();
			exit;
	
	}
	
	protected function articles(){
		
			
		
			$page =  count($this->request) > 2? $this->request[2] : "default";

			switch ($page) {
				
				case 'admin_notes':
				$this->notes();
				break;
				
				case 'admin_reference':
				$this->articles_reference();
				break;
				
				case 'admin_urls':
				$this->articles_urls();
				break;
				
				case 'admin_images':
				$this->articles_images();
				break;
				
				case '_save':
				require_once CONTROLLERS.'article.php';	
				$controller = new controllerArticle();
				$controller->save();
				break;
				
				default: 
				//	Article admin page with TinyMCE editor
					$this->view->addJsExtern("//cdn.tinymce.com/4/tinymce.min.js");
					//$this->view->addJsExtern("../../../_js/vendor/tinymce/js/tinymce/tinymce.min.js");
					
					$this->view->addReplacement('box',  $this->view->getHtml('box.html'));
					
					require_once CONTROLLERS.'article.php';	
					$controller = new controllerArticle();
					$html 		= $controller->crs_admin();
					$this->html = $this->view->html_page($html);
					$this->respond();
					break;
				
			}
			exit;
	}
	
	protected function notes(){
		
		
		require_once CONTROLLERS.'note.php';	
		$controller = new controllerNote();
		$html 		= $controller->crs_admin();
		$this->html = $this->view->html_page($html);
		$this->respond();		
		exit;
		
	}
	
	protected function references(){
	
		require_once CONTROLLERS.'reference.php';	
		$controller = new controllerReference();
		$html 		= $controller->crs_admin();
		$this->html = $this->view->html_page($html);
		$this->respond();		
		exit;
	}
	
	
	protected function articles_images(){
		
		if (count($this->request) > 3) {
			header('Location: '.HOME_.'admin/articles/admin_images');
			exit;
		}
	
	//	Instance of image contoller:
		require_once CONTROLLERS.'image.php';
		$image = new controllerImage();
		
	//	Get popup boxes:
		$html_box  = $image->html_admin_page_popup();
		
		$this->view->addReplacement('box',  $html_box);
		
		$html =$image->html_admin_page();
			
	//	Respond page to client:
		$this->html = $this->view->html_page($html);
		$this->respond();
		exit;
	
	}
	
	protected function observations(){
		
		
		    $this->view->addJs('_js/admin.observation.min.js');
		
			$page =  count($this->request) > 2? $this->request[2] : "default";

			switch ($page) {
				
			
				
				default: 
			
					require_once CONTROLLERS.'observation.php';	
					$controller = new controllerObservation();
					$html 		= $controller->crs_admin();
					$this->html = $this->view->html_page($html);
					$this->respond();
					break;
				
			}
			exit;

	}

	protected function tools(){
	
	//	Get page html from tools-controller:
		require_once CONTROLLERS.'tools.php';
		$controller = new controllerTools();
		
		$html = $controller->show_page();
		$this->html = $this->view->html_page($html);
		$this->respond();
		exit;

	}
	
	protected function calculations(){
		
		
		   $this->view->addJs('_js/admin.calculation.min.js');
		   $this->view->addReplacement('box',  $this->view->getHtml('box.html'));
		
			$page =  count($this->request) > 2? $this->request[2] : "default";

			switch ($page) {
				
			
				
				default: 
			
					require_once CONTROLLERS.'calculation.php';	
					$controller = new controllerCalculation();
					$html 		= $controller->crs_admin();
					$this->html = $this->view->html_page($html);
					$this->respond();
					break;
				
			}
			exit;
		
	
	}

//	Ajax call handlers:
	protected function toggle_publish(){
		
		$this->meta_msg 	= TXT_ERR_UNKWOWN;
	
	// 	Check table name:
		$this->meta_error 	= 1;
		if (array_key_exists('name', $_POST) === false){ parent::respond_data(); return; }
		
	//	Check ID:
		$this->meta_error 	= 2;
		if (array_key_exists('id', $_POST) === false){ parent::respond_data(); return; }
		if ($_POST['id'] === "new") { //for a new record: no database-storage: just return
			$this->meta_error 	= 0;
			$this->meta_msg 	= "ok";
			parent::respond_data();
			return;
		}
		$ID = (int)$_POST['id'];
		if ($ID < 1 ) { parent::respond_data(); return; }
	
	//	Create table:
		$this->meta_error 	= 3;
		$table = new table($_POST['name']);
		if (!$table->excists() ) {parent::respond_data(); return;}
		
	//	Set active record:
		$this->meta_error 	= 4;
		$ar = $table->ar($ID);
		if ((int)$ar['ID'] !== $ID ) { parent::respond_data(); return;}
		
	//	Toggle publish:
		$this->meta_error 	= 5;
		$publish = (int)$ar['publish'] == 1? -1 : 1;
		$check = $table->ar_update(array("publish" => $publish));
		if (!$check) { parent::respond_data(); return; }
		
		$this->meta_error 	= 0;
		$this->meta_msg 	= "ok";
	
		parent::respond_data();
		return;
	
	}

	protected function reload(){
		
		$this->meta_msg 	= TXT_ERR_UNKWOWN;
		
	// 	Check form_id:
		$this->meta_error 	= -1;
		if (array_key_exists('form_id', $_GET) === false){ parent::respond_data(); return; }
		$this->data['form_id'] = $_GET['form_id'];
		
	// 	Check table name:
		$this->meta_error 	= -2;
		$check= false;
		if (array_key_exists('table', $_GET) ){ $check = true; }
		$this->meta_error 	= -21;
		if (array_key_exists('model', $_GET) ){ $check = true; }
		if ($check === false) {parent::respond_data(); return; }
		
	//	Check ID:
		$this->meta_error 	= -3;
		if (array_key_exists('ID', $_GET) === false){ parent::respond_data(); return; }
		if ($_GET['ID'] === "new") { //for a new record: just return
			$this->meta_error 	= 0;
			$this->meta_msg 	= "ok";
			parent::respond_data();
			return;
		}
		$ID = (int)$_GET['ID'];
		if ($ID < 1 ) { parent::respond_data(); return; }
		$this->data['ID'] = $ID;
		
	//	Get AR from table :
		$this->meta_error 	= -4;
	    if ( array_key_exists('table', $_GET) ) {
			$table = new table($_GET['table']);
			if (!$table->excists() ) {parent::respond_data(); return;}
		
			//	Get active record:
				$this->meta_error 	= -41;
				$ar = $table->ar($ID);
				if ((int)$ar['ID'] !== $ID ) { parent::respond_data(); return;}
	    }
	    
	 //	Get AR from model:
	    $this->meta_error 	= -5;
	    if ( array_key_exists('model', $_GET) ) {
	    	
	    	$file = trim($_GET['model']);
	    	$file = rtrim($file, '.php').'.php';
	    	if ( !file_exists(MODELS.$file)){ parent::respond_data(); return; }
	    	
	    	$this->meta_error 	= -51;
	    	require_once MODELS.$file;
	    	$class_name = 'model'.$this->file_to_classname($file);
	    	if ( !class_exists($class_name)) { parent::respond_data(); return;}	
	    	$model =  new $class_name();
	    	
	    //	Get active record:
	    	$this->meta_error 	= -52;
	    	$this->meta_msg = $ID;
	    	$ar = $model->get_ar($ID);
	    	if (!array_key_exists('ID', $ar) ){parent::respond_data(); return;}
	    	$this->meta_error 	= -53;
	    	if ((int)$ar['ID'] !== $ID ) { parent::respond_data(); return;}
	    }
		
	//	Set data:
		$this->data['ar'] = $ar;
		
		$this->meta_error 	= 0;
		$this->meta_msg 	= "ok";
		
		parent::respond_data();
		return;
		
	}
	
	protected function save_category(){
		
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
		
		
		if (array_key_exists('PARENT_ID', $_POST) ){
			$fields['PARENT_ID']  = trim($_POST['PARENT_ID']);
		}
		
		if (array_key_exists('FID_ARTICLE_CAT', $_POST) ){
			$fid_article_cat = (int)$_POST['FID_ARTICLE_CAT'];
			if ($fid_article_cat>0) {
				$fields['FID_ARTICLE_CAT']  = $fid_article_cat;
				$fields['FID_ARTICLE']  	= null; 
			} else {
				$fields['FID_ARTICLE_CAT']  = null;
				$fields['FID_ARTICLE']  	= null;
			}
		}
		
		if (array_key_exists('FID_ARTICLE', $_POST) ){
			$fid_article = (int)$_POST['FID_ARTICLE'];
			$aR = $this->model_article->ar($fid_article);
			if ( array_key_exists('FID_CAT', $aR ) ){
				$fields['FID_ARTICLE_CAT']  = (int)$aR['FID_CAT'];
				$fields['FID_ARTICLE']  	= $fid_article;
			}
		}
		
		if (array_key_exists('position_id', $_POST)  && array_key_exists('PARENT_ID', $_POST) ){
			$position_id = $_POST['position_id'];
			if ($position_id== 'last'){
				$last = $model->categories->positions_last($fields['PARENT_ID']);
				if ($last !== false) {
					$fields['position'] = (int)$last['position']+100;
				}
			} else {
				
				//	Normalize positions:
					$model->categories->positions($fields['PARENT_ID']);
					
				//	Set postion
					$position_id = (int)$position_id;
					if ($position_id > 0) {
						$record = $model->categories->ar($position_id );
						if (array_key_exists('position', $record) ){
							$fields['position'] = $record['position'] - 10;
						}
					}
			}
					
			
		}

	//	Update / insert record:
		$this->meta_error 	= -4;
		if ($_POST['ID'] === "new") {
			$this->meta_error 	= -41;
			if (!array_key_exists('position', $fields) ) {
				$last = $model->categories->positions_last($fields['PARENT_ID']);
				if ($last !== false) {
					$fields['position'] = (int)$last['position']+100;
				}
			}
			$check = $model->categories->ar_insert($fields);
			if (!$check) {
				$known_error = $this->known_error($model->categories->last_sql_error);
				if ($known_error !== false){
					$this->meta_msg = $known_error['msg'];
					$this->data['field_warning'] = $known_error['field'];
				}
				parent::respond_data(); return;
			}
			$ID = $model->categories->ar_pk();
			$this->data['redirect']= rtrim(HOME_.'admin/'.$_POST['root_path'].$model->get_category_path_by_id($ID),'/');
			
			
		} else {
			$ID = (int)$_POST['ID'];
			if ($ID < 1 ) { parent::respond_data(); return; }
			$check = $model->categories->ar($ID);
			$this->meta_error 	= -421;
			if (!$check){ parent::respond_data(); return; }
			$this->meta_error 	= -422;
			$check = $model->categories->ar_update($fields);
			if (!$check) {
				$known_error = $this->known_error($model->categories->last_sql_error);
				if ($known_error !== false){
					$this->meta_msg = $known_error['msg'];
					$this->data['field_warning'] = $known_error['field'];
				}
				parent::respond_data(); return;
			}
			$this->data['redirect']= HOME_.'admin/'.$_POST['root_path'].$model->get_category_path_by_id($ID).$fields['path'];
			
		}
		$model->categories->positions($fields['PARENT_ID']);
		

		$this->data['ID'] 	= $ID;
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_DB_SUCCESS;
	
		parent::respond_data();
		return;
	
	}

	protected function add_record(){
	
		$this->meta_msg 	= TXT_ERR_UNKWOWN;
	
	//	Form id
		$this->data['form_id'] = isset($_POST['form_id'])? $_POST['form_id'] : 'unknown';
	
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
	
	
		if (array_key_exists('FID_CAT', $_POST) ){
			$fields['FID_CAT']  = trim($_POST['FID_CAT']);
		}
		
		if (array_key_exists('position_id', $_POST)  && array_key_exists('FID_CAT', $_POST) ){
			$position = $model->ar_positions_get_position($_POST['position_id'], $_POST['FID_CAT']);
			if ($position !== false) {
				$fields['position'] = $position;
			}
		}
	
	//	Insert record:
		$this->meta_error = -4;
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
		$this->data['redirect']= rtrim(HOME_.'admin/'.$_POST['root_path'].$model->get_category_path_by_id($record['FID_CAT'],true),'/');
	
		$this->data['ID'] 	= $ID;
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_DB_SUCCESS;
	
		parent::respond_data();
		return;
	
	}
		
	protected function change_positions(){
	
		$this->meta_msg = TXT_ERR_UNKWOWN;
		
		$this->meta_error 	= -1;
		if (array_key_exists('FID', $_POST) === false ){parent::respond_data(); return;}
		$FID = (int)$_POST['FID'];
	
	//	Form id
		$this->data['form_id'] = isset($_POST['form_id'])? $_POST['form_id'] : 'unknown';
	
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
		
		
		foreach ($_POST as $name => $value){
			if (str_starts('ID:', $name) ) {
				
				$ID 				= str_replace('ID:', '', $name);
				$ID 				= (int)$ID ;
				$this->meta_msg 	= $name;
				$fields 			= array();
				$fields['position'] = (int)$value;
				if ($_POST['recordname'] == $_POST['table']) {
					$check =$model->ar($ID);
				} else {
					$check = $model->categories->ar($ID);
				}
				$this->meta_error 	= -31;
				if (!$check){ parent::respond_data(); return; }
				$this->meta_error 	= -32;
				if ($_POST['recordname'] == $_POST['table']) {
					$check = $model->ar_update($fields);
				} else {
					$check = $model->categories->ar_update($fields);
				}
				if (!$check){ parent::respond_data(); return; }
				
			}
		}
		if ($FID >0 ) {
			if ($_POST['recordname'] == $_POST['table']) {
				$model->ar_positions($FID);
			} else {
				$model->categories->positions($FID);
			}
			$record = $model->categories->ar($FID);
			$path = array_key_exists('path', $record)? $record['path'] : '';
			$this->data['redirect']= HOME_.'admin/'.$_POST['root_path'].$model->get_category_path_by_id((int)$_POST['FID']).$path;
		} else {
			$this->data['redirect']= rtrim(HOME_.'admin/'.$_POST['root_path'],'/');
		}
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_DB_SUCCESS;
		
		parent::respond_data();
		return;
	
	
	}
	
	public function ajax(){
		
	
	
	//	Get contoller + method from url:
		if (count($this->request)<3 ) {
			$this->respond_data_error();
			return;
		}
		
	//	Get instance of controller:
		$controller_file = trim( strtolower( $this->request[2]) ).'.php';
		if (!file_exists(CONTROLLERS.$controller_file) ) {
			$this->respond_data_error();
			return;
		}
		require_once CONTROLLERS.$controller_file;
		
		$controller_name 	= 'controller'.ucfirst( trim($this->request[2]) );
		$controller 		= new  $controller_name();
		
	
		
	//  Get method:
		$method 	     = trim( strtolower( $this->request[3]) );
		if (!method_exists($controller, $method) ) {
			$this->respond_data_error();
			return;
		}
		
		$controller->$method();
		return;
		
	}
}
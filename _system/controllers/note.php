<?php	//	_system/controllers/note.php
			defined('BONJOUR') or die;
	

class controllerNote extends controller {
	
		
	public function __construct(){
		
		
		$this->has_category_record_structure 	= true;
		
		
		parent::__construct();
		
	//	Model and view:
		require_once MODELS.'note.php';
		$this->model = new modelNote();
		
		require_once VIEWS.'note.php';
		$this->view = new viewNote();
		
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
		
		if (array_key_exists('description', $_POST) ){
			$description  = trim($_POST['description']);
			$fields['description']  = $description;
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


	
	


}
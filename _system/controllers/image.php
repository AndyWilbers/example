<?php	//	_system/controllers/image.php
			defined('BONJOUR') or die;
	

class controllerImage extends controller {
	
	private $request_dir = array();
	
	private $allowed_file_types = array();
	
	private  $image_path   = PATH;
		
	public function __construct(){
		
		parent::__construct();
	
	//	Read from request, the path to the active directory:	
		$this->request_dir = $this->request;
		
		//	remove admin/artilce/admin_images:
			array_shift($this->request_dir); 
			array_shift($this->request_dir);
			array_shift($this->request_dir);
		
	//	Model and view:
		require_once MODELS.'image.php';
		$this->model = new modelImage();
		
		require_once VIEWS.'image.php';
		$this->view = new viewImage();
		
	//	Get and start respond_method:
		$respond_method = count($this->request) > 1? $this->request[1] : $this->respond_method;
		if ( method_exists($this, $respond_method) ) {  $this->respond_method = $respond_method;}
		
	//	Read allowed filetypes from settings:
		$this->allowed_file_types = explode(';',UPLOAD_FILES);
		
		return;
			
	}
	public function set_image_path($path = PATH){
		$this->image_path = trim($path);
		return $this->image_path;
	}
	public function get_image_path(){
		return $this->image_path;
	}
	
	public function html_admin_page(){
		
	//	Get dir from $_SESSION:
		$dir = $this->get_dir();
		
	//	Get message from $_SESSION:
		$message = '';
		if ( array_key_exists('message', $_SESSION)){
			$message = $_SESSION['message'];
			unset($_SESSION['message']);
		}
	
	//	Return view:
		return $this->view->html_view($dir, $message);
	}
	
	public function html_admin_page_popup(){
		
	//	Get dir from $_SESSION:
		$dir = $this->get_dir();
		
	//	Get directory options
		$options = $this->model->get_directory_options(IMG);
		$options = $this->view->html_options($options, strtolower(TXT_VBNE_LBL_IMAGES),$dir,'');
		$this->view->addReplacement('options', $options);
		
	//	Create popup boxes:
		$html_form_upload = $this->view->getHtml('form.image.popup.html');
		$this->view->addReplacement('box',  $html_form_upload);
		return $this->view->getHtml('box.html');
	}
	
	public function get_selector(){
	
		$this->meta_msg 	= TXT_ERR_UNKNOWN;
		$this->meta_error 	= -99;
		
		$image_path =  array_key_exists('image_path', $_GET)? $_GET['image_path']: PATH;
		$this->set_image_path($image_path);
	
		$this->data['html'] = $this->image_picker_html();
	
		$this->meta_msg 	= TXT_OK;
		$this->meta_error 	= 0;
	
		
		parent::respond_data();
		return;
	
	}
	
	public function image_picker_html(){
		
	//	Get dir and assign to $_SESSION['image_dir']:
		$dir = array_key_exists('dir', $_GET)? trim($_GET['dir']) : '';
		$dir = $this->set_dir($dir);
		$this->data['dir'] =$dir;
		
		
		$this->view->set_image_path($this->image_path);
		
		return $this->view->html_image_selector($dir);
		
	}
	
	public function get_image(){
		
		$this->meta_msg 	= TXT_ERR_UNKNOWN;
		$this->meta_error 	= -99;
		
	//	Check ID:
		$ID = array_key_exists('ID', $_GET)? (int)$_GET['ID'] :-1;
		if ($ID < 1) {
			$this->meta_error 	= -1;
			parent::respond_data();
			return;
		}
		
	//	Get record:
		$record= $this->model->ar($ID);
		if (!is_array($record)){
			$this->meta_error 	= -2;
			parent::respond_data();
			return;
		}
		if (!array_key_exists('ID', $record)){
			$this->meta_error 	= -3;
			parent::respond_data();
			return;
		}
		
		$this->data['record'] = $record;
		
		$this->meta_msg 	= TXT_OK;
		$this->meta_error 	= 0;
		
		
		parent::respond_data();
		return;
		
	}
	
	
	
	public function upload(){
		
		$location = 'Location: '.HOME_.'admin/articles/admin_images';
		
	//	Get dir from $_SESSION:
		$dir = $this->get_dir();
		
		
	//	Build directory $dir:
		$parts = explode('/',$dir);
		$part = reset($parts);
		$dir  = '';
		while ($part !== false) {
			$dir  .= $part.DS;
			$part = next($parts);
		}
		
	//	Check if there is at least one file for uploaded:
		$files_uploaded = false;
		if (array_key_exists('img', $_FILES)) {
			$files_uploaded = trim($_FILES['img']['name'][0]) == "" ?  false: true;
		}
		if ($files_uploaded == false) {
			$_SESSION['message'] = TXT_ERR_NO_FILES;
			header($location);
			exit;
		}
		
	//	Check size and mime-type:	
		$message = '';
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$files = array();
		foreach ($_FILES['img']['name']   as $key=>$name) {
			
		//	Check size:
			$size = $_FILES['img']['size'][$key];
			if ((int)$size > 1000*(int)MAX_FILES_SIZE) { 
				$message .= $this->fieldReplace(TXT_ERR_UPLOAD_SIZE, array('name'=>$name)).'<br/>';
				continue;
			}
			
		//	Get path to temporary file	
			$path = $_FILES['img']['tmp_name'][$key];
		
		//	Check mime-type:
			$type_info = strtolower($finfo->file($path));
			$needle = reset($this->allowed_file_types);
			do {
				$type = $needle;
				$found = strpos($type_info, $type);
				$needle = next($this->allowed_file_types);
			} while ($found === false && $needle !== false);
			if ($found === false) {
				$message .= $this->fieldReplace(TXT_ERR_UPLOAD_TYPE, array('name'=>$name)).'<br/>';
				continue;
			}
			$file = array();
			$file['path'] = $path;
			$file['type'] = $type;
			$files[] = $file;
		}
		if ( count($files)  == 0){
			$_SESSION['message'] = $message.TXT_ERR_NO_FILES;
			header($location);
			exit;
		}
		
	//	Handle uploaded files:	
		foreach ($files as $file) {
			
		//	Registrate in database:
			$fields = array();
			$fields['dir']  	= $dir;
			$fields['type'] 	= $file['type'];
			$aR = $this->model->save_ar($fields);
			if ($aR === false){ continue;}
			
			
		//	For svg types: no resize: no double storage:
			if ($fields['type'] == 'svg'){
				$img= IMG.$dir.'img'.$aR['ID'].'.svg';
				move_uploaded_file($file['path'], $img);
				continue;
			}
			
		//	For jpeg, png or gif files: resize and save in two resolutions:
			
			//	Function to read image:	
				switch ($fields['type']) {
					case 'jpeg':
						$image_read = 'imagecreatefromjpeg';
						break;
				
					case 'png':
						$image_read = 'imagecreatefrompng';
						break;
				
					case 'gif':
						$image_read = 'imagecreatefromgif';
						break;
				
					default:
						$image_read = false;
						break;
				}
				if (! $image_read) {continue;}
				
			//	Read uploaded_file:
				$uploaded_file = $image_read($file['path']);
				
			//	Get original dimentions:
				list($width, $height) = getimagesize($file['path']);
				if ( (int)$width<0 ||(int)$height<0 ) {continue;}
				
			//	Rescale in case original size is above standard image size	
				if ( $width >= $height) {
					$re_scale 	= (int)$width > (int)IMG_SIZE_SR;
					$width_new  = (int)IMG_SIZE_SR;
					$height_new = (int)IMG_SIZE_SR*$height/$width;
					$width_lr  = (int)IMG_SIZE_LR;
					$height_lr = (int)IMG_SIZE_LR*$height/$width;
					
				} else {
					$re_scale   = (int)$height > (int)IMG_SIZE_SR;
					$width_new  = (int)IMG_SIZE_SR*$width/$height;
					$height_new = (int)IMG_SIZE_SR;
					$width_lr  = (int)IMG_SIZE_LR*$width/$height;
					$height_lr = (int)IMG_SIZE_LR;
				}
				
			//	Create resized files:
				$file_lr = imagecreatetruecolor($width_lr, $height_lr);
				imagecopyresampled($file_lr, $uploaded_file, 0, 0, 0, 0, $width_lr, $height_lr, $width, $height);
				if ($re_scale) {
					$file_sr = imagecreatetruecolor($width_new, $height_new);
					imagecopyresampled($file_sr, $uploaded_file, 0, 0, 0, 0, $width_new, $height_new, $width, $height);
				} else {
					$file_sr =$uploaded_file;
				}
				
			//	Save file as jpeg in standard resolution:
				$img= IMG.$dir.'img'.$aR['ID'].'.jpeg';
				imagejpeg($file_sr, $img);
				
			//	Save file as png in low resolution:
				$img= IMG.$dir.'img'.$aR['ID'].'.png';
				imagepng($file_lr, $img);
			
	
			
			
			
			
		}
		
		$_SESSION['message'] = 	$message;
	
	//	Re-load page	
		header($location);
		exit;
	}
	
	
	private function resize($newWidth, $targetFile, $originalFile) {
		
			$info = getimagesize($originalFile);
			$mime = $info['mime'];
		
			switch ($mime) {
				case 'image/jpeg':
					$image_create_func = 'imagecreatefromjpeg';
					$image_save_func = 'imagejpeg';
					$new_image_ext = 'jpg';
					break;
		
				case 'image/png':
					$image_create_func = 'imagecreatefrompng';
					$image_save_func = 'imagepng';
					$new_image_ext = 'png';
					break;
		
				case 'image/gif':
					$image_create_func = 'imagecreatefromgif';
					$image_save_func = 'imagegif';
					$new_image_ext = 'gif';
					break;
		
				default:
					throw Exception('Unknown image type.');
			}
		
			$img = $image_create_func($originalFile);
			list($width, $height) = getimagesize($originalFile);
			$newHeight = ($height / $width) * $newWidth;
			$tmp = imagecreatetruecolor($newWidth, $newHeight);
			imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		
			if (file_exists($targetFile)) {
				unlink($targetFile);
			}
			$image_save_func($tmp, "$targetFile.$new_image_ext");
		
		
		
	}
	
	
	public function toggle_view(){
		
		$this->meta_msg 	= TXT_ERR_UNKNOWN;
		$this->meta_error 	= -99;
		
	//	Get type of view and toggle:	
		$type = array_key_exists('type', $_GET)? $_GET['type'] : 'thumbs';
		$type = $type == 'thumbs'? 'list' : 'thumbs';
		if ($type =='list') {
			$_SESSION['image_selector_view_type'] ='list';
		} else {
			if (array_key_exists('image_selector_view_type', $_SESSION)) {
				unset($_SESSION['image_selector_view_type']);
			}
		}
		$this->data['type'] =  $type;
		
	//	Get dir from $_SESSION:
		$dir = $this->get_dir();
		
	//	Get html:
		switch ($type) {
			case "list":
				$this->data['html'] = $this->view->html_list($dir);
			break;
			default:
				$this->data['html'] = $this->view->html_thumbs($dir);
			break;
		}
		
	
		
		$this->meta_msg 	= TXT_OK;
		$this->meta_error 	= 0;
		parent::respond_data();
		return;
		
	}
	
	public function save_alt(){
	
		$this->meta_msg 	= TXT_ERR_UNKNOWN;
		$this->meta_error 	= -99;
		
		
	//	Get records:
		$records = array_key_exists('records', $_POST)? json_decode($_POST['records'], true): array();
		if (! is_array($records)){ 
			$this->meta_error 	= -1;
			parent::respond_data();
			return;
		}
		if (count($records) == 0){
			$this->meta_error 	= -2;
			parent::respond_data();
			return;
		}
		
		$records = $this->model->save_alt($records);
		if ($records === false) {
			$this->meta_error 	= -3;
			parent::respond_data();
			return;
		}
		$this->data['records']= $records;
		$this->meta_msg 	= TXT_OK;
		$this->meta_error 	= 0;
		parent::respond_data();
		return;
	
	}
	
	public function new_dir(){
		
		$this->meta_msg 	= TXT_ERR_UNKNOWN;
		$this->meta_error 	= -99;
		
	//	Check name
		$name = array_key_exists('name', $_POST)? trim($_POST['name']) : '';
		if ($name == '' ) {
			$this->meta_msg 	= TXT_VBNE_ERR_NOT_FILLED;
			$this->meta_error 	= -1;
			parent::respond_data();
			return;
		}
		
	//	Get current directory	
		$dir = $this->get_dir();
		$directory = $this->model->get_directory_content($dir);
		
	//	Check is new name is unique:
		if (array_key_exists($name, $directory['items']) ) {
			$this->meta_msg 	= TXT_VBNE_ERR_NAME_UNIQUE;
			$this->meta_error 	= -2;
			parent::respond_data();
			return;
		}
		
	//	Create new directory:
		$dir = $dir.'/'.$name;
		$path = IMG.str_replace('/', DS, $dir);
		if (mkdir($path, 0755) === false ) {
			$this->meta_error 	= -3;
			parent::respond_data();
			return;
		}
	
	//	Set to new created directory
		$this->set_dir($dir);
		
		$this->meta_msg 	= TXT_OK;
		$this->meta_error 	= 0;
		parent::respond_data();
		return;
		
	}
	
	public function move(){
	
		$this->meta_msg 	= TXT_ERR_UNKNOWN;
		$this->meta_error 	= -99;
	
	//	Get dir
		$dir = array_key_exists('dir', $_POST)? trim($_POST['dir']) : '';
		
	//	Get records:
		$records = array_key_exists('records', $_POST)? json_decode($_POST['records'], true): array();
		if (! is_array($records)){
			$this->meta_error 	= -1;
			parent::respond_data();
			return;
		}
		if (count($records) == 0){
			$this->meta_error 	= -2;
			parent::respond_data();
			return;
		}
		
		$this->model->move($records,$dir);
	
		$this->meta_msg 	= '<h3>'.$dir.'</h3>'.array_pretty_print($records);
		$this->meta_error 	= 0;
		parent::respond_data();
		return;
	
	}
	
	public function set_dir($dir =''){
		$dir = trim($dir);
		if ($dir == '') {
			if (array_key_exists('image_dir', $_SESSION)) { unset($_SESSION['image_dir']);}
		} else {
			$_SESSION['image_dir'] = $dir;
		}
		return $dir;
	}
	
	public function get_dir(){
		
	//	Get dir from $_SESSION:
		if (! array_key_exists('image_dir', $_SESSION) ) {return '';}
		$dir = trim($_SESSION['image_dir']);
		if ($dir == '') {
			unset($_SESSION['image_dir']);
			return '';
		}
		if (!file_exists(IMG.$dir)) {
			unset($_SESSION['image_dir']);
			return '';
		}
		return $dir;
	}
	
	public function set_type($type= 'thumbs'){
		
		if ( array_key_exists('image_selector_view_type', $_SESSION)){
			unset( $_SESSION['image_selector_view_type']);
		}
		
		if ( $type== 'list') $_SESSION['image_selector_view_type'] = 'list';
		return $this->get_type();
	}
	
	public function get_type(){
		return array_key_exists('image_selector_view_type', $_SESSION)? $_SESSION['image_selector_view_type']: 'thumbs';
	}


}
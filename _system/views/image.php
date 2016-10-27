<?php	// _system/views/image.php

class viewImage extends view {
	
	private $structure  		= array();
	private $request_dir 		= array();
	private $active_directory 	= null;
	private $image_path   		= PATH;
	
	private  $model_image        = null;
	
	public function __construct($template = 'form.image.html'){
		
	//	Create an instance of model 'image':	
		require_once MODELS.'image.php';
		$this->model = new modelImage();
		
		$this->has_category_record_structure 	= false;
		parent::__construct($template);
		
	
	}
	
	public function set_image_path($path = PATH){
		$this->image_path = trim($path);
		return $this->image_path;
	}
	public function get_image_path(){
		return $this->image_path;
	}
	
	public function html_view($dir= '', $message=''){
		
	//	Message:
		$this->addReplacement('message', $message);
		
	//	Get view:
		$this->addReplacement('imageselector', $this->html_image_selector($dir));
		
	//  View type related fields:
		$type = array_key_exists('image_selector_view_type', $_SESSION)? $_SESSION['image_selector_view_type']: 'thumbs';	
		if ($type == 'thumbs'){
			
			$this->addReplacement('toggle_view_text', TXT_IMG_VIEW_LIST);
			$this->addReplacement('toggle_view_title', TXT_IMG_VIEW_LIST_TITLE);
			$this->addReplacement('toggle_view_data_text', TXT_IMG_VIEW_THUMBS);
			$this->addReplacement('toggle_view_data_title', TXT_IMG_VIEW_THUMBS_TITLE);
			$this->addReplacement('save_alt_hidden', ' hidden');
			
			
		} else {
			$this->addReplacement('toggle_view_text', TXT_IMG_VIEW_THUMBS);
			$this->addReplacement('toggle_view_title', TXT_IMG_VIEW_THUMBS_TITLE);
			$this->addReplacement('toggle_view_data_text', TXT_IMG_VIEW_LIST);
			$this->addReplacement('toggle_view_data_title', TXT_IMG_VIEW_LIST_TITLE);
			$this->addReplacement('save_alt_hidden', '');

		}
		
		return parent::html_view();
		
	}
	
	
	
	
	public function html_image_selector($dir=''){
		
	//	Get $type from $_SESSION:
		$type = array_key_exists('image_selector_view_type', $_SESSION)? $_SESSION['image_selector_view_type']: 'thumbs';
		$this->addReplacement('type', $type);
	
	//	Get and assign menu:
		$menu = $this->html_menu($dir);
		$this->addReplacement('menu', $menu);
		
	//	Get and assign view:
		switch ($type) {
			case 'list':
			$view= $this->html_list($dir);
			break;
			
			default:
			$view = $this->html_thumbs($dir);
			break;
		}
		$this->addReplacement('view', $view);
	
	//	Return html from template
		return $this->getHtml('form.image.selector.html');
	
	}
	
	public function html_menu($dir=''){
		
	//	Get directory content:
		$directory_content = $this->model->get_directory_content($dir);
		
	//	Name and crumbpath:
		$crumbs = $directory_content['crumbs'];
		$crumb = end($crumbs);
		$crumb_path = strtolower($crumb['name']);
		$name = ucfirst($crumb_path);
		$this->addReplacement('name', $name);
		$crumb = prev($crumbs);
		
	    while ($crumb !=false) {
	    	$attr = array();
	    	$attr['href'] = $crumb['dir'];
	    	$crumb_path = $this->wrapTag('a',strtolower($crumb['name']),$attr).'&nbsp;/&nbsp;'.$crumb_path;
	    	$crumb = prev($crumbs);
	    }
	    $this->addReplacement('crumb_path', $crumb_path);
	    
	 //	Menu:
	 	$items = $directory_content['items'];
		$buttons 		= array();
		$attr 			= array();
		foreach ($items as $name=>$href){
			$attr['href'] 	= $href;
			$attr['class'] ='block';
			$buttons[]		= $this->wrapTag('a',$name,$attr);
		}
		
		$menu =  $this->wrapTag('nav',$buttons);
		$this->addReplacement('menu', $menu);
		
	//	Return html from template:
		return $this->getHtml('form.image.selector.menu.html');
	
	}
	
	public function html_thumbs($dir = ''){
		
	//	Get directory content:
		$this->model->set_image_path($this->image_path);
		$directory_content = $this->model->get_directory_content($dir);
		$thumbs = $directory_content['thumbs'];
	
	//	Build thumbs:
		$divs= array();
		$attr 			= array();
		$attr['class'] 	= 'thumb';
		foreach ($thumbs as $ID=>$url){
			$attr['style'] = 'background-image:url('.$url.');';
			$attr['data-id'] =$ID;
			$divs[] = $this->wrapTag('div','',$attr);
		}
		
		return $this->wrapTag('div',$divs);
		
	}
	
	public function html_list($dir = ''){
		
	//	Get list from database:
		$this->model->set_image_path($this->image_path);
		$records = $this->model->get_records_by_dir($dir);
		
	//	Build tbody:
		$attr 			= array();
		$attr['class'] 	= 'thumb small';
		$rows = array();
		foreach ($records as $record){
			$attr['style'] = 'background-image:url('.$record['thumb_url'].');';
			$attr['data-id'] =$record['ID'];
			$record['thumb'] = $this->wrapTag('div','',$attr);
			$this->addReplacement('record', $record);
			$rows[] = $this->getHtml('form.image_list.row.html');
		}
		$tbody = $this->wrapTag('tbody',$rows);
		$this->addReplacement('tbody', $tbody);
		
	//	Return template:
		return $this->getHtml('form.image_list.html');
		
	}
	
	
	
}

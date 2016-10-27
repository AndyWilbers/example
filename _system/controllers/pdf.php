<?php	//	_system/controllers/pdf.php
			defined('BONJOUR') or die;
		

class controllerPdf extends controller {
				
	public function __construct(){
		
	//	Respond with meta-data-form, in case these data was not posted.
		if (!array_key_exists('create', $_POST)) {
			require_once CONTROLLERS.'page.php';
			$page = new controllerPage();
			exit;
		}
		
	//	Create pdf:
		parent::__construct();
			
		require_once VIEWS.'pdf.php';
		$this->view = new viewPdf();
		$this->view->set_style('_css'.DS.'tcpdf.css'); 	// Stylesheet for use in html blocks.
		$this->view->set_css('_css'.DS.'pdf.css'); 		// Stylesheet for styling the pdf output.
		
		$respond_method = count($this->request)>1? $this->request[0].'_'.$this->request[1]:$this->request[0];
		if ( !method_exists($this, $respond_method) ) { $respond_method = $this->respond_method;}
		$this->$respond_method();
		return;
		
			
	}
	private function pdf(){
	
		$ID = array_key_exists('id', $_REQUEST)? (int)$_REQUEST['id'] : -1;
		if ($ID >0) {
			$this->pdf_report($ID);
			exit;
		}
		$respond_method = $this->respond_method;
		$this->$respond_method();
		exit;
	
	}
	
	
	private function pdf_favorites(){
	
	//	Set meta-data to view:
		$title 					= array_key_exists('title', $_POST)? trim($_POST['title']) : TXT_VBNE_LBL_FAVORITES;
		$this->view->title 		= strlen($title)>0? $title: TXT_VBNE_LBL_FAVORITES;
		
		$user 					= $this->user();
		$user_name 				= array_key_exists('name', $user)? $user['name']: '';
		$author 				= array_key_exists('author', $_POST)? trim($_POST['author']) : $user_name ;
		$this->view->author		= strlen($author)>0? $author: $user_name;
		
		$remarks 				= array_key_exists('remarks', $_POST)? trim($_POST['remarks']) : '';
		$this->view->remarks	= $remarks;
		$this->view->pdf_favorites();
		exit;
	}

	
	private function pdf_report($ID){
		
	//	Set meta-data to view:
		$title 					= array_key_exists('title', $_POST)? trim($_POST['title']) : TXT_VBNE_LBL_FAVORITES;
		$this->view->title 		= strlen($title)>0? $title: '';
		
		$user 					= $this->user();
		$user_name 				= array_key_exists('name', $user)? $user['name']: '';
		$author 				= array_key_exists('author', $_POST)? trim($_POST['author']) : $user_name ;
		$this->view->author		= strlen($author)>0? $author: $user_name;
		
		$remarks 				= array_key_exists('remarks', $_POST)? trim($_POST['remarks']) : '';
		$this->view->remarks	= $remarks;
		
		$date 					= array_key_exists('date', $_POST)? trim($_POST['date']) : '#NA';
		if ($date !== '#NA'){$this->view->date	= $date;}
		
		$this->view->pdf_report($ID);
		exit;
	}
	

	

	
}
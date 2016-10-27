<?php	//	_system/controllers/route.php


			defined('BONJOUR') or die;
	
/**
 * @todo:  obsolete????
 */
class controllerRoute extends controller {
		
		
	public function __construct(){
		
			
			parent::__construct();
			
		//	Model and view:
			require_once MODELS.'route.php';
			$this->model = new modelRoute();
			
			require_once VIEWS.'admin.php';
			$this->view  = new viewAdmin();
			
			 $this->html = $this->view->getHtml('admin.route.html');
	
		return;
			
		
	}
	

		
}
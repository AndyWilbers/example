<?php	//	_system/controllers/monitor.php
			defined('BONJOUR') or die;

class controllerMonitor extends controller {
			
	public function __construct(){
		
			parent::__construct();
		
		//	Initialization
			require_once VIEWS.'monitor.php';
		
		//	Create view-instances:
			$this->view = new viewMonitor();
			
		//	Get and start respond_method:
			$respond_method = $this->respond_method;
			if ( count($this->request_params) > 0 ) {
				$respond_method = strtolower($this->request_params[0]);
				if ( !method_exists($this, $respond_method) ) {
					$respond_method = $this->respond_method;
				}
			} else {
				if ( count($this->request) === 1) {
					$respond_method = 'log';
				}
			}
			$this->$respond_method();
			return;
			
	}
	
	private function info(){
			echo phpinfo();
			exit;
	}
	
	private function server(){
			$constants = $this->model_system_info->constants_server();
			$this->respond_html($constants);
			return;
	}
	
	private function constants(){
			$constants = $this->model_system_info->constants_app();
			$this->respond_html($constants);
			return;
	}
	
	private function session(){
			$constants = $this->model_system_info->constants_session();
			$this->respond_html($constants);
			return;
	}
	
	private function log(){
			$constants = $this->model_system_info->log();
			$attr 					= array();
			$attr['thead'] 			= array();
			$attr['thead']['class'] = 'hide';
			$this->respond_html($constants, $attr);
			return;
	}
	
	private function respond_html($constants, $attr = array()){
		$content = $this->view->htmlTable($constants,$attr);
		$this->view->addReplacement('content',$content);
		$this->html = $this->view->getHtml();
		$this->respond();
		return;
	}
	
	private function setdebuglevel(){
		
		//	Get level from request:
			$level = isset($_POST['level'])? (int)$_POST['level']: -1;
			
		//	Set or unset $_SESSION['DEBUG']:
			if ($level > 0 ) {
			
				$_SESSION['DEBUG'] = $level;
				$this->data['level'] =  $level;
			
			} else {
				if ( isset($_SESSION['DEBUG']) ) { unset($_SESSION['DEBUG']);}
				$this->data['level'] =  -1;
			
			}
			$this->meta_error 	= 0;
			$this->meta_msg 	= "Level is aangepast";
			parent::respond_data();
			return;
	}
		
}
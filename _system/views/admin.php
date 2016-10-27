<?php	// _system/views/admin.php

class viewAdmin extends view {
	
	private $role = 0;		// role of active user

	public function __construct(){
		
		
		parent::__construct('admin.html');
		
		
	//	Default show header and footer:
		$this->addReplacement('hide', '');
		
	//	Show button to monitor depending on role of user:
		$user		= $this->user();
		$this->role	= array_key_exists('role', $user) ? $user['role'] : 0;
	
		
	}
	
	public function html_menu_admin(){
		
		//	Button for monitor:
			$attr= array();
			$attr['href'] 	= PATH.'monitor';
			$attr['class'] 	= 'btn';
			$attr['title'] 	= 'Open monitor.';
			$attr['target'] = '_blank';
			$html_btn_monitor =  $this->wrapTag('a','monitor',$attr);
		
		
		//	Build menu:
			if (APPLICATION === '') {
				$html = $html_btn_monitor;
				$html .=  $this->wrapTag('h2', TITLE);
				$html .= PHP_EOL.$this->html_nested_menu_root('admin', '/admin');
				foreach ($this->apps as $APP =>$name) {
					$html .=  $this->wrapTag('h2', ucfirst($name));
					$html .= PHP_EOL.$this->html_nested_menu_app('admin', '/admin',$APP);
				}
				
			} else {
				if ($this->role >= 1000) {
					$html = $html_btn_monitor;
					$html .=  $this->html_nested_menu_root('admin', '/admin');
					$html .= PHP_EOL.$this->wrapTag('h2', TITLE);
					$html .= PHP_EOL.$this->html_nested_menu_app('admin', '/admin');
					
				} else{
					$html =  $this->wrapTag('h2', TITLE);
					$html .= PHP_EOL.$this->html_nested_menu_app('admin', '/admin');
				}
			}
			return $html;
	}
	
	public function html_page($content){
		
		$this->addReplacement('content',$content);
		$content=  $this->getHtml('admin.page.html');
		$this->addReplacement('content',$content);
		return $this->getHtml();
	}
	
	public function html_page_2_8_2($left='',$right=''){
	
		$this->addReplacement('left',$left);
		$this->addReplacement('right',$right);
		$content=  $this->getHtml('admin.page.2_8_2.html');
		$this->addReplacement('content',$content);
		return $this->getHtml();
	}
	
	
	
}

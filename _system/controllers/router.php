<?php	//	_system/controllers/router.php
			defined('BONJOUR') or die;

class controllerRouter extends controller {
	
	
	public function __construct()  {
		
			parent::__construct();	

		//	In case start of ROUTE could not be matched: return 404 to client:
			if (  $this->route_to_controller === null) { 
				
			//	$_SESSION['LOG'] 	= array();
				$_SESSION['LOG'][] 	= 'url:'.URL;
				$_SESSION['LOG'][] 	= 'route:'.ROUTE;
				$_SESSION['LOG'][] 	= 'FAILED 404';
				$this->respond_404();
			}
			
		//	Handle STACK:
		    $this->handle_request_stack();
			
		//	Reset debug-logging in case controller is not "monitor":
			if ($this->route_to_controller['controller'] !== 'monitor'){
				$_SESSION['LOG'] 	= array();
				$_SESSION['LOG'][] 	= 'url:'.URL;
				$_SESSION['LOG'][] 	= 'route:'.ROUTE;
				
			}
			
			
			
		//	Check authentication:
			if ( (int)$this->route_to_controller['level'] > 0) {
				
				//	Set Loction for login:
					if ( $this->request[0] === 'admin' || $this->request[0] === 'monitor' ) {
						$login_location = 'Location:'.PATH.'admin/login';
					} else {
						$login_location = 'Location:'.PATH.'login';
					}
				
				//	Get $_SESSION['user']:
					$user = $this->user();
					
				//	Check if user is loged in:
					if (!$this->user_check_active()) {
						if ($this->route_to_controller['request'] === "html") {
							
							//	Not loged in  html-request: direct to login-form:
								$_SESSION['redirect'] = HOME_.ROUTE;
								header($login_location);
								exit;
						} else {
							
							//	Not loged in data-request: send empty data
								$this->meta_error 	= -100;
								$this->meta_msg		= TXT_LOGIN;
								$this->respond_data();
								exit;
						}
					}
					
				//	Check user's authenticationlevel:
					if ((int)$user['role'] < (int)$this->route_to_controller['level'] ) {
						
						if ($this->route_to_controller['request'] === "html") {
							
							//	Re-route to applicaiont
								if (str_starts('admin', ROUTE)  && $user['role'] >= 100 ) {
									$admin_of = $user['application'];
									$_SESSION['redirect'] 	= HOME_.ROUTE;
									header('Location:'.HOME.$admin_of.'/admin');
									exit;
								}
								
							//	Not loged in  html-request: direct to login-form:
								$_SESSION['redirect'] 	= HOME_.ROUTE;
								$_SESSION['msg']		= TXT_NO_ACCESS;
								header($login_location);
								exit;
						} else {
								
							//	Not loged in data-request: send empty data
								$this->meta_error 	= -10;
								$this->meta_msg		= TXT_NO_ACCESS;
								$this->respond_data();
								exit;
						}
					}
					
				//	For admins: re-route own-adminpage:
					if (str_starts('admin', ROUTE)  && $user['role'] < 1000 ) {
						if ($user['application'] != APPLICATION ) {
							if ($this->route_to_controller['request'] === "html") {
								$login_location = 'Location:'.HOME.$user['application'].'/'.ROUTE;
								header($login_location);
								exit;
							} else {
								$this->meta_error 	= -20;
								$this->meta_msg		=  TXT_X_APPLICATION;
								$this->respond_data();
								exit;
								
							}
						}
				
					}
					
				//	Check idle-time:
					if ($this->route_to_controller['request'] === "html") {
						if ( time() - $user['lastactive'] > MAX_IDLE_TIME ) {
							
							$_SESSION['redirect'] 	= HOME_.ROUTE;
							$_SESSION['msg'] 		= TXT_CONFIRM_PASSWORD;
							header($login_location);
							exit;
						}
					}
					
				//	Authentication passed:
					$this->user_update_last_active();
				//	if (isset($_SESSION['msg']) ) 		{ unset($_SESSION['msg']); 		}
				//	if (isset($_SESSION['redirect']) ) 	{ unset($_SESSION['redirect']);	}
					
			}
			
			
		//	Update last-active skip in case of login path:
			$go = $this->request[0] == 'login'? false : true;
			if ( $go && count($this->request) >=2 ){
				$go = $this->request[0].'/'.$this->request[1] == 'admin/login'? false : true;
			}
			if ( $go ) { $this->user_update_last_active(); }
			
			 	
		//	Load controller file:
			$file = strtolower($this->route_to_controller['controller']);
			$file = trim($file);
			$file = rtrim($file, '.php').'.php';
			
			if ( !file_exists(CONTROLLERS.$file)){
				$this->addLog('Controllerfile "'.$file.'" is not found.',1);
				if ($this->route_to_controller['request'] === "html") {
					$this->respond_404();
					exit;
					
				} else {
					$this->meta_error 	= -99;
					$this->meta_msg		= TXT_ERR_UNKWOWN;
					$this->respond_data();
					exit;
				}
				
			}	
			require_once CONTROLLERS.$file;
	
				
	 	//	Start controller:
			$class_name = 'controller'.$this->file_to_classname($file);
			
			if ( !class_exists($class_name)) {
				
				$this->addLog('Class "'.$class_name.'" is not found in "'.$file.'".',1);
				if ($this->route_to_controller['request'] === "html") {
					$this->respond_404();
					exit;
						
				} else {
					$this->meta_error 	= -99;
					$this->meta_msg		= TXT_ERR_UNKWOWN;
					$this->respond_data();
					exit;
				}
			}
			
			$controller =  new $class_name();
			$controller->start_respons();
			exit;

	}
	
	/**
	 * Check if request has a parameter that indicates for stack handling.
	 * In case: handle stack and redirect to requested url without stack_handler parameter.
	 */
	private function handle_request_stack() {	
		$this->handle_request_stack_push();
		$this->handle_request_stack__pop();
		return;
	}
	
	/**
	 *  In case the request url contains an parameter that indicates the url should
	 *  be added to stack: 'calculation', 'article',
	 *  The url is added to stack and the page is re-directed to  requested url without
	 *  the paremter that triggered the put on stack:
	 */
	private  function handle_request_stack_push(){
		
	//	$request_trigger: $_REQUEST parameters that trigger the building of a return-button:
		$request_trigger = ['calculation', 'article', 'result'];
		
	//	$trigger: name of paramters that triggered the building of a return-button:
		$trigger 	= false;
		$i			= 0;
		$nb 		= count($request_trigger);
		do {
			$trigger = $request_trigger[$i];
			$i++;
		} while (!array_key_exists($trigger, $_REQUEST) && $i < $nb );
	
	//	Return empty html in case none of the triggers is in $_REQUEST:
		if ( !array_key_exists($trigger, $_REQUEST)) { return;}
		
	//	If trigger = "result"
		if ($trigger == 'result'){
			$url = array_key_exists('id', $_REQUEST)? HOME.URL.'?id='.$_REQUEST['id']: HOME.URL;
			$id   = (int)$_REQUEST[$trigger];
			$href =  HOME_.'sleutels/calculaties/result?id='.$id;
			//	Add positon parameter 'pos' to href:
				if ($pos >0 ) { $href .= '&pos='.$pos;}
			//	Push to stack:
				$this->stack_push($href);
				
			//	Re-direct:
				header('Location: '.$url);
				exit;
			
		}
		
		
	//	Model instance for getting href:
		require_once MODELS.$trigger.'.php';
		$model_name = 'model'.ucfirst($trigger);
		$model 		= new $model_name();
		
	//	Get $href from model:
		$id   = (int)$_REQUEST[$trigger];
		unset($_REQUEST[$trigger]);
		$href = $model->get_ar_href($id);
		
	//	Get pos:
	    $pos = 0;
		if (array_key_exists('pos', $_REQUEST)) {
			$pos = (int)$_REQUEST['pos'];
			unset($_REQUEST['pos']);
		}
		
	//	Re-build url with parameter 'id' in case was part of request: 
		$url = array_key_exists('id', $_REQUEST)? HOME.URL.'?id='.$_REQUEST['id']: HOME.URL;
		
		
	//	Href not found: re-direct without adding to stack:
		if ( !$href ) {
			header('Location: '.$url);
			exit;
		}
		
	//	Add positon parameter 'pos' to href:
		if ($pos >0 ) {
			$href .= '&pos='.$pos;
		}
		
	//	Push to stack:	
		$this->stack_push($href);
		
	//	Re-direct:
		header('Location: '.$url);
		exit;
		
	}
	
	/**
	 * Removes last element From $_SESSION['STACK']
	 * in case $_REQUEST contains ['stack_pop']
	 * and redirect to requeste page without 'stack_pop' parameter:
	 */
	private function handle_request_stack__pop(){
		if ( !array_key_exists('stack_pop', $_REQUEST) ){ 
			return;
		}
				
		//	Remove last element from stack:	
			$this->stack_pop();
			
		//	Remove 'stack_pop':
		    unset($_REQUEST['stack_pop']);
		    
		//	Re-build url without parmeter 'id' in case was part of request: 
			$url =  HOME.URL;
			$glue = '?';
			if ( array_key_exists('id', $_REQUEST)  ) {
				$url .=$glue.'id='.$_REQUEST['id'];
				$glue = '&';
			}
			if ( array_key_exists('pos', $_REQUEST)  ) {
				$url .=$glue.'pos='.$_REQUEST['pos'];
			}
		   
		 //	Re-direct:
		    header('Location: '.$url);
		    exit;
			
		
	}
	
}
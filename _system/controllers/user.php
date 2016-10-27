<?php	//	_system/controllers/user.php
			defined('BONJOUR') or die;
		

class controllerUser extends controller {
	
	private $entry = '';	//	Login entry: default:'' (readers) for admin login it will be set to "admin".
	private $email = '';
				
	public function __construct(){
		
			parent::__construct();
			
		// 	Define model user:
			require_once MODELS.'user.php';
			$this->model = new modelUser();
			
		//	Set fe view 
			if ($this->request[0] !== "admin"){
				
			// 	Define view fe:
				require_once VIEWS.'fe.php';
				$this->view = new viewFe();
				
			}
			
		//	Get email of loged in user:
			$user = $this->user();
			$this->email = array_key_exists('email', $user)? $user['email'] : '';
			
			$this->addLog('controllerUser',1);
		
		//	Get and start respond_method:
			switch ($this->request[0]){
				case 'login':								
				case 'logout':								
				case 'registatie':								
				case 'password_reset':	
				case 'password_reset_request':
				case 'activate_account':
				case 'authentication':	$respond_method = $this->request[0];
										break;
										
				case 'admin':			//	Login for admin-pages:
											require_once VIEWS.'admin.php';
											$this->view  = new viewAdmin();
											$this->entry = "admin";
											$respond_method = $this->request[1];
											break;
											
				case 'my_account': 		$respond_method = 'my_account_'.$this->request[1];
										break;
										
				case 'ajax'	:			$respond_method = $this->request[1];
										break;
										
				default:				$respond_method = $this->respond_method;
										break;
			}
			if ( !method_exists($this, $respond_method) ) { $respond_method = $this->respond_method;}
			$this->$respond_method();
			return;
			
	}
	
	private function login(){
		
	
		//	Hide header and footer:
			$this->view->addReplacement('hide', ' class="hide"');
			
		//	Set $email, $entry, $msg :
			$this->view->addReplacement('email', $this->email );
			$this->view->addReplacement('entry', $this->entry);
			
			
			$warning 	= '';
			$msg 		= '';
			if ( isset($_SESSION['msg']) ){
				$msg = $_SESSION['msg'];
				unset($_SESSION['msg']);
			}
		
			
			$this->view->addReplacement('msg', $msg);
			
		//	$_SESSION['redirect'] = HOME.'account';
		
		//	Create html and respond:
			$this->view->addReplacement('page', TXT_LBL_LOGIN);
			$this->view->addReplacement('content', $this->view->getHtml('login.html') ); // for admin login.
			$this->view->addReplacement('sections', $this->view->getHtml('login.fe.html') ); // for fe login.
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;	
	}
	
	private function logout() {
	
		//	Set message and show inlog form
			$_SESSION['msg'] = TXT_LOGOUT_SUCCESS;
			
		//	Unset user:
			$this->user_set(null);
			
		//	Reset favorite_id:
			require_once CONTROLLERS.'article.php';
			$controller = new controllerArticle();
			$controller->favorite_reset_id();
			
		//	Set redirect to root or root admin:
			if ( $this->request[0] == 'admin' ||  $this->request[0] == 'monitor') {
				$_SESSION['redirect'] = 'admin';
				header('Location:'.PATH.'admin/login');
				exit;
			} else {
				if ( isset($_SESSION['redirect']) ) {
					unset($_SESSION['redirect']);
				}
				header('Location:'.PATH.'login');
				exit;
			}
	}
			
	private function authentication(){
			
			
			//	Overwrite email in case of a POST:
				if ( isset($_POST['email']) ) {
					$this->email = $_POST['email'];
				}
				
			//	Check for setup:
				if (enigma_setup($this->email) ) {
						require_once VIEWS.'admin.php';
						$this->view  = new viewAdmin();
						$this->entry = 'setup';
						$this->login();
						return;
				}
			
			//	Show form again in case no password is posted:
				if ( !isset($_POST['password']) ) {$this->login();}
				
			//	Get user record:
				$user = $this->model->getByEmail($this->email);
				if ($user === false )  { 
					$_SESSION['msg'] = TXT_LOGIN_FAIL;
					header('Location:'.$_SESSION['redirect']);
					exit;
				}
					
			//	Check Password:
				if (!$this->model->checkLogin($_POST['password'], $user) ) {
					$_SESSION['msg'] = TXT_LOGIN_FAIL;
					header('Location:'.$_SESSION['redirect']);
					exit;
				} 
				
			//	All passed: 
			
				//	Set active user:
					$user = $this->user_set($user);
			
				//	Re-direct:
					$redirect = HOME;
					if ( isset($_SESSION['redirect']) ) {
						$redirect = $_SESSION['redirect'];
						unset($_SESSION['redirect']);
					}
					unset( $_SESSION['msg'] );
					header('Location:'.$redirect);
					exit;
				
					
		}
					
	private function my_account(){
		
		
	//	Get email of active user:
		$active_user = $this->user();
		if (!array_key_exists('email', $active_user) ) {
			$this->view->addReplacement('content', '<h3 class="warning">U moet eerst inloggen voordat u uw gegevens kunt aanpassen.</h3>' );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
		
	//	Get record of active user
		$ar = $this->model->getByEmail($active_user['email']);
		if ($ar === false) {
			$this->view->addReplacement('content', '<h3 class="warning">'.TXT_ERR_UNKWOWN.'</h3>' );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
		$this->view->addReplacement('ar', $ar );
		
	//	Show my_account form
		$this->view->addReplacement('content', $this->view->getHtml('form.my_account.html') );
		$this->html = $this->view->getHtml() ;
		$this->respond();
		exit;
		
		
	}
		
	protected function my_account_save(){
		
		
		 $fields = array();
		
	//	Check if ID is send:
		if ( !array_key_exists("ID", $_POST)) {
			$_SESSION['warning'] = ' warning';
		 	$_SESSION['msg'] = TXT_ERR_UNKNOWN;
		 	header('Location: '.HOME.'account');
		 	exit;
		}
		$this->meta_error 	=-2;
		$ID = (int)$_POST['ID'];
		if ( $ID < 1 ) {
			$_SESSION['warning'] = ' warning';
			$_SESSION['msg'] = TXT_ERR_UNKNOWN;
		 	header('Location: '.HOME.'account');
		 	exit;
		}
		
		
	//	Check if user is logged in:
		$user = $this->user();
		if ( !array_key_exists('ID', $user) ){
			$_SESSION['msg'] = TXT_LOGIN;
		 	header('Location: '.HOME.'account');
		 	exit;
		}
		if ( (int)$ID !== (int)$user['ID']){
			$_SESSION['warning'] = ' warning';
			$_SESSION['msg'] = TXT_LOGIN;
		 	header('Location: '.HOME.'account');
		 	exit;
		}
		$email = array_key_exists("email",$_POST)? trim($_POST['email']): $user['email']; 
		
	// Get showName	
		if ( array_key_exists("showName", $_POST) ) { $fields['showName']  = (int)$_POST['showName']; }
	
	// Get shareObservations
		if ( array_key_exists("shareObservations", $_POST) ) { $fields['shareObservations']  = (int)$_POST['shareObservations']; }
		
	//	Get name records:
		if ( array_key_exists("firstName", $_POST) ) { $fields['firstName']  = trim($_POST['firstName']); }
		if ( array_key_exists("lastName",  $_POST) ) { $fields['lastName']   = trim($_POST['lastName']);  }
		if ( array_key_exists("midName",   $_POST) ) { $fields['midName']    = trim($_POST['midName']); }
		
		
	//	Check password:
		$password_new   =  array_key_exists("password_new", $_POST)?  trim($_POST['password_new'])   : null;
		$password_new   =  strlen($password_new)>1? $password_new  : null;
		if ($password_new  !== null) {
		 	
		//	Check length of password:
		 	if (   strlen($password_new) < MIN_PASSWORD_LEN ) {
		 		$_SESSION['warning'] = ' warning';
		 		$_SESSION['msg'] = TXT_PASSWORD_FAIL;
		 		header('Location: '.HOME.'account');
		 		exit;
			 }
			 
		//	Compare passwords:	 
			$password_check =  array_key_exists("password_new", $_POST)?  trim($_POST['password_check']) :'';
			if ($password_new !== $password_check) {
				$_SESSION['warning'] = ' warning';
				$_SESSION['msg'] = TXT_PASSWORD_CHECK_FAIL;
		 		header('Location: '.HOME.'account');
		 		exit;
			}
			$fields['password'] = $password_new;
		}
		
	//	Check if email should be updated:
		if (strtolower($email) !== strtolower($user['email']) ) {
			if ($this->model->check_email_is_free($email)){
				$fields['email'] =$email;
			} else {
				$_SESSION['warning'] = ' warning';
				$_SESSION['msg'] =  $this->fieldReplace(TXT_ERR_EMAIL_DOUBLE, array('email'=>$email)) ;
				header('Location: '.HOME.'account');
		 		exit;
			}
		}
		
		
			
		 
	//	Save record:
		$this->meta_error 	= -6;
		$check = $this->model->save_ar($fields, $ID);
		if ( $check === false) {
			$_SESSION['warning'] = ' warning';
			$_SESSION['msg']= TXT_ERR_UNKWOWN;
			header('Location: '.HOME.'account');
		 	exit;
		}
		
	//	Update active user record:
		$aR = $this->model->getByEmail($email);
		$this->user_set($aR);
		$_SESSION['warning'] = '';
	    $_SESSION['msg'] = TXT_DB_SUCCESS;
		header('Location: '.HOME.'account');
		exit;
	
	}
	
	protected function my_account_delete(){
	
	
		$fields = array();
	
	//	Check if ID is send:
		if ( !array_key_exists("ID", $_POST)) {
			$_SESSION['warning'] = ' warning';
			$_SESSION['msg'] = TXT_ERR_UNKNOWN;
			header('Location: '.HOME.'account');
			exit;
		}
		
		$ID = (int)$_POST['ID'];
		if ( $ID < 1 ) {
			$_SESSION['warning'] = ' warning';
			$_SESSION['msg'] = TXT_ERR_UNKNOWN;
			header('Location: '.HOME.'account');
			exit;
		}
	
	
	//	Check if user is logged in:
		$user = $this->user();
		if ( !array_key_exists('ID', $user) ){
			$_SESSION['msg'] = TXT_LOGIN;
			header('Location: '.HOME.'account');
			exit;
		}
		if ( (int)$ID !== (int)$user['ID']){
			$_SESSION['warning'] = ' warning';
			$_SESSION['msg'] = TXT_LOGIN;
			header('Location: '.HOME.'account');
			exit;
		}
		
	//	Logout:
		$this->user_set(null);
			
	//	Delete record:
		$check = $this->model->delete_ar($ID);
		if ( $check === false) {
			$_SESSION['warning'] = ' warning';
			$_SESSION['msg']= TXT_ERR_UNKWOWN;
		//	header('Location: '.HOME.'account');
			exit;
		}
	
		$_SESSION['warning'] = '';
		$_SESSION['msg'] = TXT_DELETE_SUCCESS;
		header('Location: '.HOME.'account');
		exit;
	
	}
		
	private function registatie(){
		
		
	//	Default replacements:	
		$this->view->addReplacement('page', TXT_LBL_ACCOUNT_NEW);
		$this->view->addReplacement('email', null);
		$this->view->addReplacement('entry', TXT_LBL_ACCOUNT_NEW);
		$this->view->addReplacement('msg', '');
		$this->view->addReplacement('warning', '');
		
		$hide = $this->view->hide;
		$hide['account'] ='';
		$this->view->addReplacement('hide', $hide);
		
	//	Fill email:
		$email = array_key_exists('email', $_POST)? $_POST['email'] : null;
		
	//	Read message:
		$warning 	= '';
		$msg 		= '';
		if ( isset($_SESSION['msg']) ){
			$msg = $_SESSION['msg'];
			unset($_SESSION['msg']);
		}
		if ( isset($_SESSION['warning']) ){
			if ($_SESSION['warning']) {
				$warning 	= ' warning';
			}
			unset($_SESSION['warning']);
		}
		
	//	Read state:
		$state = array_key_exists('state', $_POST)? $_POST['state'] : 'open';
		
	//	Just open form in case state is "open":
		if ($state == "open" && $email== null){
		
		//	Show form, no process, no message:
			$this->view->addReplacement('email', $email);
			$this->view->addReplacement('sections', $this->view->getHtml('fe.new.account.html') );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
		
	
	//	START requesting new account:
	
	//	Check email format:
		if ($this->validate($email, 'EMAIL') === false ) {
			$this->view->addReplacement('msg', TXT_ERR_EMAIL);
			$this->view->addReplacement('warning', ' warning');
			$this->view->addReplacement('email', $email);
			$this->view->addReplacement('sections', $this->view->getHtml('fe.new.account.html') );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
		
		
	//	Check is email is unique.
		$check = $this->model->check_email_is_free($email);
		if ($check === false){
			$msg =$this->fieldReplace(TXT_ERR_EMAIL_DOUBLE, array('email'=>$email));
			$this->view->addReplacement('msg', $msg);
			$this->view->addReplacement('warning', ' warning');
			$this->view->addReplacement('email', $email);
			$this->view->addReplacement('sections', $this->view->getHtml('fe.new.account.html') );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
		
		
	//	Create new account:
		$aR = $this->model->new_account($email);
		if ($aR === false ) {
			$this->view->addReplacement('email', $email);
			$this->view->addReplacement('msg', TXT_ERR_UNKWOWN);
			$this->view->addReplacement('warning', 'warning');
			$this->view->addReplacement('sections', $this->view->getHtml('fe.new.account.html') );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
		
	//	Send mail to user:	
		$to      	=  $email;
		$subject   	=  TXT_VBNE_MSG_USER_NEW_ACCOUNT_SUBJECT;
		$message 	=  USER_NEW_ACCOUNT_MESSAGE($aR);
		$headers 	= 'From: '.ADMIN_MAIL;
		$check 	=  mail($to , $subject, $message, $headers );
	    if ($check === false) {
	    	$this->view->addReplacement('email', $email);
	    	$this->view->addReplacement('msg', TXT_ERR_UNKWOWN);
	    	$this->view->addReplacement('warning', 'warning');
	    	$this->view->addReplacement('sections', $this->view->getHtml('fe.new.account.html') );
	    	$this->html = $this->view->getHtml() ;
	    	$this->respond();
	    	exit;
	    }
	
	//	Create html and respond:
		$this->view->addReplacement('email', $email);
		$replacements = array();
		$replacements['email'] = $email;
		$replacements['date']  = $aR['respond_date'];
		
		$msg = $this->fieldReplace(TXT_VBNE_MSG_ACCOUNT_MAIL_IS_SEND,$replacements);
		$this->view->addReplacement('msg', $msg);
		$this->view->addReplacement('warning', '');
		$this->view->addReplacement('sections', $this->view->getHtml('fe.new.account.send.html') );
		$this->html = $this->view->getHtml() ;
		$this->respond();
		exit;
	}
	
	private function activate_account(){
	
	
	//	Default replacements:
		$this->view->addReplacement('page', TXT_LBL_ACCOUNT_NEW);
		$this->view->addReplacement('email', null);
		$this->view->addReplacement('entry', TXT_LBL_ACCOUNT_NEW);
		$this->view->addReplacement('msg', '');
		$this->view->addReplacement('warning', '');
	
		$hide = $this->view->hide;
		$hide['account'] ='';
		$this->view->addReplacement('hide', $hide);
	
	//	Fill email:
		$email = array_key_exists('email', $_REQUEST)? $_REQUEST['email'] : null;
		
	//	Fill code:
		$code = array_key_exists('code', $_REQUEST)? $_REQUEST['code'] : null;
	
	//	Read message:
		$warning 	= '';
		$msg 		= '';
		if ( isset($_SESSION['msg']) ){
			$msg = $_SESSION['msg'];
			unset($_SESSION['msg']);
		}
		if ( isset($_SESSION['warning']) ){
			if ($_SESSION['warning']) {
				$warning 	= ' warning';
			}
			unset($_SESSION['warning']);
		}
	
	//	Read state:
		$state = array_key_exists('state', $_POST)? $_POST['state'] : 'open';
	
	//	Just open form in case state is "open":
		if ($state == "open" ){
	
		//	Show form, no process, no message:
			$this->view->addReplacement('email', $email);
			$this->view->addReplacement('code', $code);
			$this->view->addReplacement('sections', $this->view->getHtml('fe.new.account.activate.html') );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
	
	//	Activate account:
		$fields = array();
		$fields['email']  			= $email;
		$fields['code']  			= $code;
		$fields['password']  		= array_key_exists('password', $_POST)? $_POST['password'] : null;
		$fields['password_check']  	= array_key_exists('password_check', $_POST)? $_POST['password_check'] : null;
		$check = $this->model->activate_account($fields);
		if ($check == false) {
			$this->view->addReplacement('warning', ' warning');
			$this->view->addReplacement('msg', $this->model->msg);
			$this->view->addReplacement('email', $email);
			$this->view->addReplacement('code', $code);
			$this->view->addReplacement('sections', $this->view->getHtml('fe.new.account.activate.html') );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
	
	//	Account is created, user is logged in: go to account page:
		$_SESSION['msg'] = TXT_VBNE_MSG_USER_NEW_ACCOUNT_IS_CREATED;
		header('Location: '.HOME.'account');
		exit;
	}
	
	private function password_reset_request(){
	
	
	//	Default replacements:
		$this->view->addReplacement('page', TXT_LBL_PASSWORD_NEW);
		$this->view->addReplacement('email', null);
		$this->view->addReplacement('entry', TXT_LBL_PASSWORD_NEW);
		$this->view->addReplacement('msg', '');
		$this->view->addReplacement('warning', '');
	
		$hide = $this->view->hide;
		$hide['account'] ='';
		$this->view->addReplacement('hide', $hide);
	
	//	Fill email:
		$email = array_key_exists('email', $_POST)? $_POST['email'] : null;
	
	//	Read message:
		$this->message_assign();
		
	
	//	Read state:
		$state = array_key_exists('state', $_POST)? $_POST['state'] : 'open';
	
	//	Just open form in case state is "open":
		if ($state == "open" && $email== null){
	
		//	Show form, no process, no message:
			$this->view->addReplacement('email', $email);
			$this->view->addReplacement('sections', $this->view->getHtml('fe.password.reset.request.html') );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
	
	
	//	START requesting new password:
	
	//	Check email format:
		if ($this->validate($email, 'EMAIL') === false ) {
			$this->view->addReplacement('msg', TXT_ERR_EMAIL);
			$this->view->addReplacement('warning', ' warning');
			$this->view->addReplacement('email', $email);
			$this->view->addReplacement('sections', $this->view->getHtml('fe.password.reset.request.html') );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
	
	
	//	Check is email is known.
		$aR = $this->model->getByEmail($email);
		if ($aR === false){
			$msg = $this->fieldReplace(TXT_ERR_EMAIL_UNKNOWN, array('email'=>$email));
			$this->view->addReplacement('msg', TXT_ERR_EMAIL_DOUBLE);
			$this->view->addReplacement('warning', ' warning');
			$this->view->addReplacement('email', $email);
			$this->view->addReplacement('sections', $this->view->getHtml('fe.password.reset.request.html') );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
	
	//	Deactivate account and get restet code:
		$aR = $this->model->deactivate_account($email);
		if ($aR === false ) {
			$this->view->addReplacement('email', $email);
			$this->view->addReplacement('msg', TXT_ERR_UNKWOWN);
			$this->view->addReplacement('warning', 'warning');
			$this->view->addReplacement('sections', $this->view->getHtml('fe.password.reset.request.html') );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
	
	//	Send mail to user:
		$to      	=  $email;
		$subject   	=  TXT_VBNE_MSG_USER_NEW_PASSWORD;
		$message 	=  USER_PASSWORD_RESET_MESSAGE($aR);
		$headers 	= 'From: '.ADMIN_MAIL;
		$check 	=  mail($to , $subject, $message, $headers );
		if ($check === false) {
			$this->view->addReplacement('email', $email);
			$this->view->addReplacement('msg', TXT_ERR_UNKWOWN);
			$this->view->addReplacement('warning', 'warning');
			$this->view->addReplacement('sections', $this->view->getHtml('fe.password.reset.request.html') );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
	
	//	Create html and respond:
		$this->view->addReplacement('email', $email);
		$replacements = array();
		$replacements['email'] = $email;
		$replacements['date']  = $aR['respond_date'];
	
		$msg = $this->fieldReplace(TXT_VBNE_MSG_PASSWORD_RESET_MAIL_IS_SEND,$replacements);
		$this->view->addReplacement('msg', $msg);
		$this->view->addReplacement('warning', '');
		$this->view->addReplacement('sections', $this->view->getHtml('fe.password.reset.send.html') );
		$this->html = $this->view->getHtml() ;
		$this->respond();
		exit;
	}
	
	private function password_reset(){
	
	
	//	Default replacements:
		$this->view->addReplacement('page', TXT_LBL_PASSWORD_NEW);
		$this->view->addReplacement('email', null);
		$this->view->addReplacement('entry', TXT_LBL_PASSWORD_NEW);
		$this->view->addReplacement('msg', '');
		$this->view->addReplacement('warning', '');
	
		$hide = $this->view->hide;
		$hide['account'] ='';
		$this->view->addReplacement('hide', $hide);
	
	//	Fill email:
		$email = array_key_exists('email', $_REQUEST)? $_REQUEST['email'] : null;
	
	//	Fill code:
		$code = array_key_exists('code', $_REQUEST)? $_REQUEST['code'] : null;
	
	//	Read message:
		$this->message_assign();
	
	//	Read state:
		$state = array_key_exists('state', $_POST)? $_POST['state'] : 'open';
	
	//	Just open form in case state is "open":
		if ($state == "open" ){
	
		//	Show form, no process, no message:
			$this->view->addReplacement('email', $email);
			$this->view->addReplacement('code', $code);
			$this->view->addReplacement('sections', $this->view->getHtml('fe.password.reset.html') );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
	
	//	Password reset:
		$fields = array();
		$fields['email']  			= $email;
		$fields['code']  			= $code;
		$fields['password']  		= array_key_exists('password', $_POST)? $_POST['password'] : null;
		$fields['password_check']  	= array_key_exists('password_check', $_POST)? $_POST['password_check'] : null;
		$check = $this->model->activate_account($fields);
		if ($check == false) {
			$this->view->addReplacement('warning', ' warning');
			$this->view->addReplacement('msg', $this->model->msg);
			$this->view->addReplacement('email', $email);
			$this->view->addReplacement('code', $code);
			$this->view->addReplacement('sections', $this->view->getHtml('fe.password.reset.html') );
			$this->html = $this->view->getHtml() ;
			$this->respond();
			exit;
		}
	
	//	Account is created, user is logged in: go to account page:
		$_SESSION['msg'] = TXT_VBNE_MSG_USER_PASSWORD_IS_CHANGED;
		header('Location: '.HOME.'account');
		exit;
	}
	
	
	
	private function report_set_save(){
		
		
		$this->meta_error 	= 0;
		$this->meta_msg 	= "Test";
	
	
	//	Read favorite articles from $_SESSION:
		$fields = array();
		$fields['RECORDS'] = $this->favorites();
		
	//	Check ID:
		$ID = array_key_exists('ID', $_POST)? (int)$_POST['ID'] : null;
		$ID  = $ID < 1 ? null: $ID;
		
	//	Check name:
		$name  = array_key_exists('name', $_POST)? trim($_POST['name']) : '';
		
		if ($name == '')  {
			$this->meta_error 	= -1;
			$this->meta_msg 	= TXT_VBNE_ERR_NAME;
			parent::respond_data();
			return;
		}
		$fields['name'] = $name; 
		
	//	Save 	
		$check = $this->model->save_report($fields,$ID);
		if ($check ===  false) {
			$this->meta_error 	= -2;
			$this->meta_msg 	= $this->model->msg;
			parent::respond_data();
			return;
		}
		
	//	Set ID:
		$ID = (int)$check['ID'];
		require_once CONTROLLERS.'article.php';
		$controller = new controllerArticle();
		$controller->favorite_set_id($ID);
	
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_DB_SUCCESS;
		parent::respond_data();
		return;
		
	}
	
	private function report_set_change(){
	
	
	
	//	Check ID:
		$ID = array_key_exists('ID', $_POST)? (int)$_POST['ID'] : -1;
		if ($ID <1) {
			
			$this->meta_error 	= -1;
			$this->meta_msg 	= TXT_ERR_UNKNOWN;
			parent::respond_data();
			return;

		}

	
	//	Set ID:
	
		require_once CONTROLLERS.'article.php';
		$controller = new controllerArticle();
		$controller->favorite_set_id($ID);
	
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_DB_SUCCESS;
		parent::respond_data();
		return;
	
	}
	
	private function observation_set_save(){
	

		$this->meta_error 	= -99;
		$this->meta_msg 	= TXT_ERR_UNKNOWN;
		
	
	//	Read stored observations from $_SESSION:
		$fields = array();
		$fields['RECORDS'] = vbne_observations_get();
	
	//	Check ID:
		$ID = array_key_exists('ID', $_POST)? (int)$_POST['ID'] : null;
		$ID  = $ID < 1 ? null: $ID;
	
	//	Check name:
		$name  = array_key_exists('name', $_POST)? trim($_POST['name']) : '';
	
		if ($name == '')  {
			$this->meta_error 	= -1;
			$this->meta_msg 	= TXT_VBNE_ERR_NAME;
			parent::respond_data();
			return;
		}
		$fields['name'] = $name;
	
	//	Save
		$check = $this->model->save_observation($fields,$ID);
		if ($check ===  false) {
			$this->meta_error 	= -2;
			$this->meta_msg 	= $this->model->msg;
			parent::respond_data();
			return;
		}
	
	//	Set ID:
		$ID = (int)$check['ID'];
		vbne_observation_set_id($ID);
	
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_DB_SUCCESS;
		parent::respond_data();
		return;
	
	}
	
	private function observation_set_change(){
	
		$this->meta_error 	= -99;
		$this->meta_msg 	=  TXT_ERR_UNKNOWN;
		
	
	//	Check ID:
		$ID = array_key_exists('ID', $_POST)? (int)$_POST['ID'] : -1;
		if ($ID <1) {
				
			$this->meta_error 	= -1;
			$this->meta_msg 	= TXT_ERR_UNKNOWN;
			parent::respond_data();
			return;
				
		}
	
	
	//	Set ID:
		vbne_observation_set_id($ID);
	
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_DB_SUCCESS;
		parent::respond_data();
		return;
	
	}
	
	private function observation_set_save_meta(){
	
		$this->meta_error 	= -99;
		$this->meta_msg 	=  TXT_ERR_UNKNOWN;
	
	
	//	Check ID:
		$ID = array_key_exists('ID', $_POST)? (int)$_POST['ID'] : -1;
		if ($ID <1) {
	
			$this->meta_error 	= -1;
			$this->meta_msg 	= TXT_ERR_UNKNOWN;
			parent::respond_data();
			return;
	
		}
		$this->data['ID'] = $ID;
	
	//	Get fields from post
		$fields = array();
		
		//	name:
			$val  = array_key_exists('name', $_POST)? trim($_POST['name']) : '#NOT_SET';
			if ($val !== '#NOT_SET')  { $fields['name'] = $val;}
			
		//	date:
		   	$val  = array_key_exists('date', $_POST)? trim($_POST['date']) : '#NOT_SET';
		   	if ($val !== '#NOT_SET'){ $fields['date'] = $val;}
		   
		 // NE_LAT:
		   	$val  = array_key_exists('NE_LAT', $_POST)? trim($_POST['NE_LAT']) : '#NOT_SET';
		   	if ($val !== '#NOT_SET'){ $fields['NE_LAT'] = $val;}
		   	
		 // NE_LNG:
		   	$val  = array_key_exists('NE_LNG', $_POST)? trim($_POST['NE_LNG']) : '#NOT_SET';
		   	if ($val !== '#NOT_SET'){ $fields['NE_LNG'] = $val;} 
		   	
		 // SW_LAT:
		   	$val  = array_key_exists('SW_LAT', $_POST)? trim($_POST['SW_LAT']) : '#NOT_SET';
		   	if ($val !== '#NOT_SET'){ $fields['SW_LAT'] = $val;}
		   	
		 // SW_LNG:
		   	$val  = array_key_exists('SW_LNG', $_POST)? trim($_POST['SW_LNG']) : '#NOT_SET';
		   	if ($val !== '#NOT_SET'){ $fields['SW_LNG'] = $val;}
		   	
		 //	Viewers: 
		   	$val  = array_key_exists('viewers', $_POST)? trim($_POST['viewers']) : '#NOT_SET';
		   	if ($val !== '#NOT_SET'){ $fields['viewers'] = $val;}
		   	
		   	
	 //	Save:
	    $check = $this->model->save_observation($fields,$ID);
		if ($check ===  false) {
		   $this->meta_error 	= -2;
		   $this->meta_msg 	= $this->model->msg;
		   parent::respond_data();
		   return;
		}
	
		   	  	 
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_DB_SUCCESS;
		parent::respond_data();
		return;
	
	}
	
	private function report_set_save_meta(){
	
		$this->meta_error 	= -99;
		$this->meta_msg 	=  TXT_ERR_UNKNOWN;
	
	
	//	Check ID:
		$ID = array_key_exists('ID', $_POST)? (int)$_POST['ID'] : -1;
		if ($ID <1) {
	
			$this->meta_error 	= -1;
			$this->meta_msg 	= TXT_ERR_UNKNOWN;
			parent::respond_data();
			return;
	
		}
		$this->data['ID'] = $ID;
	
	//	Get fields from post
		$fields = array();
	
	//	name:
		$val  = array_key_exists('name', $_POST)? trim($_POST['name']) : '#NOT_SET';
		if ($val !== '#NOT_SET')  { $fields['name'] = $val;}
			
	
	
	//	Viewers:
		$val  = array_key_exists('viewers', $_POST)? trim($_POST['viewers']) : '#NOT_SET';
		if ($val !== '#NOT_SET'){ $fields['viewers'] = $val;}
	
	
	//	Save:
		$check = $this->model->save_report($fields,$ID);
		if ($check ===  false) {
			$this->meta_error 	= -2;
			$this->meta_msg 	= $this->model->msg;
			parent::respond_data();
			return;
		}
	
	
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_DB_SUCCESS;
		parent::respond_data();
		return;
	
	}
	
	private function remove_set(){
		
		$this->meta_error 	= -99;
		$this->meta_msg 	=  TXT_ERR_UNKNOWN;
		
	//	Check ID:
		$ID = array_key_exists('ID', $_POST)? (int)$_POST['ID'] : -1;
		if ($ID <1) {
			$this->meta_error 	= -1;
			$this->meta_msg 	= TXT_ERR_UNKNOWN;
			parent::respond_data();
			return;
		}
		
		
	//	Delete:
		$ext = array_key_exists('ID', $_POST)? strtolower(trim($_POST['ext'])) : '#NOT_SET';
		switch ($ext){
			case 'rep':
			$this->data['page'] = 'my_reports';
			$check = $this->model->delete_report($ID);
			if ($check ===  false) {
				$this->meta_error 	= -3;
				$this->meta_msg 	= $this->model->msg;
				parent::respond_data();
				return;
			}	
			break;
			
			case 'obs':
			$this->data['page'] = 'my_observations';
			$check = $this->model->delete_observation($ID);
			if ($check ===  false) {
				$this->meta_error 	= -3;
				$this->meta_msg 	= $this->model->msg;
				parent::respond_data();
				return;
			}
			break;
			
			default:
			$this->meta_error 	= -2;
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
	
}
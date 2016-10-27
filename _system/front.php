<?php	//_system/_front.php


//	Load system configuation:
	define ('ROOT_CONFIG', ROOT.'config'.DS);
	require_once ROOT_CONFIG.'system.php';
	
//	Define absolute URL to HOME
	$env = ENV === 'PRODUCTION'? '': strtolower(ENV).'/';
	define ('HOME', HTTP.$env);
	
//	Stop in case calling file is not on this server:
	if (substr(ROOT, 0, strlen(SERVER_PATH)) !== SERVER_PATH ){ exit; }
	
//  BONJOUR will be checked by opening each file to prevent direct access.
	define ('BONJOUR',1);
	
	
//	Directorries for file import and exports
	define ('CSV_IMPORT', SERVER_PATH.'_csv'.DS.'import'.DS);
	define ('CSV_EXPORT', SERVER_PATH.'_csv'.DS.'export'.DS);
	define ('UPLOADS', SERVER_PATH.'uploads'.DS);
	define ('CSV_EXPORT_HREF', HTTP.'_csv/export/');
	define ('CSV_RAW', SERVER_PATH.'_csv'.DS.'raw'.DS);
	
//	START APPLICATION:
	session_start();
	
//	Error reporting and logging:
	if (!isset($_SESSION['DEBUG'])  ){$_SESSION['DEBUG']=0;}
	if ($_SESSION['DEBUG'] > 0 ) {
		error_reporting(E_ALL);
		ini_set('display_errors', E_ALL);
	} else {
		if (isset($_SESSION['LOG'])  ){unset($_SESSION['LOG']);}
		error_reporting(0);
		ini_set('display_errors', 0);
	}
	
//	Define root to vendor libraries:
	define ('VENDOR',	ROOT.'vendor'.DS);
	
//	Define root to templates:
	define ('ROOT_TEMPLATES', ROOT.'templates'.DS.LANGUAGE.DS);
	
//	Load functions:
	define ('ROOT_FUNCTIONS', ROOT.'functions'.DS);
	require_once ROOT_FUNCTIONS.'loader.php';
	
//	Load classes:
	define ('ROOT_CLASSES', ROOT.'classes'.DS);
	require_once ROOT_CLASSES.'loader.php';
	
//	Load languages:
	define ('ROOT_LANGUAGES', ROOT.'languages'.DS);
	require_once ROOT_LANGUAGES.'loader.php';
	
//	Load system classes:
	define ('ROOT_SYSTEM', ROOT.'system'.DS);
	require_once ROOT_SYSTEM.'patterns.php';
	require_once ROOT_SYSTEM.'constants.php';
	require_once ROOT_SYSTEM.'db.php';
	require_once ROOT_SYSTEM.'common.php';
	require_once ROOT_SYSTEM.'controller.php';
	require_once ROOT_SYSTEM.'table.php';
	require_once ROOT_SYSTEM.'model.php';
	require_once ROOT_SYSTEM.'view.php';
	require_once ROOT_SYSTEM.'reader.php';
	
//	Define Class locations:
	define ('CONTROLLERS',	ROOT.'controllers'.DS);
	define ('MODELS', 		ROOT.'models'.DS);
	define ('VIEWS', 		ROOT.'views'.DS);
	define ('READERS', 		ROOT.'readers'.DS);
	
	
//	Create database object instance:
	$db = new Db();
	
//	Create GLOBAL['apps'] array with available applications:
	$apps 	= array();
	$rs 	= $db->query('SELECT `APP`, `name` FROM `application` WHERE `app` <> "gen" ORDER BY `name`');
	if ($rs !== false) {
		while ($row = $rs->fetch_assoc()) {
			$apps[$row['APP']] = $row['name'];
		}
	}
	
//	Read url:
	defined('URL') or exit;

//	Check if call is from an application or the root by existance of configuration file:
	$aURL = explode('/', URL);
	$application = strtolower(array_shift ($aURL));
	if ( file_exists(ROOT_CONFIG.$application.'.php') ) {
		
		//	An application-request:
			define ('APPLICATION',	$application );
			
	} else {
		
		//	An introduction-request:
			define ('APPLICATION', '');
			$application = 'root';
	}
		
	
//  Keep last selected application:
	if ( $application != 'root') {
		$_SESSION['LAST_APP'] = $application;
	}

//	Get configuration:	
	require_once ROOT_CONFIG.$application.'.php';
	
//	Directory for uploaded images
	$img = $application === 'root'?  ROOT_ENV.'_img'.DS.'vbne'.DS.'img'.DS:  ROOT_ENV.'_img'.DS.APPLICATION.DS.'img'.DS;
	define ('IMG', $img);	

//	Define ROUTE, relative and absolute path to "application":	
	define ('ROUTE',	trim(ltrim(trim(URL,'/'),APPLICATION),'/' ) );
	$application_ = APPLICATION !== "" ? APPLICATION.'/':'';
	define ('PATH_', 	PATH.$application_);
	define ('HOME_',	HOME.$application_ );
	define ('DEPTH',	substr_count(ROUTE,'/') +1);
	
//	Create modelRoute instance:
	require_once MODELS.'route.php';
	$model_route = new modelRoute();
	
//	Create modelArticle instance:
	require_once MODELS.'article.php';
	$model_article = new modelArticle();
	
//	Create instances system_info:
	require_once MODELS.'system_info.php';
	$model_system_info = new modelSystemInfo();
	

//	Detect mobile
	require_once VENDOR.'mobile_detect'.DS.'Mobile_Detect.php';
	$detect = new Mobile_Detect;
	if (!array_key_exists('device',$_SESSION) ) {
		$_SESSION['device'] = $detect->isMobile()? 'mobile': 'desktop';
	}
	

	
//	Start route-controller:	
	require_once CONTROLLERS.'router.php';
	$router = new controllerRouter();
	exit;
	
	
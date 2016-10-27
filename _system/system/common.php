<?php	//	_system/system/common.php
			defined('BONJOUR') or die;
		
/**
 * 
 *  Parent for controllers, models, views and tables
 *
 */
class	common {
		
		public	$db 					=  null;	//	Database connector.
		public  $apps					= array();	//  Available applications.
		public 	$request 				= array();	// route split into an array.
		protected	$replacements 	=  array();
	
		public $html_encoded_fields = array();
		
		public function __construct() {
			
			
		//	names of  html encoded fields:
			$this->html_encoded_fields= ['name','content','description'];
			
			//	Get database connector from GLOBALS:
				$this->db = $GLOBALS['db'];
				
			//	Get avaialable applications from GLOBALS
				$this->apps  = $GLOBALS['apps'];
				
			//	Request array:
				$this->request = explode('/',ROUTE);
				
		} // END __construct().
		
		static public  function my_name() {
			return get_called_class();
		}
		
		
		/**
		 * Converts a camelcase "ClassName" in "class_name.php".
		 * @param string $className.
		 * retunr filename formated string.
		 */
			public function class_to_filename($className) {
				
				
				//	Add '-" before each capital:
					$fileName = preg_replace('/([A-Z])/', '_$1',trim($className));
					
				//	Remove leading '_' for classnamse starting with a capital.
					$fileName = trim($fileName,'_');
				
				//	Return filename in lowercae with '.php' extention:
					return strtolower($fileName).'php';	
			}
			
			/**
			 * Converts a "class_name.php"  in a camelcase ClassName.
			 * @param string $file_name.
			 * return ClassName formated string.
			 */
			public function file_to_classname($file_name) {
				
				//	Clean file_name:
					$file_name 	= trim($file_name);
					$file_name 	= strtolower($file_name);
					$file_name 	= rtrim($file_name, '.php');
					
				//	Create and return camelcase ClassName:	
					$pieces = explode('_',$file_name);
					$pieces = array_map('ucfirst', $pieces);
				
			    	return implode('', $pieces );
			}
		
		
		/**
		 * 	Adds $smg to $_SESSION['LOG']  in case $_SESSION['DEBUG'] is set and equal or greater then $level
		 * 	@param string $msg: log message.
		 * 	@param number $level: minimum level on which log should be created.
		 *  @return boolean.
		 */
		public function addLog($msg,  $level = 0) {
			
		/*
			Adds a $msg to the $_SESSION['LOG'] collector, in case
			$_SESSION['DEBUG'] is equal or above $level.
		*/	
			//	No loging iin case debug mode is turned off.
				if (!isset($_SESSION['DEBUG'])) { return false;}
			
			//	No logging for monitor pages:
				if (str_starts('monitor', ROUTE)) {return false;}
				
				if ( $_SESSION['DEBUG'] >= $level ) {
					if (isset($_SESSION['LOG']) === false) {$_SESSION['LOG'] = array();}
					$_SESSION['LOG'][] = $msg;
					return true;
				}
				return false;
		} // END addLog().
		
		
		/**
		 * Validates $value using $method. In case falidation fails, false is retruned
		 * @param  $value value to validate
		 * @param  $method INT, FLOAT, TEXT, EMAIL or an array to defined options for a enum field
		 * @return validaed value or false
		 */
		public function validate($value, $method ) {
				
			//	Trim value:
				$value = trim($value);
				
			//	Validate an input value based on validate method:
				if (is_array($method)) {
					$options = array();
					foreach ($method as $option) {
						$options[trim(strtolower($option))] = $option;
					}
					$method= "ENUM";
				}
				switch ( strtoupper( trim($method)) ) {
		
					//	Integers:
						case 'INT':
							return filter_var($value, FILTER_VALIDATE_INT);
							break;
		
					//	Floating point:
						case 'FLOAT':
							$sanatized = filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION );
							return filter_var($sanatized, FILTER_VALIDATE_FLOAT);
							break;
						
					//	Text:
						case	'TEXT':
							return htmlspecialchars($value);
							break;
						
						
					//	Email validation
						case 'EMAIL':
							return filter_var(strtolower($value), FILTER_VALIDATE_EMAIL);
							break;
						
					//	ENUM
						case 'ENUM':
							$sanatized = trim(strtolower($value));
							return array_key_exists($sanatized, $options)? $options[$sanatized] : false;
							break;
		
					//	False: in case no validation can be made:
						default:
						return false;
			}
				
		} // END validate.
		
		/**
		 * 	Returns the active user as an array, in case no user an empty array is send.
		 *  @return 
		 *  Array() => (string)["email"] 	: email af active user.
		 *             (string)["name"]  	: full-name of active user.array.array
		 *             (int)["role"]	 	: 10 | 100| 1000.
		 *              (int)["ID"]	 	
		 *             (time)["lastactive"] : data/time value of the lastest server-request.
		 */
			public function user() {
				if ( isset($_SESSION['user']) ) { 
					return $_SESSION['user'];
				} else { 
					return array();
				}
			}
			
		/**
		 * 	Updates the lastactive field of the active user, in case excists.
		 */
			public function user_update_last_active() {
				if ( isset($_SESSION['user']) ) {
					$_SESSION['user']['lastactive'] = time();
				}
				return;
			}
			
		/**
		 * 	Checks if user is active
		 *  @return true | false.
		 */
			public function user_check_active() {
				return  isset($_SESSION['user']) ;
			}
					
			
		
		/**
		 *
		 * 	Sets $_SESSION['user'] with content of $user or removes $_SESSION['user'] in
		 *  case  $user = null or left unset.
		 * 	@param array $user: user record | null
		 *  @return array with user data or empty in case of a reset.
		 */
		public function user_set($user = null){
				
			//	In $user is not set:  reset
				if ($user === null ) {
					if (isset ($_SESSION['user'] )) {
					unset ( $_SESSION['user'] );
					}
					return array();
				}
				
			//	In case $user is not an array: return $_SESSION['user'] uchanged:
				if ( !is_array($user) ) { return  isset($_SESSION['user'])? $_SESSION['user']: array();}
		
			//	Other cases: set $_SESSION['user']:
				$_SESSION['user'] = array();
				$_SESSION['user']['lastactive'] 	= time();
				$_SESSION['user']['email'] 			= array_key_exists('email', $user) ? 	$user['email']		: '';
				$_SESSION['user']['role'] 			= array_key_exists('role', $user) ? 	(int)$user['role']	: 0;
				$_SESSION['user']['ID'] 			= array_key_exists('ID', $user) ? 	    (int)$user['ID']	: -1;
				$_SESSION['user']['application'] 	= '';
				if ( $_SESSION['user']['role'] == 100) {
						$_SESSION['user']['application'] = array_key_exists('application', $user) ? $user['application']	: '';
				}
		
			//	Create fullName:
				if (array_key_exists('name', $user) ) {
					$_SESSION['user']['name'] = $user['name'];
				}  else {
					$name = array();
					if (array_key_exists('firstName', $user) ) 	{ if ($user['firstName']!=='' ) { $name[] = $user['firstName'];	}	}
					if (array_key_exists('midName', $user) ) 	{ if ($user['midName']!=='' ) 	{ $name[] = $user['midName'];	}	}
					if (array_key_exists('lastName', $user) )	{ if ($user['lastName']!=='' ) 	{ $name[] = $user['lastName'];	}	}
				
					$_SESSION['user']['name'] = count($name) >0? implode(' ',$name) : $_SESSION['user']['email'];
				}	
				return $_SESSION['user'];	
		}
		
		/**
		 * Creates a string in ClassNameFormat from a string. In case no, or unknown prefix is given, no prefix is added.
		 * @param string $str:  string to be converted to className. extention '.php' will be removed, 
		 *                      in casse string contains "/" (an url) only the last part is used.
		 * @param string $prefix [ controller | model | view | table | uts]
		 * @return prefixCamelCaseClassName
		 */
		public function className($str = "", $prefix = ""){
			
			//	Remove .php:
				$str = trim($str);
				$str = trim($str, '.php');
			
			//	Get last part in case string contains "/":
				$str 	= trim($str,'/');
				$last  	= strrchr($str, "/");
				$str	= $last === false ? $str : trim(  $last,'/');
			
			//	Make CamelCaseName:
				$parts = explode('_',strtolower($str));
				$parts = array_map ( "ucfirst",$parts);
				$className = implode($parts);
				
			//	Add prefix:
				$prefix = trim( strtolower($prefix) );
				switch ($prefix) {
					case "controller":
					case "model":
					case "view":
					case "table":
					case "uts": 	$className =$prefix.$className; break;
					default:		break;
				}
				return $className;
			
		}
		
		/**
		 * HTML templating
		 * Replace in $str fields {$field}.
		 * @param string $str: template formated string
		 * @param array $fields: replace values: field=>value
		 * @return string $str with replaced fields.
		 */
		protected function fieldReplace($str='', $fields = null) {
			
				if ($fields === null)  {
					$fields = $this->replacements;
				}
			
				$result ='';
			
			//	Split string
				$parts = preg_split("/[{}]/",$str);
				
			//	In case no brackets objects are found or by an incorrect format: add line as is.
				$num = count($parts);
				if ( $num<3 || is_even($num)) { 
					if ($num>1) {
						$this->addLog('\`.'.$str.'` cann`t be filled in: incorrect format.', 100);
					}
					return $str; 
				}
					
			//	Step by part:
				foreach ($parts as $i => $part) {
							
					//	An even part is plain text: add as is:
						if (is_even($i) ) {$result .= $part; continue;}
						
					//	Odd part: label to be replaced:
						$label = trim($part);
						//$label = ltrim($label,'$');
						if (strstr($label,'.')) {
							$replacement = $this->findReplacement(explode('.',$label),$fields);
							if ($replacement !== false) {
								$result .=$replacement;
							} else {
								$this->addLog('Field`.'.$label.'` is not found.', 100);
							} 	
						} else {
							if ( array_key_exists($label, $fields) ){
								$result .= $fields[$label];
							} else {
								$this->addLog('Field`.'.$label.'` is not found.', 100);
							}
						}
				}
				return $result;
			
		} // END fieldReplace()
		
		/**
		 * HTML templating
		 * Recursive lookup of replacements in a nested array
		 * 
		 */
		protected function findReplacement($names, $replacements) {
			
			//	Stop in case there are no nested replacements:
				if ( !is_array($replacements) ) {return false;}
			
			//	Get next name:
				$name = array_shift($names);
				
			//	Check if name excists:	
				if (array_key_exists($name,$replacements)) {	
					if (count($names) >0 ) {
						return $this->findReplacement($names,$replacements[$name]);
					} else {
						return $replacements[$name];
					}
				} else {
					return false;
				}
			
		} // END findReplacement().
		
		
		/**
		 * Executes a SELECT query
		 * @param string $sql: MySQL query.
		 * @param boolean $transepose: if true: result will be transposed (default: false).
		 * @return associative array [row=>columns] / [fields=>value]
		 * At failure an empty array is returned.
		 */
		protected function select($sql, $transepose = false) {
		
	
			
			//	Export an array row=>columns:
				$records = array();
		
			//	Excecute Query:
				$this->addLog($sql,100);
				$result = $this->db->query($sql);
				if ($result === false) {
					$this->addLog($this->db->error,1);
					return $records;
				}
				
			//	Process result:	
				if ($result->num_rows == 0  ) {
					$this->addLog('Last SQL request didn\'t return anny result!',100);
					return $records;
				}
				
				if ($transepose) {
					while ($row = $result->fetch_assoc()) {
						foreach ($row as $col=>$val) {
							if (array_key_exists($col, $records) === false) {
								$records[$col] = array();
							}
						$records[$col][] = $val;
						}
					}
				} else {
					while ($row = $result->fetch_assoc()) {
						$records[] = $row;
					}
				}
				return $records;
				
				
		} // END select().
		
		/**
		 *  Push $href to $_SESSION['STACK']
		 *  @param string href
		 */
		public function stack_push($href){
				
			//	Create $_SESSION['STACK'] (in case not excist:
			if (!array_key_exists('STACK', $_SESSION) )	{$_SESSION['STACK'] = array(); }
			array_push($_SESSION['STACK'], $href);
			return;
		}
		
		/**
		 *  Removes last element of
		 *  $_SESSION['STACK']
		 *   @return $href or false
		 */
		function stack_pop(){
			if (!array_key_exists('STACK', $_SESSION) ) {return false;}
			$return = array_pop($_SESSION['STACK']);
			if ( count($_SESSION['STACK']) <1 ) {
				unset($_SESSION['STACK']);
			}
			return $return;
		}
		
		/**
		 *  Get last element of
		 *  $_SESSION['STACK']
		 *   @return $href or false
		 */
		function stack_get(){
			if (!array_key_exists('STACK', $_SESSION) ) {return false;}
			$stack = $_SESSION['STACK'];
			if ( count($stack) <1 ) {return false;}
			return end($stack);
		}
		
		public function favorites(){
		
			$favorites =  array_key_exists(SES_FAVORITES, $_SESSION)?  $_SESSION[SES_FAVORITES]: array();
			return array_key_exists(APP, $favorites)?  $favorites[APP]: array();
		}


}  // END class common
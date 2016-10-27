<?php	// _system/classes/css.php


class css{
	
	
	private $css_array 			= array();
	private $pattern_selectors 	='/(\/\*|\*\/|\{|\})/m';
	
	/**
	 * Constructor for ccs class.
	 * @param string $path: (optional) absolute path from ROOT to the css-file.
	 *                       Remark: use "DIRECTORY_SEPARATOR
	 *	                     When set, the css_array is created during construction.
	 */
	public function __construct($path = null){
		
		
		
	//	Read $html node:
		if ( !is_null( $path) ){ $this->set_source($path);}
		return;
	}
	
	/**
	 * Create css_array-property using file indicatd by css_path-property. 
	 * @param string $path: (optional) absolute path from ROOT to the css-file.
	 *                       Remark: use "DIRECTORY_SEPARATOR
	 *	                     When set, the css_array is created re-created using $path.
	 * @return array represending css file:
	 * 			[selector] => array{ [property_name] => property_value; }
	 */
	public function get_css_array($path = null){
	
		if (is_null($path)) { return $this->css_array;}
		$this->set_source($path);
		return $this->css_array;
	}
	
	
	/**
	 * Set the location of the css file and read its content and fills the css_array-property. 
	 * In case file doesn't excist, no action is taken and current css_array-property is returned.
	 * @param string $path: absolute path from ROOT to the css-file.
	 *                      Remark: use "DIRECTORY_SEPARATOR".
	 * @return:bool
	 */
	public function set_source($path){
		
	//	Reset css_array-property:
		$this->css_array = array();
		
	//	Check path:
		if (is_null($path))    {return false;}
		if (!is_string($path)) {return false;}
	
	//	Check if file 
		$path = ROOT_ENV.trim($path);
		if (!file_exists($path) ){return false;}
		
	//	Read file into string:
		$css = file_get_contents($path);
		
	//	Split into comment and css rules:
		$rules =  preg_split($this->pattern_selectors, $css, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		
	
	//	Process
		$result = array();
		$is_css = true;
		$is_rules = false;
		$selector = '___';
		foreach  ($rules as $rule){
			
			$rule =trim($rule);
			
			
			switch ($rule) {
				
				case '':
				break;
				
				case '{':
				$is_rules = true;
				break;
				
				case '}':
				$is_rules = false;
				break;
				
				case '/*':
				$is_css =  false;
				break;
				
				case '*/':
				$is_css =  true;
				break;
				
				default:
				if ($is_css){
					if ($is_rules) {
						$parts = explode(';',$rule);
						$r = array();
						foreach ($parts as $part){
							$part = trim($part);
							$key_val = explode(':',$part);
							if (count($key_val) == 2) {
								$r[$key_val[0]] = $key_val[1];
							}
						}
						$selectors = explode(',',$selector);
						foreach ($selectors as $sel) {
							$sel = trim($sel);
							if (array_key_exists($sel, $result)){
								$result[$sel]= array_merge($result[$sel],$r);
							} else {
								$result[$sel]= $r;
							}
							
						}
					}else {
						$selector = trim($rule);
					}
					
				}
				break;

			}
	
		}
		
		$this->css_array = $result;
		return true;
		
	}
	

	
	

	


}
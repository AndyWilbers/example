<?php	//	_system/function/genetic.php
			defined('BONJOUR') or die;
			
//	lcfirst for PHP verdsion < 5.3	:		
	if(function_exists('lcfirst') === false) {
		function lcfirst($str) {
			$str[0] = strtolower($str[0]);
			return $str;
		}
	}			

//	Checking numeric:
	function is_even($num){
	
		if ( (int)$num & 1 ) {
		  return false; //odd.
		} else {
		  return true; //even.
		}
		
	} // END is_even();
	
	function is_odd($num){
		
		if ( (int)$num & 1 ) {
		  return true; //odd.
		} else {
		  return false; //even.
		}
		
	} // END is_odd();
	
	
	function geo_location_decimal_to_degrees($dec, $direction='lat'){
		
		if (is_null($dec)){
			return TXT_LBL_GEO_EMPTY;
		}
		
		$pos = strtolower(trim($direction)) == 'lat'? TXT_LBL_GEO_N: TXT_LBL_GEO_E;
		$neg = strtolower(trim($direction)) == 'lat'? TXT_LBL_GEO_S: TXT_LBL_GEO_W;
		
		$dir 		= $dec>=0? $pos : $neg;
		$dec		= abs($dec);
		$deg 		= floor($dec);
		$minutes 	= floor(60*($dec-$deg));
		$seconds 	= round(60*60*($dec-$deg - $minutes/60));
		
		return $deg.'&deg;&nbsp;'.str_pad($minutes, 2, '0', STR_PAD_LEFT).'&quot;&nbsp;'.str_pad($seconds, 2, '0', STR_PAD_LEFT).'&quot;&quot;&nbsp;'.$dir;
	}
	
/**
 * In case "$haystack" starts with "$needle" true is returned, in other cases the return-value is false.
 * @param string $needle
 * @param string $haystack
 * @return boolean
 */
	function str_starts($needle, $haystack) {
		return substr($haystack, 0, strlen($needle)) === $needle;
	}

	/**
	 * Creates a nested ul of an array.
	 * @param array $arr
	 * @return (string) key=>value of a nested array.
	 */
	function array_pretty_print($arr) {
		
		$html = '<ul class="pretty_print">';
		if (is_array($arr)){
			foreach ($arr as $key=>$val){
				if (is_array($val)){
					$html .= '<li>' . $key . ' => <span>('.gettype($val).')</span> ' . array_pretty_print($val) . '</li>';
				}else{
					if ( gettype($val) == "boolean" ) {
						$print_val = $val?"true" :"false";
					} else {
						$print_val = $val;
					}
					$html .= '<li>' . $key . ' => <span>('.gettype($val).')</span> ' . $print_val . '</li>';
				}
			}
		}
		$html .= '</ul>';
		return $html;
		

	}
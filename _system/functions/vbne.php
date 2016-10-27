<?php	//	_system/function/vbne.php
			defined('BONJOUR') or die;
			
/**
 * Returns the content of $_SESSION['observations']
 * When doesn't excist an empty array is returned.
 * @return array [ID] { float | integer | array{ integers}
 */
function vbne_observations_get(){
	if (!array_key_exists('observations', $_SESSION) ) { return array();}
    if (!array_key_exists(APP, $_SESSION['observations']) ) { return array();}
	return $_SESSION['observations'][APP];
}

/**
 * Unset $_SESSION['observations']
 * @return true
 */
function vbne_observations_clear(){
	if (!array_key_exists('observations', $_SESSION) ) { return true;}
 	if (!array_key_exists(APP, $_SESSION['observations']) ) { return true;}
 	unset( $_SESSION['observations'][APP]);
 	if (count($_SESSION['observations']) == 0 ) { unset($_SESSION['observations']);}
	return true;
}

/**
 * Set  $_SESSION['observations'][ID]
 * @return true |false
 */
function vbne_observations_set($ID=null, $value = null){	

//	Check ID:
	if( (int)$ID <= 0 ) 	{ return false;}

// 	$value is set: ADD to $_SESSION['observations']:	
	if ($value !== null )	{ 
		
	//	Create $_SESSION['observations'] in case not excist:
		if (!array_key_exists('observations', $_SESSION) ) {$_SESSION['observations'] = array();}
		if (!array_key_exists(APP, $_SESSION['observations']) ) { $_SESSION['observations'][APP] = array();}
		$_SESSION['observations'][APP][$ID] = $value;
		return true;
		
//	$value is NULL: REMOVE value from $_SESSION['observations']:		
	} else {
	
	//	Return in case $ID is not set in $_SESSION['observations']:
		if ( !array_key_exists('observations', $_SESSION ) 		) {	return true;}
		if (!array_key_exists(APP, $_SESSION['observations']) ) { return true;}
		if ( !array_key_exists($ID, $_SESSION['observations'][APP] )	) {	return true;}
	
	//	Unset $ID, in case $_SESSION['observations'][APP] is empty: remove all;
		unset( $_SESSION['observations'][APP][$ID]);
		if (count($_SESSION['observations'][APP]) == 0) { unset( $_SESSION['observations'][APP]);}
		if (count($_SESSION['observations']) == 0 ) { unset($_SESSION['observations']);}
		return true;
	}
}

/**
 * Get  $_SESSION['observations'][ID]
 * @return { float | integer | array{ integers} or false in case ID doesn't excist.
 */
function vbne_observation_get($ID=null){
//	Check input:
	if( (int)$ID <= 0 ) 	{ return false;}

//	Check availability of $_SESSION['observations']:
	if ( !array_key_exists('observations', $_SESSION) ) 			{ return false;}
	if ( !array_key_exists(APP, $_SESSION['observations'] )	) 		{ return false;}
	if ( !array_key_exists($ID,  $_SESSION['observations'][APP]) ) 	{ return false;}

	return $_SESSION['observations'][APP][$ID];
}

function vbne_observation_set_id($ID = null){

	$ID = (int)$ID;
	if ($ID >0 ) {
		if (!array_key_exists('observations_ID', $_SESSION)){
			$_SESSION['observations_ID'] = array();
		}
		$_SESSION['observations_ID'][APP] = $ID;
		$set  = vbne_observation_get_record();
			
		if (array_key_exists('RECORDS', $set)){

			if (!array_key_exists('observations', $_SESSION) ) {$_SESSION['observations'] = array();}
			$_SESSION['observations'][APP]=$set['RECORDS'];

		}
			
	}
}

function vbne_observation_reset_id(){
	if ( !array_key_exists('observations_ID', $_SESSION) ){ return true;}
	if ( !array_key_exists(APP, $_SESSION['observations_ID']) ){ 
		if (count($_SESSION['observations_ID']) == 0 ){ unset($_SESSION['observations_ID']);} 
		return true;
	}
	unset($_SESSION['observations_ID'][APP]);
	if (count($_SESSION['observations_ID']) == 0 ){ unset($_SESSION['observations_ID']);}
	return true;
}

function vbne_observation_get_record(){

	if ( !array_key_exists('observations_ID', $_SESSION)       ){ return array(); }
	if ( !array_key_exists(APP, $_SESSION['observations_ID'])  ){ return array(); }

	$ID = (int)$_SESSION['observations_ID'][APP];

	$table = new table('user_obs');
	$aR =  $table->ar($ID);

	if (array_key_exists('RECORDS', $aR) ){
		$aR['RECORDS'] = json_decode($aR['RECORDS'], true);
	}

	return $aR;

}
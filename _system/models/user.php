<?php	//	_system/models/user.php
			defined('BONJOUR') or die;		
			
				

class	modelUser extends  model {
		
/*	
	The user model.
*/	

			
//	Constructor:	
	public function __construct() {
		
			parent::__construct();
			

		  	//	set active table to "user":
				$this->active_table_set('user');
			
		    	
			
	} // END __construct(). 

		
// 	Methods:

	public function get_ar($ID = null){
	
	
		//	Get records from base table:
			$ID = (int)$ID;
			if ($ID < 1 ) { return $ar; }
			$ar = parent::get_ar($ID);
			
		// 	Get only those fields that can edited by client
			$ar_send= array();
			$ar_send['ID'] 			        = array_key_exists('ID', $ar)? $ar['ID'] 					:-1;
			$ar_send['firstName'] 			= array_key_exists('firstName', $ar)? $ar['firstName'] 		: "";
			$ar_send['midName'] 			= array_key_exists('midName', $ar)? $ar['midName']   		: null;
			$ar_send['lastName'] 			= array_key_exists('lastName', $ar)? $ar['lastName'] 		: "";
			$ar_send['email'] 			    = array_key_exists('email', $ar)? $ar['email'] 		        : "";
			$ar_send['showName'] 			= array_key_exists('showName', $ar)? $ar['showName'] 		: -1;
			
			return $ar_send;
	
	}
	
	public function save_ar($fields = array(), $ID = null) {
		
		
	
	//	Check ID:
		if ( $ID === null) {return false;}
		$ID = (int)$ID;
		if ($ID < 1) { return false;}
	
		
	//	Check fields:
		if ( !is_array($fields) ){ return false;}
		if (count($fields) < 1)  { 
			$this->addLog('Geen velden',1);
			return false;
		}
		
	//	Crypt password:
		if ( array_key_exists('password', $fields) ){
		
		//	Get salt:
			$ar = $this->ar($ID);
			if ( !array_key_exists('salt', $ar) ){
				return false;
			}
			
			$fields['password'] = enigma_crypt($fields['password'], $ar['salt']);
		}
		
	//	Update 'vbne' field in stored observations
		if (array_key_exists('shareObservations', $fields)){
			$vbne = (int)$fields['shareObservations']>0? 1: -1;
			$sql = 'UPDATE `user_obs` SET `vbne`='.$vbne.' WHERE `FID_USER`='.$ID;
			$this->db->query($sql);
		}
	
		return parent::save_ar($fields,$ID);
		
	}

	/**
	 * Gives user-record by email
	 * @param string $email
	 * @return array with user-record (or false in case of failure  or no match)
	 */
	public function getByEmail($email='') {
		
		//	Validate email:
			$email = $this->validate($email, 'EMAIL');
			if ($email === false) { return false;}
			$email =strtolower($email);
		
		//	Get record from user-table:
			$options = array();
			$options['where'] = '`email`="'.$email.'"';
			$records = $this->active_table->select_all($options);
			
			
			$aR = count($records) === 1? $records[0] : false;
			if ($aR == false )  {return false;}
			
		//	Add respond_date:
			$date = new DateTime($aR['updateRequest']);
			$date->add(new DateInterval('PT'.TIMESLOT.'H'));
			$aR['respond_date'] =$date->format('j-M-Y, H:i');
			
		//	Add fullname:
			$full_name = array();
			$name =  array_key_exists('firstName', $aR)? trim($aR['firstName'])		: "";
			if ( strlen($name) > 0) {
				$full_name[] = $name;
			}
			$name =  array_key_exists('midName', $aR)? trim($aR['midName'])		: "";
			if ( strlen($name) > 0) {
				$full_name[] = $name;
			}
			$name =  array_key_exists('lastName', $aR)? trim($aR['lastName'])		: "";
			if ( strlen($name) > 0) {
				$full_name[] = $name;
			}
			$aR['full_name'] = implode(' ',$full_name);
			
			return $aR;
			
	} // END getByEmail().
	
	public function check_email_is_free($email){
	
	//	Remove records that never have been activated:	
		$this->clean();
		
	//	Get record:
		$aR = $this->getByEmail($email);
		if ( $aR === false ) {
			return true;
		}
		if ($aR['new'] < 1) {return false;}
		return true;
		
	}
	
	private function clean(){
		
	//	Remove records that never have been activated:	
		$timeslot = 60 * (int)TIMESLOT;
		$sql = 'DELETE FROM `user` WHERE TIMESTAMPDIFF(MINUTE,`updateRequest`,NOW()) > '.$timeslot.' AND `new` > 0';
		$this->db->query($sql);
		return;
	
	}
	
	/**
	 * Compares $password, with password form $user. Returns also false in case user is not activaed in the database.
	 * @param string $password 
	 * @param array $user record 
	 * @return boolean:
	 */
	public function checkLogin ($password, $user) {
		
		//	Check if user is active:
			if ($user['active'] != 1) { return false;}
			
		//	Check password:
			if ($user['password'] !== enigma_crypt($password, $user['salt']) ) {return false;}
		
		// Update lastLogin:
			$ID = array_key_exists('ID',$user)? (int)$user['ID'] :-1;
			if ($ID >0) {
				$fields = array();
				$now = new DateTime('now',timezone_open('Europe/Berlin'));
				$fields['lastLogin'] =  $now->format('Y-m-d H:i:s');
				
				$this->save_ar($fields, $ID);
			}
			
			
		
		    return true;
	
	} // END checkLogin().
	
	
	public function new_account($email){
		
		$aR = $this->getByEmail($email);
		$ID = $aR === false? null: $aR['ID'];
		$aR = $aR === false? array(): $aR;
		
		$fields 		 			= array();
		$fields['email'] 			= strtolower($email);
		$fields['salt']  			=  array_key_exists('salt', $aR)? $aR['salt']: enigma_salt();
		$code  						=  array_key_exists('password', $aR)? $aR['password']: enigma_salt();
		$fields['password']         =  $code;
		$fields['code']  			= enigma_crypt($code, $fields['salt'] );
		$fields['updateRequest']  	= null;
		
		$aR = $this->getByEmail($email);
	    $ID = $aR === false? null: $aR['ID'];
		parent::save_ar($fields,$ID);
		
		$fields =  $this->getByEmail($email);
		$fields['code'] = $code;
		return $fields;
		
	}
	
	public function deactivate_account($email){
	
		$aR = $this->getByEmail($email);
		if ($aR === false) {return false;}
		
		$ID = $aR['ID'];
		
	
		$fields = array();
	
		if ($aR['active'] == 1) {
			$fields['active'] =-1;
			$code = enigma_salt();
			$fields['password'] = $code;
		} else {
			$code = $aR['password'];
		}
		$fields['password']         = $code;
		$fields['code']  			= enigma_crypt($code, $aR['salt']);
		$fields['updateRequest']  	= null;
	
		parent::save_ar($fields,$ID);
	
		$fields =  $this->getByEmail($email);
		$fields['code'] = $code;
		return $fields;
	
	}
	
	public function activate_account($fields){
		
		
	//	get aR:
		$aR = $this->getByEmail($fields['email']);
		if ( $aR === false ) {
			$this->msg = TXT_ERR_UNKNOWN;
			return false;
		}
		
	//	Check code:
		$code = enigma_crypt($fields['code'], $aR['salt']);
		$this->addLog('code-2: '.$code,1);
		if ($code  != $aR['code']){
			$this->msg = TXT_ERR_USER_CODE;
			return false;
		}
		
	//	Check respond date:
		$date = new DateTime($aR['updateRequest']);
		$date->add(new DateInterval('PT'.TIMESLOT.'H'));
		$now = new DateTime();
		if ($now > $date) {
			$this->msg =TXT_ERR_USER_TIMEOUT;
			return false;
		}
		
	//	Compare passwords:
		if ($fields['password'] !== $fields['password_check']){
			$this->msg  = TXT_ERR_USER_PASSWORD_CHECK;
			return false;
		}
		
	//	Check password length:
		$password = trim($fields['password']);
		if ( strlen($password) < (int)MIN_PASSWORD_LEN ){
			$this->msg  = TXT_ERR_USER_PASSWORD;
			return false;
		}
		
		
	//	save new account
		$new =array();
		$new['password'] = $password;
		$new['code'] = null;
		$new['updateRequest'] = null;
		$new['new'] = -1;
		$new['active'] = 1;
		$now = new DateTime('now',timezone_open('Europe/Berlin'));
		$fields['lastLogin'] =  $now->format('Y-m-d H:i:s');
		$check = $this->save_ar($new,$aR['ID']);
		if ($check == false){
			$this->msg = TXT_ERR_UNKNOWN;
			return false;
		}
		$aR = $this->getByEmail($aR['email']);
		$this->user_set($aR );
		return true;
		
	
	}
//	Report- observation sets:
	
	/**
	 * Gives the record of a report set including [viewers]
	 * @return array()
	 */
	public function get_my_report($ID) {
		return $this->get_my_record('rep',$ID);
	}
	
	/**
	 * Gives the record of an observation set including [viewers]
	 * @return array()
	 */
	public function get_my_observation($ID) {
		return $this->get_my_record('obs',$ID);
	}
	
	private function get_my_record($ext,$ID=null){
		
		$ID =(int)$ID;
		$ID  = $ID<1? -1: $ID;
	
		$return 			= array();
		$return['viewers']  = array();
		
	
	//  Check ownership and open ar of table
		$table = $this->check_recordset_owner_ship($ext, $ID);
		if ($table === false ) {return $return;}
	
	//	Get record:
		$return = $table->ar($ID);
		
	//	Get parameters of initial Google Map:
		if ( $ext == "obs"){
			$return['zoom'] =floatval(GEO_ZOOM);
			
			$ne_lat = $return['NE_LAT'] == null? floatval(GEO_CENTRE_LAT): floatval( $return['NE_LAT']);
			$sw_lat = $return['SW_LAT'] == null? floatval(GEO_CENTRE_LAT): floatval( $return['SW_LAT']);
			$return['centre_LAT'] = $sw_lat + ($ne_lat-$sw_lat)/2;
			
			$ne_lng = $return['NE_LNG'] == null? floatval(GEO_CENTRE_LNG): floatval( $return['NE_LNG']);
			$sw_lng = $return['SW_LNG'] == null? floatval(GEO_CENTRE_LNG): floatval( $return['SW_LNG']);
			$return['centre_LNG'] = $ne_lng + ($sw_lng-$ne_lng)/2;
			
			$return['NE_LATtxt'] = geo_location_decimal_to_degrees($return['NE_LAT'],'lat');
			$return['SW_LATtxt'] = geo_location_decimal_to_degrees($return['SW_LAT'],'lat');
			$return['NE_LNGtxt'] = geo_location_decimal_to_degrees($return['NE_LNG'],'lng');
			$return['SW_LNGtxt'] = geo_location_decimal_to_degrees($return['SW_LNG'],'lng');
		}
		
	//	Get viewers
	    $return['viewers'] = $this-> get_recordset_viewers($ext, $ID);
	    
	    return $return;
	
	}
	
	/**
	 * Gives availalbe report sets for current active user
	 * @return array [owner]=> {fields}; [shared]=> {fields}
	 */
	public function get_reports() {
		return $this->get_recordset('rep');
	}
	
	/**
	 * Gives availalbe observation sets for current active user
	 * @return array [owner]=> {fields}; [shared]=> {fields}
	 */
	public function get_observations(){
		return $this->get_recordset('obs');
	}
	private function get_recordset($ext) {
		
		$table 		= '`user_'.$ext.'`';
		$table_sha 	= '`user_'.$ext.'_sha`';
		
		$return = array();
		$return['owner']  = array();
		$return['shared'] = array();
		
		
	//	Get ID of active user:
		$user =$this->user();
		$ID = array_key_exists('ID', $user) ? (int)$user['ID'] : -1;
		if ($ID <1) { return $return;}
		
	//	Get records of owner:
		$sql =	' SELECT * FROM '.$table.' WHERE `APP`= "'.APP.'" AND `FID_USER`="'.$ID.'" ORDER BY `name`';
		$records = $this->select($sql);
		foreach ($records as $key=>$record){
			$records[$key]['RECORDS'] = json_decode($record['RECORDS'],false);
		}
		$return['owner']= $records;
		
	//	Get shared records:
		$sql =	' SELECT * FROM '.$table_sha.' WHERE `FID_USER`="'.$ID.'"';
		$rows= $this->select($sql);
		$IN = array();
		foreach ($rows as $row){
			$IN[] = $row['FID_RECORD'];
		}
		if (count($IN) >0) {
			$ID_IN =  $table.'`.ID` IN('.implode(',',$IN).')';
			
			$sql =	' 	SELECT DISTINCT '.$table.'.*,  `user`.`email` 
						FROM '.$table.' 
						LEFT JOIN `user` ON( `user`.`ID` = '.$table.'`FID_USER`)
						WHERE `APP`= "'.APP.'" AND '.$ID_IN.' 
						ORDER BY '.$table.'`.name`
					
					';
			
			$records = $this->select($sql);
			foreach ($records as $key=>$record){
				$records[$key]['RECORDS'] = json_decode($record['RECORDS'],false);
			}
			$return['shared']= $records;
		}
		return $return;
		
	}
	
	/**
	 * Save (INSERT or UPDATE) record in table user_rep 
	 * @param $fields: array with fields to save, $ID (for INSERT null)
	 * @return active record  or false and set ->msg;
	 */
	public function save_report($fields = array(), $ID=null) {
		return $this->save_recordset('rep',$fields,$ID);
	}
	
	/**
	 * Save (INSERT or UPDATE) record in table user_obs
	 * @param $fields: array with fields to save, $ID (for INSERT null)
	 * @return active record  or false and set ->msg;
	 */
	public function save_observation($fields= array(),$ID=null){
		return $this->save_recordset('obs',$fields,$ID);
	}
	private function save_recordset($ext,$fields= array(),$ID=null){
		
		
	
	//	Table name:
		$table 	= '`user_'.$ext.'`';
		
	//	Get ID of active user:
		$user = $this->user();
		$FID_USER = array_key_exists('ID', $user) ? (int)$user['ID'] : -1;
		if ($FID_USER  <1) { 
			$this->msg = TXT_LOGIN;
			return false;
		}
		$fields['FID_USER'] =$FID_USER;
		
	//	Get 'vbne': share observation with vbne:
		if ($ext === 'obs'){
			$aR = $this->active_table->ar($FID_USER);
			$fields['vbne'] = $aR['shareObservations'];
		}
		
	//	Check  unique name:
		if ( array_key_exists('name', $fields) ){
			if ( $ID==null ){
				$sql =	' SELECT COUNT(`ID`) as `nb` FROM '.$table.' WHERE `APP`= "'.APP.'" AND `FID_USER`="'.$FID_USER.'" AND `name`="'.$fields['name'].'" LIMIT 1';
			} else {
				$sql =	' SELECT COUNT(`ID`) as `nb` FROM '.$table.' WHERE `APP`= "'.APP.'" AND `FID_USER`="'.$FID_USER.'" AND `name`="'.$fields['name'].'" AND `ID`<>"'.$ID.'" LIMIT 1';
			}
			$check= $this->select($sql);
			if ( is_array($check) ){
				if ( (int)$check[0]['nb'] >0 ) {
					$this->msg = TXT_VBNE_ERR_NAME_FE_UNIQUE;
					return false;
				}
			}
		}
		
		
	//	overwrite field APP with current APP
	    $fields['APP'] = APP;
	    
	//	Encode RECORDS field:
	    if ( array_key_exists('RECORDS', $fields) ){
	    	$records = json_encode($fields['RECORDS']);
	    	if ($records !== false) {
	    		$fields['RECORDS'] =$records;
	    	}
	    }
	    
    //	Date field:
	    if ( array_key_exists('date', $fields) ){
	    	$aDate = explode('-',$fields['date']);
	    	unset($fields['date']);
	    	if (count($aDate) == 3 ){
	    		$strDate = $aDate[2].'-'.$aDate[1].'-'.$aDate[0].' 00:00:00';
	    		$date = new DateTime($strDate,timezone_open('Europe/Berlin'));
	    		$fields['date'] =  $date->format('Y-m-d H:i:s');
	    		
	    	}
	    }
	    
	//	Viewers:
	    if (!is_null($ID) && array_key_exists('viewers', $fields)){
	    		$this->addLog('add VIEWERS',1);
	    		
	    		
	    		$this->addLog($fields['viewers'],1);
	    		
	    	    $aViewers = json_decode($fields['viewers']);
	    	    unset($fields['viewers']);
	    	    
	    	    $this->addLog($aViewers[0],1);
	    	
	    	//	Remove all viewers:
	    		$table_sha = '`user_'.$ext.'_sha`';
	    		$sql = 'DELETE FROM '.$table_sha.' WHERE `FID_RECORD`="'.$ID.'"'; 
	    		$result = $this->db->query($sql);
	    		$this->addLog($sql,1);
	    		
	    	//	Add new viewers:
	    		if ($result) {
	    			$sql  = 'INSERT INTO '.$table_sha.' (`FID_RECORD`,`FID_USER`) VALUES';
	    			$glue = '';
	   
	    			foreach ($aViewers as $v){
	    				$sql .= $glue.'('.$ID.','.(int)$v.')';
	    				$glue= ', ';
	    			}
	    			$this->db->query($sql);
	    		}
	    		$this->addLog($sql,1);
	    
	    }
	    
    //	Swap  GEO-location  in case NE is sout-west of SW:
    	if (!is_null($ID) && $ext=="obs") {
    		
    		$tbl = new table('user_obs');
    		$aR = $tbl->ar($ID);
    		
    		$ne_lat = array_key_exists('NE_LAT', $fields)? $fields['NE_LAT'] : $aR['NE_LAT'];
    		$ne_lng = array_key_exists('NE_LNG', $fields)? $fields['NE_LNG'] : $aR['NE_LNG'];
    		$sw_lat = array_key_exists('SW_LAT', $fields)? $fields['SW_LAT'] : $aR['SW_LAT'];
    		$sw_lng = array_key_exists('SW_LNG', $fields)? $fields['SW_LNG'] : $aR['SW_LNG'];
    		
    		
    		if (!is_null($ne_lat )) {$ne_lat = floatval($ne_lat);}
    		if (!is_null($ne_lng )) {$ne_lng = floatval($ne_lng);}
    		if (!is_null($sw_lat )) {$sw_lat = floatval($sw_lat);}
    		if (!is_null($sw_lng )) {$sw_lng = floatval($sw_lng);}
    		
    		if (!is_null($ne_lat ) && !is_null($sw_lat ) ) {
    			if ($ne_lat < $sw_lat) {
    				$swap   = $sw_lat;
    				$sw_lat = $ne_lat;
    				$ne_lat = $swap;
    			}
    		}
    		if (!is_null($ne_lng ) && !is_null($sw_lng ) ) {
    			if ($ne_lng < $sw_lng) {
    				$swap   = $sw_lng;
    				$sw_lng = $ne_lng;
    				$ne_lng = $swap;
    			}
    		}
    		
    		$fields['NE_LAT'] = $ne_lat;
    		$fields['NE_LNG'] = $ne_lng;
    		$fields['SW_LAT'] = $sw_lat;
    		$fields['SW_LNG'] = $sw_lng;
    		
    	}
    	
	    
	//  INSERT or UPDATE:
	    if ($ID === null) {
	    	
	    	//	Create table instance:
	    		$table = new table('user_'.$ext);
	    		
	    	//	Insert 
	    		$check = $table->ar_insert($fields);
	    		$this->msg = $check ? TXT_DB_SUCCESS: TXT_ERR_UNKNOWN;
	    		if ($check === false) { return false;}
	    		return $table->ar();
	    } else {
	    	
	    	//  Check ownership and open ar of table
	    		$table = $this->check_recordset_owner_ship($ext, $ID);
	    		if ($table === false ) {return false;}
	    		
	    	//	Update record:
	    		$table->ar($ID);
	    		$check = $table->ar_update($fields);
	    		$this->msg = $check ? TXT_DB_SUCCESS: TXT_ERR_UNKNOWN;
	    		if ($check === false) { return false;}
	    		return $table->ar();
	    }
		
	}
	
	/**
	 * Delete record in table user_rep
	 * @param integer  $ID
	 * @return boolean and set ->msg;
	 */
	public function delete_report($ID=null){
		return $this->delete_recordset('rep', $ID);
	}
	
	/**
	 * Delelte record in table user_obs
	 * @param integer  $ID 
	 * @return boolean and set ->msg;
	 */
	public function delete_observation($ID=null){
		return $this->delete_recordset('obs', $ID);
	}
	private function delete_recordset($ext,$ID){
		

	//  Check ownership and open ar of table
		$table = $this->check_recordset_owner_ship($ext, $ID);
		if ($table === false ) {return false;}
		
	//	Delete record:
		$check =$table->ar_delete(); 
		$this->msg  = $check? TXT_DELETE_SUCCESS: TXT_ERR_UNKNOWN;
		return $check;

	}
	/**
	 * Get list of all users that share their name
	 * @param integer ID of report set.
	 * @return array {'ID':user ID, 'name': full name + email, check: boolean }
	 */
	public function get_report_viewers($ID = null){
		return $this->get_recordset_viewers('rep', $ID);
	}
	
	/**
	 * Get list of all users that share their name
	 * @param integer ID of observation set.
	 * @return array {'ID':user ID, 'name': full name + email, check: boolean }
	 */
	public function get_observation_viewers($ID = null){
		return $this->get_recordset_viewers('obs', $ID);
	}
	
	private function get_recordset_viewers($ext, $ID= null){
		
		$return = array();
		
	//	Get list of available users:
		$table = new table('user');
		$users = $table->select_all(array('where'=>'`active` >0 AND `showName`>0','orderby'=>'`lastName`, `firstName`, `email`'));
		
	//	Get current viewers:
		$table_sha 	= '`user_'.$ext.'_sha`';
		$sql =	' SELECT * FROM '.$table_sha.' WHERE `FID_RECORD`="'.$ID.'"';
		$rows = $this->select($sql);
		$current_viewers = array();
		foreach ($rows  as $row){
			$current_viewers [$row['FID_USER']] =$row['FID_USER'];
		}
	
	//	Create array to return:
		$current_user = $this->user();
		
		$return = array();
		foreach ($users as $user){
			if ((int)$user['ID'] !== (int)$current_user['ID']){
				$name = array();
				if (strlen(trim($user['firstName'])) > 0){ $name[] = trim($user['firstName']);}
				if (strlen(trim($user['midName'])) > 0){ $name[] = trim($user['midName']);}
				if (strlen(trim($user['lastName'])) > 0){ $name[] = trim($user['lastName']);}
				$name = implode(' ', $name);
				
				$record 			= array();
				$record['ID'] 		= $user['ID'] ;
				$record['name']		= $name !=''? $name.'&nbsp;('.$user['email'].')' :$user['email'];
				$record['check']	= array_key_exists($user['ID'], $current_viewers)? true: false;
				
				$return[] = $record;
			}
		}
		return $return;
	
	}
	
	/**
	 * Update the shared report sets:
	 * @param integer $ID
	 * @param array  user IDs with report_set is shared.
	 * @return boolean and msg is set.
	 */
	public function update_shared_report($ID, $user_ids = array()){
		return $this->update_shared_recordset('rep',$ID, $user_ids);
	}
	/**
	 * Update the shared observation sets:
	 * @param integer $ID
	 * @param array  user IDs with observation_set is shared.
	 * @return boolean and msg is set.
	 */
	public function update_shared_observation($ID, $user_ids = array()){
		return $this->update_shared_recordset('obs',$ID, $user_ids);
	}
	
	private function update_shared_recordset($ext,$ID, $user_ids=array() ){
		
	//  Check ownership and open ar of table
		$table = $this->check_recordset_owner_ship($ext, $ID);
		if ($table === false ) {return false;}
		
	//	Delete all shared:
		$sql = 'DELETE FROM `user_'.$ext.'_sha` WHERE `FID_RECORD`="'.$ID.'"';
		$check = $this->db->query($sql);
		if ($check == false) {
			$this->msg= TXT_ERR_UNKNOWN;
			return false;
		}
	
		
	//	Insert new records:
		$sql = 'INSERT INTO `user_'.$ext.'_sha` (`FID_RECORD`, `FID_USER`) VALUES ';
	
	 	$glue='';
	 	foreach ( $user_ids as  $USER_ID ) {
	 	
	 		$sql .= $glue.' ("'.$ID.'","'.$USER_ID.'")';
	 		$glue = ',';
	 			
	 	}
	 	$check = $this->db->query($sql);
	 	if ($check == false) {
	 		$this->msg = TXT_ERR_UNKNOWN;
	 	} else {
	 		$this->msg = TXT_DB_SUCCESS;
	 	}
	 	return $check;
	
	}
	/**
	 * Checks if current user is owner of record indicated by ID , creates 
	 * a table instance and set arctive record to ID
	 * @param string $ext: rep or obs
	 * @param string $ID
	 * @return table instance with active record set to $ID  | false
	 */
	private function check_recordset_owner_ship($ext,$ID= null){
		
	//	Check ID given:
		$ID = (int)$ID;
		if ($ID <1) {
			$this->msg  = TXT_ERR_UNKNOWN;
			return false;
		}
		
	//	Check user loged in:
		$user = $this->user();
		$FID_USER = array_key_exists('ID', $user) ? (int)$user['ID'] : -1;
		if ($FID_USER  <1) {
			$this->msg = TXT_LOGIN;
			return false;
		}
		
	//	Table instance:
		$table = new table('user_'.$ext);
		
	//	Set active_record:
		$aR = $table->ar($ID);
		if ( !array_key_exists('FID_USER', $aR) ){
			$this->msg  = TXT_ERR_UNKNOWN;
			return false;
		}
		
	//	Check  owner_ship:
		if ( $aR['FID_USER']  != $FID_USER){
			$this->msg  = TXT_ERR_UNKNOWN;
			return false;
		}
		return $table;
		
	}
		
} // END class ModelUser
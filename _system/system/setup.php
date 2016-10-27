<?php //_system/setup
/**
 * 	Setup script: should be removed before after installation.
 */
 

	



class setup Extends common {
	
	public function run($email){
		

		//	Stop in case table `salt` excists:
			$sql = 'SELECT *  FROM `information_schema`.`tables` WHERE `table_schema` = "'.DB_NAME.'" AND `table_name` = "salt" LIMIT 1';
			$check = $this->select($sql);
			if (count($check) > 0 ) {return false;}
			
		//	Stop in case table `user` excists:
			$sql = 'SELECT *  FROM `information_schema`.`tables` WHERE `table_schema` = "'.DB_NAME.'" AND `table_name` = "user" LIMIT 1';
			$check = $this->select($sql);
			if (count($check) > 0 ) {return false;}	
		
		//	Stage 0: reset.
			if ($email == '') {
				if (isset($_SESSION['setup'])) { unset($_SESSION['setup']); }
			}
		
		//	Stage 1: ask for owner account.
			if (!isset($_SESSION['setup']) ) {
				$_SESSION['setup'] = array();
				$_SESSION['msg'] = "Setup: enter  email and password for owner account";
				return true;
			}
		 
		//	Stage 2: ask for verification.
			if (! array_key_exists('verify', $_SESSION['setup']) ) {
				$_SESSION['setup'] ['email']  	= $email;
				$_SESSION['setup'] ['password']	= isset($_POST['password'])? $_POST['password']: '';
				$_SESSION['setup'] ['verify'] = true;
				$_SESSION['msg'] = "Repeat password for owner account";
				return true;
			} else {
					
				if ( $_SESSION['setup'] ['password'] !== $_POST['password'] ) {
						
					$_SESSION['setup'] ['email']  	= $email;
					$_SESSION['setup'] ['password'] = $_POST['password'];
					$_SESSION['setup'] ['verify'] = true;
					$_SESSION['msg'] = "Passwords do not match, repeat password past entered.";
					return true;
				}
					
			}
		
		//	Start setup:
			$email = $_SESSION['setup'] ['email'];
			$password = $_SESSION['setup'] ['password'];
			unset($_SESSION['setup']);
			
			
			
		//	Create table `salt`:
			$sql = ' CREATE TABLE `salt` ( `ID` int(11) NOT NULL, `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
			$result = $this->db->query($sql);
			if ( $result === false ) {
				$_SESSION['msg'] = 'Setup failed';
				return true;
			}
			$sql = ' ALTER TABLE `salt` ADD PRIMARY KEY (`ID`)';
			$result = $this->db->query($sql);
			if ( $result === false ) {
				$_SESSION['msg'] = 'Setup failed';
				return true;
			}
			$sql = ' ALTER TABLE `salt` MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1';
			$result = $this->db->query($sql);
			if ( $result === false ) {
				$_SESSION['msg'] = 'Setup failed';
				return true;
			}
			
		
		//	Insert salt records:
			$tblSalt = new table('salt');
			for ($ID = 1; $ID <= 3; $ID++) {
				$fields = array();
				$fields['ID'] = $ID;
				$fields['salt'] = enigma_salt();
				$tblSalt->ar_insert($fields);
			}
			
		//	Update first salt:	
			$tblSalt->ar(1);
			$fields = array();
			$fields['salt'] = enigma_salt();
			$tblSalt->ar_update($fields);
			
			$this->addLog('setupSTAR: create user',1);
			
		//	Create table `user`:
			$sql = '	CREATE TABLE `user` (
						`ID` int(11) NOT NULL,
						  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
						  `firstName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
						  `lastName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
						  `midName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
						  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
						  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
						  `lastLogin` timestamp NULL DEFAULT NULL,
						  `role` enum("10","100","1000") COLLATE utf8_unicode_ci NOT NULL DEFAULT "10",
						  `active` int(11) NOT NULL DEFAULT "-1",
						  `code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
						  `updateRequest` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `new` int(11) NOT NULL DEFAULT "1",
						  `showName` int(11) NOT NULL DEFAULT "1"
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
					
			$result = $this->db->query($sql);
			if ( $result === false ) {
				$_SESSION['msg'] = 'Setup failed1';
				return true;
			}
			$sql = ' ALTER TABLE `user` ADD PRIMARY KEY (`ID`)';
			$result = $this->db->query($sql);
			if ( $result === false ) {
				$_SESSION['msg'] = 'Setup failed2';
				return true;
			}
			$sql = ' ALTER TABLE `user` MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1';
			$result = $this->db->query($sql);
			if ( $result === false ) {
				$_SESSION['msg'] = 'Setup failed3';
				return true;
			}		
				
		//	Insert owner account:
			$tblUser = new table('user');
			$fields = array();
			$fields['ID'] = 1;
			$fields['email'] = $this->validate ($email,'EMAIL');
			if ( $fields['email'] === false ) {
				$_SESSION['msg'] = 'Setup failed';
				return true;
			}
			$fields['salt'] = enigma_salt();
			$fields['password'] = enigma_crypt($password,$fields['salt'] );
			$fields['role'] = 1000;
			$fields['active'] =1;
			$fields['new'] =-1;
			$tblUser->ar_insert($fields);
				
			
			
			
			
			$_SESSION['msg'] = 'owner-account is created, remove "setup.php" from system directory.';
			
			return true;
	

		
	}
		
}



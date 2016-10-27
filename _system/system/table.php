<?php	//	_system/system/table.php	
			defined('BONJOUR') or die;
				
class	table  extends common {

//	Properties:
	private		$name;								// Name of the table in database
	private		$columns = array();					// Fields of table name=>type.
	private		$parent_child_indices = array();	// Field names that can be used as PARENT_CHILD index.
	private 	$parent_child_sql_select 	= "";	// SELECT for PARENT_CHILD selections
	private 	$parent_child_orderby 		= "";	// ORDER BY clause for PARENT_CHILD selections
	private 	$parent_child_where 		= "";	// WHERE clause for PARENT_CHILD selections
	
	private		$pk;								// Name of primary key.
	private 	$qryAll;							// SELECT ALL
	private 	$arPk = null;						// Value of active record's primary key.
	private 	$arRecord	= array();				// Active record.
	public 		$last_sql_error = null;				// Is set on error at inzet, delete or update of active record in cas of an error
	
				
	/**
	 *
	 * Creates an instance of the class "table", an extention of class "common".
	 *
	 * @param    string $name name of the table as used in database.
	 * @return   object table or false in case primary-key is not an auto-increment integer.
	 *
	 */
		public	function __construct($name) {
			
	
			//	Set name of the table:
				$this->name =trim($name);
				
			//	Add common functionality:
				parent::__construct();
				
				if (!$this->excists()) {
					$this->addLog($this->name.' does not excists',1);
					return false;
				}
				
			//	Read fields of table:	
				$records = $this->select('DESCRIBE '.$this->name);
				foreach ($records as $record){
					
					//	Load column-name:
						$this->columns[$record['Field']]=$record['Type'];
						
					//	Set primary key:
						if ( $record['Key']==='PRI' ){
							
							if ($record['Extra'] !== 'auto_increment' ) {
								$this->addLog(__FILE__.': '.__FUNCTION__.'('.$this->name.') failed: primary key is not auto increment',1);
								return false;
							}
							
							$this->pk=$record['Field'];
						}
						
					//	Set WHERE clause for select to limit to active APP
						if ($record['Field'] === "APP") {
							$this->app = ' `APP`="'.APP.'" ';
						}
				}
				
			//	Read allowed PARENT_CHILD indices:
				$KEYS = $this->select('SHOW KEYS FROM `'.DB_NAME.'`.`'.$this->name.'`');
			
				$Key_names = array();
				foreach ($KEYS as $KEY) {
					if ($KEY['Column_name'] === 'PARENT_ID' ) {
						$Key_names[$KEY['Key_name']] = $KEY['Key_name'];
					}
				}
				foreach ($KEYS as $KEY) {
					if ($KEY['Column_name'] !== 'PARENT_ID' && array_key_exists($KEY['Key_name'], $Key_names )  ) {
						$this->parent_child_indices[$KEY['Column_name']] = $KEY['Column_name'];
					}
				}
				
				
			//	Create selaect all:
				$this->qryAll = 'SELECT `'.implode('`,`',array_keys($this->columns)).'` FROM `'.$this->name.'` ';

			//	Check primary key:
				if ($this->columns[$this->pk] !== 'int(11)') {
					$this->addLog(__FILE__.': '.__FUNCTION__.'('.$this->name.') ` failed: primary key is not an integer',1);
					return false; 
				}				
				
			
		} 	//	END __construct()
	
// 	Methods:

	public function excists(){
	//	Check if table excist:
		$check = $this->select('SELECT COUNT(`TABLE_NAME`) AS `nb` FROM information_schema.tables WHERE table_schema = "'.DB_NAME.'" AND table_name = "'.$this->name.'" LIMIT 1');
		if ($check === false ) { return false;}
		if ((int)$check[0]['nb'] !== 1 ) { return false;}
		return true;
	}

	private function where($where_clause = ''){
		
		if ( array_key_exists("APP", $this->columns)) {
				return $where_clause !== ''?	' WHERE `'.$this->name.'`.`APP`="'.APP.'" AND ('.$where_clause. ')' 	: ' WHERE `'.$this->name.'`.`APP`="'.APP.'" ';
			} else {
				return $where_clause !== ''? 	' WHERE '.$where_clause : '';
		}
	}
	

	
	private function create_parent_child( $structure = array() , $parentID = -1, $options = array() ){
		
		
		//	Get records for this PARENT_ID:
			if ( $this->parent_child_where === '') {
				$sql =  $this->parent_child_sql_select.' WHERE `'.$this->name.'`.`ID`>0 AND `'.$this->name.'`.`PARENT_ID`='.$parentID.$this->parent_child_orderby;
			} else {
				$sql =  $this->parent_child_sql_select.$this->parent_child_where.' AND `'.$this->name.'`.`ID`>0 AND `'.$this->name.'`.`PARENT_ID`='.$parentID.$this->parent_child_orderby;
			}		
			$records = $this->select($sql);
			
		//	Stop at fail or at moment no children are found:
			if (count($records) == 0 ) { return false; }
			
		//	Read records:
			foreach ($records as $row ){
				
					$result[$row['INDX']] 		= $row;
					$result[$row['INDX']]['ID'] = $row['ID'];
			
				//	Add children by recursive call:
					$children = $this->create_parent_child( $row ,$row['ID'], $options);
					if ($children !== false) {$result[$row['INDX']]['children'] = $children; };
					
				//	Add records from satelite tables:
					if ( array_key_exists('satelites',$options) ) {
						
						
						foreach ($options['satelites'] AS $table => $props) {
							
							if (array_key_exists('FID', $props)) {
								
								$ID 	= array_key_exists('ID', $props) ? $props['ID'] : 'ID';
								$FID 	= $props['FID'];
								$fields = array_key_exists('fields', $props) ? is_array($props['fields'])? $props['fields']: array() : array();
								$satelite_options = array_key_exists('options', $props) ? is_array($props['options'])? $props['options']: array() : array();
								
								$satelite = new table($table);
								if (array_key_exists('where', $satelite_options)) {
									$satelite_options['where'] = ' `'.$FID.'` = "'.$row[$ID].'" AND '.$satelite_options['where'].' ';
								} else {
								
									$satelite_options['where'] = ' `'.$FID.'` = "'.$row[$ID].'" ';
								}
								$satelite_options['transpose'] =  array_key_exists('transpose', $satelite_options)? $satelite_options['transpose'] : false;
								
								$AS =  array_key_exists('AS', $satelite_options)? $satelite_options['AS'] : $table;
								
								$result[$row['INDX']][$AS] = $satelite->select_fields($fields,$satelite_options);
								
							
							} else { 
								$this->addLog(__FILE__.': '.__FUNCTION__.'('.$this->name.') ` warning: satelite '.$table.' can`t be read: FID field is not defnied.',1000);
							}	
						}
					}
					
			}
			
		//	Return nested result.
			return $result;
		
	}
	/**
	 * For tables with a PARENT_ID column, a nested array with incices is created
	 * @param string $index: name of index field (must be unique in combination with PARENT_ID field. by default "ID" is used
	 * @param array $options	"where":   WHERE CLAUSE;
	 *           				"orderby": ORDER BY CLAUSE;
	 */
	public function select_parent_child($index = 'ID', $options=array()){
		
		//	Check if table contains a PARENT_ID field:
			if ( !array_key_exists('PARENT_ID', $this->columns) ) {
				$this->addLog(__FILE__.': '.__FUNCTION__.'('.$this->name.') ` failed: table doesn`t contain the PARENT_ID field.',100);
				return array();
			}
			
		//	Handle index and options:
			$index 						= array_key_exists($index, $this->parent_child_indices)? 		$index								: 'ID';
			$this->parent_child_where 	= array_key_exists('where', $options)?							$this->where($options['where'])		: $this->where();
			$orderby 					= array_key_exists('orderby', $options)?  						$options['orderby'] 				: '';
			$PARENT_ID					= array_key_exists('PARENT_ID', $options)?  					$options['PARENT_ID'] 				: -1;
			
			$fields 					= array();
			$fields['main'] 			= array_key_exists('fields', $options)? 						is_array($options['fields'] )? 	$options['fields']: array()	: array();
			$fields['joins'] 			= array();
			
		//	Build joins
		    $joins= '';
			if (array_key_exists('joins', $options) ){
				
				foreach ($options['joins'] as $table => $props) {
					if (array_key_exists('FID', $props)) {
						$ID 	= array_key_exists('ID', $props) ? $props['ID'] : 'ID';
						$FID 	= $props['FID'];
						$type 	= array_key_exists('type', $props) ? $props['type'] : 'LEFT';
						$AS 	= array_key_exists('AS', $props) ? $props['AS'] : $table;
						
						$joins .= ' '.$type.' JOIN `'.$table.'` AS `'.$AS.'` ON (`'.$this->name.'`.`'.$ID.'` = `'.$table.'`.`'.$FID.'`) ';
						
						if ( array_key_exists('fields', $props) ) { $fields['joins'][$AS]= $props['fields'];}
						
					} else { $this->addLog(__FILE__.': '.__FUNCTION__.'('.$this->name.') ` warning: table '.$table.' can`t be joined: FID field is not defnied.',1000);}
					
				}
			}
			
	
			
		//	Build ORDER BY:
			$ORDER_BY  =  array_key_exists('position', $this->columns) ? ' `'.$this->name.'`.`position`' : '';
			$glue      =  $ORDER_BY !== '' ? ' ,' : '';
			$ORDER_BY .=  array_key_exists('name', $this->columns) ? $glue.'`'.$this->name.'`.`name`' : '';
			$glue      =  $ORDER_BY !== '' ? ' ,' : '';
			
			if ( $orderby === '') {
				$this->parent_child_orderby = $ORDER_BY !== '' ? ' ORDER BY '.$ORDER_BY : '';
			} else {
				$this->parent_child_orderby = ' ORDER BY '.$orderby.$ORDER_BY;
			}
			
			
		//	Build SELECT:
			if (count($fields['joins']) == 0 ) {
				$select_fields	= count($fields['main'])>0? ' ,`'.implode('`,`', $fields['main']).'` ' : '';
			} else {
				$select_fields	= count($fields['main'])>0? ' ,`'.$this->name.'`.`'.implode('`,`'.$this->name.'`.`', $fields['main']).'` ' : '';
				foreach ($fields['joins'] as $join_name => $join_fields) {
					
					foreach ($join_fields as $join_field) {
						$select_fields	.= ' ,`'.$join_name.'`.`'.$join_field.'` AS `'.$join_name.'_'.$join_field.'`';
					}
					
				}
			}
			$this->parent_child_sql_select = 'SELECT DISTINCT `'.$this->name.'`.`ID`,`'.$this->name.'`.`'.$index.'` AS `INDX` '.$select_fields.'FROM `'.$this->name.'` '.$joins;
			
		
		//	Start creation of parent_child selection:
			$records = $this->create_parent_child(array(), $PARENT_ID, $options);
			return $records !== false? $records : array();
		
	}

	
	/**
	 *
	 * Runs a SELECT query on the table object.
	 *
	 * @param    (optional) array with options:
	 * 			 "where":   WHERE CLAUSE;
	 *           "orderby": ORDER BY CLAUSE;
	 *            "limit";
	 *            "offset";
	 *           "transpose": if true an array by columns is returned.
	 * @return   Array with all (filtered) records of table. In case of a failure of no match an empty array is returned.
	 *
	 */
		public function select_all($options=array()){
			
			//	Handle options options:
				$WHERE 		= 	array_key_exists('where', $options)?		$this->where($options['where'])		: $this->where();
				$ORDER_BY 	= 	array_key_exists('orderby', $options)? 		' ORDER BY '.$options['orderby']	: '';
				$LIMIT      = '';
				if (array_key_exists('limit', $options) ) {
					$LIMIT  = array_key_exists('offset', $options ) ? ' LIMIT '.(int)$options['offset'].', ' : ' LIMIT 0, ' ;
					$LIMIT .= (int)$options['limit'];
				}
				
				$transpose  =  	array_key_exists('transpose', $options)?  	$options['transepose']				: false;
	
				
			//	Execute SQL:
				return $this->select($this->qryAll.$WHERE.$ORDER_BY.$LIMIT, $transpose );
				
		}
	
	/**
	 *
	 * Counts number of rows of the table.
	 *
	 * @param    (optional) array with options:
	 * 			 "where":   WHERE CLAUSE;
	 * @return   integer number of rows. In case of a failure 0 is retruned.
	 *
	 */
		public function count_all($options=array()){
		
			//	Handle options options:
				$WHERE 		= 	array_key_exists('where', $options)?		$this->where($options['where'])		: $this->where();
				
			//	Execute SQL:
				$count = $this->select('SELECT count(`'.$this->pk.'`) AS `nb` FROM `'.$this->name.'`'.$WHERE);
				if (count($count) === 0 ) {return 0;}
				return (int)$count[0]['nb'];
		}
	
	/**
	 *
	 * Runs a SELECT query on the table object.
	 * 
	 * @param    fields array with fieldnames to select:
	 * @param    (optional) array with options:
	 * 			 "where":   WHERE CLAUSE;
	 *           "orderby": ORDER BY CLAUSE;
	 *            "limit";
	 *            "offset";
	 *            "transpose" default "true";
	 * @return   Array field=>records with all (filtered) records of table. In case of a failure of no match an empty array is returned.
	 *
	 */
		public function select_fields($fields,$options=array()){
		
			//	Handle options options:
				$WHERE 		= 	array_key_exists('where', $options)?		$this->where($options['where'])		: $this->where();
				$ORDER_BY 	= 	array_key_exists('orderby', $options)? 		' ORDER BY '.$options['orderby']	: '';
				$transpose  = array_key_exists('transpose', $options)? 		$options['transpose']	: true;
				
				$LIMIT      = '';
				if (array_key_exists('limit', $options) ) {
					$LIMIT  = array_key_exists('offset', $options ) ? ' LIMIT '.(int)$options['offset'].', ' : ' LIMIT 0, ' ;
					$LIMIT .= (int)$options['limit'];
				}
				
				$sql = 'SELECT `'.implode('`,`',$fields).'` FROM `'.$this->name.'`'.$WHERE.$ORDER_BY.$LIMIT;
				
			//	Execute SQL:
				return $this->select($sql,$transpose);
		
		}

	/**
	 *
	 * Loads an active record by primary-key.
	 *
	 * @param    $pk: value of the primary-key. In case no primary-key value is passed, the content of the last loaded active records is returned.
	 *
	 * @return   Array with all fields of active record.  In case
	 * 			 the primary key value is not found an empty array is returned.
	 *
	 */
		public function ar($pk=""){
		
			if ($pk ==="") {return $this->arRecord;}
			$this->arPk =$pk;
			
		
		//	Execute SQL:
			$records = $this->select($this->qryAll.' WHERE `'.$this->pk.'`="'.$this->arPk.'"');
			
			if (count($records)!== 1) {
				$this->arRecord= array();
				return $this->arRecord;
			}
			
			$record = $records[0];
			if (array_key_exists('content', $record)){$record['content'] = htmlspecialchars_decode($record['content'], ENT_HTML5); }
			if (array_key_exists('description', $record)){$record['description'] = htmlspecialchars_decode($record['description'], ENT_HTML5); }
			if (array_key_exists('name', $record)){$record['name'] = htmlspecialchars_decode($record['name'], ENT_HTML5); }
			
			$this->arRecord = $record;
			return $this->arRecord;
		}
		
	/**
	 *
	 * Gives primary-key value of last active record.
	 *
	 * @return Last record's primary-key value.
	 *
	 */
		public function ar_pk(){
			return $this->arPk;
		}
		
	/**
	 *
	 * Reset current active record; primary key is set to "null" and active record array is cleared.
	 *
	 * @return Last record's primary-key value.
	 *
	 */
		public function ar_reset(){
			$this->arPk 	= null;
			$this->arRecord = array();
			return;
		}
		
	/**
	 *
	 * Updates the active record.
	 * @param array $fields: name=>value pairs of fields to be updated.
	 * @return boolean true / false.
	 *
	 */
		public function ar_update($fields) {
			
			
			//	Remove  APP field to prevent cross application updates:
				if ( array_key_exists('APP', $fields)) {
					unset($fields['APP']);
				}
				
			//	Convert special characters:
				if (array_key_exists('content', $fields) ){
					$fields['content']  =  htmlspecialchars($fields['content'], ENT_HTML5, "UTF-8");
				}
				if (array_key_exists('name', $fields) ){
					$fields['name']  =  htmlspecialchars($fields['name'], ENT_COMPAT, "UTF-8");
				}
				if (array_key_exists('description', $fields) ){
					$fields['description']  =  htmlspecialchars($fields['description'], ENT_HTML5, "UTF-8");
					$fields['description']  =  trim($fields['description']) == ''?  'NULL': trim($fields['description']) ;
				}
			
		
			//	Check if active record is set:
				if ($this->arPk === null ) {
					$this->addLog(__FILE__.': '.__FUNCTION__.'() Update table `'.$this->name.'` failed: primary key is not set',1);
					return false;
				}
			
			//	Build UPDATE query of active record:
				$sql = 'UPDATE `'.$this->name.'` SET '; 
				
				$glue = '';
				foreach ($fields as $key=>$value) {
					
					if ( is_null($value) || strtoupper(trim($value)) =="NULL") {
						$sql .= $glue.'`'.$key.'`=NULL';
					} else {
						$sql .= $glue.'`'.$key.'`="'.$this->db->real_escape_string($value).'"';
					}
					
					$glue = ', ';
				}
				$sql .= ' WHERE `'.$this->pk.'`="'.$this->arPk.'"'; 
				
			//	Add query to logging:
				$this->addLog($sql,100);
				
			//	Execute query:	
				$result = $this->db->query($sql);
				if ($result === false ) {
					$this->addLog(__FILE__.': '.__FUNCTION__.'() Update table `'.$this->name.'` failed, error"'. $this->db->error,1);
					$this->last_sql_error = $this->db->error;
					return false;
				}
				
			//	Reload active record:
				$this->arRecord = $this->ar($this->arPk);
				return true;
				
		} // END ar_update()
		
		
	/**
	 *
	 * Insert a new record and set active record to this last inserted record.
	 * @param array $fields: name=>value pairs of fields to be updated.
	 * @return boolean true / false.
	 *
	 */
	
		public function ar_insert($fields) {
			
			//	Set APP field (overwite input to prevent cross application inserts:
				if ( array_key_exists('APP', $this->columns)) {
					$fields['APP']  = APP;
				}
				
			//	Convert special characters:
				if (array_key_exists('content', $fields) ){
					$fields['content']  =  htmlspecialchars($fields['content'], ENT_HTML5, "UTF-8");
				}
				if (array_key_exists('name', $fields) ){
					$fields['name']  =  htmlspecialchars($fields['name'], ENT_COMPAT, "UTF-8");
				}
				if (array_key_exists('description', $fields) ){
					$fields['description']  =  htmlspecialchars($fields['description'], ENT_HTML5, "UTF-8");
				}
				
			
			//	Build INSERT query
				$glue = '';
				$values = '';
				$columns = '';
				foreach ($fields as $key=>$value) {
					$columns .=$glue.'`'.$key.'`';
					
					if ( is_null($value) || strtoupper(trim($value)) =="NULL") {
						$values .= $glue.'NULL';
					} else {
						$values .= $glue.'"'.$this->db->real_escape_string($value).'"';
					}
						
					$glue = ', ';
				}
				
				$sql = 'INSERT INTO `'.$this->name.'` ('.$columns.') VALUES('.$values.')'; 
			
			//	Add query to logging:
				$this->addLog($sql,100);
				
			//	Run query.
				$result = $this->db->query($sql);
				
			//	Check result:
				if ($result === false) {
					$this->addLog(__FILE__.': '.__FUNCTION__.'() Insert table `'.$this->name.'` failed, error"'. $this->db->error,1);
					$this->last_sql_error = $this->db->error;
					return false;
				}
					
			//	Read the inserted ID	
				$this->arPk =  $this->db->insert_id;
				
			//	Add record in _app satelite	
				
			// 	Load inserted record as active record	
				$this->arRecord = $this->ar($this->arPk);	
				return true;
			
		} // END ar_insert()
		
	/**
	 *
	 * Deletes the active rerord.
	 * @return boolean true / false.
	 *
	 */
	public function ar_delete() {
		
	
		
		//	Build DELETE query (limit to active application)
			if ( array_key_exists('APP', $this->columns)) {
				$sql = 'DELETE FROM `'.$this->name.'` WHERE `'.$this->pk.'`="'.$this->arPk. '" AND `APP`="'.APP.'" LIMIT 1'; 
			} else {
				$sql = 'DELETE FROM `'.$this->name.'` WHERE `'.$this->pk.'`="'.$this->arPk. '" LIMIT 1'; 
			}
			
		//	Add query to logging:
			$this->addLog($sql,100);
			
		//	Run query:	
			$result = $this->db->query($sql);
			
		//	Check result:
			if ($result === false) {
				$this->addLog(__FILE__.': '.__FUNCTION__.'() Delete AR from table `'.$this->name.'` failed, error"'. $this->db->error,1);
				$this->last_sql_error = $this->db->error;
				return false;
			}
				
		//	Reset active record.	
			$this->arPk  =  null;
			$this->arRecord = array();
			return true;	
		
	} // END delete()
	
	/**
	 * Fills the field `position` with numbers 100,200, 300...
	 * for a certain PARENT_ID or FID_CAT
	 * @param integer $FID: PARENT_ID or FID_CAT 
	 * @return boolean 
	 */
	 public function positions($FID=-1){

	//	Read FID field: stop in case table doesn't have a FID field:
		$FID_FIELD = null;
		if (array_key_exists('PARENT_ID', $this->columns)) {$FID_FIELD= 'PARENT_ID'; }
		if (array_key_exists('FID_CAT', $this->columns)) {$FID_FIELD= 'FID_CAT'; }
		if ($FID_FIELD === null) { return false;}
		
	
	//	Get ID's in correct order:
		$options= array();
		$options['where'] = ' `'.$FID_FIELD.'`="'.$FID.'"';
		$options['orderby'] = '`position`, `name`';
		$IDs = $this->select_fields(['ID'], $options);
		if ($IDs === false) { return false;}
		
	//	Update the position-field:
		$fields = array();
		$fields['position'] = 0;
		foreach ($IDs['ID'] as $ID){
			$this->ar($ID);
			$fields['position'] +=100; 
			$this->ar_update($fields);
		}
		return true;
		
	}
	
	/**
	 * Returns the ID and position of the last record.
	 * for a certain PARENT_ID or FID_CAT
	 * @param array | false
	 */
	public function positions_first($FID=-1){
	
	//	Read FID field: stop in case table doesn't have a FID field:
		$FID_FIELD = null;
		if (array_key_exists('PARENT_ID', $this->columns)) {$FID_FIELD= 'PARENT_ID'; }
		if (array_key_exists('FID_CAT', $this->columns)) {$FID_FIELD= 'FID_CAT'; }
		if ($FID_FIELD === null) { return false;}
	
	
	//	Get first:
		$options= array();
		$options['where'] = ' `'.$FID_FIELD.'`="'.$FID.'"';
		$options['orderby'] = '`position`, `name`';
		$options['limit'] = 1;
		$options['transpose'] = false;
		
		$records = $this->select_fields(['ID','position'], $options);
		if ($records === false) { return false;}
		if ( count($records ) != 1 ) {return false;}

		return $records[0];
	
	}
	
	/**
	 * Returns the ID and position of the last record.
	 * for a certain PARENT_ID or FID_CAT
	 * @param array | false
	 */
	public function positions_last($FID=-1){
	
	//	Read FID field: stop in case table doesn't have a FID field:
		$FID_FIELD = null;
		if (array_key_exists('PARENT_ID', $this->columns)) {$FID_FIELD= 'PARENT_ID'; }
		if (array_key_exists('FID_CAT', $this->columns)) {$FID_FIELD= 'FID_CAT'; }
		if ($FID_FIELD === null) { return false;}
	
	
	//	Get last:
		$options= array();
		$options['where'] = ' `'.$FID_FIELD.'`="'.$FID.'"';
		$options['orderby'] = '`position` DESC, `name` DESC';
		$options['limit'] = 1;
		$options['transpose'] = false;
	
		$records = $this->select_fields(['ID','position'], $options);
		if ($records === false) { return false;}
		if ( count($records ) != 1 ) {return false;}
	
		return $records[0];
	
	}
	
	public function positions_get_position($position_id = null, $FID = null ){
		
	//	Read parmeters
		if ($FID === null)  {return false;}
		$FID = (int)$FID;
		if ($position_id === null)  {return false;}
		$position_id =  trim(strtolower($position_id)) === "last"? "last": (int)$position_id;
		if ($position_id === "last" ) {
			$last = $this->positions_last($FID);
			if ($last == false) { return false;}
			return (int)$last['position']+100;
			
		} else {
			
		//	Normalize positions:
			$this->positions($FID);
				
		//	Set postion
			if ($position_id > 0) {
				$record = $this->ar($position_id);
				if ($record == false) { return false;}
				if (!array_key_exists('position', $record) ){ return false;}
				return $record['position'] - 10;
			}
			
		}
		
		
	
	}
	
	
	
	
}  // END class table
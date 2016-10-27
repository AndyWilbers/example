<?php	//	_system/system/model.php
			defined('BONJOUR') or die;
			
		

class	model extends  common {
	
	protected $active_table 		= null;
	protected $active_table_name 	= '';
	public    $msg = '';
	protected $return_path			= null;
	
//	Properties for models with category-record structure----------------------------------------------------------------:
	public  $categories  		= null;
	private $category_id 		= null; // current category_id (search trough published ony) for front-end
	private $record_id   		= null; // current record_id (search trough published ony) for front-end
	private $active_categories  = null; // is's of active categories.
	private $category_url		= '';
	private $category_ar		= null;
	private $category_structure = null;
    private $categories_records	= null;
    public  $ar_category_new    = array();
    protected $root_path 		= null;
	private $structure 			= null;
   
    
    
    
    
    
    public function __construct() {
    	
    	$this->name = $this->my_name();
    	
    	$this->ar_category_new['ID'] 		= "new";
    	$this->ar_category_new['name'] 		= "";
    	$this->ar_category_new['path'] 		= "";
    	$this->ar_category_new['PARENT_ID'] = -1;
    	$this->ar_category_new['publish'] 	= -1;
    	
    	parent::__construct();
    	
  
    	return;
    }
	
    /**
     *  Gets the active record from this model.
     *  A child can extend this method to include data from satelite tables
     *  
      * @param   ID: ID of the record to set active 
      * 		
	 *  @return   Fields of the active record.  
     */
     public function get_ar($ID =null){
     		if ($ID === null) {return array();}
     		$ID = (int)$ID;
     		if ($ID <1 ) { return array(); }
     		return $this->ar($ID);
        	
     }
    /**
     *  An INSERT or UPDATE action on model's active table.
     *  A child can extend this method to update data in satelite tables
     *  
     *  @param fields: array with name=> value pairs of fields to update or insert
     *         ID:     in case of an update give an excisting record ID,
     *                 for an INSERT do not set ID or set to null (default).
     *  @return array with fields of (new) artive records or false.
     */
     public function save_ar($fields = array(), $ID = null) {
     	
     //	Stop when $fields is not an array wiht at least one field
     	if ( !is_array($fields) ) { return false; }
     	if ( count($fields)<1   ) { return false; }
     
     //	When $ID is null: INSERT a new record in active table
     	if ($ID === null) { return $this->ar_insert($fields); } 
     
     // Check ID and set active record:
     	$ID = (int)$ID;
     	if ($ID <1 ) { return false;}
     	$ar = $this->ar($ID);
     	if (!array_key_exists('ID', $ar)) { return false; }
     	if ((int)$ar['ID']  !== $ID) { return false; }
     	return $this->ar_update($fields);
     	
     }
     /**
      *  A DELETE action on model's active table.
      *  A child can extend this method to delete data in satelite tables
      *
      *   @param  ID:   give an excisting record ID
      *   @return true | false
      */
     public function delete_ar ($ID = null) {
     	
     	$ID = (int)$ID;
     	if ($ID <1 ) { return false;}
     	$ar = $this->ar($ID);
     	if (!array_key_exists('ID', $ar)) { return false; }
     	if ((int)$ar['ID']  !== $ID) { return false; }
     	return $this->ar_delete();
     	
     }
     
	/**
	 * Set table object to $this->active_table
	 * @param (string)$name: name of table to set.
	 * @return table object.
	 */
		protected function active_table_set($name) {
			$this->active_table_name = $name;
			return $this->active_table = new table($name);
		}
		
	/**
	 * On active table:
	 * Runs a SELECT query on the active object.
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
		public function active_table_select_all($options= array()){
			return $this->active_table->select_all($options);
		}
	
	/**
	 * On active table:
	 * Counts number of rows of the table.
	 *
	 * @param    (optional) array with options:
	 * 			 "where":   WHERE CLAUSE;
	 * @return   integer number of rows. In case of a failure 0 is retruned.
	 *
	 */
		public function active_table_count_all($options=array()){
			return $this->active_table->count_all($options);
		}
	
		
	/**
	 * On active table:
	 * Runs a SELECT query on the table object.
	 *
	 * @param    fields array with fieldnames to select:
	 * @param    (optional) array with options:
	 * 			 "where":   WHERE CLAUSE;
	 *           "orderby": ORDER BY CLAUSE;
	 *            "limit";
	 *            "offset";
	 * @return   Array field=>records with all (filtered) records of table. In case of a failure of no match an empty array is returned.
	 *
	 */
		public function active_table_select_fields($fields, $options= array()){
			return $this->active_table->select_fields($fields, $options);
		}
		
	/**
	 * On active table:
	 * For tables with a PARENT_ID column, a nested array with incices is created
	 * @param string $index: name of index field (must be unique in combination with PARENT_ID field. by default "ID" is used
	 * @param array $options	"where":   WHERE CLAUSE;
	 *           				"orderby": ORDER BY CLAUSE;
	 */
		public function active_table_select_parent_child($index = 'ID', $options=array()){
			return $this->active_table->select_parent_child($index,$options);
		}
		
	/**
	 * On active table:
	 * Loads an active record by primary-key.
	 *
	 * @param    $pk: value of the primary-key. In case no primary-key value is passed, the content of the last loaded active records is returned.
	 *
	 * @return   Array with all fields of active record.  In case
	 * 			 the primary key value is not found an empty array is returned.
	 *
	 */
		public function ar($pk=""){
			return $this->active_table->ar($pk);
		}
		
	/**
	 * On active table:
	 * Gives primary-key value of last active record.
	 *
	 * @return Last record's primary-key value.
	 *
	 */
		public function ar_pk(){
			return $this->active_table->ar_pk();
		}
	
	/**
	 * On active table:
	 * Reset current active record; primary key is set to "null" and active record array is cleared.
	 *
	 * @return Last record's primary-key value.
	 *
	 */
		public function ar_reset(){
			return $this->active_table->ar_reset();
		}
	
	/**
	 * On active table:
	 * Updates the active record.
	 * @param array $fields: name=>value pairs of fields to be updated.
	 * @return boolean true / false.
	 *
	 */
		public function ar_update($fields) {	
			return $this->active_table->ar_update($fields);
		}
		
	public function last_sql_error(){
		return $this->active_table->last_sql_error;
	}
	
	public function ar_positions($FID=-1) {
		return $this->active_table->positions($FID);
	}
	public function ar_positions_first($FID=-1) {
		return $this->active_table->positions_first($FID);
	}
	public function ar_positions_last($FID=-1) {
		return $this->active_table->positions_last($FID);
	}
	public function ar_positions_get_position($position_id = null, $FID = null ){
		return $this->active_table->positions_get_position($position_id, $FID);
	}
	
	/**
	 * On active table:
	 * Insert a new record and set active record to this last inserted record.
	 * @param array $fields: name=>value pairs of fields to be updated.
	 * @return boolean true / false.
	 *
	 */
		public function ar_insert($fields) {	
			return $this->active_table->ar_insert($fields);	
		}
	
	/**
	 * On active table:
	 * Deletes the active rerord.
	 * @return boolean true / false.
	 *
	 */
		public function ar_delete() {
			return $this->active_table->ar_delete();
		}
	
//		Methods for models with category-record structure----------------------------------------------------------------:

		public function get_records_without_category($published_only = true){
			return $this->get_records_by_category(-1, $published_only);
		}
		
		public function get_records_by_category($ID = -1, $published_only = true){
			
			$ID = (int)$ID;
			$ID = $ID>0? $ID: -1;
		
			$fields					= array('ID','name','publish','position');
			$options 				= array();
			$options['transpose']	= false;
			$options['where']		= $published_only? ' `publish`="1" AND `FID_CAT`="'.$ID.'" ' : ' `FID_CAT`="'.$ID.'" ';
			$options['orderby']		= ' `position`, `name` ';
			return $this->active_table->select_fields($fields, $options);
		
		}
		
		/**
		 * Gives an array with [value]=>[name] for records for a certain category
		 * @param integer $FID_CAT: categrory ID default: -1
		 * @param boolean $published_only default true
		 * @return array
		 */
		public function get_options_record($FID_CAT = -1, $published_only = true){
			$records = $this->get_records_by_category($FID_CAT, $published_only);
			$options = array();
			foreach ($records as $record) {
				$options[$record['ID']] = $record['name'];
			}
			return $options;
		}
		
		public function get_records_by_category_all_fields($FID_CAT = -1, $published_only = true){
			
		//	Sanatize $FID_CAT:	
			$FID_CAT = (int)$FID_CAT;
			$FID_CAT = $FID_CAT>0? $FID_CAT: -1;
			
		//	Check is category is published:
			if ($published_only && $FID_CAT>0) {
				$category = $this->categories->ar($FID_CAT);
				if ((int)$category['publish'] != 1 ){
					return array();
				}
			}
		
		//	Get records
			$options 				= array();
			$options['where']		= $published_only? ' `publish`="1" AND `FID_CAT`="'.$FID_CAT.'" ' : ' `FID_CAT`="'.$FID_CAT.'" ';
			$options['orderby']		= ' `position`, `name` ';
			return $this->active_table->select_all($options);
		
		}
		
		public function get_categories_by_parent($ID = -1, $published_only = true){
				
			$ID = (int)$ID;
			$ID = $ID>0? $ID: -1;
		
			$fields					= array('ID','name', 'path','publish','position');
			$options 				= array();
			$options['transpose']	= false;
			$options['where']		= $published_only? ' `publish`="1" AND `PARENT_ID`="'.$ID.'" ' : ' `PARENT_ID`="'.$ID.'" ';
			$options['orderby']		= ' `position`, `name` ';
			return $this->categories->select_fields($fields, $options);
		
		}
		
		/**
		 * Gives an array with [value]=>[name] for categories for a certain parent category
		 * @param integer $FID_PARENT: categrory ID default: -1
		 * @param boolean $published_only default true
		 * @return array
		 */
		public function get_options_cat($FID_PARENT = -1, $published_only = true){
			$records = $this->get_categories_by_parent($FID_PARENT, $published_only);
			$options = array();
			foreach ($records as $record) {
				$options[$record['ID']] = $record['name'];
			}
			return $options;
		}
		
		/**
		 * Gives an array with [FID_CAT] :
		 *                     [structure_cat] => category structure
		 * 		               [records]       => {[value]=>[name]}  
		 * for a certain record ID.
		 * @param integer $ID: record ID default: -1
		 * @param boolean $published_only default true
		 * @return array
		 */
		public function get_options($ID = -1, $published_only = true){
			
			$published_only  = boolval($published_only);
			$ID  		= (int)$ID > 0? (int)$ID: -1;
			$FID_CAT 	= -1;
			if ($ID >0 ){
				$aR = $this->ar($ID);
				$FID_CAT = (int)$aR['FID_CAT'] >0? (int)$aR['FID_CAT']: -1;
				
			}
			$return = array();
			$return['FID_CAT']        = $FID_CAT;
			$return['structure_cat']  = $this->get_category_structure(-1,$published_only);
			$return['records']        = $this->get_options_record($FID_CAT,$published_only);
			return $return;
			
		}
		
		public function get_categories($PARENT_ID = null){
		
		//	Get parent-child structure:
			$options 							= array();
			$options['fields'] 					= array('name', 'path','publish','position');
			if ($PARENT_ID === null)	{
				$options['PARENT_ID'] = $this->get_category_id_admin();
			} else {
				$options['PARENT_ID'] = (int)$PARENT_ID>0? (int)$PARENT_ID: -1;
			}
			$options['satelites']  				= array();
			$satelite 							= array();
			$satelite ['FID']					= 'FID_CAT';
			$satelite ['fields']				= array('ID','name', 'path','publish','position','FID_CAT');
			$satelite ['options']				= array();
			$satelite ['options']['orderby']	= ' `position`, `name` ';
			$satelite ['options']['AS']			= 'records';
				
			$options['satelites'][$this->active_table_name] 	= $satelite;
			return $this->categories->select_parent_child('ID',$options);
		
		}
		
		public function get_category_structure() {
			if ($this->category_structure === null) { return $this->load_category_structure();}
			return $this->category_structure;
		}
		
		private function load_category_structure() {
			
			$options 							= array();
			$options['fields'] 					= array('name', 'path','publish','position');
			$options['PARENT_ID']               = -1;
			$this->category_structure =  $this->categories->select_parent_child('ID',$options);
			return $this->category_structure;
		}
		
		public function get_category_id(){
			if ($this->category_id === null) { $this->category_id = $this->get_category_id_by_route();}
			return $this->category_id;
		}
		
		public function get_category_id_admin(){
			
			if ($this->category_id === null) { $this->category_id = $this->get_category_id_by_route(false);}
			return $this->category_id;
		}
		
		public function get_active_categories(){
			if ($this->category_id === null) { $this->category_id = $this->get_category_id_by_route();}
			return $this->active_categories;
		}
		
		public function get_active_categories_admin(){
			if ($this->category_id === null) { $this->category_id = $this->get_category_id_by_route(false);}
			return $this->active_categories;
		}
		
		public function get_category_url(){
			if ($this->category_id === null) { $this->category_id = $this->get_category_id_by_route();}
			return $this->category_url;
		}
		
		public function get_category_url_admin(){
			if ($this->category_id === null) { $this->category_id = $this->get_category_id_by_route(false);}
			return $this->category_url;
		}
		
		public function get_category_id_by_route($publish = true) {
			
		
			$request  			= $this->request;
			$this->category_url	= '';
			$start				= false;
			$path 				= current($request);
			
			$PARENT_ID  		= -1;
			while ( $path !== false ){
				if ($start) {
					$fields  = array('ID');
					$options = array();
					$options['limit'] = 1;
					$options['transpose'] = false;
					if ($publish) {
						$options['where'] = ' `PARENT_ID` ="'.$PARENT_ID.'" AND `path`="'.$path.'" AND `publish` ="1" ';
					} else {
						$options['where'] = ' `PARENT_ID` ="'.$PARENT_ID.'" AND `path`="'.$path.'" ';
					}
					$record = $this->categories->select_fields($fields, $options);
					if ($record === false) { return false; }
					
					if (count($record) !== 1 ){ return false; }
					$PARENT_ID  = $record[0]['ID'];
					if ($this->active_categories === null) { $this->active_categories = array(); }
					$this->active_categories[] = $PARENT_ID;
					$this->category_url .= $path.'/';
						
				} else { $path === $this->root_path? $start = true: false;}
				$path = next($request);
			}
			return $PARENT_ID;
		}
		
		public function get_category_by_id($FID_CAT){
			
			$FID_CAT = (int)$FID_CAT;
			if ($FID_CAT<1) {return array();}
			
			$category_table_name = $this->active_table_name.'_cat';
			
			$table = new table($category_table_name);
			if ($table  === false ) {return array();}
			
			
			
			return $table->ar($FID_CAT);
			
			
		}
		
		public function get_name_with_path($ID, $glue ='-'){
		
		//	Check $ID:
			$ID = (int)$ID;
			if ($ID<1) {return '';}
			
		//	Get record:
			$aR = $this->ar($ID);
			if (!array_key_exists('name', $aR) ) { return '';}
			
			$name = $aR['name'];
			$aR['PARENT_ID'] =$aR['FID_CAT'];
			while ( (int)$aR['PARENT_ID']>0 ){
				$aR = $this->get_category_by_id($aR['PARENT_ID']);
				if (array_key_exists('name', $aR) ){
					$name = $aR['name'].$glue.$name;
				}
			}
			return $name;
			
		}
		
		public function get_category_ar(){
			if ($this->category_ar === null ) {$this->load_category_ar();}
			return $this->category_ar;
		}
		
		public function get_category_ar_admin(){
			if ($this->category_ar === null ) {$this->load_category_ar(true);}
			return $this->category_ar;
		}
		
		private function load_category_ar($admin = false){
			if ($admin) {
				if ($this->category_id === null) { $this->category_id = $this->get_category_id_by_route(false);}
			} else {
				if ($this->category_id === null) { $this->category_id = $this->get_category_id_by_route(true);}
			}
			$ar = array();
			if ($this->category_id !== false) {
				$ar = $this->categories->ar($this->category_id);
				$parent = array();
				if (array_key_exists('PARENT_ID', $ar) && $ar['PARENT_ID'] >0 ) {
					$options 			= array();
					$options['limit'] 	= 1;
					$options['where'] 	= ' `ID`="'.$ar['PARENT_ID'].'" ';
					$parent = $this->categories->select_all($options);
					$parent = $parent[0];
				}
				$ar['parent'] = $parent;
			}
			$this->category_ar = $ar;
			return $this->category_ar;
		}
		
		private function load_categories_records(){
			if ( $this->categories_records === null) {
				
				$rows = $this->categories->select_all();
				
				
				$records = array();
				foreach ($rows as $row){
					$this->addLog($row['ID'],1);
					$records[(int)$row['ID']]= $row;
				}
				$this->categories_records = $records;
				
			}
			return $this->categories_records;
		}
		
		/**
		 * 
		 * Gives the path to the encapsuling category
		 * @param ID: ID of the active category.
		 * @param record: false | true: default "false" indicating the path to the parent-category will be returned.
		 *        When the "path" should lead to the category of the ID self (in case of an record-form) set this
		 *        parmter to "true" 
		 * @return string path
		 */
		public function get_category_path_by_id($ID = null, $record = false){
			
			//	Check and sanatize ID:
				if ($ID === null)  { return '';}
				$ID = (int)$ID;
				if ($ID < 1 ) { return '';}
				
			//	Get all category records:
				$cat_records = $this->load_categories_records();
				if (!array_key_exists($ID, $cat_records) ){ return '';}
				
			    $parent_id 	= $record? $ID :  $cat_records[$ID]['PARENT_ID'];
			    $path = '';
			    while ( (int)$parent_id > 0){
			    	$path 		 = $cat_records[$parent_id]['path'].'/'.$path;
			    	$parent_id 	 = $cat_records[$parent_id]['PARENT_ID'];
			    }
			    return $path;
			
		}
		/**
		 *
		 * Gives the parent categories of a record starting with the category on root level.
		 * @param ID: ID an record.
		 *
		 * @return array
		 */
		public function get_parent_categories_by_id($ID = null){
			

			
		//	Check and sanatize ID:
			if ($ID === null)  { return array();}
			$ID = (int)$ID;
			if ($ID < 1 ) { return array();}
			
			$aR = $this->get_ar($ID);
			$parent_id = $aR['FID_CAT'];
			if ($parent_id < 1 ) { return array();}
				
			
		//	Get all category records:
			$cat_records = $this->load_categories_records();
			if (!array_key_exists($parent_id, $cat_records) ){ return array();}
			
		
			$return = array();
			while ( (int)$parent_id > 0){
				array_unshift($return, $cat_records[$parent_id]);
				$parent_id 	 = $cat_records[$parent_id]['PARENT_ID'];
			}
			return $return;
		}

		/**
		 * Create the category structure path.
		 * @param integer ID of a record.
		 * 		  boolean add_id: add ?id=ID to result or not, default true.
		 * @return array aR of active_table ONLY with add field href: string path according to the category-structure. 
		 *         By failure: an empty array()
		 */
		public function get_ar_with_path_to_record($ID,$add_id= true){
		
		//	Check ID:	
			$ID = (int)$ID;
			if ($ID <1) return array();
			
		//	Get record:
			$aR = $this->active_table->ar($ID);	
			if ( !array_key_exists('FID_CAT', $aR) ) { array();}
			
		//	Get and return path:
			$path = $this->get_category_path_by_id($aR['FID_CAT'], true);
			$aR['href'] = $add_id? $path.'?id='.$ID : $path;
			return $aR;
			
		
			
		}
		
		/**
		 * Create full path href with the category structure path to record (with ?id)
		 * @param integer ID of a record.
		 * @return string href
		 *         By failure: false
		 */
		public function get_ar_href($ID) {
			$aR = $this->get_ar_with_path_to_record($ID, true);
			return  array_key_exists('href', $aR)? $this->return_path.$aR['href'] : false;
		}
		
		public function get_top_category($PARENT_ID = -1){
			$options = array();
			$options['where'] 		= '`PARENT_ID`="'.$PARENT_ID.'" AND `top`="1"';
			$options['transpose']  	= false;
			$options['limit']  	= 1;
			$record = $this->categories->select_fields(['ID'],$options);
			if ($record === false) { return false; }
			if (count($record) !== 1) { return false;}
			return (int)$record[0]['ID'];
		}

		
		public function csv_import($csv = array(), $table = null, $execute = false){
			
			$result = array();
			$result['error'] = true;
			
		//	Check if $csv is an array and contains at least two rows:
			if ( is_array($csv) === false) {
				$this->addLog('$csv is not an array.',1);
				$this->msg= '$csv is not an array.';
				return $result;
			}
			if (count($csv) < 2) {
				$this->addLog('$csv does not contain rows.',1);
				$this->msg= '$csv does not contain rows.';
				return $result;
			}
			
		//	Check if $table is the name of an excisting table for this model:
			if ($table === null) {
				$this->addLog('$table is not set at all.',1);
				$this->msg= '$table is not set at all.';
				return $result;
			}
			$sql = 'SHOW TABLES IN `'.DB_NAME.'` LIKE "'.$table.'"';
			$results = $this->select($sql);
			if (count($results) !== 1) {
				$this->addLog('$table is not found in database',1);
				$this->msg= '$table is not found in database';
				return $result;
			}
			
		//	Get all fields of target table: name=>type
			$target 		= array();
			$primary_key	= array(); 
			$records = $this->select('DESCRIBE '.$table);
			foreach ($records as $record){
				$target[$record['Field']]=$record['Type'];
				if ( $record['Key']==='PRI' ){
					// By default: false: at read of $csv's first row it will be set to true in case found.
					$primary_key[$record['Field']] = false;
					$target[$record['Field']]=$record['Type'].' PK';
				}
			}
			$result['target']       = $target; 
			$result['primary_key'] 	= $primary_key;
			
		//	Read first row of $csv: map field-name to column-number and look for primary key:
			$fields = array();
			$csv_fields  = array_shift ($csv);
			$indx = 0;
			foreach ($csv_fields as $name){
				if (array_key_exists($name, $target) ){
					$fields[$name] = $indx;
					if (array_key_exists($name, $primary_key) ){
						$primary_key[$name] = true;
					}
				}
				$indx++;
			}
			$result['field'] 	= $fields;
			if (count($fields) == 0) {
				$this->addLog('$csv does not contain matching columns',1);
				$this->msg= '$csv does not contain matching columns';
				return $result;
			}
			foreach ($primary_key as $pk) {
				if ($pk == false) {
					$this->addLog('$csv does not contain all required primary key columns',1);
					$this->msg= '$csv does not contain all required primary key columns';
				    return $result;
				}
			}
			
		//	IMPORT LOOP:
			$count 						= array();
			$count['total'] 			= array();
			$count['insert'] 			= array();
			$count['update'] 			= array();
			$count['total']['all'] 		= 0;
			$count['total']['fail'] 	= 0;
			$count['insert']['all'] 	= 0;
			$count['insert']['fail'] 	= 0;
			$count['update']['all'] 	= 0;
			$count['update']['fail'] 	= 0;
			
			$import = array();
			$skip = array();
			$missing = array();
			$log = array();
			foreach ($csv as $row=>$cols_raw){
				
				
				$count['total']['all']++;
				
			//	Select columns for import:
				$cols = array();
			    foreach ($fields as $name=>$indx) {
			    	if ( !array_key_exists($indx, $cols_raw)){
			    		$missing[] = ($row+1).':'. $name.' ('.$indx.')';
			    	} else {
			    		$cols[$name] = $cols_raw[$indx];
			    	}
			    	
			    }
			    $skip_this_row = false;
			    if (count($cols) == count($fields)) {
			    	if (array_key_exists('APP', $target)) {
			    		$cols['APP'] = APP;
			    	}
			        $c = array();
			        $c['row'] = $row+1;
			        $import[$row+1] = array_merge($c, $cols);
			    } else {
			    	$skip_this_row = true;
			        $c = array();
			        $c['row'] = $row+1;
			        $skip[$row+1] = array_merge($c, $cols);
			    }
				
			//	Convert special characters:
				if (array_key_exists('content', $cols) ){
					$cols['content']  =  htmlspecialchars($cols['content'], ENT_HTML5, "UTF-8");
				}
				if (array_key_exists('name', $cols) ){
					$cols['name']  =  htmlspecialchars($cols['name'], ENT_COMPAT, "UTF-8");
				}
				if (array_key_exists('description', $cols) ){
					$cols['description']  =  htmlspecialchars($cols['description'], ENT_COMPAT, "UTF-8");
				}
				
			//	Check by primary key if record already excist in table; determine: INSERT | UPDATE
				$select 		= 'SELECT';
				$glue_select	= ' ';
				
				// @WHERE_CLAUSE_PRIMARY_KEY:
				$where  		= 'WHERE'; //This WHERE clause wil also be used for UPDATE.
				$glue_where   	= ' ';
				foreach ($primary_key as $name=>$val){
					
					$select 		.= $glue_select.'`'.$name.'`';
					$glue_select	 = ',';
					
					$where .= $glue_where.'`'.$name.'`="'.$cols[$name].'"';
					$glue_where = ' AND ';
				}
				$sql = $select.' FROM `'.$table.'` '.$where.' LIMIT 1';
				$records = $this->select($sql);
				
			//	INSERT | UPDATE
				if (count($records) == 0) {
					
				//	INSERT:
					$import[$row+1]['action'] ='INSERT';
					$count['insert']['all']++;
					
					if ($execute) {
						$glue = '';
						$values = '';
						$columns = '';
						foreach ($cols as $key=>$value) {
							$columns .=$glue.'`'.$key.'`';
							if ( strtoupper($value) === "NULL" ) {
								$values .= $glue.'NULL';
							} else {
								$values .= $glue.'"'.$this->db->real_escape_string($value).'"';
							}
							$glue = ', ';
						}
						$sql = 'INSERT INTO `'.$table.'` ('.$columns.') VALUES('.$values.')';
			
						//	Add query to logging:
							$this->addLog($sql,100);
					
						//	Run query:
							$result_query = $this->db->query($sql);	
							if ($result_query === false) {
								
								$count['total']['fail']++;
								$count['insert']['fail']++;
								$this->addLog('$csv INSERT row'.($row+1).' failed ERROR:'.$this->db->error, 1);
								$R 			= array();
								$R['row'] 	= $row+1;
								$M			= array();
								$M['msg'] 	= $this->db->error;
								$log[]	= array_merge($R,$cols,$M);
								
							}
					}		
					
				} else{
					
				//	UPDATE:
					$import[$row+1]['action'] ='UPDATE';
					$count['update']['all'] ++;
				
					if ($execute) {
						$glue = '';
						$sql = 'UPDATE `'.$table.'` SET ';
						foreach ($cols as $key=>$value) {
							if ( array_key_exists($key, $primary_key) == false) {
								if ( strtoupper($value) === "NULL" ) {
									$values .= $glue.'NULL';
									$sql .= $glue.'`'.$key.'`= NULL';
								} else {
									$sql .= $glue.'`'.$key.'`="'.$this->db->real_escape_string($value).'"';
								}
								$glue = ', ';
							}
						}
						$sql .= ' '.$where; // where-clause on primary key (see @WHERE_CLAUSE_PRIMARY_KEY).
						
						//	Add query to logging:
						    $this->addLog($sql,100);
						
						//	Run query:
						    $result_query = $this->db->query($sql);
							if ($result_query === false ) {
								$this->addLog('$csv UPDATE row'.($row+1).' failed ERROR:'.$this->db->error, 1);
								$count['total']['fail']++;
								$count['update']['fail']++;
								$R 			= array();
								$R['row'] 	= $row+1;
								$M			= array();
								$M['msg'] 	= $this->db->error;
								$log[]	= array_merge($R,$cols,$M);
							}
					}
				
				}// END INSERT | UPDATE
			}// END IMPORT LOOP
			
		
			$result['import']    = $import;
			$result['missing']   = $missing;
			$result['skip']      = $skip;
			$result['log'] 		= $log;
			$result['error'] 	= false;
			$result['count'] = $count;
		    return $result;
			
		}

		/**
		 * Gives an array with child-categories and records,
		 * based on the last part of the requested path.
		 * Only published items are returned.
		 * 
		 * @param request: 	array part of the request related to the model.
		 *                  When omitted the top level is returned.
		 * 
		 * @return Array => {
		 * 						["children"] => { 'name', 'path','publish','position'}
		 *  	   				["records"]  =>  { 'ID','name', 'path','publish','position','FID_CAT' }
		 *  				}
		 * 
		*/
		public function fe_menu($request = null, $category_has_introduction = true){
		
		//	Setup menu:
			$menu 				= array();
			$menu['children']  	= array();
			$menu['records']  	= array();
			$menu['fields']  	= array();
			
		//	Check parameter:
			if ( $request !== null){
				if ( !is_array($request)) { return $menu;}
				$request = count($request) > 0 ? $request : null;
			}
		
		//	Get parent-child structure:
			$options 							= array();
			if ($category_has_introduction === true){
				$options['fields'] 					= array('name', 'path','publish','position','FID_ARTICLE_CAT','FID_ARTICLE');
			} else {
				$options['fields'] 					= array('name', 'path','publish','position');
			}
			$options['PARENT_ID'] 				= -1;
			
			$options['satelites']  				= array();
			$satelite 							= array();
			$satelite ['FID']					= 'FID_CAT';
			$satelite ['fields']				= array('ID','name', 'path','publish','position','FID_CAT');
			$satelite ['options']				= array();
			$satelite ['options']['orderby']	= ' `position`, `name` ';
			$satelite ['options']['AS']			= 'records';
		
			$options['satelites'][$this->active_table_name] 	= $satelite;
			if ( $this->structure == null){
				$this->structure =  $this->categories->select_parent_child('path',$options);
			}
			$structure = $this->structure;
			
		//	Top level: return categories only:
			if ($request === null){
				foreach($structure as $cat){
					if ($cat['publish'] == 1){
						//unset($cat['records']);
						//unset($cat['children']);
						$menu['children'][] = $cat;
					}
				}
				return $menu;
			}
		//	$menu["structure"] = $structure;
			
		//	Drill down to requested path:
			foreach ($request as $path){
				if ( array_key_exists($path, $structure) ){
				//	On Top level look 'path' is in keys"
					$structure = $structure[$path];
				
					
				} else {
				//	Other levels 'path' is in children:
						if ( !array_key_exists('children', $structure) ) { break; }
						if ( array_key_exists($path, $structure['children']) ) { 
							$structure = $structure['children'][$path];
						}
				}
			}
			
		//	Remove children en records of children and return:	
			if ( array_key_exists('children', $structure) ){
				unset ($structure['children']['records']);
				if ( array_key_exists('children', $structure['children']) ){
					unset ($structure['children']['children']); 
				}
			}
			
		//	Create menu array and return:
			if ( array_key_exists('children', $structure) ){ 
				$menu['children'] = $structure['children']; 
				unset($structure['children']);
			}
			if ( array_key_exists('records', $structure) ) { 
				$menu['records']  = $structure['records']; 
				unset( $structure['records']);
			}
			$menu['fields'] = $structure;
			return $menu;
		
		}
		
		public function fe_get_category_id_by_route($route = null) {
			
		   $route = is_array($route)? $route :	$this->request;
			
		//  Create lookup list key: $ID.$path:	
			$options 			= array();
			$options['where'] 	= ' `publish` ="1" ';
			$all_categories 	= $this->categories->select_all($options);
			$categories = array();
			foreach ($all_categories as $cat){
				$key = (int)$cat['PARENT_ID']>0? (int)$cat['PARENT_ID'].$cat['path'] :$cat['path'];
				$categories[$key] = $cat;
			}
			
		//	Find PARENT_ID:	
			$parent_id='';
			foreach ($route as $path){
				$key =$parent_id.$path;
				$parent_id = array_key_exists($key, $categories)? (int)$categories[$key]['ID'] : false;
				if ($parent_id === false){ break;}
			}
			return $parent_id;
			
		}
}
<?php	//	_system/models/route.php
			defined('BONJOUR') or die;		
			
class	modelRoute extends  model {
	
	private $route_to_controller 		= null;
	private $routes						= null;
	private $menus						= null;
	private $menu_structure				= null;
	public  $crumbs						= array();
	private $active_routes				= array();
	public  $category_names 			= array();
	
	
	public $debug =array();
	
	/**
	 * Model on table `route`.
	 */
	public function __construct() {
	
			parent::__construct();
			
		//	Set active table to "route":
			$this->active_table_set('route');
			
		//	Get all routes on scope:
			$options = array();
			
			$scope =  APPLICATION === ''? 'ROOT' :'APP';
			$options['where'] = '`scope` = "'.$scope.'"';		
			$options['orderby'] = 'CHAR_LENGTH(`path`) DESC';
			
			$routes = $this->active_table->select_all($options);
			foreach ($routes as $route){
				$this->routes[$route['path']] = $route;
			}
			
			
		//	Get route to controller:
			$route  = reset($routes);
			do {
				$result = str_starts(strtolower(trim($route['path'])).'/', '/'.ROUTE.'/') ? true : false;
				$this_route = $route;
				$route = next($routes);
			} while ($result === false && $route !== false );
			
			
			//	Stop if no match could be made:
				if ($result === false ) { return; }
			
			// 	Check is path is disabled (for scope: "APP" only):
				if ($scope  === 'APP' ) {
					if ( $this->is_route_available( $this_route['ID']) === false) { return; }
				}
				$this->route_to_controller = $this_route;
				
			//	Start of crumb:
				if (APPLICATION != ''){
					$crumb= array();
					$crumb['name'] = TXT_LBL_HOME;
					$crumb['path'] = HOME;
					$this->crumbs[] = $crumb;
					
					$crumb['name'] = APPLICATION;
					$crumb['path'] = PATH_;
					$this->crumbs[] = $crumb;
					
				}
				if (APPLICATION == '' && $this->request[0]  != ''){
					$crumb= array();
					$crumb['name'] = TXT_LBL_HOME;
					$crumb['path'] = HOME;
					$this->crumbs[] = $crumb;
				}
				
			
				if ($this->request[0]  == '') {return;}
				

			//	Get paths that are disabled (on menu):
				$routes_disabled = array();
				$sql = 'SELECT `ROUTE_ID` FROM `menu_disable` WHERE `APP` = "'.APP.'"';
				$disable = $this->select($sql);
				$strIN = "";
				$glue = '';
				foreach ($disable as $row){
					$strIN .=$glue.$row['ROUTE_ID'];
					$glue = ', ';
				}
				$routes_disabled_raw = array();
				if ($strIN !== ""){
					$sql = 'SELECT `path` FROM `route` WHERE `ID` IN('.$strIN.')';
					$routes_disabled_raw = $this->select($sql);
				}
				$routes_disabled = array();
				foreach ($routes_disabled_raw as $row){
					$routes_disabled[$row['path']] = $row['path'];
				}
				
		    //  Complete crumb:
				$category_names  	= null;
				$table 				= null;
				$path_start 		= PATH_;
				$path 				= '';
				$glue               = '';
				$key 				= '';
				$glue_key           = '';
				foreach ($this->request as $part){
					
					$path.= $glue.$part;
					$name = str_replace('_', ' ', $part);
					$glue ='/';
					
					if ($category_names !== null) {
						$key.= $glue_key.$part;
						$glue_key ='/';
					}
					
				//	Read $category_names table at first match:	
					if ($category_names === null){
						
						switch ($part){
							
						case   'articles':
						case   'inhoud':
							    $table = 'article';
								$category_names 	= $this->crumbs_lookup_category_name($table);
								$this->category_names = $category_names;
								$key.= $glue_key.$part;
								$glue_key ='/';
							    break;
									
						case   'observaties':
						case   'observation':
							    $table = 'observation';
								$category_names 	= $this->crumbs_lookup_category_name($table);
								$this->category_names = $category_names;
							    break;
							
						case   'calculations':
						case   'calculaties':
							    $table = 'calculation';
								$category_names 	= $this->crumbs_lookup_category_name($table);
								$this->category_names = $category_names;
							    break;
						}
						
						
					} 
					
					
				//	Convert name:
					if ($category_names !== null) {
						
						$name = array_key_exists($key, $category_names)?  $category_names[$key] : $name;
					}
					
					
					$crumb			= array();
					$crumb['name']  = $name;
					$crumb['path']  = PATH_.$path;
					if (!array_key_exists('/'.$path, $routes_disabled)){
						$this->crumbs[] = $crumb;
					}
					
				}
				
			
				
				$ID = array_key_exists('id', $_REQUEST)? (int)$_REQUEST['id']: -1;
				if ($ID >0 && $table !== null){
					
					$aR 	= $this->crumbs_lookup_record($table,$ID);
					if (array_key_exists('name', $aR) ) {
						$crumb			= array();
						$crumb['name']  = $aR['name'];
						$crumb['path']  = PATH_.$path.'?id='.$ID;
						$this->crumbs[] = $crumb;
					}
					
				}
				
				
				
				return;
				
				
				
	}
	
	private function crumbs_lookup_category_name($table){
		
		
	//	Get category records:	
		$options 				= array();
		$options['transpose'] 	= false;
		$names 					= null;
		$last_crumb 			= null;
		$tbl = new table($table.'_cat');
		$rows = $tbl->select_fields(array('ID','PARENT_ID','path','name'),$options);
		
	//  Create categories_by_id:
		$categories_by_id =array();
		foreach ($rows as $row){
			$categories_by_id[$row['ID']] = $row;
		}
		
	//	Create convert array
		$names = array();
		foreach ($rows as $row){
			$PARENT_ID 	= $row['PARENT_ID'];
			$path = $row['path'];
			$name = $row['name']; 
			while ($PARENT_ID>0){
				$path =$categories_by_id[$PARENT_ID]['path'].'/'.$path;
				$PARENT_ID =$categories_by_id[$PARENT_ID]['PARENT_ID'];
			}
			$names[$path] = $name;
		}
		return $names;

	}
	
	private function crumbs_lookup_record($table, $ID = -1){
		$ID = (int)$ID;
		$tbl = new table($table);
		return $tbl->ar($ID);
		
	}
	/**
	 * Checks if route is availalbe for an application.
	 * @param integer id: ID of route
	 * @param string  app: default APP is used
	 * @return boolean | null (in case of an error);
	 */
	public function is_route_available($id, $app = APP){
		
		//	Stop in case id is not correct:
			if ( (int)$id < 1 ){ return null; }
			
		//	Get record from DB:
			$sql = '
						SELECT IF (COUNT(`APP`) = 0 , "yes","no") as `available`
						FROM `route_disable`
						WHERE `ROUTE_ID` ="'.$id.'" AND `APP` = "'.$app.'"
						LIMIT 1
				   ';
			$check = $this->select($sql);
			if ( $check === false ){ return null; }
			
		//	Retrun result:
			return $check[0]['available']==="yes"? true : false;
	}
	
	/**
	 * Gives one record from table `route` where `path` match ROUTE.
	 * A match is found when `path` equals ROUTE, controllerRouter uses
	 * this match to start up the first controller.
	 * 
	 * @return array one record form table `route`.
	 */
	public function get_route_to_controller(){
		return $this->route_to_controller;
	}
	
	
	private function load_menus() {

		
		//	Reset menus:
			$this->menus = null;
			
			$scope = APPLICATION === ''? 'ROOT'	: 'APP';
		
		//	Get menus from table `route`:
			$current 		= '/'.ROUTE;
			$current_len 	= strlen($current);
			
			$pieces = explode('/', ROUTE);
			$active_routes = array();
			$active_route = '/';
			$active_routes[] = $active_route;
			foreach ( $pieces as $piece ) {
				$active_route .= $piece.'/';
				$active_routes[] = $active_route;
			}
			$active = '"'.implode('","',$active_routes).'"';
		
			$sql = '
				 			SELECT * , 
							CAST(( LENGTH(`path`) - LENGTH( REPLACE( `path`, "/", "") )   ) / LENGTH("/") AS UNSIGNED) AS `depth`,
					
							CONCAT_WS (" ", 
										IF ( `path` = "'.$current.'" AND `scope` = "'.$scope.'",																				"current", 	NULL ),
										IF ( CONCAT(`path`,"/") IN ('.$active.') AND `scope` = "'.$scope.'", 																	"active",	NULL )		
									)  AS `class`
									    		
							FROM `route`
							WHERE `menu` IS NOT NULL
							ORDER BY 
							`scope`,`menu`, CAST(( LENGTH(`path`) - LENGTH( REPLACE( `path`, "/", "") )   ) / LENGTH("/") AS UNSIGNED),
							`position`, `name`
								
					';
			$this->addLog($sql,1000);
			$rs = $this->db->query($sql);
			if ($rs === false) {
				$this->addLog($this->db->error,100);
				return;
			}
		
		//	Build menus-array:
			$this->menus  = array();
			$scope 	= '';
			$menu 	= '';
			$depth 	= 0;
			while ($row = $rs->fetch_assoc()) {
				if ($row['scope'] !== $scope) { 
					$scope = $row['scope'];
					$menu 	= '';
					$depth 	= 0;
					$this->menus[$scope] = array();
				}
				if ($row['menu']  !== $menu) { 
					$menu = $row['menu'] ;
					$depth 	= 0;
					$this->menus[$scope][$menu] = array();
				}
				if ($row['depth']  > $depth) {
					$depth = $row['depth'] ;
					$this->menus[$scope][$menu][$depth] = array();
				}
				$this->menus[$scope][$menu][$depth][] = $row;
			}
			
		
	}
	
	public function get_menu_structure() {
		
		//	Load only in case not already availalbe:
			if ( $this->menu_structure === null) { $this->load_menu_structure(); }
			
		//	Retrun menus or empty array in case loading failed.
			return $this->menu_structure !== null? $this->menu_structure : array();
	
		
	}
	
	private function query_structure ( $scope = null, $menu= null, $depth = null, $parent_path = null) {
		
		
		// 	Parameters for current | active class:
			$current_scope = APPLICATION === ''? 'ROOT'	: 'APP';
			$current 		= '/'.ROUTE;
			$current_len 	= strlen($current);
		
			$pieces = explode('/', ROUTE);
			$active_routes = array();
			$active_route = '/';
			$active_routes[] = $active_route;
			foreach ( $pieces as $piece ) {
				$active_route .= $piece.'/';
				$active_routes[] = $active_route;
			}
			$active = '"'.implode('","',$active_routes).'"';
			
			
		//	WHERE  and ORDER BY clauses:
			$depth = (int)$depth;
			
			$where 	=  $menu  !== null  ? ' WHERE `menu` ="'.$menu.'"' 		: ' WHERE `menu` IS NOT NULL' ;
			$where .=  $scope !== null  ? ' AND `scope`= "'.$scope.'"' 		: '';
			$where .=  $depth > 0       ? ' AND CAST(( LENGTH(`path`) - LENGTH( REPLACE( `path`, "/", "") )   ) / LENGTH("/") AS UNSIGNED) ="'.$depth.'"' : '';
			if ($parent_path !== null ) {
				$len = strlen($parent_path);
				$where .= ' AND SUBSTRING(`path`,1,'.$len.') ="'.$parent_path.'"';
			}
			
			$sort = array();
			if ($scope === null ) 	{	$sort[] = '`scope`' ;}
			if ($menu  === null ) 	{	$sort[] = '`menu`'  ;}
			if ($depth < 1  )		{ 	$sort[] = 'CAST(( LENGTH(`path`) - LENGTH( REPLACE( `path`, "/", "") )   ) / LENGTH("/") AS UNSIGNED)' ;}
			$sort[] ='`position`';
			$sort[] ='`name`';
			$orderby = ' ORDER BY '.implode(',',$sort);
			
		//	SELECT records:	
			$sql = '	SELECT * ,
						CAST(( LENGTH(`path`) - LENGTH( REPLACE( `path`, "/", "") )   ) / LENGTH("/") AS UNSIGNED) AS `depth`,

						CONCAT_WS (" ",
									IF ( `path` = "'.$current.'" AND `scope` = "'.$current_scope.'",																				"current", 	NULL ),
									IF ( CONCAT(`path`,"/") IN ('.$active.') AND `scope` = "'.$current_scope.'", 																	"active",	NULL )
								)  AS `class`
			
						FROM `route`'.$where.$orderby;
			
		
			return $records = $this->select($sql);
		
		
	}
	
	private function query_menus ($scope = null) {
	
		$sql = 'SELECT DISTINCT `menu` FROM `route` WHERE `menu` IS NOT NULL';
		switch ($scope) {
			case 'APP': $sql .=' AND `scope` = "APP"'; break;
			case 'ROOT':$sql .=' AND `scope` = "ROOT"'; break;
			break;
		}
			
		$this->addLog($sql,1000);
		$rs = $this->db->query($sql);
		if ($rs === false) { $this->addLog($this->db->error,10); }
		return $rs;
	
	}
	
	
	private function load_menu_structure() {
	
	
		//	Reset menu_structure:
			$this->menu_structure  	= null;
			$menu_structure 		= array();
			
		//	Build for both scopes:
			foreach (['ROOT', 'APP'] as $scope) {	
			//	Get avaialble menus; stop in fail:	
				$rs = $this->query_menus($scope);
				if ($rs === false )  {return;}
				
				$menu_structure[$scope] = array();
				while ($row = $rs->fetch_assoc()) {
					$menu_structure[$scope][$row['menu']] = $this->load_menu_structure_(1, null, $scope, $row['menu']);
				}
			}
			
		//	Load structure:
			$this->menu_structure = $menu_structure;
			return;
		
	}
	private function load_menu_structure_($depth=1, $parent_path=null, $scope, $menu ) {
		
		//	Get records on this depth level and stop in case there are no found:
			$records = $this->query_structure($scope, $menu, $depth, $parent_path);
			if (count($records) == 0 ) { return false; }
			
		//	Walk trough records fill and find children:	
			$result = array();
			foreach ($records as $record) {
				
				$result[$record['path']] 	= $record;
				$children 					= $this->load_menu_structure_( $depth+1, $record['path'], $scope, $menu );
				if ($children !== false) {
					$result[$record['path']]['_'] = $children;
				}
			}
			return $result;
		
	}
	
	
	/**
	 * Gives all available menus in an associative array:
	 *
	 * @return array ['APP'| ROOT'] => ["menu"] => [1] => { `route`.*, class: => "" |"active"| 'current active'}
	 * 									 		=> [2] => { `route`.*, class: => "" |"active"| 'current active'}
	 * 												...
	 * 									 		=> [#] => { `route`.*, class: => "" |"active"| 'current active'}
	 * 
	 * 								=> ["menu2"] 
	 * 									...
	 * 						 		=> ["menu#"] 
	 * 		
	 */
	public function get_menus(){
		
		//	Load only in case not already availalbe:
			if ( $this->menus === null) { $this->load_menus(); }
			
		//	Retrun menus or empty array in case loading failed.
			return $this->menus !== null? $this->menus : array();
	}
	
	
	
	/**
	 * Gives those records from table `route` where `path` leads to ROUTE.
	 * These routes are the active routes.
	 *
	 * @return array with record form table `route`.
	 */
	public function get_active_routes(){
	
		$route = reset($this->routes);
		do {
			$result = str_starts(strtolower(trim($route['path'])).'/', '/'.ROUTE.'/') ? true : false;
			$current_route = $route;
			$route = next($this->routes);
		} while ($result === false && $route !== false );
		if ($result) { $this->route_to_controller = $current_route;}
	
		return $this->route_to_controller;
	
	}
	
    public function fe_applications(){
    	
    	$sql = 'SELECT `name` FROM `application` WHERE `APP` <>"gen"';
    	return $this->select($sql);
    	
    	
    }
    
    public function fe_get_menu_by_name($name='unknown'){
    	
    	
    //	Get menu-items:	
    	$sql = 'SELECT DISTINCT `menu_items`.`name`, `menu_items`.`title`, `menu_items`.`FID_ROUTE`, `menu_items`.`FID_MENU`, `route`.`path`, `counter`
                FROM `menu_items` 
                LEFT JOIN `menu` ON (`menu`.`ID` = `FID_MENU`)
                LEFT JOIN `route` ON (`route`.`ID` = `FID_ROUTE`)
                WHERE `menu`.`name`="'.trim($name).'"
                ORDER BY `menu_items`.`position`';
    	
    	$select = $this->select($sql);
    	
    	$return = array();
   
    //	Get items that are disabled (on route):
    	$sql = 'SELECT `ROUTE_ID` FROM `route_disable` WHERE `APP` = "'.APP.'"';  
    	$disable = $this->select($sql);
    	$nb = count($disable);
    	
    	if ($nb>0) {
    		$not_in= array();
    		foreach($disable as $row){
    			$not_in[]= $row['ROUTE_ID'];
    		}
    		foreach ($select as $row) {
    			if (!in_array($row['FID_ROUTE'], $not_in)){
    				$return[]=$row;
    			}
    		}
    		$select = $return;
    	}
    	
    	
    //	Get items that are disabled (on menu):
    	$sql = 'SELECT `MENU_ID`, `ROUTE_ID` FROM `menu_disable` WHERE `APP` = "'.APP.'"';
    	$disable = $this->select($sql);
    	$nb = count($disable );
    
    	if ($nb== 0 )  { return $select;}
    	$not_in= array();
    	foreach($disable as $row){
    			$not_in[]= $row['MENU_ID'].'_'.$row['ROUTE_ID'];
    	}
   
    	$return = array();
    	foreach ($select as $row) {
    		if (!in_array($row['FID_MENU'].'_'.$row['FID_ROUTE'], $not_in)){
    			$return[]=$row;
    		}
    	}
    	return $return;
    	
    	
    	
    }
    
	
	

}
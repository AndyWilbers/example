<?php	//	_system/system/contoller.php
			defined('BONJOUR') or die;
			
		

class	controller extends  common {
	
	public 	$view 	= null;					//	default view.
	protected 	$views 	= array();				//	views collection.
	public 	$model 	= null;					//  default model.
	protected 	$models = array();				// 	models collection.
	protected	$model_route		=  null;	//	Instance of modelSysyemInfo.
	protected	$model_system_info	=  null;	//	Instance of modelRoute.
	protected 	$respond_method = null;			//	Respond method
	private   	$request_data   = array();		//  data ajax-calls.
	protected   $meta_error = 0; 	
	protected   $meta_msg = '';
	public		$request_params = array(); 		// part of route that can be seen as parameters 
	public 		$html = '';						// html to respond;
	public 		$data =  array();				// data to respond;
	public 		$route_to_controller = array();	// Route-record used by controllerRouter to start de first controller.
	
	
//	Properties for views with category-record structure----------------------------------------------------------------	
	protected	$has_category_record_structure 	= false;
	protected 	$cat_structure 					= array();
	public	$ar									= null;
	protected   $model_article			        =  null;	//	Instance of modelArticle.
	
	
	
	public function __construct() {
		
		
		//	Parent constructor:
			parent::__construct();
			
		//	Get modes from GLOBALS:
			$this->model_route = $GLOBALS['model_route'];
			$this->model_system_info = $GLOBALS['model_system_info'];
			$this->model_article = $GLOBALS['model_article'];
			
			
		//	Set default view:
			$this->view = new view();
		
		//	Set current route:
			$this->route_to_controller = $this->model_route->get_route_to_controller();
		
		//	Set default meta-data:
			$this->request_data['meta'] 		 = array();
			$this->request_data['meta']['url'] 	 = URL;
			$this->request_data['meta']['path']  = PATH;
			$this->request_data['meta']['path_'] = PATH_;
			
		//	Request-parameters:
			$path = trim( ltrim(ROUTE,trim($this->route_to_controller['path'],'/') ),'/');
			if ( strlen($path) > 0 ) {
				$params = explode('/',$path);
				if (count($params) > 1) {
					$this->request_params = $params;
				} else {
					$this->request_params[] = $path;
				}
			}
			
		//	Set default respond_method to 404 page or data-error:
			
			$this->respond_method = $this->route_to_controller['request'] === 'data'? 'respond_data_error' : 'respond_404';
			
			return;
			
	}
	
	public function start_respons(){
		$response_method_name = $this->respond_method;
		$this->$response_method_name();
		return;
	}

	protected function save(){
		$this->meta_msg 	= TXT_ERR_UNKNOWN_SAVE;
		$this->meta_error 	= -99;
			
	//	Form id
		$this->data['form_id'] = isset($_POST['form_id'])? $_POST['form_id'] : 'unknown';
		$this->respond_data();
		return;
			
	}	
	
	
	/**
	 *  Opens an admin page for category record based modules
	 */
	public function crs_admin(){
		
	$this->addLog('crs_admin()',1);
		//	Handle request for new record or new category:
			if (count($this->request)> 2){
				
				if (in_array("_new",$this->request)) {
		
					
					//	Request for new record: load controller's ar with default data form model;
						$this->set_active_record($this->model->ar_new);
						return $this->crs_admin_record();
				}
				
				if (in_array("_new_category",$this->request)) {
		
		
				//	New category:
					$ar  = $this->model->ar_category_new;
					$this->view->addReplacement('ar', $ar);
		
					$ar_parent_cat = array();
					$ar_parent_cat['name'] = '';
					$ar_parent_cat['hide'] = ' class ="hide" ';
					$this->view->addReplacement('ar_parent_cat', $ar_parent_cat);
		
				//	Assign select category to view:
					$this->crs_set_select_category('PARENT_ID', $ar);
		
					$this->view->addReplacement('subforms', '');
					return $this->view->getHtml('form.category.html');
				}
			}
	
		//	Load category structure and set category_path:
			$this->cat_structure = $this->model->get_categories();
			$cat_structure 	= $this->model->get_categories();
	
		//	Get category
			$category_id = $this->model->get_category_id_admin();
	
		//	Respond index page in case category is not found:
			if ($category_id === false ) { return  $this->crs_admin_index();}
	
		//	Set cath_path:
			$this->view->cat_path = PATH_.'admin/'.$this->view->root_path.$this->model->get_category_url_admin();
	
		//	In case request contains an id: open record form:
			if (isset( $_REQUEST['id'])) {
		
			//	Set category's active record:
				if ($category_id > 0 ) {
					$cat_ar = array();
					$category_ar = $this->model->get_category_ar_admin();
					$cat_ar['name'] = $category_ar['name'];
					$cat_ar['href'] = $this->view->cat_path;
					$cat_ar['hide'] = '';
					$this->view->addReplacement('cat_ar', $cat_ar);
				}
		
			//	Open admin_record
				return $this->crs_admin_record();
			}
	
		//	Show index on top level:
			if ($category_id === -1) { return  $this->crs_admin_index();}
	
		//	Other cases: show category form:
			$this->view->setTemplate('form.category.html');
	
		//	Menu with child-categories:
			$categories = $this->model->get_categories_by_parent($category_id,false);
			$this->view->set_categories($categories);
			$this->view->addReplacement('cat_menu', $this->view->get_cat_menu());
	
		//	Menu with records:
			$records = $this->model->get_records_by_category($category_id,false);
			$this->view->set_records($records);
			$this->view->addReplacement('record_menu', $this->view->get_record_menu());
	
		//	Category's active record:
			$ar = $this->model->get_category_ar_admin();
			$this->view->addReplacement('ar', $ar);
			$class_publish = $ar['publish']==1? 'publish': 'publish not';
			$this->view->addReplacement('class_publish', $class_publish);
			
		//	Category's parent active record
			$ar_parent_cat = array();
			$ar_parent_cat['name'] = '';
			$ar_parent_cat['hide'] = ' class ="hide" ';
			$category_ar = $this->model->get_category_ar_admin();
			if ($category_ar['PARENT_ID'] >0 ) {
				$ar_parent_cat['name'] = $category_ar['parent']['name'];
				$ar_parent_cat['hide'] = '';
			}
			$this->view->addReplacement('ar_parent_cat', $ar_parent_cat);
			
		//	Select for parent category:
			$options = array();
			$options['exclude'] = $category_id;
			$options['name'] 	= 'PARENT_ID';
			$category_ar= $this->model->get_category_ar();
			$options['value'] = $category_ar['PARENT_ID'];
			$html_select_parent_category = $this->view->html_select_category($this->model->get_category_structure(),$options);
			$this->view->addReplacement('select_category',$html_select_parent_category);
			
		//	Child-category-form:
			$html_category_position_select = $this->view->get_category_position_select();
			$hide_position = $html_category_position_select == '' ? ' class="hide"' :'';
			$this->view->addReplacement('hide_position',$hide_position);
			$this->view->addReplacement('select_position',$html_category_position_select);
			$this->view->addReplacement('form_add_child_category', $this->view->getHtml('form.category.add.html'));
			
		//	Add form_category_change_positions:
			$html_form = '';
			if ( count($categories) > 1) {
				$this->view->addReplacement('tbody', $this->view->html_tbody_change_position($categories) );
				$html_form = $this->view->getHtml('form.category.change_positions.html');
			}
			$this->view->addReplacement('form_category_change_positions', $html_form);
			
		//	Record-form:
			$html_record_postion_select = $this->view->get_record_position_select();
			$hide_position = $html_record_postion_select == '' ?' class="hide"' :'';
			$this->view->addReplacement('hide_position',$hide_position);
			$this->view->addReplacement('select_position',$html_record_postion_select);
			$this->view->addReplacement('form_add_record', $this->view->getHtml('form.record.add.html'));
			
		//	Add form_record_change_positions:
			$html_form = '';
			if (count($records) > 1 ) {
				$this->view->addReplacement('tbody', $this->view->html_tbody_change_position($records) );
				$html_form = $this->view->getHtml('form.record.change_positions.html');
			}
			$this->view->addReplacement('form_record_change_positions',$html_form);
			
			$this->view->addReplacement('subforms', $this->view->getHtml('form.category.subforms.html'));
	
			return $this->view->getHtml();
	
	}
	
	/**
	 *  For category_record_structure only.
	 *  Give admin_indax page
	 * @return string html index page
	 */
	protected function crs_admin_index() {
		
		$this->view->cat_path = PATH_.'admin/'.$this->view->root_path;
	
		if (!$this->has_category_record_structure){ return '';}
	
		$no_cat = $this->model->get_records_without_category(false);
		$this->view->addReplacement('no_cat',$this->view->html_record_list($no_cat));
			
		$this->view->setTemplate('admin.index.html');
		
		//	Load complete structure
			$cat_structure = $this->model->get_categories(-1);
			
		$this->view->addReplacement('content', $this->view->html_category_record_list($cat_structure));

		$html_form = '';
		if ( count($cat_structure) > 1) {
			$this->view->addReplacement('tbody', $this->view->html_tbody_change_position($cat_structure) );
			$html_form = $this->view->getHtml('form.category.change_positions.html');
		}
		$this->view->addReplacement('form_category_change_positions', $html_form);
		return $this->view->getHtml();
	}
		
	/**
	 *  For category_record_structure only.
	 *  Get from model the full category structure.
	 *  Add replacement 'select_category to view.
	 *  @param FID: name of categry foreign-key field FID_CAT | PARENT_ID
	 *  return: html select_category.
	 */
	protected function crs_set_select_category($FID = 'FID_CAT', $ar = array()){
		$html_select_category = '';
		if ($this->has_category_record_structure){
			
		//	Load select for category:
			$options = array();
			$options['value'] = $ar[$FID];
			$options['name'] =  $FID;
			$html_select_category = $this->view->html_select_category($this->model->get_category_structure(),$options);
			$this->view->addReplacement('select_category',$html_select_category);
			
		}
		return $html_select_category;
	}
	
	/**
	 * Create empty form for new article on root level
	 */
	protected function crs_admin_record(){
		
			
		//	In case controller's active record is not set: set using request parameter "id":
			if ($this->ar === null) {
				if (isset( $_REQUEST['id'])) {
					$id = (int)$_REQUEST['id'];
					if ($id < 1) {  						return  $this->crs_admin_index(); }
					$ar = $this->model->ar($id);
					if (!array_key_exists('ID', $ar)) {  	return  $this->crs_admin_index(); }
					if ( (int)$ar['ID']  !== $id ) {  		return  $this->crs_admin_index(); }
					$this->set_active_record($ar);
				}
			}
		
	
		//	Set publish field:
			$class_publish = $this->ar['publish']==1? 'publish': 'publish not';
			$this->view->addReplacement('class_publish', $class_publish);
	
		//	Assign active-record:
			$this->view->addReplacement('ar', $this->ar);
	
		//	Assign select-category:
			$this->crs_set_select_category('FID_CAT',$this->ar);
			
		//	Assign header:
			$header = $this->view->getHtml('admin.recordheader.html');
			$this->view->addReplacement('header', $header);
			
			
			return $this->view->getHtml();
	}
	
	/**
	 *  Assigns parameter "ar" to property $this->ar in case "ar" is an array.
	 *  @param ar: array with active record.
	 *  @return property $this->ar
	 */
	public function set_active_record($ar= array()){
		if (is_array($ar)) {
			$this->ar = $ar;
		}
		return $this->ar;
	}
	
	/**
	 * Set/get meta_error number for data-requests. 
	 * @param (inter) $number
	 * @return number
	 */
	public function meta_error($number = null) {
		if ( $number !== null ){
			$this->meta_error = (int)$number;
		}
		return $this->meta_error;
	}
	
	/**
	 * Set/get message for data-requests.
	 * @param (string) msg
	 * @return string
	 */
	public function meta_msg($msg = null) {
		if ( $msg !== null ){
			$this->meta_msg =  $msg;
		}
		return $this->meta_msg;
	}
	
	/**
	 * Respond to client 404 error-page.
	 */
	public function respond_404() {
		
		$this->view = new view();
		$content = $this->view->getHtml('404.html');
		$this->view->addReplacement('content', $content);
		$this->view->addReplacement('classBody',' class="centre-600"');
		$this->view->addReplacement('crumb','');
		$this->html =  $this->view->getHtml();
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		echo $this->view->getHtml();
		exit;
	}
	/**
	 * Respond empty data to client with error message.
	 */
	public function respond_data_error() {
		$data 						= array();
		$data['meta'] 				= array();
		$data['data']				= array();
		$data['meta']['error'] 		= -99;
		$data['meta']['msg']		= TXT_ERR_UNKWOWN;
		$view = new view('404.html');
		echo json_encode($data);
		exit;
	}
	
	/**
	 * Respond to client $this->html content.
	 */
	public function respond() {
		
			if ($this->html === '') {
				$this->addLog('Empty html respond',1000);
			}
			echo $this->html;
			exit;
	}
	
	/**
	 * Respond $this->data()+ meta data to client json encoded.
	 */
	public function respond_data() {
		
			if ( count($this->data) === 0 ){ $this->addLog('Empty data respond',1000);}
		
			$this->request_data['meta']['error'] 	= $this->meta_error;
			$this->request_data['meta'] ['msg']		= $this->meta_msg;
			$this->data['meta'] 					= $this->request_data['meta'] ;
		
			echo json_encode($this->data);
			exit;
	}
			
	public function known_error($error) {
		
		$result = false;
		
		if ( strpos($error,'UNIQUE_NAME') !== false ) {
			$result = array();
			$result['field'] = "name";
			$result['msg'] 	 = TXT_VBNE_ERR_NAME_UNIQUE;
		}
		if ( strpos($error,'UNIQUE_PATH') !== false ) {
			$result = array();
			$result['field'] = "path";
			$result['msg'] 	 = TXT_VBNE_ERR_PATH_UNIQUE;
		}
		
		
		return $result;
		
	}
	
	/**
	 * Assings to the controller's view the select for 
	 * the FID_ARTICLE_CAT in the category form.
	 */
	public function set_select_article_cat() {
		
		require_once MODELS.'article.php';
		$model = new modelArticle();
		$options = $model->get_categories(-1);
		
		$ar_category = $this->model->get_category_ar_admin();
		$value = array_key_exists('FID_ARTICLE_CAT', $ar_category)?$ar_category['FID_ARTICLE_CAT']: -1;
	
		$this->view->set_select_article_cat($options, $value );
		return;
	}

	/**
	 * Assings to the controller's view the select for
	 * the FID_ARTICLE in aform.
	 */
	public function set_select_article() {
	// @todo: article selector.
	
		
		
		$ar_category = $this->model->get_category_ar_admin();
		$FID_ARTICLE_CAT	= array_key_exists('FID_ARTICLE_CAT', $ar_category)?	$ar_category['FID_ARTICLE_CAT']	: null;
		$FID_ARTICLE 		= array_key_exists('FID_ARTICLE', $ar_category)?  		$ar_category['FID_ARTICLE'] 	: null;
		$FID_ARTICLE_CAT 	= is_null($FID_ARTICLE_CAT)?							-1 								: (int)$FID_ARTICLE_CAT;
		$FID_ARTICLE 		= is_null($FID_ARTICLE)?								-1 								: (int)$FID_ARTICLE;
		

		
		$html_select_artcicle = $this->view->html_select_article($FID_ARTICLE_CAT, $FID_ARTICLE);
		$this->view->addReplacement('select_article', $html_select_artcicle);
		
	
		return;
		
	
	}
	
	
	
	
	/**
	 * Create  HTML for menu on front-end
	 * @param integer PARENT_ID, default: -1.
	 * $return HTML code
	 */
	public function fe_menu($PARENT_ID = -1){
		
		
	
	//	Get catetgory records from model and add href:
		if ( !is_integer($PARENT_ID) ) { return '';}
		$rows = $this->model->get_categories($PARENT_ID);
		$records = array();
	
	//	Top level menu:
		if ( $PARENT_ID == -1) {
			foreach ($rows as $key => $row){
				$nb_records  = count($row['records']);
				$nb_children = array_key_exists('children', $row)? count($row['children']) : 0;
				if ( $nb_records+$nb_children >0) {
					$href = $this->model->get_category_path_by_id($row['ID']);
					$row['href'] = PATH_.ROUTE.'/'.$href.$row['path'];
					$row['nb_children'] = $nb_children;
					$records[] = $row;
				}
			}
		}
	//	Other levels:
		if ( $PARENT_ID > 0) {	
			$rows = $rows[$PARENT_ID];
			return	array_pretty_print($rows);
		}
			
		
	
	
	//	Build HTML:
		if ( count($records) == 0 ){ return '';}
		$a = array();
		foreach ( $records as $row){
			$attr 			= array();
			$attr['href'] 	= $row['href'];
			$attr['target'] = '_self';
			$attr['title']  = $this->fieldReplace(TXT_VBNE_TITLE_OPEN, array('name' => strtolower($row['name'])));
			$a[]			= $this->view->wrapTag('a',$row['name'],$attr);
		}
		return $this->view->wrapTag('nav',$a,array('class' => 'menu'));
	
	}
	
	public function fe_published_records( $records = array() ){
		
	//	Check parameters:
		if ( !is_array($records) )  { return false;   }
		if ( count($records) == 0 ) { return array(); }
		
	//	Get published_records:
		$published_records = array();
		foreach ( $records as $record) {
			if ($record['publish']){
				$published_record = $this->model->ar($record['ID']);
				if ( array_key_exists('ID', $published_record) ){
					if ((int)$published_record['ID']  === (int)$record['ID'] ){
						$published_records[$record['ID']] = $published_record;
					}
				}
			}
		}	
		return $published_records;
	}

	/**
	 * AJAX response send data to client for updating the record selector.
	 * Only availalbe for controllers supporting category-record-structure. 
	 */
	public function get_record_selector(){
	
	//	Respond error when the controller doesn't support category-record-structure:
		if ( $this->has_category_record_structure === false ) {
			$this->respond_data_error();
			exit;
			return;
		}
		
	//	GET FID_CAT:
		$FID_CAT = array_key_exists('FID_CAT', $_REQUEST)?  (int)$_REQUEST['FID_CAT']:-1;
		$FID_CAT = $FID_CAT > 0? $FID_CAT : -1;
		
	//	GET title:
		$title = array_key_exists('title', $_REQUEST)? $_REQUEST['title']: TXT_LBL_EMPTY;
		
	//	GET ID:
		$ID = array_key_exists('ID', $_REQUEST)?  (int)$_REQUEST['ID']:-1;
		$ID = $ID > 0? $ID: -1;
		
	//	GET exclude
		$exclude = array_key_exists('exclude', $_REQUEST)?  $_REQUEST['exclude']:'no';	
		$exclude = ($exclude  == "yes" && $ID>0)? [$ID] : array();
		
	//	Get options
		$options = $this->model->get_options_record($FID_CAT, false);
		
		$this->data['html'] = $this->view->html_options($options, $title,-1, -1, $exclude );
	
		$this->meta_error =0;
		$this->meta_msg = TXT_OK;
		$this->respond_data();
		exit;
		return;
	}


	
	public function ajax(){
		if ( count($this->request) < 2) {
			$this->meta_error 	= -10001;
			$this->meta_msg 	= TXT_ERR_UNKNOWN;
			parent::respond_data();
			return;
		}
		$method = $this->request[2];
		if (!method_exists($this, $method)){
			$this->meta_error 	= -10002;
			$this->meta_msg 	= TXT_ERR_UNKNOWN;
			parent::respond_data();
			return;
		}
		$this->$method();
		return;
	}
	
	
	
	private function get_record_by_id(){
		$this->meta_error 	= -10;
		$this->meta_msg 	= TXT_ERR_UNKNOWN;
		
		
		$ID = array_key_exists('ID', $_GET)? (int)$_GET['ID']: -1;
		if ($ID <1 ) {
			$this->data['ar']   = array();
			$this->meta_error 	= 0;
			$this->meta_msg 	= TXT_OK;
			$this->respond_data();
			return;
		}
		
		$aR = $this->model->get_ar_with_path_to_record($ID);
		if (!array_key_exists('ID', $aR)){
			parent::respond_data();
			return;
		}
		$this->data['ar']   = $aR;
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_OK;
		$this->respond_data();
		return;
		
	}
	/**
	 * read  $_POST('ID'): in case it's not an positive integer, an error is responded to client
	 * @param string $name of ID field; default "ID"
	 * @param integer $error number; default -1;
	 * @param string $msg error messsage; default TXT_ERR_UNKNOWN 
	 * @return integer >0.
	 */
	public function sanatize_POST_ID($name='ID', $error = -1, $msg = TXT_ERR_UNKNOWN ){
		$name = trim($name);
		$ID = array_key_exists(trim($name), $_POST)? (int)$_POST[$name]: -1;
		return $this->sanatize_int($ID, $error, $msg);
	}
	/**
	 * read  $_GET('ID'): in case it's not an positive integer, an error is responded to client
	 * @param string $name of ID field; default "ID"
	 * @param integer $error number; default -1;
	 * @param string $msg error messsage; default TXT_ERR_UNKNOWN
	 * @return integer >0.
	 */
	public function sanatize_GET_ID($name, $error = -1, $msg = TXT_ERR_UNKNOWN ){
		$name = trim($name);
		$ID = array_key_exists(trim($name), $_GET)? (int)$_GET[$name]: -1;
		return $this->sanatize_int($ID,$error, $msg);
	
	}
	/**
	 * read  $_REQUEST('ID'): in case it's not an positive integer, an error is responded to client
	 * @param string $name of ID field; default "ID"
	 * @param integer $error number; default -1;
	 * @param string $msg error messsage; default TXT_ERR_UNKNOWN
	 * @return integer >0.
	 */
	public function sanatize_REQUEST_ID($name, $error = -1, $msg = TXT_ERR_UNKNOWN ){
		$name = trim($name);
		$ID = array_key_exists(trim($name), $_REQUEST)? (int)$_REQUEST[$name]: -1;
		return $this->sanatize_int($ID,$error, $msg);
	
	}
	private function sanatize_int($ID, $error, $msg){
		if ( $ID<1 ){
			$this->meta_error 	= $error;
			$this->meta_msg 	= $msg;
			parent::respond_data();
			return false;
		}
		return $ID;
	}
	
	
	public function message_assign($view = null) {
	
	//	Set view:
		if ($view == null){ $view = $this->view;}
		
	//	Read message and warning:
		$msg = array();
		$msg['warning'] 	= '';
		$msg['msg']		    = '';
		if ( isset($_SESSION['msg']) ){
			$msg['msg']	 = $_SESSION['msg'];
			unset($_SESSION['msg']);
		}
		if ( isset($_SESSION['warning']) ){
			if ($_SESSION['warning']) {
				$msg['warning'] 	= ' warning';
			}
			unset($_SESSION['warning']);
		}
	
		$view->addReplacement('warning', $msg['warning']);
		$view->addReplacement('msg', $msg['msg']);
		return $msg;
	
	}

}
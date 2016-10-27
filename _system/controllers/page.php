<?php	//	_system/controllers/page.php
			defined('BONJOUR') or die;

class controllerPage extends controller {
	
	public function __construct(){
		
			
			parent::__construct();
			
		//	Initialization
			require_once VIEWS.'fe.php';
		
		//	Create view-instances:
			$this->view = new viewFe();
			
		//	Default model: article
			$this->model = $this->model_article;
		
			$page = TXT_VBNE_PAGE_HEADER($this->request);
			$this->view->addReplacement('page', $page);
		
		//	Get and start respond_method:
			if (APPLICATION === '') {
				$respond_method = ROUTE === '' ? 'intro' : $this->request[0];
			} else {
				$respond_method = ROUTE === '' ? 'info' : $this->request[0];
			}
			
			if (in_array($this->request[0], ['account','my_reports','my_observations']) ){
				$respond_method = 'account';
			}
			
			if ($this->request[0] == 'pdf'){
				$respond_method = 'pdf';
			}
			
			if ($respond_method === 'ajax') {
				$respond_method = $this->request[1];
			}
			
		//	Set device button:	
			$device = array();
			$device['class'] = $_SESSION['device']==="mobile"? ' mobile': '';
			$device['title'] = $_SESSION['device']==="mobile"? TXT_TITLE_SET_TO_DESKTOP: TXT_TITLE_SET_TO_MOBILE;
			$device['data-title'] = $_SESSION['device']==="mobile"?  TXT_TITLE_SET_TO_MOBILE: TXT_TITLE_SET_TO_DESKTOP;
			$this->view->addReplacement('device', $device);
			
			$this->addLog("RESPOND_METHOD: ".$respond_method,1);
			if ( !method_exists($this, $respond_method) ) { $respond_method = $this->respond_method;}
			$this->$respond_method();
			return;
			
		
	}
	
	private function intro(){
	
		$model = $this->model_article;
		
		
	//	Build menu of al available applicatiotions:
		$applications = $this->model_route->fe_applications();
		$this->view->menus['children']  = $this->view->fe_menu_applications($applications);
		$this->view->addReplacement('menus', $this->view->menus);
		
	//	Get articles for page:	
		$records = $model->get_system_articles(SYSTEM_CAT_INTRO);
		$sections = $this->view->html_articles($records,TXT_VBNE_LBL_VBNE_SLEUTELS);
		$this->view->addReplacement('sections', $sections);
		
	//	Respond view:
		$this->view->addReplacement('classArticle', '');
		$this->html = $this->view->getHtml();
		$this->respond();
		exit;
		
	}
	
	private function info(){
	
		$model = $this->model_article;
		
		if (APP === 'ven'){
			header('Location: http://www.natuurkennis.nl/sleutels/vennensleutel');
			exit;
		}
		
	//	Build header menu:
		$items = $this->model_route->fe_get_menu_by_name(MENU_FE_INFO);
		$this->view->menus['header'] = $this->view->fe_menu_header($items);
		
		$this->view->addReplacement('menus', $this->view->menus);
		
		
		
		
	//	Get articles for page:	
		$records = $model->get_system_articles(SYSTEM_CAT_INTRO);
		$sections = $this->view->html_articles($records, ucfirst(APPLICATION),true);
		$this->view->addReplacement('sections', $sections);
		
		$this->view->addReplacement('sections', $sections);
			
		$this->html = $this->view->getHtml();
		$this->respond();
		exit;
		
	
	}
	
	private function introductie(){
		
		$model = $this->model_article;
		
		if (APP === 'ven'){
			header('Location: http://www.natuurkennis.nl/sleutels/vennensleutel/index.php');
			exit;
		}
		
	//	Build header menu:
		$items = $this->model_route->fe_get_menu_by_name(MENU_FE_INFO);
		$this->view->menus['header'] = $this->view->fe_menu_header($items);
		$this->view->addReplacement('menus', $this->view->menus);
		
		
	//	Get start FID_CAT:
		$FID_CAT = $model->fe_get_category_id_by_route();
		if ($FID_CAT=== false) {
			$sections = $this->view->getHtml('404.html');
			$this->view->addReplacement('sections', $sections);
			$this->html = $this->view->getHtml();
			$this->respond();
			exit;
		}
		
	//	Title and previous button:	
		$previous= array();
		$previous['class'] = ' hide';
		$previous['name']  = '';
		$title = ucfirst(TXT_LBL_INDEX);
			
		$previous['href'] = '#';
		if ( count($this->request) >1 ){
			$previous['class'] = '';
			$path = $this->request;
			$key = implode('/',$this->request);
			$title = array_key_exists($key, $this->model_route->category_names)? $this->model_route->category_names[$key] : str_replace('_',' ', end($path));
			$title = ucfirst($title);
			array_pop($path);
			$previous['href']  = PATH_.implode('/',$path);
			$key = implode('/',$path);
			$name = array_key_exists($key, $this->model_route->category_names)? $this->model_route->category_names[$key] : str_replace('_',' ', end($path));
			$previous['name']  = $name;
		}
		$this->view->addReplacement('previous', $previous);
		$title =  $title!=''? $this->view->wrapTag('h2',$title) :'';
		$this->view->addReplacement('article_title', $title);
		
	//	Build page:
		$records 	= $model->get_records_by_category($FID_CAT); 			// buttons to articles in category
		$next  		= $model->get_categories_by_parent($FID_CAT); 			// buttons to next categories
		$ID = array_key_exists('id', $_REQUEST)? (int)$_REQUEST['id'] : -1; 
		$aR = $ID > 0?  $model->ar($ID): array();							// selected article
		$sections = $this->view->fe_index_page($records,$aR, $next);
		$this->view->addReplacement('sections', $sections);

	//	Respond	html:
		$this->html = $this->view->getHtml();
		$this->respond();
		exit;
		
	}
	
	private function begrippen(){
		
		require_once MODELS.'note.php';
		$model = new modelNote();
		return $this->list_page($model, TXT_VBNE_LBL_NOTES);
		
	}
	
	private function literatuur(){
		
		require_once MODELS.'reference.php';
		$model = new modelReference();
		return $this->list_page($model, TXT_VBNE_LBL_REFERENCES,'listitem.reference.html');
	
	}
	
	private function list_page($model,$title, $template = 'listitem.html'){
		
	
	//	Build header menu:
		$items = $this->model_route->fe_get_menu_by_name(MENU_FE_INFO);
		$this->view->menus['header'] = $this->view->fe_menu_header($items);
		$this->view->addReplacement('menus', $this->view->menus);
		
	//	Get start FID_CAT:
	    $route = $this->request;
	    array_shift( $route);
		$FID_CAT = $model->fe_get_category_id_by_route($route);
		$FID_CAT= $FID_CAT=== false? -1 : $FID_CAT;	
	
	//	Title and previous button:	
		$previous= array();
		$previous['class'] = ' hide';
		$previous['name']  = '';
		$title = ucfirst($title);
			
		$previous['href'] = '#';
		if ( count($this->request) >1 ){
			$previous['class'] = '';
			$path = $this->request;
			$key = implode('/',$this->request);
			$title = array_key_exists($key, $this->model_route->category_names)? $this->model_route->category_names[$key] : str_replace('_',' ', end($path));
			$title = ucfirst($title);
			array_pop($path);
			$previous['href']  = PATH_.implode('/',$path);
			$key = implode('/',$path);
			$name = array_key_exists($key, $this->model_route->category_names)? $this->model_route->category_names[$key] : str_replace('_',' ', end($path));
			$previous['name']  = $name;
		}
		$this->view->addReplacement('previous', $previous);
		$title =  $title!=''? $this->view->wrapTag('h2',$title) :'';
		$this->view->addReplacement('article_title', $title);
		
	//	Build page:
		$records 	= $model->get_records_by_category_all_fields($FID_CAT); // current records.
		$next  		= $model->get_categories_by_parent($FID_CAT); 			// buttons to next categories.
		 
		$sections = $this->view->fe_list_page($records, $next, $template);
		$this->view->addReplacement('sections', $sections);
			
		$this->html = $this->view->getHtml();
		$this->respond();
		exit;
	
	}
	

	private function rapportage(){
	
		$model = $this->model_article;
	
	//	Build header menu:
		$items = $this->model_route->fe_get_menu_by_name(MENU_FE_INFO);
		$this->view->menus['header'] = $this->view->fe_menu_header($items);
		$this->view->addReplacement('menus', $this->view->menus);
		
	
		$records = $this->model_article->get_favorites();
		$favorites= array();
		foreach ($records as $record){
		
			$this->view->addReplacement('record',$record);
			$favorites[] = $this->view->getHtml('favorites.article.html');
		}
		
		$favorites =  $this->view->wrapTag('div',$favorites);
		$this->view->addReplacement('favorites', $favorites );
		$sections = $this->view->getHtml('favorites.html');
		
	//	$sections = $this->view->html_articles($records);
	
		$this->view->addReplacement('sections',$sections);
		
	//	Show settings when user is logged in:
		if ($this->user_check_active() ) {
			
			require_once CONTROLLERS.'article.php';
			$controller = new controllerArticle();
			$cs_raw = $controller->favorite_get_record();
			$cs = array();
			$cs['name'] = array_key_exists('name', $cs_raw)? $cs_raw['name'] : TXT_VBNE_TITLE_SELECTION_NOT_SAVED;
			$cs['id'] = array_key_exists('ID', $cs_raw)? (int)$cs_raw['ID'] : -1;
			$this->view->addReplacement('cs',$cs);
			
			
			$this->view->addReplacement('settings_hide','');
			$this->view->addReplacement('settings',$this->view->html_settings_report($cs['id']));
		}
			
		$this->html = $this->view->getHtml();
		$this->respond();
		exit;
	
	}
	
	private function article(){
	
	//	Check paramter "id":
		$ID = array_key_exists('id', $_REQUEST)? (int) $_REQUEST['id']: -1;
		if ($ID <1) {
			$sections =  $this->view->getHtml('404.html');
			$this->view->addReplacement('sections', $sections);
				
			$this->html = $this->view->getHtml();
			$this->respond();
			exit;
			return;
		}
		
	//	Get article
		require_once MODELS.'article.php';
		$model = new modelArticle();
		$article= $model->get_ar($ID);
		
		$publish = false;
		if (array_key_exists('publish', $article) ){
			$publish = (int)$article['publish'] == 1? true : false;
		}
		if ($publish == false) {
			$sections =  $this->view->getHtml('404.html');
			$this->view->addReplacement('sections', $sections);
			$this->html = $this->view->getHtml();
			$this->respond();
			exit;
			return;
		}
		

	
	//	Build page:
		$sections = $this->view->html_article($article);
		$this->view->addReplacement('sections', $sections);
		$this->html = $this->view->getHtml();
		$this->respond();
		exit;
	    return;
	
	}
	
	private function sleutels(){
		
		if (APP === 'ven'){
			header('Location: http://www.natuurkennis.nl/sleutels/vennensleutel/index.php?view=sleutels');
			exit;
		}
		
	//	Show settings when user is logged in:
		if ($this->user_check_active() ) {
			
			$cs_raw = vbne_observation_get_record();
			
			$cs = array();
			$cs['name'] = array_key_exists('name', $cs_raw)? $cs_raw['name'] : TXT_VBNE_TITLE_SELECTION_NOT_SAVED;
			$cs['id'] = array_key_exists('ID', $cs_raw)? (int)$cs_raw['ID'] : -1;
			$this->view->addReplacement('cs',$cs);
		
			$this->view->addReplacement('settings_hide','');
			$this->view->addReplacement('settings',$this->view->html_settings_observation($cs['id']));
			
		}
		
		
	//	Build header menu:
		$items = $this->model_route->fe_get_menu_by_name(MENU_FE_CALCULATIONS);
		
		if (count($items) == 0) {
		//	Hide open menu button in case no items are available.	
			$this->view->hide['btn_showmenu'] = ' hide';
			$this->view->addReplacement('hide', $this->view->hide);
			
		//	Na additional margin at banner.
			$this->view->addReplacement('classArticle', '');
		}
		$this->view->menus['header'] = $this->view->fe_menu_header($items);
		$this->view->addReplacement('menus', $this->view->menus);
	
	//	Get system_category.
		$count_request 			= count($this->request);
		$system_category_name 	= $count_request == 1? $this->request[0] : $this->request[1];
		$system_category 		= $this->model->get_system_category($system_category_name);
		
	//	unknown url: ERROR 404.
		if ($system_category === false ){
			$html_sections = $this->view->getHtml('404.html');
			$this->view->addReplacement('sections', $html_sections);
			$this->html = $this->view->getHtml();
			$this->respond();
			exit;
		}
		
	//	Build html:
		$html = array();
		
	//	url = /sleutels: show introduction-page.
		if ($count_request <= 2) {
			$rows 					= $this->model->get_system_articles($system_category_name);
			
		//	Top header:
			$H2						= $system_category['name'];
			
		//	Attributes for all ellements	
			$attr 					= array();
			$attr['h2'] 			= array();
			$attr['h3'] 			= array();
			$attr['section'] 		= array();
			$attr['div'] 			= array();
			
			$attr['h2']['class'] = 'title closed';
			$attr['h2']['href'] ="#";
			$attr['h2']['data-sa'] = 'onClick:fe.toggle_view;label:content_h2;';
			
			$attr['div']['class'] = 'hide';
			
			$attr['h3']['class'] = 'title';
			
			$attr['section']['class'] = 'hide';
			$attr['section']['data-sa'] = "content_h2";
			
		//	Attributes by row:
			foreach ($rows as $key => $row){
				$rows[$key]['attr'] = array();
				$rows[$key]['attr']['h3'] = array();
				$rows[$key]['attr']['h3']['data-sa'] = "onClick:fe.toggle_view;label:content_h3_".$row['ID'];
				
				$rows[$key]['attr']['div']= array();
				$rows[$key]['attr']['div']['data-sa'] = "content_h3_".$row['ID'];
			}
			
		//	Use a-tag instead of h2 and h3.	
			$tags =array();
			$tags['h2'] = 'a';
			$tags['h3'] = 'a';
			
			$html[]				= $this->view->html_articles($rows,$H2, false, $attr, $tags);
		}
		
	//	Introduction page:
		if ($count_request == 1) {
			$html[] =  $this->view->html_menu_sleutels($items);
			$html_sections = $this->view->wrapTag('div',$html);
			$this->view->addReplacement('sections', $html_sections);
			$this->html = $this->view->getHtml();
			$this->respond();
			exit;
		}
		
	//	Get controller:
		switch ($system_category_name) {
			
			case "observaties":
			$controller_file_name 	= CONTROLLERS.'observation.php';
			$controller_name 		= 'controllerObservation';	
			break;
			
			case   "calculaties":
			$controller_file_name 	= CONTROLLERS.'calculation.php';
			$controller_name 		= 'controllerCalculation';
			break;
				
			default: 
			//	ERROR 404:
				$html_sections = $this->view->getHtml('404.html');
				$this->view->addReplacement('sections', $html_sections);
				$this->html = $this->view->getHtml();
				$this->respond();
				exit;
		}
		if ( !file_exists($controller_file_name) ){
			$html_sections = $this->view->getHtml('404.html');
			$this->view->addReplacement('sections', $html_sections);
			$this->html = $this->view->getHtml();
			$this->respond();
			exit;
		}
		require_once $controller_file_name;
		$controller = new $controller_name;
		
		$structure_root = $controller->model->fe_menu();
		$controller->view->set_root_structure($structure_root);
		
	//	Get menu structure:						$controller->model->fe_menu($request);
		$request = null;					//	On top-level $request = null.
		if ($count_request > 2) {			//	request-path > 2 segments: use last n-2 segements.
			$request = $this->request;
			array_shift($request);
			array_shift($request);
		}
		
		if ( $request[0] === "result" ){
			$html[] = $controller->result();
		//	Respond to client;
			$html_sections = $this->view->wrapTag('div',$html);
			
		//	Hidden breadcrumb:
			$this->view->addReplacement('crumb_hidden', ' class="hidden" ');
			
			$this->view->addReplacement('sections', $html_sections);
			$this->html = $this->view->getHtml();
			$this->respond();
			exit;
			
		}
		
		$structure = $controller->model->fe_menu($request);
	
		
	//	Get published records:
		if ( array_key_exists('records', $structure)) {
			$published_records = $controller->fe_published_records($structure['records']);
			if ($published_records  !== false) {
				$structure['records'] = $published_records;
			}
		}
	
	//	Build page:
		$html[] = $controller->view->fe_page($structure);
	
	//	Add score to the stroke_top
		if ( $system_category_name === "calculaties" ) {

			if ( array_key_exists('id', $_REQUEST)) {
				
				$ID = (int)$_REQUEST['id'];
				if ( $ID >0 ) {
					$this->view->addReplacement('stroke_top_hide','');
					$score =$controller->score;
					$score['name'] = strtolower($score['name']);
					

					
					$attr = array();
					$attr['href'] = $attr['href'] = PATH_.'sleutels/calculaties/result?calculation='.$ID.'&id='.$ID; // parmeter 'calculation is needed for re-direct and updata return stack.
					$attr['target'] = "_self";
					$attr['title'] = TXT_VBNE_TITLE_REPORT;
					$score['name'] = $this->view->wrapTag('a',$score['name'],$attr);
					
					
					$this->view->addReplacement('score',$score);
					$html_stroke_top = $this->view->getHtml('score.stroke_top.html');
					$this->view->addReplacement('stroke_top',$html_stroke_top);
					
				}
			}
		}
		
	//	Respond to client;
		$html_sections = $this->view->wrapTag('div',$html);
		$this->view->addReplacement('sections', $html_sections);
		$this->html = $this->view->getHtml();
		$this->respond();
		exit;
		
	
	}
	
	private function account(){
		
		
	//	Build header menu:
		$items = $this->model_route->fe_get_menu_by_name(MENU_FE_ACCOUNT);
		$this->view->menus['header'] = $this->view->fe_menu_header($items);
		$this->view->addReplacement('menus', $this->view->menus);
	
		
	//	Get user  aR record:
		require_once MODELS.'user.php';
		$model = new modelUser();
		$user = $this->user();
		$aR = $model->getByEmail($user['email']);
		
		$aR['showName_checked']= (int)$aR['showName']>0? ' checked' : '';
		$aR['shareObservations_checked']= (int)$aR['shareObservations']>0? ' checked' : '';
		$this->view->addReplacement('ar', $aR );
		
		
	//	Set msg;
		$this->message_assign();
		
	
		
		switch($this->request[0]) {
			
			case "my_reports":
			case "my_observations":
				
			$this->view->addCss('_vendor/jquery_ui/jquery-ui.min.css');
			$this->view->addCss('_vendor/jquery_ui/jquery-ui.structure.min.css');
			$this->view->addCss('_vendor/jquery_ui/jquery-ui.theme.min.css');
			$this->view->addCss('_vendor/jquery_ui/overwrite.css');
			$this->view->addJs('_vendor/jquery_ui/jquery-ui.min.js');
				
			if (APP == 'gen'){
				$html = '';
			//	Build menu of al available applications:
				$applications = $this->model_route->fe_applications();
				if ( $this->request[0] == 'my_reports') {
					$this->view->menus['children']  = $this->view->fe_menu_applications_my_reports($applications);
				} else {
					$this->view->menus['children']  = $this->view->fe_menu_applications_my_observations($applications);
				}
				$this->view->addReplacement('menus', $this->view->menus);
				break;
				
			}
				
			$file_name = $this->request[0] == 'my_reports'? 'my_reports.php' : 'my_observations.php';
			$view_name = $this->request[0] == 'my_reports'? 'viewMyReports'  : 'viewMyObservations';
				
			require_once VIEWS.$file_name;
			$view = new $view_name();
			
			if ( array_key_exists('id', $_REQUEST) ){
				$ID = (int)$_REQUEST['id'];
				if ($ID >0) {
					
					$html = $view->html_form($ID);
					break;
				}
				
			}
			
			$html = $view->getHtml();
			break;
			
			default:
			$html = $this->view->getHtml('form.my_account.html');
			break;
		}
		
		$sections = $this->view->wrapTag('section',$html);
		
		$this->view->addReplacement('sections', $sections);
		
		$this->html = $this->view->getHtml();
		$this->respond();
		exit;
	
	}
	
	private function geolocation(){
		
		$record = array();
		if ( array_key_exists('id', $_REQUEST) ){
			$ID = (int)$_REQUEST['id'];
			
			require_once MODELS.'user.php';
			$model_user = new modelUser();
			$record = $model_user->get_my_observation($ID);
		}
		

		$this->view->addReplacement('record', $record);
		$sections = $this->view->getHtml('form.geolocation.html');
		
		$google_maps =$this->view->getHtml('google.maps.html');
		
		$this->view->addReplacement('google_map', $google_maps);
		
		$this->view->addReplacement('sections', $sections);
		$this->html = $this->view->getHtml();
		$this->respond();
		exit;
		
	}
	
	private function help(){
		
		
		$model = $this->model_article;
		
	//	Read ID:
		$ID = array_key_exists('id', $_REQUEST)? (int)$_REQUEST['id'] : -1;
		
	//  Root cat ID
		$root_cat_id = $model->get_system_category_id(SYSTEM_CAT_HELP);
		if ($root_cat_id === false) {
		$html_sections = $this->view->getHtml('404.html');
		$this->view->addReplacement('sections', $html_sections);
		$this->html = $this->view->getHtml();
		
		$this->respond();
		exit;
		}
		
		
	//	Get category:
		$FID_CAT = $model->fe_get_category_id_by_route();
		$FID_CAT = $FID_CAT=== false? $root_cat_id :$FID_CAT;
		$cat = $model->get_category_by_id($FID_CAT);
		if (!array_key_exists('publish', $cat)) {
		$html_sections = $this->view->getHtml('404.html');
		$this->view->addReplacement('sections', $html_sections);
		$this->html = $this->view->getHtml();
		$this->respond();
		exit;
		}

		
	//	Title:
		$title = ucfirst($cat['name']);
		$title = $this->view->wrapTag('h2',$title);
		$this->view->addReplacement('article_title', $title);
		
	//	Previous button:
		$previous				= array();
		$previous['class'] 		= ' hide';
		$previous['name']  		= '';
		$previous['href'] 		= '#';
		if ($root_cat_id !== $FID_CAT) {
			$previous['class'] = '';
			$path = $this->request;
			array_pop($path);
			$previous['href']  = PATH_.implode('/',$path);
			$key = implode('/',$path);
			$name = array_key_exists($key, $this->model_route->category_names)? $this->model_route->category_names[$key] : str_replace('_',' ', end($path));
			$previous['name']  = $name;
		}
		$this->view->addReplacement('previous', $previous);
		
	//	Build page:
		$records 	= $model->get_records_by_category($FID_CAT); 			// buttons to articles in category
		$next  		= $model->get_categories_by_parent($FID_CAT); 			// buttons to next categories
		$aR = $ID > 0?  $model->ar($ID): array();							// selected article
		$sections = $this->view->fe_index_page($records,$aR, $next);
		$this->view->addReplacement('sections', $sections);

	//	Respond	html:
		$this->html = $this->view->getHtml();
		$this->respond();
		exit;
	
	}
	
	private function imagesize(){
		
		$current_status = $_POST['status'];
		
		if ($current_status === 'normal') {
			$new_status = "lr";
			$_SESSION['IMAGE_LR']= "lr";
			$this->data['status']="lr";
			
		} else {
			$this->data['status']="normal";
			if ( isset($_SESSION['IMAGE_LR']) ) { 
				unset($_SESSION['IMAGE_LR']);
			}
		}
		
				
		$this->meta_error 	= 0;
		$this->meta_msg 	= "Level is aangepast in. ".$this->data['status'];
		parent::respond_data();
		return;
		
	}
	
	private function device(){
	
		$current = array_key_exists('status', $_POST)? strtolower(trim($_POST['status'])) : '#NOT_SET';
		if ( $current === '#NOT_SET') {
			$this->meta_error 	= -99;
			$this->meta_msg 	= TXT_ERR_UNKNOWN;
			parent::respond_data();
			return;
		}
		
		$_SESSION['device'] = $current === 'desktop'? 'mobile' : 'desktop';
		$this->data['status'] = $_SESSION['device'];
		$this->meta_error 	= 0;
		$this->meta_msg 	= TXT_OK;
		parent::respond_data();
		return;
	
	}
	
	private function pdf(){
		
	//	Hidden breadcrumb:
		$this->view->addReplacement('crumb_hidden', ' class="hidden" ');
	
		$respond = count($this->request)>1? $this->request[1] : '';
		
		$this->view->addCss('_vendor/jquery_ui/jquery-ui.min.css');
		$this->view->addCss('_vendor/jquery_ui/jquery-ui.structure.min.css');
		$this->view->addCss('_vendor/jquery_ui/jquery-ui.theme.min.css');
		$this->view->addCss('_vendor/jquery_ui/overwrite.css');
		$this->view->addJs('_vendor/jquery_ui/jquery-ui.min.js');
		
		$meta = array();
		
		$user 					= $this->user();
		$user_name 				= array_key_exists('name', $user)? $user['name']: '';
		$this->view->addReplacement('author', $user_name);
		
		$meta['author'] = $user_name;
		 
		
		switch ($respond) {
			case 'favorites':
				
			//	Get name of active record set:	
				if ($this->user_check_active()) {
					
					require_once CONTROLLERS.'article.php';
					$controller = new controllerArticle();
					$f = $controller->favorite_get_record();
					$meta['title'] = array_key_exists('name', $f)? $f['name'] : '';
					$meta['remarks']= '';
					$this->view->addReplacement('meta', $meta);
				}
				
				$html_sections = $this->view->getHtml('form.pdf.meta.favorites.html');
			break;
			
			default:
			//	Get name of active record set:
				if ($this->user_check_active()) {
					
					$ar_user_obs = vbne_observation_get_record();
					$user_obs_id = array_key_exists('ID', $ar_user_obs)? (int)$ar_user_obs['ID'] : -1;
					
					if ($user_obs_id>0 ){
					
						require_once MODELS.'user.php';
						$model_user = new modelUser();
						$ar_user_obs = $model_user->get_my_observation($user_obs_id);
						
						$meta['title'] = array_key_exists('name', $ar_user_obs)? $ar_user_obs['name'] : '';
						
						$meta['date'] = array_key_exists('date', $ar_user_obs)?  $ar_user_obs['date']: '';
						
					
						
						$NE_LATtxt = array_key_exists('NE_LATtxt', $ar_user_obs)?  $ar_user_obs['NE_LATtxt'] : TXT_LBL_GEO_EMPTY;
						$NE_LNGtxt = array_key_exists('NE_LNGtxt', $ar_user_obs)?  $ar_user_obs['NE_LNGtxt'] : TXT_LBL_GEO_EMPTY;
						$SW_LATtxt = array_key_exists('NE_LATtxt', $ar_user_obs)?  $ar_user_obs['NE_LATtxt'] : TXT_LBL_GEO_EMPTY;
						$SW_LNGtxt = array_key_exists('NE_LNGtxt', $ar_user_obs)?  $ar_user_obs['NE_LNGtxt'] : TXT_LBL_GEO_EMPTY;
						
						
						$meta['remarks'] = TXT_LBL_GEO_N.TXT_LBL_GEO_E.': '.$NE_LATtxt.'; '.$NE_LNGtxt.'; ';
						$meta['remarks'] .= TXT_LBL_GEO_S.TXT_LBL_GEO_W.': '.$SW_LATtxt.'; '.$SW_LNGtxt.'; ';
					
					}
					 
						
					
					$this->view->addReplacement('meta', $meta);
				}
					
				
				
			$ID = array_key_exists('id', $_REQUEST)? (int)$_REQUEST['id'] : -1;
			if ($ID >0)	 {
				$this->view->addReplacement('ID', $ID);
				$html_sections = $this->view->getHtml('form.pdf.meta.result.html');;
			}else {
				$html_sections = $this->view->getHtml('404.html');
			}
			break;
		}
		$this->view->addReplacement('sections', $html_sections);
		$this->html = $this->view->getHtml();
		$this->respond();
		exit;
		
	}
		
	
}
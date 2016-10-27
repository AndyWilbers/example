<?php	// _system/views/fe.php

class viewFe extends view {
	
	public $hide = array()	;	// template-ellemets that should be hidden for specific routes
	private $banner = "banner.png";
	protected $image_lr = false;
	
	public $menus = array();

	public function __construct(){
		
		
			parent::__construct('fe.html');
	
		//	ROUTE depending elements:
			$this->hide['reset'] = APPLICATION !== '' && str_starts('sleutels/', ROUTE.'/') ? "": " hide";
			$this->hide['account'] = APPLICATION === ''  ? " hide": "";
			$this->hide['help'] = APPLICATION === ''  ? " hide": "";
			$this->hide['btn_showmenu'] = !array_key_exists('LAST_APP', $_SESSION) ? " hide": "";
			
		//	Device toggle button depending is hidden when not logged in:
			$this->hide['device'] = $this->user_check_active()? "": " hide";
			if ( APP === "gen") { $this->hide['device']  .= ' hidden';}
		
			$this->addReplacement('hide', $this->hide);
			if ( array_key_exists('LAST_APP', $_SESSION)) {
				$this->addReplacement('classArticle', ' class="extra-margin-top"');
			}
			
		//	CLASS Settings main menu:
			$class = array();
			$class['intro'] 	= (APPLICATION == '' && $this->request[0] == '')? 'class="active"': '';
	
			$active = ['inhoud','begrippen','literatuur','rapportage'];
			$check  = in_array($this->request[0], $active)? true: false;
			$check  = $check || (APPLICATION != '' && $this->request[0] == '');
			$info_active = $check? 'class="active"' :'';
			$class['info'] 		= !array_key_exists('LAST_APP', $_SESSION)? 'class="hide"': $info_active;
			
			$sleutels_active = $this->request[0] == 'sleutels'? 'class="active"' :'';
			$class['sleutels'] 	= !array_key_exists('LAST_APP', $_SESSION)? 'class="hide"': $sleutels_active;
			
			
			$account_active = $this->request[0] == 'account'? ' active' :'';
			$user = $this->user();
			$class['account'] 	= !array_key_exists('ID', $user)? ' hide': $account_active;
			if ( APP === "gen") { $class['account']   .= ' hidden';}
			
			$this->addReplacement('class', $class);
			
			$account_name = array_key_exists('name', $user)? $user['name'] : '';
			$this->addReplacement('account_name', $account_name);
		
		//	Banner file:
			$this->addReplacement('banner', $this->banner);
			
		//	Assign menus: 
			$this->menus= array();
			$this->menus['header'] = '';
		 	$this->menus['children'] = '';
			$this->addReplacement('menus', $this->menus);
			
			
		//	Set and assign lr for image resolution:
			$this->image_lr = isset($_SESSION['IMAGE_LR']);
		
			$lr = array();
			$lr['class'] 		= $this->image_lr? ' lr' : '';
			if ( APP === "gen") { $lr['class']  .= ' hidden';}
			$lr['path'] 		= $this->image_lr? 'lr/' : '';
			$lr['title'] 		= $this->image_lr? TXT_VBNE_TITLE_LOAD_IMAGES_NORMAL 	: TXT_VBNE_TITLE_LOAD_IMAGES_LR;
			$lr['data_title'] 	= $this->image_lr? TXT_VBNE_TITLE_LOAD_IMAGES_LR 		: TXT_VBNE_TITLE_LOAD_IMAGES_NORMAL;
			$this->addReplacement('lr', $lr );
			
		//	Previous button:
			$previous= array();
			$previous['class'] = ' hide';
			$previous['name']  = '';
			$this->addReplacement('previous', $previous);
			
		//	Title:
			$this->addReplacement('article_title', '');
			
		//	LogOn LogOut button:
			$my_account = array();
			$my_account['class'] = $this->user_check_active()?   ' logedin'         : '';
			if ($this->request[0] =='login' || APP === "gen") {
				$my_account['class'] = ' hidden';
			}
			$my_account['path']  =  PATH_.'account'   ;
			$my_account['title']  =  TXT_VBNE_TITLE_MY_ACCOUNT_LOGIN;
			$this->addReplacement('my_account', $my_account);
			
		//	Settings (default hidden)
			$this->addReplacement('settings_hide', ' hide');
			$this->addReplacement('settings', '');
			$this->addReplacement('settings_ui_closed', 'class="ui-closed"');
		
			
		//	Current selection:
			$cs 		 = array();
			$cs['type']  = "unknown";
			$cs['id'] 	 = -1;
			$cs['name']  = TXT_VBNE_TITLE_SELECTION_NOT_SAVED;
			$this->addReplacement('cs', $cs);
			
			
		// Google map:
		   $this->addReplacement('google_map', '<!-- Google Map -->');
		
		
	}
	
	private function html_menu(){
		
		
		
		//	Not on root level:
			$this->addReplacement('btn_show_menu', '' );
			if (APPLICATION === '') { return '';}
			
			return $this->wrapTag('nav','menu');
			
		//	Assign show menu button:
			$attr= array();
			$attr['class'] = "btn";
			$attr['id'] = "btn-showmenu";
			$attr['href'] = "#";
			$attr['title'] = TXT_TITLE_SHOW_MENU;
			$this->addReplacement('btn_show_menu', $this->wrapTag('a','' ,$attr) );
			
			
			$structure  = $this->model_route->get_menu_structure();
			$lines 		= array();
			
			
		//	Link to introduction page:
			$attr= array();
			$attr['target']  = "_self";
			$attr['href'] 	 = PATH.'intro';
			$attr['title']   = $structure['ROOT']['fe']['/intro']['title'];
			$lines[] = $this->wrapTag('a',$structure['ROOT']['fe']['/intro']['name'] ,$attr);
		
		//	Items dependent on route:
			$path_start = '';
			if (ROUTE !== ''){
				$pieces = explode('/',ROUTE);
				$path_start = $pieces[0];
			}
			switch ($path_start) {
				case   'info':
					$items 	= $structure['APP']['fe']['/info']['_'];
					$items[] = $structure['APP']['fe']['/sleutels'];
					break;
				case   'sleutels':
					$items = $structure['APP']['fe']['/sleutels']['_'];
					array_unshift($items,$structure['APP']['fe']['/info']);
					break;
				default:
					$items = array();
					$items[] = $structure['APP']['fe']['/info'];
					$items[] = $structure['APP']['fe']['/sleutels'];
					break;
						
			}
			
			
		//	Build:
			foreach ($items as $item) {
				if ( $this->model_route->is_route_available($item['ID'],APP) ) {
					$attr = array();
					$attr['target'] = "_self";
					$attr['title'] 	= $this->fieldReplace($item['title'], array('$application'=>APPLICATION));
					$attr['href']   = PATH_.ltrim($item['path'],'/');
					$attr['class'] = $item['class'];
					$name 			= $this->fieldReplace($item['name'], array('$application'=>APPLICATION));
					$lines[] 		= $this->wrapTag('a',$name ,$attr);
				}
			}
			
			$attr= array();
			$attr['class'] = "btn close hide";
			$attr['href'] = "#";
			$attr['title'] = TXT_TITLE_CLOSE_MENU;
			$lines[] 		= $this->wrapTag('a','' ,$attr);
			
		//	Return  menu:
			$attr = array();
			$attr['class'] ="menu";
			return $this->wrapTag('nav',$lines ,$attr);
		
	}
	
	private function html_menu_applications(){
		
		//	On root info page only:
			if ( APPLICATION !== '' ) 				{ return ''; }
			if ( !in_array(ROUTE, ['','intro']) )	{ return ''; }
		
			$structure  = $this->model_route->get_menu_structure();
			$item		= $structure['APP']['fe']['/info'];
			$lines 		= array();
		
		//	Build:
			foreach ($this->apps as $APP =>$name) {
				$attr= array();
				$attr['target']  = "_self";
				$attr['href'] 	 = PATH.$name.'/info';
				$attr['title'] 	 = $this->fieldReplace($item['title'], array('$application'=>$name));
				$lines[] = $this->wrapTag('a',$name ,$attr);
				
			}
			
		//	Return menu:
			$attr= array();
			$attr['class'] = "menu";
			return $this->wrapTag('nav',$lines ,$attr);
		
	}
	
	private function html_menu_other_applications(){
		
		//	Not on root level:
			if (APPLICATION === '' && in_array(ROUTE, ['','intro'])) { return ''; }
		
			$structure  = $this->model_route->get_menu_structure();
			$item		= $structure['APP']['fe']['/info'];
			$lines 		= array();
		
		//	Label:
			if (APPLICATION !== '') {
				$lines[] = $this->wrapTag('p',TXT_VBNE_LBL_OTHER_APPLICATIONS);
			}
			
		//	Build:	
			foreach ($this->apps as $APP =>$name) {
				if ($APP !== APP) {
					$attr= array();
					$attr['target']  = "_self";
					$attr['href'] 	 = PATH.$name.'/info';
					$attr['title'] 	 = $this->fieldReplace($structure['APP']['fe']['/info']['title'], array('application'=>$name));
					$lines[] = $this->wrapTag('a',$name ,$attr);
				}
			}
			
		//	Return menu:	
			$attr= array();
			$attr['class'] = APPLICATION === ''? "menu" : "menu dropdown";
			return $this->wrapTag('nav',$lines ,$attr);
	
	}
	
	public function fe_menu_applications($records = array()){
		
		$items = array();
		$attr  = array();
		$attr['target']  ='_self';
		foreach ($records as $record){
			$attr['title'] = $this->fieldReplace(TXT_VBNE_TITLE_OPEN_APPLICATION, array('application'=>$record['name']));
			$attr['href'] = $record['name'];
			$items[]= $this->wrapTag('a',$record['name'],$attr);
		}
		$attr  = array();
		$attr['class']  ='menu';
		return $this->wrapTag('nav',$items,$attr);
	}
	
	public function fe_menu_applications_my_reports($records = array()){
		return $this->fe_menu_applications_my_records('rep', $records);
	}
	
	public function fe_menu_applications_my_observations($records = array()){
		return $this->fe_menu_applications_my_records('obs', $records);
	}
	
	private function fe_menu_applications_my_records($ext, $records){
	
		$items = array();
		$attr  = array();
		$attr['target']  ='_self';
		foreach ($records as $record){
			$attr['title'] = $this->fieldReplace(TXT_VBNE_TITLE_OPEN_APPLICATION, array('application'=>$record['name']));
			$attr['href'] = $ext=='rep'? $record['name'].'/my_reports' :$record['name'].'/my_observations';
			$items[]= $this->wrapTag('a',$record['name'],$attr);
		}
		$attr  = array();
		$attr['class']  ='menu';
		return $this->wrapTag('nav',$items,$attr);
	}
	
	
	
	public function fe_menu_header($records = array()){
		$items = array();
		
		foreach ($records as $record){
			$attr  = array();
			$attr['target']  ='_self';
			$path = trim($record['path'],'/');
			
			
			$nb 			= count($this->request);
			$i 				= 0;
			$request_path 	= '';
			$glue			= '';
			while ($i <$nb) {
				$request_path .= $glue.$this->request[$i];
				$glue = '/';
				$i++;	
				if ($request_path == $path ) {
					$attr['class'] ='active';
					break;
				}
				
			}
			
			$attr['title'] = $record['title'];
			$attr['href'] = PATH_.$path;
			$name = $record['name'];
			if ( $record['counter']  !== null) {
				$counter_attr = array();
				
				
				$counter_attr['data-counter']  = $record['counter'];
				
				$counter = array_key_exists( $record['counter'],$_SESSION)? $_SESSION[ $record['counter']]:  array();
				$counter = array_key_exists( APP,$counter)? $counter[APP]:  array();
				
				$nb = count($counter);
				$counter_attr['class'] = $nb>0? "counter": "counter hide";
				$name .= $this->wrapTag('span',$nb,$counter_attr);
			}
			$items[]= $this->wrapTag('a',$name,$attr);
		}
		$attr  = array();
		$attr['class']  ='menu';
		return $this->wrapTag('nav',$items,$attr);
		
	}
	
	public function fe_index_page( $records= array(), $aR=array(), $next= array(),$class ='open'){
		
		$sections = array();
		
	//	Default attribute settings:
		$attr= array();
		$attr['target'] = '_self';
		$attr['class'] = 'title';
		$path = PATH_.implode('/',$this->request);
	
		
		
	//	Read open record:
		$ID = array_key_exists('ID', $aR)? (int)$aR['ID']: -1;
		$publish = array_key_exists('publish', $aR)? (int)$aR['publish']: -1;
 
		if ( $ID >0 && $publish>0 ) {
			
			$h3 = array();
			$h3[] =$this->wrapTag('span',$aR['name'], array('class'=>'float-left'));
			$this->addReplacement('data-id', $aR['ID']);
			$h3[] =$this->getHtml('fe.favorite.html');
			$h3[] = $this->wrapTag('div','',array('class'=>'clearfix'));
			
			$html   = array();
			$html[] = $this->wrapTag('h3',$h3, array('class'=>'title open','data-sa'=>'action:ui.toggle_article;id:'.$aR['ID'].';'));
			$html[] = $this->wrapTag('div',$aR['content'], array('data-sa'=>'hide_on:'.$aR['ID'].';'));
			$html_open= $this->wrapTag('div',$html);
		}
		

		
	//	Records:
		$html = array();
		foreach ($records as $record) {
			if ( (int)$record['ID'] !== (int)$ID){
				$attr['href'] = $path.'?id='.(int)$record['ID'];
				$html[] =$this->wrapTag('a',$record['name'], $attr);
			} else {
				$html[] = $html_open;
			}
		}
		if (count($html) >0) {
			$sections[] = $this->wrapTag('div',$html );
		}
		
	//	Links to next directories:
		$html = array();
		foreach ($next as $record) {
			$attr['href'] = $path.'/'.$record['path'];
			$attr['class'] = 'title closed';
			$html[] =$this->wrapTag('a',$record['name'], $attr);
		}
		if (count($html) >0) {
			$sections[] = $this->wrapTag('div',$html);
		}
		
		return $this->wrapTag('div',$sections);
		
		
	}
	
	public function fe_list_page( $records= array(),  $next= array(), $template = 'listitem.html'){
	
		$sections = array();
	
	//	Default attribute settings:
		$attr= array();
		$attr['target'] = '_self';
		$attr['class'] = 'btn title closed';
		$path = PATH_.implode('/',$this->request);
	
	
	//	Records:
		$html = array();
		foreach ($records as $record) {
			$this->addReplacement('record', $record);
			$html[] = $this->getHtml($template);
		}
		if (count($html) >0) {
			$sections[] = $this->wrapTag('div',$html);
		}
	
	//	Links to next directories:
		$html = array();
		foreach ($next as $record) {
			$attr['href'] = $path.'/'.$record['path'];
			$html[] =$this->wrapTag('a',$record['name'], $attr);
		}
		if (count($html) >0) {
			$sections[] = $this->wrapTag('div',$html, array('class'=>'next'));
		}
	
		return $this->wrapTag('div',$sections);
	
	
	}
	
	public function html_settings_report($ID = -1){
		
		$ID = (int)$ID;
		$ID = $ID >0? $ID  : -1; 
		
		require_once MODELS.'user.php';
		$model = new modelUser();
		$reports = $model->get_reports();
		
		$options = array();
		foreach ($reports['owner'] as $report){
			$options[$report['ID']] = $report['name'];
		}
		foreach ($reports['shared'] as $report){
			$options[$report['ID']] = $report['name'].' ('.$report['email'].')';
		}
		
		$options = $this->html_options($options, TXT_LBL_SELECT_GEN,$ID,-1);
		$this->addReplacement('options', $options);
		
		return $this->getHtml('settings.report.html');
	}
	
	public function html_settings_observation($ID = -1){
	
		$ID = (int)$ID;
		$ID = $ID >0? $ID  : -1;
	
		require_once MODELS.'user.php';
		$model = new modelUser();
		$sets = $model->get_observations();
	
		$options = array();
		foreach ($sets['owner'] as $set){
			$options[$set['ID']] = $set['name'];
		}
		foreach ($sets['shared'] as $set){
			$options[$set['ID']] = $set['name'].' ('.$set['email'].')';
		}
	
		$options = $this->html_options($options, TXT_LBL_SELECT_GEN,$ID,-1);
		$this->addReplacement('options', $options);
	
		return $this->getHtml('settings.observation.html');
	}
	
	public function html_menu_sleutels($items) {
	
	//	Read if observations and / or calculations ar in menu items:
		$observations = false;
		$calculations = false;
		foreach ($items as $item){
			if ($item['path'] == '/sleutels/observaties') {$observations = true;}
			if ($item['path'] == '/sleutels/calculaties') {$calculations = true;}
		}
	
	//	Observations:
	    if ($observations) {
			$html = $this->getHtml('sleutels.menu.observations.html');
			$this->addReplacement("observations", $html);
	    } else {
	    	
	    //	Get records and  catageries on top level:	
	    	require_once MODELS.'observation.php';
	    	$model = new modelObservation();
	    	$records 		= $model->get_records_by_category();
	    	$categories 	= $model->get_categories_by_parent(); 
	    	$nb = count($records ) + count($categories);
	    	$html = '';
	    	if ($nb >0) {
	    		$html = $this->wrapTag('h2',TXT_VBNE_LBL_OBSERVATIONS).PHP_EOL;
	    		$attr = array();
	    		$attr['target']  = '_self';
	    		$attr['class']   = 'title';
	    		foreach ($records as $record){
	    			$attr['href'] ='sleutels/observaties?id='.$record['ID'];
	    			$html .= $this->wrapTag('a','sleutels/observaties/'.$record['name'],$attr).PHP_EOL;
	    		}
	    		$attr['class']   = 'title closed';
	    		foreach ($categories as $record){
	    			$attr['href'] = $record['path'];
	    			$html .= $this->wrapTag('a',$record['name'],$attr).PHP_EOL;
	    		}
	    	}
	    	$this->addReplacement("observations", $html);
		}
		
	//	Calculations:	
		if ($calculations){
			$html = $this->getHtml('sleutels.menu.calculations.html');
			$this->addReplacement("calculations", $html);
		} else {
			require_once MODELS.'calculation.php';
			$model = new modelCalculation();
			$records 		= $model->get_records_by_category();
			$categories 	= $model->get_categories_by_parent();
			
			$nb = count($records ) + count($categories);
			
			$html = '';
			if ($nb >0) {
				$html = $this->wrapTag('h2',TXT_VBNE_LBL_CALCULATIONS).PHP_EOL;
				$attr = array();
				$attr['target']  = '_self';
				$attr['class']   = 'title';
				foreach ($records as $record){
					$attr['href'] ='sleutels/calculaties?id='.$record['ID'];
					$html .= $this->wrapTag('a',$record['name'],$attr).PHP_EOL;
				}
				$attr['class']   = 'title closed';
				foreach ($categories as $record){
					$attr['href'] ='sleutels/calculaties/'.$record['path'];
					$html .= $this->wrapTag('a',$record['name'], $attr).PHP_EOL;
				}
			}
			$this->addReplacement("calculations", $html);
		}
		$html = $this->getHtml('sleutels.menu.html');
		
		return $html;
	}
}

<?php	//	_system/system/view.php
			defined('BONJOUR') or die;
			
			
	

class	view extends  common {
		
/*	
	Parent class for Views.
*/	
		
//	Properties:	
	protected 	$template 		= 'default.html';
	protected	$path			= '';
	protected	$path_			= '';
	protected	$url			= '';
	protected	$crumb			= '';
	protected	$model_route			=  null;	//	Instance of modelSysyemInfo.
	protected	$model_system_info		=  null;	//	Instance of modelRoute.
	protected   $model_article			=  null;	//	Instance of modelArticle.
	private		$css			=  array();
	private		$js				=  array();
	private 	$modifiers		=  array();
	private 	$parts			= ''; // parts of active line;
	private		$menu_path      = '';
	private 	$menu_app		= ''; 


	
//	Properties for nested list views:	
	public 		$nestedListText 		= '';
	public		$nestedListAttrGroup	= '';
	public 		$nestedListAttr			= '';
	public		$nestedListTag 			= 'li';
	public		$nestedListTagGroup 	= 'ul';
	public 		$nestedListGroup		= true;
	
	
	
//	Properties for views with category-record structure----------------------------------------------------------------
	protected	$has_category_record_structure = false;
	protected 	$label_plu 			= 'Records';
	protected	$label				= 'Record';
	protected 	$record_name		= '';
	protected  	$href_prev 			= PATH_;
	public  	$root_path   		= PATH_;
	public 		$cat_path			= null;
	protected	$categories			= array();
	protected	$records			= array();
	protected   $inputfield_path	= '';
	private     $select_article_cat = '';
	private     $select_article     = '';
	private		$root_structure		= array();
			
//	Constructor:	
	public function __construct($template = null) {
		
		
		
		
		//	Clean template filename:
			if ( $template !== null ) {
			$template 	= trim($template);
			$template 	= strtolower($template);
			$template	= rtrim($template, '.html');
			$template   = $template.'.html';
			} else {
				$template = 'default.html';
			}
		
		//	Parent constructor:
			parent::__construct();
			
			
			
		//	Get model_route from GLOBALS:
			$this->model_route = $GLOBALS['model_route'];
			$this->model_system_info = $GLOBALS['model_system_info'];
			$this->model_article = $GLOBALS['model_article'];
			
			
			
		//	Assign relative and absolute path:
			$this->path = PATH;
			$this->home = HOME;
			$this->path_ = PATH_;
			$this->home_ = HOME_;
			$this->depth = DEPTH;;
				
		//	Set url and route
			$this->url = URL;
			$this->route = ROUTE;
			
		//	Name of application:	
			$this->application = APPLICATION ===""? strtolower(TXT_VBNE_LBL_GEN_INTRODUCTION) : APPLICATION;
			$this->application_path = APPLICATION ===""? '' : APPLICATION.'/';
			
			
		//	Set test:
			$this->addReplacement('test','');
				
			
		//	Set crumb-path
			$this->crumb = $this->createCrumb();
			$this->addReplacement('crumb',$this->crumb);
		
		//	Don't hide crumb-path:
			$this->addReplacement('crumb_hidden',' class="mobile-first-hide" ');
				
		//	Define default replacements:
			$this->addReplacement('path',$this->path) ;
			$this->addReplacement('path_',$this->path_) ;
			$this->addReplacement('home',$this->home) ;
			$this->addReplacement('home_',$this->home_) ;
			$this->addReplacement('url',$this->url);
			$this->addReplacement('route',$this->route);
		
			$this->addReplacement('depth',$this->depth);
			$this->addReplacement('box','<!-- no box -->');
			$this->addReplacement('id_pop_up',' id="popup"');
			
			
			$this->addReplacement('title',TITLE);
			$this->addReplacement('description', DESCRIPTION);
			
			$this->addReplacement('application', $this->application);
			$this->addReplacement('last_app', array_key_exists('LAST_APP', $_SESSION)? $_SESSION['LAST_APP']: '');
			$this->addReplacement('application_path', $this->application_path);
			
			$this->addReplacement('keywords', KEYWORDS);
			
			$this->addReplacement('classHtml','');
			$this->addReplacement('classBody','');
			$this->addReplacement('classMain','');
			$this->addReplacement('classHeader','');
			$this->addReplacement('classArticle','');
			$this->addReplacement('classFooter','');
			
			$dummy =$this->getHtml('dummy.html');
			$this->addReplacement('content', $dummy);
			$this->addReplacement('header', '');
			$this->addReplacement('footer','');
			
			
			$this->addReplacement('stroke_top', strtolower(TXT_VBNE_LBL_NO_SCORE).'<span class="circle-16"></span>');
			$this->addReplacement('stroke_top_hide',' class="hide" ');
			
			
			$this->addReplacement('css','<!-- no additional css files -->');
			$this->addReplacement('js_extern', '<!-- no external js library files -->');
			$this->addReplacement('js', '<!-- no additional js files -->');
			
		
			$html_ga = ENV === "PRODUCTION"? $this->getHtml('google.analytics.html') : '<!-- no google analystics -->';
			$this->addReplacement('ga',$html_ga);
			
			
		//	Will be uses for vennensleutel hardcoded links only:
			$vennen = array();
			$vennen['info'] ='';
			$vennen['sleutel'] ='';
			$this->addReplacement('vennen',$vennen);
			
		//	User:
			$this->addReplacement('user',$this->user());
				
			
		//	Settings for views with category-records structure:
			if ($this->has_category_record_structure) {
				
				//	Publish field:	
					$this->addReplacement('class_publish', "publish not");		
					
				//	Default replacements:	
					$this->addReplacement('label',  	$this->label );
					$this->addReplacement('label_plu',  $this->label_plu );
					$this->addReplacement('root_path', $this->root_path);
					$this->addReplacement('record_name',$this->record_name);
					$this->addReplacement('table',$this->record_name);
					$this->addReplacement('table_cat',$this->record_name.'_cat');
					
				//	Previous location:	
					$parts = $this->request;
					array_pop($parts);
					$glue = '';
					foreach ($parts as $part){
						$this->href_prev .= $glue.$part;
						$glue = '/';
					}
					$this->addReplacement('href_prev',$this->href_prev);
					
				//	Empty category active record:
					$cat_ar = array();
					$cat_ar['name'] = $this->label_plu;
					$cat_ar['href'] = PATH_.'admin/'.trim($this->root_path,'/');
					$cat_ar['hide'] = ' class ="hide" ';
					$this->addReplacement('cat_ar', $cat_ar);
					
				//	Article category selector+ label:
					$this->addReplacement('select_article_cat', $this->select_article_cat);
					
				//	Article  selector+ label:
					$this->addReplacement('select_article', $this->select_article);
				
				//	By default no input_field for the path is shown on add_record form;
					$this->addReplacement('inputfield_path', $this->inputfield_path);
					
				
				
			}
			
			$this->setTemplate($template);
		
	} // END __construct(). 
		
// 	Methods:
	public function html_view(){
		
		return $this->getHtml($this->template);
	
	}
	
	private function createCrumb() {
		
	
		
		//	Read crumb-path records from model_route:
			$last = null;
			$crumbs = $this->model_route->crumbs;
			
			if ( count($crumbs)>1 ){
				$last = array_pop($crumbs);
				
			}
			
		//	Build crumb path	
			$lines =array();
			foreach ($crumbs as $crumb) {
				$attr['href'] 	 = $crumb['path'];
				$lines[] 		.= $this->wrapTag('a',TXT_VBNE_CRUMB($crumb['name']), $attr).' / ';
				$this->wrapTag('a',$crumb, $attr);	
			
			}
			if ($last !== null){
				
				$lines[] =TXT_VBNE_CRUMB($last['name']);
			}
		
		
		//	Complete and return:
			if (count($lines) === 0 ) return '';
			$attr = array();
			$attr['class'] = 'crumbs float-left';
			return $this->wrapTag('nav',$lines,$attr);
			

	}
	
	
	
	
	
/**
 * Create a nested menu in ROOT scope below start_path 
 * @param  menu: name of an menu in model_route->menu_structure.
 * @param  start_path: path of which childs form the top-level of the menu.
 * @return html anchors in nav stucture
 * 
 */	
public  function html_nested_menu_root($menu, $start_path ){
	return $this->html_nested_menu('ROOT', $menu, $start_path);
}

/**
 * Create a nested menu in APP scope below start_path
 * @param  menu: name of an menu in model_route->menu_structure.
 * @param  start_path: path of which childs form the top-level of the menu.
 * @param app: key of of application to include in the menu-path when menu is places on a page on root-level.
 * @return html anchors in nav stucture.
 *
 */
public  function html_nested_menu_app($menu, $start_path, $app = APP ){
	return $this->html_nested_menu('APP', $menu, $start_path, $app);
}
private function html_nested_menu($scope, $menu, $start_path, $app = APP ){
	
	//	Get menu-structure:
		$structure = $this->model_route->get_menu_structure();
		
	//	Check menu:
		if (!array_key_exists($menu, $structure[$scope])) { 
			$this->addLog('"'.$menu.'" is not an valid menu-name in'.$scope.'-scope.',1000);
			return '';
		}
		
	//	Check  start-path 
		$start_path = strtolower('/'.ltrim($start_path,'/'));
		if (!array_key_exists($start_path, $structure[$scope][$menu])) {
			$this->addLog('"'.$start_path.'" is not an valid path in menu "'.$menu.'"('.$scope.'-scope).',1000);
			return '';
		}
	
	//	Set path to menu-item:
		if ($scope === 'ROOT') {
			$this->menu_path =  PATH;
		} else {
			$application = $this->apps[$app];
			$this->menu_path = APPLICATION === ''?  PATH.$application.'/': PATH_;
			$this->menu_app = $app;
		}
		
	//	Create menu:
		return $this->html_nested_menu_( $structure[$scope][$menu][$start_path]['_']);

}
 private function html_nested_menu_($records, $tab = ''){
 	
 		$html= $tab.'<nav class="menu">'.PHP_EOL;

	 	foreach ($records as $record) {
	 		
	 		if ($this->model_route->is_route_available($record['ID'], $this->menu_app) ) {
	 		
		 		$attr  = array();
		 		$class = '';
		 		if ($record['class'] !=='') {
		 			$attr['class']		= $record['class'];
		 			$class= ' class ="'.$record['class'].'"';
		 		}
		 		$attr['href']	 = $this->menu_path.ltrim($record['path'],'/');
		 		$attr['target']	 = '_self';
		 		$attr['title']	= $record['title'];
	 		
	 			$html.=$tab."\t".'<nav'.$class.'>'.PHP_EOL;
	 			
	 			$html.=$tab."\t"."\t".$this->wrapTag('a',$record['name'],$attr).PHP_EOL;
	 			
	 			//	Add child-menu in case present:
			 		if ( array_key_exists('_', $record) ) {
			 			$html.= $this->html_nested_menu_($record['_'],$tab."\t"."\t");
			 		}
	 		
	 			$html.=$tab."\t".'</nav>'.PHP_EOL;
	 		}
	 	}
 		$html.= $tab.'</nav>'.PHP_EOL;
 		
 	return $html;
 }
	
	
	
	public function setTemplate($name = '#NOT_SET'){
	/*
		Set and get name of template file.
	*/	
		
		if ( $name === '#NOT_SET') {
			return $this->template;
		} 
		$this->template = $name;
		return $this->template;
		
	} // END  setTemplate().
		
	public function addReplacement($name, $value){
	
		$this->replacements[$name] = $value;
		return $this->replacements;
		
	} // END  addReplacement().
	
	public function getReplacements() {
		
		return $this->replacements;
		
	} // END getReplacements().
	
	public function addCss($path=''){
	
		$this->css[$path] = $this->wrapTag('link','', array('rel'=>'stylesheet','href'=>$this->path.$path));
		$this->replacements['css']='';
		foreach ($this->css as $css) {
			$this->replacements['css'] .= $css.PHP_EOL;
		}
		return $this->replacements['css'];
		
	} // END  addCss.
	
	public function getCss() {
		
		return $this->css;
		
	} // END getCss().
	
	public function addJs($path=''){
	
		$this->js[$path] = $this->wrapTag('script','', array('src'=>$this->path.$path));
		$this->replacements['js']='';
		foreach ($this->js as $js) {
			$this->replacements['js'] .= $js.PHP_EOL;
		}
		return $this->replacements['js'];
		
	} // END  addCss.
	
	public function addJsExtern($path=''){
	
		$this->js_extern[$path] = $this->wrapTag('script','', array('src'=>$path));
		$this->replacements['js_extern']='';
		foreach ($this->js_extern as $js) {
			$this->replacements['js_extern'] .= $js.PHP_EOL;
		}
		return $this->replacements['js_extern'];
	
	} // END  addCss.
	
	public function getJs() {
		
		return $this->js;
		
	} // END getCss().	
	
	/**
	 * Wraps a opening and closing tag to $content ,
	   optional attributes are added.
	   For tags 'img' and 'link' no content is added and tag is closed with '/>'.
	 	
	   In case content is an array, the tag is wrapped as block 
	 
	 * @param string $tag (required)
	 * @param string | array $content (optional, by default ''), in case $content is an array,
	 *        the elements will never be trimmed, one leading "\t" is in included.
	 * @param array $attr (optional: name=>value pairs for attributes to be added).
	 * @param array $trim_content (optional) default: true) wil not be used when content is an array;
	 * @return string
	 */
		
	public function wrapTag($tag, $content='', $attr = array(), $trim_content=true) {
	/*
		Wrap a opening and closing tag to $content ,
		optional attributes are added.
	*/
	
		//	Build attribute string:
			$strAttr = '';
			$pre = ' ';
			foreach ($attr as $name=>$value) {
				$strAttr .=$pre.$name.'="'.$value.'" ';
				$pre = '';
			}
			
		//	Wrap with tag and return:
			$tag = trim(strtolower($tag));
			switch ($tag) {
				case 'img':
				case 'link':
				case 'input':
					return '<'.$tag.$strAttr.'>';
					break;
				
				default:
					if ( is_array($content) ) {
						$lines = $content;
						$content = PHP_EOL;
						foreach ($lines as $line){
							$content .= "\t".$line.PHP_EOL;
						}
						
						return '<'.$tag.$strAttr.'>'.$content.'</'.$tag.'>';
						break;
						
					} else {
						
						$content = $trim_content? trim($content): $content;
						return '<'.$tag.$strAttr.'>'.$content.'</'.$tag.'>';
						break;
					}
					
			}
	
	} // END wrapTag().
	
	
	
		
	private function loadTemplate($name) {
	/*
		Returns an array with all lines of a template file.
	*/	
		$pathTemplate = ROOT_TEMPLATES.$name;
		
		if( file_exists($pathTemplate) === false) {
			$this->addLog('Template file "'.$pathTemplate.'" doesn\'t excists.',1);
			return false;
		}
		
		$this->html = file($pathTemplate);
		return $this->html;
		
	} // END  loadTemplate().
	
	public function getHtml($name = '#NOT_SET') {
	/*
		Reads $this->html, fills in repplacements and blocks and returns html code.
	*/
		//	Collector for html lines:
			$html = ''; 
			
		//	Load template file:
			$name = $name === '#NOT_SET'?$this->template :trim($name);
	        $file = $this->loadTemplate($name);
			if ($file === false) { return ''; }
			
		//	Interpreter line by line:
			
			foreach ($file as $line) {
				
				
				//	Divide into parts by {} brackets:
					$parts=preg_split("/[{}]/",$line);
					
				//	set active parts:
					$this->parts=$parts;	
					
				
				//	In case no brackets objects are found or by an incorrect format: add line as is.
					$num = count($parts);
					if ( $num<3 || is_even($num)) { $html .= $line; continue; }
				
				//	Step by part:
					foreach ($parts as $i => $part) {
						
					
						//	An even part is plain html: add as is:
							if (is_even($i) ) {$html .= $part; continue;}
							
						//	Treat part as code:	
							$code = trim($part);
							switch ($code) {
								
								case substr($code,0,1) === "$":
								//	Reconized as a replacement:
									
									//	get name and modifiers:
										$this->modifiers=array(); // reset;
										$c = explode('|',$code);
										$name =  str_replace('$','',trim($c[0]));
										
										if (count($c)>1) {
											$mmm = explode(';',$c[1]);
											foreach ($mmm as $mm) {
												$m = explode('=',$mm);
												$modifierName = trim(strtolower($m[0]));
												$this->modifiers[$modifierName] =true;
												if (count($m)>1) {
													$this->modifiers[$modifierName] =trim($m[1]);
												}
											}
										}
									
									//	replace:
										
										if (array_key_exists($name,$this->replacements)) {
											
											$html .= $this->modify($this->replacements[$name]);
												
										} else {
											//	In case '.' found: read as array
												if (strstr($name,'.') !== false ) { 
													$modifier = '';
													$nm = explode('|', $name);
													if (count($nm) >1 ) {
														$name = trim($nm[0]);
														$modifier = '|'.trim($nm[1]);
													} 
													$replacement = $this->findReplacement(explode('.', $name),$this->replacements);
													if ($replacement !== false ) {
														$html .= $this->modify($replacement.$modifier);
													}
												
												} else {
													$html .= $part;
												}
											
										}
									break;
								
								default:
								//	Incorrect format: add as is.
										$html .= $part; 
									break;
	
							} // END switch($code).
						
					} // END foreach ($parts as $key=> $part).
					
					
			} // END foreach ($file as $line).
	
		//	return html collector:
			return $html;
		
	} // END getHtml().
		
	private function modify($html) {
		
		//	Apply modifiers:
			foreach ($this->modifiers as $name=>$argument) {
				switch($name) {
					case 't':
					//	add leading tabs to each line:
						$l = explode(PHP_EOL,$html);
						if (count($l) >1 ) {
							$html = array_shift($l).PHP_EOL;
							if (count($l) >0 ) {
								foreach($l as $key=>$value) {
									$l[$key] =$this->parts[0].$value;
								}
								$html .= implode(PHP_EOL,$l);
							}
						} 
					break;
					case 'lc':
						$html = strtolower($html);
					break;
					case 'uc':
						$html = strtoupper($html);
						break;
					default:
					//	no modification:
						break;
				} // END switch($name).
			} // END foreach ($this->modifiers as $name=>$argument).	
			
		//	return modified html
			return $html;
			
	} // END modify().
	
	protected function attr( $attr= array()){
		$strAttr ='';
		$glue =' ';
		foreach ($attr as $name=>$value){
			$strAttr .=$glue.$name.'="'.$value.'"';
		}
		return $strAttr;
		
	}
	
	/**
	 * Create html of an array of artcle record
	 * h2-tag: H2[otptional]
	 * h3-tag: $article['name']
	 * div-tag: >$article['content']
	 * @param array [articles] => {[name],[content]}
	 * @param boolean [H2] optional
	 * @param boolen [favorite], default false: add favorite-button to article titels.
	 * @param array [attr], default an empty array: can be used to add attributes to the
	 * three main elements of hte html:
	 * attr['h2']=>array(); attr['h3']=>attr(); attr['section']=attr(); attr['div']=attr().
	 */
	public function html_articles($rows, $H2= null, $favorite = false, $attr = array(), $tags = array()){
	
	//	Check $articles:
		if (is_array($rows) === false) 	{ return ''; }
		if (count($rows) === 0) 		{ return ''; }
		
	// 	Attributes:
		$attr = is_array($attr)? $attr : array();
		$attr['h2'] = array_key_exists('h2', $attr)? $attr['h2'] : array();
		$attr['h2'] = is_array($attr['h2'])? $attr['h2'] : array();
		
		$attr['h3'] = array_key_exists('h3', $attr)? $attr['h3'] : array();
		$attr['h3'] = is_array($attr['h3'])? $attr['h3'] : array();
		
		$attr['section'] = array_key_exists('section', $attr)? $attr['section'] : array();
		$attr['section'] = is_array($attr['section'])? $attr['section'] : array();
		
		$attr['div'] = array_key_exists('div', $attr)? $attr['div'] : array();
		$attr['div'] = is_array($attr['div'])? $attr['div'] : array();
		
	//	$tags:
		$tags = is_array($tags)? $tags : array();
		$tags['h2']= array_key_exists('h2', $tags)? strtolower(trim($tags['h2'])) : 'h2';
		$tags['h3']= array_key_exists('h3', $tags)? strtolower(trim($tags['h3'])) : 'h3';
		$tags['section']= array_key_exists('section', $tags)? strtolower(trim($tags['section'])) : 'section';
		$tags['div']= array_key_exists('div', $tags)? strtolower(trim($tags['div'])) : 'div';
		
		
	//	Build $header:
		$header = '';
		if  ( $H2 !== null ) {
			$H2 = trim($H2);
			$header = strlen($H2)>0? $this->wrapTag($tags['h2'],$H2, $attr['h2']).PHP_EOL :'';
		}
		$sections = array();
		foreach ($rows as $row){
			$el = array();
			$h3 = $row['name'];
			if ($favorite) {
				$h3 = array();
				$h3[] =$this->wrapTag('span',$row['name'], array('class'=>'float-left'));
				$this->addReplacement('data-id', $row['ID']);
				$h3[] =$this->getHtml('fe.favorite.html');
				$h3[] = $this->wrapTag('div','',array('class'=>'clearfix'));
			}
			
			if (array_key_exists('attr', $row)){
				if (array_key_exists('h3', $row['attr'])) {
					$attr['h3'] = array_merge($attr['h3'] ,$row['attr']['h3'] );
				}
				if (array_key_exists('div', $row['attr'])) {
					$attr['div'] = array_merge($attr['div'] ,$row['attr']['div'] );	
				}
				if (array_key_exists('section', $row['attr'])) {
					$attr['section'] = array_merge($attr['section'] ,$row['attr']['section'] );
				}
			}
			
			$el[] = $this->wrapTag($tags['h3'],$h3,$attr['h3']);
			$el[] = $this->wrapTag($tags['div'],htmlspecialchars_decode ($row['content'], ENT_HTML5),$attr['div']);
			$sections[] =  $this->wrapTag($tags['section'],$el, $attr['section'] );
		}
		return $header.$this->wrapTag('div',$sections );
	}
	
	
   /**
	* Create html of an array with an article record
	* @param array [articles] => {[name],[content] options: [return]{ [attr]=>{}, [name]}}
	* @return html
    */
	public function html_article($fields ){
	
	//	Check $articles:
		if (is_array($fields) === false) 	{ return ''; }
		if (count($fields) === 0) 			{ return ''; }
	
	//	Build section:
		$el = array();
		
	
		
	//	Build page:
	
	
		
		$el[] = $this->wrapTag('h2',$fields['name']);
		
		
		//	Add return-button to previous page (when on stack):
			$html_return_button = $this->html_stack_return_button();
			if ($html_return_button != ''){
				$el[] =$html_return_button;
			}
		
		$el[] = $this->wrapTag('div',htmlspecialchars_decode ($fields['content'], ENT_HTML5));
		
		
		return $this->wrapTag('section',$el );
	}
	
	
	
//	Methods for html constuctions:
	/**
	 * Creates a table from array $records. First row is used to get the names of the columns, attributes
	 * can be defined at table, thead, tbody by row, by column and by th.
	 * 
	 * @param array $records: row=>fields
	 * @param array $attr: attributes "table", "thead", "tbody", tr=>row, td=>col, th=>col.
	 * @return string table html.
	 */
	public function htmlTable($records=array(), $attr= array()) {
		$strAttr = array_key_exists('table', $attr)? $this->attr($attr['table']) : '';
		$html = PHP_EOL.'<table'.$strAttr.'>'.PHP_EOL;
		$setHead = true;
		$idR = 0;
		foreach ($records as $row) {
			if ($setHead) {
				$strAttr = array_key_exists('thead', $attr)? $this->attr($attr['thead']) : '';
				$html .= "\t".'<thead'.$strAttr.'>'.PHP_EOL."\t\t".'<tr>'.PHP_EOL;
				$idC = 0;
				foreach ($row as $key=>$value) {
					$strAttr = '';
					if (array_key_exists('th', $attr)) {
						
						$strAttr = array_key_exists($idC, $attr['th'])? $this->attr($attr['th'][$idC]) : '';
					}
					$html .= "\t\t\t".'<th'.$strAttr.'>'.$key.'</th>'.PHP_EOL;
					$idC++;
				}
				$html .= "\t\t".'</tr>'.PHP_EOL;
				$html .= "\t".'</thead>'.PHP_EOL;
				$strAttr = array_key_exists('tbody', $attr)? $this->attr($attr['tbody']) : '';
				$html .= "\t".'<tbody'.$strAttr.'>'.PHP_EOL;
				$setHead = false;
			}
			$strAttrR = '';
			if (array_key_exists('tr', $attr)) {
				$strAttrR = array_key_exists($idR, $attr['tr'])? $this->attr($attr['tr'][$idR]) : '';
			}
			$html .= "\t\t".'<tr'.$strAttrR.'>'.PHP_EOL;
			$idC = 0;
			foreach ($row as $value) {
				$strAttr = '';
				if (array_key_exists('td', $attr)) {
					$strAttr = array_key_exists($idC, $attr['td'])? $this->attr($attr['td'][$idC]) : '';
				};
				$html .= "\t\t\t".'<td'.$strAttr.'>'.$value.'</td>'.PHP_EOL;
				$idC++;
			}
			$html .= "\t\t".'</tr>'.PHP_EOL;
			$idR++;
		}
		$html .= count($records)>0? "\t".'</tbody>'.PHP_EOL:'';
		$html .= '</table>'.PHP_EOL;
		
		return $html;
	}

	public function htmlNestedList ($records=array(), $parent= array(), $options = array()) {
		
		//	Set default options:
			$defaultOptions = array();
			$defaultOptions['tab'] = 0;
			$defaultOptions['parentText'] = '';
			$defaultOptions['chain'] = false;
			$defaultOptions['glue'] = '';
			
			$defaultOptions['preText'] = '';
			$defaultOptions['preTextRepeat'] = false;
			$defaultOptions['postText'] = '';
			$defaultOptions['postTextRepeat'] = false;
			$defaultOptions['parentText'] = '';
			
			
			$defaultOptions['hideParent'] = false;
			$defaultOptions['hideParentText'] = false;

			$op = array_merge($defaultOptions,$options);
			
		
		//	Check records:
			if (is_array($records) === false ) {
				$this->addLog('htmlNesteList input records isn\'t an array.',100);
				return '';
			}
			
		//	No records: return:	
		 	if (count($records) == 0 ) { return '';}
			
		//	Create preText:
			$preText = $op['preText'];
			if ($preText !== '') {
				if  ( $op['preTextRepeat'] !== false) {
					$preText = str_repeat($preText,$op['tab']/2);
				}
			}
			
		//	Create postText:
			$postText = $op['postText'];
			if ($postText !== '') {
				if  ( $op['postTextRepeat'] !== false) {
					$postText = str_repeat($postText,$op['tab']/2);
				}
			}
			
		//	Create parentTest:	
			$parentText ='';
			if ($op['parentText'] !== '' ) {
				$chain = strtolower($op['chain']);
				switch ($chain) {
						case 'pre':
							$parentText = $op['parentText'].$op['glue'];
							break;
						case 'post':
							$parentText = $op['glue'].$op['parentText'];
							break;
						default:
							
							break;
						
					}
				
			}
				
			
		//	Create identLeft:
			$identLeft = str_repeat("\t",$op['tab']);
			$identLefAfter='';
			if ($op['tab'] >0 ) {
				$identLefAfter = str_repeat("\t",$op['tab']-1);
			}
			$op['tab'] = $op['tab']+2;
			
		//	Fill in replacements for ul attributes:
			$attrGroup ='';
			if ($this->nestedListAttrGroup !== ''	) {
				$attrGroup= ' '.$this->fieldReplace($this->nestedListAttrGroup, $parent).' ';
			}
		
		//	Build resursive list:
			$html = $this->nestedListGroup !== false? PHP_EOL.$identLeft.'<'.$this->nestedListTagGroup.$attrGroup.'>'.PHP_EOL :'';
			
			foreach ($records as $item) {
				
					$replacements = array();
					$replacements['item'] =$item['row'];
					$replacements['parent'] =$parent;
					
				//	Build text:
				    $chain = strtolower($op['chain']);
					
					switch ($chain) {
						case 'pre':
							$text = $parentText.$this->fieldReplace($this->nestedListText, $replacements);
							break;
						case 'post':
							$text = $this->fieldReplace($this->nestedListText, $replacements).$parentText;
							break;
						default:
							$text = $this->fieldReplace($this->nestedListText, $replacements);
							break;
						
					}
					$op['parentText'] = $text;
				
					
				//	Fill in replacements for ul attributes:
					$attr ='';
					if ($this->nestedListAttr !== ''	) {
						$attr= ' '.$this->fieldReplace($this->nestedListAttr, $replacements).' ';
					}
					
				//	Build html:
					$text = (count($item['children']) > 0  && $op['hideParentText'] !== false )?'': $preText.$text.$postText;
		
					if ($this->nestedListGroup !== false) {	
						$html.= $identLeft."\t".'<'.$this->nestedListTag.$attr.'>'. $text;
						$html.= $this->htmlNestedList($item['children'], $item['row'], $op);
						$html.= '</'.$this->nestedListTag.'>'.PHP_EOL;
					} else {
						if (count($item['children']) === 0  || $op['hideParent'] === false ) {
							$html.= '<'.$this->nestedListTag.$attr.'>'. $text.'</'.$this->nestedListTag.'>'.PHP_EOL;
						}
						$html.= $this->htmlNestedList($item['children'], $item['row'], $op);
					}
				
					
			}
			
			$html.= $this->nestedListGroup !== false?$identLeft.'</'.$this->nestedListTagGroup.'>'.PHP_EOL.$identLefAfter :'';
			
			return  $html;
			
		
	} // END htmlNestedList().
	
	/**
	 *  Creates parent-child list markup.
	 *  
	 *  @param	$records array(n) { "content" => value, attr(n) => {"name" => value, ...}
	 *  		$markup array(2)  {
	 *  							"parent"  => {"tag" => value, attr(n) => {"name" => value, ...} }
	 *  							"child"   => {"tag" => value, attr(n) => {"name" => value, ...} }
	 *  						   }
	 *  
	 *  @return	(string) html-markup, by default ul-li list without attributes.
	 */
   public function html_list_parent_child($records, $content_field= "content", $markup = array() ) {
   	
   		//	Check records:
   			if ( $records === false ) { return '';}
   			if ( is_array($records) === false ) { return '';}
   	
   	
   		//	Intitialize:
   		    $default = array();
   		    $parent = array();
   		    $parent['tag'] ='ul';
   		    $default[] = $parent;
   		    $child = array();
   		    $child['tag'] ='li';
   		    $default[] = $child;
   		 	$markup = array_merge($markup,$default);
   		 	$parent = array_shift($markup);
   		 	$child = array_shift($markup);

   			
   		//	Wrap children:
   			$html= array();
   			$tag = $child['tag'];
   			$attr = array_key_exists('attr', $child)? $child['attr']: array();
   			foreach ($records as $row ){
   				$attr_row = array_key_exists('attr', $row)? $row['attr']: array();
   				$attr = array_merge($attr,$attr_row);
   				$html[] = $this->wrapTag($tag, $row[$content_field], $attr );
   			}
   			
   		//	Wrap parent and return:	
   			$tag = $parent['tag'];
   			$attr = array_key_exists('attr', $parent)? $parent['attr']: array();
   			return $this->wrapTag($tag, $html, $attr);
   			
   		
   }

   /**
    * Creates a list of options for a select object from an array with [value]=>[name] records.
    * 
    * @param array  $options 	{[value]=>[name]}
    * @param string $title   	name of first option  shown when no valid option is selected. 
    * @param string $selected   (optional) value of selected option (default: "#NA").
    * @param string $na			(optional) value of no selection (default: "#NA").
    * @param array  $exlude		(optional) ID's that shoud be excluded (default: an empty array).
    * 
    * @return string html list of options.
    */
   public function html_options($options, $title, $selected="#NA", $na = "#NA", $exclude = array()) {
   
   //	Check parameters:
   		if ( !is_array($options) ){return '';}
   		$title 		= trim($title);
   		$selected 	= trim($selected);
   		$na 		= trim($na);
   		
   	//  Remove options that should be excluded:
   	    if ( is_array( $exclude) ){
   	    	foreach ($exclude as $id) {
   	    		if (array_key_exists($id, $options) ) {
   	    			unset($options[$id]);
   	    		}
   	    	}
   	    }
   
   	//	Look is selected rule is avaiable:
   		$selected=  array_key_exists($selected, $options)? $selected : "#NA";
   
   	//	Create options
   		$attr = array();
   		$attr['value'] = $na;
   		if ($selected == $na) {
   			$attr['selected'] = "selected";
   		}
   		$html = "\t".$this->wrapTag('option',$title, $attr).PHP_EOL;
   		foreach ($options as $value => $name) {
   			$attr = array();
   			$attr['value'] = $value;
   			if ($value == $selected) {
   				$attr['selected'] = "selected";
   			}
   			$html .= "\t". $this->wrapTag('option',$name, $attr).PHP_EOL;
   		}
   
   		return $html;
   }


// Methods for views with category-record structure----------------------------------------------------------------:   
   
   public function html_category_record_list($arr, $tab = "", $path ='', $show_records = false){

   	if ($this->cat_path=== null ) {
   		$this->addLog('view: html_category_record_list: "cat_path" is not set',1);
   		return false;
   	}
    $cat_path  = $this->cat_path.$path;
  
   	if ( !is_array($arr)) { return false;}
   	if ( count($arr) == 0) 	{ return false; }
  	
    $html = $tab.'<ul class="category-record-list">'.PHP_EOL;
    $tab_ = $tab."\t";
    $tab__ = $tab_."\t";
   	foreach ($arr as $key=>$cat){
   		
   		$html .= $tab_.'<li>'.PHP_EOL;
   		$attr = array();
   		$attr['target']  = '_self';
   		$attr['href']    = $cat_path.$cat['path'];
   		$attr['class']  = 'category';
   		$publish = $cat['publish']>0? '': ' not';
   		$text = $cat['name'].'&nbsp;<i>('.count($cat['records']).')</i>';
   		$html .=  $tab__.'<span data-sa="onClick:admin.toggle_publish;name:'.$this->record_name.'_cat;id:'.$cat['ID'].';" class="publish'.$publish.'"></span>'.$this->wrapTag('a',$text, $attr).PHP_EOL; 
   		if ( array_key_exists('records', $cat) ) {
   			if (count($cat['records']) >0  && $show_records){
   			
   				$html .= $tab__.'<ul>'.PHP_EOL;
   				foreach( $cat['records'] as $record){
   					$attr = array();
   					$attr['target']  = '_self';
   					$attr['href']    = $cat_path.$cat['path'].'?id='.$record['ID'];
   					$attr['class']  = 'record';
   					$publish = $record['publish']>0? '': ' not';
   					$record_link 	 = '<span data-sa="onClick:admin.toggle_publish;name:'.$this->record_name.';id:'.$record['ID'].';" class="publish'.$publish.'"></span>'.$this->wrapTag('a',$record['name'], $attr);
   					$html .=  $tab__."\t".$this->wrapTag('li',$record_link).PHP_EOL;
   				}
   				$html .= $tab__.'</ul>'.PHP_EOL;
   			}
   		}
   		if ( array_key_exists('children', $cat) ) {
   			$children = $this->html_category_record_list( $cat['children'], $tab__, $path.$cat['path'].'/' );
   			$html .=  $children.PHP_EOL;
   		}
   		$html .= $tab_.'</li>'.PHP_EOL;
   	}
   
   	$html .= $tab.'</ul>';
   	return $html;

   
   }

   public function html_record_list($records){
 //return  array_pretty_print($records);
   
   	if ($this->cat_path=== null ) {
   		$this->addLog('view: html_category_record_list: "cat_path" is not set',1);
   		return false;
   	}
   	$cat_path  = $this->cat_path;
   
   	if ( !is_array($records)) { return false;}
   	if ( count($records) == 0) 	{ return false; }
   	 
   	$html = '<ul class="category-record-list">'.PHP_EOL;
   	foreach( $records as $record){
   		$attr = array();
   		$attr['target']  = '_self';
   		$attr['href']    = $this->cat_path.'?id='.$record['ID'];
   		$attr['class']  = 'record';
   		$publish = $record['publish']>0? '': ' not';
   		$record_link 	 = '<span data-sa="onClick:admin.toggle_publish;name:'.$this->record_name.';id:'.$record['ID'].';" class="publish'.$publish.'"></span>'.$this->wrapTag('a',$record['name'], $attr);
   		$html .=  "\t".$this->wrapTag('li',$record_link).PHP_EOL;
   	}
   	$html .= '</ul>';
   	
   	return $html;
   }
   
   public function set_categories($categories=""){
   		if (is_array($categories) ) {
   			$this->categories = $categories;
   		}
   		return $this->categories;
   } 
   
   public function set_records($records=""){
	   if (is_array($records) ) {
	   	$this->records=$records;
	   }
	   return $this->records;
   }
   
   public function get_cat_menu(){
   	
  // 	return array_pretty_print($this->categories);
	   
	   	$html =  array();
	   	$attr			= array();
	   	$attr['target'] = "_self";
	   	$attr['class'] = "category";
	   
	   	foreach( $this->categories as $id =>$category ){
	   		$attr['href'] 	= $this->cat_path.$category['path'];
	   		$publish = $category['publish']>0? '': ' not';
	   		$html[] = '<li><span data-sa="onClick:admin.toggle_publish;name:'.$this->record_name.'_cat;id:'.$category['ID'].';" class="publish'.$publish.'"></span>'.$this->wrapTag('a',$category['name'],$attr).'</li>';
	   	}
	   	$attr			= array();
	   	$attr['class'] = "category-record-list";
	   	$attr['data-sa'] = $this->record_name."_cat";
	   	return $this->wrapTag('ul',$html, $attr);
	   
	 }
	 
	public function get_category_position_select(){
	 
	 	$html =  array();
	 	$attr = array();
	 	$attr['value'] 	= "last";
	 	$html[]= $this->wrapTag('option','onderaan',$attr);
	 	foreach( $this->categories as $id =>$category ){
	 		$attr['value'] 	= $category['ID'];
	 		$html[]= $this->wrapTag('option','voor: '.$category['name'],$attr);
	 		
	 	}
	 	if (count($html) <1) { return '';}
	 	$attr	= array();
	 	$attr['name'] ='position_id';
	 	return $this->wrapTag('select',$html,$attr);
	 }

	public function get_record_menu(){
	  	 
	 	$html =  array();
	 	$attr			= array();
	 	$attr['target'] = "_self";
	 	$attr['class'] = "record";
	 
	 	foreach( $this->records as $id =>$record ){
	 		$attr['href'] 	= rtrim($this->cat_path,'/').'?id='.$record['ID'];
	 		$publish = $record['publish']>0? '': ' not';
	 		$html[] = '<li><span data-sa="onClick:admin.toggle_publish;name:'.$this->record_name.';id:'.$record['ID'].';" class="publish'.$publish.'"></span>'.$this->wrapTag('a',$record['name'],$attr).'<li/>';
	 	}
	 	$attr			= array();
	 	$attr['class'] = "category-record-list";
	 	$attr['data-sa'] = $this->record_name;
	 	return $this->wrapTag('ul',$html, $attr);
	 }
	 
	 public function get_record_position_select(){
	 
	 	$html =  array();
	 	$attr = array();
	 	$attr['value'] 	= "last";
	 	$html[]= $this->wrapTag('option','onderaan',$attr);
	 	foreach( $this->records as $id =>$record ){
	 		$attr['value'] 	= $record['ID'];
	 		$html[]= $this->wrapTag('option','voor: '.$record['name'],$attr);
	 	}
	 	if (count($html) <1) { return '';}
	 	$attr	= array();
	 	$attr['name'] ='position_id';
	 	return $this->wrapTag('select',$html,$attr);
	 }
	/**
	 * 
	 * @param array structure
	 * @param array options['exclude'] : ID of category to be excluded: default -1
	 *              options['name']    : name of PARENT ID; default FID_CAT.
	 *              options['value']   : ID of selected category; default -1.
	 *              options['attr']    : array with additional attributes for selectl default empty
	 *              
	 */ 
	public function html_select_category($structure = array(), $options = array()){
		
		
	
	//	Initialize:
		if (!is_array($structure)) { return '';}
		$options = is_array($options)? $options: array();
		$options['exclude'] = array_key_exists('exclude', $options) ? (int)$options['exclude'] : null;
		if ($options['exclude'] !== null) { 
			$options['exclude'] = $options['exclude'] >0? $options['exclude'] : null;
		}
		$options['name'] = array_key_exists('name', $options) ? $options['name'] : 'FID_CAT';
		$options['value'] = array_key_exists('value', $options) ? (int)$options['value'] : -1;
	
	//	Get options:
		$html_options = $this->get_select_category_options($structure, $options['exclude'],$options['value']);
		
	//	Add top level:
		$attr= array();
		$attr['value'] = -1;
		if ( (int)$options['value'] == -1) {$attr['selected'] = 'selected';}
		$top_level_option = $this->wrapTag('option','geen categorie', $attr);
		array_unshift($html_options, $top_level_option);
		$attr = array();
		if( array_key_exists('attr', $options) ) {
			$attr = is_array($options['attr'])? $options['attr']: array();
		}
		$attr['name'] =  $options['name'];
		$attr['value'] = $options['value'];
		return $this->wrapTag('select', $html_options,$attr);
		
	}
	private function get_select_category_options($structure,$exclude,$value, $options = array(),$pre='') {
		
		foreach ($structure as $ID=>$record) {
				if ((int)$ID !== $exclude) {
					$attr= array();
					$attr['value'] = $ID;
					if ( (int)$value === (int)$ID) {$attr['selected'] = 'selected';}
					$options[] = $this->wrapTag('option',$pre.$record['name'],$attr);
					if (array_key_exists('children', $record)) {
						$options = $this->get_select_category_options($record['children'],$exclude,$value, $options, '_'.$pre.$record['name'].'-');
					}
				}
		}
		return $options;
		
	}

	public function html_tbody_change_position($records = array()){
		
		if (!is_array($records))  {return '';}
		if (count($records) == 0) {return '';}
		$html = array();
		$pos = 100;
		foreach ($records as $record) {
			$td= array();
			$label = $this->wrapTag('label',$record['name']);
			$td[] = $this->wrapTag('td',$label);
			$attr= array();
			$attr['value']  = $pos;
			$attr['name'] = 'ID:'.$record['ID'];
			$attr['class'] = 'n3';
			$attr['type'] = 'text';
			$attr['pattern'] = '^[0-9]+$';
			$input = $this->wrapTag('input','',$attr);
			$td[] = $this->wrapTag('td',$input);
			$html[] = $this->wrapTag('tr',$td);
			$pos+=100;
		}
		return  $this->wrapTag('tbody',$html);
	}
	
	public function html_select_acticle_category($options= array()){
	
	//	Get structuctur of all article categories:
		$structure= $this->model_article->get_categories(-1);
		
		return $this->html_select_category($structure,$options);
		
	}

	public function set_select_article_cat($tructure = array(),$value=-1){
		
		if (is_array($tructure) === false) { 
			$this->select_article_cat ='';
			$this->addReplacement('select_article_cat', $this->select_article_cat);
			return '';
		}
		if (count($tructure) < 1 ) {
			$this->select_article_cat ='';
			$this->addReplacement('select_article_cat', $this->select_article_cat);
			return '';
		}
		
		$html = $this->wrapTag('label',TXT_VBNE_LBL_INTRO_ARTICLE_CAT).PHP_EOL;
		$options= array();
		$options['name'] = "FID_ARTICLE_CAT";
		
		$options['value'] = (int)$value >0? (int)$value: -1;
		$html .= $this->html_select_category($tructure, $options);
		$this->select_article_cat =$html;
		$this->addReplacement('select_article_cat', $this->select_article_cat);
		return $this->select_article_cat;
	}
	
	public function html_select_article($FID_ARTICLE_CAT=-1, $FID_ARTICLE=-1 ){
	
		$FID_ARTICLE_CAT 	= (int)$FID_ARTICLE_CAT;
		$FID_ARTICLE 		= (int)$FID_ARTICLE;
	
		$html 			= array();
		$attr 			= array();
		$attr['name']  	= 'FID_ARTICLE';
		$options 		= array();
	
	//	No category: create an empty hidden selector:	
		if ( $FID_ARTICLE_CAT < 1) {
			
			$html[] = $this->wrapTag('label', TXT_VBNE_LBL_INTRO_ARTICLE );
			$options[] = '<!--no options -->';
			$attr['value'] 	= -1;
			$attr['class']  = 'ui-closed';
			$html[] = $this->wrapTag('select',$options);
			return $this->wrapTag('div',$html,array('data-sa'=>'FID_ARTICLE_CAT','class'=>'ui-closed'));
		}
		
	//	Get options
		$articles = $this->model_article->get_records_by_category($FID_ARTICLE_CAT);
		$opt_attr = array();
		$opt_attr['value'] = -1;
		if (  $FID_ARTICLE-1 ) {$opt_attr['selected'] ="selected";}
		$option_name = $this->fieldReplace(TXT_LBL_SELECT, array('name'=> '&nbsp;'.strtolower(TXT_VBNE_LBL_ARTICLE) ) );
		$options[] =$this->wrapTag('option',$option_name,$opt_attr);
		foreach ($articles as $article){
			$opt_attr = array();
			$opt_attr['value'] = $article['ID'];
			if ( $article['ID'] == $FID_ARTICLE ) {$opt_attr['selected'] ="selected";}
			$options[] =$this->wrapTag('option',$article['name'],$opt_attr);
		}
		
		$attr['value'] 	= $FID_ARTICLE;
		$html[] = $this->wrapTag('label', TXT_VBNE_LBL_INTRO_ARTICLE );
		$html[] = $this->wrapTag('select',$options, $attr);
		return $this->wrapTag('div',$html,array('data-sa'=>'FID_ARTICLE_CAT'));
		
	
	}	
	
	
	public function set_inputfield_path(){
		
		$this->inputfield_path = $this->getHtml('form.record.path.html');
		$this->addReplacement('inputfield_path', $this->inputfield_path);
		return $this->inputfield_path;
	}

	public function set_root_structure($request = null){
		
		$this->root_structure = is_array($request)? $request : array();
		return $this->root_structure;
		
	}
	
	public function get_root_structure(){
	
		return $this->root_structure;
	
	}

	public function get_category_by_request_path($start=2){
		
	//	Start-point
		$start = (int)$start>0? (int)$start: 0;
		
		$structure_root = $this->get_root_structure();
		if (!array_key_exists('children', $structure_root) ) { return array(); }
	
	//	Return on top level for start=0:
		if ($start == 0 ){ return $structure_root['children'];}
		
	//	Load root-level of the category-structure:
		$structure_cat 	= array();
	
		foreach ($structure_root['children'] as $field) {
			$structure_cat[$field['path']] = $field;
		}	
		
	//	Step to top-category and build lead for href:
		
		$i 			= 0;
		$href  		= PATH_;
		$request 	= $this->request;
		$glue		= '';
		while ($i < $start ) {
			$path  	 = array_shift($request);
			$href 	.= $glue.$path;
			$glue    = '/';
			$i++;
		}
		
		
			
	//	Get category-menu 
		
		$return = array();
		while ( $path !== null){
		
			if ( array_key_exists($path, $structure_cat) ){
				$return = $structure_cat[$path];
			}
			$structure_cat	 = array_key_exists($path, $structure_cat)? 		$structure_cat[$path] 		: array();
			$structure_cat	 = array_key_exists('children', $structure_cat)? 	$structure_cat['children'] 	: array();
			$path 			 = count($request)>0? array_shift($request): null;
			if ($path !== null){
				$href 		 	.= $glue.$path;
				$glue			='/';
			}
		
		}
		
	//	Add "href" and return
		$return['href'] = $href;
		return $return;
		
	}

// Methods for views for front-end ----------------------------------------------------------------:

	/**
	 * 
	 * @param array record 	{ ['name']
	 *                        ['introduction] => {['name'],['content'] }
	 * 						}
	 */
	public function fe_page ($record = array() ) {
		
	//	Check input:
		if ( !is_array($record) ) { return ''; }
		if ( count($record) == 0) { return ''; }
		
		$html = array();
		
	//	Name:	
		$name = array_key_exists('name', $record)?  $record['name']: '&nbsp;';
		$html[] =$this->wrapTag('h2',$name);
		
	//	(Optional) introduction-articles:
		if ( array_key_exists('introduction', $record) ) {
			if ( is_array($record['introduction'])){
				if ( count($record['introduction']) > 0 ){
					foreach ($record['introduction'] as $intro){
						if ( array_key_exists('name', $intro)) {
							$html[] =$this->wrapTag('h3',$intro['name']);
						}
						if ( array_key_exists('content', $intro)) {
							$html[] =$this->wrapTag('div', htmlspecialchars_decode($intro['content'], ENT_HTML5));
						}
					}
				}
			}
		}
		
	//	return html:
		return $this->wrapTag('div',$html);
	}

		
	
	
	public function fe_menu_cat($records = array()){
	
	//	Check input:	
		if ( !is_array($records) ) { return ''; }
		if ( count($records) == 0) { return ''; }
		
	// 	Build menu:
		$a = array();
		foreach ($records as $row) {
			if ( $row['publish'] == 1){
				$attr = array();
				$attr['href'] 	= PATH_.ROUTE.'/'.$row['path'];
				$attr['target'] = "_self";
				$attr['title']  = $this->fieldReplace(TXT_VBNE_TITLE_OPEN, array('name' => strtolower($row['name'])));
				$a[]			= $this->wrapTag('a',$row['name'],$attr);
			}
		}
		return $this->wrapTag('nav',$a,array('class' => 'menu'));
	}
	
	public function fe_menu($records = array()){
	
	//	Check input:
		if ( !is_array($records) ) { return ''; }
		if ( count($records) == 0) { return ''; }
	
	// 	Build menu:
		$a = array();
		foreach ($records as $row) {
			if ( $row['publish'] == 1){
				$attr = array();
				$attr['href'] 	= PATH_.ROUTE.'?id='.$row['ID'];
				$attr['target'] = "_self";
				$attr['title']  = $this->fieldReplace(TXT_VBNE_TITLE_OPEN, array('name' => strtolower($row['name'])));
				$a[]			= $this->wrapTag('a',$row['name'],$attr);
			}
		}
		return $this->wrapTag('nav',$a,array('class' => 'menu'));
	}
	

	/**
	 * Create an array with fields for introduction articles.
	 * @param array $fields {[FID_ARTICLE_CAT], [FID_ARTICLE]}
	 * @return array [] => { 
	 * 						[name]
	 * 						[content]
	 * 					  }
	 * In case no published articles are found: false is returned.
	 */
	public function fe_introduction($fields = array()){
		
		$introduction = false;
		
	//	Get $FID_ARTICLE_CAT, $FID_ARTICLE 
		$FID_ARTICLE_CAT 	= array_key_exists('FID_ARTICLE_CAT',  $fields)? (int)$fields['FID_ARTICLE_CAT'] : -1;
		$FID_ARTICLE 		= array_key_exists('FID_ARTICLE',  	   $fields)? (int)$fields['FID_ARTICLE'] 	 : -1;
			
	//	When FID_ARTICLE_CAT is an excisting value, but FID_ARTICLE not: get all articles from categorie.
		if ( $FID_ARTICLE_CAT > 0 && $FID_ARTICLE <= 0 ) {
				
			$articles = $this->model_article->get_records_by_category_all_fields($FID_ARTICLE_CAT);
			if ( count ($articles)> 0) {
				$introduction	= array();
				foreach ($articles as $article){
					$intro						= array();
					$intro['name'] 		    	= $article['name'];
					$intro['content'] 			= $article['content'];
					$introduction[] 			= $intro;
				}
			}
				
		}
			
	//	When FID_ARTICLE is an excisitng value get article.
		if ($FID_ARTICLE > 0 ) {
			$article = $this->model_article->ar($FID_ARTICLE);
			if ((int)$article['publish'] == 1){
				$introduction			= array();
				$intro					= array();
				$intro['name'] 		    = $article['name'];
				$intro['content'] 		= $article['content'];
				$introduction[] 		= $intro;
		
			}	
		}
		return $introduction;
		
	}


	public function html_stack_return_button(){
		

		
	//	Pop last entry from stack:
		$href =$this->stack_get();
		
	//	Return in case $href is false (not available on stack or last on stack is equal to current url):
		if ( !$href ) { return '';}
		
	//	Return html:
		$attr 				= array();
		$attr['class'] 		= 'button margin-bottom-20';
		$attr['href'] 		= $href.'&stack_pop';
		$attr['target'] 	= "_self";
		$attr['title'] 		= $this->fieldReplace( TXT_TITLE_RETURN) ;
		
		return $this->wrapTag('a',strtolower(TXT_LBL_BACK),$attr).'<div class="clearfix"></div>';
		
	}
	
	
	
	

} // END class View.
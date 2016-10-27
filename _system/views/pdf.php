<?php	// _system/views/pdf.php

class viewPdf extends view {
	
	//	Constants for TCPDF:
		private $PDF_PAGE_ORIENTATION 			= 'P'; 			// Page orientation (P=portrait, L=landscape).
		private $PDF_UNIT					 	= 'mm';			// Document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch].
		private $PDF_PAGE_FORMAT 				= 'A4'; 		// Page format.
		private $PDF_CREATOR 					= 'VBNE'; 		// Document creator.
		private $PDF_IMAGE_SCALE_RATIO 			= 1.25; 		// Height of cell respect font height.
		private $font							= 'helvetica';	// Font (should be installed on server).
		private $PDF_OUTPUT						= 'D';			// 'I': output in browswr 'D': dialog for download.
	
	//	Constants for TCPDF margins:
		private $PDF_MARGIN_HEADER 		= 5;
		private $PDF_MARGIN_FOOTER 		= 10;
		private $PDF_MARGIN_TOP 		= 22;
		private $PDF_MARGIN_BOTTOM 		= 25;
		private $PDF_MARGIN_LEFT 		= 15;
		private $PDF_MARGIN_RIGHT		= 15;
		
	//	Constants for view	
		private $pdf 		= null;					//	Instance of  extended TCPDF class
		private $style 		= '';					//	String contaning style rules for TDPDF->writeHTMLCell() layout. 
		private $pdf_css	= array();				//	Array contaning style rules pdf layout.
		private $dom 		= null;					//	Instance of dom class to read html.
		private $singles 	= ['img', 'br','hr'];	//  To reconize tags with no closing tag.
		private $blank 		= '';					//	Path to blank image (set in contruct).
		private $controller_calculation = null;		//	Instance fo controllerCalculation
		private $model_observation = null;
		
		private $calculation_ar = array();
		private $calculation_result = array();
		
	//	Public properties:
		public $title 		= '';
		public $author 		= '';
		public $date		= '';
		public $remarks 	= '';
		private $key_name   =  null;
		
	
	public function __construct(){
		
		$this->blank = ROOT_ENV.'_img'.DS.'_blank.png';
		
	//	Create instnace of 	extended TCPDF class
		$this->pdf = new  pdf($this->PDF_PAGE_ORIENTATION, $this->PDF_UNIT, $this->PDF_PAGE_FORMAT, true, 'UTF-8', false);
	
		
		$this->pdf->SetDisplayMode('real');
		
	//	set document information
		$this->pdf->SetCreator($this->PDF_CREATOR);
	//	$this->pdf->SetAuthor($this->author);
		$this->pdf->SetTitle(APPLICATION);
	//	$this->pdf->SetSubject($this->subject);
		$this->pdf->SetKeywords(TXT_VBNE_PDF_KEY_WORDS);
		
	//	Set margins
		$this->pdf->SetMargins($this->PDF_MARGIN_LEFT, $this->PDF_MARGIN_TOP, $this->PDF_MARGIN_RIGHT);
		$this->pdf->SetHeaderMargin($this->PDF_MARGIN_HEADER);
		$this->pdf->SetFooterMargin($this->PDF_MARGIN_FOOTER);
		
	//	Set auto page breaks
		$this->pdf->SetAutoPageBreak(TRUE, $this->PDF_MARGIN_BOTTOM);
		
	//	Set image scale factor
		$this->pdf->setImageScale($this->PDF_IMAGE_SCALE_RATIO);
		
	// 	Set default font subsetting mode
		$this->pdf->setFontSubsetting(true);
		$this->pdf->SetFont($this->font, '', 7, '', true);
		
		
	//	DOM interpreter:
		$this->dom = new dom();
		
		parent::__construct('pdf.html');
	
	}
	
	/**
	 * Set the location of the css file and assign it's content to the property "style", which is used
	 * for TDPDF->writeHTMLCell() layout.
	 * In case file doesn't excist, no action is taken and current style-property is returned.
	 * @param string $path: absolute path from ROOT to the css-file.
	 *                      Remark: use "DIRECTORY_SEPARATOR".
	 * @return:string
	 */
	public function set_style($path){
		
	//	Reset style-property:
		$this->style = '';
		
	//	Check path:
		if (is_null($path))    {return $this->style;}
		if (!is_string($path)) {return $this->style;}
	
	//	Check if file 
		$path = ROOT_ENV.trim($path);
		if (!file_exists($path) ){return $this->style;}
		
	//	Read file into string:
		$this->style = '<style>'.trim(file_get_contents($path)).'</style>';
		
		return $this->style;
	}
	
	/**
	 * Read a css file into an array property "ccs", which is used
	 * for layout of the pdf export.
	 * In case file doesn't excist, no action is taken and current css-property is returned.
	 * @param string $path: absolute path from ROOT to the css-file.
	 *                      Remark: use "DIRECTORY_SEPARATOR".
	 * @return:string
	 */
	public function set_css($path){
	
	//	Reset css-property:
		$this->ccs = array();
	
	//	Check path:
		if (is_null($path))    {return $this->css;}
		if (!is_string($path)) {return $this->css;}
	
	
	//	Read file into string:
		$ccs = new css($path);
		
		$this->pdf_ccs = $ccs->get_css_array();
		
		return $this->pdf_ccs;
	}
	
	

	
	
	/**
	 * Create an pdf export of the collected favorites
	 */
	public function pdf_favorites(){
		
		
	//	Set meta-data for header and footer:	
		$this->pdf->pdf_title 	= $this->title;
		$this->pdf->pdf_author 	= $this->author;
		
	//	Create first page:
		$O = $this->new_page(3);
		$x = $O['x'];
		$y = $O['y'];
		
	//	Header with meta-data:
		$this->meta($O);
		
	//	Get favorites:
		$records = $this->model_article->get_favorites('pdf');
		
	//	Write favorite articles:
	    $new_page 	= false;
	    
	    $this->pdf->SetX($O['x']);

	    $this->pdf->SetFont($this->font, '', 7);
	    $y 			= $this->pdf->GetY()+2;
		foreach($records as $record){
	 
			if ($new_page) {
				$this->pdf->AddPage();
				$y = $O['y'];
			}
			
			$this->pdf->SetX($O['x']);
			
		//	Write header:	
			$this->pdf->SetY($y);
			$this->pdf->SetFont($this->font, '', 12);
			$this->pdf->SetCellPaddings(1, 0.5,1, 0.5);
			$this->pdf->setColor('fill',163,175,38);
			$this->pdf->setColor('text',255,255,255);
			
			$this->pdf->writeHTMLCell(0,0,'','', $record['name'],0, 1,true,true,'L', true);
			
			$y = $this->pdf->GetY();
			$this->pdf->SetY($y);
			
		//	Write content:
			$this->write_article($record['content']);

			$new_page = true;
			
		}
	
	//	List with notes:
		$this->write_notes();
		
	//	List with referenes:
		$this->write_references();
		
	//	Create pdf: 	
		$dd = $this->pdf->pdf_created;
		$file_name = 'vbne_favorites_'.$dd->format('Ymd_His').'.pdf';
		$this->pdf->Output($file_name, $this->PDF_OUTPUT);
		exit;
		
	}
	
	public function pdf_report($ID){
		
	//	Create calculation controller:
		require_once CONTROLLERS.'calculation.php';
		$this->controller_calculation = new controllerCalculation();
		
	//	Set data of calculation:
		$this->calculation_ar			= $this->controller_calculation->model->get_ar($ID);
		$this->calculation_result		= $this->controller_calculation->calculation($this->calculation_ar);
		
	//	Build key name from categories:
		$categories 	= $this->controller_calculation->model->get_parent_categories_by_id($ID);
		$title = '';
		$glue = '';
		$lower_case = false;
		foreach ($categories as $category){
			$name = $lower_case? strtolower(trim($category['name'])): trim($category['name']);
			$title .= $glue.$name;
			$glue = ' - ';
			$lower_case = true;
		}
		$name = $lower_case? strtolower(trim($this->calculation_ar['name'])): trim($this->calculation_ar['name']);
		$title .= $glue.$name;
		$this->key_name = $title;
		if (  strlen(trim($this->title)) == 0 ){$this->title = $this->key_name ; }
		
	//	Set meta-data for header and footer:
		$this->pdf->pdf_title 	= $this->key_name;
		$this->pdf->pdf_author 	= $this->author;
		
	//	Create first page:
		$O = $this->new_page(3);
		$x = $O['x'];
		$y = $O['y'];
		
	
	
	//	Header with meta-data:
		$this->meta($O);
		
	//	Result:
		$this->write_result();
		
	//	Write observations:
		$this->new_page();
		$this->write_observations();
		
	//	$this->pdf->SetX($O['x']);
	//	$this->pdf->SetFont($this->font, '', 7);
		
	//	List with notes:
		$this->write_notes();
		
	//	List with referenes:
		$this->write_references();
	
	
	//	Create pdf:
		$dd = $this->pdf->pdf_created;
		$file_name = 'vbne_report_'.$dd->format('Ymd_His').'.pdf';
		$this->pdf->Output($file_name, $this->PDF_OUTPUT);
		exit;
	
	}
	
	private function write_article($html){
		
	//	Origin:
		$O = $this->pdf->pdf_get_page_top_left();
		
	//  Reset style:
		$this->pdf->SetFont($this->font, '', 7);
		$this->pdf->SetCellPadding(0);
		$this->pdf->setColor('text',0,0,0);
		
	//	Create  ellements node_array from content field:	
		$elements = $this->dom_create_ellements_array($html);
		
	//	Write loop:
		foreach($elements as $e) {
			
		
			
		//	Get text:
			$text = is_array($e['content'])? '' : trim($e['content']);
			
		//	Get class:
			$classes = array_key_exists('class', $e['attr'])? explode(' ',trim($e['attr']['class'])) : array();
		
			
		//	Get CSS rules:
			$css_rules = $this->css_simple_selecector($e['name'],$classes);
			
			
		//	Empty element: skip:	
			if ($e['name'] =='#text' && $text === '') {continue;}
			
		//	Write text:
			if ($e['name'] =='#text' ){
				$html = $this->style.$text;
				$this->pdf->writeHTMLCell(0,0,'','',$html,0, 1,false,true,'L', true);
				continue;
			}
			
		//	Write image:
			if ( in_array('img-frame', $classes)){
				
			//	Get immage:	
				
				$content = is_array($e['content'])? $e['content']: array();
				$src = $this->blank;
				$c = reset($content);
				while (strtolower($c['name'] )!== 'img'){
					$c = next($content);
					if ($c === false ) {break;}
				}
				
				$src		= array_key_exists('src', $c['attr'])? trim($c['attr']['src']) : $this->blank;
				
				$p 			= (int)strrpos($src,'.');
				$l 			= (int)strlen($src) - 1 -$p;
				$type 		= strtoupper(substr($src, $p+1,$l));
					
				$page_width = $this->pdf->getPageWidth();	
				$margins 	= $this->pdf->getMargins();
				$x 			= (int)$margins['left'];
				$w 			= (int)$page_width - (int)$margins['right']-$x;	
				$y 			= $this->pdf->getY()+3;
				
				$margin_top =  array_key_exists('margin-top', $css_rules)? (int)$css_rules['margin-top']: 0;
				$y = $this->pdf->getY()+$margin_top;
				
				$this->pdf->Image($src, $x, $y, $w, '', $type, '', 'T', true, 300, '', false, false, 0, false, false, false);
				
				$y = $this->pdf->getImageRBY();
				$this->pdf->SetY($y);
				continue;
			}
		
		//	Write image title:
			if ( in_array('figuur', $classes) ){
				
				$content = is_array($e['content'])? $e['content']: array();
				$txt = '';
				$c = reset($content);
				while ( is_array($c['content']) ){
					$c = next($content);
					if ($c === false ) {break;}
				}
				$txt = is_array($c['content'])? '' : trim($c['content']);
				
				$html = $this->style.'<p class="figuur"><span class="figuur">Figuur</span>&nbsp;'.$txt.'</p>';
				$this->pdf->writeHTMLCell(0,0,'','',$html,0, 1,false,true,'L', true);
				
				$margin_bottom =  array_key_exists('margin-bottom', $css_rules)? (int)$css_rules['margin-bottom']: 0;
				$this->pdf->SetY($this->pdf->getY()+$margin_bottom);
				continue;
			}
		//	Write icon-class:
			if ( in_array('icon', $classes) ){
				
			//	Get text:	
				$content = is_array($e['content'])? $e['content']: array();
				$txt = '';
				$c = reset($content);
				while ( is_array($c['content']) ){
					$c = next($content);
					if ($c === false ) {break;}
				}
				$txt = is_array($c['content'])? '' : trim($c['content']);
				
			//	Get settings for icon:
				$width 					= array_key_exists('width', $css_rules)? (int)$css_rules['width']: 0;
				$height 				= array_key_exists('height', $css_rules)? (int)$css_rules['height']: 0;
				$background_color 		= array_key_exists('background-color', $css_rules)? $css_rules['background-color']: '#FFFFFF';
				$background_image 		= array_key_exists('background-image', $css_rules)? trim($css_rules['background-image']): 'logo.svg';
				$margin_right 			= array_key_exists('margin-right', $css_rules)? (int)$css_rules['margin-right']: 0;
				
			//	Create icon-image:
				$image_file = ROOT_ENV.'_css'.DS.'img'.DS.$background_image ;
				$color = $this->color_code_to_rgb($background_color);
			
			//	Margin-top:
				
				$margin_top =  array_key_exists('margin-top', $css_rules)? (int)$css_rules['margin-top']: 0;
				$y = $this->pdf->GetY()+$margin_top;
				$this->pdf->SetY($y);
				$x = $O['x'];
				
			//	Place icon and text (in transaction: should be kept together)
				$this->pdf->startTransaction();
				
				//	Icon:	
					$this->pdf->Rect($x, $y, $width,$height ,'F',array(), $color);
					$this->pdf->ImageSVG($image_file, $x, $y, $width,$height , $link='', $align='', $palign='', $border=0, $fitonpage=false);
					
				//	Text:
					$html = $this->style.'<p>'.$txt.'</p>';
					$this->pdf->writeHTMLCell(0,0, $x+$width+$margin_right, $y,$html,0, 1,false,true,'L', true);
					
				if ($this->pdf->GetY() < $y ){
					
				//	Split over two pages: roll-back and create net page first	
					$pdf= $this->pdf->rollbackTransaction();
					$this->pdf = $pdf;
						
					$this->pdf->AddPage();
					$y = $O['y']+$margin_top;
					$this->pdf->SetY($y);
						
					//	Icon:
						$this->pdf->Rect($x, $y, $width,$height ,'F',array(), $color);
						$this->pdf->ImageSVG($image_file, $x, $y, $width,$height , $link='', $align='', $palign='', $border=0, $fitonpage=false);
							
					//	Text:
						$html = $this->style.'<p>'.$txt.'</p>';
						$this->pdf->writeHTMLCell(0,0, $x+$width+$margin_right, $y,$html,0, 1,false,true,'L', true);
						
				} else {
						$this->pdf->commitTransaction();
				}
				
				
			//	Margin-bottom:
				$margin_bottom =  array_key_exists('margin-bottom', $css_rules)? (int)$css_rules['margin-bottom']: 0;
				$y = $this->pdf->GetY()-$y > $height?  $this->pdf->GetY() : $y+$height;
				$this->pdf->SetY($y+$margin_bottom);
				continue;
				
			}
	
		//	Write default:
			if (is_array($e['content'])){
				
				$this->pdf->startTransaction();
				$y_start = $this->pdf->GetY();
				
			//	Openings-tag with attibutes:	
				$attr = '';
				$glue = ' ';
				foreach ($e['attr'] as $name=> $value){
					$attr .=$glue.$name.'="'.$value.'" ';
					$glue = '';
				}
				$html='<'.$e['name'].$attr.'>';
					
			//	Nested body:	
				$html .= $this->elements_to_html_nest($e['content']);
				
			//	Close-tag:	
				$html.='</'.$e['name'].'>';
				
			//	Add style:	
				$html = $this->style.$html;
			
			//	Margin-top:
				$y = $this->pdf->GetY();
				$margin_top =  array_key_exists('margin-top', $css_rules)? (int)$css_rules['margin-top']: 0;
				$this->pdf->SetY($y+$margin_top);
				
				$this->pdf->writeHTMLCell(0,0,'','',$html,0, 1,false,true,'L', true);
				
			//	Margin-bottom:
				$margin_bottom =  array_key_exists('margin-bottom', $css_rules)? (int)$css_rules['margin-bottom']: 0;
				$y = $this->pdf->GetY();
				
				if ($y<$y_start) {
					
					$pdf= $this->pdf->rollbackTransaction();
					$this->pdf = $pdf;
					
					$this->new_page();
					
					//	Openings-tag with attibutes:
					$attr = '';
					$glue = ' ';
					foreach ($e['attr'] as $name=> $value){
						$attr .=$glue.$name.'="'.$value.'" ';
						$glue = '';
					}
					$html='<'.$e['name'].$attr.'>';
						
					//	Nested body:
					$html .= $this->elements_to_html_nest($e['content']);
					
					//	Close-tag:
					$html.='</'.$e['name'].'>';
					
					//	Add style:
					$html = $this->style.$html;
						
					//	Margin-top:
					$y = $this->pdf->GetY();
					$margin_top =  array_key_exists('margin-top', $css_rules)? (int)$css_rules['margin-top']: 0;
					$this->pdf->SetY($y+$margin_top);
					
					$this->pdf->writeHTMLCell(0,0,'','',$html,0, 1,false,true,'L', true);
					
					//	Margin-bottom:
					$margin_bottom =  array_key_exists('margin-bottom', $css_rules)? (int)$css_rules['margin-bottom']: 0;
					$y = $this->pdf->GetY();
					
				} else {
					$this->pdf->commitTransaction();
				}
				$y = $this->pdf->GetY();
				$this->pdf->SetY($y+$margin_bottom);
			}
			
		}
	}
	
	private function write_notes(){
	
		if (count($this->model_article->notes)>0){
			
		//	Header on new page:	
			$this->new_page();
			
			$this->pdf->SetFont($this->font, '', 12);
			$this->pdf->SetCellPaddings(1, 0.5,1, 0.5);
			$this->pdf->setColor('fill',163,175,38);
			$this->pdf->setColor('text',255,255,255);
			
			$this->pdf->writeHTMLCell(0,0,'','', TXT_VBNE_LBL_NOTES,0, 1,true,true,'L', true);
		
		//	Create table with notes
			$y = $this->pdf->GetY();
			$this->pdf->SetY($y);
			$this->reset_style();
			
			$rows= array();
			foreach ($this->model_article->notes as $record){
				$this->addReplacement('record', $record);
				$rows[] =$this->getHtml('pdf.note.html');
			}
			$html = $this->style.$this->wrapTag('table',$rows);
			$this->pdf->writeHTMLCell(0,0,'','', $html,0, 1,true,true,'L', true);
			
		}
		return;
	}
	
	private function write_references(){
	
		if (count($this->model_article->references)>0){
			
		//	Header on new page:
			$this->new_page();
			
			$this->pdf->SetFont($this->font, '', 12);
			$this->pdf->SetCellPaddings(1, 0.5,1, 0.5);
			$this->pdf->setColor('fill',163,175,38);
			$this->pdf->setColor('text',255,255,255);
			
			$this->pdf->writeHTMLCell(0,0,'','', TXT_VBNE_LBL_REFERENCES,0, 1,true,true,'L', true);
			
			$y = $this->pdf->GetY();
			$this->pdf->SetY($y);
			$this->reset_style();
			
		//	Create table with references
			$rows= array();
			foreach ($this->model_article->references as $record){
				$this->addReplacement('record', $record);
				$rows[] =$this->getHtml('pdf.reference.html');
			}
			$html = $this->style.$this->wrapTag('table',$rows);
		  //$html = $this->style.array_pretty_print($this->model_article->references);
				
			$this->pdf->writeHTMLCell(0,0,'','', $html,0, 1,true,true,'L', true);
				
			
		}
	}

	private function write_observations(){
		
	
	//	Model observation
		require_once MODELS.'observation.php';
		$this->model_observation = new modelObservation();
		
	//	Header:	
		$y 	= $this->pdf->GetY()+2;
		$this->pdf->SetY($y);
		$this->pdf->SetFont($this->font, '', 12);
		$this->pdf->SetCellPaddings(1, 0.5,1, 0.5);
		$this->pdf->setColor('fill',163,175,38);
		$this->pdf->setColor('text',255,255,255);
		$this->pdf->writeHTMLCell(0,0,'','', TXT_VBNE_LBL_OBSERVATIONS,0, 1,true,true,'L', true);
		
	//	Introduction:
		/*
		$content = array();
		$html = "";
		if ( is_null($this->calculation_ar['FID_ARTICLE']) === false) {
			$introduction = $this->fe_introduction( array('FID_ARTICLE'=>(int)$this->calculation_ar['FID_ARTICLE']) );
			if ($introduction !== false) {
				foreach($introduction as $row){
					$content[] = $this->wrapTag('h3',$row['name']);
					$content[] = $this->wrapTag('div',htmlspecialchars_decode($row['content'],ENT_HTML5));
				}
				$html = $this->wrapTag('div',$content);
			}
		}
		if ( is_null($this->calculation_ar['FID_ARTICLE']) && count($content) == 0) {
			if (!is_null($this->calculation_ar['description']) && $this->calculation_ar['description']!= ''){
				$html = $this->wrapTag('div',htmlspecialchars_decode($this->calculation_ar['description'],ENT_HTML5));
			}
		}
		if ($html !== ""){
			$this->reset_style();
			$this->write_article($html);
		}
		*/
		
	//	Inputs:	
		$this->reset_style();
		foreach ($this->calculation_ar['inputs'] as $input){
			
			$y = $this->pdf->GetY();
			$this->pdf->startTransaction();
			$this->write_observation($input);
			if ($this->pdf->GetY()< $y) {
				$pdf= $this->pdf->rollbackTransaction();
				$this->pdf = $pdf;
				$this->new_page();
				$this->write_observation($input);
			} else {
				$this->pdf->commitTransaction();
			}
		}
		return;
	}
	private function write_result(){
		
		$score = $this->calculation_result['score'];
		
		$O = $this->pdf->pdf_get_page_top_left();
		$x = $O['x'];
		
		$y = $this->pdf->GetY()+2;
		
		$width 		= 5;
		$height 	= $width;
		$color 		= $this->color_code_to_rgb($score['color']);
		$image_file = $image_file = ROOT_ENV.'_css'.DS.'img'.DS.'icon_score.svg';
		
	//	Icon:
		$this->pdf->Rect($x+0.1, $y+0.1, $width-0.2,$height-0.2 ,'F',array(), $color);
		$this->pdf->ImageSVG($image_file, $x, $y, $width,$height , $link='', $align='', $palign='', $border=0, $fitonpage=false);
			
	//	Text:
		$this->pdf->SetFont($this->font, '', 10);
		$this->pdf->setColor('text',163,175,38);
		$title = htmlspecialchars_decode($score['name']);
		
		$this->pdf->writeHTMLCell(0,0, $x+$width+1, $y,$title,0, 1,false,true,'L', true);
		
	//	Conclusion:	
		$FID_ARTICLE = (int)$score['FID_ARTICLE'];
		
		if ($FID_ARTICLE> 0){
			
			$article = $records = $this->model_article->ar($FID_ARTICLE,'pdf');
			
			$this->reset_style();
			
			$this->pdf->SetFont($this->font, 'B', 8);
			$this->pdf->setColor('text',0,0,0);
			$y = $this->pdf->GetY()+4;
			
			$title = $article['name'];
			$this->pdf->writeHTMLCell(0,0, $x, $y,$title,0, 1,false,true,'L', true);
			
			$y = $this->pdf->GetY()+1;
			$this->reset_style();
			$this->write_article($article['content']);
		
		}
		
	//	Articles:
		$this->reset_style();
		
		$this->pdf->SetFont($this->font, '', 12);
		$this->pdf->setColor('text',163,175,38);
		$y = $this->pdf->GetY()+4;
		$this->pdf->writeHTMLCell(0,0, $x, $y,TXT_VBNE_LBL_ARTICLES_BACKGROUND,0, 1,false,true,'L', true);
		
	
		$fid_articles = $this->calculation_result['fid_articles'];
	
		
		foreach ($fid_articles as $FID){
			
			$article = $records = $this->model_article->ar($FID,'pdf');
				
			$this->reset_style();
				
			$this->pdf->SetFont($this->font, 'B', 8);
			$this->pdf->setColor('text',0,0,0);
			$y = $this->pdf->GetY()+4;
				
			$title = $article['name'];
			$this->pdf->writeHTMLCell(0,0, $x, $y,$title,0, 1,false,true,'L', true);
				
			$y = $this->pdf->GetY()+1;
			$this->reset_style();
			$this->write_article($article['content']);
			
		}
		
	//	Analysis:	
		$fid_calculations = $this->calculation_result['fid_calculations'];
		
		$this->reset_style();
		
		$this->pdf->SetFont($this->font, '', 12);
		$this->pdf->setColor('text',163,175,38);
		$y = $this->pdf->GetY()+4;
		$this->pdf->writeHTMLCell(0,0, $x, $y,TXT_VBNE_LBL_CALCULATIONS_NEXT,0, 1,false,true,'L', true);
		$y = $this->pdf->GetY()+4;
		$model_calultaions = new modelCalculation();
		foreach ($fid_calculations as $FID){
			
			$calc = $model_calultaions->ar($FID);
				
			
		
			$this->reset_style();
		
			$this->pdf->SetFont($this->font, 'B', 8);
			$this->pdf->setColor('text',0,0,0);
			
		
			$title = $calc ['name'];
			$this->pdf->writeHTMLCell(0,0, $x, $y,$title,0, 1,false,true,'L', true);
			$y = $this->pdf->GetY();
				
		}
		
	}
	
	private function write_observation($input){
		
		if (array_key_exists($input['input'],$this->calculation_result['stack'])){
			
			$O = $this->pdf->pdf_get_page_top_left();
			$x = $O['x'];
			
			
		    $FID_OBSERVATION	= (int)$input['FID_OBSERVATION'];	
			$obs				= $this->model_observation->get_ar( $FID_OBSERVATION);
			$type 				= array_key_exists('type', $obs)? $obs['type'] : '#NA';
			$name 				= htmlspecialchars($obs['name']);
			$value 				= $this->calculation_result['stack'][$input['input']];
		
		//	Name
			$y 	= $this->pdf->GetY();
			$this->pdf->SetY($y+8);
			$this->pdf->SetFont($this->font, 'B', 7);
			$this->pdf->setColor('text',0,104,136);
			$this->pdf->writeHTMLCell(0,0,'','', $name,0, 1,true,true,'L', true);
			
			$this->reset_style();
			$y 	= $this->pdf->GetY();
		
		//	Description:
			$description  = '<div>'.trim($obs['description']).trim($input['description']).'</div>';
			$description  = $this->model_article->content_decode($description ,'pdf');
			$description  = htmlspecialchars_decode($description );
			
			$this->pdf->SetY($y)+1;
			$this->pdf->writeHTMLCell(0,0,'','', $this->style.$description,0, 1,true,true,'L', true);
			
		
		//	Observation:
			$obs_html = false;
			switch($type) {
				case  'value': 
					$value = floatval($value);
					$obs_html = '<div class="obs-value">'.TXT_VBNE_PDF_OBS_VALUE.':&nbsp;VALUE.</div>';
				break;
				
				case  'radio':
					$indx		= (int)$value -1;
					$options 	= $this->model_observation->options_get($FID_OBSERVATION);
					$h = 3;
					foreach ($options as $opt_indx =>  $option){
						$y = $this->pdf->GetY()+$h-1;
						
						
						
					//	Icon:
						$img = (int)$opt_indx ===$indx? 'radio_checked.svg' :'radio_unchecked.svg';
					
						$image_file = ROOT_ENV.'_css'.DS.'img'.DS.'ce'.DS.$img ;
						$this->pdf->ImageSVG($image_file, $x, $y, $h,$h , $link='', $align='', $palign='', $border=0, $fitonpage=false);
							
					//	Text:
						$html = $this->style.'<p>'.$option['name'].'</p>';
						$this->pdf->writeHTMLCell(0,0, $x+$h+1, $y,$html,0, 1,false,true,'L', true);
					}
				
				break;
				
				case  'check':
					$options 	= $this->model_observation->options_get($FID_OBSERVATION);
					$h = 3;
					foreach ($options as $opt_indx =>  $option){
						$y = $this->pdf->GetY()+$h-1;
						
						
						
					//	Icon:
						$indx = $opt_indx+1;
						in_array($indx, $value);
						$img = in_array($indx, $value)? 'check_checked.svg' :'check_unchecked.svg';
					
						$image_file = ROOT_ENV.'_css'.DS.'img'.DS.'ce'.DS.$img ;
						$this->pdf->ImageSVG($image_file, $x, $y, $h,$h , $link='', $align='', $palign='', $border=0, $fitonpage=false);
							
					//	Text:
						$html = $this->style.'<p>'.$option['name'].'</p>';
						$this->pdf->writeHTMLCell(0,0, $x+$h+1, $y,$html,0, 1,false,true,'L', true);
					}
					$$obs_html = '<div class="obs-value">Ingevulde waarde:&nbsp;CHECK.</div>';
				break;
				
				default: //#NA
				break;
			}
			
			if ($obs_html !== false){
				$y 	= $this->pdf->GetY();
				$this->pdf->SetY($y+1);
				$this->pdf->writeHTMLCell(0,0,'','', $this->style.$obs_html,0, 1,true,true,'L', true);
			}
				
		
		}
		return;
		
	}
	/**
	 * @todo: Implement only in case description layout should be changed.
	 */
	private function htm_description($elements) {
		
	
		$html = '<div>';
		foreach ($elements as $element ){
			switch ( strtolower(trim($element['name'])) ){
				case 'img':
				case 'hr':
				break;
				
				case 'br':
				$html .='<br/>';
				break;
				
				default:
				$html .='<span';
				if ($element['name'] === 'span') {
					$attr = '';
					foreach ($element['attr'] as $k =>$v){
						$attr .=' '.$k.'="'.$v.'"';
					}
					$html .= $attr === ''? '>' : $attr. ' >';
				} else {
					$html .= ' class="'.$element['name'].'"';
				}
				$content =  is_array($element['content'])? $element['content'] : array(0=>array('content'=>$element['content']));
				if ( count($content) === 1 ){
					$html .= $content[0]['content'];
				} else {
					$html .= $this->htm_description($content);
				}
				$html .=$element['name'] === 'span'? '</span>' : '</span><br/>';
				break;
				
			}
		}
		return $html.'</div>';
	}
	
	private function dom_create_ellements_array($html){
		
	//	Check html:
		if (!is_string($html)){return array();}
		
	//	Create node_array from content field:
		$this->addReplacement('html', $html);
		$html = $this->getHtml('pdf.html');
		$node_array = $this->dom->create_node_array($html);
		
	//	Get elements from body:
		$elements = count($node_array) == 1? $node_array[0] : array();
		$elements = array_key_exists('content',$elements)? $elements['content'] : array();
		$elements = count($elements) == 1? $elements[0] : array();
		$elements = array_key_exists('content',$elements)? $elements['content'] : array();
		
		return $elements;
	}
	
	private function new_page($margin_top = 0){
		
		
		
	//	Create new page:
		$this->pdf->AddPage();
		
	//	Read origin:	
		$O = $this->pdf->pdf_get_page_top_left();
		
	//	Set page to left top:
		$this->pdf->SetX($O['x']);
		$this->pdf->SetY($O['y']+$margin_top);
		
		return $O;
		
	}
	
	private function elements_to_html_nest($elements){
		
		$return = '';
		
		foreach ($elements as $e){
			
			if ($e['name'] =='#text'){
				$return .= $e['content'];
				continue;
			}
			$attr = '';
			$glue = ' ';
			foreach ($e['attr'] as $name=> $value){
				$attr .=$glue.$name.'="'.$value.'" ';
				$glue = '';
			}
			$return .='<'.$e['name'].$attr;
			if (in_array($e['name'],$this->singles)){
				$return .='/>';
				continue;
			}
			$return .='>';
			$return .= is_array($e['content'])? $this->elements_to_html_nest($e['content']) : $e['content'];
			$return .='</'.$e['name'].'>';
		}
		
		
		
		return $return;
	}
		
	private function meta($O){
		
	//	Meta-data:
		$this->pdf->SetFont($this->font, 'B', 12);
		$this->pdf->Cell(0,0,$this->title,0,'L', false,'',0, false,'T','B');
		$this->pdf->writeHTMLCell(0,0,'','', $this->title,0, 0,false,true,'L', true);
		
		if (!is_null($this->key_name)) {
			
			$this->pdf->SetX($O['x']);
			$y = $this->pdf->GetY()+5;
			$this->pdf->SetY($y);
			$this->pdf->SetFont($this->font, '', 7);
			$this->pdf->writeHTMLCell(0,0,15,'', TXT_VBNE_LBL_KEY,0, 0,false,true,'L', true);
			
			$this->pdf->SetY($y);
			$this->pdf->SetX($O['x']+20);
			$this->pdf->SetFont($this->font, '', 7);
			$this->pdf->writeHTMLCell(0,0,'','', $this->key_name,0, 0,false,true,'L', true);
		}
		
		
		$this->pdf->SetX($O['x']);
		$y = $this->pdf->GetY()+5;
		$this->pdf->SetY($y);
		$this->pdf->SetFont($this->font, '', 7);
		$this->pdf->writeHTMLCell(0,0,15,'', TXT_VBNE_PDF_AUTHOR,0, 0,false,true,'L', true);
		
		$this->pdf->SetY($y);
		$this->pdf->SetX($O['x']+20);
		$this->pdf->SetFont($this->font, '', 7);
		$this->pdf->writeHTMLCell(0,0,'','', $this->author,0, 0,false,true,'L', true);
		
		$y = $this->pdf->GetY()+4;
		$this->pdf->SetY($y);
		$this->pdf->SetX($O['x']);
		$this->pdf->SetFont($this->font, '', 7);
		$this->pdf->writeHTMLCell(0,0,15,'', TXT_VBNE_PDF_dd,0, 0,false,true,'L', true);
		
		$this->pdf->SetY($y);
		$this->pdf->SetX($O['x']+20);
		$this->pdf->SetFont($this->font, '', 7);
		$dd = $this->pdf->pdf_created;
		$date = $this->date !=''?$this->date:$dd->format('d-m-Y');
		$this->pdf->writeHTMLCell(0,0,'','', $date,0, 0,false,true,'L', true);
		
		$y = $this->pdf->GetY()+4;
		$this->pdf->SetY($y);
		$this->pdf->SetX($O['x']);
		$this->pdf->SetFont($this->font, '', 7);
		$this->pdf->writeHTMLCell(0,0,15,'', TXT_VBNE_PDF_REMARKS,0, 0,false,true,'L', true);
		
		$this->pdf->SetY($y);
		$this->pdf->SetX($O['x']+20);
		$this->pdf->SetFont($this->font, '', 7);
		$dd = $this->pdf->pdf_created;
		$this->pdf->writeHTMLCell(0,0,'','', $this->remarks,0, 0,false,true,'L', true);
		
		$h = $this->pdf->getLastH();
		
		$y = $this->pdf->GetY()+$h+4;
		$this->line($y);
		
	}
	
	private function line($y){
		
		$this->pdf->SetLineStyle(array('width' => 0.05 , 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(163,175,38)));
		$this->pdf->SetY($y);
		$O = $this->pdf->pdf_get_page_top_left();
		$this->pdf->SetX($O['x']);
		$this->pdf->writeHTMLCell(0,0,'','' ,'','T', 0,false,true,'L', true);
	}
	
	private function css_simple_selecector($element, $classes = array()){
		
	//	Check parameters:
		if ( !is_string($element) )	{ return array();}
		if ( !is_array($classes)  ) { return array();}
		
		$e = strtolower(trim($element));
		
	//	Match on plain element:	
		$match = array_key_exists($e, $this->pdf_ccs)? $this->pdf_ccs[$e] : array();
		
	//	Match on element with one or more classes:
		foreach ($classes as $class){
			
		//	Match on class:
			$match = array_key_exists('.'.$class, $this->pdf_ccs)? array_merge($match,$this->pdf_ccs['.'.$class]) 			: $match;
			
		//	Match on e.class:
			$match = array_key_exists($e.'.'.$class, $this->pdf_ccs)? array_merge($match, $this->pdf_ccs[$e.'.'.$class])  	: $match;	
		}
		
	//	Match on combined classesd:
		$combined_classes ='.'.implode('.',$classes);
		$match = array_key_exists($combined_classes, $this->pdf_ccs)? array_merge($match,$this->pdf_ccs[$combined_classes])		: $match;
		
		return $match;
		
	}
	
	private function color_code_to_rgb($code){
		
		$code = strtoupper(trim($code));
		
		$colors = str_split($code);
		$rgb= array();
		
		
		switch ((int)strlen($code)){
			
			case 7:
			$rgb[] =hexdec($colors[1].$colors[2]);
			$rgb[] =hexdec($colors[3].$colors[4]);
			$rgb[] =hexdec($colors[5].$colors[6]);
			break;
			
			case 4:
			$rgb[] =hexdec($colors[1].$colors[1]);
			$rgb[] =hexdec($colors[2].$colors[2]);
			$rgb[] =hexdec($colors[3].$colors[3]);
			break;
				
			default:
			$rgb[] = 0;
			$rgb[] = 0;
			$rgb[] = 0;
			break;
			
		}
		
		return $rgb;
		
	}
	
	private function reset_style(){
	//  Reset style:
		$this->pdf->SetFont($this->font, '', 7);
		$this->pdf->SetCellPadding(0);
		$this->pdf->setColor('fill',255,255,255);
		$this->pdf->setColor('text',0,0,0);
	}
	
}

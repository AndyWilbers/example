<?php	//	_system/models/article.php
			defined('BONJOUR') or die;		
			
				
class	modelArticle extends  model {
	
	private $system_categories = null;
	
	private $models = array();
	
	public $link_ar = array();
	
	
	public $ar_new = array();
	
	public $notes 		= array();
	public $references 	= array();
		
	public function __construct() {
		
			$this->root_path = 'articles';
			$this->return_path = HOME_.'article/';
		
			parent::__construct();
			
		  	//	set active table to "article":
				$this->active_table_set('article');
				
			//	set category table:
				$this->categories = new table('article_cat');
			
			//  Default settings for a new active record:
				$this->ar_new['ID'] 		= "new";
				$this->ar_new['name'] 		= "";
				$this->ar_new['path'] 		= "";
				$this->ar_new['content'] 	= "";
				$this->ar_new['FID_CAT'] 	= -1;
				$this->ar_new['publish'] 	= -1;
				
				
				
			
				
				require_once MODELS.'image.php';
				$this->models['image']  = new modelImage();
				
				require_once MODELS.'calculation.php';
				$this->models['calculation']  = new modelCalculation();
				
				require_once MODELS.'note.php';
				$this->models['note']  = new modelNote();
				
				require_once MODELS.'reference.php';
				$this->models['reference']  = new modelReference();
				
			
			
	} // END __construct(). 
	
	public function ar($pk='', $type = 'html'){
		$record = parent::ar($pk);
		if (array_key_exists('content', $record)){
				$record['content']  = htmlspecialchars_decode($record['content'],ENT_HTML5);
				$record['content'] =  $this->content_decode($record['content'],$type);
		}
		return $record;
		
	}
	
	
	

	public function get_records_by_fid($FID = -1){
		
		$FID = (int)$FID;
		if ( $FID < 1) {
			if ($FID !== -1) {return array();}
		}
		
		$options              = array();
		$options['transpose'] = false;
		$options['where']	  = ' `FID_CAT`="'.$FID.'" AND `publish`="1"';
		$records =  $this->active_table->select_fields(['name','content'],$options);
		if ($records === false ) {return array();}
		
		foreach ($records as $row => $fields) {
			$records[$row]['content']  = htmlspecialchars_decode($fields['content'],ENT_HTML5);
			$records[$row]['content'] = $this->content_decode($fields['content']);
		}
		return $records;
		
	}
	
   /**
	* Gives the ID's of of the available system categories in 
	* an associotive array [system] => [ID]
	* @param boolean 'load': if true: the array is loaded from DB
	*/
	public function get_system_categories($load = false){
		
		if ($this->system_categories === null || $load ){
			
			$sql 	= 'SELECT `SYSTEM_CAT`, `ID` FROM `article_cat` WHERE `APP`= "'.APP.'" AND `SYSTEM_CAT` IS NOT NULL';
			$rows = $this->select($sql);
			$this->system_categories = null;
			if (count($rows) !== 0) {
				$this->system_categories = array();
				foreach ($rows as $row){
					$this->system_categories[$row['SYSTEM_CAT']] = $row['ID'];
				}
			}
		}
		return $this->system_categories;
	}
	
   /**
	* Lookup the CATEGORY_ID by a valid system category name.
	* @param string system_category_name.
	* @return a CATEGORY_ID or false.
	*/
	public function get_system_category_id ( $system_category_name ){
		
		$name = trim( strtolower($system_category_name) );
		$names = $this->get_system_categories();
		if ($names === null) { return false; }
		return array_key_exists($name, $names)?  (int)$names[$name]: false;
		
	}
	
	/**
	 * Gives the record by a valid system category name.
	 * @param string system_category_name.
	 * @return an associative array. or false
	 */
	public function get_system_category ( $system_category_name ){
		
	//	Get ID of $system_categorie:
		$ID = $this->get_system_category_id($system_category_name);
		if ( $ID === false ) { return false; }
		
	//	Get article_cat record
		return $this->categories->ar($ID);
		
	
	}
	
   /**
    * Gives name and content field set of system articles ordered by postion, name.
    * @param string  system_category_name
    * @param bool    all: (optional, default: false) all | published only.
    * @return array  $rows => { [name],[content]}
    */
	public function get_system_articles ($system_category_name, $all = false){
		
	//	Get CATEGORY_ID:
		$CATEGORY_ID = $this->get_system_category_id($system_category_name);
		if ($CATEGORY_ID === false) { return array();}
		
	//	Get articles
		$fields  = array('ID','name', 'content');
		$options = array();
		$options['transpose'] 	= false;
		$options['where']	 	= '`FID_CAT` ="'.$CATEGORY_ID.'"';
		$options['where']	   .= $all? '' : ' AND `publish`=1';
		$options['orderby']	 	= '`position`, `name`';
		$records =  $this->active_table->select_fields($fields, $options);
		foreach ($records as $key=> $record) {
			if (array_key_exists('content', $record)){
				$records[$key]['content'] = $this->content_decode($record['content']);
			}
			
		}
		return $records;
	
	}
	
	
	/**
	 * Encodes images and internal links:
	 * {image;ID}
	 * {link;article|calculation|reference|note|url;ID}
	 *
	 */
	public function content_encode($content=''){
	
	//	Check if there is content:
		$content = trim($content) ;
		if ($content  == '')	 {return $content;}
	
		$content = $this->content_encode_image($content);
		$content = $this->content_encode_link($content);
	
		return $content;
	
	}
	
	
	/**
	 * Replaces all 'img-tags' with attribute data-image-id="ID" by {image;ID}
	 * @param string $content
	 */
	public function content_encode_image($content){
	
	//	Split content in parts:
		$parts = preg_split(PATTERN_ENCODE_IMAGE, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	
	//	Rebuild content with encoded images:
		$content = '';
		$skip = false;
		foreach ($parts as $part){
			if ($part == '<img') {
				//	img-tag found: skip until 'data-image-id="' is found:
				$skip = true;
				continue;
			}
			if (str_starts('data-image-id="', $part)){
				//	'data-image-id="' is found:
				$find_id = explode('"', $part);
				$ID = count($find_id)>1? (int)$find_id[1] : -1;
				if ($ID > 0) {
					$content .= '{image;'.$ID.'}';
				}
				$skip = false;
				continue;
	
			}
			if (!$skip){
				$content .=$part;
			}
		}
		return $content;
	
	}
	
	/**
	 * Replaces all 'a-tags' with attribute data-link-id="type;ID" by {link;type;ID}
	 * @param string $content
	 */
	public function content_encode_link($content){
	
	//	Split content in parts:
		$parts = preg_split(PATTERN_ENCODE_LINK, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	
	//	Rebuild content with encoded links:
		$return =$content;
		$content 	= '';
		$skip 		= false;
		$other_link ='';
		$encode 	= false;
		
		foreach ($parts as $part){
			
			if ($part == '<a') {
				$other_link = $part;
				$skip = true;
				continue;
			}
			if ($skip) {
				$other_link .= $part;
				
				$elements = preg_split(PATTERN_ENCODE_LINK_SEP, $part );
				
				$check = count($elements) == 3;
				if ($check) {
					$check = in_array($elements[0], ['article','calculation','note','reference']);
				}
				
				if ($check) {
					$check = (int)$elements[1] > 0;
				}
				
				if ( $check) {
					$content .= '{link;'.$elements[0].';'.(int)$elements[1].';'.$elements[2].'}';
					$encode   = true;
					continue;
				}
				
				if ($part == '</a>') {
					if ($encode === false) {
						$content .= $other_link;
					}
					$skip 	= false;
					$encode = false;
					continue;
					
				}
				continue;
			}
		
			$content .=$part;
	
		}
		return $content;
			
	
	}
	
	/**
	 * Decodes {image;ID} {link;article|calculation|reference|note|url;ID}
	 * into image tag or internal link
	 */
	public function content_decode($content='',$type = 'html'){
	
	//	Check if there is content:
		$content = trim($content) ;
		if ($content  == '')	 {return $content;}
	
		$content = $this->content_decode_image($content, $type);
		$content = $this->content_decode_link($content, $type);
	
		return $content;
	
	}
	
	public function content_decode_image($content, $type = 'html'){
		
		require_once MODELS.'image.php';
		$model_image = new modelImage();
		
		$view = new view();
			
	//	Split content in parts:
		$parts = preg_split(PATTERN_DECODE_IMAGE, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		$content ='';
		foreach ($parts as $part){
	
			if (str_starts('{image;', $part)){
					
					
			//	Get id:
				$find_id = explode(';', substr($part, 1,-1));
				$ID = count($find_id)>1? (int)$find_id[1] : -1;
				if ($ID > 0) {
					$img = $model_image->ar($ID);
					if ((int)$img['ID'] === (int)$ID) {
						
						switch (strtolower(trim($type))) {
							
							case 'pdf':
							$attr 	= array();
							$attr['src'] = $img['url_pdf'];
							break;
							
							
							default:
							$resolution = array_key_exists('IMAGE_LR', $_SESSION)? $_SESSION['IMAGE_LR'] : 'sr';
							
							$attr 					= array();
							$attr['src'] 			= $resolution =='sr'? $img['url_sr']: $img['url_lr'];
							$attr['alt'] 			= $img['alt'];
							$attr['data-ext'] 		= $img['type'];
							$attr['data-image-id'] 	= $img['ID'];
							if ($resolution !='sr') {
								$attr['title'] 	= TXT_TITLE_IMG_LOAD;
							}
							break;
						}
						$content .=$view->wrapTag('img','', $attr);
				
					}
				}
				
				continue;
	
			}
	
			$content .=$part;
	
		}
		return $content;
	}
	
	
	
	public function content_decode_link($content,$type = 'html'){
		
	
	
		$view = new view();
			
	//	Split content in parts:
		$parts = preg_split(PATTERN_DECODE_LINK, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		
	
		$content = '';
	
		foreach ($parts as $part){
			
			 
			if (str_starts('{link;', $part)){
					
			//	Get model and id:
				$find_id = explode(';', substr($part, 1,-1));
				
				$model_name = count($find_id)>1? $find_id[1] : 'unknown';
				if ($model_name == 'article' && !array_key_exists('article', $this->models)){
					require_once MODELS.'article.php';
					$this->models['article']  = new modelArticle();
				}
				
				
				if ( !array_key_exists($model_name, $this->models)) { continue;}
				
				$ID = count($find_id)>2? (int)$find_id[2] : -1;
				if ( $ID <1) { continue;}
				
			
			//	Get active record:
				$model= $this->models[$model_name];
				$aR = $model->get_ar_with_path_to_record($ID);
				if ((int)$aR['ID'] !== (int)$ID) { continue;}
				$text = count($find_id)>3? trim($find_id[3]) : '';
				$text = $text == ''? $aR['name'] : $text;
	
			//	Build link:
				$attr 					= array();
				$pdf_type = '';
				switch ($model_name){
					case 'article':
					$attr['href'] = PATH_.$aR['href'];
					$attr['title'] 	= $aR['name'];
					break;
						
					case 'calculation':
					$attr['href'] = PATH_.'sleutels/calculaties/'.$aR['href'];
					$attr['title'] = $aR['name'];
					break;
					
					default:
					$attr['href'] = $aR['href'];
					$attr['title'] = $model_name  == 'reference'? $aR['author'].', '.$aR['YYYY'].', '.$aR['name'].', '.$aR['pages'].'.': $aR['name'].": ".$aR['description'];
					$pdf_type = $model_name  == 'reference'? 'reference': 'note';
					if (strtolower(trim($type)) == 'pdf') {
						if ($model_name  == 'reference') {
							$this->references[$aR['ID']] = $aR;
						}else {
							$this->notes[$aR['ID']] = $aR;
						}
					}
					break;
							
				}
				if (strtolower(trim($type)) == 'pdf') {
					
					switch ($pdf_type) {
						
						case 'reference':
						case 'note':
						$prefix =$pdf_type=='reference'? 'r':'b';
						$ref =$view->wrapTag('span','&nbsp;['.$prefix.$aR['ID'].']', array('class'=>$pdf_type));
						$c = (int)$text === (int)$aR['ID']?$ref : $text.$ref;
						$content .= $c;
						break;
						
						default:
						$content .= $text;
						break;
					}
					
					
					
				} else {
					
					$attr['target'] 		= '_self';
					$attr['data-link-id'] 	= $model_name.';'.$aR['ID'];
					$this->link_ar = $attr;
					$content .=$view->wrapTag('a',$text, $attr);
						
				}
				
				continue;
			}
			$content .=$part;
	
		}
		return $content;
	}	
	
	public function get_favorites($type= 'html'){
		
		$favorites = $this->favorites();
		if (count($favorites) == 0)  {return array();}
		
		$IN = '`ID` IN("'. implode('","',$favorites).'")';
		
		$records = $this->active_table->select_all(array('where'=>$IN));
		foreach ($records as $row => $fields) {
			$records[$row]['content']  = htmlspecialchars_decode($fields['content'],ENT_HTML5);
			$records[$row]['content'] = $this->content_decode($records[$row]['content'], $type);
		}
		return $records;
		
	}
	
	
	
} // END class modelArticle
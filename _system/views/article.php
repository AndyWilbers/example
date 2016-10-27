<?php	// _system/views/article.php

class viewArticle extends view {
	
	public function __construct($template = 'form.article.html'){
		
		$this->has_category_record_structure 	= true;
		$this->label							= TXT_VBNE_LBL_ARTICLE;
		$this->label_plu 						= TXT_VBNE_LBL_ARTICLES;
		$this->root_path 						= 'articles/';
		$this->record_name						= "article";
		
		parent::__construct($template);
		
		$this->set_inputfield_path();
		
	}
	
	
}

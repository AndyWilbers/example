<?php	// _system/views/tools.php

class viewTools extends view {
	
	public function __construct($template = 'tool.info.html'){
		
		parent::__construct($template);
		
		$this->addReplacement('msg', '<!--no message-->');
		$this->addReplacement('raw', '');
		$this->addReplacement('log', '');
		
	}
	
	
	public function csv_html_menu($files){
	
		$a = array();
		foreach ($files as $file=>$file_name) {
			$attr= array();
			$attr['href'] = PATH_.'admin/tools/csv_import/'.$file;
			$attr['target'] = '_self';
			$attr['class'] = "block";
			$a[] =$this->wrapTag('a',$file_name,$attr);
		}
		return $this->wrapTag('div',$a);
	
	}
	
	public function csv_html_convert($content = array() ) {
		
	//	Add message or warning:
		if ( array_key_exists('msg', $content) ){
			$attr = array();
			$attr['class'] = "max-width-600";
			$msg = $this->fieldReplace($content['msg']);
			$html = $this->wrapTag('p',$msg,$attr);
			$this->addReplacement('msg', $html);
		} 
		if ( array_key_exists('warning', $content) ){
			$attr = array();
			$attr['class'] = "max-width-600 obn-red";
			$msg = $this->fieldReplace($content['warning']);
			$html = $this->wrapTag('p',$msg,$attr);
			$this->addReplacement('msg', $html);
		}
		
	// 	Create overview of available RAW csv-files.
		if ( array_key_exists('raw', $content) ){
			$html = array();
			$html[]='<h3>'.TXT_VBNE_READER_RAW_FILES.'</h3>';
			$li = array();
			foreach ($content['raw'] as $file_name) {
				$li[]= $this->wrapTag('li',$file_name);
			}
			$html[]=$this->wrapTag('ul',$li);
			$raw = $this->wrapTag('section',$html);
			$this->addReplacement('raw', $raw);
		}
		
	// 	Create overview of available system tables:
		if ( array_key_exists('system_tables', $content) ){
			$html = array();
			$html[]='<h3>'.TXT_VBNE_READER_SYSTEM_TABLES.'</h3>';
			$li = array();
			foreach ($content['system_tables'] as $table=>$ID) {
				$li[]= $this->wrapTag('li',$table.'<span class="sub">ID_NEXT:'.$ID.'</span>');
			}
			$html[]=$this->wrapTag('ul',$li);
			$html = $this->wrapTag('section',$html);
			$this->addReplacement('system_tables', $html);
		
		}
		
		$flow =  array_key_exists('flow', $content)?  $content['flow'] :'';
		$this->addReplacement('flow', $flow);
		return $this->getHtml('tools.csv_convert.html');
	
	}
	
	public function html_delete_all_records(){
		return $this->getHtml('tools.delete.html');
	
	}
	
	
}

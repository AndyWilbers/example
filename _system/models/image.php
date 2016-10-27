<?php	//	_system/models/image.php
			defined('BONJOUR') or die;		
			
				
class	modelImage extends  model {
	
	
	private $structure   = null;
    private $extentions  = array();
    private  $image_path   = PATH;
	
		
	public function __construct() {
		
			
		
			parent::__construct();
			
			$this->extentions = [ 'png','svg'];
			
			
		  	//	set active table to "image":
				$this->active_table_set('image');
				
			//	set category table:
				$this->categories = new table('article_cat');
			
			//  Default settings for a new active record:
				$this->ar_new['ID'] 		= "new";
				$this->ar_new['path'] 		= null;
		
			
	} // END __construct(). 
	
	public function set_image_path($path = PATH){
		$this->image_path = trim($path);
		return $this->image_path;
	}
	public function get_image_path(){
		return $this->image_path;
	}
	
	public function ar($pk=""){
		$record = parent::ar($pk);
		$record['url_lr']  = $this->thumb_url($record);
		$record['url_sr']  = $this->img_url($record);
		$record['url_pdf'] = $this->img_url_pdf($record);
		return $record;
	}
	
	public function save_ar($fields= array(), $ID = null) {
		$fields['dir']  = $fields['dir']== ''? '/' : $fields['dir'];
		$fields['pos']  = $this->next_position($fields['dir']);
		$check = parent::save_ar($fields, $ID );
		if  ($check === false)  {return false;}
		return $this->active_table->ar();
	}
	
	public function save_alt ($records = array()){
		
	//	Stop in case of incorrect $record array
		if (!is_array($records))  { return false;}
		if (count($records)  == 0){ return false;}
		
		$return = array();
		foreach ($records as $key=> $alt) {
			$ID = (int)str_replace('ID', '', $key);
			if ($ID <1) {continue;}
			
			$fields = array();
			$alt = trim($alt);
			$fields['alt']   = $alt!=''? $alt : null;
			if( parent::save_ar($fields, $ID ) !== false) {
				$aR = $this->active_table->ar();
				$return['ID'.$aR['ID']] = $aR['alt'];
			}
		}
		return $return;
	}
	
	public function move($records = array(), $dir =''){
		
	//	Stop in case of incorrect $record array
		if (!is_array($records))  { return false;}
		if (count($records)  == 0){ return false;}
		
		$dir .='/';
		
		$path_destination = str_replace('/', DS, IMG.$dir);
		
		foreach ($records as $ID){
			
		//	Get current record:
			$aR = $this->ar($ID);
			
			$path_current = $aR['dir']=='/'? IMG : IMG.$aR['dir'];
			$path_current = str_replace('/', DS, $path_current);
			$img = 'img'.$ID.'.'.$aR['type'];
			
			
		//	Move file(s):
			rename ($path_current.$img,$path_destination.$img);
			if ($aR['type'] == 'jpeg') {
				$img = 'img'.$ID.'.'.'png';
				rename ($path_current.$img,$path_destination.$img);
			}
		
		//	Update record:
			$fields = array();
			$fields['dir']  = $dir;
			$this->save_ar($fields,$ID);
		
		}
		return;
		
	}
	
	/**
	 * Gives the content of an image direcory in an array.
	 * @param string $dir: directory path in image-directory (sub-directory of "IMG").
	 * @return array ['crumbs'] => {[name], [dir]}
	 * 				 ['items'] => [name] => [href]
	 * 				 ['thumbs'] => [name]=> [dir]
	 */
	public function get_directory_content($dir=''){
		

		$return = array();
		
	//	Create list with thumbs:
		$return['thumbs'] = $this->read_thums($dir);
		
	//	Read sub-directories:
		$return['items'] = $this->read_sub_directories($dir);
		
	//	Get crumbs:
		$paths = $this->convert_dir_to_paths($dir);
		$return['crumbs'] =$paths['crumbs'];

		return $return;
		
	}
	
	/**
	 * Re=number the pos field within a directory path and returns the next free pos.
	 * @param string $dir:
	 * @return number
	 */
	public function next_position($dir){
		
		$options = array();
		$options['where'] = '`dir`="'.trim($dir).'"';
		$options['orderby'] = '`pos`';
		
		$pos = 1;
		$records = $this->active_table_select_all($options);
		foreach ($records as $key => $record){
			parent::save_ar(array('pos'=> $pos), $record['ID'] );
			$pos++;
		}
		return $pos;
		
	}
	
	
	
	
	/**
	 * Creates absolute path for filesystem, relative path for url and array with crumb
	 * @param (string) dir: relative url like dir
	 * @return (array) ['url'] ['fs'] ['crumbs']=> {['name'], ['dir']}
	 */
	public function convert_dir_to_paths($dir){
		
		$return 				= array();
		$return ['crumbs']   	= array();
		$return['url'] 			= '';
		$glue_url 				= '';
		$return['fs'] 			= '';
		$glue_fs 				= '';
		
		
	//	Split dir
		$dir = $dir==''? array() : explode('/',$dir);
		
	//	Build:
		$crumb 				= array();
		$crumb['name'] 		= TXT_VBNE_LBL_IMAGES;
		$crumb['dir']  		= '';
		$return['crumbs'][] = $crumb;
		
		foreach ($dir as $name) {
		
		//	Url:	
			$return['url']	   .= $glue_url.$name;
			$glue_url 	   		= '/';
		
		//	fs:
			$return['fs']  	   .= $glue_fs.$name;
			$glue_fs 	   		= DS;
		
		//	Crumb:
			$crumb 				= array();
			$crumb['name'] 		= $name;
			$crumb['dir']  		= $return['url'];
			$return['crumbs'][] = $crumb;
			
		}
		return $return;
		
	}
	
	public function read_sub_directories($dir){
		
	//	Get path to dir:
		$paths = $this->convert_dir_to_paths($dir);
		$path  = $paths['url']!=''? $paths['url'].'/': '';
		
	//	Create full path filesystem dir	
		$dir = IMG.str_replace('/', DS, $dir);
		
		$result = array();

		$handle = dir($dir);
		while (false !== ($file_name = $handle->read())) {
						
		//	skip system directories:
			if (in_array($file_name , ['.','..']) ){ continue; }
	
		//	Type: file or directory:
			if (is_dir($dir.DS.$file_name) ){
				$result[$file_name]= $path.$file_name;
			}
		}
		$handle->close();
		return $result;
	}
	
	public function read_thums($dir){
		
		$result = array();
	
		$options = array();
		$options['where'] = '`dir`="'.trim($dir).'/"';
		$options['orderby'] = '`pos`';
		$records = $this->active_table_select_all($options);
		foreach ($records as $key => $record){
			$result[$record['ID']] = $this->thumb_url($record);
		}
		return $result;
	
	}	
	
	
	private function thumb_url($record){
		$ext = $record['type'] == 'svg'? '.svg' :'.png';
		$app_dir  = APPLICATION ==''? 'vbne' :APPLICATION;
		$dir =  $record['dir'] =='/'? '':$record['dir'];
		return $this->image_path.'_img/'.$app_dir.'/img/'.$dir.'img'.$record['ID'].$ext;
	}
	
	private function img_url($record){
		$app_dir  = APPLICATION ==''? 'vbne' :APPLICATION;
		$dir =  $record['dir'] =='/'? '':$record['dir'];
		return $this->image_path.'_img/'.$app_dir.'/img/'.$dir.'img'.$record['ID'].'.'.$record['type'];
	}
	
	private function img_url_pdf($record){
		$app_dir  = APPLICATION ==''? 'vbne' :APPLICATION;
		$dir =  $record['dir'] =='/'? '':$record['dir'];
		return ROOT_ENV.'_img'.DS.$app_dir.DS.'img'.DS.$dir.'img'.$record['ID'].'.'.$record['type'];
	}
	
	public function get_records_by_dir ($dir =''){
		$options = array();
		$options['where'] = '`dir`="'.trim($dir).'/"';
		$options['orderby'] = '`pos`';
		$records = $this->active_table_select_all($options);
		foreach ($records as $key => $record){
			$records[$key]['thumb_url'] = $this->thumb_url($record);
		}
		return $records;
	}
	
	
	public function get_directory_structure($dir = IMG){
		
		$return = array();
		
		$handle = dir($dir);
		while (false !== ($name = $handle->read())) {
		
		//	skip system directories:
			if (in_array($name , ['.','..']) ){ continue; }
		
		//	Type: file or directory:
			if (is_dir($dir.DS.$name) ){
				$return [$name] = $this->get_directory_structure($dir.DS.$name);
			}
		}
		$handle->close();
		return $return;
		
	}
	
	private function convert_ds_to_directory_options($ds, $pre= '', $return = array()){
		
		$pre = $pre==''? $pre: $pre.'/';
		
		foreach ($ds as $name=>$sub){
			$return[$pre.$name] =$pre.$name;
			if (count($sub) > 0 ) {
				$return = $this->convert_ds_to_directory_options($sub,$pre.$name,$return);
			}
		}
		return $return;
		
	}
	
	public function get_directory_options($dir = IMG) {
		$ds = $this->get_directory_structure($dir);
		return $this->convert_ds_to_directory_options($ds);
	}
}

























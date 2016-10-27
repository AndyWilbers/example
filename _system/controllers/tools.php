<?php	//	_system/controllers/tools.php
			defined('BONJOUR') or die;
	

class controllerTools extends controller {
	

	
	private $csv_import_dir = null;
	private $csv_files  = null;
		
	public function __construct(){
		
			parent::__construct();
			
			$this->csv_import_dir = SERVER_PATH.'_csv/import';
		
		//	Create view-instance:
			require_once VIEWS.'tools.php';
			$this->view = new viewTools();
			$this->view->addCss("_css/tools.css");
			
		//	Create model-instance:
			require_once MODELS.'tools.php';
			$this->model = new modelTools();
			
			
		/*
			$list = array (
					array('aaa', 'bbb', 'ccc', 'dddd'),
					array('123', '456', '789'),
					array('"aaa"', '"bbb"')
			);
			
			$fp = fopen(SERVER_PATH.'_csv/export/test.csv', 'w');
			
			foreach ($list as $fields) {
				fputcsv($fp, $fields, ";" , '"' , "\\" );
			}
			
			fclose($fp);
			*/
			
		/*	
			
			//	Select respond-method:
				$method =  count($this->request) >1? $this->request[1] : 'start';
				switch ($method ){
				
					case "csv": $this->respond_method = "csv"; break;
						
						
					default:    $this->respond_method = "start"; break;
				}
		*/
			return;
			
		
	}
	
	public function show_page() {
		
	//	Start method :
		$page =  count($this->request) > 2? $this->request[2] : "info";
		switch ($page) {
	
			case 'info':
			return $this->view->getHtml('tools.info.html');
			break;
			
			case 'csv_import':
			$file_name = count($this->request) > 3? $this->request[3] : null;	
			return $this->csv_import($file_name);
			break;
			
			case 'csv_convert':
			return $this->csv_convert();
			break;
			
			case 'csv_export':
			return $this->csv_export();
			break;
			
			case 'delete':
			return $this->delete_all_records();
			break;
	
			default:
			return $this->view->getHtml('404.html');	
			break;
	
		}
		return $this->view->getHtml('404.html');
	}
	
	private function csv_import($file_name = null){
		
	//	Set execute-flag, is case false: the import is evaluated only and not executed.
		$execute = false;
		if (count($this->request) >4) {
			$execute= $this->request[4] == "execute"? true : false;
		}
		
	//	Get available file-names:
		$files = $this->model->csv_get_files();
			
	//	Get menu
		$menu = $this->view->csv_html_menu($files);
		$this->view->addReplacement("menu", $menu);
		
		
	//	When no file_name is specified: show menu with files to import:
		if ( $file_name === null) {
			return $this->view->getHtml('tools.import.html');
		}
		
	//	Read file into an array:
		$result = $this->model->csv_get_file($file_name);
		if ($result  === false ) {	
			$attr =array();
			$attr['class'] = "obn-red";
			$msg = $this->view->wrapTag('p',$this->model->msg,$attr);
			$this->view->addReplacement('msg', $msg);
			return $this->view->getHtml('tools.import.html');
		}
		$model = $result['model'];
		$rows = $result['rows'];
	
	//	[4] Import or evaluate csv-file:
		$check = $model->csv_import($rows, $file_name, $execute);
		if ($check['error']) {
			$attr =array();
			$attr['class'] = "obn-red";
			$msg = $this->view->wrapTag('p',$this->model->msg,$attr);
			$this->view->addReplacement('msg', $msg);
			return $this->view->getHtml('tools.import.html');
		}
		
	
		
	//	Success: create report:	
		$this->view->addReplacement('file_name', $file_name);
		$this->view->addReplacement('check', $check);
		
	//	Show results in case of an execured import:
		if ($execute) {
			$log = '';
			if ((int)$check['count']['total']['fail']>0) {
				$log = '<h3>Error loging:</h3>';
				$log .= $this->view->htmlTable($check['log']);
				$this->view->addReplacement('log', $log);
			}
			return $this->view->getHtml('tools.import.csv_result.html');
		}
		
		
		
	//	Add check-data in case of evalution:
		$html_table = array();
		$html_table['rows']= $check['target'];
		$target = $this->view->htmlTable($html_table);
		$this->view->addReplacement('target', $target);
		
		$import = $this->view->htmlTable($check['import']);
		$this->view->addReplacement('import', $import);
		$skip   = $this->view->htmlTable($check['skip']);
		$this->view->addReplacement('skip', $skip);
		return $this->view->getHtml('tools.import.csv_evaluation.html');
	
	}
	

	private function csv_convert(){
		
	//	Content array will be send to the view:	
		$content = array();
		
	//	Check is there is an reader for this application available
		if (!file_exists(READERS.APPLICATION.'.php') ) {
			$content['warning'] = TXT_VBNE_READER_NOT_AVAILABLE;
			return $this->view->csv_html_convert($content);
		}
		require_once READERS.APPLICATION.'.php';
		$reader_name = "reader".APP;
		$reader = new $reader_name();
		$content['msg'] = TXT_VBNE_READER_WELCOME;
	
	//	Load system-talbes info from tool model:
		$content['system_tables'] = $this->model->system_tables;
	
	//	Load RAW files:
		$raw = $reader->get_raw_files();
		$content['raw'] = $raw;
		if (count($raw) == 0 ) {
			$content['warning'] = TXT_VBNE_READER_NO_RAW_FILES;
			unset($content['raw']);
			return $this->view->csv_html_convert($content);
		}
		
	//	Start convert-flow:
		$flow = $reader->flow();
		if ($flow === false) {
			$content['warning'] = $reader->msg;
			return $this->view->csv_html_convert($content);
		}
		$content['flow']  = $flow;
		return $this->view->csv_html_convert($content);
		
	}
	
	private function delete_all_records(){
		if (count($this->request) >3) {
			$execute= $this->request[3] == "execute"? true : false;
		}else {
			return $this->view->html_delete_all_records();
		}
		$result = $this->model->delete_application();
		$msg = $this->model->msg;
		if ($result !== false) {
			$msg = array_pretty_print($result);
		}
		$this->view->addReplacement('msg', $msg );
		return $this->view->getHtml('tools.delete.completed.html');
	}
	
		private function csv_export(){
			
		//	Export application:
			$csv_files = $this->model->csv_export_application_tables();
			
			$html = array();
			
		//	Create cvs export and link for download
			foreach ($csv_files as $name=>$href)  {
				
				
				if ($name !== false) {
					$attr= array();
					$attr['href'] 		= $href;
					$attr['download'] 	= $name.'.csv';
					$attr['target'] 	= '_blank';
					$html[] = $this->view->wrapTag('a',$name.'.csv', $attr).'<br/>';
				}
				
			}
			return $this->view->wrapTag('div',$html );
		}
	
}














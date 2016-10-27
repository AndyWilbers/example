<?php	// _system/classes/pdf.php

require_once(VENDOR.'TCPDF-master'.DS.'tcpdf.php');

class pdf extends TCPDF {
	
	
	public 	$pdf_title= null;
	private $pdf_border = 0;
	public 	$pdf_author= null;
	
	private $pdf_ccs_array = array();
	private $pdf_style_file = null;
	
	
	private $pdf_top_left = null;
	
	public  $pdf_created='';
	
	public function __construct(){
		
		
		$this->pdf_style_file = ROOT_ENV.'_css'.DS.'pdf.css';
		
		$this->pdf_created = new DateTime('now',timezone_open('Europe/Berlin'));
		
	
		parent::__construct();
	
	}
	/**
	 * Gives the coordinates of the top-left page-origin.
	 * @return array {x,y}
	 */
	public function pdf_get_page_top_left(){
		
		if ( is_null($this->pdf_top_left) ) {
			
			$this->pdf_top_left = array();
		
		//	Read margins:
			$margins = $this->getMargins();
			
		//	Get coordinates:
			$this->pdf_top_left['x']  = $this->getRTL()? $margins['right']:  $margins['left'];
			$this->pdf_top_left['y']  = $margins['top'];
	
		}
		return $this->pdf_top_left;
		
	}
	
	/**
	 * Set the location of the css file used for pdf creation. In casse file
	 * doesn't excist, no action is taken and false is returned.
	 * @param string $path: absolute path from ROOT to a css file, default "_css/pdf.css".
	 *                      Remark: use "DIRECTORY_SEPARATOR".
	 * @return:bool
	 */
	public function pdf_set_css( $path = null){
		
		if (is_null($path))    {return false;}
		if (!is_string($path)) {return false;}
		
		$path = ROOT_ENV.trim($path);
		if (!file_exists($path) ){return false;}
		
		$this->pdf_style_file = $path;
		return true;
	}
	
	/**
	 * Gives absolute path to the css file used for pdf creation.
	 * @return:string
	 */
	public function pdf_get_css(){
		
		return $this->pdf_style_file;
		
	}
	
	public function pdf_get_tags(){
		return $this->pdf_tags;
		
	}
	
	public function pdf_css_to_array(){
		$css = file_get_contents($this->pdf_style_file);
		
		
	}
	
	public function pdf_explode_html($html = null){
		
	//	Check html:	
		if (is_null($html))    {return array();}
		if (!is_string($html)) {return array();}
		
	//	Split on tags:
		$tag = '('.implode('|',$this->pdf_tags).')';
		$parts = preg_split(pattern_html_tag($tag), $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		
	//	Create result; do not include the double split delimeter elements:
	    $to_remove = $this->pdf_tags;
	    array_push($to_remove, '/');
	   
	    $return = array();
	    $is_tag_open = false;
		foreach ($parts as $part){
			
			$pdf->startTransaction();
			$pdf->AddPage();
			$y = $margins['top'];
			$pdf->SetY($y);
			$title = $this->view->wrapTag('h3',TXT_VBNE_LBL_NOTES, array('class'=>'title'));
			$pdf->writeHTMLCell(0, 0, '', '', $this->style.$title, 0, 1, 0, true, '', true);
			if (array_key_exists('rollback', $_REQUEST)){
				$pdf= $pdf->rollbackTransaction();
			} else {
				$pdf->commitTransaction();
			}
			
			
			if ( !in_array(strtolower($part),$to_remove) ){$return[]= $part;}
		}
		
		return  $return;
		
		
		
		
	}

	
	
//	OVERWRITES================================================================================
	public function Header() {
		
		
		$this->SetFont('helvetica', '', 8);
		$this->setColor('text',163,175,38);
	//	Logo
		$image_file = K_PATH_IMAGES.'obn.png';
		if ($this->rtl) {
			$x = $this->original_rMargin;
		} else {
			$x = $this->original_lMargin;
		}
		$this->Image($image_file, $x, 10, 50, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
		$imgx = $this->getImageRBX()+5;
		
	 // Title
		$title = !is_null($this->pdf_title)? trim($this->pdf_title) :'-';

		$this->SetX($imgx);
		$this->Cell(0, 14, $title, $this->pdf_border, false, 'L', 0, '', 0, false, 'M', 'B');
		
	// 	Print an ending header line
		$imgy = $this->getImageRBY();
		$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(163,175,38)));
		$this->SetY((2.835 / $this->k) + max($imgy, $this->y));
		if ($this->rtl) {
			$this->SetX($this->original_rMargin);
		} else {
			$this->SetX($this->original_lMargin);
		}
		$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
	}

		public function Footer() {
		// 	Position at 15 mm from bottom
			$this->SetY(-15);
			
		//	Set font
			$this->SetFont('helvetica', '', 6);
		
		//	Page numbwr
			$page_nb = (int)trim($this->getAliasNumPage());
			$pagenumtxt = TXT_PDF_PAGE.' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages();
			if ($this->getRTL()) {
				$this->SetX($this->original_rMargin);
				
			} else {
				$this->SetX($this->original_lMargin);
				
			}
			$this->Cell(40,0, $pagenumtxt, $this->pdf_border, false, 'L', 0, '', 0, false, 'T', 'M');
			
		//	Name and time stamp:
			$now = $this->pdf_created;
			$dd =  TXT_PDF_EXPORT_DD.' '.$now->format('Y-m-d H:i:s');
			
			
			if (strlen($this->pdf_author)>0){
				$dd.= ', '.strtolower(TXT_PDF_BY).': '.$this->pdf_author;
			} 
			if ($this->getRTL()) {
				$this->SetX(-$this->original_lMargin);
				
			
			} else {
				
				$this->SetX(-$this->original_rMargin);
			}
			$this->Cell(0,0, $dd, $this->pdf_border, false, 'R', 0, '', 0, false, 'T', 'M');
			
			
			$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(163,175,38)));
			$this->SetY(-15);
			if ($this->rtl) {
				$this->SetX($this->original_rMargin);
			} else {
				$this->SetX($this->original_lMargin);
			}
			$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
		
	}
}
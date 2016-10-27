<?php	// _system/classes/dom.php


class dom {
	
	
	private $html 			= null;
	private $dom 			= null;
	private $nodes 			= null;
	private $node_array 	= null;
	
	
	/**
	 * Constructor for dom class.
	 * @param string $html (optional). When set, the html-property is loaded during construction.
	 */
	public function __construct($html = null){
		
	//	Get instance of DOM:
		$this->dom = new DOMDocument;
	
		
	//	Read $html node:
		if ( !is_null( $html) ){ $this->set_html($html);}
		return;
	}
	
	/**
	 * Gives the content of the html-propery.
	 * @return string
	 */
	public function get_html(){
		return $this->html;
	}
	
	/**
	 * Set the html-property.
	 * @param string $html : html code;
	 * @return html-property
	 */
	public function set_html($html){
		
	//	Read $html node:
		if (is_null($html)) 	{return;}
		if (!is_string($html))	{return;}
		$html = trim($html);
		
		$this->dom->loadHTML($html);
		$this->nodes = $this->dom->getElementsByTagName('html');
	
		
		$this->html= $html;
		return;
		
	}
	
	/**
	 * Get the current node_array-property. 
	 * When the node_array-property is not set, it will be created
	 * using the html-property.
	 */
	public function get_node_array(){
		
	//	Create node array using current html-propery in case node_array-property is not yet loaded:
		if (is_null($this->node_array) ){
			return $this->create_node_array();
		}
		return $this->node_array;
	}
	
	
	/**
	 * Creates an array of the nodes from the html-property. 
	 * @param $html (optional): loads the html-property of the dom-instance before creation of the array.
	 * @return	nested array:
	 * 			[name]: 	(string) name of node.
	 * 			[attr]: 	array with attributes in name=>value.
	 * 			[content]: 	(string) | (array) child nodes. 
	 */
	public function create_node_array($html = null){
		
	//	Read $html node:
		if ( !is_null($html) ){ $this->set_html($html);} 
	    if (  is_null($this->html) ){ return array();} 
		
	//	Create node  array by recursive call:
		$result = $this->read_html_recursive($this->nodes );
		
	//	Set node array and return;	
		$this->node_array =  $result;
		
		return $this->node_array;
		
	}
	
	private function read_html_recursive($nodes){
		
	//	Create for each node in nodes an element array:
		
		$result = array();
		
		if (!is_object($nodes)) { return array();}
		
		$nb = $nodes->length;
		for ($i = 0; $i < $nb; $i++) {
			
		//	Get node item:	
			$node= $nodes->item($i);
			
		//	Build element array:
				$element = array();
		
			//	Name:
				$element['name'] = $node->nodeName;
			
			//	Attributes:
				$attr = array();
				if (is_object($node->attributes)){
					for ($j = 0; $j < (int)$node->attributes->length; $j++) {
							$name = $node->attributes->item($j)->name;
							$value = $node->attributes->item($j)->value;
							$attr[$name] = $value;
					}
				}
				$element['attr'] = $attr;
				
			//	Content: child-nodes or text:
				$child_nodes = $node->childNodes;
				$children = $this->read_html_recursive($child_nodes);
				
				switch (count($children) ){
					case 0:
					$element['content'] =$node->nodeValue;
					break;
				
					default:
					$element['content'] = $children;
					break;
				}
				
				
				
		//	Assign to $result.
			$result[] = $element;
			
		}
		
		return $result;
		
	}
	

}
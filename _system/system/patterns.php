<?php	//	_system/system/patterns.php
			defined('BONJOUR') or die;	

		//	encode and decode article content:
			define('PATTERN_ENCODE_IMAGE',			'/(<img|data-image-id="[0-9]+"\s*\/>)/m'                      );
			define('PATTERN_DECODE_IMAGE',			'/(\{image;[0-9]+\})/m'                                       );
			
	
			define('PATTERN_ENCODE_LINK',			'/(<a|data-link-id\s*=\s*"|<\/a>)/m'                           );
			define('PATTERN_ENCODE_LINK_SEP',		'/(\s*;\s*|\s*>\s*)/m'                           			  );
			define('PATTERN_DECODE_LINK',			'/(\{link;article;[0-9]+;[^}]*\}|\{link;calculation;[0-9]+;[^}]*\}|\{link;note;[0-9]+;[^}]*\}|\{link;reference;[0-9]+;[^}]*\})/m'                                          );
			
		//	replacements in pdf:
			define('PATTERN_PDF_P',					'/(<p[^\>]*\>|<\/p\>)/m'   );
			define('PATTERN_PDF_P_OPEN',			'/(<p[^\>]*\>)/m'          );
			define('PATTERN_PDF_P_CLOSE',			'/(<\/p\>)/m'     		   );
			define('PATTERN_PDF_CLASS',				'/(class="[^"]*")/m'   );
			
			
		//	HTML tag-functions:
			function pattern_html_tag_has_close_tag($tag){
				$tag = strtolower(trim($tag));
				$singles = ['img','br', 'hr'];
				return !in_array($tag,$singles);
			}
			function pattern_html_tag($tag){
				$tag = strtolower(trim($tag));
				$tag_close = pattern_html_tag_has_close_tag($tag)? $tag: '';
				return '/(<'.$tag.'[^\>]*\>|<\/'.$tag_close.'\>)/mi';
			}
			
			function pattern_html_tag_open($tag){
				$tag = strtolower(trim($tag));
				return	'/(<'.$tag.'[^\>]*\>)/mi';
			}
			
			function pattern_html_get_open_tag($tag, $subject){
				$regexp = pattern_html_tag_open($tag);
				$matches = array();
				if ( preg_match($regexp, $subject,$matches, PREG_OFFSET_CAPTURE) ){
					return $matches;
				}
				return false;
			}
			
			function pattern_html_tag_close($tag){
				$tag = strtolower(trim($tag));
				return	'/(<\/'.$tag.'\>)/mi' ;
			}
			
			
			
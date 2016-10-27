<?php	//	_system/language/loader.php
			defined('BONJOUR') or die;
		
		//	Get names of language files:
			$files =	scandir (ROOT_LANGUAGES.LANGUAGE);
		
		//	load language files:		
			foreach ($files as $file) {
				if (strtolower(substr($file,-4,4)) === '.php') {
					require_once ROOT_LANGUAGES.LANGUAGE.DS.$file;
				}
			}
<?php	defined('BONJOUR') or die;
//	_system/functions/loader.php

/*	Description:scans the directory where this file is located and 'loads' 
	all .php filesby a 'requiere_once' statement.
*/

	$files =	scandir (dirname(__FILE__));
	
	foreach ($files as $file) {
		if (strtolower(substr($file,-4,4)) === '.php' && $file !==basename(__FILE__)) {
			require_once $file;
		}
	}
<?php	//	_system/system/constants.php
			defined('BONJOUR') or die;	

		//	PATHs:
			define('PATH_INTRO',							'intro');
			define('PATH_INFO',			    				'info');
			define('PATH_INFO_INDEX',						'info/inhoud');
			define('PATH_INFO_NOTE',						'info/begrippen');
			define('PATH_INFO_REFERENCE',					'info/literatuur');
			define('PATH_INFO_REPORT',	    				'info/rapportage');
			define('PATH_CALCULATIONS',	    				'sleutels');
			define('PATH_CALCULATIONS_OBSERVATIONS',	    'sleutels/observaties');
			define('PATH_CALCULATIONS_CALCULATIONS',	    'sleutels/calculaties');
			
			
			
		//	Flags for system categories:
			define('SYSTEM_CAT_INTRO',							'intro');
			define('SYSTEM_CAT_INDEX',							'index');
			define('SYSTEM_CAT_HELP',							'help');	
			define('SYSTEM_CAT_SLEUTELS',						'sleutels');
			define('SYSTEM_CAT_OBSERVATIES',					'observaties');
			define('SYSTEM_CAT_CALCULATIES',					'calculaties');
			define('SYSTEM_CAT_ACCOUNT',						'account');
			
		//	Names of menus:
			define('MENU_FE_INFO',								'fe-info');
			define('MENU_FE_CALCULATIONS',						'fe-calculations');
			define('MENU_FE_ACCOUNT',							'fe-account');
			
			
		//	Names of $_SESSIONS:
			define('SES_FAVORITES',								'favorites');
			
			
		//	Google maps default:
		
			define('GEO_CENTRE_LAT',						52.0655);
			define('GEO_CENTRE_LNG',						5.2585);
			define('GEO_ZOOM',								9);
		
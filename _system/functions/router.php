<?php 	//_system/functions/router.php

	defined('BONJOUR') or die;


	/**
	 * Directs route in case active ROUTE starts with given $route
	 * 
	 * @param 	$route to check on, do not use an end "/".
	 * @return  void
	 */
	function router_direct($route = null) {
		if ($route === null) { return; }
		$route  = strtolower(trim($route)).'/';
		if ( str_starts($route, ROUTE.'/') ) {
			$routPath = ROUTES.'_'.str_replace('/', '_', $route).'php';
			if ( file_exists($routPath) ) {
				require_once $routPath;
				exit;
			}
		}
		return;
	}
	
	/**
	 * Checks if active ROUTE starts with given $route
	 *
	 * @param 	$route  or array with routes to check; do not use an end "/".
	 * @return  boolan
	 */
	function router_check($route = null) {
		if ($route === null) { return; }
		
		$routes = array();
		if ( is_array( $route ) ) {
			$routes = $route;
		} else {
			
			$routes = array();
			$routes[] = $route;
		}

		$route = reset($routes);
		do {
			$result = str_starts(strtolower(trim($route)).'/', ROUTE.'/') ? true : false;
			$route = next($routes);
		} while ($result === false && $route !== false );
			
		return  $result;
		
	}	
	
	
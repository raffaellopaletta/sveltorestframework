<?php

/*
 * This file is part of the sveltorestframework\sveltorestframework.
 *
 * Copyright (c) 2015 Raffaello Paletta <raffaellopaletta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
  

/*
 * SveltoRouter is an extension of AltoRouter class. 
 * See http://altorouter.com/ for more information about this project.
 *
 */    
  
class SveltoRouter extends AltoRouter
{				
	protected $routesDir;
	protected $authentication;
	
	/**
	 * Router creator
	 * 
     * @param string $basePath
     * @param array $matchTypes 
     * @param RestAuthentication $authentication an instance of authentication class     
     * @param string $routesDir path to dir where are stored routes config files (default src/routes.d)
     *              
     */			
	public function __construct($basePath = '', $matchTypes = array(), RestAuthentication $authentication = null, $routesDir = null)
	{		
		$this->routesDir = is_null($routesDir) ? dirname(__FILE__).'/routes.d' : $routesDir;
				
		if ($routes = $this->loadRoutes()) {
			
			$this->authentication = $authentication;			
			parent::__construct($routes, $basePath, $matchTypes);
			
		}	
	} 	
	
	/**
	 * Match a given Request Url against stored routes
	 * 
	 * @param string $requestUrl
	 * @param string $requestMethod
	 * 
	 * @return array|boolean Array with route information on success, false on failure (no match). 
	 * An appropriate http status code is sent to the http client
	 * 
	 */	
	public function match($requestUrl = null, $requestMethod = null)
	{											
		$match = parent::match($requestUrl, $requestMethod);
		
		if ($match) {		
							
			// rest authentication
																		
			if (isset($match['target']['auth']) && $match['target']['auth'] === true) {
												
				if (!is_null($this->authentication) && ($this->authentication instanceof RestAuthentication)) {
															
					if ($this->authentication->auth() === false) {
																		
						$this->authentication->setHttpHeaders();																	
						http_response_code(401);
						return false;							
					}					
				}
									
			}
			
			// check required parameters (only for POST)
													
			if (isset($match['target']['required'])) {
				
				if(is_array($match['target']['required'])) {
				
					foreach ($match['target']['required'] as $req) {
						
						if (!isset($_POST[$req])) {
							
							http_response_code(400);		
							return false;
						}
					}
					
				} else {
					
					if (!isset($_POST[$match['target']['required']])) {
						
						http_response_code(400);		
						return false;
					}					
				}										
			}
							
			// execute middlewares
										
			if (isset($match['target']['middlewares'])) {
								
				foreach ($match['target']['middlewares'] as $middleware) {
										
					$classname = $this->getMiddlewareName($middleware['name']);
					$action = $this->getActionName($middleware['action']);
					
					if (class_exists($classname)) {
						
						$obj = new $classname;
						$this->callUserFuncIfExists(array($obj,$action),$match['params']);	
											
					}
					
				}											
			}
			
			// call controller method
			
			if (isset($match['target']['controller']) &&  isset($match['target']['action'])) {
											
				if (class_exists($match['target']['controller'])) {
					
					$obj = new $match['target']['controller'];
					
					if (method_exists($obj, $match['target']['action'])) {
						
						$this->callUserFuncIfExists(array($obj,$match['target']['action']),$match['params']); 					
						http_response_code(200);	
						return $match;
					
					} else {
						
						http_response_code(500);		
						return false;	
						
					}
									
				} else {
										
					http_response_code(500);		
					return false;	
				
				}			
			}					
		}	
		
		http_response_code(404);			
		return false;
	}
	
	/**
	 * Set the routes authenticator
	 * 
	 * @param RestAuthentication $authentication an instance of authentication class
	 * 
	 */ 	
	public function setAuthentication(RestAuthentication $authentication = null)
	{
		$this->authentication = $authentication;
	}
	
	
	/**
	 * Load and validate routes configuration file.
	 * 	 	 
	 * @return array routes array as AltoRouter expects
	 * @throws \Exception in case of invalid json format
	 * 
	 */	
	protected function loadRoutes()
	{				
		$altoroutes = array();
		
		if ($handle = opendir($this->routesDir)) {
			
			while (false !== ($entry = readdir($handle))) {
				
				if ($entry != "." && $entry != ".." && is_file($this->routesDir.'/'.$entry)) {
					
					$routes_file = $this->routesDir.'/'.$entry;
															
					$json = file_get_contents($routes_file);
					$routes = json_decode($json, true);
				
					if (is_array($routes)) {
									
						foreach ($routes as $r) {
							
							if (!$this->isValidRoute($r)) {
								throw new \Exception("Invalid route format");
							}
							
							$target = array(
								'controller' => $this->getControllerName($r['controller']),
								'action' => empty($r['action']) ? 'index' : $this->getActionName($r['action']),
								'required' => (isset($r['required']) && is_array($r['required'])) ? $r['required'] : array(),
								'middlewares' => (isset($r['middlewares']) && is_array($r['middlewares'])) ? $r['middlewares'] : array(),
								'auth' => isset($r['auth']) ? $r['auth'] : false,
							);
											
							$route = array($r['method'], $r['path'], $target, $r['name']);
							
							$altoroutes[] = $route;	
						}
						
						
					} else {
						throw new \Exception("Unable to decode $routes_file. Check json format");
					}			
				}
			}
			closedir($handle);
		}
		
		return $altoroutes;
	}
	
	
	/**
	 * 
	 * @param string $classname
	 * 	 	 
	 * @return string in PascalCase convention
	 * 	 
	 */
	protected function normalizeRouteClassName($classname)
	{
		$parts = explode("_", $classname);
		$classname = ucfirst(strtolower(array_shift($parts)));
		
		foreach ($parts as $part) {
			
			$classname .= ucfirst(strtolower($part));				
		}
		
		return $classname;
	}
	
	/**
	 * 
	 * @param string $middleware
	 * 	 	 
	 * @return string in PascalCase convention with "Middleware" suffix
	 * 	 
	 */
	protected function getMiddlewareName($middleware) 
	{
		return $this->normalizeRouteClassName($middleware).'Middleware';
	}
	
	/**
	 * 
	 * @param string $middleware
	 * 	 	 
	 * @return string in PascalCase convention with "Controller" suffix
	 * 	 
	 */
	protected function getControllerName($controller) 
	{
		return $this->normalizeRouteClassName($controller).'Controller';
	}
	
	/**
	 * 
	 * @param string $action
	 * 	 	 
	 * @return string in camelCase convention
	 * 	 
	 */
	protected function getActionName($action='')
	{			
		if (empty($action)) {				
			return false;
			 
		} else {
			
			$parts = explode('_', $action);			
			$action = strtolower(array_shift($parts));
			
			foreach ($parts as $part) {
				
				$action	.= ucfirst(strtolower($part));	
			}
			
			return $action;
		}
	}

	/**
	 * 
	 * @param array $route
	 * 	 	 
	 * @return boolean
	 * 	 
	 */
	protected function isValidRoute($route = array())
	{
		if (!isset($route['method'])) return false; 
		if (!isset($route['path'])) return false; 
		if (!isset($route['controller'])) return false; 
							
		return true;
	}
	
	
	/**
	 * 
	 * @param string $func
	 * @param array $args
	 * 	 	 
	 * @return mixed
	 * 	 
	 */
	protected function callUserFuncIfExists($func, $args = array())
	{
		$retval=null;

		if (is_array($func)) {

			if (method_exists($func[0], $func[1])) {
				$retval=call_user_func_array(array($func[0], $func[1]), $args);
			}
		} 
		else {

			if (function_exists($func))	{
				$retval = call_user_func_array($func, $args);
			}
		}
		return $retval;
	}
 
}

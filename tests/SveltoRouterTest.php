<?php 

/*
 * This file is part of the sveltomvcframework\sveltomvcframework.
 *
 * Copyright (c) 2015 Raffaello Paletta <raffaellopaletta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class SveltoRouterTest extends \PHPUnit_Framework_TestCase {
 	
	protected $routesPath;
	protected $routeFile;
	protected $router;
	
	protected function setUp()
	{	
		unset($_POST['id']);	
		$this->routesPath = dirname(dirname(__FILE__)).'/tests';
		$this->routeFile = 'routes.json';					
		$this->router = new SveltoRouter('', array(), null, $this->routeFile, $this->routesPath);		
	}
 
	protected function tearDown()
	{
		unset($this->router);
	}
 	
 	/**
 	 * Test for simple route match
 	 *  	 
 	 */  			
	public function testMatch()
	{					
		$expected = array(
			'target' => array(
				'controller' => 'TestController',
				'action' => 'sayHello',
				'required' => array(),
				'middlewares' => array(),
				'auth' => false
			),
			'params' => array(
				'name' => 'raffaello'				
			),
			'name' => 'test_hello'
		);
					
		$this->assertEquals($expected, $this->router->match('/test/raffaello', 'GET'));					
	}
	
	/**
 	 * Test for hmac authenticated route match
 	 * 
 	 * Assertions: 
 	 * 		assert valid request
 	 * 		assert incomplete request (missing X-Hash http header)
 	 *  	assert unauthorized request (wrong hash) 	  
 	 */  		 	 
	public function testAuthHmacMatch()
	{	
		$auth = new HmacAuthentication(HMAC_SECRET);
		$this->router->setAuthentication($auth);									
		$x_hash = $_SERVER['HTTP_X_Hash'];						
		
		$expected = array(
			'target' => array(
				'controller' => 'TestController',
				'action' => 'privateInfo',
				'required' => array(),
				'middlewares' => array(),
				'auth' => true
			),
			'params' => array(
				'name' => 'raffaello'				
			),
			'name' => 'test_auth'
		);
					
		$this->assertEquals($expected, $this->router->match('/test/private/raffaello', 'GET'));
		
		unset($_SERVER['HTTP_X_Hash']);					
		
		$this->assertFalse($this->router->match('/test/private/raffaello', 'GET'));						
		
		$_SERVER['HTTP_X_Hash'] = $x_hash.'WRONG';
		
		$this->assertFalse($this->router->match('/test/private/raffaello', 'GET'));
					
	}
		
	public function testHttpResponseCode()
	{					
		$auth = new HmacAuthentication(HMAC_SECRET);
		$this->router->setAuthentication($auth);
		
		$this->router->match('/data/save', 'POST');		
		$this->assertEquals(400, http_response_code());			
				
		$this->router->match('/test/private/raffaello', 'GET');		
		$this->assertEquals(401, http_response_code());
					
		$this->router->match('/inexistent/route', 'GET');				
		$this->assertEquals(404, http_response_code());			
		
	}
	
	public function testPostRequest()
	{					
		$_POST = array('name' => 'raffaello', 'surname' => 'paletta');
				
		$this->router->match('/data/save', 'POST');		
		$this->assertEquals(200, http_response_code());			
	}			
}


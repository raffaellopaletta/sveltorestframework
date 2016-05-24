<?php 

/* 
 * Svelto Router API tests
 * 
 */ 
class SveltoRouterApiTest extends \PHPUnit_Framework_TestCase 
{				
	protected function setUp()
	{							
		$this->router = new SveltoRouter('', array());		
	}
 
	protected function tearDown()
	{
		unset($this->router);
	}
 	
 	/**
 	 * Test route match for GET request without params
 	 *  	 
 	 */  			
	public function testProductsListRequest()
	{					
		$expected = array(
			'target' => array(
				'controller' => 'ProductController',
				'action' => 'index',
				'required' => array(),
				'middlewares' => array(),
				'auth' => false
			),
			'params' => array(),
			'name' => 'products_list'
		);
		
		$match = $this->router->match('/products', 'GET');							
		$this->assertEquals($expected, $match);
		
		$match = $this->router->match('/products/', 'GET');							
		$this->assertEquals(false, $match);
		$this->assertEquals(404, http_response_code());
		
		$match = $this->router->match('/products', 'POST');							
		$this->assertEquals(false, $match);
		$this->assertEquals(400, http_response_code());		
	}
	
	/**
 	 * Test route match for GET request with a param
 	 *  	 
 	 */  			
	public function testProductViewRequest()
	{					
		$expected = array(
			'target' => array(
				'controller' => 'ProductController',
				'action' => 'view',
				'required' => array(),
				'middlewares' => array(),
				'auth' => false
			),
			'params' => array(
				'id' => 1
			),
			'name' => 'product_view'
		);
		
		$match = $this->router->match('/products/1/', 'GET');		
		$this->assertEquals($expected, $match);
		
		$match = $this->router->match('/products/1/', 'POST');							
		$this->assertEquals(false, $match);
		$this->assertEquals(404, http_response_code());			
	}
	
	/**
	 * Test route match for PUT request
	 * 
	 */ 		
	public function testProductUpdateRequest()
	{	
		$auth = new HmacAuthentication(HMAC_SECRET);
		$this->router->setAuthentication($auth);
									
		$expected = array(
			'target' => array(
				'controller' => 'ProductController',
				'action' => 'update',
				'required' => array(),
				'middlewares' => array(array("name"=>"authorization","action"=>"is_authorized")),
				'auth' => true
			),
			'params' => array(
				'id' => 1
			),
			'name' => 'product_update'
		);
		
		$match = $this->router->match('/products/1/', 'PUT');							
		$this->assertEquals($expected, $match);
		$this->assertEquals(200, http_response_code());
		
		unset($_SERVER['HTTP_X_Hash']);
		$match = $this->router->match('/products/1/', 'PUT');							
		$this->assertEquals(401, http_response_code());
				
		$match = $this->router->match('/products/', 'PUT');							
		$this->assertEquals(false, $match);
		$this->assertEquals(404, http_response_code());
		
	}	
		
}


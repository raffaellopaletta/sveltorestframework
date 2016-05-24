<?php 

class TestController {
			
	public function index()
	{	
		print "home page\n";
	}
	
	public function sayHello($name='')
	{	
		print "hello $name !!\n";					
	}		
	
	public function privateInfo($name='') 
	{
		print "hello $name, this is an authenticated route !!";					
	}
	
	public function save()
	{		
		print_r($_POST);
	}
	
	public function testPut()
	{
		$data = file_get_contents("php://input");
		print $data;
	}	
}

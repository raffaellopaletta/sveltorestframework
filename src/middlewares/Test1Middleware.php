<?php 

class Test1Middleware 
{				
	public function hello()
	{
		print "Hello, I am ".__CLASS__;
	}		
	
	public function doSomething()
	{
		print "Hello, I am ".__CLASS__.' and I do something' ;
	}
	
}

?>

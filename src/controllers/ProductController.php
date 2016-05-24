<?php 

class ProductController 
{	
	protected $products = array(
		array('name' => 'iPhone 6', 'price' => 659, 'category' => 'smartphones' ),
		array('name' => 'PS4', 'price' => 399, 'category' => 'consoles' ),
		array('name' => 'Harry Potter', 'price' => 15, 'category' => 'books' )
	);
				
	public function index()
	{			
		print json_encode($this->products);
	}
	
	public function update($id)
	{					
		print "PRODUCT ID: $id\n";
		//retrieve data from put
		$data = file_get_contents("php://input");		
		
		// ...
		// do something ...		
	}
	
	public function view($id) 
	{
		if (isset($this->products[$id])) {
			print json_encode($this->products[$id]);
		}
		
	}
}

<?php 

require '../../vendor/autoload.php';

try {
	
	//$auth = new HmacAuthentication('thisismysecret');
	//$auth = new HttpBasicAuthentication('My Realm');
	//$auth = new HttpDigestAuthentication('My Realm');
		
	$router = new SveltoRouter('', array());
	$match = $router->match();
		
	if (!$match) {
				
		$code = http_response_code();
				
		switch($code) {
			
			case '400':
			
				print "Bad Request";
				break;
			
			case '401':
			
				print "Unauthorized";
				break;
			
			case '404':
			
				print "Not Found";
				break;
			
			case '500':
				
				print "Internal Server Error";
				break;
			
			default :
			
				print "NO MATCH";
				break;
		}
			
	}
	
	
} catch(Exception $e) {
	
	print $e->getMessage();
	
}

?>

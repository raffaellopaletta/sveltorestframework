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
 * Implements HMAC authentication
 * 
 */ 
  
class HmacAuthentication extends RestAuthentication 
{
	protected $shared_key;
	protected $algo;
	
	/*
	 * @param string $shared_key hmac shared secret
	 * @param string $algo hmac cypher algorithm
	 * 
	 * @throws \Exception in case of invalid parameters
	 */ 	
	public function __construct($shared_key, $algo = 'sha256')
	{
		if (empty($shared_key) || empty($algo)) {
			
			throw new Exception ("Invalid Parameters");
		}
		
		$this->shared_key = $shared_key;
		$this->algo = $algo;
	}
	
	/*
	 * Add HMAC http header to the http request.
	 * 
	 * @param array associative array of associative arrays in the format $arr['wrapper']['option'] = $value. 
	 * 
	 * @return boolean
	 * @throws \Exception in case of invalid context option
	 * 
	 */ 		
	public function sign(&$context_option)
	{
		if (!isset($context_option['content'])) {
			
			error_log(__CLASS__.": Invalid Parameter. Required HTTP context options array");			
			return false;	
		}
				
		$content = $context_option['content'];
		$header = isset($context_option['header']) ? $context_option['header'] : '' ;
		
		$hash = hash_hmac($this->algo,$content,$this->shared_key);
		
		$header .= "X-HASH: $hash\r\n";
		
		$context_option['header'] = $header;
		
		return true;
	}
		
	/*
	 * HMAC auth method
	 * 
	 * @return boolean 
	 * 
	 */ 	
	public function auth()
	{	
		if (!isset($_REQUEST)) {
			error_log(__CLASS__.": No request found");
			return false;			
		}
		
		$headers = $this->requestHeaders();
								
		if (!isset($headers['X-Hash']) && !isset($headers['X-HASH'])) {
			
			error_log(__CLASS__.": HTTP header X-Hash not found");
			return false;
		}
		
		$req_hash = $headers['X-Hash'];
				
		$content = http_build_query($_POST);						
		$hash = hash_hmac($this->algo, $content, $this->shared_key);
		
		return ($hash == $req_hash) ;		
	}
	
	public function setHttpHeaders() {}
	
	/*
	 * Get http request headers
	 * 
	 * @return array
	 * 
	 */ 
	protected function requestHeaders()
	{
		if(function_exists("apache_request_headers")) // If apache_request_headers() exists...
		{
			if($headers = apache_request_headers()) // And works...
			{
				return $headers; // Use it
			}
		}
		$arh = array();
		$rx_http = '/\AHTTP_/';
		foreach($_SERVER as $key => $val) {
				if( preg_match($rx_http, $key) ) {
						$arh_key = preg_replace($rx_http, '', $key);
						$rx_matches = array();
						// do some nasty string manipulations to restore the original letter case
						// this should work in most cases
						$rx_matches = explode('_', strtolower($arh_key));
						if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
								foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
								$arh_key = implode('-', $rx_matches);
						}
						$arh[$arh_key] = $val;
				}
		}
		if(isset($_SERVER['CONTENT_TYPE'])) $arh['Content-Type'] = $_SERVER['CONTENT_TYPE'];
		if(isset($_SERVER['CONTENT_LENGTH'])) $arh['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
		return( $arh );			
	}
	
}

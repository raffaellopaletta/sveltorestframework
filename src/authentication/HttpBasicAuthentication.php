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
 * Implements HTTP Basic authentication
 * 
 */ 
 
class HttpBasicAuthentication extends RestAuthentication 
{
	protected $realm;
		
	/*
	 * @param string $realm http authentication realms
	 * 
	 */ 	
	public function __construct($realm='SimpleRest')
	{
		$this->realm = $realm;				
	}
	
	/*
	 * Retrieve users
	 * 
	 * @return array associative array that represents a credentials collection
	 * 
	 */ 	
	public function getUsers() 
	{
		return array(
			'user1' => 'pwd1',
			'user2' => 'pwd2',
			'user3' => 'pwd3'			
		);
	}
	
	/*
	 * HTTP Basic auth method
	 * 
	 * @return boolean 
	 * 
	 */ 		
	public function auth()
	{			
		if (!isset($_SERVER['PHP_AUTH_USER'])) {			
			error_log(__CLASS__.": No username provided");					
			return false;			
		} 
		
		foreach ($this->getUsers() as $username => $password) {
			
			if ($username == $_SERVER['PHP_AUTH_USER'] && $password == $_SERVER['PHP_AUTH_PW']) return true;
		
		}
		
		return false;
	}
	
	/*
	 * Add WWW-Authenticate header to the http response	 	
	 * 
	 */ 
	public function setHttpHeaders()
	{
		header('WWW-Authenticate: Basic realm="'.$this->realm.'"');		
	}
	
	
}

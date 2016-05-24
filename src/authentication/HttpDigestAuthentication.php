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
 * Implements HTTP Digest authentication
 * 
 */ 
 
class HttpDigestAuthentication extends HttpBasicAuthentication
{
			
	/*
	 * HTTP Basic auth method
	 * 
	 * @return boolean 
	 * 
	 */ 		
	public function auth()
	{			
		if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
			error_log(__CLASS__.": No username provided");					
			return false;			
		} 
		
		$users = $this->getUsers();
					
		if (!($data = $this->httpDigestParse($_SERVER['PHP_AUTH_DIGEST'])) || !isset($users[$data['username']])) return false;
		    
		$A1 = md5($data['username'] . ':' . $this->realm . ':' . $users[$data['username']]);
		$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
		$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

		if ($data['response'] == $valid_response) return true;
					
		return false;
	}
	
	/*
	 * Add WWW-Authenticate header to the http response	 	
	 * 
	 */ 
	public function setHttpHeaders()
	{	
		header('WWW-Authenticate: Digest realm="'.$this->realm.'",qop="auth",nonce="'.uniqid(rand(),true).'",opaque="'.md5($realm).'"');
	}
	
	/*
	 * Parse digest informations
	 *
	 */  
	protected function httpDigestParse($txt)
	{
		// protect against missing data
		$needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
		$data = array();
		$keys = implode('|', array_keys($needed_parts));

		preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

		foreach ($matches as $m) {
			$data[$m[1]] = $m[3] ? $m[3] : $m[4];
			unset($needed_parts[$m[1]]);
		}

		return $needed_parts ? false : $data;
	}
	
	
}

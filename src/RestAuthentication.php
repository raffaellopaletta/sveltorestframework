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
 * Abstract class that represent the authentication method for the routes
 * 
 */ 
 
abstract class RestAuthentication 
{	
	
	/*
	 * Implements the authentication logic. This method is called
	 * by SveltoRouter for each route where auth attribute = true if  
	 * an instance of this class is passed to the constructor of SveltoRouter
	 * 	 
	 * @return boolean
	 */ 
	abstract public function auth(); 
	
	/*
	 * This method is called by SveltoRouter if auth() method return false.
	 * It can be used to add custom http header to the http response.
	 * 	 
	 */
	abstract public function setHttpHeaders();
}

# Svelto REST Framework
Svelto REST Framework is a micro mvc framework for PHP 5.4+ useful for rapid REST API developement, inspired by [AltoRouter](https://github.com/dannyvankooten/AltoRouter).

## Main Features
* JSON routes mapping
* Extendible routes authentication (HTTP Basic, HTTP Digest, HMAC are built-in)
* Middlewares

## Requirements

1. PHP 5 >= 5.4.0
2. Install Svelto REST Framework using Composer
2. Setup URL rewriting so that all requests are handled by **index.php**
3. Create an instance of SveltoRouter, map your routes and match a request.

## Getting started


### Install Package

Using composer:

```
composer require sveltorestframework/sveltorestframework
```

### Directory Structure

```
src
|--
	|-- authentication	
	|   |-- HmacAuthentication.php
	|   |-- HttpBasicAuthentication.php
	|   `-- HttpDigestAuthentication.php
	|-- controllers				
	|-- middlewares   			
	|-- models					
	|-- routes.d				
	|   |-- default.json
	|-- RestAuthentication.php	
	|-- SveltoRouter.php		
	`-- web						
		`-- index.php					
```

### Requests Handler

The file **web/index.php** is the default requests handler:

```php
require '../../vendor/autoload.php';

try {
		
	$router = new SveltoRouter();
	$match = $router->match();
		
} catch(Exception $e) {
	
	print $e->getMessage();
	
}
```
The SveltoRouter constructor takes four optional params:

* *$basePath*: the base path. Useful when your project lives in a sub-folder, e.g. /api/
* *$matchTypes*: [see AltoRouter Documentation](http://altorouter.com/usage/mapping-routes.html)
* *$authentication*: an instance of RestAuthentication class (default is null)
* *$routesDir*: the directory where are stored the json files for routes mapping (default routes.d)

For rewrite all requests to this file we can use the url rewrite functions provided by web server. Following two examples for Apache and Nginx:

#### Apache

Create *.htaccess* into the **web** directory

```
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule . index.php [L]
```

#### Nginx

Add the following directive to the nginx.conf

```
try_files $uri /index.php;
```

## Create a sample rest api (step by step)

Suppose we want to implement the following methods (resource mapping):

* GET /products - Retrieves a list of products
* GET /products/1 - Retrieves a specific product (#1)
* POST /products - Creates a new product
* PUT /products/1 - Updates product #1   
* DELETE /products/1 - Deletes product #1

### Routes Mapping
Create a new file **products.json** in *routes.d* directory (we can map routes using one or more json files). 

```json
[
	{		
		"method":"GET",
		"path":"/products",		
		"name":"products_list",		
		"controller":"product",
		"action":"index"	
	},
	{
		"method":"GET",
		"path":"/products/[i:id]/",	
		"name":"product_view",		
		"controller":"product",
		"action":"view"	
	},	
	{
		"method":"POST",
		"path":"/products",	
		"name":"product_create",
		"required": ["name", "price", "category"],
		"middlewares": [{"name":"authorization","action":"is_authorized"}],
		"controller":"product",
		"action":"create",
		"auth":true
	},
	{
		"method":"PUT",
		"path":"/products/[i:id]/",	
		"name":"product_update",		
		"middlewares": [{"name":"authorization","action":"is_authorized"}],
		"controller":"product",
		"action":"update",
		"auth":true
	},
	{
		"method":"DELETE",
		"path":"/products/[i:id]/",	
		"name":"product_delete",		
		"middlewares": [{"name":"authorization","action":"is_authorized"}],
		"controller":"product",
		"action":"delete",
		"auth":true
	}
]
```

* **method** (string): the HTTP requests methods. Using pipe to specify more methods (GET|POST|PATCH|PUT|DELETE)
* **path** (string): route pattern
* **name** (string, optional)

```php
*                    // Match all request URIs
[i]                  // Match an integer
[i:id]               // Match an integer as 'id'
[a:action]           // Match alphanumeric characters as 'action'
[h:key]              // Match hexadecimal characters as 'key'
[:action]            // Match anything up to the next / or end of the URI as 'action'
[create|edit:action] // Match either 'create' or 'edit' as 'action'
[*]                  // Catch all (lazy, stops at the next trailing slash)
[*:trailing]         // Catch all as 'trailing' (lazy)
[**:trailing]        // Catch all (possessive - will match the rest of the URI)
.[:format]?          // Match an optional parameter 'format' - a / or . before the block is also optional

```
-- <cite>[from AltoRouter Documentation](http://altorouter.com/usage/mapping-routes.html)</cite>

* **middlewares** (array, optional): one or more methods of a class that will be called by router before controller method
	* **name** (string): class name (will be transformed in PascalCase with suffix Middleware, e.g. class_name => ClassNameMiddleware)
	* **action** (string): method name (will be transformed in camelCase, e.g. method_name => methodName)
	* *examples*: [{"name":"midd_one","action":"method_test"}, {"name":"midd_two","action":"method_test"}]
* **controller** (string): the controller class name (will be transformed in PascalCase with suffix Controller, e.g. class_name => ClassNameController)
* **action** (string, optional): the controller's method name (will be transformed in camelCase, e.g. method_name => methodName). If no specified, it'll be *index*
* **auth** (boolean, optional): specify whether the route should be authenticated. If *true* the method *auth* of an authentication instance will be called

### Create Controller
Create a new controller class into *controllers* directory.

```php
class ProductController
{	
	public function index() 
	{		
		// ...		
	}
	
	public function view($id) 
	{		
		// ...				
	}
	
	public function create() 
	{		
		// ...				
	}
	
	public function update($id) 
	{		
		// ...				
	}
	
	public function delete($id) 
	{		
		// ...				
	}	
}
```	

Naming conventions:

* The name of controller classes must be in PascalCase convention with the suffix Controller
* The name of methods (actions) must be in camelCase convention


### Create Middleware
Create a new middleware class into *middlewares* directory.

```php
class AuthorizationMiddleware 
{
	public function isAuthorized()
	{
		// check authorization ...				
	}
}
```

### Customize the Requests Handler


```php
require '../../vendor/autoload.php';

try {
	
	$auth = new HmacAuthentication('thisismysecret');
	//$auth = new HttpBasicAuthentication('My Realm');
	//$auth = new HttpDigestAuthentication('My Realm');
		
	$router = new SveltoRouter('', array(), $auth);
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
```

### Extends Authentication 

To extend route's authentication simply create a new class **MyAuthentication.php** in *authentication* directory that extends the abstract class **RestAuthentication** 
and implement both methods *auth* and *setHttpHeaders*



## License

(MIT License)

Copyright (c) 2015 Raffaello Paletta <raffaellopaletta@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

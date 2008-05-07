Curl, CurlResponse
==================

Description
-----------

A basic CURL wrapper written in PHP
See [http://php.net/curl](http://php.net/curl) for more information about the libcurl extension for PHP


Installation
------------

	git clone git://github.com/shuber/curl.git


Usage
-----

### Initialization

Simply require and initialize the Curl class like so

	require_once 'curl.php';
	$curl = new Curl;

### Requests

TODO

### The CurlResponse Object

TODO

### Basic Configuration Options

You can easily set the referer or user-agent like so

	$curl->referer = 'http://google.com';
	$curl->user_agent = 'some user agent string';
	
You may even set these headers manually if you wish (see below)

### Setting Custom Headers

You can set custom headers to send with the request like so

	$curl->headers['Host'] = 12.345.678.90;
	$curl->headers['Some-Custom-Header'] = 'Some Custom Value';

### Setting Custom CURL request options

TODO


Other
-----

Problems, comments, and suggestions all welcome: [shuber@huberry.com](mailto:shuber@huberry.com)
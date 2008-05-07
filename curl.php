<?php

# Curl, CurlResponse
#
# Author  Sean Huber - shuber@huberry.com
# Date    May 2008
#
# A basic CURL wrapper written in PHP
#
# See the README for documentation/examples or http://php.net/curl for more information about the libcurl extension for PHP

class Curl {

	public $cookie_file = 'curl_cookie.txt';
	public $headers = array();
	public $options = array();
	public $referer = '';
	public $user_agent = '';

	protected $error = '';

	public function __construct() {
		$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
	}

	public function delete($url, $vars = array()) {
		return $this->request('DELETE', $url, $vars);
	}

	public function error() {
		return $this->error;
	}

	public function get($url, $vars = array()) {
		if (!empty($vars)) {
			$url .= (stripos($url, '?') !== false) ? '&' : '?';
			$url .= http_build_query($vars);
		}
		return $this->request('GET', $url);
	}

	public function post($url, $vars = array()) {
		return $this->request('POST', $url, $vars);
	}

	public function put($url, $vars = array()) {
		return $this->request('PUT', $url, $vars);
	}

	protected function request($method, $url, $vars = array()) {
		$handle = curl_init();
		
		# Set some default CURL options
		curl_setopt($handle, CURLOPT_COOKIEFILE, $this->cookie_file);
		curl_setopt($handle, CURLOPT_COOKIEJAR, $this->cookie_file);
		curl_setopt($handle, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($handle, CURLOPT_HEADER, 1);
		curl_setopt($handle, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $vars);
		curl_setopt($handle, CURLOPT_REFERER, $this->referer);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($handle, CURLOPT_URL, $url);
		curl_setopt($handle, CURLOPT_USERAGENT, $this->user_agent);
		
		# Determine the request method and set the correct CURL option
		switch ($method) {
			case 'GET':
				curl_setopt($handle, CURLOPT_HTTPGET, 1);
				break;
			case 'POST':
				curl_setopt($handle, CURLOPT_POST, 1);
				break;
			default:
				curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
		}
		
		# Set any custom CURL options
		foreach ($this->options as $option => $value) {
			curl_setopt($handle, constant('CURLOPT_'.str_replace('CURLOPT_', '', strtoupper($option))), $value);
		}
		
		$response = curl_exec($handle);
		if ($response) {
			$response = new CurlResponse($response);
		} else {
			$this->error = curl_errno($handle).' - '.curl_error($handle);
		}
		curl_close($handle);
		return $response;
	}

}

class CurlResponse {

	public $body = '';
	public $headers = array();

	public function __construct($response) {
		# Extract headers from response
		$pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';
		preg_match_all($pattern, $response, $matches);
		$headers = split("\r\n", str_replace("\r\n\r\n", '', array_pop($matches[0])));
		
		# Extract the version and status from the first header
		$version_and_status = array_shift($headers);
		preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $version_and_status, $matches);
		$this->headers['Http-Version'] = $matches[1];
		$this->headers['Status-Code'] = $matches[2];
		$this->headers['Status'] = $matches[2].' '.$matches[3];
		
		# Convert headers into an associative array
		foreach ($headers as $header) {
			preg_match('#(.*?)\:\s(.*)#', $header, $matches);
			$this->headers[$matches[1]] = $matches[2];
		}
		
		# Remove the headers from the response body
		$this->body = preg_replace($pattern, '', $response);
	}

	public function __toString() {
		return $this->body;
	}

}

?>
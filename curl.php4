<?php

# Curl, CurlResponse
#
# Author  Sean Huber - shuber@huberry.com
# Date    May 2008
#
# A basic CURL wrapper for PHP
#
# See the README for documentation/examples or http://php.net/curl for more information about the libcurl extension for PHP

class Curl 
{

    var $cookie_file = 'curl_cookie.txt';
    var $headers = array();
    var $options = array();
    var $referer = '';
    var $user_agent = '';
 
    # Protected
    var $error = '';
    var $handle;


    function Curl() 
    {
        $this->__construct();
    }
    
    function __construct() 
    {
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'];
    }
      
    function delete($url, $vars = array()) 
    {
        return $this->request('DELETE', $url, $vars);
    }
    
    function error() 
    {
        return $this->error;
    }
    
    function get($url, $vars = array()) 
    {
        if (!empty($vars)) {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= http_build_query($vars);
        }
        return $this->request('GET', $url);
    }
    
    function post($url, $vars = array()) 
    {
        return $this->request('POST', $url, $vars);
    }
    
    function put($url, $vars = array()) 
    {
        return $this->request('PUT', $url, $vars);
    }
    
    # Protected
    function request($method, $url, $vars = array()) 
    {
        $this->handle = curl_init();
        
        # Set some default CURL options
        curl_setopt($this->handle, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($this->handle, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($this->handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->handle, CURLOPT_HEADER, true);
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, http_build_query($vars));
        curl_setopt($this->handle, CURLOPT_REFERER, $this->referer);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_USERAGENT, $this->user_agent);
        
        # Format custom headers for this request and set CURL option
        $headers = array();
        foreach ($this->headers as $key => $value) {
            $headers[] = $key.': '.$value;
        }
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);
        
        # Determine the request method and set the correct CURL option
        switch ($method) {
            case 'GET':
                curl_setopt($this->handle, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($this->handle, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $method);
        }
        
        # Set any custom CURL options
        foreach ($this->options as $option => $value) {
            curl_setopt($this->handle, constant('CURLOPT_'.str_replace('CURLOPT_', '', strtoupper($option))), $value);
        }
        
        $response = curl_exec($this->handle);
        if ($response) {
            $response = new CurlResponse($response);
        } else {
            $this->error = curl_errno($this->handle).' - '.curl_error($this->handle);
        }
        curl_close($this->handle);
        return $response;
    }
}

class CurlResponse 
{
    
    var $body = '';
    var $headers = array();
    
    
    function CurlResponse($response)
    {
        $this->__construct($response);
    }
    
    function __construct($response) 
    {
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
    
    function __toString() 
    {
        return $this->body;
    }
}

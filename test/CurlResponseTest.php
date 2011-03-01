<?php
require_once dirname(__FILE__).'/../lib/curl.php';
require_once dirname(__FILE__).'/../lib/curl_response.php';

class CurlResponseTest extends PHPUnit_Framework_TestCase
{

  function setUp()
  {
    $this->curl = new Curl();
    $this->response = $this->curl->get('www.google.com');
  }

  function testShouldSeparateResponseHeadersFromBody()
  {
    $this->assertTrue(is_array($this->response->headers));
    $this->assertRegExp('#^<!doctype#', $this->response->body);
  }

  function testShouldSetStatusHeaders()
  {
    $this->assertEquals(200, $this->response->headers['Status-Code']);
    $this->assertEquals('200 OK', $this->response->headers['Status']);
  }

  function testShouldReturnResponseBodyWhenCallingToString()
  {
    $this->assertEquals($this->response->body, (string) $this->response);
  }

}
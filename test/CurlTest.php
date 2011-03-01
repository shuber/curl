<?php
require_once dirname(__FILE__).'/../lib/curl.php';
require_once dirname(__FILE__).'/../lib/curl_response.php';

class CurlTest extends PHPUnit_Framework_TestCase
{

  function setup()
  {
    $this->curl = new Curl;
    $this->response = $this->curl->get('www.google.com');
  }

  function testGet()
  {
    $this->assertRegExp('#google#', (string) $this->response);
    $this->assertEquals(200, $this->response->headers['Status-Code']);
  }

  function testError()
  {
    $this->curl->get('diaewkaksdljf-invalid-url-dot-com.com');
    $err = $this->curl->error();
    $this->assertTrue(!empty($err));
  }

}
<?php
require_once dirname(__FILE__).'/../curl.php';

class CurlTest extends PHPUnit_Framework_TestCase
{

  function testGet()
  {
    $curl = new Curl();
    $response = $curl->get('www.google.com');
    $this->assertRegExp('#google#', (string) $response);
    $this->assertEquals(200, $response->headers['Status-Code']);
  }

  function testError()
  {
    $curl = new Curl();
    $curl->get('diaewkaksdljf-invalid-url-dot-com.com');
    $err = $curl->error();
    $this->assertTrue(!empty($err));
  }

  function testSsl()
  {
    $curl = new Curl();
    $response = $curl->get('https://www.facebook.com/');
    $this->assertEquals(200, $response->headers['Status-Code']);
  }

  function testValidatedSsl()
  {
    $curl = new Curl();
    $curl->setValidateSsl(true);
    $response = $curl->get('https://www.facebook.com/');
    $this->assertEquals(200, $response->headers['Status-Code']);
  }
}
<?php

namespace Curl\Test\Unit;

use shuber\Curl\Curl;

class CurlTest extends \PHPUnit_Framework_TestCase {

    function setUp() {
        $this->curl = new Curl;
        $this->response = $this->curl->get('www.google.com');
    }

    function test_get() {
        $this->assertRegExp('/google/', $this->response->body);
        $this->assertEquals(200, $this->response->headers['Status-Code']);
    }

    function test_error() {
        $this->curl->get('diaewkaksdljf-invalid-url-dot-com.com');
        $this->assertNotEmpty($this->curl->error());
    }

}